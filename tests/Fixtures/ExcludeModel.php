<?php

namespace andrewsauder\jsonDeserialize\Tests\Fixtures;

use andrewsauder\jsonDeserialize\attributes\excludeJsonDeserialize;
use andrewsauder\jsonDeserialize\attributes\excludeJsonSerialize;
use andrewsauder\jsonDeserialize\jsonDeserialize;

class ExcludeModel extends jsonDeserialize {

	public string $included = '';

	#[excludeJsonDeserialize]
	public string $notDeserialized = 'default';

	#[excludeJsonSerialize]
	public string $notSerialized = 'secret';

}
