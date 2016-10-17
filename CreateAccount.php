<?php
require_once 'TemplateRenderer.php';
$renderer = new TemplateRenderer();

// Set registration variables
$submit = $_POST["submit"];

$password = $_POST["password"];
$repassword = $_POST["repassword"];
$email = $_POST["email"];
$name = $_POST["name"];

if ($submit) { // Submitted account registration
	$email = safe($email, "sql");
	$name = safe($name, "sql");
	
	// Verify that the registration form was submitted and that the email & password are correct
	if (valid_registration($password, $repassword, $email) && register($password, $email, $name))
		$register_result = create_alert("Account created successfully! Verification email sent to $email.", "info");
	else
		$register_result = create_alert(registration_error($password, $repassword, $email), "danger");
	
	// Load page
	if ($register_result == "success")
		$renderer->redirect('index.php');
}

print $renderer->render('MakeAccount.html.twig');
?>
