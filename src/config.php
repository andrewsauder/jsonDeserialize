<?php

namespace andrewsauder\jsonDeserialize;


class config {

	private static bool   $debugLogging = false;

	private static string $debugLogPath = '';

	private static string $debugLogChannel = 'andrewsauder.jsonDeserialize';
	private static string $debugLogFilePath = '';
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
		self::$debugLogPath = trim( $debugLogPath, '/\\' );
		self::$debugLogFilePath = self::$debugLogPath . '/' . self::getDebugLogChannel() . '.log';
	}


	/**
	 * @return string
	 */
	public static function getDebugLogChannel() : string {
		return self::$debugLogChannel;
	}


	/**
	 * @return string
	 */
	public static function getDebugLogFilePath() : string {
		return self::$debugLogFilePath;
	}


	/**
	 * @return \Monolog\Logger
	 */
	public static function getDebugLogger() : \Monolog\Logger {
		if(!isset(self::$debugLogger)) {
			self::createDebugLogger();
		}
		return self::$debugLogger;
	}


	/**
	 * @param  \Monolog\Logger  $debugLogger
	 */
	public static function createDebugLogger(  ) : void {
		self::$debugLogger = new \Monolog\Logger( self::getDebugLogChannel() );
		self::$debugLogger->pushHandler( new \Monolog\Handler\StreamHandler( config::getDebugLogFilePath(), \Monolog\Logger::DEBUG ) );
	}

}