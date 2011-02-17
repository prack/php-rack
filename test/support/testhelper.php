<?php

class TestHelper
{
	public static function gibberish( $length = 128 )
	{
		$aZ09 = array_merge( range( 'A', 'Z' ), range( 'a', 'z' ),range( 0, 9 ) );
		$out  = '';
		for( $c=0; $c < $length; $c++ )
			$out .= (string)$aZ09[ mt_rand( 0, count( $aZ09 ) - 1 ) ];
		return $out;
	}
}