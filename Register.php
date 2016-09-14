<?php
require_once 'TemplateRenderer.php';
$renderer = new TemplateRenderer();

// Set registration variables
$submit = $_POST["submit"];
$password = $_POST["password"];
$repassword = $_POST["repassword"];
$email = safe($_POST["email"], "sql");
$username = safe($_POST["username"], "sql");

// Verify that the registration form was submitted and that the email & password are correct
if ($submit)
	if (valid_registration($password, $repassword, $email) && register($password, $email))
		$register_result = create_alert("Registered successfully! Verification email sent to $email.", "info");
	else
		$register_result = create_alert(registration_error($password, $repassword, $email), "danger");

// Load page
if ($register_result == "success")
	header('Location: index2.php');
else
	print $renderer->render('Register.php.twig', array());
?>
