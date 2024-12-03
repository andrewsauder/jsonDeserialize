<?php

namespace andrewsauder\jsonDeserialize\attributes;

use Attribute;

#[Attribute( Attribute::TARGET_PROPERTY )]
class jsonSerializeDateTimeFormat {

	/** @var string PHP DateTime format string */
	public string $datetimeFormat = '';


	public function __construct( string $datetimeFormat ) {
		$this->datetimeFormat = $datetimeFormat;
	}

}
