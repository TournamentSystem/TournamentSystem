<?php

namespace controller\admin;

use controller\Controller;

class DashboardController extends Controller {
	
	public function __construct() {
		parent::__construct();
	}
	
	protected function get(): int {
		if(!session_exists()) {
			return parent::UNAUTHORIZED();
		}
		
		session_start();
		
		return parent::OK;
	}
}
