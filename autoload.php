<?php

// TODO: Document!
function autoloadPrackClasses( $class_or_interface ) {
	$path_components = preg_split( '/_/', $class_or_interface );
	$path_components = array_map( 'strtolower', $path_components );

	$rel = implode( DIRECTORY_SEPARATOR, $path_components ).'.php';
	$abs = join( DIRECTORY_SEPARATOR, array( dirname( __FILE__ ), $rel ) );
	
	if ( file_exists( $abs ) && !is_dir( $abs ) )
		include $abs;
	else
		throw new RuntimeException( "Unable to load class {$class_or_interface} at {$abs}." );
}

spl_autoload_register( 'autoloadPrackClasses' );