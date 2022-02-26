<?php

namespace andrewsauder\jsonDeserialize;


class config {

	private static bool            $debugLogging               = false;

	private static bool            $logJsonMissingProperty      = true;

	private static bool            $logClassPropertyMissingType = true;

	private static bool            $logClassMissingProperty     = true;

	private static string          $debugLogPath               = '';

	private static string          $debugLogChannel            = 'andrewsauder.jsonDeserialize';

	private static string          $debugLogFilePath           = '';

	private static \Monolog\Logger $debugLogger;


	/**
	 * @return bool
	 */
	public static function isDebugLogging() : bool {
		return self::$debugLogging;
	}


	/**
	 * @param  bool  $debugLogging
	 */
	public static function setDebugLogging( bool $debugLogging ) : void {
		self::$debugLogging = $debugLogging;
	}


	/**
	 * @return string
	 */
	public static function getDebugLogPath() : string {
		return self::$debugLogPath;
	}


	/**
	 * @param  string  $debugLogPath
	 */
	public static function setDebugLogPath( string $debugLogPath ) : void {
		self::$debugLogPath     = trim( $debugLogPath, '/\\' );
		self::$debugLogFilePath = self::$debugLogPath . '/' . self::getDebugLogChannel() . '.log';
	}


	/**
	 * @return string
	 */
	public static function getDebugLogChannel() : string {
		return self::$debugLogChannel;
	}


	/**
	 * @return bool
	 */
	public static function isLogJsonMissingProperty() : bool {
		return self::$logJsonMissingProperty;
	}


	/**
	 * @param  bool  $logJsonMissingProperty
	 */
	public static function setLogJsonMissingProperty( bool $logJsonMissingProperty ) : void {
		self::$logJsonMissingProperty = $logJsonMissingProperty;
	}


	/**
	 * @return bool
	 */
	public static function isLogClassPropertyMissingType() : bool {
		return self::$logClassPropertyMissingType;
	}


	/**
	 * @param  bool  $logClassPropertyMissingType
	 */
	public static function setLogClassPropertyMissingType( bool $logClassPropertyMissingType ) : void {
		self::$logClassPropertyMissingType = $logClassPropertyMissingType;
	}


	/**
	 * @return bool
	 */
	public static function isLogClassMissingProperty() : bool {
		return self::$logClassMissingProperty;
	}


	/**
	 * @param  bool  $logClassMissingProperty
	 */
	public static function setLogClassMissingProperty( bool $logClassMissingProperty ) : void {
		self::$logClassMissingProperty = $logClassMissingProperty;
	}


	/**
	 * @return string
	 */
	private static function getDebugLogFilePath() : string {
		return self::$debugLogFilePath;
	}


	/**
	 * @return \Monolog\Logger
	 */
	public static function getDebugLogger() : \Monolog\Logger {
		if( !isset( self::$debugLogger ) ) {
			self::createDebugLogger();
		}

		return self::$debugLogger;
	}


	private static function createDebugLogger() : void {
		self::$debugLogger = new \Monolog\Logger( self::getDebugLogChannel() );
		self::$debugLogger->pushHandler( new \Monolog\Handler\StreamHandler( self::getDebugLogFilePath(), \Monolog\Logger::DEBUG ) );
	}

}