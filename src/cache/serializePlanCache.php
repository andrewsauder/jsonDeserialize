<?php

namespace andrewsauder\jsonDeserialize\cache;

use andrewsauder\jsonDeserialize\attributes\excludeJsonSerialize;
use andrewsauder\jsonDeserialize\attributes\jsonSerializeCast;
use andrewsauder\jsonDeserialize\attributes\jsonSerializeDateTimeFormat;
use andrewsauder\jsonDeserialize\attributes\skipJsonSerializeProcessing;
use andrewsauder\jsonDeserialize\jsonSerializeCastType;

final class serializePlanCache {

	/** in-process cache for this PHP worker */
	private static array $local = [];


	public static function for( object|string $objOrClass ): SerializePlan {
		$class = \is_object( $objOrClass ) ? $objOrClass::class : $objOrClass;

		// 1) Local (request/process) cache hit?
		if( isset( self::$local[ $class ] ) ) {
			return self::$local[ $class ];
		}

		// 2) APCu (optional, if enabled)
		$key = 'jsonDeserialize.serializePlan.' . $class . '.' . self::classMtimeSig( $class );
		if( \function_exists( 'apcu_fetch' ) ) {
			$planPlan = \apcu_fetch( $key );
			$plan = eval( str_replace( '<?php', '', $planPlan) );
			if( $plan instanceof SerializePlan ) {
				return self::$local[ $class ] = $plan;
			}
		}

		// 3) Disk cache (optional). Uses a PHP-returning file so opcache can cache it.
		$diskKey = \sys_get_temp_dir() . '/jsonDeserialize-cache/' . \md5( $key ) . '.php';
		if( \is_file( $diskKey ) ) {
			/** @var SerializePlan $plan */
			$plan = include $diskKey;
			if( $plan instanceof SerializePlan ) {
				self::$local[ $class ] = $plan;
				if( \function_exists( 'apcu_store' ) ) {
					\apcu_store( $key, self::exportPlanAsPhp($plan) );
				}
				return $plan;
			}
		}

		// 4) Build plan via reflection once.
		$r     = new \ReflectionClass( $class );
		$skipProc     = !empty($r->getAttributes(skipJsonSerializeProcessing::class, \ReflectionAttribute::IS_INSTANCEOF));
		$props = [];
		foreach( $r->getProperties( \ReflectionProperty::IS_PUBLIC ) as $rp ) {
			if( self::hasAttribute( $rp, excludeJsonSerialize::class ) ) {
				continue;
			}
			$dateFmt  = self::getDateFormatAttr( $rp );
			$castType = self::getCastTypeAttr( $rp );

			// Create a fast property getter closure bound to the property name.
			$name           = $rp->getName();
			$getter         = static function( object $o ) use ( $name ) {
				return $o->$name ?? null;
			};
			$props[ $name ] = new serializeProp( $name, $dateFmt, $castType, $getter );

		}

		$plan = new serializePlan( $props, $skipProc );

		// Persist: make parent dir then write a tiny PHP file returning the plan.
		// In production you might skip disk if APCu is present.
		if( !\is_dir( \dirname( $diskKey ) ) ) {
			@\mkdir( \dirname( $diskKey ), 0777, true );
		}
		// Safe serialize: we only store simple objects; closures are not serializable.
		// So we rehydrate closures at load-time (see below).
		\file_put_contents( $diskKey, self::exportPlanAsPhp( $plan ) );
		if( \function_exists( 'apcu_store' ) ) {
			\apcu_store( $key, self::exportPlanAsPhp( $plan ) );
		}

		return self::$local[ $class ] = $plan;
	}


	private static function classMtimeSig( string $class ): string {
		// Invalidate when source changes. Reflection -> getFileName() may return false for eval’d code.
		$rc    = new \ReflectionClass( $class );
		$file  = $rc->getFileName() ?: $class;
		$mtime = $file && \is_file( $file ) ? \filemtime( $file ) : 0;
		return (string)$mtime;
	}


	private static function hasAttribute( \ReflectionProperty $rp, string $attrClass ): bool {
		foreach( $rp->getAttributes() as $attr ) {
			if( $attr->getName()===$attrClass ) {
				return true;
			}
		}
		return false;
	}


	private static function getDateFormatAttr( \ReflectionProperty $rp ): ?string {
		foreach( $rp->getAttributes() as $attr ) {
			if( $attr->getName()===jsonSerializeDateTimeFormat::class ) {
				$args = $attr->getArguments();
				return $args[ 0 ] ?? null;
			}
		}
		return null;
	}


	private static function getCastTypeAttr( \ReflectionProperty $rp ): ?jsonSerializeCastType {
		foreach( $rp->getAttributes() as $attr ) {
			if( $attr->getName()===jsonSerializeCast::class ) {
				$args = $attr->getArguments();
				return $args[ 0 ] ?? null;
			}
		}
		return null;
	}


	private static function exportPlanAsPhp( SerializePlan $plan ): string {
		// Because closures can’t be serialized, we emit code that rebuilds them.
		$propsPhp = [];
		foreach( $plan->props as $p ) {
			$df         = $p->dateFormat===null ? 'null' : var_export( $p->dateFormat, true );
			$castType   = $p->castType===null ? 'null' : var_export( $p->castType, true );
			$n          = var_export( $p->name, true );
			$propsPhp[] =
				"new \\andrewsauder\\jsonDeserialize\\cache\\serializeProp($n, $df, $castType, static function(object \$o){return \$o->{$p->name} ?? null;})";
		}
		$propsList = \implode( ',', $propsPhp );
		$skip      = $plan->skipProcessing ? 'true' : 'false';
		return <<<PHP
<?php
return new \\andrewsauder\\jsonDeserialize\\cache\\serializePlan([$propsList], $skip);
PHP;
	}

}
