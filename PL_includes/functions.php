<?php
/* This file contains the the MySQL connection and definitions for all of the custom functions used in PSPS Portal:
 * login(), register(), valid_login(), valid_registration(), registration_error(), add_question(), 
 * display_questions(), add_debate(), add_response(), display_debates(), display_responses()
 */

// Create MySQL connection variable
$mysql_con = mysqli_connect("localhost", "pupc", "Zx2p?&d:+bbE", "psps_platform") or die(mysqli_connect_error());
$timezone = "America/New_York";
$rand_sess = "NnOoPpQqRrSsTtUuVvWwXxYyZz0123456789";
$rand_salt = "AABcDdeFgHJkLmNnoPqQrrSssTtuVwxYz12334567889";
$substr_salt = 6;
$rand_gen = "AaBbCcDdEeFfGgHhIIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz";
$substr_gen = 10;


/**********************************
 * Authentication functions       *
 *                                *
 **********************************/

/**
 * Authenticates the user with given username and password.
 * @param string $username the given username
 * @param string $password the given password
 * @return {@code true} if successful, {@code false} otherwise
 */
function login($username, $password)
{
	global $mysql_con, $rand_sess;
	$ip = $_SERVER["REMOTE_ADDR"];
	$SessID = str_shuffle($rand_sess);
	setcookie("Psps_sess", $SessID, 0, '/');
	$UserID_query = mysqli_fetch_array(mysqli_query($mysql_con, "SELECT uid FROM members WHERE email='$username'")) or die(mysqli_error($mysql_con));
	$UserID = $UserID_query[0];
	setcookie("Plink_uid", $UserID, 0, '/');
	if (is_numeric($UserID))
		mysqli_query($mysql_con, "UPDATE members SET session_id='$SessID', ip='$ip' WHERE uid=$UserID") or die(mysqli_error($mysql_con));
	return true;
}

/**
 * Checks if given password corresponds with the given username.
 * @param string $username the given username
 * @param string $password the given password
 * @return int {@code 1} if it does, {@code 0} otherwise
 */
function valid_login($username, $password)
{
	global $mysql_con;
	$password = generate_hash($password, get_salt($username));
	$query = mysqli_query($mysql_con, "SELECT * FROM members WHERE password='$password' AND email='$username'") or die(mysqli_query($mysql_con));
	return mysqli_num_rows($query);
}

/** Returns whether the current user is logged in. */
function logged_in()
{
	global $mysql_con;
	$SessID = safe($_COOKIE["Psps_sess"], "sql");
	$UserID = safe($_COOKIE["Plink_uid"], 'sql');
	if (is_numeric($UserID))
	{
		$query = mysqli_query($mysql_con, "SELECT * FROM members WHERE uid=$UserID AND session_id='$SessID'");
		if (mysqli_num_rows($query))
			return true;
	}
	return false;
}

/** Logs out the current user. */
function logout()
{
	global $mysql_con;
	$UserID = safe($_COOKIE["Plink_uid"], 'sql');
	if (is_numeric($UserID))
	{
		mysqli_query($mysql_con, "UPDATE members SET session_id=NULL WHERE uid=$UserID") or die(mysqli_error($mysql_con));
		setcookie("Plink_uid", "", time()-3600, '/');
		setcookie("Psps_sess", "", time()-3600, '/');
	}
}


/**********************************
 * Registration functions         *
 *                                *
 **********************************/

/**
 * Registers the given email and password as a new member.
 * Note: this function assumes that the given email is SQL-safe.
 * @param string $password the given password
 * @param string $email the given email
 * @return boolean whether the registration was successful
 */
