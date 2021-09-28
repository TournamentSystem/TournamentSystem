<?php

namespace TournamentSystem\Controller\Admin;

use TournamentSystem\Controller\Controller;
use TournamentSystem\View\Admin\DashboardView;

class DashboardController extends Controller {
	
	public function __construct() {
		parent::__construct();
	}
	
	protected function get(): int {
		if(!session_exists()) {
			return parent::UNAUTHORIZED();
		}
		
		parent::render(new DashboardView());
		
		return parent::OK;
	}
}
