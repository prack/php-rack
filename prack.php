<?php

global $g_prack_last_gets;
global $g_prack_f_sep;
global $g_prack_o_sep;

if ( is_null( $g_prack_f_sep ) )
	$g_prack_f_sep = Prack::_String( PHP_EOL );

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
	
	// TODO: Document!
	static function _Array( $wrap = array() )
	{
		return new Prack_Wrapper_Array( $wrap );
	}
	
	// TODO: Document!
	static function _Hash( $wrap = array() )
	{
		return new Prack_Wrapper_Hash( $wrap );
	}
	
	// TODO: Document!
	static function _Set( $wrap = array() )
	{
		return new Prack_Wrapper_Set( $wrap );
	}
	
	// TODO: Document!
	static function _String( $wrap = null )
	{
		return new Prack_Wrapper_String( $wrap );
	}
}