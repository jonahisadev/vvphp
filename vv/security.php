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

?>