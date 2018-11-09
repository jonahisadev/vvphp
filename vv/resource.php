<?php

	function stylesheet($name) {
		echo('<link rel="stylesheet" href="' . BASEURL . '/app/res/css/' . $name . '" />');
	}

	function script($name) {
		echo('<script src="' . BASEURL . '/app/res/js/' . $name . '"></script>');
	}

	function image($name, $opts=NULL) {
		$attr = "";

		foreach ($opts as $key => $val) {
			$attr .= $key . '="' . $val . '" ';
		}

		echo('<img src="' . BASEURL . '/app/res/img/' . $name . '" ' . $attr . '></img>');
	}

?>