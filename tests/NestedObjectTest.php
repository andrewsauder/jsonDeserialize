<?php

namespace andrewsauder\jsonDeserialize\Tests;

use andrewsauder\jsonDeserialize\Tests\Fixtures\ChildModel;
use andrewsauder\jsonDeserialize\Tests\Fixtures\NestedModel;
use PHPUnit\Framework\TestCase;

class NestedObjectTest extends TestCase {

	public function testDeserializesNestedJsonDeserializeClass(): void {
		$json = '{"title":"Parent","child":{"name":"kid","value":7}}';

		$model = NestedModel::jsonDeserialize( $json );

		$this->assertSame( 'Parent', $model->title );
		$this->assertInstanceOf( ChildModel::class, $model->child );
		$this->assertSame( 'kid', $model->child->name );
		$this->assertSame( 7, $model->child->value );
	}


	public function testDeserializesArrayOfNestedClasses(): void {
		$json = '{"children":[{"name":"a","value":1},{"name":"b","value":2}]}';

		$model = NestedModel::jsonDeserialize( $json );

		$this->assertCount( 2, $model->children );
		$this->assertInstanceOf( ChildModel::class, $model->children[ 0 ] );
		$this->assertSame( 'a', $model->children[ 0 ]->name );
		$this->assertSame( 1, $model->children[ 0 ]->value );
		$this->assertSame( 'b', $model->children[ 1 ]->name );
		$this->assertSame( 2, $model->children[ 1 ]->value );
	}


	public function testSerializesNestedObjects(): void {
		$model        = new NestedModel();
		$model->title = 'top';

		$child           = new ChildModel();
		$child->name     = 'inner';
		$child->value    = 99;
		$model->child    = $child;
		$model->children = [ $child ];

		$encoded = json_encode( $model );
		$decoded = json_decode( (string)$encoded, true );

		$this->assertSame( 'top', $decoded[ 'title' ] );
		$this->assertSame( 'inner', $decoded[ 'child' ][ 'name' ] );
		$this->assertSame( 99, $decoded[ 'child' ][ 'value' ] );
		$this->assertSame( 'inner', $decoded[ 'children' ][ 0 ][ 'name' ] );
	}

}
