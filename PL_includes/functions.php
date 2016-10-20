<?php
/* This file contains the the MySQL connection and definitions for all of the custom functions used in PUPC Portal:
 * login(), register(), valid_login(), valid_registration(), registration_error(), add_question(), 
 * display_questions(), add_debate(), add_response(), display_debates(), display_responses()
 */

session_start();

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
 * Registers the given email and password as a new member, of given name and role.
 * Note: this function assumes that the given email, name, and role are SQL-safe.
 * @param string $password the given password
 * @param string $email the given email
 * @param string $name the given forename
 * @param string $surname the given surname
 * @param string $name the given role
 * @return boolean whether the registration was successful
 */
function register($password, $email, $name, $surname, $role)
{
	global $mysql_con, $rand_salt, $substr_salt;
	date_default_timezone_set($timezone);
	$date = date("d/m/Y");
	$ip = $_SERVER["REMOTE_ADDR"];
	$type = 3;
	$salt = substr(str_shuffle($rand_salt), 0, $substr_salt);
	$password = generate_hash($password, $salt);
	$UserID = reset(mysqli_fetch_array(mysqli_query($mysql_con, "SELECT uid FROM members ORDER BY uid DESC LIMIT 1"))) + 1; // Sets new UID to next UID in sort order
	return mysqli_query($mysql_con, "INSERT INTO members (date, IP, type, salt, password, uid, email) VALUES ('$date', '$ip', '$type', '$salt', '$password', '$UserID', '$email')")
		&& mysqli_query($mysql_con, "INSERT INTO profiles (uid, name, surname, role) VALUES ($UserID, '$name', '$surname', '$role')")
		&& email_registration($email, $name);
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
function registration_error($password, $repassword, $email) // TODO: do email validation
{
	global $mysql_con;
	$email_query = mysqli_query($mysql_con, "SELECT * FROM members WHERE email='$email'");
	$error = "There was a problem with your registration: <ul>";
	if (mysqli_num_rows($email_query))
		$error .= "<li>An account with this email already exists. <a href=\"Reset.php\">Forgot your password</a>?</li>";
//	if (!preg_match('#[a-zA-Z0-9]+#i', $email))
//		$error .= "<li>The email you entered is invalid.</li>";
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
function email_registration($email, $name)
{
	global $mysql_con, $rand_gen, $substr_gen;
	$rand = substr(str_shuffle($rand_gen), 0, $substr_gen);
	if (mysqli_query($mysql_con, "INSERT INTO verification_codes (email, code) VALUES ('$email', '$rand')")) {
		$to      = $email;
		$subject = 'PUPC account confirmation';
		$message = "Hello $name,<br /><br />Thank you for your interest in the Princeton University Physics Competition.<br />Please hit the link below to confirm your e-mail and begin registration!<br /><br />http://pupc.princeton.edu/?code=".$rand.'&email='.$email."<br /><br />Thank you,<br />PUPC Organizers<br /><br />If you'd like to unsubscribe, please go here: http://pupc.princeton.edu/?action=unsubscribe&email=$email.";
		$headers = "From: PUPC Administrator <noreply@pupc.princeton.edu>\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\n";
		$headers .= "X-Mailer: PHP/" . phpversion() . "\n";
		$headers .= "List-Unsubscribe: http://pupc.princeton.edu/?action=unsubscribe&email=$email";
		return mail($to, $subject, $message, $headers);
	}
	return false;
}

/**
 * Verifies that the given verification code corresponds to the given email address.
 * Note: this function assumes that the given verification code and email are SQL-safe.
 * @param string $code the given verification code
 * @param string $email the given email address
 * @return bool whether the verification was successful
 */
function verify($code, $email)
{
	global $mysql_con;
	$email = str_replace(' ', '+', $email);
	echo $email;
	$query = mysqli_query($mysql_con, "SELECT * FROM verification_codes WHERE code='$code' AND email='$email'");
	if (mysqli_num_rows($query))
	{
		mysqli_query($mysql_con, "UPDATE members SET type=2 WHERE email='$email'");
		mysqli_query($mysql_con, "DELETE FROM verification_codes where email='$email'");
		create_alert("Your account has been verified.", 'success');
		return true;
	}
	else
	{
		create_alert("The verification code is invalid. Please try again.", 'info');
		return false;
	}
}

/** Returns whether the current user has been verified. */
function verified()
{
	global $mysql_con;
	
	$UserID = get_uid();
	if (is_numeric($UserID))
	{
		$query = mysqli_query($mysql_con, "SELECT * FROM members WHERE uid=$UserID AND type=2");
		return (mysqli_num_rows($query) == 1);
	}
}

/**
 * Registers the given uid for the PUPC of the given year, with given testing site and aid options, and note.
 * Note: this function assumes that the given email is SQL-safe.
 * @param int $uid the given uid
 * @param int $site the given testing site
 * @param int $aid the given aid option
 * @param string $note the given note
 * @param int $year the given year
 * @return boolean whether the registration was successful
 */
function register_PUPC($uid, $site, $aid, $note, $year)
{
	global $mysql_con;
	
	date_default_timezone_set($timezone);
	$date = date("Y-m-d H:i:s");
	if (!verified())
		return 0;
	$status = mysqli_query($mysql_con, "INSERT INTO pupc_$year (date, uid, site, aid, notes) VALUES ('$date', $uid, '$site', $aid, '$note')");
	if ($status)
		email_PUPC_confirmation(get_email($uid), $site, $year);
	return $status;
}

/**
 * Registers the given uids for the PUPC online test of the given year, with given team name and members.
 * Note: this function assumes that the given team name is SQL-safe.
 * @param string $name the given team name
 * @param int array $uids the given uids array of members
 * @param int $year the given year
 * @return boolean whether the registration was successful
 */
function register_PUPC_team($name, $uids, $year)
{
	global $mysql_con;
	
	date_default_timezone_set($timezone);
	$date = date("Y-m-d H:i:s");
	$name = trim($name);
	if (count($name) == 0) {
		create_alert("Team names must be non-empty.", 'danger');
		return false;
	}
	$uids = array_unique($uids);
	if (count($uids) < 1) {
		create_alert("Teams must have at least one member.", 'danger');
		return false;
	}
	foreach ($uids as $uid)
		for ($i = 1; $i <= 6; $i++)
			if (mysqli_num_rows(mysqli_query($mysql_con, "SELECT * FROM pupc_online_$year WHERE uid$i=$uid")) > 0) {
				create_alert("One or more of your team members is registered with another team.", 'danger');
				return false;
			}
	while (count($uids) < 6)
		array_push($uids, 'NULL'); // pad out array
	$status = mysqli_query($mysql_con, "INSERT INTO pupc_online_$year (date, name, uid1, uid2, uid3, uid4, uid5, uid6) VALUES ('$date', '$name', $uids[0], $uids[1], $uids[2], $uids[3], $uids[4], $uids[5])");
	if ($status) {
		$names = array();
		foreach ($uids as $uid)
			if ($uid !== 'NULL')
				array_push($names, get_name($uid) . " " . get_surname($uid));
		foreach ($uids as $uid)
			if ($uid !== 'NULL')
				email_PUPC_team_confirmation(get_email($uid), $name, $names, $year);
	}
	else
		create_alert("There was a problem with your registration. Your team name may already be taken.");
	return $status;
}

/**
 * Sends a confirmation email of PUPC registration of the given year to the given email address.
 * Note: this function assumes that the given email is SQL-safe.
 * @param string $email the given email address
 * @param string $year the given year
 */
function email_PUPC_confirmation($email, $site, $year)
{
	$name = get_name(get_id($email));
	$to      = $email;
	$subject = "PUPC $year registration confirmation: Onsite Exam";
	$message = "Hello $name,<br /><br />Thank you for registering for PUPC $year onsite test in $site! This e-mail confirms that you have successfully registered. ";
//	$message .= "and your ID is 16<continent code><country ISO code><index from within country>.";
	$message .= "We look forward to your participation!<br /><br />Best wishes,<br />PUPC Organizers";
	$headers = "From: PUPC Administrator <noreply@pupc.princeton.edu>\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\n";
	$headers .= "X-Mailer: PHP/" . phpversion() . "\n";
	$headers .= "List-Unsubscribe: http://pupc.princeton.edu/?action=unsubscribe&email=$email";
	mail($to, $subject, $message, $headers);
}

/**
 * Sends a confirmation email of PUPC team registration of the given year
 * to the given email address for the given team name and member names.
 * Note: this function assumes that the given email is SQL-safe.
 * @param string $email the given email address
 * @param string $names the given names
 * @param string $team_name the given team name
 * @param string $year the given year
 */
function email_PUPC_team_confirmation($email, $team_name, $names, $year)
{
	$name = get_name(get_id($email));
	$to      = $email;
	$subject = "PUPC $year registration confirmation: Online Part";
	$message = "Hello $name,<br /><br />Thank you for registering for the PUPC $year online part! This e-mail confirms that your team, $team_name, has successfully been registered with the following members: ";
	for ($i = 0; $i < count($names)-1; $i++)
		$message .= "(" . ($i+1) . ") " . $names[$i] . ", ";
	$message .= "(" . ($i+1) . ") " . $names[$i] . ". ";
//	$message .= "and your ID is 16<continent code><country ISO code><index from within country>.";
	$message .= "We look forward to your participation!<br /><br />Best wishes,<br />PUPC Organizers";
	$headers = "From: PUPC Administrator <noreply@pupc.princeton.edu>\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\n";
	$headers .= "X-Mailer: PHP/" . phpversion() . "\n";
	$headers .= "List-Unsubscribe: http://pupc.princeton.edu/?action=unsubscribe&email=$email";
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
	$subject = 'PUPC Portal password reset';
	$message = "Hello $name,<br /><br />To reset your password, please visit the following link: http://pupc.princeton.edu/Reset.php?reset=true&code=$rand&email=$email.";
	$headers = 'From: The PUPC Portal Administrator <noreply@pupc.princeton.edu>' . "\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "List-Unsubscribe: http://pupc.princeton.edu/?action=unsubscribe&email=$email";
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
 * @return boolean whether the password was reset successfully
 */
function reset_password($email, $new_password, $re_new_password) // TODO: Limit the number of code attempts
{
	global $mysql_con, $rand_salt, $substr_salt;
	if (valid_registration($new_password, $re_new_password, $email.'asdf'))
	{
		$salt = substr(str_shuffle($rand_salt), 0, $substr_salt);
		$password = generate_hash($new_password, $salt);
		mysqli_query($mysql_con, "UPDATE members SET password='$password', salt='$salt' WHERE email='$email'");
		mysqli_query($mysql_con, "DELETE FROM reset_codes WHERE email='$email'");
		create_alert("Your password was successfully reset!", 'success');
		return true;
	}
	else
		create_alert(registration_error($new_password, $re_new_password, $email.'asdf'), 'danger');
	return false;
}


/**********************************
 * Profile functions              *
 *                                *
 **********************************/

/**
 * Updates the user profile with the given name, surname, grade, school, city, state, country, coach, and code.
 * Note: This function assumes all passed values are SQL-safe.
 * @param string $name the given name
 * @param string $surname the given surname
 * @param string $grade the given grade
 * @param string $school the given school
 * @param string $city the given city
 * @param string $state the given state
 * @param string $country the given country
 * @param string $coach the given coach
 * @param string $hash the given code
 */
function update_profile($name, $surname, $grade, $school, $city, $state, $country, $coach, $hash)
{
	global $mysql_con;

	$UserId = get_uid();
	$success = true;
	$success &= mysqli_query($mysql_con, "UPDATE profiles SET name='$name' WHERE uid=$UserId");
	$success &= mysqli_query($mysql_con, "UPDATE profiles SET surname='$surname' WHERE uid=$UserId");
	$success &= mysqli_query($mysql_con, "UPDATE profiles SET grade='$grade' WHERE uid=$UserId");
	$success &= mysqli_query($mysql_con, "UPDATE profiles SET school='$school' WHERE uid=$UserId");
	$success &= mysqli_query($mysql_con, "UPDATE profiles SET city='$city' WHERE uid=$UserId");
	$success &= mysqli_query($mysql_con, "UPDATE profiles SET state='$state' WHERE uid=$UserId");
	$success &= mysqli_query($mysql_con, "UPDATE profiles SET country='$country' WHERE uid=$UserId");
	$success &= mysqli_query($mysql_con, "UPDATE profiles SET coach='$coach' WHERE uid=$UserId");
	$success &= mysqli_query($mysql_con, "UPDATE profiles SET hash='$hash' WHERE uid=$UserId");
	return $success;
}

/**
 *
 */
function load_profile()
{
	global $mysql_con;
	
	$UserId = get_uid();
	$query = mysqli_query($mysql_con, "SELECT * FROM profiles WHERE uid=$UserId");
	return mysqli_fetch_array($query);
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
 * Returns the user ID of the currently logged in user, or 0 if none.
 */
function get_uid()
{
	if (logged_in())
		return safe($_COOKIE["Plink_uid"], 'sql');
	return 0;
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
 * Returns the name corresponding to the given user ID.
 * @param string UserId the given netid
 */
function get_name($UserId)
{		 
	global $mysql_con;
	$UserId = (int)$UserId;
	$query = mysqli_query($mysql_con, "SELECT name FROM profiles WHERE uid=$UserId") or die(mysqli_query($mysql_con));
	$email_array = mysqli_fetch_array($query);
	return $email_array[0];
}

/**
 * Returns the surname corresponding to the given user ID.
 * @param string UserId the given netid
 */
function get_surname($UserId)
{		 
	global $mysql_con;
	$UserId = (int)$UserId;
	$query = mysqli_query($mysql_con, "SELECT surname FROM profiles WHERE uid=$UserId") or die(mysqli_query($mysql_con));
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

function is_organizer()
{
	global $mysql_con;
	$UserId = get_uid();
	$query = mysqli_query($mysql_con, "SELECT * FROM profiles WHERE uid='$UserId' AND hash='4H9mMSCaJK'") or die(mysqli_query($mysql_con));
	return mysqli_num_rows($query) == 1;
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
	if (!$_SESSION['load_flashes'])
		return;
	if (!isset($_SESSION['flashes']))
		$_SESSION['flashes'] = array();
	array_push($_SESSION['flashes'], array($type, $message));
	return $type;
}
?>
