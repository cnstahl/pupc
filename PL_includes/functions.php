<?php
/* This file contains the the MySQL connection and definitions for all of the custom functions used in PSPS Portal:
login(), register(), valid_login(), valid_registration(), registration_error(), add_question(), 
display_questions(), add_debate(), add_response(), display_debates(), display_responses() */

ob_start();

//Create MySQL connection variable
$mysql_con = mysqli_connect("localhost", "psps_platmin", "Zx2p?&d:+bbD", "psps_platform") or die(mysqli_connect_error());

function login($username, $password)
{
	global $mysql_con;
	$SessID = str_shuffle("NnOoPpQqRrSsTtUuVvWwXxYyZz0123456789");
	setcookie("Psps_sess", $SessID);
	$UserID_query = mysqli_fetch_array(mysqli_query($mysql_con, "SELECT uid FROM members WHERE email='$username'")) or die(mysqli_query($mysql_con));
	$UserID = $UserID_query[0];
	setcookie("Plink_uid", $UserID);
	if (is_numeric($UserID))
		mysqli_query($mysql_con, "UPDATE members SET session_id='$SessID' WHERE uid=$UserID") or die(mysqli_query($mysql_con));
}

function valid_login($username, $password)
{
	global $mysql_con;
	$password = generate_hash($password, get_salt($username));
	$query = mysqli_query($mysql_con, "SELECT * FROM members WHERE password='$password' AND email='$username'") or die(mysqli_query($mysql_con));
	return mysqli_num_rows($query);
}

function logged_in()
{
	global $mysql_con;
	$SessID = safe($_COOKIE["Psps_sess"], "sql");
	$UserID = $_COOKIE["Plink_uid"];
	if (is_numeric($UserID))
	{
		$query = mysqli_query($mysql_con, "SELECT * FROM members WHERE uid=$UserID AND session_id='$SessID'");
		if (mysqli_num_rows($query))
			return true;
	}
}

function register($password, $email)
{
	global $mysql_con;
	date_default_timezone_set("America/New_York");
	$date = date("d/m/Y");
	$ip = $_SERVER["REMOTE_ADDR"]; 
	$type = 3;
	$salt = substr(str_shuffle("AABcDdeFgHJkLmNnoPqQrrSssTtuVwxYz12334567889"),0,6);
	$password = generate_hash($password, $salt);
	$UserID_query = mysqli_query($mysql_con, "SELECT uid FROM members ORDER BY uid DESC LIMIT 1");
	$UserID = mysqli_fetch_array($UserID_query);
	$UserID = $UserID[0]+1;
	email_registrations($email);
	mysqli_query($mysql_con, "INSERT INTO members (date, IP, type, salt, password, uid, email) VALUES ('$date', '$ip', '$type', '$salt', '$password', '$UserID', '$email')");
}

function valid_registration($password, $email)
{
	global $mysql_con;
	$email_query = mysqli_query($mysql_con, "SELECT * FROM members WHERE email='$email'");
	return !(mysqli_num_rows($email_query) || !preg_match('#[a-zA-Z0-9_]+@princeton\.edu#i', $email) || strlen($password) < 7 || strlen($password) > 100);
}

function registration_error($password, $email)
{
	global $mysql_con;
	$email_query = mysqli_query($mysql_con, "SELECT * FROM members WHERE email='$email'");
	$error = "There was a problem with your registration:";
	if (mysqli_num_rows($email_query))
		$error .= "<br>- The email you entered already belongs to another account.";
	if (!preg_match('#[a-zA-Z0-9]+@princeton\.edu#i', $email))
		$error .= "<br>- The email you entered was invalid; the correct format is NetID@princeton.edu.";
	if (strlen($password) < 7 || strlen($password) > 100)
		$error .= "<br>- Your password must be between 7 and 100 characters long.";
	
	return $error;
}

