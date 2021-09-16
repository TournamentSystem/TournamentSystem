<?php

namespace view;

use model\Person;

abstract class PersonView extends View {
	
	public function renderPerson(Person $person, string $body): void {
		parent::renderPage(parent::$latte->renderToString('templates/person.latte', [
				'person' => $person
			]) . $body);
	}
}