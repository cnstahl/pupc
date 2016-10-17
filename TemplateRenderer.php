<?php
include("./PL_includes/functions.php");
require_once 'Twig/Autoloader.php';
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
			array($_SERVER['DOCUMENT_ROOT'].'/templates'), // Base directory with all templates
			$templateDirs
		);
		$this->loader = new Twig_Loader_Filesystem($templateDirs);
		$this->environment = new Twig_Environment($this->loader, $envOptions);
		$_SESSION['load_flashes'] = true;
		if (logged_in() && !verified()) {
			create_alert("Please verify your account! Contact a system administrator if you cannot find your verification email.", 'danger');
		}
		if ($_GET["logout"]) {
			logout();
			create_alert("Logged out successfully.", 'info');
			$this->redirect();
		}
	}
	public function redirect($page = NULL) // Redirect to page with no GET arguments, or given page
	{
		if ($page == NULL)
			if (count($_GET) > 0)
				$page = substr(strstr($_SERVER['REQUEST_URI'], '?', TRUE), 1);
			else
				return;
		$_SESSION['load_flashes'] = false;
		header("Location: ../" . $page);
	}
	public function render($templateFile, array $variables = array())
	{
		if ($_SESSION['load_flashes']) {
			$variables['flashes'] = $_SESSION['flashes'];
			$_SESSION['flashes'] = array();
		}
		$variables['page'] = $_SERVER['REQUEST_URI'];
		$variables['logged_in'] = logged_in();
		if ($variables['logged_in'])
		{
			$UserID = get_uid();
			$variables['user_email'] = get_email($UserID);
			$variables['user_name'] = get_name($UserID);
		}
		return $this->environment->render($templateFile, $variables);
	}
}
