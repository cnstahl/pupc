<?php
require_once 'TemplateRenderer.php';
$renderer = new TemplateRenderer();

if ($_GET["code"] && $_GET["email"])
{
	$code = safe($_GET["code"], 'sql');
	$email = safe($_GET["email"], 'sql');
	verify($code, $email);
}

/*
if (!logged_in())
	print $renderer->render('index.php.twig', array());
else if (!verified())
	header("Location: Sorry.php");
*/
if ($_GET["logout"])
	logout();
print $renderer->render('index.php.twig', array());

?>
