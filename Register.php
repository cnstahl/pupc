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
$type = $_POST["type"];
$aid = $_POST["aid"];
$note = $_POST["note"];

if ($password) {
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
		print $renderer->render('MakeAccount.php.twig', array());
}
else {
	// Must be authenticated to register for PUPC
	if (!logged_in() || !verified()) {
		create_alert("Please create an account or log in before registering.", "warning");
		print $renderer->render('MakeAccount.html.twig', array());
	}
	else {
		$uid = safe($_COOKIE["Plink_uid"], 'sql');
		// Verify that the registration form was submitted and that the email & password are correct
		if ($submit)
			if (register_PUPC($uid, $format, $type, $aid, $note, $year))
				create_alert("Registered successfully!", "success");
			else
				create_alert("There was a problem with your registration. You may already be registered.", "danger");
			
		// Load page
		print $renderer->render('Register.html.twig', array());
	}
}
?>