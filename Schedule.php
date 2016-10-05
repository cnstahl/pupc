<?php
require_once 'TemplateRenderer.php';
$renderer = new TemplateRenderer();

print $renderer->render('Schedule.html.twig', array());
?>