function register($password, $email)
{
	global $mysql_con, $rand_salt, $substr_salt;
	date_default_timezone_set($timezone);
	$date = date("d/m/Y");
	$ip = $_SERVER["REMOTE_ADDR"];
	$type = 3;
	$salt = substr(str_shuffle($rand_salt), 0, $substr_salt);
	$password = generate_hash($password, $salt);
	$UserID = reset(mysqli_fetch_array(mysqli_query($mysql_con, "SELECT uid FROM members ORDER BY uid DESC LIMIT 1"))) + 1; // Sets new UID to next UID in sort order
	email_registration($email);
	return mysqli_query($mysql_con, "INSERT INTO members (date, IP, type, salt, password, uid, email) VALUES ('$date', '$ip', '$type', '$salt', '$password', '$UserID', '$email')");
}

/**
 * Returns whether the given email, password, and re-entered password are valid.
 * Note: this function assumes that the given email is SQL-safe.
 * @param string $password the given password
 * @param string $repassword the given re-entered password
 * @param string $email the given email
 */
function valid_registration($password, $repassword, $email) // TODO: accept other email domains
{
	global $mysql_con;
	$email_query = mysqli_query($mysql_con, "SELECT * FROM members WHERE email='$email'");
	return !(mysqli_num_rows($email_query) || !preg_match('#[a-zA-Z0-9_]+#i', $email) || strlen($password) < 7 || strlen($password) > 100 || $password != $repassword);
}

/**
 * Returns the registration error corresponding to the given problematic password or email.
 * Note: this function assumes that the given email is SQL-safe.
 * @param string $password the given password
 * @param string $repassword the given re-entered password
 * @param string $email the given email
 * @return string the error message
 */
function registration_error($password, $repassword, $email) // TODO: generalise
{
	global $mysql_con;
	$email_query = mysqli_query($mysql_con, "SELECT * FROM members WHERE email='$email'");
	$error = "There was a problem with your registration: <ul>";
	if (mysqli_num_rows($email_query))
		$error .= "<li>The email you entered has already been registered.</li>";
	if (!preg_match('#[a-zA-Z0-9]+#i', $email))
		$error .= "<li>The email you entered is invalid; the correct format is NetID@princeton.edu.</li>";
	if (strlen($password) < 7 || strlen($password) > 100)
		$error .= "<li>Your password must be between 7 and 100 characters long.</li>";
	if ($password != $repassword)
		$error .= "<li>The two entered passwords do not match.</li>";
	
	return $error."</ul>";
}

/**
 * Returns whether the given email is registered.
 * Note: this function assumes that the given email is SQL-safe.
 * @param string $email the given email
 */
function registered($email)
{
	global $mysql_con;
	$query = mysqli_query($mysql_con, "SELECT * FROM members WHERE email='$email'");
	if (mysqli_num_rows($query))
		return true;
}

/**
 * Sends a verification email for registration to the given email address.
 * Note: this function assumes that the given email is SQL-safe.
 * @param string $email the given email address
 */
function email_registration($email)
{
	global $mysql_con, $rand_gen, $substr_gen;
	$end = strpos($email, '@');
	$name = substr($email, 0, $end);
	$rand = substr(str_shuffle($rand_gen), 0, $substr_gen);
	mysqli_query($mysql_con, "INSERT INTO verification_codes (email, code) VALUES ('$email', '$rand')");
	$to      = $email;
	$subject = 'PSPS Portal registration confirmation';
	$message = "Hello $name,<br /><br />Thank you for signing up at the PSPS Portal, Princeton Society of Physics Students' online network and interstudent Q&A platform, open only to members of the Princeton University community. Its advantage is in having the ability to handle mentorship and mentoring requests from anyone who signs up. Every few weeks videos or other educational content containing physics-related topics will be posted, with the ability to discuss each of them, as well as to pose anonymous general questions about PSPS or the Physics Department to the officers. <br />Please hit the link below to confirm your e-mail and begin using the Portal!<br /><br />http://psps.mycpanel.princeton.edu/?code=".$rand.'&email='.$email."<br /><br />Thank you,<br />Pavel Shibayev '15<br />Physics Department<br />PSPS secretary/Portal developer<br /><br />If you'd like to unsubscribe, please go here: http://psps.mycpanel.princeton.edu/?action=unsubscribe&email=$email.";
	$headers = "From: The PSPS Portal Administrator <noreply@psps.mycpanel.princeton.edu>\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\n";
	$headers .= "X-Mailer: PHP/" . phpversion() . "\n";
	$headers .= "List-Unsubscribe: http://psps.mycpanel.princeton.edu/?action=unsubscribe&email=$email";
	mail($to, $subject, $message, $headers);
}

