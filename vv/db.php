<?php

	function db_initialize($config) {
		$host = $config['host'];
		$username = $config['username'];
		$password = $config['password'];
		$dbname = $config['dbname'];

		$connection = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
		return $connection;
	}

?>