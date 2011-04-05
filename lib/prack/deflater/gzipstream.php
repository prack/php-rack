<?php

// TODO: Document!
class Prack_Deflater_GzipStream
  implements Prb_I_Enumerable
{
	private $body;
	private $parts;
	private $callback;
	
	// TODO: Document!
	static function with( $body, $mtime = null )
	{
		return new Prack_Deflater_GzipStream( $body, $mtime );
	}

	// TODO: Document!
	function __construct( $body, $mtime = null )
	{
		$this->body  = $body;
		$this->mtime = $mtime;
		$this->parts = array();
	}
	
	// TODO: Document!
	public function each( $callback )
	{
		if ( is_string( $this->body ) )
			$this->body = array( $this->body );
		
		if ( is_array( $this->body ) )
			$encoded = gzencode( join( '', $this->body ), 9 );
		else
		{
			$this->body->each( array( $this, 'onWrite' ) );
			$encoded = gzencode( join( '', $this->parts ), 9 );
		}
		
		if ( isset( $this->mtime ) )
			$encoded = substr_replace( $encoded, pack( 'V', $this->mtime ), 4, 4 );
		
		call_user_func( $callback, $encoded );
		
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