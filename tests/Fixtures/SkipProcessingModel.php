<?php

namespace andrewsauder\jsonDeserialize\Tests\Fixtures;

use andrewsauder\jsonDeserialize\attributes\skipJsonSerializeProcessing;
use andrewsauder\jsonDeserialize\jsonDeserialize;

#[skipJsonSerializeProcessing]
class SkipProcessingModel extends jsonDeserialize {

	public string $name = '';

	public int $count = 0;

	public bool $beforeSerializeHookRan = false;

	protected function _beforeJsonSerialize(): void {
		$this->beforeSerializeHookRan = true;
	}

	protected function _afterJsonSerialize( array $export ): array {
		$export[ 'extra' ] = 'should-not-appear';
		return $export;
	}

}
