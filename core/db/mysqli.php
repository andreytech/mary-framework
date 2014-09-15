<?php

class CoreDBMysqli {
	protected $user;
	protected $pass;
	protected $dbhost;
	protected $dbname;
	protected $dbh = null;
	protected $query = '';
	protected $result = null;
	protected $error = '';
	
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
	
    public function getCell() {
        if (!$this->execute()) {
            return false;
        }

        // INSERT/UPDATE/DELETE query executed
        if($this->result === true) {
            return true;
        }

        $retval = $this->result->fetch_array();
        if ($retval === false) {
            return null;
        }
        return $retval[0];
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

	public function getQuery() {
		return $this->query;
	}
}
