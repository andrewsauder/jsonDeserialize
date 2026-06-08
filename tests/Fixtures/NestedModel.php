<?php

namespace andrewsauder\jsonDeserialize\Tests\Fixtures;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class NestedModel extends jsonDeserialize {

	public string $title = '';

	public ChildModel $child;

	/** @var \andrewsauder\jsonDeserialize\Tests\Fixtures\ChildModel[] */
	public array $children = [];

}
