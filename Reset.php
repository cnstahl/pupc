<?php
require_once 'TemplateRenderer.php';
$renderer = new TemplateRenderer();

// Set reset variables
$submit = $_POST["submit"];
$email = safe($_POST["email"], "sql");
$code = safe($_POST["code"], "sql");
$password = safe($_POST["password"], "sql");
$repassword = safe($_POST["repassword"], "sql");
$reset = false;

if ($_GET['reset'] == 'true') { // Verify code is correct: if it is, password blanks will be rendered
	$email = safe($_GET['email'], 'sql');
	$code = safe($_GET['code'], 'sql');
	if (verify_reset($code, $email))
	{
		$reset = true;
		if ($submit) // New password submitted
			if (reset_password($email, $password, $repassword)) // Password reset successful
				$renderer->redirect();
	}
}
else if ($submit) // POST request submitted
{
	if ($code == '') // Initiating recovery process
		// Verify that the reset form was submitted and that the email is registered
		if ($submit && registered($email)) 
		{
			email_reset($email);
			create_alert("An email has been sent with a link that will allow you to reset your password.", 'info');
		}
		else
			create_alert($reset_result = "Your email was not found in the database.", 'danger');
	else if (verify_reset($code, $email)) // Submitting new password; TODO: check if control ever reaches here
		if (reset_password($email, $password, $repassword)) // Password reset successful
			$renderer->redirect();
}

print $renderer->render('Reset.html.twig', array(
	'reset' => $reset,
	'code' => $code,
	'email' => $email	
));
?>
