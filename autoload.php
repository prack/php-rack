<?php

function autoloadPrackClasses( $class_or_interface ) {
	$path_components = preg_split( '/_/', $class_or_interface );
	$path_components = array_map( 'strtolower', $path_components );

	$path = implode( DIRECTORY_SEPARATOR, $path_components ).'.php';
	if ( file_exists( $path ) && !is_dir( $path ))
		include $path;
	
	if ( !class_exists( $class_or_interface ) && !interface_exists( $class_or_interface ) )
		throw new RuntimeException( "Unable to load class {$class_or_interface} at {$path}." );
}

spl_autoload_register( 'autoloadPrackClasses' );