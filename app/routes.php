<?php

	include "model/Person.php";

	Route::get("/", function() {
		View::show("index");
	});

	Route::get("/person", function() {
		Person::get(1);
	});

?>