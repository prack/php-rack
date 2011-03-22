<?php

global $g_prack_last_gets;
global $g_prack_f_sep;
global $g_prack_o_sep;

if ( is_null( $g_prack_f_sep ) )
	$g_prack_f_sep = Prb::Str( PHP_EOL );

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