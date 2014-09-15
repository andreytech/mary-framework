<?php
class CoreBase {
	protected $db;
	protected $errors = array();

	function __construct() {
		$this->db = CoreFactory::getDB();
	}

	function addError($error) {
		$this->errors[] = $error;
	}

	function getErrorMsg() {
		if($this->errors) {
			return implode('', $this->errors);
		}
		return '';
	}

	function getErrors() {
		return $this->errors;
	}

}