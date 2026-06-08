<?php

namespace andrewsauder\jsonDeserialize\Tests\Fixtures;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class NullableModel extends jsonDeserialize {

	public ?string $nullableString = null;

	public ?ChildModel $nullableChild = null;

	public ?int $nullableInt = null;

}
