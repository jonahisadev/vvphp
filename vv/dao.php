<?php

abstract class DAOItemType {
	const NUMBER 	= 0;
	const STR 		= 1;
	const BLOB		= 2;
	const DATE		= 3;
	const TIME		= 4;
	const TEXT 		= 5;
}

abstract class DAOConst {
	const NOW		= 0;
}

class DAOItem {

	function __construct($type, $name, $def, $opts=NULL) {
		$this->type = $type;
		$this->name = $name;
		$this->def = $def;
		$this->opts = $opts;

		$this->primary = false;
		$this->inc = false;
		$this->not_null = false;
	}

	function primary() {
		$this->primary = true;
		return $this;
	}

	function inc() {
		$this->inc = true;
		return $this;
	}

	function not_null() {
		$this->not_null = true;
		return $this;
	}

	function unsigned() {
		if ($this->type == DAOItemType::NUMBER) {
			$this->opts = [
				'unsigned' => 1
			];
		}
		return $this;
	}

}

abstract class DAO {

	private $_items = [];

	// Define model structure
	public abstract function model();

	// Create the structure in DB
	public function create($name) {
		$sql = "CREATE TABLE $name (\n";

		for ($i = 0; $i < count($this->_items); $i++) {
			$item = $this->_items[$i];

			// Name
			$sql .= $item->name;

			// Type
			switch ($item->type) {
				case DAOItemType::NUMBER:
					$sql .= " INT";
					if (isset($item->opts['unsigned'])) {
						$sql .= " UNSIGNED";
					}
					break;
				case DAOItemType::STR:
					$sql .= " VARCHAR(" . $item->opts['len'] . ")";
					break;
				case DAOItemType::BLOB:
					$sql .= " BLOB";
					break;
				case DAOItemType::DATE:
					$sql .= " TIMESTAMP";
					if (isset($item->opts['def'])) {
						$sql .= $item->opts['def'];
					}
					break;
				case DAOItemType::TIME:
					$sql .= " TIME";
					if (isset($item->opts['def'])) {
						$sql .= $item->opts['def'];
					}
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

			// Not Null
			if ($item->not_null) {
				$sql .= " NOT NULL";
			}

			// Default
			if ($item->def != NULL) {
				$sql .= " DEFAULT " . $this->def;
			}

			// Comma if not last
			if ($i != count($this->_items) - 1) {
				$sql .= ",";
			}
			$sql .= "\n";
		}

		$sql .= ")";

		// Read config and create table
		$conf = parse_ini_file(__DIR__ . "/../config.ini", true)["database"];
		$db = mysqli_connect($conf['host'], $conf['username'], $conf['password'], $conf['dbname'], 3306);
		if (!$db) {
			echo("Error connecting to DB: " . mysqli_connect_error() . "\n");
			exit();
		}

		if (!mysqli_query($db, $sql)) {
			echo("Error running query\n");
			exit();
		}

		echo("Successfully migrated!\n");
	}

	public static function get($pri) {
		// Get a child class instance
		$class = new ReflectionClass(get_called_class());
		$inst = $class->newInstance();
		
		// Run model
		$inst->model();

		// Find the primary key
		$primary = DAO::findPrimaryItem($inst);

		return $class->getMethod("getBy")->invoke($class, $primary->name, $pri);
	}

	public static function getBy($parameter, $value) {
		// Ensure DB is up and running
		global $_DB;
		if (!$_DB) {
			die("DB was not initialized");
		}

		// Get a child class instance
		$class = new ReflectionClass(get_called_class());
		$inst = $class->newInstance();
		
		// Run model
		$inst->model();

		// Actually run query
		if ($value != NULL) {
			// Run the query
			$sql = "SELECT * FROM " . $class->getName() . " WHERE " . $parameter . "=:" . $parameter;
			$stmt = $_DB->prepare($sql);
			$stmt->execute([$parameter => $value]);
			$data = $stmt->fetch();

			// Create DBO
			foreach ($inst->_items as $item) {
				$key = $item->name;
				$inst->{$key} = $data[$key];
			}
		} else {
			foreach ($inst->_items as $item) {
				$key = $item->name;
				$inst->{$key} = NULL;
			}
		}

		// Return
		return $inst;
	}

	public static function new($data=NULL) {
		global $_DB;
		if (!$_DB) {
			die("DB was not initialized");
		}

		// Set up a class instance
		$class = new ReflectionClass(get_called_class());

		// Create an empty DBO
		if ($data == NULL) {
			return $class->getMethod("get")->invoke($class, NULL);
		}

		// Assemble variable names
		$ph = "(";
		$index = 0;
		foreach ($data as $key => $val) {
			$ph .= ":" . $key;
			if ($index != count($data) - 1) {
				$ph .= ", ";
			}
			$index++;
		}
		$ph .= ")";

		// Assemble variable names
		$vars = "(";
		$index = 0;
		foreach ($data as $key => $val) {
			$vars .= $key;
			if ($index != count($data) - 1) {
				$vars .= ", ";
			}
			$index++;
		}
		$vars .= ")";

		// Create query
		$sql = "INSERT INTO " . get_called_class() . " " . $vars . " VALUES " . $ph;

		$stmt = $_DB->prepare($sql);
		$stmt->execute($data);

		return $class->getMethod("get")->invoke($class, $_DB->lastInsertId());
	}

	public function save() {
		global $_DB;
		if (!$_DB) {
			die("DB was not initialized");
		}

		// Begin SQL statement
		$sql = "UPDATE " . get_called_class() . " SET ";

		// Get object properties
		$props = get_object_vars($this);

		// Loop through
		$index = 0;
		foreach ($props as $key => $val) {
			if ($key == "_items") {
				continue;
			}

			$sql .= $key . "=:" . $key;
			
			$index++;
			if ($index != count($props)-1) {
				$sql .= ", ";
			}
		}

		// Finish up the SQL
		$primary = DAO::findPrimaryItem($this);
		$sql .= " WHERE " . $primary->name . "=:" . $primary->name;

		// Run a PDO
		$stmt = $_DB->prepare($sql);
		$stmt->execute($props);
	}

	private static function findPrimaryItem($inst) {
		foreach ($inst->_items as $item) {
			if ($item->primary) {
				$primary = $item;
			}
			break;
		}
		return $primary;
	}

	protected function number($name, $def=NULL) {
		$item = new DAOItem(DAOItemType::NUMBER, $name, $def);
		$this->_items[] = $item;
		return $item;
	}

	protected function string($name, $len, $def=NULL) {
		$opts = [
			"len" => $len
		];
		$item = new DAOItem(DAOItemType::STR, $name, $def, $opts);
		$this->_items[] = $item;
		return $item;
	}

	protected function text($name, $def=NULL) {
		$item = new DAOItem(DAOItemType::TEXT, $name, $def);
		$this->_items[] = $item;
		return $item;
	}

	protected function binary($name, $def=NULL) {
		$item = new DAOItem(DAOItemType::BLOB, $name, $def);
		$this->_items[] = $item;
		return $item;
	}

	protected function timestamp($name, $def=NULL) {
		if ($def == DAOConst::NOW) {
			$opts = [
				"def" => " DEFAULT NOW()"
			];
			$def = NULL;
		}
		$item = new DAOItem(DAOItemType::DATE, $name, $def, $opts);
		$this->_items[] = $item;
		return $item;
	}

	protected function time($name, $def=NULL) {
		if ($def == DAOConst::NOW) {
			$opts = [
				"def" => " DEFAULT NOW()"
			];
			$def = NULL;
		}
		$item = new DAOItem(DAOItemType::TIME, $name, $def, $opts);
		$this->_items[] = $item;
		return $item;
	}

}

?>