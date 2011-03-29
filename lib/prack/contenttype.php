<?php

// TODO: Document!
# Sets the Content-Type header on responses which don't have one.
#
# Builder Usage:
#   use Rack::ContentType, "text/plain"
#
# When no content type argument is provided, "text/html" is assumed.
class Prack_ContentType
  implements Prack_I_MiddlewareApp
{
	private $middleware_app;
	private $content_type;
	
	// TODO: Document!
	static function with( $middleware_app, $content_type = 'text/html' )
	{
		return new Prack_ContentType( $middleware_app, $content_type );
	}
	
	// TODO: Document!
	public function __construct( $middleware_app, $content_type = 'text/html' )
	{
		$this->middleware_app = $middleware_app;
		$this->content_type   = $content_type;
	}
	
	// TODO: Document!
	public function call( &$env )
	{
		list( $status, $headers, $body ) = $this->middleware_app->call( $env );
		
		$headers = Prack_Utils_HeaderHash::using( $headers );
		$headers->contains( 'Content-Type' )
		  ? true
		  : $headers->set( 'Content-Type', $this->content_type );
		
		return array( $status, $headers->raw(), $body );
	}
}
