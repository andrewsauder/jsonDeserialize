<?php

namespace andrewsauder\jsonDeserialize\Tests;

use andrewsauder\jsonDeserialize\Tests\Fixtures\NullableArrayModel;
use andrewsauder\jsonDeserialize\Tests\Fixtures\NullableModel;
use andrewsauder\jsonDeserialize\Tests\Fixtures\UnionTypeModel;
use andrewsauder\jsonDeserialize\Tests\Fixtures\UntypedModel;
use PHPUnit\Framework\TestCase;

class EdgeCasesTest extends TestCase {

	public function testNullableStringWithNull(): void {
		$json = '{"nullableString":null}';

		$model = NullableModel::jsonDeserialize( $json );

		$this->assertNull( $model->nullableString );
	}


	public function testNullableStringWithValue(): void {
		$json = '{"nullableString":"hello"}';

		$model = NullableModel::jsonDeserialize( $json );

		$this->assertSame( 'hello', $model->nullableString );
	}


	public function testNullableChildObjectWithNull(): void {
		$json = '{"nullableChild":null}';

		$model = NullableModel::jsonDeserialize( $json );

		$this->assertNull( $model->nullableChild );
	}


	public function testNullableIntWithZero(): void {
		$json = '{"nullableInt":0}';

		$model = NullableModel::jsonDeserialize( $json );

		$this->assertSame( 0, $model->nullableInt );
	}


	public function testNullableArrayWithNullValuesInside(): void {
		$json = '{"children":[{"name":"a","value":1},null,{"name":"c","value":3}]}';

		$model = NullableArrayModel::jsonDeserialize( $json );

		$this->assertNotNull( $model->children );
		$this->assertCount( 3, $model->children );
		$this->assertSame( 'a', $model->children[ 0 ]->name );
		$this->assertNull( $model->children[ 1 ] );
		$this->assertSame( 'c', $model->children[ 2 ]->name );
	}


	public function testPropertyWithoutTypeIsAssignedDirectly(): void {
		$json = '{"whatever":{"any":"thing"},"typed":"yes"}';

		$model = UntypedModel::jsonDeserialize( $json );

		$this->assertSame( 'yes', $model->typed );
		$this->assertInstanceOf( \stdClass::class, $model->whatever );
		$this->assertSame( 'thing', $model->whatever->any );
	}


	public function testUnionTypeProperty(): void {
		$json = '{"anything":"a string"}';

		$model = UnionTypeModel::jsonDeserialize( $json );

		$this->assertSame( 'a string', $model->anything );
	}

}
