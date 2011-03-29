<?php

// TODO: Document!
class Prack_Auth_Digest_Request extends Prack_Auth_Abstract_Request
{
	private $nonce;
	
	// TODO: Document!
	function __call( $method, $args )
	{
		if ( $this->params()->contains( $method ) )
			return $this->params()->get( $method );
		throw new RuntimeException( "attempt to access missing key {$method} in Prack_Auth_Digest_Request" );
	}
	
	// TODO: Document!
	public function method()
	{
		return @$this->env[ 'rack.methodoverride.original_method' ]
		 ? $this->env[ 'rack.methodoverride.original_method' ]
		 : $this->env[ 'REQUEST_METHOD' ];
	}
	
	// TODO: Document!
	public function isDigest()
	{
		return ( $this->scheme() == 'digest' );
	}
	
	// TODO: Document!
	public function isCorrectURI()
	{
		return ( (string)@$this->env[ 'SCRIPT_NAME' ].(string)@$this->env[ 'PATH_INFO' ] ) == $this->uri();
	}
	
	// TODO: Document!
	public function nonce()
	{
		if ( is_null( $this->nonce ) )
			$this->nonce = Prack_Auth_Digest_Nonce::parse( $this->params()->get( 'nonce' ) );
		return $this->nonce;
	}
	
	// TODO: Document!
	public function params()
	{
		if ( is_null( $this->params ) )
		{
			$parts = $this->parts();
			$this->params = Prack_Auth_Digest_Params::parse( end( $parts ) );
		}
		
		return $this->params;
	}
}
