<?php

namespace controller\admin;

use controller\Controller;
use view\admin\DashboardView;

class DashboardController extends Controller {
	
	public function __construct() {
		parent::__construct();
	}
	
	protected function get(): int {
		if(!session_exists()) {
			return parent::UNAUTHORIZED();
		}
		
		session_start();
		
		parent::render(new DashboardView());
		
		return parent::OK;
	}
}
