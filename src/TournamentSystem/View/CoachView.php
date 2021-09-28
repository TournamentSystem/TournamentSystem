<?php

namespace TournamentSystem\View;

use TournamentSystem\Model\Coach;

class CoachView extends PersonView {
	private Coach $coach;
	
	public function __construct(Coach $coach) {
		parent::__construct($coach->getDisplayName(), 'coach');
		
		$this->coach = $coach;
	}
	
	public function render(): void {
		parent::renderPerson($this->coach, parent::template('coach.latte', [
			'coach' => $this->coach
		]));
	}
}
