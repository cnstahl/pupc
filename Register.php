<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/TemplateRenderer.php';
$renderer = new TemplateRenderer();

// PUPC year
$year = 2016; // Be sure to make the database table before updating this value

// Set registration variables
$submit = $_POST["submit"];

$password = $_POST["password"];
$repassword = $_POST["repassword"];
$email = $_POST["email"];
$username = $_POST["username"];

$format = $_POST["format"];
$site = $_POST["site"];
$aid = $_POST["aid"];
$note = $_POST["note"];
$name = $_POST["name"];
$emails = array();
array_push($emails, $_POST["email1"]);
array_push($emails, $_POST["email2"]);
array_push($emails, $_POST["email3"]);
array_push($emails, $_POST["email4"]);
array_push($emails, $_POST["email5"]);
array_push($emails, $_POST["email6"]);

if ($password) { // Submitted account registration
	$email = safe($email, "sql");
	$username = safe($username, "sql");
	
	// Verify that the registration form was submitted and that the email & password are correct
	if (valid_registration($password, $repassword, $email) && register($password, $email))
		$register_result = create_alert("Registered successfully! Verification email sent to $email.", "info");
	else
		$register_result = create_alert(registration_error($password, $repassword, $email), "danger");
	
	// Load page
	if ($register_result == "success")
		header('Location: index.php');
	else
		print $renderer->render('MakeAccount.html.twig', array());
}
else {
	// Must be authenticated to register for PUPC
	if (!logged_in()) {
		create_alert("Please log in before registering.", "warning");
		print $renderer->render('Login.html.twig', array());
	}
	else if (!verified()) {
		print $renderer->render('Blank.html.twig');
	}
	else {
		$uid = safe($_COOKIE["Plink_uid"], 'sql');
		// Verify that the registration form was submitted and that the email & password are correct
		if ($submit) {
			$uids = array();
			for ($i = 0; $i < count($emails); $i++) {
				$email = trim(safe($emails[$i], 'sql'));
				if (strlen($email) > 0)
					array_push($uids, get_id($email));
			}
			if ($format == "true")
				if (register_PUPC_team($name, $uids, $year))
					create_alert("Registered successfully!", "success");
				else
					create_alert("There was a problem with your registration. Your team name may already be registered.", "danger");
			else
				if (register_PUPC($uid, $site, $aid, $note, $year))
					create_alert("Registered successfully!", "success");
				else
					create_alert("There was a problem with your registration. You may already be registered.", "danger");
		}
			
		// Load page
		print $renderer->render('Register.html.twig', array());
	}
}
?>
