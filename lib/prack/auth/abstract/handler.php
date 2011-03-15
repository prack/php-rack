<?php

// TODO: Document!
abstract class Prack_Auth_Abstract_Handler
{
	private $realm;
	
	// TODO: Document!
	function __construct( $middleware_app, $realm = null, $callback = null )
	{
		$this->middleware_app = $middleware_app;
		$this->realm          = $realm;
		$this->callback       = $callback;
	}
	
	// TODO: Document!
	public function getRealm()
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
		
		return Prb::_Array( array(
		  Prb::_Numeric( 401 ),
		  Prb::_Hash( array(
		    'Content-Type'     => Prb::_String( 'text/plain' ),
		    'Content-Length'   => Prb::_String( '0' ),
		    'WWW-Authenticate' => $www_authenticate->toS()
		  ) ),
		  Prb::_Array()
		) );
	}
	
	// TODO: Document!
	protected function badRequest()
	{
		return Prb::_Array( array(
		  Prb::_Numeric( 400 ),
		  Prb::_Hash( array(
		    'Content-Type'     => Prb::_String( 'text/plain' ),
		    'Content-Length'   => Prb::_String( '0' )
		  ) ),
		  Prb::_Array()
		) );
	}
}