/**
 * Verifies that the given verification code corresponds to the given email address.
 * Note: this function assumes that the given verification code and email are SQL-safe.
 * @param string $code the given verification code
 * @param string $email the given email address
 */
function verify($code, $email)
{
	global $mysql_con;
	$query = mysqli_query($mysql_con, "SELECT * FROM verification_codes WHERE code='$code' AND email='$email'");
	if (mysqli_num_rows($query))
	{
		mysqli_query($mysql_con,"UPDATE members SET type=2 WHERE email='$email'");
		create_alert('Your account has been verified. Explore the PSPS Portal now!', 'success');
	}
	else
		create_alert("The verification code is invalid. Please try again.", 'info');
}

/** Returns whether the current user has been verified. */
function verified()
{
	global $mysql_con;
	$UserID = safe($_COOKIE["Plink_uid"], 'sql');
	if (is_numeric($UserID))
	{
		$query = mysqli_query($mysql_con, "SELECT * FROM members WHERE uid=$UserID AND type=2");
		return (mysqli_num_rows($query) == 1);
	}
}

/**
 * Registers the given uid for the PUPC of the given year, with given format, type, and aid options and note.
 * Note: this function assumes that the given email is SQL-safe.
 * @param int $uid the given uid
 * @param int $format the given format option (0 for on-site, 1 for online)
 * @param int $type the given type option (0 for team, 1 for individual)
 * @param int $aid the given aid option
 * @param string $note the given note
 * @param int $year the given year
 * @return boolean whether the registration was successful
 */
function register_PUPC($uid, $aid, $note, $year)
{
	global $mysql_con;
	date_default_timezone_set($timezone);
	$date = date("Ymd H:i:s");
	$status = mysqli_query($mysql_con, "INSERT INTO pupc_$year (date, uid, aid, notes) VALUES ('$date', $uid, $aid, '$note')");
	if ($status)
		email_PUPC_confirmation(get_email($uid), $year);
	return $status;
}

/**
 * Registers the given uids for the PUPC of the given year, with given format, type, and aid options and note.
 * Note: this function assumes that the given team is SQL-safe.
 * @param string $name the given team name
 * @param int array $uids the given uids array
 * @param int $year the given year
 * @return boolean whether the registration was successful
 */
function register_PUPC_team($name, $uids, $year)
{
	global $mysql_con;
	date_default_timezone_set($timezone);
	$date = date("Ymd H:i:s");
	while (count($uids) < 6)
		array_push($uids, 'NULL'); // pad out array TODO: use NULL instead
	$status = mysqli_query($mysql_con, "INSERT INTO pupc_online_$year (date, name, uid1, uid2, uid3, uid4, uid5, uid6) VALUES ('$date', '$name', $uids[0], $uids[1], $uids[2], $uids[3], $uids[4], $uids[5])");
//	if ($status)
//		for ($i = 0; $i < count($uids); $i++)
//			email_PUPC_team_confirmation(get_email($uid[i]), $year);
	return $status;
}

/**
 * Sends a confirmation email of PUPC registration of the given year to the given email address.
 * Note: this function assumes that the given email is SQL-safe.
 * @param string $email the given email address
 * @param string $year the given year
 */
function email_PUPC_confirmation($email, $year)
{
	global $mysql_con;
	$end = strpos($email, '@');
	$name = substr($email, 0, $end);
	$to      = $email;
	$subject = "PUPC $year registration confirmation";
	$message = "Hello $name,<br /><br />Thank you for registering for PUPC $year! This e-mail confirms that you have successfully registered. ";
//	$message .= "and your ID is 16<continent code><country ISO code><index from within country>.";
	$message .= "We look forward to your participation!<br /><br />Best wishes,<br />PUPC Organizers";
	$headers = "From: The PSPS Portal Administrator <noreply@psps.mycpanel.princeton.edu>\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\n";
	$headers .= "X-Mailer: PHP/" . phpversion() . "\n";
	$headers .= "List-Unsubscribe: http://psps.mycpanel.princeton.edu/?action=unsubscribe&email=$email";
	mail($to, $subject, $message, $headers);
}

