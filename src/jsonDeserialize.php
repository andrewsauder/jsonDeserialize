<?php
namespace andrewsauder\jsonDeserialize;
use andrewsauder\jsonDeserialize\attributes\excludeJsonDeserialize;
use andrewsauder\jsonDeserialize\exceptions\jsonDeserializeException;


abstract class jsonDeserialize
	implements
	\andrewsauder\jsonDeserialize\interfaces\jsonDeserialize {


	private static function jsonDeserializeLog( string $message, array $context=[] ) {
		if( config::isDebugLogging() ) {
			config::getDebugLogger()->debug( $message, $context );
		}
	}

	/**
	 * Initialize from outside object
	 *
	 * @param  string|\stdClass  $json
	 *
	 * @return mixed Instance of the called class
	 * @throws \andrewsauder\jsonDeserialize\exceptions\jsonDeserializeException
	 */
	public static function jsonDeserialize( string|\stdClass $json ) : mixed {
		$calledClassFqn = self::classNameToFqn( get_called_class() );

		//parse the json
		if( is_string( $json ) ) {
			try {
				$json = json_decode( $json, false, 512, JSON_THROW_ON_ERROR );
			}
			catch( \JsonException $e ) {
				throw new jsonDeserializeException( 'Malformed JSON '.$calledClassFqn, 400, $e );
			}
		}

		if( is_array( $json ) ) {
			$objects = [];
			foreach( $json as $stdObject ) {
				$objects[] = self::jsonDeserializeObject( $calledClassFqn, $stdObject );
			}

			return $objects;
		}
		else {
			return self::jsonDeserializeObject( $calledClassFqn, $json );
		}
	}


	/**
	 * @throws \andrewsauder\jsonDeserialize\exceptions\jsonDeserializeException
	 */
	private static function jsonDeserializeObject( string $calledClassFqn, \stdClass $json ) {
		//load new instance of this class
		try {
			$rClass   = new \ReflectionClass( $calledClassFqn );
			$instance = $rClass->newInstance();
		}
		catch( \ReflectionException $e ) {
			throw new jsonDeserializeException( 'Failed to load type ' . $calledClassFqn . ' for deserialization', 500, $e );
		}

		//get properties of the class
		$rProperties = $rClass->getProperties();

		//get the fields not defined on the class
		foreach($json as $key=>$value) {
			if(!$rClass->hasProperty($key)) {
				if( config::isDebugLogging() && config::isLogClassMissingProperty() ) {
					config::getDebugLogger()->debug( $calledClassFqn.'->'.$key.' is not defined on the class. Value will be injected in class with standard JSON decode types.' );
				}
				$instance->$key = $value;
			}
		}

		//load data from $json into the class $instance
		foreach( $rProperties as $rProperty ) {
			$propertyName = $rProperty->getName();

			//exclude if attribute says to
			$attributes = $rProperty->getAttributes( excludeJsonDeserialize::class );
			if( count( $attributes ) > 0 ) {
				continue;
			}

			//if there is not a matching json property, ignore it
			if( !property_exists( $json, $propertyName ) ) {
				if( config::isDebugLogging() && config::isLogJsonMissingProperty() ) {
					config::getDebugLogger()->debug( $rProperty->class.'->'.$propertyName.' not defined JSON source data' );
				}
				continue;
			}

			//get the type of this property
			$rPropertyType = $rProperty->getType();

			if(!isset($rPropertyType)) {
				if( config::isDebugLogging() && config::isLogClassPropertyMissingType() ) {
					config::getDebugLogger()->debug( $rProperty->class.'->'.$propertyName.' does not have a type defined. Value will be injected in class with standard JSON decode types.' );
				}
				$instance->$propertyName = $json->$propertyName;
				continue;
			}

			//if the property is an array, check if the doc comment defines the type
			$propertyIsTypedArray = false;
			if( $rPropertyType->getName() == 'array' ) {
				$arrayType = self::getVarTypeFromDocComment( $rProperty->getDocComment() );
				if( $arrayType != 'array' ) {
					$propertyIsTypedArray = true;
				}
			}

			//load the data from json into the instance of our class
			if( $propertyIsTypedArray ) {
				$instance->$propertyName = [];
				foreach( $json->$propertyName as $key => $jsonItem ) {
					$instance->$propertyName[ $key ] = self::jsonDeserializeDataItem( $instance, $rProperty, $jsonItem, false );
				}
			}
			else {
				$instance->$propertyName = self::jsonDeserializeDataItem( $instance, $rProperty, $json->$propertyName, $rPropertyType->allowsNull() );
			}
		}

		return $instance;
	}


	/**
	 * @param  mixed                $instance    Instance of the class we are building
	 * @param  \ReflectionProperty  $rProperty   Reflection of the property we are working with
	 * @param  mixed                $jsonValue   Set the property equal to this value - provided from the json object
	 * @param  boolean              $allowsNull  Can the property be set to null
	 *
	 * @return mixed
	 * @throws \andrewsauder\jsonDeserialize\exceptions\jsonDeserializeException
	 */
	private static function jsonDeserializeDataItem( mixed $instance, \ReflectionProperty $rProperty, mixed $jsonValue, bool $allowsNull ) : mixed {
		$propertyName     = $rProperty->getName();
		$rPropertyType    = $rProperty->getType();
		$propertyTypeName = $rPropertyType->getName();

		//get type of array if specified
		if( $propertyTypeName == 'array' ) {
			//get type  from @var doc block
			$propertyTypeName = self::getVarTypeFromDocComment( $rProperty->getDocComment() );
		}

		//if the property type is a class we try to get reflection information about it and set the value properly, otherwise it does the default types in the catch
		try {
			$rPropertyClass = new \ReflectionClass( $propertyTypeName );
		}
		//regular non-class types
		catch( \ReflectionException $e ) {
			if( $jsonValue !== null ) {
				if( $propertyTypeName == 'array' ) {
					return (array) $jsonValue;
				}

				//cast jsonValue to the property type
				try {
					$castSuccessfully = settype( $jsonValue, $propertyTypeName );
				}
				catch(\Error $e ) {
					error_log(  'JsonDeserializeException: '.$instance::class . '->'.$propertyName.' has an invalid type (or one that cannot be found) of '.$propertyTypeName );
					throw new jsonDeserializeException( 'Invalid data type for ' . $propertyName, 500, $e );
				}

				if( !$castSuccessfully ) {
					throw new jsonDeserializeException( 'Invalid data type for ' . $propertyName );
				}

				return $jsonValue;
			}

			//return default value
			return $rProperty->getValue( $instance );
		}

		//error messagings
		$errorMessageDataPosition = $instance::class . ' ' . $propertyName;

		//if no value is provided and nulls are not allowed, create a new instance
		if( empty( $jsonValue ) && !$allowsNull ) {
			try {
				return $rPropertyClass->newInstance();
			}
			catch( \ReflectionException $e ) {
				throw new jsonDeserializeException( 'Failed to instantiate type ' . $propertyTypeName . ' for ' . $errorMessageDataPosition, 500, $e );
			}
		}

		//no value provided, nulls allowed - use the default instantiated value
		elseif( empty( $jsonValue ) && $allowsNull ) {
			//return default value
			return $rProperty->getValue( $instance );
		}

		//implementers of jsonDeserialize
		elseif( $rPropertyClass->implementsInterface( \andrewsauder\jsonDeserialize\interfaces\jsonDeserialize::class ) ) {
			try {
				$method           = $rPropertyClass->getMethod( 'jsonDeserialize' );
				$tempTypeInstance = $rPropertyClass->newInstanceWithoutConstructor();

				return $method->invoke( $tempTypeInstance, $jsonValue );
			}
			catch( \ReflectionException $e ) {
				throw new jsonDeserializeException( 'Failed to instantiate type ' . $propertyTypeName . ' for ' . $errorMessageDataPosition, 500, $e );
			}
		}

		//value provided
		else {
			try {
				return $rPropertyClass->newInstance( $jsonValue );
			}
			catch( \ReflectionException $e ) {
				throw new jsonDeserializeException( 'Failed to instantiate type ' . $propertyTypeName . ' for ' . $errorMessageDataPosition, 500, $e );
			}
			catch( \Exception $e ) {
				throw new jsonDeserializeException( 'Invalid date time provided for ' . $errorMessageDataPosition, 400, $e );
			}
		}

	}


	private static function classNameToFqn( $className ) : string {
		$className = ltrim( $className, '\\' );

		return '\\' . $className;
	}


	/**
	 * @param  string  $docComment  Doc comment block to parse (reflection getDocComment)
	 *
	 * @return string
	 */
	private static function getVarTypeFromDocComment( string $docComment ) : string {
		$matches = [];

		preg_match( '/@var ([^ \[\]]+)(\[])?/', $docComment, $matches );

		if( count( $matches ) > 0 ) {
			return $matches[ 1 ];
		}

		return 'array';
	}

}