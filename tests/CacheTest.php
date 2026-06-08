<?php

namespace andrewsauder\jsonDeserialize\Tests;

use andrewsauder\jsonDeserialize\cache;
use PHPUnit\Framework\TestCase;

class CacheTest extends TestCase {

	public function testGetClassExistsReturnsTrueForRealClass(): void {
		$this->assertTrue( cache::getClassExists( \DateTime::class ) );
	}


	public function testGetClassExistsReturnsFalseForFakeClass(): void {
		$this->assertFalse( cache::getClassExists( '\This\Does\Not\Exist' ) );
	}


	public function testSetClassExistsOverridesCache(): void {
		cache::setClassExists( '\Custom\Cached\Class', true );

		$this->assertTrue( cache::getClassExists( '\Custom\Cached\Class' ) );

		cache::setClassExists( '\Custom\Cached\Class', false );

		$this->assertFalse( cache::getClassExists( '\Custom\Cached\Class' ) );
	}


	public function testRepeatedCallsReturnSameValue(): void {
		$first  = cache::getClassExists( \DateTime::class );
		$second = cache::getClassExists( \DateTime::class );

		$this->assertSame( $first, $second );
	}

}
