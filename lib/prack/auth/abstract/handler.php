<?php

// TODO: Document!
abstract class Prack_Auth_Abstract_Handler
{
	private $realm;
	
	// TODO: Document!
	function __construct( $middleware_app, $realm = null, $callback = null )
	{
		$this->middleware_app = $middleware_app;
		$this->realm          = isset( $realm ) ? $realm : Prb::Str();
		$this->callback       = $callback;
	}
	
	// TODO: Document!
	public function realm()
	{
		return $this->realm;
	}
	
	// TODO: Document!
	public function setRealm( $realm )
	{
		$this->realm = $realm;
	}
	
	// TODO: Document!
	protected function unauthorized( $www_authenticate = null )
	{
		if ( is_null( $www_authenticate ) )
			$www_authenticate = $this->challenge();
		
		return Prb::Ary( array(
		  Prb::Num( 401 ),
		  Prb::Hsh( array(
		    'Content-Type'     => Prb::Str( 'text/plain' ),
		    'Content-Length'   => Prb::Str( '0' ),
		    'WWW-Authenticate' => $www_authenticate->toS()
		  ) ),
		  Prb::Ary()
		) );
	}
	
	// TODO: Document!
	protected function badRequest()
	{
		return Prb::Ary( array(
		  Prb::Num( 400 ),
		  Prb::Hsh( array(
		    'Content-Type'     => Prb::Str( 'text/plain' ),
		    'Content-Length'   => Prb::Str( '0' )
		  ) ),
		  Prb::Ary()
		) );
	}
}