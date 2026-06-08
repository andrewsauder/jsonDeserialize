<?php

namespace andrewsauder\jsonDeserialize\Tests;

use andrewsauder\jsonDeserialize\Tests\Fixtures\DateTimeModel;
use andrewsauder\jsonDeserialize\Tests\Fixtures\ExcludeModel;
use PHPUnit\Framework\TestCase;

class AttributesTest extends TestCase {

	public function testExcludeJsonDeserializeKeepsDefault(): void {
		$json = '{"included":"yes","notDeserialized":"override"}';

		$model = ExcludeModel::jsonDeserialize( $json );

		$this->assertSame( 'yes', $model->included );
		$this->assertSame( 'default', $model->notDeserialized );
	}


	public function testExcludeJsonSerializeOmitsField(): void {
		$model                  = new ExcludeModel();
		$model->included        = 'yes';
		$model->notDeserialized = 'visible';
		$model->notSerialized   = 'hidden';

		$encoded = json_encode( $model );
		$decoded = json_decode( (string)$encoded, true );

		$this->assertArrayHasKey( 'included', $decoded );
		$this->assertArrayHasKey( 'notDeserialized', $decoded );
		$this->assertArrayNotHasKey( 'notSerialized', $decoded );
	}


	public function testDateTimeDefaultFormatIsDateAtom(): void {
		$model            = new DateTimeModel();
		$model->createdAt = new \DateTime( '2024-05-15T10:30:00+00:00' );
		$model->birthDate = new \DateTime( '1990-01-02T03:04:05+00:00' );

		$encoded = json_encode( $model );
		$decoded = json_decode( (string)$encoded, true );

		$this->assertSame( '2024-05-15T10:30:00+00:00', $decoded[ 'createdAt' ] );
	}


	public function testDateTimeCustomFormatAttribute(): void {
		$model            = new DateTimeModel();
		$model->createdAt = new \DateTime( '2024-05-15T10:30:00+00:00' );
		$model->birthDate = new \DateTime( '1990-01-02T03:04:05+00:00' );

		$encoded = json_encode( $model );
		$decoded = json_decode( (string)$encoded, true );

		$this->assertSame( '1990-01-02', $decoded[ 'birthDate' ] );
	}


	public function testDateTimeDeserialization(): void {
		$json = '{"createdAt":"2024-01-01T12:00:00+00:00","birthDate":"1990-06-15T00:00:00+00:00"}';

		$model = DateTimeModel::jsonDeserialize( $json );

		$this->assertInstanceOf( \DateTime::class, $model->createdAt );
		$this->assertSame( '2024-01-01T12:00:00+00:00', $model->createdAt->format( DATE_ATOM ) );
		$this->assertInstanceOf( \DateTime::class, $model->birthDate );
	}

}
