<?php
require_once 'TemplateRenderer.php';

$renderer = new TemplateRenderer();
print $renderer->render('About.php.twig', array());
?>