/**
 * Sends a confirmation email of PUPC team registration of the given year to the given email address.
 * Note: this function assumes that the given email is SQL-safe.
 * @param string $email the given email address
 * @param string $year the given year
 */
function email_PUPC_team_confirmation($email, $year)
{
	global $mysql_con;
	$end = strpos($email, '@');
	$name = substr($email, 0, $end);
	$to      = $email;
	$subject = "PUPC $year registration confirmation";
	$message = "Hello $name,<br /><br />Thank you for registering for PUPC $year! This e-mail confirms that you have successfully registered. ";
//	$message .= "and your ID is 16<continent code><country ISO code><index from within country>.";
	$message .= "We look forward to your participation!<br /><br />Best wishes,<br />PUPC Organizers";
	$headers = "From: The PSPS Portal Administrator <noreply@psps.mycpanel.princeton.edu>\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\n";
	$headers .= "X-Mailer: PHP/" . phpversion() . "\n";
	$headers .= "List-Unsubscribe: http://psps.mycpanel.princeton.edu/?action=unsubscribe&email=$email";
	mail($to, $subject, $message, $headers);
}


/**********************************
 * Reset functions                *
 *                                *
 **********************************/

/**
 * Sends a password-reset email to the given email address.
 * @param string $email the given email address
 */
function email_reset($email)
{
	global $mysql_con, $rand_gen, $substr_gen;
	$end = strpos($email, '@');
	$name = substr($email, 0, $end);
	$rand = substr(str_shuffle($rand_gen), 0, $substr_gen);
	mysqli_query($mysql_con, "INSERT INTO reset_codes (email, code) VALUES ('$email', '$rand') ON DUPLICATE KEY UPDATE code='$rand'");
	$to      = $email;
	$subject = 'PSPS Portal password reset';
	$message = "Hello $name,<br /><br />To reset your password, please visit the following link: http://psps.mycpanel.princeton.edu/Reset.php?reset=true&code=$rand&email=$email.";
	$headers = 'From: The PSPS Portal Administrator <noreply@psps.mycpanel.princeton.edu>' . "\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "List-Unsubscribe: http://psps.mycpanel.princeton.edu/?action=unsubscribe&email=$email";
	mail($to, $subject, $message, $headers);
}

/**
 * Returns whether the given verification code is valid for password reset on the given email.
 * Note: this function assumes that the given verification code and email are SQL-safe.
 * @param string $code the given verification code
 * @param string $email the given email
 * @return boolean
 */
function verify_reset($code, $email) // TODO: Add time limit for resetting
{
	global $mysql_con, $rand_salt, $substr_salt;
	$code_query = mysqli_query($mysql_con, "SELECT * FROM reset_codes WHERE email='$email' AND code='$code'");
	if (!mysqli_num_rows($code_query))
		create_alert("That code is incorrect. Please try to reset your password again.", 'danger');
	else
		return true;
}

/**
 * Resets the password for the user registered under the given email
 * to the given new password.
 * Note: this function assumes that the given email is SQL-safe.
 * @param string $email the given email
 * @param string $new_password the given new password
 * @return string the status message for whether the password was reset successfully
 */
function reset_password($email, $new_password, $re_new_password) // TODO: Limit the number of code attempts
{
	global $mysql_con, $rand_salt, $substr_salt;
	if (valid_registration($new_password, $re_new_password, $email.'asdf'))
	{
		$salt = substr(str_shuffle($rand_salt), 0, $substr_salt);
		$password = generate_hash($password, $salt);
		mysqli_query($mysql_con, "UPDATE members SET password='$password' WHERE email='$email'");
		mysqli_query($mysql_con, "DELETE FROM reset_codes WHERE email='$email'");
		create_alert("Your password was successfully reset!", 'success');
	}
	else
		create_alert(registration_error($new_password, $re_new_password, $email.'asdf'), 'danger');
}


