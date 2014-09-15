<?php
class CoreActiveRecord extends CoreDBMysqli {
	public static $_instance = null;
	private $select_params = array();
	private $is_trans_started = 0;

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

	public function insert($table, $data, $table_fields = false) {
		if(empty($data) || empty($table) || !is_array($data)) {
			return false;
		}

		$values = array();
		foreach($data as $field => $value) {
			if(is_array($table_fields) && !in_array($field, $table_fields)
			) {
				continue;
			}
			$field = $this->escape($field);
			$value = $this->escape($value);
			$values[] = " `{$field}` = '{$value}' ";
		}

		$q = "INSERT INTO `{$table}` SET ".implode(', ', $values);

//        echo json_encode(array('sql'=>$q));exit();
//        echo $q;exit();

		return $this->setQuery($q)->execute();
	}

	public function update($table, $data, $condition, $table_fields = array()) {
		if(empty($data) || empty($table) || !is_array($data)) {
			return false;
		}

		$values = array();
		foreach($data as $field => $value) {
			if($table_fields && is_array($table_fields)
					&& !in_array($field, $table_fields)
			) {
				continue;
			}
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

	private function _buildSelect() {
		if(empty($this->select_params['table'])) {
			return;
		}
		$q = "SELECT ";
		if(!empty($this->select_params['fields'])) {
			$q .= implode(',', $this->select_params['fields']);
		}else {
			$q .= '*';
		}
		$q .= " \nFROM `{$this->select_params['table']}` ";
		if(!empty($this->select_params['table_synonym'])) {
			$q .= $this->select_params['table_synonym'];
		}
		if(!empty($this->select_params['joins'])) {
			$q .= implode('', $this->select_params['joins']);
		}
		if(!empty($this->select_params['where'])) {
			$q .= " \nWHERE ".implode(" \nAND ", $this->select_params['where']);
		}
		if(!empty($this->select_params['group_by'])) {
			$q .= " \nGROUP BY ".$this->select_params['group_by'];
		}
		if(!empty($this->select_params['order_by'])) {
			$q .= " \nORDER BY ".$this->select_params['order_by'];
		}
		if(!empty($this->select_params['limit'])) {
			$q .= " \nLIMIT ";
			if(!empty($this->select_params['offset'])) {
				$q .= intval($this->select_params['offset']).', ';
			}
			$q .= intval($this->select_params['limit']);
		}
		$this->setQuery($q);
	}

	public function select($fields) {
		if(!is_array($fields)) {
			$fields = explode(',', $fields);
		}

		if(empty($this->select_params['fields'])) {
			$this->select_params['fields'] = $fields;
		}else {
			$this->select_params['fields'] = array_merge($this->select_params['fields'], $fields);
		}

		$this->_buildSelect();
		return $this;
	}

	public function from($table, $synonym = '') {
		$this->select_params['table'] = $table;
		$this->select_params['table_synonym'] = $synonym;

		$this->_buildSelect();
		return $this;
	}

	public function where() {
		$args = func_get_args();
		if(!$args) {
			return $this;
		}

		if(count($args) == 1) {
			if(is_array($args[0])) {
				foreach($args[0] as $field => $value) {
					$this->select_params['where'][] = "`".$this->escape($field)."` = '".$this->escape($value)."'";
				}
			}else {
				$this->select_params['where'][] = $args[0];
			}
		}else {
			$value = $this->escape($args[1]);
			if(isset($args[2])) {
				if($args[2] == 'int') {
					$value = (int) $args[1];
				}
			}
			if(strstr($args[0], '.')!==false) {
				$this->select_params['where'][] = $this->escape($args[0])." = '{$value}'";
			}else {
				$this->select_params['where'][] = "`".$this->escape($args[0])."` = '{$value}'";
			}

		}

		$this->_buildSelect();

		return $this;
	}

	public function like($field, $value) {
		$field = $this->escape($field);
		$value = $this->escape($value);
		$this->select_params['where'][] = "`{$field}` LIKE '%{$value}%'";

		$this->_buildSelect();
		return $this;
	}

	public function order_by($order_by) {
		$this->select_params['order_by'] = $order_by;

		$this->_buildSelect();
		return $this;
	}

	public function limit($limit, $offset = 0) {
		$this->select_params['limit'] = (int) $limit;
		$this->select_params['offset'] = (int) $offset;

		$this->_buildSelect();
		return $this;
	}

	public function group_by($group_by) {
		$this->select_params['group_by'] = $group_by;

		$this->_buildSelect();
		return $this;
	}

	public function join($table, $on, $type = 'inner') {
		$join = " \nINNER JOIN ";
		if($type == 'left') {
			$join = " \nLEFT JOIN ";
		}

		$join .= " {$table} ON {$on}";
		$this->select_params['joins'][] = $join;

		return $this;
	}

	public function clearQuery() {
		$this->select_params = array();
	}

	public function execute() {
		$this->select_params = array();

		$res = parent::execute();
		if($res === false && $this->is_trans_started) {
			$this->setQuery("ROLLBACK")->execute();
			$this->is_trans_started = 0;
		}

		return $res;
	}

	public function trans_start() {
		$this->setQuery("START TRANSACTION")->execute();
		$this->is_trans_started = 1;
	}

	public function trans_complete() {
		if(!$this->is_trans_started) {
			return false;
		}
		$this->setQuery("COMMIT")->execute();
		$this->is_trans_started = 0;
		return true;
	}
}