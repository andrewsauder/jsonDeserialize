<?php

namespace andrewsauder\jsonDeserialize\Tests;

use andrewsauder\jsonDeserialize\Tests\Fixtures\TypedArrayModel;
use PHPUnit\Framework\TestCase;

class TypedArrayTest extends TestCase {

	public function testDeserializesStringArrayFromDocComment(): void {
		$json = '{"strings":["a","b","c"]}';

		$model = TypedArrayModel::jsonDeserialize( $json );

		$this->assertSame( [ 'a', 'b', 'c' ], $model->strings );
	}


	public function testDeserializesIntArrayFromDocComment(): void {
		$json = '{"ints":[1,2,3]}';

		$model = TypedArrayModel::jsonDeserialize( $json );

		$this->assertSame( [ 1, 2, 3 ], $model->ints );
	}


	public function testDeserializesArrayGenericSyntax(): void {
		$json = '{"stringsAlt":["x","y"]}';

		$model = TypedArrayModel::jsonDeserialize( $json );

		$this->assertSame( [ 'x', 'y' ], $model->stringsAlt );
	}


	public function testDeserializesArrayMapSyntax(): void {
		$json = '{"map":{"a":1,"b":2}}';

		$model = TypedArrayModel::jsonDeserialize( $json );

		$this->assertSame( [ 'a' => 1, 'b' => 2 ], $model->map );
	}


	public function testDeserializesUntypedArray(): void {
		$json = '{"untyped":[1,"two",3.0]}';

		$model = TypedArrayModel::jsonDeserialize( $json );

		$this->assertCount( 3, $model->untyped );
		$this->assertSame( 1, $model->untyped[ 0 ] );
		$this->assertSame( 'two', $model->untyped[ 1 ] );
		$this->assertSame( 3.0, $model->untyped[ 2 ] );
	}


	public function testCastsIntArrayFromStrings(): void {
		$json = '{"ints":["1","2","3"]}';

		$model = TypedArrayModel::jsonDeserialize( $json );

		$this->assertSame( [ 1, 2, 3 ], $model->ints );
	}


	public function testSerializesArrayProperty(): void {
		$model          = new TypedArrayModel();
		$model->strings = [ 'a', 'b' ];
		$model->ints    = [ 4, 5, 6 ];

		$encoded = json_encode( $model );
		$decoded = json_decode( (string)$encoded, true );

		$this->assertSame( [ 'a', 'b' ], $decoded[ 'strings' ] );
		$this->assertSame( [ 4, 5, 6 ], $decoded[ 'ints' ] );
	}

}
