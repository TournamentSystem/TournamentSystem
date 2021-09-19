<?php

namespace view;

use model\Person;

abstract class PersonView extends View {
	
	protected final function renderPerson(Person $person, string $body): void {
		parent::renderView(parent::template('templates/person.latte', [
				'person' => $person
			]) . $body);
	}
}
