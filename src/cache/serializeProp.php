<?php

namespace andrewsauder\jsonDeserialize\cache;

use andrewsauder\jsonDeserialize\jsonSerializeCastType;

final class serializeProp {

	public string $name;
	public ?string $dateFormat; // from #[jsonSerializeDateTimeFormat], null => DATE_ATOM
	/** callable(object $o): mixed */
	public ?jsonSerializeCastType $castType = null;
	public \Closure $getter; // avoids ReflectionProperty per call
	public function __construct(string $name, ?string $dateFormat, ?jsonSerializeCastType $castType, \Closure $getter) {
		$this->name = $name;
		$this->dateFormat = $dateFormat;
		$this->getter = $getter;
		$this->castType = $castType;
	}
}
