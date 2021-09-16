<?php

namespace controller\admin;

use controller\Controller;
use view\admin\LoginView;

class DashboardController extends Controller {
	
	public function __construct() {
		parent::__construct();
	}
	
	protected function get(): int {
		if(!isset($_COOKIE['PHPSESSID'])) {
			parent::render(new LoginView());
			
			header('WWW-Authenticate: Cookie realm="TournamentSystem" form-action="/admin/login/" cookie-name="' . session_name() . '"');
			return parent::UNAUTHORIZED;
		}
		
		session_start();
		
		return parent::OK;
	}
}
