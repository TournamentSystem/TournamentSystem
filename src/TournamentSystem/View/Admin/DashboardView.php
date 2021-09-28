<?php

namespace TournamentSystem\View\Admin;

use TournamentSystem\View\View;

class DashboardView extends View {
	
	public function __construct() {
		parent::__construct('Dashboard', 'admin_dashboard');
	}
	
	public function render(): void {
		parent::renderView(parent::template('admin/dashboard.latte'));
	}
}
