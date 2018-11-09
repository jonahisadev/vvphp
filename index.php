<?php

	// Prepare URL
	function prepare_url($str) {
		$x = str_replace(BASEURL, "", explode("?", $str)[0]);
		if (substr($x, -1) != "/") {
			$x = substr($x, 1, strlen($x)-1);
			header("Location: " . BASEURL  . "/" . $x . "/", TRUE, 301);
			exit();
		}
		return $x;
	}

	// Parse config
	$config = parse_ini_file("config.ini", true);
	define("BASEURL", $config['app']['baseurl'], true);
	define("VIEWPATH", $config['app']['views'], true);

	// Prepare the URL
	$url = prepare_url($_SERVER['REQUEST_URI']);

	// Connect to the database
	require_once "vv/db.php";
	$_DB = db_initialize($config['database']);
	$_DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	// Required functions
	require_once "vv/session.php";
	require_once "vv/security.php";
	require_once "vv/router.php";
	require_once "vv/view.php";
	require_once "vv/resource.php";
	require_once "vv/dao.php";

	// Get the routes to check
	include 'app/routes.php';

	// Check route
	Route::handle($url, $_SERVER['REQUEST_METHOD']);

	// Destroy any flash variables
	Session::destroyFlashes();

?>