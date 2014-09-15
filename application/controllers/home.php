<?php

class HomeController extends CoreController
{
	function __construct() {
		parent::__construct();

		// Put any controller initialization code here
	}

	function index() {
		$data['page_title'] = 'Home page';
		$data['page_data'] = $this->models->home->getHomePageData();
		$this->views->load('home', $data, 'templates/main');
	}

	function second_page() {
		$data['text'] = "Here is second page without template";
		$this->views->load('second_page', $data);
	}
}

