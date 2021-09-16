<?php

namespace view;

use DateTime;
use model\Tournament;

class TournamentListView extends View {
	/**
	 * @var array<string, Tournament[]>
	 */
	private array $tournaments = [];
	private string $year;
	
	/**
	 * @param Tournament[] $tournaments
	 */
	public function __construct(array $tournaments = [], string $year = null) {
		parent::__construct('Tournament List', 'tournament_list');
		
		uasort($tournaments, fn($a, $b) => $a->getStart() <=> $b->getStart());
		foreach($tournaments as $tournament) {
			$sy = $tournament->getStart()->format('Y');
			$ey = $tournament->getEnd()->format('Y');
			
			if(!array_key_exists($sy, $this->tournaments)) {
				$this->tournaments[$sy] = [];
			}
			
			array_push($this->tournaments[$sy], $tournament);
			
			if($ey != $sy) {
				if(!array_key_exists($ey, $this->tournaments)) {
					$this->tournaments[$ey] = [];
				}
				
				array_push($this->tournaments[$ey], $tournament);
			}
		}
		krsort($this->tournaments);
		
		$this->year = $year ?? (new DateTime())->format('Y');
	}
	
	public function render(): void {
		parent::renderView(parent::$latte->renderToString('templates/tournament_list.latte', [
			'tournaments' => $this->tournaments,
			'year' => $this->year
		]));
	}
}
