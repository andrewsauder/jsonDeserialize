<?php

namespace andrewsauder\jsonDeserialize\Tests\Fixtures;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class UnionTypeModel extends jsonDeserialize {

	public int|string $anything = '';

}
