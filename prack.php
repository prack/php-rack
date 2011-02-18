<?php

// TODO: Document!
class Prack
{
	// TODO: Document!
	static function version()
	{
		static $version = null;
		
		if ( is_null( $version ) )
			$version = array( 0, 1, 0 );
		
		return $version;
	}
}