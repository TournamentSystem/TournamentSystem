<?php

namespace TournamentSystem\View;

use TournamentSystem\Model\Person;

abstract class PersonView extends View {
	
	protected final function renderPerson(Person $person, string $body): void {
		parent::renderView(parent::template('person.latte', [
				'person' => $person
			]) . $body);
	}
}
