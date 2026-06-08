<?php

namespace andrewsauder\jsonDeserialize\Tests\Fixtures;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class TypedArrayModel extends jsonDeserialize {

	/** @var string[] */
	public array $strings = [];

	/** @var int[] */
	public array $ints = [];

	/** @var array<string> */
	public array $stringsAlt = [];

	/** @var array<string, int> */
	public array $map = [];

	public array $untyped = [];

}
