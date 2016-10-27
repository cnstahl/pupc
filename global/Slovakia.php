<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/TemplateRenderer.php';
$renderer = new TemplateRenderer();

print $renderer->render('Slovakia.html.twig', array());
?>
