<?php

require __DIR__ . '/../vendor/autoload.php';

// Clear the serializePlanCache disk cache so tests aren't affected by stale
// plans from previous runs. Each test run rebuilds plans from current fixtures.
$cacheDir = sys_get_temp_dir() . '/jsonDeserialize-cache';
if( is_dir( $cacheDir ) ) {
	foreach( glob( $cacheDir . '/*.php' ) ?: [] as $file ) {
		@unlink( $file );
	}
}
