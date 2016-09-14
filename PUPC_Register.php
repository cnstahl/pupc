<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/TemplateRenderer.php';
$renderer = new TemplateRenderer();

// PUPC year
$year = 2016; // Be sure to make the database table before updating this value

// Set registration variables
$submit = $_POST["submit"];
$aid = $_POST["aid"];
$note = $_POST["note"];

// Must be authenticated to register
if (!logged_in() || !verified())
	print $renderer->render('Sorry.html.twig', array());
else
{
	$uid = safe($_COOKIE["Plink_uid"], 'sql');
	// Verify that the registration form was submitted and that the email & password are correct
	if ($submit)
		if (register_PUPC($uid, $aid, $note, $year))
			create_alert("Registered successfully!", "success");
		else
			create_alert("There was a problem with your registration. You may already be registered.", "danger");
		
	// Load page
	print $renderer->render('RegisterPUPC.php.twig', array());
}
?>
