<?php
// Include prerequisite file
include("./PL_includes/functions.php");

// Set registration variables
$submit = $_POST["submit"];
$pass = $_POST["pass"];
$email = safe($_POST["email"], "sql");
$usernm = safe($_POST["usernm"], "sql");

// Verify that the registration form was submitted and that the email & password are correct 
if ($submit && valid_registration($pass, $email))
{
	register($pass, $email);
	$register_result = "Registered successfully!";
}
else
{
	$register_result = registration_error($pass, $email);
}

// Load page
require_once 'TemplateRenderer.php';

$renderer = new TemplateRenderer();
print $renderer->render('Register.php.twig', array(
	'submit' => $submit,
	'register_result' => $register_result
));
?>
