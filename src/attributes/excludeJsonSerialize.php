<?php

namespace andrewsauder\jsonDeserialize\attributes;


use Attribute;


#[Attribute( Attribute::TARGET_PROPERTY )]
class excludeJsonSerialize {

	public function __construct() {
	}

}