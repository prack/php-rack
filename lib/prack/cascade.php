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
			$not_found = Prb::Ary( array( Prb::Num( 404 ), Prb::Hsh(), Prb::Ary() ) );
		
		return $not_found;
	}
	
	// TODO: Document!
	static function with( $middleware_apps, $catch = null )
	{
		return new Prack_Cascade( $middleware_apps, $catch );
	}
	
	// TODO: Document!
	public function __construct( $middleware_apps, $catch = null )
	{
		$catch = is_null( $catch )
		  ? Prb::Ary( array( Prb::Num( 404 ) ) )
		  : $catch;
		
		$this->middleware_apps = Prb::Ary();
		$this->has_app         = Prb::Hsh();
		
		foreach ( $middleware_apps->raw() as $middleware_app )
			$this->add( $middleware_app );
		
		$this->catch = Prb::Hsh();
		foreach ( $catch->raw() as $status )
			$this->catch->set( $status->toS()->raw(), true );
	}
	
	// TODO: Document!
	public function call( $env )
	{
		$result = self::notFound();
		
		foreach ( $this->middleware_apps->raw() as $middleware_app )
		{
			$result = $middleware_app->call( $env );
			if ( !( $this->catch->contains( $result->get( 0 )->toS()->raw() ) ) )
				break;
		}
		
		return $result;
	}
	
	// TODO: Document!
	public function add( $middleware_app )
	{
		$this->has_app->set( spl_object_hash( $middleware_app ), true );
		$this->middleware_apps->push( $middleware_app );
	}
	
	public function concat( $middleware_app ) { return $this->add( $middleware_app ); }
	
	// TODO: Document!
	public function contains( $middleware_app )
	{
		return $this->has_app->contains( spl_object_hash( $middleware_app ) );
	}
}
