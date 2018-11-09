<?php

	class View {

		static function show($path, $args=NULL) {
			if ($args != NULL) {
				extract($args);
			}
			include VIEWPATH . "/" . $path . ".php";
		}

		static function redirect($url) {
			header("Location: " . BASEURL . $url);
			exit();
		}

		static function redirectGlobal($url) {
			header("Location: " . $url);
			exit();
		}

	}

?>