function reset_password($code, $email, $new_password)
{
	global $mysql_con;
	$code_query = mysqli_query($mysql_con, "SELECT * FROM reset_codes WHERE code='$code'");
	if (mysqli_num_rows($code_query))
	{
		$salt = substr(str_shuffle("AABcDdeFgHJkLmNnoPqQrrSssTtuVwxYz12334567889"),0,6);
		$password = generate_hash($password, $salt);
		mysqli_query($mysql_con, "UPDATE members SET password='$password' WHERE email='$email'");
		return "Your password was sucessfully reset!";
	}
	else
		return "That code was not found in the database; please attempt to reset your password again.";
}

function logout()
{
	global $mysql_con;
	$UserID = $_COOKIE["Plink_uid"];
	if (is_numeric($UserID))
	{
		mysqli_query($mysql_con, "UPDATE members SET session_id=NULL WHERE uid=$UserID") or die(mysqli_error($mysql_con));
		setcookie("Plink_uid", "", time()-3600);
		setcookie("Psps_sess", "", time()-3600);
	}
}

function add_question($title, $content)
{
	global $mysql_con;
	$QuestionID_query = mysqli_fetch_array(mysqli_query($mysql_con, "SELECT qid FROM questions ORDER BY qid LIMIT 1"));
		 $QuestionID = $QuestionID_query[0]+1;
	$author = $_COOKIE["Plink_uid"];
	mysqli_query($mysql_con, "INSERT INTO questions (title, content, qid, author_id) VALUES ('$title', '$content', $QuestionID, '$author')");
}

function add_answer($content)
{
	global $mysql_con;
	$PostID_query = mysqli_fetch_array(mysqli_query($mysql_con, "SELECT post_id FROM responses ORDER BY post_id DESC LIMIT 1"), MYSQLI_NUM);
	$PostID = $PostID_query[0]+1;
	$author = $_COOKIE["Plink_uid"];
	$question = mysqli_real_escape_string($mysql_con, $_GET["question"]);
	date_default_timezone_set("America/New_York");
	$date = date("d/m/Y");
	//Note that type 1 = The Connection, and type 2 = Debates
	mysqli_query($mysql_con, "INSERT INTO responses (type, content, post_id, author_id, question, date) VALUES (1, '$content', $PostID, '$author', '$question', '$date')");
}

function display_answers($question)
{
	global $mysql_con;
	$qid = get_qid($question);
	$ans_query = mysqli_query($mysql_con, "SELECT content, up_votes, down_votes FROM responses WHERE question='$question' OR post_id=$qid AND type=1 ORDER BY id DESC");
	if ($ans_query)
		while ($content = safe(mysqli_fetch_array($ans_query, MYSQLI_NUM)[0], "html"))
			echo "<span class=\"list\">\n$content\n</span><hr />\n";
}

function display_replies($message)
{
	global $mysql_con;
	$qid = get_qid($question);
	$ans_query = mysqli_query($mysql_con, "SELECT content FROM messages WHERE parent_id=$message ORDER BY message_id DESC");
	if ($ans_query)
		while ($content = safe(mysqli_fetch_array($ans_query, MYSQLI_NUM)[0], "html"))
			echo "<span class=\"list\">\n$content\n</span><hr />\n";
}

function display_questions()
{
	global $mysql_con;
	$Q_query = mysqli_query($mysql_con, "SELECT title FROM questions");
	while ($title = safe(mysqli_fetch_array($Q_query, MYSQLI_NUM)[0], "html"))
		echo "<div class=\"media\">
								<div class=\"media-body\">
									 <header><h4 class=\"media-heading\"><a href=\"?question=$title#connection\">$title</a></h4></header>
								</div>
						  </div>";
}

