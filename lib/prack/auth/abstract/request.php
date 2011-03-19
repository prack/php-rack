<?php

// TODO: Document!
abstract class Prack_Auth_Abstract_Request
{
	protected $env;
	protected $parts;
	protected $scheme;
	protected $params;
	protected $authorization_key;
	
	// TODO: Document!
	static function authorizationKeys()
	{
		static $authorization_keys = null;
		
		if ( is_null( $authorization_keys ) )
			$authorization_keys = Prb::_Array( array(
			  Prb::_String( 'HTTP_AUTHORIZATION'   ),
			  Prb::_String( 'X-HTTP_AUTHORIZATION' ),
			  Prb::_String( 'X_HTTP_AUTHORIZATION' )
			) );
		
		return $authorization_keys;
	}
	
	// TODO: Document!
	function __construct( $env )
	{
		$this->env = $env;
	}
	
	// TODO: Document!
	public function isProvided()
	{
		$ak = $this->authorizationKey();
		return isset( $ak );
	}
	
	// TODO: Document!
	public function parts()
	{
		if ( is_null( $this->parts ) )
		{
			$auth_key    = $this->authorizationKey() ? $this->authorizationKey()->raw() : null;
			$auth_header = $this->env->contains( $auth_key )
			  ? $this->env->get( $auth_key )
			  : Prb::_String();
			$this->parts = $auth_header->split( '/ /', 2 );
		}
			
		return $this->parts;
	}
	
	// TODO: Document!
	public function scheme()
	{
		if ( is_null( $this->scheme ) )
			$this->scheme = $this->parts()->first()->downcase();
		return $this->scheme;
	}
	
	// TODO: Document!
	public function params()
	{
		if ( is_null( $this->params ) )
			$this->params = $this->parts()->last();
		return $this->params;
	}
	
	// TODO: Document!
	private function authorizationKey()
	{
		if ( is_null( $this->authorization_key ) )
		{
			$callback = array( $this, 'onDetect' );
			$this->authorization_key = self::authorizationKeys()->detect( $callback );
		}
		return $this->authorization_key;
	}
	
	// TODO: Document!
	public function onDetect( $key )
	{
		return $this->env->contains( $key->raw() );
	}
}
