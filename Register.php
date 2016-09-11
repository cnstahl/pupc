<?php
// Include prerequisite file
include("./PL_includes/functions.php");

// Set registration variables
$submit = $_POST["submit"];
$password = $_POST["password"];
$repassword = $_POST["repassword"];
$email = safe($_POST["email"], "sql")."@princeton.edu";
$username = safe($_POST["username"], "sql");

require_once 'TemplateRenderer.php';
$renderer = new TemplateRenderer();

// Verify that the registration form was submitted and that the email & password are correct
if ($submit)
	if (valid_registration($password, $repassword, $email) && register($password, $email))
		$register_result = create_alert("Registered successfully!", "success");
	else
		$register_result = create_alert(registration_error($password, $repassword, $email), "danger");

// Load page
if ($register_result == "success")
	header('Location: index2.php');
else
	print $renderer->render('Register.php.twig', array(
		'submit' => $submit
	));
?>