function display_recent_answers()
{
	global $mysql_con;
	$Q_query = mysqli_query($mysql_con, "SELECT content, post_id FROM responses WHERE type=1 ORDER BY id DESC LIMIT 5");
	while ($data = mysqli_fetch_array($Q_query, MYSQLI_NUM))
	{
		$content = safe($data[0], "html");
		$question = safe(get_title($data[1]), "html");
		echo "<div class=\"media\">
								<div class=\"media-body\">
									 <header>$content | posted in <b><a href=\"?question=$question#connection\">$question</a></b></header>
								</div>
						  </div>";
	}
}

function display_inbox()
{
	global $mysql_con;
	$UserID = (int)$_COOKIE["Plink_uid"];
	$M_query = mysqli_query($mysql_con, "SELECT subject,message_id FROM messages WHERE recipient=$UserID");
	while ($message = mysqli_fetch_array($M_query, MYSQLI_NUM))
	{
		$subject = safe($message[0], "html");
		$id = $message[1];
		echo "<div class=\"media\">
								<div class=\"media-body\">
									 <header><h4 class=\"media-heading\"><a href=\"?message=$id#messenger\">$subject</a></h4></header>
								</div>
						  </div>";
	}
}

function display_offers()
{
	global $mysql_con;
	$Q_query = mysqli_query($mysql_con, "SELECT quantity,subject,date,author FROM exchanges WHERE type=2");
	while ($data = mysqli_fetch_array($Q_query, MYSQLI_NUM))
	{
		$quantity = safe($data[0], "html");
		$subject = safe($data[1], "html");
		$date = safe($data[2], "html");
		$author = $data[3];
		$mentor_msg = "Offering a physics mentoring session for a group of $quantity students on $subject, on $date";
		echo "<div class=\"media\">
	<li>
		<header><h4 class=\"media-heading\"><a href=\"?to=$author&subject=$mentor_msg#messenger\">Physics mentoring session on $subject for a group of $quantity students, on $date</a></h4></header>
	</li>
</div>";
		
	}
}

function display_requests()
{
	global $mysql_con;
	$Q_query = mysqli_query($mysql_con, "SELECT quantity,subject,date,author FROM exchanges WHERE type=1");
	while ($data = mysqli_fetch_array($Q_query, MYSQLI_NUM))
	{
		$quantity = safe($data[0], "html");
		$subject = safe($data[1], "html");
		$date = safe($data[2], "html");
		$author = safe($data[3], "html");
		$mentor_msg = "Request for a physics mentoring session with $quantity students on $subject, on $date";
		echo "<div class=\"media\">
	<li>
		<header><h4 class=\"media-heading\"><a href=\"?&to=$author&subject=$mentor_msg#messenger\">Physics mentoring session on $subject with $quantity students, on $date</a></h4></header>
	</li>
</div>";
		
	}
}

function add_pass($subject, $date, $quantity, $type)
{
	global $mysql_con;
	$PassID_query = mysqli_fetch_array(mysqli_query($mysql_con, "SELECT pass_id FROM exchanges ORDER BY pass_id LIMIT 1"), MYSQLI_NUM);// or die("horses".mysqli_error($mysql_con));
	$PassID = $PassID_query[0]+1;
	$author = (int)$_COOKIE["Plink_uid"];
	$subject = safe($subject, "sql");
	$date = safe($date, "sql");
	$quantity = (int)$quantity;
	$type = (int)$type;
	//Note that type 1 = offer, and type 2 = requests
	mysqli_query($mysql_con, "INSERT INTO exchanges (quantity, subject, date, author, type, pass_id) VALUES ($quantity, '$subject', '$date', $author, $type, $PassID)") or die("horses".mysqli_error($mysql_con));
}

function get_question($question)
{
	global $mysql_con;
	$Q_query = mysqli_query($mysql_con, "SELECT title,content FROM questions WHERE title='$question'");
	$question = mysqli_fetch_array($Q_query, MYSQLI_NUM);
	$question[0] = safe($question[0], "html");
	$question[1] = safe($question[1], "html");
	return $question;
}

