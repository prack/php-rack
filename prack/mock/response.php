<?php

// TODO: Document!
class Prack_Mock_FatalWarner
  implements Prack_Interface_WritableStreamlike
{
	// TODO: Document!
	public function puts()
	{
		$args = func_get_args();
		throw new Prack_Error_Mock_Response_FatalWarning( $args[ 0 ]->toN() );
	}
	
	// TODO: Document!
	public function write( $warning )
	{
		throw new Prack_Error_Mock_Response_FatalWarning( $warning->toN() );
	}
	
	// TODO: Document!
	public function flush()
	{
		// No-op.
		return true;
	}
	
	// TODO: Document!
	public function string()
	{
		return Prack::_String();
	}
}

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
		$headers = is_null( $headers ) ? Prack::_Hash() : $headers;
		if ( !( $headers instanceof Prack_Wrapper_Hash ) )
			throw new Prack_Error_Type( 'FAILSAFE: mock request $headers must be Prack_Wrapper_Hash' );
		$body = is_null( $body ) ? Prack::_String() : $body;
		if ( !( $body instanceof Prack_Interface_Stringable ) && !( $body instanceof Prack_Interface_Enumerable ) )
			throw new Prack_Error_Type( 'FAILSAFE: mock request $body must be Prack_Interface_Stringable or Prack_Interface_Enumerable' );
		$errors = is_null( $errors ) ? Prack_Utils_IO::withString() : $errors;
		if ( !( $errors instanceof Prack_Interface_WritableStreamlike ) )
			throw new Prack_Error_Type( 'FAILSAFE: mock request $errors must be Prack_Writable_Streamlike' );
		
		$this->status           = (int)$status;
		$this->original_headers = $headers;
		$this->headers          = Prack_Utils_Response_HeaderHash::using( Prack::_Hash() );
		
		foreach ( $headers->toN() as $key => $values )
		{
			$this->headers->set( $key, $values );
			if ( is_null( $values ) || $values->isEmpty() )
				$this->headers->set( $key, Prack::_String() );
		}
		
		$this->body = Prack::_String();
		
		if ( $body instanceof Prack_Wrapper_Stringable )
			$this->body = $body->toS();
		else if ( $body instanceof Prack_Interface_Enumerable )
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
		
		throw new Prack_Error_Runtime_DelegationFailed( "Cannot delegate {$method} in Prack_Mock_Response." );
	}
	
	// TODO: Document!
	public function get( $key )
	{
		return $this->headers->get( $key );
	}
	
	// TODO: Document!
	public function match( $pattern, &$matches = null )
	{
		return ( preg_match_all( $pattern, $this->body->toN(), $matches ) > 0 );
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