<?php

namespace andrewsauder\jsonDeserialize\Tests;

use andrewsauder\jsonDeserialize\config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase {

	protected function tearDown(): void {
		config::setDebugLogging( false );
		config::setLogJsonMissingProperty( true );
		config::setLogClassPropertyMissingType( true );
		config::setLogClassMissingProperty( true );
	}


	public function testDebugLoggingDefaultsToFalse(): void {
		$this->assertFalse( config::isDebugLogging() );
	}


	public function testSetDebugLogging(): void {
		config::setDebugLogging( true );
		$this->assertTrue( config::isDebugLogging() );

		config::setDebugLogging( false );
		$this->assertFalse( config::isDebugLogging() );
	}


	public function testGetDebugLogChannel(): void {
		$this->assertSame( 'andrewsauder.jsonDeserialize', config::getDebugLogChannel() );
	}


	public function testSetDebugLogPathTrimsSlashes(): void {
		config::setDebugLogPath( '/tmp/' );

		$this->assertSame( 'tmp', config::getDebugLogPath() );

		config::setDebugLogPath( '' );
	}


	public function testLogJsonMissingPropertyFlag(): void {
		$this->assertTrue( config::isLogJsonMissingProperty() );

		config::setLogJsonMissingProperty( false );
		$this->assertFalse( config::isLogJsonMissingProperty() );
	}


	public function testLogClassPropertyMissingTypeFlag(): void {
		$this->assertTrue( config::isLogClassPropertyMissingType() );

		config::setLogClassPropertyMissingType( false );
		$this->assertFalse( config::isLogClassPropertyMissingType() );
	}


	public function testLogClassMissingPropertyFlag(): void {
		$this->assertTrue( config::isLogClassMissingProperty() );

		config::setLogClassMissingProperty( false );
		$this->assertFalse( config::isLogClassMissingProperty() );
	}


	public function testGetDebugLoggerCreatesLogger(): void {
		$tmpDir = sys_get_temp_dir() . '/json-deserialize-test-' . uniqid();
		mkdir( $tmpDir );

		try {
			config::setDebugLogPath( $tmpDir );
			$logger = config::getDebugLogger();
			$this->assertInstanceOf( \Monolog\Logger::class, $logger );
			$this->assertSame( 'andrewsauder.jsonDeserialize', $logger->getName() );
		}
		finally {
			array_map( 'unlink', glob( $tmpDir . '/*' ) ?: [] );
			rmdir( $tmpDir );
		}
	}

}
