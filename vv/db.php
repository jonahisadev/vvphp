<?php

	function db_initialize($config) {
		$host = $config['host'];
		$port = $config['port'];
		$username = $config['username'];
		$password = $config['password'];
		$dbname = $config['dbname'];

		$connection = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
		return $connection;
	}

?>