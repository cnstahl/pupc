<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/TemplateRenderer.php';
$renderer = new TemplateRenderer();

// PUPC year
$year = 2016; // Be sure to make the database table before updating this value

// Set registration variables
$submit = $_POST["submit"];

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

// Must be authenticated to register for PUPC
if (!logged_in()) {
	create_alert("Please log in to your account to register for the competition.", 'warning');
	print $renderer->render('Login.html.twig');
}
else if (!verified()) {
	print $renderer->render('Blank.html.twig');
}
else {
	$uid = safe($_COOKIE["Plink_uid"], 'sql');
	// Verify that the registration form was submitted and that the email & password are correct
	if ($submit) {
		$success = true;
		$uids = array();
		for ($i = 0; $i < count($emails); $i++) {
			$email = trim(safe($emails[$i], 'sql'));
			if (strlen($email) == 0)
				continue;
			$uid = get_id($email);
			if ($uid == NULL) {
				create_alert("User $email does not have an account.", 'danger');
				$success = false;
			}
			array_push($uids, $uid);
		}
		if ($success)
			if ($format == "true")
				if (register_PUPC_team($name, $uids, $year))
					create_alert("Registered successfully!", 'success');
				else
					;
			else
				if (register_PUPC($uid, $site, $aid, $note, $year))
					create_alert("Registered successfully!", 'success');
				else
					create_alert("There was a problem with your registration. You may already be registered.", 'danger');
	}
		
	// Load page
	print $renderer->render('Register.html.twig');
}
?>
