<?php

// TODO: Document!
# Rack::Response provides a convenient interface to create a Rack
# response.
#
# It allows setting of headers and cookies, and provides useful
# defaults (a OK response containing HTML).
#
# You can use Response#write to iteratively generate your response,
# but note that this is buffered by Rack::Response until you call
# +finish+.  +finish+ however can take a block inside which calls to
# +write+ are syncronous with the Rack response.
#
# Your application's +call+ should end returning Response#finish.
class Prack_Response
  implements Prack_Interface_Enumerable
{
	const DELEGATE = 'Prack_DelegateFor_Response';
	
	private $status;
	private $headers;
	private $callback;
	private $after_finish;
	private $length;
	private $body;
	
	// TODO: Document!
	static function defaultHeaders()
	{
		static $default_headers = null;
		
		if ( is_null( $default_headers ) )
			$default_headers = Prack::_Hash( 
				array( 'Content-Type' => Prack::_String( 'text/html' )
			) );
		
		return $default_headers;
	}
	
	// TODO: Document!
	static function with( $body = null, $status = 200, $headers = null, $on_build = null )
	{
		return new Prack_Response( $body, $status, $headers, $on_build );
	}
	
	// TODO: Document!
	function __construct( $body = null, $status = 200, $headers = null, $on_build = null )
	{
		$body = is_null( $body ) ? Prack::_Array() : $body;
		if ( !( $body instanceof Prack_Interface_Stringable ) && !( $body instanceof Prack_Interface_Enumerable ) )
			throw new Prack_Error_Type( 'FAILSAFE: __construct $body must be Prack_Interface_Stringable or Prack_Interface_Enumerable' );
		
		$status = is_null( $status ) ? 200 : (int)$status;
		
		$headers = is_null( $headers ) ? self::defaultHeaders() : $headers;
		if ( !( $headers instanceof Prack_Wrapper_Hash ) )
			throw new Prack_Error_Type( 'FAILSAFE: __construct $headers an instance of Prack_Wrapper_Hash' );
		
		$this->status       = (int)$status;
		$this->header       = new Prack_Utils_Response_HeaderHash( self::defaultHeaders()->merge( $headers ) );
		$this->writer       = array( $this, 'onWrite' );
		$this->callback     = null;
		$this->after_finish = null;
		$this->length       = 0;
		$this->body         = Prack::_Array();
		
		// Wrap the body if applicable so it has an interface.
		if ( $body instanceof Prack_Interface_Stringable )
			$this->write( $body->toS() );
		else if ( $body instanceof Prack_Interface_Enumerable )
			$body->each( $this->writer );
		
		if ( is_callable( $on_build ) )
			call_user_func( $on_build, $this );
	}
	
	// TODO: Document!
	function __call( $method, $args )
	{
		if ( method_exists( self::DELEGATE, $method ) )
		{
			array_unshift( $args, $this );
			return call_user_func_array( array( self::DELEGATE, $method ), $args );
		}
		
		throw new Prack_Error_Runtime_DelegationFailed( "cannot delegate {$method} in Prack_Response" );
	}
	
	public function onWrite( $addition )
	{
		return $this->body->push( $addition );
	}
	
	// TODO: Document!
	# Append to body and update Content-Length.
	# NOTE: Do not mix #write and direct #body access!
	public function write( $buffer )
	{
		$this->length += $buffer->length();
		call_user_func( $this->writer, $buffer );
		$this->set( 'Content-Length', Prack::_String( (string)$this->length ) );
		return $buffer;
	}
	
	// TODO: Document!
	public function get( $header )
	{
		return $this->header->get( $header );
	}
	
	// TODO: Document!
	public function set( $header, $value )
	{
		$this->header->set( $header, $value );
	}
	
	// FIXME: Implement cookie handling
	// TODO: Document!
	/*
	public function setCookie( $key, $value )
	{
		Prack_Utils::setCookieHeader( $this->header, $key, $value );
	}
	
	// TODO: Document!
	public function deleteCookie( $key, $value = array() )
	{
		Prack_Utils::deleteCookieHeader( $this->header, $key, $value );
	}
	*/
	
	// TODO: Document!
	public function redirect( $target, $status = 302 )
	{
		if ( !( $target instanceof Prack_Interface_Stringable ) )
			throw new Prack_Error_Type( 'redirect argument must be Stringable' );
		
		$this->set( 'Location', $target->toS() );
		$this->status = $status;
	}
	
	// TODO: Document!
	public function finish( $callback = null )
	{
		$this->callback = $callback;
		
		if ( in_array( (int)$this->status, array( 204, 304 ) ) )
		{
			$this->header->delete( 'Content-Type' );
			return array( (int)$this->status, $this->header->toHash(), Prack::_Array() );
		}
		
		return array( (int)$this->status, $this->header->toHash(), $this );
	}
	
	// TODO: Document!
	public function toN() 
	{
		return $this->finish();
	}
	
	// TODO: Document!
	public function each( $callback ) 
	{
		if ( !is_callable( $callback ) )
			throw new Prack_Error_Callback();
			
		$this->body->each( $callback );
		$this->writer = $callback;
		
		if ( is_callable( $this->callback ) )
			call_user_func( $this->callback, $this );
	}
	
	// TODO: Document!
	public function close()
	{
		if ( method_exists( $this->body, 'close' ) )
			$this->body->close();
	}
	
	// TODO: Document!
	public function isEmpty()
	{
		return ( $this->callback == null && $this->body->isEmpty() );
	}
	
	// TODO: Document!
	public function getLength()
	{
		return $this->length;
	}
	
	// TODO: Document!
	public function setLength( $length )
	{
		$this->length = $length;
	}
	
	// TODO: Document!
	public function getBody()
	{
		return $this->body;
	}
	
	// TODO: Document!
	public function setBody( $body )
	{
		$this->body = $body;
	}
	
	// TODO: Document!
	public function getHeader()
	{
		return $this->header;
	}
	
	// TODO: Document!
	public function getHeaders()
	{
		return $this->getHeader();
	}
	
	// TODO: Document!
	public function getStatus()
	{
		return $this->status;
	}
	
	// TODO: Document!
	public function setStatus( $status )
	{
		$this->status = $status;
	}
}