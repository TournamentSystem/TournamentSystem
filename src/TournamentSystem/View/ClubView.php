<?php

namespace TournamentSystem\View;

use TournamentSystem\Model\Club;

class ClubView extends View {
	private Club $club;
	
	public function __construct(Club $club) {
		parent::__construct($club->getName(), 'club');
		
		$this->club = $club;
	}
	
	public function render(): void {
		parent::renderView(parent::template('club.latte', [
			'club' => $this->club
		]));
	}
}
