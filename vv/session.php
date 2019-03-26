<?php

class Session {

	public static function init($arr = NULL) {
		session_start();
		if ($arr != NULL) {
			foreach ($arr as $key => $value) {
				$_SESSION[$key] = $value;
			}
		}
	}

	public static function destroy() {
		session_start();
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

	public static function addFlash($key, $value="") {
		if (!Session::exists()) {
			Session::init();
		}
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
			foreach($_SESSION as $key => $value) {
				if (substr($key, 0, 5) == "flsh_") {
					unset($_SESSION[$key]);
				}
			}
		}
	}

}

?>