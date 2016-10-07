<?php
	include("./functions.php");
	
	// Set login variables
	$login_submit = $_POST["submit"];
	$login_name = safe($_POST["email"], "sql");
	$login_pass = $_POST["password"];

	// Verify that the login was submitted and that the username & password are correct
	if ($login_submit && valid_login($login_name, $login_pass) && login($login_name, $login_pass))
	{
//		create_alert("Logged in successfully.", 'success');
		header("Location: .." . $_POST["page"]);
	}
	else
		echo $_POST["page"];
		echo "Invalid login credentials.";
//		create_alert("Invalid e-mail/password combination!", 'danger');
?>
