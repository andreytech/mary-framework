<?php

class CoreConfig {
	private $config = false;
	public static $_instance = null;

	public static function getInstance() {
		if (null === self::$_instance) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	function get($param) {
		if($this->config === false) {
			$this->loadConfig();
		}
		if(isset($this->config[$param])) {
			return $this->config[$param];
		}
		return false;
	}

	function loadConfig() {
		include ROOT_FOLDER.'config.php';
		if(isset($config)) {
			$this->config = $config;
		}else {
			$this->config = array();
		}
	}
}