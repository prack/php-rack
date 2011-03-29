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
	function __construct( $status = 200, $headers = array(), $body = '', $errors = null )
	{
		if ( !is_string( $body ) && !is_array( $body ) && !( $body instanceof Prb_I_Enumerable ) )
			throw new Prb_Exception_Type( 'FAILSAFE: __construct $body must be either string or array of items castable to string' );
		
		if ( is_null( $headers ) )
			$headers = array();
		if ( !is_array( $headers ) )
			throw new Prb_Exception_Type( 'FAILSAFE: __construct $headers must be an array' );
		
		$status  =   (int)$status;
		$headers = (array)$headers;
		
		$errors = is_null( $errors ) ? Prb_IO::withString() : $errors;
		if ( !( $errors instanceof Prb_I_WritableStreamlike ) )
			throw new Prb_Exception_Type( 'FAILSAFE: mock request $errors must be Prack_Writable_Streamlike' );
		
		$this->status           = (int)$status;
		$this->original_headers = (array)$headers;
		$this->headers          = new Prack_Utils_HeaderHash();
		
		foreach ( $headers as $key => $values )
		{
			$this->headers->set( $key, $values );
			if ( is_null( $values ) || empty( $values ) )
				$this->headers->set( $key, '' );
		}
		
		$this->body = '';
		
		if ( is_string( $body ) )
			$this->body = $body;
		else if ( is_array( $body ) )
			array_walk( $body, array( $this, 'onWrite' ) );
		else if ( $body instanceof Prb_I_Enumerable )
			$body->each( array( $this, 'onWrite' ) );
		
		if ( method_exists( $errors, 'string' ) )
			$this->errors = $errors->string();
	}
	
	// TODO: Document!
	public function onWrite( $addition )
	{
		$this->body .= $addition;
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
		return (bool)preg_match( $pattern, $this->body, $matches );
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