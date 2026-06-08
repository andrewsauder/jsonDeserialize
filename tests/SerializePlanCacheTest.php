<?php

namespace andrewsauder\jsonDeserialize\Tests;

use andrewsauder\jsonDeserialize\cache\serializePlan;
use andrewsauder\jsonDeserialize\cache\serializePlanCache;
use andrewsauder\jsonDeserialize\cache\serializeProp;
use andrewsauder\jsonDeserialize\jsonSerializeCastType;
use andrewsauder\jsonDeserialize\Tests\Fixtures\CastModel;
use andrewsauder\jsonDeserialize\Tests\Fixtures\DateTimeModel;
use andrewsauder\jsonDeserialize\Tests\Fixtures\ExcludeModel;
use andrewsauder\jsonDeserialize\Tests\Fixtures\PrivatePropertyModel;
use andrewsauder\jsonDeserialize\Tests\Fixtures\SimpleModel;
use andrewsauder\jsonDeserialize\Tests\Fixtures\SkipProcessingModel;
use PHPUnit\Framework\TestCase;

class SerializePlanCacheTest extends TestCase {

	public function testPlanForObjectReturnsSerializePlan(): void {
		$plan = serializePlanCache::for( new SimpleModel() );

		$this->assertInstanceOf( serializePlan::class, $plan );
	}


	public function testPlanForClassNameReturnsSerializePlan(): void {
		$plan = serializePlanCache::for( SimpleModel::class );

		$this->assertInstanceOf( serializePlan::class, $plan );
	}


	public function testPlanIncludesPublicProperties(): void {
		$plan = serializePlanCache::for( SimpleModel::class );

		$this->assertArrayHasKey( 'intVal', $plan->props );
		$this->assertArrayHasKey( 'stringVal', $plan->props );
		$this->assertArrayHasKey( 'floatVal', $plan->props );
		$this->assertArrayHasKey( 'boolVal', $plan->props );
	}


	public function testPlanExcludesNonPublicProperties(): void {
		$plan = serializePlanCache::for( PrivatePropertyModel::class );

		$this->assertArrayHasKey( 'publicField', $plan->props );
		$this->assertArrayNotHasKey( 'privateField', $plan->props );
		$this->assertArrayNotHasKey( 'protectedField', $plan->props );
	}


	public function testPlanExcludesExcludeJsonSerializeProperties(): void {
		$plan = serializePlanCache::for( ExcludeModel::class );

		$this->assertArrayHasKey( 'included', $plan->props );
		$this->assertArrayHasKey( 'notDeserialized', $plan->props );
		$this->assertArrayNotHasKey( 'notSerialized', $plan->props );
	}


	public function testPlanCapturesDateFormat(): void {
		$plan = serializePlanCache::for( DateTimeModel::class );

		$this->assertNull( $plan->props[ 'createdAt' ]->dateFormat );
		$this->assertSame( 'Y-m-d', $plan->props[ 'birthDate' ]->dateFormat );
	}


	public function testPlanCapturesCastType(): void {
		$plan = serializePlanCache::for( CastModel::class );

		$this->assertSame( jsonSerializeCastType::string, $plan->props[ 'intAsString' ]->castType );
		$this->assertSame( jsonSerializeCastType::int, $plan->props[ 'stringAsInt' ]->castType );
		$this->assertSame( jsonSerializeCastType::float, $plan->props[ 'stringAsFloat' ]->castType );
		$this->assertSame( jsonSerializeCastType::bool, $plan->props[ 'intAsBool' ]->castType );
		$this->assertNull( $plan->props[ 'untouched' ]->castType );
	}


	public function testPlanSkipProcessingFlag(): void {
		$noSkip = serializePlanCache::for( SimpleModel::class );
		$skip   = serializePlanCache::for( SkipProcessingModel::class );

		$this->assertFalse( $noSkip->skipProcessing );
		$this->assertTrue( $skip->skipProcessing );
	}


	public function testRepeatedCallsReturnSamePlanInstance(): void {
		$first  = serializePlanCache::for( SimpleModel::class );
		$second = serializePlanCache::for( SimpleModel::class );

		$this->assertSame( $first, $second );
	}


	public function testGetterClosureReturnsPropertyValue(): void {
		$model            = new SimpleModel();
		$model->intVal    = 17;
		$model->stringVal = 'abc';

		$plan          = serializePlanCache::for( SimpleModel::class );
		$intGetter     = $plan->props[ 'intVal' ]->getter;
		$stringGetter  = $plan->props[ 'stringVal' ]->getter;

		$this->assertSame( 17, $intGetter( $model ) );
		$this->assertSame( 'abc', $stringGetter( $model ) );
	}


	public function testSerializePropConstructor(): void {
		$getter = static fn( object $o ) => $o;
		$prop   = new serializeProp( 'foo', 'Y-m-d', jsonSerializeCastType::int, $getter );

		$this->assertSame( 'foo', $prop->name );
		$this->assertSame( 'Y-m-d', $prop->dateFormat );
		$this->assertSame( jsonSerializeCastType::int, $prop->castType );
		$this->assertSame( $getter, $prop->getter );
	}

}
