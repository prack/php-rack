<?php

// TODO: Document!
class Prack_Utils
{
	// TODO: Document!
	static public function statusWithNoEntityBody()
	{
		static $swneb = null;
		
		if ( is_null( $swneb ) )
		{
			$swneb = range( 100, 199 );
			array_push( $swneb, 204 );
			array_push( $swneb, 304 );
		}
		
		return $swneb;
	}
}