<?php

namespace andrewsauder\jsonDeserialize\Tests;

use andrewsauder\jsonDeserialize\Tests\Fixtures\CastModel;
use PHPUnit\Framework\TestCase;

class JsonSerializeCastTest extends TestCase {

	public function testCastsIntPropertyToStringOnSerialize(): void {
		$model              = new CastModel();
		$model->intAsString = 123;

		$encoded = json_encode( $model );
		$decoded = json_decode( (string)$encoded, true );

		$this->assertSame( '123', $decoded[ 'intAsString' ] );
	}


	public function testCastsStringPropertyToIntOnSerialize(): void {
		$model              = new CastModel();
		$model->stringAsInt = '456';

		$encoded = json_encode( $model );
		$decoded = json_decode( (string)$encoded, true );

		$this->assertSame( 456, $decoded[ 'stringAsInt' ] );
	}


	public function testCastsStringPropertyToFloatOnSerialize(): void {
		$model                = new CastModel();
		$model->stringAsFloat = '3.14';

		$encoded = json_encode( $model );
		$decoded = json_decode( (string)$encoded, true );

		$this->assertSame( 3.14, $decoded[ 'stringAsFloat' ] );
	}


	public function testCastsIntPropertyToBoolOnSerialize(): void {
		$model            = new CastModel();
		$model->intAsBool = 1;

		$encoded = json_encode( $model );
		$decoded = json_decode( (string)$encoded, true );

		$this->assertTrue( $decoded[ 'intAsBool' ] );
	}


	public function testCastsZeroToFalseOnSerialize(): void {
		$model            = new CastModel();
		$model->intAsBool = 0;

		$encoded = json_encode( $model );
		$decoded = json_decode( (string)$encoded, true );

		$this->assertFalse( $decoded[ 'intAsBool' ] );
	}


	public function testPropertyWithoutCastIsUntouched(): void {
		$model            = new CastModel();
		$model->untouched = 99;

		$encoded = json_encode( $model );
		$decoded = json_decode( (string)$encoded, true );

		$this->assertSame( 99, $decoded[ 'untouched' ] );
	}

}
