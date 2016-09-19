<?php
require_once 'TemplateRenderer.php';
$renderer = new TemplateRenderer();

print $renderer->render('SchedulePUPC.html.twig', array());
?>
