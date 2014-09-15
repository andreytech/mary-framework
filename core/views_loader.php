<?php

class CoreViewsLoader {
	protected $data = array();
	private $_caller = null;

	function __construct($caller_obj = false) {
		$this->_caller = $caller_obj;
	}

	function load($view, $data = array(), $template = false, $is_return_content = 0) {
		if(!$view) {
			return false;
		}
		$this->data = array_merge($this->data, $data);
		if($template) {
			$this->data['view'] = $view;
			$view = $template;
		}
		$view = strtolower($view);

		$view_file = APPLICATION_FOLDER.'views/'
											 .$view.'.php';
		if(!file_exists($view_file)) {
			error('View file "'.$view.'" does not exist');
			exit;
		}

		foreach($this->data as $key => $_data_item) {
			$$key = $_data_item;
		}

		if($is_return_content) {
			ob_start();
		}
		include $view_file;
		if($is_return_content) {
			return ob_get_clean();
		}
		return true;
	}

	function insert($field) {
		if(isset($this->data[$field])) {
			echo $this->data[$field];
		}
	}

	function set($field, $value) {
		$this->data[$field] = $value;
	}
}