/**********************************
 * Question and answer functions  *
 *                                *
 **********************************/

/**
 * 
 * @param string $title
 * @param string $content
 */
function add_question($title, $content) // Note: don't worry about this
{
	global $mysql_con;
	$QuestionID_query = mysqli_fetch_array(mysqli_query($mysql_con, "SELECT qid FROM questions ORDER BY qid LIMIT 1"));
	$QuestionID = $QuestionID_query[0]+1;
	$author = $_COOKIE["Plink_uid"];
	mysqli_query($mysql_con, "INSERT INTO questions (title, content, qid, author_id) VALUES ('".safe($title, 'sql').", ".$content.", ".$QuestionID.", ".$author.")");
}

/**
 * 
 * @param string $content
 */
function add_answer($content)
{
	global $mysql_con;
	$PostID_query = mysqli_fetch_array(mysqli_query($mysql_con, "SELECT post_id FROM responses ORDER BY post_id DESC LIMIT 1"), MYSQLI_NUM);
	$PostID = $PostID_query[0]+1;
	$author = $_COOKIE["Plink_uid"];
	$question = mysqli_real_escape_string($mysql_con, $_GET["question"]);
	date_default_timezone_set($timezone);
	$date = date("d/m/Y");
	//Note that type 1 = The Connection, and type 2 = Debates
	mysqli_query($mysql_con, "INSERT INTO responses (type, content, post_id, author_id, question, date) VALUES (1, '$content', $PostID, '$author', '$question', '$date')");
}

/**
 * 
 * @param unknown $question
 */
function display_answers($question)
{
	global $mysql_con;
	$qid = get_qid($question);
	$ans_query = mysqli_query($mysql_con, "SELECT content, up_votes, down_votes FROM responses WHERE question='$question' OR post_id=$qid AND type=1 ORDER BY id DESC");
	if ($ans_query)
		while ($content = safe(reset(mysqli_fetch_array($ans_query, MYSQLI_NUM)), "html"))
			echo "<span class=\"list\">\n$content\n</span><hr />\n";
}

/**
 * 
 * @param string $message
 */
function display_replies($message)
{
	global $mysql_con;
	$qid = get_qid($question);
	$ans_query = mysqli_query($mysql_con, "SELECT content FROM messages WHERE parent_id=$message ORDER BY message_id DESC");
	if ($ans_query)
		while ($content = safe(reset(mysqli_fetch_array($ans_query, MYSQLI_NUM)), "html"))
			echo "<span class=\"list\">\n$content\n</span><hr />\n";
}

/** */
function display_questions()
{
	global $mysql_con;
	$Q_query = mysqli_query($mysql_con, "SELECT title FROM questions");
	while ($title = safe(reset(mysqli_fetch_array($Q_query, MYSQLI_NUM)), "html"))
		echo "<div class=\"media\">
								<div class=\"media-body\">
									 <header><h4 class=\"media-heading\"><a href=\"?question=$title#connection\">$title</a></h4></header>
								</div>
						  </div>";
}

/** */
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

/** */
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

/** */
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

/** */
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

/**
 * 
 * @param string $subject
 * @param string $date
 * @param string $quantity
 * @param string $type
 */
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

/**
 * 
 * @param unknown $question
 * @return unknown
 */
function get_question($question)
{
	global $mysql_con;
	$Q_query = mysqli_query($mysql_con, "SELECT title,content FROM questions WHERE title='$question'");
	$question = mysqli_fetch_array($Q_query, MYSQLI_NUM);
	$question[0] = safe($question[0], "html");
	$question[1] = safe($question[1], "html");
	return $question;
}

/**
 * 
 * @param string $id
 * @return unknown
 */
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

/**
 * 
 * @param unknown $question
 * @return unknown
 */
