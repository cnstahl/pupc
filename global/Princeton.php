<?php
require_once 'TemplateRenderer.php';
$renderer = new TemplateRenderer();

print $renderer->render('Princeton.html.twig', array());
?>
