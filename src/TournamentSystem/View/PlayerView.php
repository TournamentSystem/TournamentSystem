<?php

namespace TournamentSystem\View;

use TournamentSystem\Model\Player;

class PlayerView extends PersonView {
	private Player $player;
	
	public function __construct(Player $player) {
		parent::__construct($player->getDisplayName(), 'player');
		
		$this->player = $player;
	}
	
	public function render(): void {
		parent::renderPerson($this->player, parent::template('player.latte', [
			'player' => $this->player
		]));
	}
}
