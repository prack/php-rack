<?php

// TODO: Document!
class Prack_NullLogger
  implements Prack_I_MiddlewareApp
{
	private $middleware_app;
	
	// TODO: Document!
	static function with( $middleware_app )
	{
		return new Prack_NullLogger( $middleware_app );
	}
	
	// TODO: Document!
	function __construct( $middleware_app )
	{
		$this->middleware_app = $middleware_app;
	}
	
	// TODO: Document!
	public function call( &$env )
	{
		$env[ 'rack.logger' ] = $this;
		return $this->middleware_app->call( $env );
	}
	
	// TODO: Document!
	public function  info( $progname = nil, $callback = null ) { return; }
	public function debug( $progname = nil, $callback = null ) { return; }
	public function  warn( $progname = nil, $callback = null ) { return; }
	public function error( $progname = nil, $callback = null ) { return; }
	public function fatal( $progname = nil, $callback = null ) { return; }
}