<?php

namespace andrewsauder\jsonDeserialize\Tests\Fixtures;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class HookModel extends jsonDeserialize {

	public int $value = 0;

	public int $squared = 0;

	public bool $afterDeserializeRan = false;

	public bool $beforeSerializeRan = false;

	public static int $staticBeforeDeserializeCount = 0;

	protected static function _beforeJsonDeserialize( string|\stdClass|array $json ): void {
		self::$staticBeforeDeserializeCount++;
	}

	protected function _afterJsonDeserialize(): void {
		$this->afterDeserializeRan = true;
		$this->squared             = $this->value * $this->value;
	}

	protected function _beforeJsonSerialize(): void {
		$this->beforeSerializeRan = true;
		$this->squared            = $this->value * $this->value;
	}

	protected function _afterJsonSerialize( array $export ): array {
		$export[ 'extra' ] = 'added-by-hook';
		return $export;
	}

}
