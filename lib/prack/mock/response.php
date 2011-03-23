<?php

# Rack::MockResponse provides useful helpers for testing your apps.
# Usually, you don't create the MockResponse on your own, but use
# MockRequest.
class Prack_Mock_Response
{
	const DELEGATE = 'Prack_DelegateFor_Response';
	
	private $status;
	private $original_headers;
	private $headers;
	private $body;
	private $errors;
	
	// TODO: Document!
	function __construct( $status, $headers, $body, $errors = null )
	{
		$status = is_null( $status ) ? Prb::Num() : $status;
		if ( !( $status instanceof Prb_Numeric ) )
			throw new Prb_Exception_Type( 'FAILSAFE: mock request $status must be Prb_Numeric' );
		
		$headers = is_null( $headers ) ? Prb::Hsh() : $headers;
		if ( !( $headers instanceof Prb_Hash ) )
			throw new Prb_Exception_Type( 'FAILSAFE: mock request $headers must be Prb_Hash' );
		
		$body = is_null( $body ) ? Prb::Str() : $body;
		if ( !( $body instanceof Prb_I_Stringlike ) && !( $body instanceof Prb_I_Enumerable ) )
			throw new Prb_Exception_Type( 'FAILSAFE: mock request $body must be Prb_I_Stringlike or Prb_I_Enumerable' );
		
		$errors = is_null( $errors ) ? Prb_IO::withString() : $errors;
		if ( !( $errors instanceof Prb_I_WritableStreamlike ) )
			throw new Prb_Exception_Type( 'FAILSAFE: mock request $errors must be Prack_Writable_Streamlike' );
		
		$this->status           = $status;
		$this->original_headers = $headers;
		$this->headers          = Prack_Utils_HeaderHash::using( Prb::Hsh() );
		
		foreach ( $headers->raw() as $key => $values )
		{
			$this->headers->set( $key, $values );
			if ( is_null( $values ) || $values->isEmpty() )
				$this->headers->set( $key, Prb::Str() );
		}
		
		$this->body = Prb::Str();
		
		if ( $body instanceof Prb_Stringable )
			$this->body = $body->toS();
		else if ( $body instanceof Prb_I_Enumerable )
			$body->each( array( $this, 'onWrite' ) );
		
		if ( method_exists( $errors, 'string' ) )
			$this->errors = $errors->string();
	}
	
	// TODO: Document!
	public function onWrite( $addition )
	{
		$this->body->concat( $addition );
	}
	  
	// TODO: Document!
	function __call( $method, $args )
	{
		if ( method_exists( self::DELEGATE, $method ) )
		{
			array_unshift( $args, $this );
			return call_user_func_array( array( self::DELEGATE, $method ), $args );
		}
		
		throw new Prb_Exception_Runtime_DelegationFailed( "Cannot delegate {$method} in Prack_Mock_Response." );
	}
	
	// TODO: Document!
	public function get( $key )
	{
		return $this->headers->get( $key );
	}
	
	// TODO: Document!
	public function match( $pattern, &$matches = null )
	{
		return ( preg_match_all( $pattern, $this->body->raw(), $matches ) > 0 );
	}
	
	// TODO: Document!
	public function getStatus()
	{
		return $this->status;
	}
	
	// TODO: Document!
	public function getHeaders()
	{
		return $this->headers;
	}
		
	// TODO: Document!
	public function getOriginalHeaders()
	{
		return $this->original_headers;
	}
	
	// TODO: Document!
	public function getBody()
	{
		return $this->body;
	}
	
	// TODO: Document!
	public function getErrors()
	{
		return $this->errors;
	}
	
	// TODO: Document!
	public function setErrors( $errors )
	{
		$this->errors = $errors;
	}
}