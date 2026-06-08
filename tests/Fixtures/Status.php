<?php

namespace andrewsauder\jsonDeserialize\Tests\Fixtures;

enum Status: string {
	case Active   = 'active';
	case Inactive = 'inactive';
	case Pending  = 'pending';
}
