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
  implements Prb_I_Enumerable
{
	const DELEGATE = 'Prack_DelegateFor_Response';
	
	private $status;
	private $header;
	private $callback;
	private $after_finish;
	private $length;
	private $body;
	
	// TODO: Document!
	static function defaultHeaders()
	{
		static $default_headers = null;
		
		if ( is_null( $default_headers ) )
			$default_headers = array( 'Content-Type' => 'text/html' );
		
		return $default_headers;
	}
	
	// TODO: Document!
	static function with( $body = array(), $status = 200, $headers = array(), $on_build = null )
	{
		return new Prack_Response( $body, $status, $headers, $on_build );
	}
	
	// TODO: Document!
	function __construct( $body = array(), $status = 200, $headers = array(), $on_build = null )
	{
		if ( !is_string( $body ) && !is_array( $body ) && !( $body instanceof Prb_I_Enumerable ) )
			throw new Prb_Exception_Type( 'FAILSAFE: __construct $body must be either string or array of items castable to string' );
		
		$status  = (int)$status;
		$headers = $headers ? (array)$headers : array();
		
		$this->status       = $status;
		$this->header       = new Prack_Utils_HeaderHash( array_merge( self::defaultHeaders(), $headers ) );
		$this->writer       = array( $this, 'onWrite' );
		$this->callback     = null;
		$this->after_finish = null;
		$this->length       = 0;
		$this->body         = array();
		
		// Wrap the body if applicable so it has an interface.
		if ( is_string( $body ) )
			$this->write( $body );
		else if ( is_array( $body ) )
			array_walk( $body, $this->writer );
		else if ( $body instanceof Prb_I_Enumerable )
			$body->each( array( $this, 'write' ) );
		
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
		
		throw new Prb_Exception_Runtime_DelegationFailed( "cannot delegate {$method} in Prack_Response" );
	}
	
	public function onWrite( $addition )
	{
		array_push( $this->body, $addition );
	}
	
	// TODO: Document!
	# Append to body and update Content-Length.
	# NOTE: Do not mix #write and direct #body access!
	public function write( $buffer )
	{
		$this->length += strlen( $buffer );
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
	public function redirect( $target, $status = null )
	{
		$target = (string)$target;
		$status = is_null( $status ) ? 302 : (int)$status;
		
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
			return array( $this->status, $this->header->raw(), array() );
		}
		
		return array( $this->status, $this->header->raw(), $this );
	}
	
	// TODO: Document!
	public function raw()
	{
		return $this->finish();
	}
	
	// TODO: Document!
	public function each( $callback ) 
	{
		if ( !is_callable( $callback ) )
			throw new Prb_Exception_Callback();
		
		array_walk( $this->body, $callback );
		
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
		return ( $this->callback == null && empty( $this->body ) );
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