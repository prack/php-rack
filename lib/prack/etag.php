<?php

// TODO: Document!
# Automatically sets the ETag header on all String bodies
class Prack_ETag
  implements Prack_I_MiddlewareApp
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
	public function call( &$env )
	{
		list( $status, $headers, $body ) = $this->middleware_app->call( $env );
		
		$response = Prack_Response::with( $body, $status, $headers );
		if ( !$response->get( 'ETag' ) )
		{
			list( $digest, $body ) = $this->digestBody( $response->getBody() );
			$response->set( 'ETag', "\"$digest\"" );
		}
		
		return $response->raw();
	}
	
	// TODO: Document!
	private function digestBody( $body )
	{
		$this->buffer = '';
		$this->parts  = array();
		
		static $callback = null;
		if ( is_null( $callback ) )
			$callback = array( $this, 'onDigestBody' );
		
		foreach ( $body as $part )
		{
			$this->buffer .= $part;
			array_push( $this->parts, $part );
		}
		
		return array( md5( $this->buffer ), $this->parts );
	}
}
