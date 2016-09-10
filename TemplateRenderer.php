<?php
require_once '../php/Twig/Autoloader.php';
Twig_Autoloader::register();

class TemplateRenderer
{
	public $loader; // Instance of Twig_Loader_Filesystem
	public $environment; // Instance of Twig_Environment

	public function __construct($envOptions = array(), $templateDirs = array())
	{
		// Merge default options
		// You may want to change these settings
		$envOptions += array(
			'debug' => false,
			'charset' => 'utf-8',
//			'cache' => './cache', // Store cached files under cache directory
			'strict_variables' => true,
		);
		$templateDirs = array_merge(
			array('./templates'), // Base directory with all templates
			$templateDirs
		);
		$this->loader = new Twig_Loader_Filesystem($templateDirs);
		$this->environment = new Twig_Environment($this->loader, $envOptions);
	}

	public function render($templateFile, array $variables)
	{
		return $this->environment->render($templateFile, $variables);
	}
}