function get_qid($question)
{
	global $mysql_con;
	$Q_query = mysqli_query($mysql_con, "SELECT qid FROM questions WHERE title='$question'");
	$qid = mysqli_fetch_array($Q_query, MYSQLI_NUM);
	$qid = $qid[0];
	return $qid;
}

/**
 * 
 * @param string $title
 * @param string $description
 * @param string $video_id
 * @param string $bio
 */
function add_debate($title, $description, $video_id, $bio)
{
	global $mysql_con;
	$DebateID_query = mysqli_fetch_array(mysqli_query($mysql_con, "SELECT did FROM debates ORDER BY did LIMIT 1"));
	$DebateID = $DebateID_query[0]+1;
	$author = $_COOKIE["Plink_uid"];
	mysqli_query($mysql_con, "INSERT INTO debates (title, description, video_id, did, author_id, bio) VALUES ('$title', '$description', '$video_id', $DebateID, '$author', '$bio')") or die(mysqli_error($mysql_con));
}

/**
 * 
 * @param string $content
 * @param unknown $yn
 */
function add_response($content, $yn)
{
	global $mysql_con;
	$PostID_query = mysqli_fetch_array(mysqli_query($mysql_con, "SELECT post_id FROM responses WHERE type=2 ORDER BY post_id LIMIT 1"), MYSQLI_NUM);
	$PostID = $PostID_query[0]+1;
	$author = $_COOKIE["Plink_uid"];
	$debate = $_GET["debate"];
	date_default_timezone_set($timezone);
	$date = date("d/m/Y");
	//Note that type 1 = The Connection, type 2 = Debates
	mysqli_query($mysql_con, "INSERT INTO responses (type, content, post_id, author_id, question, date, yn) VALUES (2, '$content', $PostID, '$author', '$debate', '$date', $yn)");
}

/**
 * 
 * @param string $recipient
 * @param string $subject
 * @param string $message
 */
function send_message($recipient, $subject, $message)
{
	global $mysql_con;
	$MessageID_query = mysqli_fetch_array(mysqli_query($mysql_con, "SELECT message_id FROM messages ORDER BY message_id LIMIT 1"), MYSQLI_NUM);
	$MessageID = $MessageID_query[0]+1;
	$sender = $_COOKIE["Plink_uid"];
	date_default_timezone_set($timezone);
	$date = date("d/m/Y");
	mysqli_query($mysql_con, "INSERT INTO messages (message_id, recipient, sender, subject, content, date) VALUES ($MessageID, '$recipient', '$sender', '$subject', '$message', '$date')") or die(mysqli_error($mysql_con));
}

/**
 * 
 * @param unknown $parent
 * @param string $message
 */
function add_reply($parent, $message)
{
	global $mysql_con;
	$MessageID_query = mysqli_fetch_array(mysqli_query($mysql_con, "SELECT message_id FROM messages ORDER BY message_id LIMIT 1"), MYSQLI_NUM);
	$MessageID = $MessageID_query[0]+1;
	$sender = $_COOKIE["Plink_uid"];
	$parent = (int)$parent;
	$message = safe($message, "sql");
	date_default_timezone_set($timezone);
	$date = date("d/m/Y");
	mysqli_query($mysql_con, "INSERT INTO messages (message_id, sender, content, date, parent_id) VALUES ($MessageID, '$sender', '$message', '$date', '$parent')") or die(mysqli_error($mysql_con));
}

/**
 * 
 * @param unknown $debate
 */
function display_responses($debate)
{
	global $mysql_con;
	$ans_query = mysqli_query($mysql_con, "SELECT content, date, author_id, up_votes, down_votes, yn FROM responses WHERE question='$debate' AND type=2 ORDER BY post_id DESC");
	while ($content = mysqli_fetch_array($ans_query, MYSQLI_NUM))
	{
		$contents = safe($content[0], "html");
		date_default_timezone_set($timezone);
		$date = safe($content[1], "html");
		$uid = $content[2];
		$username = get_username($content[2]);

		$prnt = "<span class=\"agree\"><b>$username ($date):</b><br />\n$contents\n<hr />\n</span>";
		echo $prnt;
	}
}

