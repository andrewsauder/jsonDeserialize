<?php

namespace andrewsauder\jsonDeserialize\cache;

final class serializePlan {

	/** @var array<string, \andrewsauder\jsonDeserialize\cache\serializeProp> ordered list of properties to export */
	public array $props;
	public bool  $skipProcessing;


	public function __construct( array $props, bool $skipProcessing = false ) {
		$this->props = $props;
		$this->skipProcessing = $skipProcessing;
	}

}
