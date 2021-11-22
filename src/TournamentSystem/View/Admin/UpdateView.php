<?php

namespace TournamentSystem\View\Admin;

use TournamentSystem\View\View;

class UpdateView extends View {
	
	public function __construct() {
		parent::__construct('Update', 'admin_update');
	}
	
	public function render(): void {
		parent::renderView();
	}
}