function get_message($id)
{
	global $mysql_con;
	$M_query = mysqli_query($mysql_con, "SELECT subject,content,date,recipient,sender FROM messages WHERE message_id=$id") or die(mysqli_error($mysql_con));
	$message = mysqli_fetch_array($M_query, MYSQLI_NUM);
	$message[0] = safe($message[0], "html");
	$message[1] = safe($message[1], "html");
	$message[2] = safe($message[2], "html");
	$message[3] = safe($message[3], "html");
	$message[4] = safe($message[4], "html");
	return $message;
}

function get_qid($question)
{
	global $mysql_con;
	$Q_query = mysqli_query($mysql_con, "SELECT qid FROM questions WHERE title='$question'");
	$qid = mysqli_fetch_array($Q_query, MYSQLI_NUM);
	$qid = $qid[0];
	return $qid;
}

function add_debate($title, $description, $video_id, $bio)
{
	global $mysql_con;
	$DebateID_query = mysqli_fetch_array(mysqli_query($mysql_con, "SELECT did FROM debates ORDER BY did LIMIT 1"));
	$DebateID = $DebateID_query[0]+1;
	$author = $_COOKIE["Plink_uid"];
	mysqli_query($mysql_con, "INSERT INTO debates (title, description, video_id, did, author_id, bio) VALUES ('$title', '$description', '$video_id', $DebateID, '$author', '$bio')") or die(mysqli_error($mysql_con));
}

function add_response($content, $yn)
{
	global $mysql_con;
	$PostID_query = mysqli_fetch_array(mysqli_query($mysql_con, "SELECT post_id FROM responses WHERE type=2 ORDER BY post_id LIMIT 1"), MYSQLI_NUM);
	$PostID = $PostID_query[0]+1;
	$author = $_COOKIE["Plink_uid"];
	$debate = $_GET["debate"];
	date_default_timezone_set("America/New_York");
	$date = date("d/m/Y");
	//Note that type 1 = The Connection, type 2 = Debates
	mysqli_query($mysql_con, "INSERT INTO responses (type, content, post_id, author_id, question, date, yn) VALUES (2, '$content', $PostID, '$author', '$debate', '$date', $yn)");
}

function send_message($recipient, $subject, $message)
{
	global $mysql_con;
	$MessageID_query = mysqli_fetch_array(mysqli_query($mysql_con, "SELECT message_id FROM messages ORDER BY message_id LIMIT 1"), MYSQLI_NUM);
	$MessageID = $MessageID_query[0]+1;
	$sender = $_COOKIE["Plink_uid"];
	date_default_timezone_set("America/New_York");
	$date = date("d/m/Y");
	mysqli_query($mysql_con, "INSERT INTO messages (message_id, recipient, sender, subject, content, date) VALUES ($MessageID, '$recipient', '$sender', '$subject', '$message', '$date')") or die(mysqli_error($mysql_con));
}

function add_reply($parent, $message)
{
	global $mysql_con;
	$MessageID_query = mysqli_fetch_array(mysqli_query($mysql_con, "SELECT message_id FROM messages ORDER BY message_id LIMIT 1"), MYSQLI_NUM);
	$MessageID = $MessageID_query[0]+1;
	$sender = $_COOKIE["Plink_uid"];
	$parent = (int)$parent;
	$message = safe($message, "sql");
	date_default_timezone_set("America/New_York");
	$date = date("d/m/Y");
	mysqli_query($mysql_con, "INSERT INTO messages (message_id, sender, content, date, parent_id) VALUES ($MessageID, '$sender', '$message', '$date', '$parent')") or die(mysqli_error($mysql_con));
}

