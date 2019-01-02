<?php

$_CSRF = NULL;

class RouteData {

	public $url;
	public $cb;
	public $vars = [];
	public $csrf = false;
	public $use_vars = true;

	function __construct($url, $cb, $use_vars) {
		// Add a trailing slash (helpful for regex)
		if (substr($url, -1) != "/") {
			$url .= "/";
		}

		// Make regex work for home
		if ($url == "/") {
			$url .= "/";
		}

		// We have a variable
		$var_count = substr_count($url, "{");
		if ($var_count > 0) {
			for ($i = 0; $i < $var_count; $i++) {
				// Check if the variable is the last element
				// in the URL
				$var_is_last = (substr($url, -strlen("}/")) == "}/");

				// Extract the variable name
				$var = substr($url, strpos($url, "{"), strpos($url, "}") - strpos($url, "{") + 1);
				$this->vars[] = $var;
				
				//
				//	Reformat the URL for a regex match later.
				//	This is probably the grossest code I've ever written
				//	and I would like to apologize for that.
				//
				
				// 1. Replace the variable with a catch all regex match
				$url = str_replace("/" . $var, '\/(.*)', $url);

				// 2. Remove any double forward slashes
				$url = str_replace("//", "/", $url);

				// 3. Escape the forward slashes
				$url = preg_replace("/(?<!\\\\)\//", "\/", $url);

				// 4. Different endings based on where the variable is
				if ($i == $var_count - 1) {
					if ($var_is_last) {
						$url = substr_replace($url, "/$", strlen($url) - 1, 0);
					} else {
						$url = substr_replace($url, "\/$", strlen($url) - 1, 0);
					}
				}

				// 5. Remove the leading backslash (see 3)
				$url = substr($url, 1, strlen($url) - 1);

				// 6. Remove double forward slashes
				$url = str_replace("\\\\", "\\", $url);
			}
		} else {
			$url = str_replace("//", "/", $url);
			$url = preg_replace("/(?<!\\\\)\//", "\/", $url);
			$url = substr_replace($url, "\/$", strlen($url) - 1, 0);
			$url = substr($url, 1, strlen($url) - 1);
			$url = str_replace("\\\\", "\\", $url);
		}

		// Catch this home bug
		if ($url == "\/$/") {
			$url = "/^" . $url;
		}

		// echo($url . "<br>");

		$this->url = $url;
		$this->cb = $cb;
		$this->use_vars = $use_vars;
	}

}

class Route {

	private static $get = [];
	private static $post = [];

	static function handle($url, $method) {

		// TODO: Try and combine this code differently
		// 		 There's no reason to have separate
		//		 code for GET, POST, etc.

		// GET
		if ($method == 'GET') {

			// Set up a ref array
			$matches = [];

			// 404 check
			$found = false;

			// Loop through routes
			for ($i = 0; $i < count(Route::$get); $i++) {

				// Save route for later
				$route = Route::$get[$i];

				// echo("(" . $route->url . " === " . $url . ")<br>");

				// Regex match the URL to the Route's URL
				preg_match($route->url, $url, $matches);

				// If we have a match, do more checks
				if (count($matches) > 0) {
					// This isn't 404
					$found = true;

					// Get the callback
					$cb = $route->cb;

					// Build argument list
					$ref = new ReflectionFunction($cb);
					$args = [];
					
					// We have variables in the URL
					if (count($matches) > 1) {
						// Loop through the matches
						for ($x = 1; $x < count($matches); $x++) {

							// If there's a forward slash, we didn't
							// match the right one
							if (strpos($matches[$x], "/")) {
								continue 2;
							}
							
							// Add an argument to the list
							$args[$route->vars[$x-1]] = $matches[$x];
						}
					}

					// Loop through GET parameters
					foreach($ref->getParameters() as $param) {
						if (!isset($args[$param->getName()])) {
							$args[$param->getName()] = $_GET[$param->getName()];
						}
					}

					// Call with arguments
					call_user_func_array($cb, $args);
				}
			}

			if (!$found) {
				Route::send404($url);
			}
		}

		// POST
		if ($method == 'POST') {
			// Set up a ref array
			$matches = [];

			// Check 404
			$found = false;

			// Loop through routes
			for ($i = 0; $i < count(Route::$post); $i++) {

				// Save route for later
				$route = Route::$post[$i];

				// Regex match the URL to the Route's URL
				preg_match($route->url, $url, $matches);

				// If we have a match, do more checks
				if (count($matches) > 0) {

					// Get the callback
					$cb = $route->cb;

					// Save 404
					$found = true;

					// Build argument list
					$ref = new ReflectionFunction($cb);
					$args = [];
					
					// We have variables in the URL
					if (count($matches) > 1) {
						// Get the variable names
						// TODO: do we need this?
						$flip = array_flip($matches);

						// Loop through the matches
						for ($x = 1; $x < count($matches); $x++) {

							// If there's a forward slash, we didn't
							// match the right one
							if (strpos($matches[$x], "/")) {
								continue 2;
							}

							// Add an argument to the list
							$args[$route->vars[$x-1]] = $matches[$x];
						}
					}

					// Loop through POST parameters
					if ($route->use_vars) {
						foreach($ref->getParameters() as $param) {
							if (!isset($args[$param->getName()])) {
								$args[$param->getName()] = $_POST[$param->getName()];
							}
						}
					}

					// Check CSRF if secure post
					if ($route->csrf) {
						global $_CSRF;
						if (isset($_POST['_csrf'])) {
							if (!csrf_verify($_POST['_csrf'])) {
								Route::sendSecurityError();
							}
						} else {
							Route::sendSecurityError();
						}
					}

					// Call with arguments
					call_user_func_array($cb, $args);
				}
			}

			if (!$found) {
				Route::send404($url);
			}
		}
	}

	static function get($url, $cb, $vars=TRUE) {
		Route::$get[] = new RouteData($url, $cb, $vars);
	}

	static function post($url, $cb, $vars=TRUE) {
		Route::$post[] = new RouteData($url, $cb, $vars);
	}

	static function spost($url, $cb, $vars=TRUE) {
		Route::post($url, $cb, $vars);
		$last = Route::$post[count(Route::$post)-1];
		$last->csrf = true;
		Session::init();
	}

	private static function send404($URL) {
		http_response_code(404);
		include 'vv/data/404.php';
	}

	private static function sendSecurityError() {
		http_response_code(401);
		include 'vv/data/csrf.php';
		Session::destroy();
		die();
	}

}

?>