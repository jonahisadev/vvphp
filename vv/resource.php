<?php

	function stylesheet($name) {
		echo('<link rel="stylesheet" href="' . BASEURL . '/app/res/css/' . $name . '" />');
	}

	function script($name) {
		echo('<script src="' . BASEURL . '/app/res/js/' . $name . '"></script>');
	}

?>