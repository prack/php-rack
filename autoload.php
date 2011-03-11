<?php

// TODO: Document!
function __prack_autoload( $class_or_interface ) {
	$path_components = preg_split( '/_/', $class_or_interface );
	$path_components = array_map( 'strtolower', $path_components );
	
	$rel = implode( DIRECTORY_SEPARATOR, $path_components ).'.php';
	$abs = join( DIRECTORY_SEPARATOR, array( dirname( __FILE__ ), 'lib', $rel ) );

	if ( file_exists( $abs ) && !is_dir( $abs ) )
		include $abs;
}

spl_autoload_register( '__prack_autoload', true );