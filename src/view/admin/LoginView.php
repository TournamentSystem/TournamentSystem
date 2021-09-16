<?php

namespace view\admin;

use view\View;

class LoginView extends View {
	private bool $invalidData;
	
	public function __construct(?bool $invalidData = false) {
		parent::__construct('Login', 'admin_login');
		
		$this->invalidData = $invalidData;
	}
	
	public function render(): void {
		parent::renderView(parent::$latte->renderToString('templates/admin/login.latte', [
			'invalidData' => $this->invalidData
		]));
	}
}
