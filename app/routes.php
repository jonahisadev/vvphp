<?php

	include 'model/Test.php';

	//
	//	PUT ROUTES IN HERE
	//

	Route::get("/", function() {
		View::show("index");
	});

?>