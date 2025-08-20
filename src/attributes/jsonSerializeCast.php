<?php

namespace andrewsauder\jsonDeserialize\attributes;

use andrewsauder\jsonDeserialize\jsonSerializeCastType;
use Attribute;

#[Attribute( Attribute::TARGET_PROPERTY )]
abstract class jsonSerializeCast {

	public jsonSerializeCastType $type;

	public function __construct( jsonSerializeCastType $type ) {
		$this->type = $type;
	}

}
