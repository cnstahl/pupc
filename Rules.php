<?php
require_once 'TemplateRenderer.php';
$renderer = new TemplateRenderer();

print $renderer->render('Rules.html.twig', array());
?>
