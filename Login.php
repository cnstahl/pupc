<?php
require_once 'TemplateRenderer.php';
$renderer = new TemplateRenderer();

if (!logged_in())
	print $renderer->render('Login.html.twig', array());
else {
	$referrer = $_SERVER['HTTP_REFERER'];
	$referrer = substr($referrer, strrpos($referrer, '/'));
	if ($referrer != '/Login.php')
		create_alert("You are already logged in.", 'warning');
	header("Location: ..");
}
?>
