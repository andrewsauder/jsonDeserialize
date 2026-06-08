<?php

namespace andrewsauder\jsonDeserialize\Tests;

use andrewsauder\jsonDeserialize\exceptions\jsonDeserializeException;
use andrewsauder\jsonDeserialize\Tests\Fixtures\EnumModel;
use andrewsauder\jsonDeserialize\Tests\Fixtures\Priority;
use andrewsauder\jsonDeserialize\Tests\Fixtures\Status;
use andrewsauder\jsonDeserialize\Tests\Fixtures\UnitEnumSerializeModel;
use PHPUnit\Framework\TestCase;

class EnumTest extends TestCase {

	public function testDeserializesBackedEnum(): void {
		$json = '{"status":"active"}';

		$model = EnumModel::jsonDeserialize( $json );

		$this->assertSame( Status::Active, $model->status );
	}


	public function testDeserializesBackedEnumPending(): void {
		$json = '{"status":"pending"}';

		$model = EnumModel::jsonDeserialize( $json );

		$this->assertSame( Status::Pending, $model->status );
	}


	public function testInvalidBackedEnumValueThrows(): void {
		$this->expectException( jsonDeserializeException::class );

		$json = '{"status":"not-a-real-value"}';
		EnumModel::jsonDeserialize( $json );
	}


	public function testBackedEnumSerializesAsValue(): void {
		$model         = new EnumModel();
		$model->status = Status::Inactive;

		$encoded = json_encode( $model );
		$decoded = json_decode( (string)$encoded, true );

		$this->assertSame( 'inactive', $decoded[ 'status' ] );
	}


	public function testUnitEnumSerializesAsName(): void {
		$model           = new UnitEnumSerializeModel();
		$model->priority = Priority::High;

		$encoded = json_encode( $model );
		$decoded = json_decode( (string)$encoded, true );

		$this->assertSame( 'High', $decoded[ 'priority' ] );
	}

}
