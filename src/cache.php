<?php

namespace andrewsauder\jsonDeserialize;

class cache {

	/** @var bool[] $classExists Array key is classFQN, bool is check that class exists */
	private static array $classExists = [];


	public static function getClassExists( string $classFQN ): bool {
		if( isset( self::$classExists[ $classFQN ] ) ) {
			return self::$classExists[ $classFQN ];
		}
		else {
			self::setClassExists( $classFQN, class_exists( $classFQN ) );
		}
		return self::$classExists[ $classFQN ];
	}


	public static function setClassExists( string $classFQN, bool $classExists ): void {
		self::$classExists[ $classFQN ] = $classExists;
	}

}