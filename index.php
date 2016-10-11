<?php
require_once 'TemplateRenderer.php';
$renderer = new TemplateRenderer();

if ($_GET["code"] && $_GET["email"])
{
	$code = safe($_GET["code"], 'sql');
	$email = safe($_GET["email"], 'sql');
	verify($code, $email);
}

print $renderer->render('index.html.twig', array());
?>