function display_responses($debate)
{
	global $mysql_con;
	$ans_query = mysqli_query($mysql_con, "SELECT content, date, author_id, up_votes, down_votes, yn FROM responses WHERE question='$debate' AND type=2 ORDER BY post_id DESC");
	while ($content = mysqli_fetch_array($ans_query, MYSQLI_NUM))
	{
		$contents = safe($content[0], "html");
		date_default_timezone_set("America/New_York");
		$date = safe($content[1], "html");
		$uid = $content[2];
		$username = get_username($content[2]);

		$prnt = "<span class=\"agree\"><b>$username ($date):</b><br />\n$contents\n<hr />\n</span>";
		echo $prnt;
	}
}

function display_debates()
{
	global $mysql_con;
	$D_query = mysqli_query($mysql_con, "SELECT title, video_id, description FROM debates");
	while ($info = mysqli_fetch_array($D_query, MYSQLI_NUM))
	{
		$title = safe($info[0], "html");
		$thumb = safe($info[1], "html");
		$description = safe($info[2], "html");
		echo "<article class=\"media\">
								<div class=\"media-body\">
									 <header><h4 class=\"media-heading\"><img src=\"http://i2.ytimg.com/vi/$thumb/default.jpg\" /> <a href=\"?debate=$title\">$title</a></h4> <i>$description</i></header>
								</div>
						  </article>";
	}
}

function get_debate($debate)
{
	global $mysql_con;
	$D_query = mysqli_query($mysql_con, "SELECT title,description,video_id,bio FROM debates WHERE title='$debate'");
	$debate = mysqli_fetch_array($D_query, MYSQLI_NUM);
	array_walk($debate, 'safe', "html");
	return $debate;
}

function administrator()
{
	global $mysql_con;
	$UserID = $_COOKIE["Plink_uid"];
	if (is_numeric($UserID))
	{
		$query = mysqli_query($mysql_con, "SELECT * FROM members WHERE uid=$UserID AND type=1");
		if (mysqli_num_rows($query))
			return true;
	}
}

function validated()
{
	global $mysql_con;
	$UserID = $_COOKIE["Plink_uid"];
	if (is_numeric($UserID))
	{
		$query = mysqli_query($mysql_con, "SELECT * FROM members WHERE uid=$UserID AND type!=3");
		if (mysqli_num_rows($query))
			return true;
	}
}

function registered($email)
{
	global $mysql_con;
	$email = safe($email, "sql");
	$query = mysqli_query($mysql_con, "SELECT * FROM members WHERE email='$email'");
	if (mysqli_num_rows($query))
		return true;
}
	
function generate_hash($password, $salt)
{
	global $mysql_con;
	$password = md5($password);
	$salt = md5($salt);
	$hash = md5($salt.$password.$salt);
	return $hash;
}

function get_salt($username)
{
	global $mysql_con;
	$query = mysqli_query($mysql_con, "SELECT salt FROM members WHERE email='$username'") or die(mysqli_query($mysql_con));
	$salt = mysqli_fetch_array($query, MYSQL_NUM);
	return $salt[0];
}

function get_username($UserId)
{		 
	global $mysql_con;
	$UserId = (int)$UserId;
	$query = mysqli_query($mysql_con, "SELECT email FROM members WHERE uid=$UserId") or die(mysqli_query($mysql_con));
	$username_array = mysqli_fetch_array($query);
	$username = $username_array[0];
	$end = strpos($username, '@');
	$username = substr($username,0,$end);
	return $username;
}

function get_title($id)
{		 
	global $mysql_con;
	$id = $id;
	$query = mysqli_query($mysql_con, "SELECT title FROM questions WHERE qid=$id") or die(mysqli_query($mysql_con));
	$title_array = mysqli_fetch_array($query);
	$title = $title_array[0];
	return $title;
}

function get_id($UserId)
{		 
	global $mysql_con;
	$UserId = "$UserId@princeton.edu";
	$query = mysqli_query($mysql_con, "SELECT uid FROM members WHERE email='$UserId'") or die(mysqli_query($mysql_con));
	$username_array = mysqli_fetch_array($query);
	$username = $username_array[0];
	$username = substr($username,0,$end);
	return $username;
}

