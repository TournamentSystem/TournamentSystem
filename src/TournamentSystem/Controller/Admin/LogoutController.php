<?php

namespace TournamentSystem\Controller\Admin;

use TournamentSystem\Controller\Controller;

class LogoutController extends Controller {
	
	public function __construct() {
		parent::__construct();
	}
	
	protected function get(): int {
		if(!session_exists()) {
			return parent::UNAUTHORIZED();
		}
		
		session_stop();
		
		return parent::SEE_OTHER('/');
	}
}
