<?php

namespace andrewsauder\jsonDeserialize\Tests\Fixtures;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class SimpleModel extends jsonDeserialize {

	public int $intVal = 0;

	public string $stringVal = '';

	public float $floatVal = 0.0;

	public bool $boolVal = false;

}
