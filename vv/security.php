<?php

	function csrf_field() {
		echo('<input id="_csrf" name="_csrf" type="hidden" value="' . Session::get("_csrf") . '" />');
	}

	function csrf_create() {
		Session::set("_csrf", bin2hex(random_bytes(32)));
	}

	function csrf_verify($token) {
		return hash_equals(Session::get("_csrf"), $token);
	}

	function request_limit($times, $redirect=NULL) {
		Session::init();
		if (!Session::exists()) {
			Session::init([
				"_req_attempts" => 0
			]);
		}

		Session::set("_req_attempts", Session::get("_req_attempts") + 1);

		if (Session::get("_req_attempts") >= $times) {
			http_response_code(403);
			if ($redirect != NULL) {
				View::redirect($redirect);
			} else {
				die("You've sent too many requests to this page");
			}
		}
	}

?>