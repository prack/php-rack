<?php

// TODO: Document!
abstract class Prack_Auth_Abstract_Handler
{
	private $realm;
	
	// TODO: Document!
	function __construct( $middleware_app, $realm = '', $callback = null )
	{
		$this->middleware_app = $middleware_app;
		$this->realm          = (string)$realm;
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
		
		return array(
		  401, array( 'Content-Type' => 'text/plain', 'Content-Length' => '0', 'WWW-Authenticate' => $www_authenticate ), array()
		);
	}
	
	// TODO: Document!
	protected function badRequest()
	{
		return array( 400, array( 'Content-Type' => 'text/plain', 'Content-Length' => '0' ), array() );
	}
}