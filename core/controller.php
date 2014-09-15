<?php

class CoreController extends CoreBase {
	public $views;
	public $models;

	function __construct() {
		parent::__construct();

		$this->views = new CoreViewsLoader($this);
		$this->models = new CoreModelsLoader();
	}
}