/** */
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

/**
 * 
 * @param unknown $debate
 * @return unknown
 */
function get_debate($debate)
{
	global $mysql_con;
	$D_query = mysqli_query($mysql_con, "SELECT title,description,video_id,bio FROM debates WHERE title='$debate'");
	$debate = mysqli_fetch_array($D_query, MYSQLI_NUM);
	array_walk($debate, 'safe', "html");
	return $debate;
}

/**
 * 
 * @return boolean
 */
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


/**********************************
 * Utility functions              *
 *                                *
 **********************************/

/**
 * Returns a hash generated from the given password and salt
 * @param string $password the given password
 * @param string $salt the given salt
 * @return string the generated hash
 */
function generate_hash($password, $salt)
{
	global $mysql_con;
	$password = md5($password);
	$salt = md5($salt);
	$hash = md5($salt.$password.$salt);
	return $hash;
}

/**
 * Returns the salt for the given username.
 * @param string $username the given username
 */
function get_salt($username)
{
	global $mysql_con;
	$query = mysqli_query($mysql_con, "SELECT salt FROM members WHERE email='$username'") or die(mysqli_query($mysql_con));
	$salt = mysqli_fetch_array($query, MYSQL_NUM);
	return $salt[0];
}

/**
 * Returns the email corresponding to the given user ID.
 * @param string UserId the given netid
 */
function get_email($UserId)
{		 
	global $mysql_con;
	$UserId = (int)$UserId;
	$query = mysqli_query($mysql_con, "SELECT email FROM members WHERE uid=$UserId") or die(mysqli_query($mysql_con));
	$email_array = mysqli_fetch_array($query);
	return $email_array[0];
}

/**
 * Returns the "username" corresponding to the given user ID.
 * @param string UserId the given netid
 */
function get_username($UserId)
{		 
	$email = get_email($UserID);
	$end = strpos($email, '@');
	return substr($email, 0, $end);
}

/**
 * 
 * @param unknown $id
 * @return unknown
 */
function get_title($id)
{		 
	global $mysql_con;
	$id = $id;
	$query = mysqli_query($mysql_con, "SELECT title FROM questions WHERE qid=$id") or die(mysqli_query($mysql_con));
	$title_array = mysqli_fetch_array($query);
	$title = $title_array[0];
	return $title;
}

/**
 * Returns the user ID corresponding to the given email.
 * @param string $email the given email
 */
function get_id($email)
{		 
	global $mysql_con;
	$UserId = $email;
	$query = mysqli_query($mysql_con, "SELECT uid FROM members WHERE email='$UserId'") or die(mysqli_query($mysql_con));
	$username_array = mysqli_fetch_array($query);
	$username = $username_array[0];
	return $username;
}

/**
 * Converts the given input string to a safe string by escaping all relevant characters of the given language (SQL or HTML).
 * @param string $input the given input string
 * @param string $method the given language
 * @return string the converted safe string
 */
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

/** */
function insert_stats()
{
	global $mysql_con;
	date_default_timezone_set($timezone);
	$date = date("d/m/Y");
	$ip = $_SERVER["REMOTE_ADDR"]; 
	$ua = $_SERVER["HTTP_USER_AGENT"];
	$ref = $_SERVER["HTTP_REFERER"];
	$uri = $_SERVER["REQUEST_URI"];
	//email_registration($email);
	mysqli_query($mysql_con, "INSERT INTO statistics (date, IP, ) VALUES ('$date', '$ip', '$type', '$salt', '$password', '$UserID', '$email')");
	mysqli_query($mysql_con, "INSERT INTO statistics ()");
}

/**
 * Flashes the specified message with the given alert type.
 * @param string message the message type
 * @param string type the alert type
 * @return string the alert type
 */
function create_alert($message, $type)
{
	global $renderer;
	array_push($renderer->flashes, array($type, $message));
	return $type;
}
?>
