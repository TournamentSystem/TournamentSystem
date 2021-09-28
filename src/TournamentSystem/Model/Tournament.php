<?php

namespace TournamentSystem\Model;

use DateTime;

class Tournament {
	private int $id;
	private string $name;
	private string $description;
	private DateTime $start;
	private DateTime $end;
	private string $owner;
	
	public function __construct(int $id, string $name, ?string $description, DateTime $start, DateTime $end, string $owner) {
		$this->id = $id;
		$this->name = $name;
		$this->description = $description ?? '';
		$this->start = $start;
		$this->end = $end;
		$this->owner = $owner;
	}
	
	public function getId(): int {
		return $this->id;
	}
	
	public function getName(): string {
		return $this->name;
	}
	
	public function getDescription(): string {
		return $this->description;
	}
	
	public function getStart(): DateTime {
		return $this->start;
	}
	
	public function getEnd(): DateTime {
		return $this->end;
	}
	
	public function getOwner(): string {
		return $this->owner;
	}
}
