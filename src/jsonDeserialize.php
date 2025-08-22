<?php
namespace andrewsauder\jsonDeserialize;

use andrewsauder\jsonDeserialize\attributes\excludeJsonDeserialize;
use andrewsauder\jsonDeserialize\attributes\jsonSerializeDateTimeFormat;
use andrewsauder\jsonDeserialize\cache\serializeProp;
use andrewsauder\jsonDeserialize\exceptions\jsonDeserializeException;

#[\AllowDynamicProperties]
abstract class jsonDeserialize
	implements
	\andrewsauder\jsonDeserialize\interfaces\jsonDeserialize,
	\JsonSerializable {

	private static function jsonDeserializeLog( string $message, array $context = [] ): void {
		if( config::isDebugLogging() ) {
			config::getDebugLogger()->debug( $message, $context );
		}
	}


	/**
	 * Initialize from outside object
	 *
	 * @param string|\stdClass|array $json
	 *
	 * @return $this|$this[]
	 * @throws \andrewsauder\jsonDeserialize\exceptions\jsonDeserializeException
	 */
	public static function jsonDeserialize( string|\stdClass|array $json ): self|array {
		$calledClassFqn = self::classNameToFqn( get_called_class() );

		if( method_exists( $calledClassFqn, '_beforeJsonDeserialize' ) ) {
			$calledClassFqn::_beforeJsonDeserialize( $json );
		}

		//parse the json
		if( is_string( $json ) ) {
			try {
				$json = json_decode( $json, false, 512, JSON_THROW_ON_ERROR );
			}
			catch( \JsonException $e ) {
				throw new jsonDeserializeException( 'Malformed JSON ' . $calledClassFqn, 400, $e );
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
			$final = self::jsonDeserializeObject( $calledClassFqn, $json );
		}

		if( method_exists( $final, '_afterJsonDeserialize' ) ) {
			$final->_afterJsonDeserialize();
		}

		return $final;
	}


	/**
	 * @return array
	 * @throws \andrewsauder\jsonDeserialize\exceptions\jsonDeserializeException
	 */
	public function jsonSerialize(): array {
		if( method_exists( $this, '_beforeJsonSerialize' ) ) {
			$this->_beforeJsonSerialize();
		}

		$export = self::exportObject($this);

		if( method_exists( $this, '_afterJsonSerialize' ) ) {
			return $this->_afterJsonSerialize( $export );
		}

		return $export;
	}


	/**
	 * @throws \andrewsauder\jsonDeserialize\exceptions\jsonDeserializeException
	 */
	private static function jsonDeserializeObject( string $calledClassFqn, \stdClass $json ) {
		//load new instance of this class
		try {
			$rClass   = new \ReflectionClass( $calledClassFqn );
			$instance = $rClass->newInstanceWithoutConstructor();
		}
		catch( \ReflectionException $e ) {
			throw new jsonDeserializeException( 'Failed to load type ' . $calledClassFqn . ' for deserialization', 500, $e );
		}

		//get properties of the class
		$rProperties = $rClass->getProperties();

		//get the fields not defined on the class
		foreach( $json as $key => $value ) {
			if( !$rClass->hasProperty( $key ) ) {
				if( config::isDebugLogging() && config::isLogClassMissingProperty() ) {
					config::getDebugLogger()->debug( $calledClassFqn . '->' . $key . ' is not defined on the class. Value will be injected in class with standard JSON decode types.' );
				}
				$instance->$key = $value;
			}
		}

		//load data from $json into the class $instance
		foreach( $rProperties as $rProperty ) {
			$propertyName = $rProperty->getName();

			//exclude if attribute says to
			$attributes = $rProperty->getAttributes( excludeJsonDeserialize::class, \ReflectionAttribute::IS_INSTANCEOF );
			if( count( $attributes )>0 ) {
				continue;
			}

			//if there is not a matching json property, ignore it
			if( !property_exists( $json, $propertyName ) ) {
				if( config::isDebugLogging() && config::isLogJsonMissingProperty() ) {
					config::getDebugLogger()->debug( $rProperty->class . '->' . $propertyName . ' not defined JSON source data' );
				}
				continue;
			}

			//get the type of this property
			$rPropertyType = $rProperty->getType();

			if( !isset( $rPropertyType ) ) {
				if( config::isDebugLogging() && config::isLogClassPropertyMissingType() ) {
					config::getDebugLogger()->debug( $rProperty->class . '->' . $propertyName . ' does not have a type defined. Value will be injected in class with standard JSON decode types.' );
				}
				$instance->$propertyName = $json->$propertyName;
				continue;
			}

			$rPropertyTypeName = '';
			if( !( $rPropertyType instanceof \ReflectionUnionType ) ) {
				$rPropertyTypeName = $rPropertyType->getName();
			}

			//if the property is an array, check if the doc comment defines the type
			$propertyIsTypedArray = false;
			if( $rPropertyTypeName=='array' ) {
				$arrayType = self::getVarTypeFromDocComment( $rProperty->getDocComment() );
				if( $arrayType!='array' ) {
					$propertyIsTypedArray = true;
				}
			}

			//load the data from json into the instance of our class
			if( $propertyIsTypedArray ) {
				$instance->$propertyName = [];
				foreach( $json->$propertyName as $key => $jsonItem ) {
					$instance->$propertyName[ $key ] = self::jsonDeserializeDataItem( $instance, $rProperty, $jsonItem, $rPropertyType->allowsNull() );
				}
			}
			else {
				$instance->$propertyName = self::jsonDeserializeDataItem( $instance, $rProperty, $json->$propertyName, $rPropertyType->allowsNull() );
			}
		}

		return $instance;
	}


	/**
	 * @param mixed               $instance   Instance of the class we are building
	 * @param \ReflectionProperty $rProperty  Reflection of the property we are working with
	 * @param mixed               $jsonValue  Set the property equal to this value - provided from the json object
	 * @param boolean             $allowsNull Can the property be set to null
	 *
	 * @return mixed
	 * @throws \andrewsauder\jsonDeserialize\exceptions\jsonDeserializeException
	 */
	private static function jsonDeserializeDataItem( mixed $instance, \ReflectionProperty $rProperty, mixed $jsonValue, bool $allowsNull ): mixed {
		$propertyName     = $rProperty->getName();
		$rPropertyType    = $rProperty->getType();
		$propertyTypeName = '';
		if( !( $rPropertyType instanceof \ReflectionUnionType ) ) {
			$propertyTypeName = $rPropertyType->getName();
		}

		//get type of array if specified
		if( $propertyTypeName=='array' ) {
			//get type  from @var doc block
			$propertyTypeName = self::getVarTypeFromDocComment( $rProperty->getDocComment() );
			//untype a union type array
			if( str_contains( $propertyTypeName, '|' ) ) {
				$propertyTypeName = 'array';
			}
		}

		if( str_starts_with( $propertyTypeName, '?' ) ) {
			$propertyTypeName = substr( $propertyTypeName, 1 );
			$allowsNull       = true;
		}

		//if the property type is a class we try to get reflection information about it and set the value properly, otherwise it does the default types in the catch
		try {
			$rPropertyClass = new \ReflectionClass( $propertyTypeName );
		}
			//regular non-class types
		catch( \ReflectionException $e ) {
			if( $jsonValue!==null ) {
				if( $propertyTypeName=='array' ) {
					return (array)$jsonValue;
				}

				//cast jsonValue to the property type
				if( !empty( $propertyTypeName ) ) {
					if( $propertyTypeName==='bool' ) {
						if( is_bool( $jsonValue ) ) {
							return $jsonValue;
						}
						else {
							return trim( strtolower( $jsonValue ) )==='true' || trim( $jsonValue )=='1';
						}
					}
					else {
						try {
							$castSuccessfully = settype( $jsonValue, $propertyTypeName );
						}
						catch( \Error $e ) {
							error_log( 'JsonDeserializeException: ' . $instance::class . '->' . $propertyName . ' has an invalid type (or one that cannot be found) of ' . $propertyTypeName );
							throw new jsonDeserializeException( 'Invalid data type for ' . $propertyName, 500, $e );
						}

						if( !$castSuccessfully ) {
							throw new jsonDeserializeException( 'Invalid data type for ' . $propertyName );
						}
					}
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
		elseif( $rPropertyClass->isEnum() ) {
			try {
				$rEnum = new \ReflectionEnum( $propertyTypeName );
			}
			catch( \ReflectionException $e ) {
				throw new jsonDeserializeException( 'Failed to reflect an enum of type ' . $propertyTypeName . ' for ' . $errorMessageDataPosition, 500, $e );
			}

			if( $rEnum->isBacked() ) {
				try {
					return $propertyTypeName::from( $jsonValue );
				}
				catch( \ValueError $e ) {
					throw new jsonDeserializeException( 'Failed set ' . $propertyTypeName . ' to ' . $jsonValue . ' for ' . $errorMessageDataPosition, 500, $e );
				}
			}
			else {
				try {
					return $rEnum->getCase( $jsonValue );
				}
				catch( \ReflectionException $e ) {
					throw new jsonDeserializeException( 'Enum ' . $propertyTypeName . ' does not have case ' . $jsonValue . ' for ' . $errorMessageDataPosition, 500, $e );
				}
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


	/**
	 * @param \ReflectionProperty $rProperty
	 * @param mixed               $value
	 *
	 * @return mixed
	 */
	private function jsonSerializeDataItem( \ReflectionProperty $rProperty, mixed $value ): mixed {
		if( \andrewsauder\jsonDeserialize\cache::getClassExists( '\MongoDB\BSON\ObjectId' ) && $value instanceof \MongoDB\BSON\ObjectId ) {
			return (string)$value;
		}
		elseif( $value instanceof \DateTimeInterface ) {
			$dateTimeFormatAttributes = $rProperty->getAttributes( jsonSerializeDateTimeFormat::class );
			if(count($dateTimeFormatAttributes)>0) {
				$dateTimeFormatAttributeInstance = $dateTimeFormatAttributes[0]->newInstance();
				return $value->format( $dateTimeFormatAttributeInstance->datetimeFormat );
			}
			return $value->format( DATE_ATOM );
		}

		return $value;
	}


	private static function classNameToFqn( $className ): string {
		$className = ltrim( $className, '\\' );

		return '\\' . $className;
	}


	/**
	 * @param string $docComment Doc comment block to parse (reflection getDocComment)
	 *
	 * @return string
	 */
	private static function getVarTypeFromDocComment( string $docComment ): string {
		$matches = [];

		preg_match( '/@var ([^ <>\[\]]+)(<)?([^ ,>\[\]]+)?(, )?([^ >\[\]]+)?(>)?(\[])?/', $docComment, $matches );

		if( count( $matches )>0 ) {
			if( $matches[ 1 ]=='array' ) {
				//@var array<T, T>
				if( isset($matches[ 5 ]) && $matches[ 5 ]!='' ) {
					return $matches[ 5 ];
				}
				//@var array<T>
				elseif( isset($matches[ 3 ]) && $matches[ 3 ]!='' ) {
					return $matches[ 3 ];
				}
			}
			//@var T or @var T[]
			return $matches[ 1 ];
		}

		return 'array';
	}


	/** @internal plan-driven export */
	private static function exportObject( object $obj, cache\serializePlanCache $plan=null ): array {
		if($plan===null) {
			// If no plan is provided, we create it
			$plan = cache\serializePlanCache::for( $obj );
		}
		$out  = [];

		foreach( $plan->props as $p ) {
			$value = ( $p->getter )( $obj );
			$out[ $p->name ] = self::exportValue($p, $value );
		}
		return $out;
	}


	private static function exportArray( serializeProp $p, array $a, cache\serializePlanCache $plan=null ): array {
		// Flat map with minimal branching inside the loop
		$out = [];
		foreach( $a as $key => $value ) {
			$out[ $key ] = self::exportValue( $p, $value, $plan );
		}
		return $out;
	}

	private static function exportValue( serializeProp $p, mixed $value, cache\serializePlanCache $plan=null ): mixed {

		if( \is_array( $value ) ) {
			return self::exportArray( $p, $value, $plan );
		}
		elseif( $p->castType instanceof jsonSerializeCastType ) {
			if($p->castType==jsonSerializeCastType::string) {
				return (string)$value;
			}
			elseif($p->castType==jsonSerializeCastType::int) {
				return (int)$value;
			}
			elseif($p->castType==jsonSerializeCastType::float) {
				return (float)$value;
			}
			elseif($p->castType==jsonSerializeCastType::bool) {
				return (bool)$value;
			}
		}
		elseif( $value===null || \is_scalar( $value ) ) {
			return $value;
		}
		elseif( $value instanceof \DateTimeInterface ) {
			return $value->format(  $p->dateFormat ?? DATE_ATOM  );
		}
		elseif( $value instanceof \UnitEnum ) {
			return $value instanceof \BackedEnum ? $value->value : $value->name;
		}
/*		elseif( $value instanceof self ) {
			return self::exportObject( $value, $plan );
		}*/
		elseif( $value instanceof \JsonSerializable ) {
			/** @var mixed $serialized */
			return $value->jsonSerialize();
		}
		elseif( \is_object( $value ) ) {
			// If it's an object, we try to get its public properties as a last resort
			return \get_object_vars( $value ); // last resort
		}


		return $value;
	}
}
