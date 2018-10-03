<?php

class Session {

	public static function init($arr = NULL) {
		session_start();
		foreach ($arr as $key => $value) {
			$_SESSION[$key] = $value;
		}
	}

	public static function destroy() {
		session_destroy();
	}

	public static function set($key, $value) {
		$_SESSION[$key] = $value;
	}

	public static function get($key) {
		return $_SESSION[$key];
	}

	public static function remove($key) {
		unset($_SESSION[$key]);
	}

	public static function has($key) {
		return isset($_SESSION[$key]);
	}

	public static function exists() {
		return session_id() != '';
	}

	public static function addFlash($key, $value) {
		$_SESSION["flsh_" . $key] = $value;
	}

	public static function getFlash($key) {
		return Session::get("flsh_" . $key);
	}

	public static function hasFlash($key) {
		return Session::has("flsh_" . $key);
	}

	public static function destroyFlashes() {
		if (Session::exists()) {
			$flip = array_flip($_SESSION);
			for ($i = 0; $i < count($flip); $i++) {
				$name = $flip[$i];
				if (substr($name, 0, 5) == "flsh_") {
					unset($_SESSION[$name]);
				}
			}
		}
	}

}

?>