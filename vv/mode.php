<?php

class Mode {

	static function isDebug() {
		return (MODE == "DEBUG" || MODE == "debug");
	}

	static function isProduction() {
		return (MODE == "PROD" || MODE == "PRODUCTION" || MODE == "prod" || MODE == "production");
	}

	static function runDebug($cb) {
		if (Mode::isDebug()) {
			call_user_func($cb);
		}
	}

	static function runProduction($cb) {
		if (Mode::isProduction()) {
			call_user_func($cb);
		}
	}
	
}

?>