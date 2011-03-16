<?php

// TODO: Document!
# Automatically sets the ETag header on all String bodies
class Prack_ETag
  implements Prack_Interface_MiddlewareApp
{
	private $middleware_app;
	
	// TODO: Document!
	static function with( $middleware_app )
	{
		return new Prack_ETag( $middleware_app );
	}
	
	// TODO: Document!
	function __construct( $middleware_app )
	{
		$this->middleware_app = $middleware_app;
	}
	
	// TODO: Document!
	public function call( $env )
	{
		list( $status, $headers, $body ) = $this->middleware_app->call( $env )->raw();
		
		if ( !$headers->hasKey( 'ETag' ) )
		{
			list( $digest, $body ) = $this->digestBody( $body )->raw();
			$headers->set( 'ETag', Prb::_String( "\"{$digest->raw()}\"" ) );
		}
		
		return Prb::_Array( array( $status, $headers, $body ) );
	}
	
	// TODO: Document!
	private function digestBody( $body )
	{
		$this->buffer = Prb::_String();
		$this->parts  = Prb::_Array();
		
		static $callback = null;
		if ( is_null( $callback ) )
			$callback = array( $this, 'onDigestBody' );
		
		$body->each( $callback );
		
		return Prb::_Array( array( Prb_String::md5( $this->buffer ), $this->parts ) );
	}
	
	// TODO: Document!
	public function onDigestBody( $part )
	{
		$this->buffer->concat( $part );
		$this->parts->concat( $part );
	}
}
