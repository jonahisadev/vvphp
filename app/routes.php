<?php

	//
	//	PUT ROUTES IN HERE
	//

	Route::get("/", function() {
		View::show("index");
	});

	Route::get("/test", function () {
		Mode::runDebug(function () {
			echo ("DEBUG: ");
		});
		echo ("Hey!");
	});

?>