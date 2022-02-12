<?php

namespace andrewsauder\jsonDeserialize\attributes;


use Attribute;


#[Attribute( Attribute::TARGET_PROPERTY )]
class excludeJsonDeserialize {

	public function __construct() {
	}

}