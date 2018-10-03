<?php

abstract class DAOItemType {
	const NUMBER 	= 0;
	const STR 		= 1;
}

class DAOItem {

	function __construct($type, $name, $def, $opts=NULL) {
		$this->type = $type;
		$this->name = $name;
		$this->def = $def;
		$this->opts = $opts;

		$this->primary = false;
		$this->inc = false;
	}

	function primary() {
		$this->primary = true;
		return $this;
	}

	function inc() {
		$this->inc = true;
		return $this;
	}

}

abstract class DAO {

	private $items = [];

	// Define model structure
	public abstract function model();

	// Create the structure in DB
	public function create($name) {
		$sql = "CREATE TABLE $name (\n";

		for ($i = 0; $i < count($this->items); $i++) {
			$item = $this->items[$i];

			// Name
			$sql .= $item->name;

			// Type
			switch ($item->type) {
				case DAOItemType::NUMBER:
					$sql .= " INT";
					break;
				case DAOItemType::STR:
					$sql .= " VARCHAR(" . $item->opts['len'] . ")";
					break;
			}

			// Auto increment
			if ($item->inc) {
				$sql .= " AUTO_INCREMENT";
			}

			// Primary
			if ($item->primary) {
				$sql .= " PRIMARY KEY";
				// TODO: Check for multiple primaries
			}

			// Comma if not last
			if ($i != count($this->items) - 1) {
				$sql .= ",";
			}
			$sql .= "\n";
		}

		$sql .= ")";

		echo($sql . "\n");

		error_reporting(E_ALL);

		// Read config and create table
		$conf = parse_ini_file(__DIR__ . "/../config.ini", true)["database"];
		$db = mysqli_connect($conf['host'], $conf['username'], $conf['password'], $conf['dbname'], 3306);
		if (!$db) {
			echo("Error connecting to DB: " . mysqli_connect_error() . "\n");
			exit();
		}
	}

	public static function get($pri) {
		global $_DB;
		if (!$_DB) {
			die("DB was not initialized");
		}

		$class = new ReflectionClass(get_called_class());
		$inst = $class->newInstance();
		
		$inst->model();
		foreach ($inst->items as $item) {
			$primary = $item;
			break;
		}

		$sql = "SELECT * FROM " . $class->getName() . " WHERE " . $primary->name . "=?";
		$stmt = $_DB->prepare($sql);
		$stmt->execute([$primary->name => $pri]);
		$object = $stmt->fetch();

		var_dump($object);
		die();
	}

	protected function number($name, $def=NULL) {
		$item = new DAOItem(DAOItemType::NUMBER, $name, $def);
		$this->items[] = $item;
		return $item;
	}

	protected function string($name, $len, $def=NULL) {
		$opts = [
			"len" => $len
		];
		$item = new DAOItem(DAOItemType::STR, $name, $def, $opts);
		$this->items[] = $item;
		return $item;
	}

}

?>