<?php
require_once 'functions.php';

// Set login variables
$login_submit = $_POST["submit"];
$login_name = safe($_POST["email"], "sql");
$login_pass = $_POST["password"];

// Verify that the login was submitted and that the username & password are correct
if ($login_submit && valid_login($login_name, $login_pass) && login($login_name, $login_pass))
	create_alert("Logged in successfully.", 'success');
else
	create_alert("Invalid e-mail/password combination! <a href=\"Reset.php\">Forgot your password?</a>", 'danger');
header("Location: .." . $_POST["page"]);
?>
