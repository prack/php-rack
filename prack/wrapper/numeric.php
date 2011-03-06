<?php

class Prack_Wrapper_Numeric
  implements Prack_Interface_Comparable
{
	private $numeric;
	
	// TODO: Document!
	static function with( $numeric )
	{
		return new Prack_Wrapper_Numeric( $numeric );
	}
	
	// TODO: Document!
	public function __construct( $numeric )
	{
		if ( !is_numeric( $numeric ) )
			throw new Prack_Error_Type( 'FAILSAFE: __construct $numeric is not numeric' );
		
		$this->numeric = $numeric;
	}
	
	// TODO: Document!
	public function toN()
	{
		return $this->numeric;
	}
	
	// TODO: Document!
	public function compare( $other_num )
	{
		return ( $this->numeric - $other_num->toN() );
	}
}