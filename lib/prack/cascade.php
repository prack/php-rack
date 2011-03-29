<?php

// TODO: Document!
# Rack::Cascade tries an request on several middleware_apps, and returns the
# first response that is not 404 (or in a list of configurable
# status codes).
class Prack_Cascade
  implements Prack_I_MiddlewareApp
{
	private $middleware_apps;
	private $has_app;
	private $catch;
	
	// TODO: Document!
	static function notFound()
	{
		static $not_found = null;
		
		if ( is_null( $not_found ) )
			$not_found = array( 404, array(), array() );
		
		return $not_found;
	}
	
	// TODO: Document!
	static function with( $middleware_apps, $catch = array( 404 ) )
	{
		return new Prack_Cascade( $middleware_apps, $catch );
	}
	
	// TODO: Document!
	public function __construct( $middleware_apps, $catch = array( 404 ) )
	{
		$this->middleware_apps = array();
		$this->has_app         = array();
		
		foreach ( $middleware_apps as $middleware_app )
			$this->add( $middleware_app );
		
		$this->catch = array();
		foreach ( $catch as $status )
			$this->catch[ $status ] = true;
	}
	
	// TODO: Document!
	public function call( &$env )
	{
		$result = self::notFound();
		
		foreach ( $this->middleware_apps as $middleware_app )
		{
			$result = $middleware_app->call( $env );
			if ( !@$this->catch[ $result[ 0 ] ] )
				break;
		}
		
		return $result;
	}
	
	// TODO: Document!
	public function add( $middleware_app )
	{
		$this->has_app[ spl_object_hash( $middleware_app ) ] = true;
		array_push( $this->middleware_apps, $middleware_app );
	}
	
	public function concat( $middleware_app ) { return $this->add( $middleware_app ); }
	
	// TODO: Document!
	public function contains( $middleware_app )
	{
		return @$this->has_app[ spl_object_hash( $middleware_app ) ];
	}
}
