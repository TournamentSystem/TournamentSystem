<?php

namespace TournamentSystem\View;

use TournamentSystem\Model\Tournament;

class TournamentView extends View {
	private Tournament $tournament;
	
	public function __construct(Tournament $tournament) {
		parent::__construct($tournament->getName(), 'tournament');
		
		$this->tournament = $tournament;
	}
	
	public function render(): void {
		parent::renderView(parent::template('tournament.latte', [
			'tournament' => $this->tournament
		]));
	}
}
