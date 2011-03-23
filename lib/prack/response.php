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
			$default_headers = Prb::Hsh( 
				array( 'Content-Type' => Prb::Str( 'text/html' )
			) );
		
		return $default_headers;
	}
	
	// TODO: Document!
	static function with( $body = null, $status = null, $headers = null, $on_build = null )
	{
		return new Prack_Response( $body, $status, $headers, $on_build );
	}
	
	// TODO: Document!
	function __construct( $body = null, $status = null, $headers = null, $on_build = null )
	{
		$body = is_null( $body ) ? Prb::Ary() : $body;
		if ( !( $body instanceof Prb_I_Stringlike ) && !( $body instanceof Prb_I_Enumerable ) )
			throw new Prb_Exception_Type( 'FAILSAFE: __construct $body must be Prb_I_Stringlike or Prb_I_Enumerable' );
		
		$status = is_null( $status ) ? Prb::Num( 200 ) : $status->toN();
		if ( !( $status instanceof Prb_Numeric ) )
			throw new Prb_Exception_Type( 'FAILSAFE: __construct $status must be Prb_Numeric' );
		
		$headers = is_null( $headers ) ? self::defaultHeaders() : $headers;
		if ( !( $headers instanceof Prb_Hash ) )
			throw new Prb_Exception_Type( 'FAILSAFE: __construct $headers an instance of Prb_Hash' );
		
		$this->status       = $status;
		$this->header       = new Prack_Utils_HeaderHash( self::defaultHeaders()->merge( $headers ) );
		$this->writer       = array( $this, 'onWrite' );
		$this->callback     = null;
		$this->after_finish = null;
		$this->length       = 0;
		$this->body         = Prb::Ary();
		
		// Wrap the body if applicable so it has an interface.
		if ( $body instanceof Prb_I_Stringlike )
			$this->write( $body->toS() );
		else if ( $body instanceof Prb_I_Enumerable )
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
		
		throw new Prb_Exception_Runtime_DelegationFailed( "cannot delegate {$method} in Prack_Response" );
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
		$this->set( 'Content-Length', Prb::Str( (string)$this->length ) );
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
		if ( !( $target instanceof Prb_I_Stringlike ) )
			throw new Prb_Exception_Type( 'redirect $target must be Prack_I_Stringable' );
		
		$status = is_null( $status ) ? Prb::Num( 302 ) : $status;
		if ( !( $status instanceof Prb_Numeric ) )
			throw new Prb_Exception_Type( 'redirect $status must be Prb_Numeric' );
		
		$this->set( 'Location', $target->toS() );
		$this->status = $status;
	}
	
	// TODO: Document!
	public function finish( $callback = null )
	{
		$this->callback = $callback;
		
		if ( in_array( (int)$this->status->raw(), array( 204, 304 ) ) )
		{
			$this->header->delete( 'Content-Type' );
			return Prb::Ary( array(
			  $this->status,
			  $this->header->toHash(),
			  Prb::Ary()
			) );
		}
		
		return Prb::Ary( array( $this->status, $this->header->toHash(), $this ) );
	}
	
	// TODO: Document!
	public function toA() 
	{
		return $this->finish();
	}
	
	// TODO: Document!
	public function raw()
	{
		return $this->toA()->raw();
	}
	
	// TODO: Document!
	public function each( $callback ) 
	{
		if ( !is_callable( $callback ) )
			throw new Prb_Exception_Callback();
			
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