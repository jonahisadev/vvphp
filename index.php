<?php

	// Prepare URL
	function prepare_url($str) {
		$x = str_replace(INDEX, "", explode("?", $str)[0]);
		if (substr($x, -1) != "/") {
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				$x = substr($x, 1, strlen($x)-1);
				header("Location: " . INDEX  . "/" . $x . "/", TRUE, 301);
				exit();
			} else {
				$x .= "/";
			}
		}
		return $x;
	}

	// Parse config
	$config = parse_ini_file("config.ini", true);

	if ($config['app']['index'] == "/") {
		define("INDEX", "", true);
	} else {
		define("INDEX", $config['app']['index'], true);
	}

	if ($config['app']['baseurl'] == "/") {
		define("BASEURL", "", true);
	} else {
		define("BASEURL", $config['app']['baseurl'], true);
	}

	define("VIEWPATH", $config['app']['views'], true);
	define("COMPOSER", $config['app']['composer'], true);
	define("MODE", $config['app']['mode'], true);

	// Prepare the URL
	$url = prepare_url($_SERVER['REQUEST_URI']);

	// Connect to the database
	if ($config['app']['db']) {
		require_once "vv/db.php";
		$_DB = db_initialize($config['database']);
		$_DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	// Required functions
	require_once "vv/session.php";
	require_once "vv/security.php";
	require_once "vv/router.php";
	require_once "vv/view.php";
	require_once "vv/resource.php";
	require_once "vv/dao.php";
	require_once "vv/mode.php";

	// Get the routes to check
	include 'app/routes.php';

	// Check route
	Route::handle($url, $_SERVER['REQUEST_METHOD']);

	// Destroy any flash variables
	Session::destroyFlashes();

?>