<?php

namespace view;

use model\Club;

class ClubView extends View {
	private Club $club;
	
	public function __construct(Club $club) {
		parent::__construct($club->getName(), 'club');
		
		$this->club = $club;
	}
	
	public function render(): void {
		parent::renderView(parent::$latte->renderToString('templates/club.latte', [
			'club' => $this->club
		]));
	}
}
