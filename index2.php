<?php
require_once '../php/Twig/Autoloader.php';
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader);

$template = $twig->loadTemplate('layout.php');

echo $twig->render('layout.php', array(
	'title' => 'Test'
));
?>
