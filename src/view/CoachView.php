<?php

namespace view;

use model\Coach;

class CoachView extends PersonView {
	private Coach $coach;
	
	public function __construct(Coach $coach) {
		parent::__construct($coach->getDisplayName(), 'coach');
		
		$this->coach = $coach;
	}
	
	public function render(): void {
		parent::renderPerson($this->coach, parent::template('templates/coach.latte', [
			'coach' => $this->coach
		]));
	}
}
