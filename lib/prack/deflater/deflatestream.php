<?php

// TODO: Document!
class Prack_Deflater_DeflateStream
  implements Prb_I_Enumerable
{
	private $body;
	private $parts;
	private $callback;
	
	// TODO: Document!
	static function with( $body )
	{
		return new Prack_Deflater_DeflateStream( $body );
	}

	// TODO: Document!
	function __construct( $body )
	{
		$this->body  = $body;
		$this->parts = array();
	}
	
	// TODO: Document!
	public function each( $callback )
	{
		if ( is_string( $this->body ) )
			$this->body = array( $this->body );
		
		if ( is_array( $this->body ) )
			call_user_func( $callback, gzdeflate( join( '', $this->body ) ) );
		else
		{
			$_callback = $callback;
			$this->body->each( array( $this, 'onWrite' ) );
			call_user_func( $_callback, gzdeflate( join( '', $this->parts ) ) );
		}
		
		if ( method_exists( $this->body, 'close' ) )
			$this->body->close();
		
		return null;
	}
	
	// TODO: Document!
	public function onWrite( $part )
	{
		array_push( $this->parts, $part );
	}
}