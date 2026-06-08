<?php

namespace andrewsauder\jsonDeserialize\Tests;

use andrewsauder\jsonDeserialize\Tests\Fixtures\HookModel;
use PHPUnit\Framework\TestCase;

class HooksTest extends TestCase {

	protected function setUp(): void {
		HookModel::$staticBeforeDeserializeCount = 0;
	}


	public function testBeforeJsonDeserializeIsCalled(): void {
		$json = '{"value":5}';

		HookModel::jsonDeserialize( $json );

		$this->assertSame( 1, HookModel::$staticBeforeDeserializeCount );
	}


	public function testAfterJsonDeserializeIsCalled(): void {
		$json = '{"value":6}';

		$model = HookModel::jsonDeserialize( $json );

		$this->assertTrue( $model->afterDeserializeRan );
		$this->assertSame( 36, $model->squared );
	}


	public function testAfterJsonDeserializeIsCalledForEachArrayItem(): void {
		$json    = '[{"value":2},{"value":3},{"value":4}]';
		$decoded = json_decode( $json, false );

		$models = HookModel::jsonDeserialize( $decoded );

		$this->assertIsArray( $models );
		$this->assertCount( 3, $models );
		foreach( $models as $m ) {
			$this->assertTrue( $m->afterDeserializeRan );
		}
		$this->assertSame( 4, $models[ 0 ]->squared );
		$this->assertSame( 9, $models[ 1 ]->squared );
		$this->assertSame( 16, $models[ 2 ]->squared );
	}


	public function testBeforeJsonSerializeIsCalled(): void {
		$model        = new HookModel();
		$model->value = 7;

		$encoded = json_encode( $model );
		$decoded = json_decode( (string)$encoded, true );

		$this->assertTrue( $decoded[ 'beforeSerializeRan' ] );
		$this->assertSame( 49, $decoded[ 'squared' ] );
	}


	public function testAfterJsonSerializeIsCalled(): void {
		$model        = new HookModel();
		$model->value = 3;

		$encoded = json_encode( $model );
		$decoded = json_decode( (string)$encoded, true );

		$this->assertArrayHasKey( 'extra', $decoded );
		$this->assertSame( 'added-by-hook', $decoded[ 'extra' ] );
	}

}
