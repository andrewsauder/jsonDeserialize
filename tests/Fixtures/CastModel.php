<?php

namespace andrewsauder\jsonDeserialize\Tests\Fixtures;

use andrewsauder\jsonDeserialize\attributes\jsonSerializeCast;
use andrewsauder\jsonDeserialize\jsonDeserialize;
use andrewsauder\jsonDeserialize\jsonSerializeCastType;

class CastModel extends jsonDeserialize {

	#[jsonSerializeCast( jsonSerializeCastType::string )]
	public int $intAsString = 0;

	#[jsonSerializeCast( jsonSerializeCastType::int )]
	public string $stringAsInt = '0';

	#[jsonSerializeCast( jsonSerializeCastType::float )]
	public string $stringAsFloat = '0';

	#[jsonSerializeCast( jsonSerializeCastType::bool )]
	public int $intAsBool = 0;

	public int $untouched = 0;

}
