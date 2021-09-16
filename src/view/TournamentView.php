<?php

namespace view;

use model\Tournament;

class TournamentView extends View {
	private Tournament $tournament;
	
	public function __construct(Tournament $tournament) {
		parent::__construct($tournament->getName(), 'tournament');
		
		$this->tournament = $tournament;
	}
	
	public function render(): void {
		parent::renderView(parent::$latte->renderToString('templates/tournament.latte', [
			'tournament' => $this->tournament
		]));
	}
}
