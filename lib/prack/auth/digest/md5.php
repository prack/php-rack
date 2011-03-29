<?php

// TODO: Document!
class Prack_Auth_Digest_MD5 extends Prack_Auth_Abstract_Handler
  implements Prack_I_MiddlewareApp
{
	private $opaque;
	private $passwords_hashed;
	private $hash;
	
	// TODO: Document!
	static function qop()
	{
		static $qop = null;
		
		if ( is_null( $qop ) )
			$qop = 'auth';
		
		return $qop;
	}
	
	// TODO: Document!
	static function with()
	{
		$args             = func_get_args();
		$reflection_class = new ReflectionClass( 'Prack_Auth_Digest_MD5' );
		return $reflection_class->newInstanceArgs( $args );
	}
	
	// TODO: Document!
	function __construct()
	{
		$args = func_get_args();
		call_user_func_array( array( $this, 'parent::__construct' ), $args );
		$this->passwords_hashed = false;
	}
	
	// TODO: Document!
	public function call( &$env )
	{
		$auth = new Prack_Auth_Digest_Request( $env );
		
		if ( !$auth->isProvided() )
			return $this->unauthorized();
		
		if ( !$auth->isDigest() || !$auth->isCorrectURI() || !$this->isValidQop( $auth ) )
			return $this->badRequest();
		
		if ( $this->isValid( $auth ) )
		{
			if ( $auth->nonce()->isStale() )
				return $this->unauthorized( $this->challenge( Prb::Hsh( array( 'stale' => true ) ) ) );
			else
				$env[ 'REMOTE_USER' ] = $auth->username();
			
			return $this->middleware_app->call( $env );
		}
		
		return $this->unauthorized();
	}
	
	// TODO: Document!
	private function params( $hash = null )
	{
		if ( is_null( $hash ) )
			$hash = array();
		
		$this->hash = $hash;
		
		return new Prack_Auth_Digest_Params( array( $this, 'onParams' ) );
	}
	
	// TODO: Document!
	public function onParams( $params )
	{
		$params->set( 'realm',  $this->realm()                         );
		$params->set( 'nonce',  Prack_Auth_Digest_Nonce::with()->raw() );
		$params->set( 'opaque', $this->H( $this->opaque() )            );
		$params->set( 'qop',    self::qop()                            );
		
		$this->params = $params;
		
		foreach ( $this->hash as $key => $value )
			$this->params->set( $key, $value );
	}
	
	// TODO: Document!
	public function challenge( $hash = null )
	{
		if ( is_null( $hash ) )
			$hash = array();
		
		return "Digest {$this->params( $hash )->raw()}";
	}
	
	// TODO: Document!
	public function isValid( $auth )
	{
		return ( $this->isValidOpaque( $auth ) && $this->isValidNonce( $auth ) && $this->isValidDigest( $auth ) );
	}
	
	// TODO: Document!
	public function isValidQop( $auth )
	{
		return ( self::qop() == $auth->qop() );
	}
	
	// TODO: Document!
	public function isValidOpaque( $auth )
	{
		return ( $this->H( $this->opaque() ) == $auth->opaque() );
	}
	
	// TODO: Document!
	public function isValidNonce( $auth )
	{
		return ( $auth->nonce()->isValid() );
	}
	
	// TODO: Document!
	public function isValidDigest( $auth )
	{
		return ( $this->digest( $auth, call_user_func( $this->callback, $auth->username() ) ) == $auth->response() );
	}
	
	// TODO: Document!
	private function md5( $data )
	{
		return md5( $data );
	}
	
	// TODO: Document!
	private function H( $data ) { return $this->md5( $data ); }
	
	// TODO: Document!
	private function KD( $secret, $data )
	{
		return $this->H( join( ':', array( $secret, $data ) ) );
	}
	
	// TODO: Document!
	private function A1( $auth, $password )
	{
		return join( ':', array( $auth->username(), $auth->realm(), $password ) );
	}
	
	// TODO: Document!
	private function A2( $auth )
	{
		return join( ':', array( $auth->method(), $auth->uri() ) );
	}
	
	// TODO: Document!
	public function digest( $auth, $password )
	{
		$password_hash = $this->arePasswordsHashed()
		  ? $password
		  : $this->H( $this->A1( $auth, $password ) );
		
		return $this->KD(
		  $password_hash,
		  join( ':', array( $auth->nonce()->raw(), $auth->nc(), $auth->cnonce(), self::qop(), $this->H( $this->A2( $auth ) ) ) )
		);
	}
	
	// TODO: Document!
	public function arePasswordsHashed()
	{
		return !!$this->passwords_hashed;
	}

	// TODO: Document!
	public function opaque()
	{
		return $this->opaque;
	}
	
	// TODO: Document!
	public function setOpaque( $opaque )
	{
		$this->opaque = $opaque;
	}
	
	// TODO: Document!
	public function setPasswordsHashed( $truth )
	{
		$this->passwords_hashed = $truth;
	}
}
