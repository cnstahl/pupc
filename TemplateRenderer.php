<?php
include("./PL_includes/functions.php");
require_once 'Twig/Autoloader.php';
Twig_Autoloader::register();

class TemplateRenderer
{
	public $loader; // Instance of Twig_Loader_Filesystem
	public $environment; // Instance of Twig_Environment
	public $flashes;

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
			array($_SERVER['DOCUMENT_ROOT'].'/templates'), // Base directory with all templates
			$templateDirs
		);
		$this->loader = new Twig_Loader_Filesystem($templateDirs);
		$this->environment = new Twig_Environment($this->loader, $envOptions);
		$this->flashes = array();
	}

	public function render($templateFile, array $variables)
	{
		$variables['flashes'] = $this->flashes;
		$variables['logged_in'] = logged_in();
		if ($variables['logged_in'])
		{
			$UserID = safe($_COOKIE["Plink_uid"], 'sql');
			$variables['user_email'] = get_email($UserID);
		}
		return $this->environment->render($templateFile, $variables);
	}
}
