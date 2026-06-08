<?php

namespace andrewsauder\jsonDeserialize\Tests;

use andrewsauder\jsonDeserialize\Tests\Fixtures\SkipProcessingModel;
use PHPUnit\Framework\TestCase;

class SkipJsonSerializeProcessingTest extends TestCase {

	public function testSkipBypassesBeforeJsonSerializeHook(): void {
		$model        = new SkipProcessingModel();
		$model->name  = 'no-hooks';
		$model->count = 5;

		$encoded = json_encode( $model );
		$decoded = json_decode( (string)$encoded, true );

		$this->assertFalse( $decoded[ 'beforeSerializeHookRan' ] );
	}


	public function testSkipBypassesAfterJsonSerializeHook(): void {
		$model        = new SkipProcessingModel();
		$model->name  = 'no-hooks';
		$model->count = 5;

		$encoded = json_encode( $model );
		$decoded = json_decode( (string)$encoded, true );

		$this->assertArrayNotHasKey( 'extra', $decoded );
	}


	public function testSkipReturnsObjectVars(): void {
		$model        = new SkipProcessingModel();
		$model->name  = 'verbatim';
		$model->count = 42;

		$encoded = json_encode( $model );
		$decoded = json_decode( (string)$encoded, true );

		$this->assertSame( 'verbatim', $decoded[ 'name' ] );
		$this->assertSame( 42, $decoded[ 'count' ] );
	}

}
