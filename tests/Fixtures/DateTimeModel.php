<?php

namespace andrewsauder\jsonDeserialize\Tests\Fixtures;

use andrewsauder\jsonDeserialize\attributes\jsonSerializeDateTimeFormat;
use andrewsauder\jsonDeserialize\jsonDeserialize;

class DateTimeModel extends jsonDeserialize {

	public \DateTime $createdAt;

	#[jsonSerializeDateTimeFormat( 'Y-m-d' )]
	public \DateTime $birthDate;

}
