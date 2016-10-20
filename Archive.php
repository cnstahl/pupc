<?php
require_once 'TemplateRenderer.php';
$renderer = new TemplateRenderer();

print $renderer->render('Archive.html.twig', array());
?>
