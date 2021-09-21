<?php

namespace model;

use DateTime;

class Person {
	private int $id;
	private string $firstname;
	private string $name;
	private DateTime $birthday;
	private int $gender;
	
	public function __construct(int $id, string $firstname, string $name, DateTime $birthday, int $gender) {
		$this->id = $id;
		$this->firstname = $firstname;
		$this->name = $name;
		$this->birthday = $birthday;
		$this->gender = $gender;
	}
	
	public function getId(): int {
		return $this->id;
	}
	
	public function getFirstname(): string {
		return $this->firstname;
	}
	
	public function getName(): string {
		return $this->name;
	}
	
	public function getBirthday(): DateTime {
		return $this->birthday;
	}
	
	public function getGender(): int {
		return $this->gender;
	}
	
	public function getDisplayName(): string {
		return "$this->name, $this->firstname";
	}
}
