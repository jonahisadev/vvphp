<?php

class Person extends DAO {

	public function model() {
		$this->number("id")->primary()->inc();
		$this->string("name", 32);
		$this->number("age", 0);
	}

}

?>