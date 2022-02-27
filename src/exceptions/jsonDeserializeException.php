<?php
namespace andrewsauder\jsonDeserialize\exceptions;


class jsonDeserializeException extends \Exception {

	// Redefine the exception so message isn't optional

	/** @noinspection PhpPureAttributeCanBeAddedInspection */
	public function __construct( $message, $code = 0, \Exception|\Error|\ErrorException $previous = null ) {

		// make sure everything is assigned properly
		parent::__construct( $message, $code, $previous );
	}

}