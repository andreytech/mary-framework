<?php

class CoreDBMysqli {
	public static $_instance = null;
	protected $user;
	protected $pass;
	protected $dbhost;
	protected $dbname;
	protected $dbh = null;
	protected $query = '';
	protected $result = null;
	protected $error = '';
	
	public static function getInstance() {
		if (null === self::$_instance) {
			$config = CoreConfig::getInstance();
			self::$_instance = new self(
				$config->get('dbhost')
				, $config->get('dbname')
				, $config->get('dbuser')
				, $config->get('dbpass')
			);
		}

		return self::$_instance;
	}

	public function __construct($host, $dbname, $user, $pass) {
		$this->dbhost = $host;
		$this->dbname = $dbname;
		$this->user = $user;
		$this->pass = $pass;
	}
	
	protected function connect() {
		if($this->dbh) {
			return true;
		}
		$this->dbh = new mysqli($this->dbhost, $this->user, $this->pass, $this->dbname);

		if (!$this->dbh) {
		    die('Connect Error (' . mysqli_connect_errno() . ') '
		            . mysqli_connect_error());
		}

		return true;
	}

	public function escape($escape_value) {
		$this->connect();

		if (is_array($escape_value)) {
			foreach($escape_value as $key => $value) {
				$escape_value[$key] = $this->escape($value);
			}
			return $escape_value;
		}

		return $this->dbh->real_escape_string($escape_value);
	}

	public function getArrays() {
		if (!$this->execute()) {
			return false;
		}

		// INSERT/UPDATE/DELETE query executed
		if($this->result === true) {
			return true;
		}

		$retval = array();
		while($row = $this->result->fetch_assoc() ) {
			$retval[] = $row;
		}
		return $retval;
	}
	
	public function getArray() {
		if (!$this->execute()) {
			return false;
		}

		// INSERT/UPDATE/DELETE query executed
		if($this->result === true) {
			return true;
		}

		$retval = $this->result->fetch_assoc();
		if ($retval === false) {
			return null;
		}
		return $retval;
	}
	
	public function getDBConnection() {
		return $this->dbh;
	}
	
	public function getErrorMsg() {
		return $this->error;
	}

	public function getObjects() {
		if (!$this->execute()) {
			return false;
		}

		// INSERT/UPDATE/DELETE query executed
		if($this->result === true) {
			return true;
		}

		$retval = array();
		while($row = $this->result->fetch_object() ) {
			$retval[] = $row;
		}
		return $retval;
	}

	public function getObject() {
		if (!$this->execute()) {
			return false;
		}

		// INSERT/UPDATE/DELETE query executed
		if($this->result === true) {
			return true;
		}

		$retval = $this->result->fetch_object();
		if ($retval === false) {
			return null;
		}
		return $retval;
	}

	public function getColumn() {
		if (!$this->execute()) {
			return false;
		}

		// INSERT/UPDATE/DELETE query executed
		if($this->result === true) {
			return true;
		}

		$retval = array();
		while($row = $this->result->fetch_object() ) {
			$fields = get_object_vars($row);
			reset($fields);
			$first_field = key($fields);

			$retval[] = $row->$first_field;
		}
		return $retval;
	}

	public function getResult() {
		if (!$this->execute()) {
			return false;
		}

		// INSERT/UPDATE/DELETE query executed
		if($this->result === true) {
			return true;
		}

		$row = $this->result->fetch_row();
		if ($row === false) {
			return null;
		}
		return $row[0];
	}
	
	public function getInsertID() {
		return $this->dbh->insert_id;
	}

	public function execute() {
		$this->result = $this->dbh->query($this->query);
		if(!$this->result) {
			$this->error = $this->dbh->error.'Query:'.$this->query;
			return false;
		}
		return true;
	}
	
	public function setQuery($query) {
		$this->connect();
		$this->query = $query;
		return $this;
	}

	public function insert($table, $data) {
		if(empty($data) || empty($table) || !is_array($data)) {
			return false;
		}

		$values = array();
		foreach($data as $field => $value) {
			$field = $this->escape($field);
			$value = $this->escape($value);
			$values[] = " `{$field}` = '{$value}' ";
		}

		$q = "INSERT INTO `{$table}` SET ".implode(', ', $values);

		return $this->setQuery($q)->execute();
	}

	public function update($table, $data, $condition) {
		if(empty($data) || empty($table) || !is_array($data)) {
			return false;
		}

		$values = array();
		foreach($data as $field => $value) {
			$field = $this->escape($field);
			$value = $this->escape($value);
			$values[] = " `{$field}` = '{$value}' ";
		}

		$q = "UPDATE `{$table}` SET ".implode(', ', $values)." WHERE {$condition}";
//		var_dump($q);exit;
		return $this->setQuery($q)->execute();
	}

	public function delete($table, $condition) {
		if(empty($table) || empty($condition)) {
			return false;
		}

		$q = "DELETE FROM `{$table}` WHERE {$condition}";

		return $this->setQuery($q)->execute();
	}
}
