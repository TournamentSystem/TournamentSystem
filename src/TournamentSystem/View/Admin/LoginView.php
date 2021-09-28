<?php

namespace TournamentSystem\View\Admin;

use TournamentSystem\View\View;

class LoginView extends View {
	private bool $invalidData;
	
	public function __construct(?bool $invalidData = false) {
		parent::__construct('Login', 'admin_login');
		
		$this->invalidData = $invalidData;
	}
	
	public function render(): void {
		parent::renderView(parent::template('admin/login.latte', [
			'invalidData' => $this->invalidData
		]));
	}
}
