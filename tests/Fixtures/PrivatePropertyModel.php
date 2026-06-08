<?php

namespace andrewsauder\jsonDeserialize\Tests\Fixtures;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class PrivatePropertyModel extends jsonDeserialize {

	public string $publicField = '';

	private string $privateField = 'private';

	protected string $protectedField = 'protected';


	public function getPrivateField(): string {
		return $this->privateField;
	}


	public function getProtectedField(): string {
		return $this->protectedField;
	}

}
