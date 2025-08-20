<?php

namespace andrewsauder\jsonDeserialize\cache;

final class serializePlan {

	/** @var array<string, \andrewsauder\jsonDeserialize\cache\serializeProp> ordered list of properties to export */
	public array $props;
	public function __construct(array $props) { $this->props = $props; }
}
