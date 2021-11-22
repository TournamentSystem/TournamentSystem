<?php

namespace TournamentSystem\Controller\Admin;

use TournamentSystem\Controller\Controller;
use TournamentSystem\View\Admin\UpdateView;

class UpdateController extends Controller {
	
	public function __construct() {
		parent::__construct();
	}
	
	protected function get(): int {
		if(!session_exists()) {
			return parent::UNAUTHORIZED();
		}
		
		parent::render(new UpdateView());
		
		return parent::OK;
	}
}