<?php

namespace andrewsauder\jsonDeserialize\Tests\Fixtures;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class NullableArrayModel extends jsonDeserialize {

	/** @var ?\andrewsauder\jsonDeserialize\Tests\Fixtures\ChildModel[] */
	public ?array $children = null;

}
