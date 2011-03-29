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
			$authorization_keys = array(
			  'HTTP_AUTHORIZATION',
			  'X-HTTP_AUTHORIZATION',
			  'X_HTTP_AUTHORIZATION'
			);
		
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
			$auth_key    = $this->authorizationKey();
			$this->parts = preg_split( '/ /', (string)@$this->env[ $auth_key ], 2 );
		}
		
		return $this->parts;
	}
	
	// TODO: Document!
	public function scheme()
	{
		if ( is_null( $this->scheme ) )
		{
			$parts = $this->parts();
			$first = reset( $parts );
			$this->scheme = strtolower( (string)$first );
		}
		
		return $this->scheme;
	}
	
	// TODO: Document!
	public function params()
	{
		if ( is_null( $this->params ) )
		{
			$parts        = $this->parts();
			$this->params = end( $parts );
		}
		
		return $this->params;
	}
	
	// TODO: Document!
	private function authorizationKey()
	{
		if ( is_null( $this->authorization_key ) )
		{
			$found = null;
			foreach ( self::authorizationKeys() as $key )
			{
				if ( @$this->env[ $key ] )
				{
					$found = $key;
					break;
				}
			}
			$this->authorization_key = $found;
		}
		
		return $this->authorization_key;
	}
}
