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
		$this->status           = (int)$status;
		$this->original_headers = $headers;
		
		$this->headers = new Prack_Utils_Response_HeaderHash();
		foreach ( $headers as $key => $values )
		{
			$this->headers->set( $key, $values );
			if ( empty( $values ) )
				$this->headers->set( $key, '' );
		}
		
		$this->body = '';
		
		if ( is_string( $body ) )
			$this->body = $body;
		else if ( $body instanceof Prack_Interface_Enumerable )
			$body->each( array( $this, 'onWrite' ) );
		else
			throw new Prack_Error_Type();
		
		if ( is_null( $errors ) )
			$errors = Prack_Utils_IO::withString( '' );
		if ( method_exists( $errors, 'string' ) )
			$this->errors = $errors->string();
	}
	
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
		
		throw new Prack_Error_Runtime_DelegationFailed( "Cannot delegate {$method} in Prack_Mock_Response." );
	}
	
	// TODO: Document!
	public function get( $key )
	{
		return $this->headers->get( $key );
	}
	
	// TODO: Document!
	public function matches( $pattern, &$matches = null )
	{
		return preg_match_all( $pattern, $this->body, $matches );
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