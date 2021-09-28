<?php

namespace TournamentSystem\Controller;

use TournamentSystem\Model\Club;
use TournamentSystem\View\ClubView;

class ClubController extends Controller {
	
	public function __construct() {
		parent::__construct('SELECT * FROM tournament_club WHERE id=?');
	}
	
	protected function get(): int {
		$this->stmt->bind_param('i', $_REQUEST['id']);
		$this->stmt->execute();
		
		if($result = $this->stmt->get_result()) {
			if($club = $result->fetch_assoc()) {
				$club = new Club(
					$club['id'],
					$club['name']
				);
				
				parent::render(new ClubView($club));
			}
			
			$result->free();
		}
		
		return parent::OK;
	}
}
