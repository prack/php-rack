<?php

// TODO: Document!
class Prack_Wrapper_Time extends Prack_Wrapper_Numeric
{
	private $seconds;
	private $microseconds;
	
	// TODO: Document!
	function __construct( $time = null )
	{
		if ( is_null( $time ) )
			$time = microtime( true );
		
		parent::__construct( $time );
		
		$abs = abs( $time );
		$this->seconds      = Prack::_Numeric( (int)$abs );
		$this->microseconds = Prack::_Numeric( (int)( ( $abs - floor( $abs ) ) * pow( 10, 6 ) ) );
	}
	
	// TODO: Document!
	public function strftime( $format )
	{
		if ( !( $format instanceof Prack_Wrapper_String ) )
			throw new Prack_Error_Argument( 'strftime $format must be instance of Prack_Wrapper_String' );
		
		$formatted = strftime( $format->toN(), (int)$this->numeric );
		return is_string( $formatted ) ? Prack::_String( $formatted ) : Prack::_String();
	}
	
	// TODO: Document!
	public function getSeconds()
	{
		return $this->seconds;
	}
	
	// TODO: Document!
	public function getMicroseconds()
	{
		return $this->microseconds;
	}
}