function email_registrations($email)
{
	global $mysql_con;
	$end = strpos($email, '@');
	$name = substr($email, 0, $end);
	$rand = substr(str_shuffle("AaBbCcDdEeFfGgHhIIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz"), 0, 10);
	mysqli_query($mysql_con, "INSERT INTO verification_codes VALUES ('$rand')");
	$to		= $email;
	$subject = 'PSPS Portal registration confirmation';
	$message = "Hello $name,<br /><br />Thank you for signing up at the PSPS Portal, Princeton Society of Physics Students' online network and interstudent Q&A platform, open only to members of the Princeton University community. Its advantage is in having the ability to handle mentorship and mentoring requests from anyone who signs up. Every few weeks videos or other educational content containing physics-related topics will be posted, with the ability to discuss each of them, as well as to pose anonymous general questions about PSPS or the Physics Department to the officers. <br />Please hit the link below to confirm your e-mail and begin using the Portal!<br /><br />http://psps.mycpanel.princeton.edu/?code=".$rand.'&email='.$email."<br /><br />Thank you,<br />Pavel Shibayev '15<br />Physics Department<br />PSPS secretary/Portal developer<br /><br />If you'd like to unsubscribe, please go here: http://psps.mycpanel.princeton.edu/?action=unsubscribe&email=$email.";
	$headers = 'From: The PSPS Portal Administrator <do-not-reply@psps.mycpanel.princeton.edu>' . "\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "List-Unsubscribe: http://psps.mycpanel.princeton.edu/?action=unsubscribe&email=$email";
	mail($to, $subject, $message, $headers);
}

function email_reset($email)
{
	global $mysql_con;
	$end = strpos($email, '@');
	$name = substr($email, 0, $end);
	$rand = substr(str_shuffle("AaBbCcDdEeFfGgHhIIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz"), 0, 10);
	mysqli_query($mysql_con, "INSERT INTO reset_codes VALUES ('$rand')");
	$to		= $email;
	$subject = 'PSPS Portal password reset';
	$message = "Hello $name,<br /><br />To reset your password, please visit the following link: http://psps.mycpanel.princeton.edu/reset.php?reset=true&code=$rand&email=$email.";
	$headers = 'From: The PSPS Portal Administrator <do-not-reply@psps.mycpanel.princeton.edu>' . "\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "List-Unsubscribe: http://psps.mycpanel.princeton.edu/?action=unsubscribe&email=$email";
	mail($to, $subject, $message, $headers);
}

function verify($code, $email)
{
	global $mysql_con;
	$query = mysqli_query($mysql_con, "SELECT * FROM verification_codes WHERE code='$code'");
	if (mysqli_num_rows($query))
	{
		mysqli_query($mysql_con,"UPDATE members SET type=2 WHERE email='$email'");
		echo "<script>alert('Your account has been validated; explore the PSPS Portal now!');</script>";
	}
}

function safe($input, $method)
{
	global $mysql_con;
	switch($method)
	{
		case "sql":
			return mysqli_real_escape_string($mysql_con, $input);
		case "html":
			return htmlentities($input);
	}
}

function insert_stats()
{
	global $mysql_con;
	date_default_timezone_set("America/New_York");
	$date = date("d/m/Y");
	$ip = $_SERVER["REMOTE_ADDR"]; 
	$ua = $_SERVER["HTTP_USER_AGENT"];
	$ref = $_SERVER["HTTP_REFERER"];
	$uri = $_SERVER["REQUEST_URI"];
	//email_registrations($email);
	mysqli_query($mysql_con, "INSERT INTO statistics (date, IP, ) VALUES ('$date', '$ip', '$type', '$salt', '$password', '$UserID', '$email')");
	mysqli_query($mysql_con, "INSERT INTO statistics ()");
}
?>
