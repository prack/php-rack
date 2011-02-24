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
	static function wrap( $item )
	{
		if ( is_string( $item ) || $item instanceof Prack_Interface_Stringable )
			return Prack_Wrapper_String::with( (string)$item );
		
		else if ( is_array( $item ) )
			return Prack_Wrapper_Array::with( $item );
		
		return $item;
	}
	
	// TODO: Document!
	function __construct( $body = array(), $status = 200, $headers = array(), $on_build = null )
	{
		$this->status       = (int)$status;
		$this->header       = new Prack_Utils_Response_HeaderHash( array_merge( array( 'Content-Type' => 'text/html' ), $headers ) );
		
		$this->writer       = array( $this, 'onWrite' );
		$this->callback     = null;
		$this->after_finish = null;
		$this->length       = 0;
		
		$this->body         = Prack_Wrapper_Array::with( array() );
		
		// Wrap the body if applicable so it has an interface.
		$body = self::wrap( $body );
		
		if ( $body instanceof Prack_Wrapper_String )
			$this->write( $body );
		else if ( $body instanceof Prack_Interface_Enumerable )
			$body->each( array( $this, 'onWrite' ) );
		else
			throw new Prack_Error_Type();
		
		if ( isset( $on_build ) && is_callable( $on_build ) )
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
		
		throw new Prack_Error_Runtime_DelegationFailed( "Cannot delegate {$method} in Prack_Response." );
	}
	
	public function onWrite( $addition )
	{
		return $this->body->push( (string)$addition );
	}
	
	// TODO: Document!
	# Append to body and update Content-Length.
	# NOTE: Do not mix #write and direct #body access!
	public function write( $buffer )
	{
		$this->length += ( $buffer instanceof Prack_Wrapper_String ) ? $buffer->length()
		                                                             : strlen( (string)$buffer );
		call_user_func( $this->writer, $buffer );
		$this->set( 'Content-Length', (string)$this->length );
		
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
		$this->set( 'Location', $target );
		$this->status = $status;
	}
	
	// TODO: Document!
	public function finish( $callback = null )
	{
		$this->callback = $callback;
		
		if ( in_array( (int)$this->status, array( 204, 304 ) ) )
		{
			$this->header->delete( 'Content-Type' );
			return array( (int)$this->status, $this->header->toArray(), Prack_Wrapper_Array::with( array() ) );
		}
		
		return array( (int)$this->status, $this->header->toArray(), $this );
	}
	
	// TODO: Document!
	public function toArray() 
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