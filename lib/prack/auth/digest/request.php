<?php

// TODO: Document!
class Prack_Auth_Digest_Request extends Prack_Auth_Abstract_Request
{
	private $nonce;
	private $uri;
	
	// TODO: Document!
	function __call( $method, $args )
	{
		if ( $this->params()->contains( $method ) )
			return $this->params()->get( $method );
		throw new RuntimeException( 'attempt to access missing key in Prack_Auth_Digest_MockRequest' );
	}
	
	// TODO: Document!
	public function method()
	{
		return $this->env->contains( 'rack.methodoverride.original_method' )
		 ? $this->env->get( 'rack.methodoverride.original_method' )
		 : $this->env->get( 'REQUEST_METHOD'                      );
	}
	
	// TODO: Document!
	public function isDigest()
	{
		return ( $this->scheme()->raw() == 'digest' );
	}
	
	// TODO: Document!
	public function isCorrectURI()
	{
		return ( $this->env->get( 'SCRIPT_NAME' )->concat( $this->env->get( 'PATH_INFO' ) )->raw() == $this->uri()->raw() );
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
			$this->params = Prack_Auth_Digest_Params::parse( $this->parts()->last() );
		return $this->params;
	}
	
	
}
