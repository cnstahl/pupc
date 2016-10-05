<?php
require_once 'TemplateRenderer.php';
$renderer = new TemplateRenderer();

print $renderer->render('Contact.html.twig', array());
?>
