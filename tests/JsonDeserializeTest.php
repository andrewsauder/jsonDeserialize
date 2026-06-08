<?php

namespace andrewsauder\jsonDeserialize\Tests;

use andrewsauder\jsonDeserialize\exceptions\jsonDeserializeException;
use andrewsauder\jsonDeserialize\Tests\Fixtures\SimpleModel;
use PHPUnit\Framework\TestCase;

class JsonDeserializeTest extends TestCase {

	public function testDeserializesFromJsonString(): void {
		$json = '{"intVal":42,"stringVal":"hello","floatVal":3.14,"boolVal":true}';

		$model = SimpleModel::jsonDeserialize( $json );

		$this->assertInstanceOf( SimpleModel::class, $model );
		$this->assertSame( 42, $model->intVal );
		$this->assertSame( 'hello', $model->stringVal );
		$this->assertSame( 3.14, $model->floatVal );
		$this->assertTrue( $model->boolVal );
	}


	public function testDeserializesFromStdClass(): void {
		$obj            = new \stdClass();
		$obj->intVal    = 7;
		$obj->stringVal = 'world';
		$obj->floatVal  = 1.5;
		$obj->boolVal   = false;

		$model = SimpleModel::jsonDeserialize( $obj );

		$this->assertInstanceOf( SimpleModel::class, $model );
		$this->assertSame( 7, $model->intVal );
		$this->assertSame( 'world', $model->stringVal );
		$this->assertSame( 1.5, $model->floatVal );
		$this->assertFalse( $model->boolVal );
	}


	public function testDeserializesArrayOfObjects(): void {
		$json    = '[{"intVal":1,"stringVal":"a","floatVal":0.1,"boolVal":true},{"intVal":2,"stringVal":"b","floatVal":0.2,"boolVal":false}]';
		$decoded = json_decode( $json, false );

		$result = SimpleModel::jsonDeserialize( $decoded );

		$this->assertIsArray( $result );
		$this->assertCount( 2, $result );
		$this->assertInstanceOf( SimpleModel::class, $result[ 0 ] );
		$this->assertSame( 1, $result[ 0 ]->intVal );
		$this->assertSame( 'a', $result[ 0 ]->stringVal );
		$this->assertSame( 2, $result[ 1 ]->intVal );
		$this->assertSame( 'b', $result[ 1 ]->stringVal );
	}


	public function testCastsBoolFromStringTrue(): void {
		$obj          = new \stdClass();
		$obj->boolVal = 'true';

		$model = SimpleModel::jsonDeserialize( $obj );

		$this->assertTrue( $model->boolVal );
	}


	public function testCastsBoolFromStringFalse(): void {
		$obj          = new \stdClass();
		$obj->boolVal = 'false';

		$model = SimpleModel::jsonDeserialize( $obj );

		$this->assertFalse( $model->boolVal );
	}


	public function testCastsBoolFromStringOne(): void {
		$obj          = new \stdClass();
		$obj->boolVal = '1';

		$model = SimpleModel::jsonDeserialize( $obj );

		$this->assertTrue( $model->boolVal );
	}


	public function testCastsBoolFromActualBool(): void {
		$obj          = new \stdClass();
		$obj->boolVal = true;

		$model = SimpleModel::jsonDeserialize( $obj );

		$this->assertTrue( $model->boolVal );
	}


	public function testCastsIntFromString(): void {
		$obj         = new \stdClass();
		$obj->intVal = '42';

		$model = SimpleModel::jsonDeserialize( $obj );

		$this->assertSame( 42, $model->intVal );
	}


	public function testMalformedJsonThrows(): void {
		$this->expectException( jsonDeserializeException::class );
		$this->expectExceptionCode( 400 );

		SimpleModel::jsonDeserialize( '{not valid json' );
	}


	public function testIgnoresPropertyMissingFromJson(): void {
		$json = '{"intVal":5}';

		$model = SimpleModel::jsonDeserialize( $json );

		$this->assertSame( 5, $model->intVal );
		$this->assertSame( '', $model->stringVal );
		$this->assertSame( 0.0, $model->floatVal );
		$this->assertFalse( $model->boolVal );
	}


	public function testInjectsUndeclaredJsonProperties(): void {
		$json = '{"intVal":1,"undeclared":"injected"}';

		$model = SimpleModel::jsonDeserialize( $json );

		$this->assertSame( 1, $model->intVal );
		$this->assertSame( 'injected', $model->undeclared );
	}


	public function testSerializesToJson(): void {
		$model            = new SimpleModel();
		$model->intVal    = 9;
		$model->stringVal = 'serialize';
		$model->floatVal  = 2.5;
		$model->boolVal   = true;

		$encoded = json_encode( $model );
		$decoded = json_decode( (string)$encoded, true );

		$this->assertSame( 9, $decoded[ 'intVal' ] );
		$this->assertSame( 'serialize', $decoded[ 'stringVal' ] );
		$this->assertSame( 2.5, $decoded[ 'floatVal' ] );
		$this->assertTrue( $decoded[ 'boolVal' ] );
	}


	public function testRoundTrip(): void {
		$original = '{"intVal":11,"stringVal":"round","floatVal":4.5,"boolVal":false}';
		$model    = SimpleModel::jsonDeserialize( $original );
		$encoded  = json_encode( $model );
		$decoded  = SimpleModel::jsonDeserialize( (string)$encoded );

		$this->assertSame( 11, $decoded->intVal );
		$this->assertSame( 'round', $decoded->stringVal );
		$this->assertSame( 4.5, $decoded->floatVal );
		$this->assertFalse( $decoded->boolVal );
	}

}
