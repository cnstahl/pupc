<?php
require_once 'TemplateRenderer.php';

$renderer = new TemplateRenderer();
print $renderer->render('index.php.twig', array(
	'title' => 'Portal'
));
?>
