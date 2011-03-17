<?php

// TODO: Document!
class Prack_ContentLength
  implements Prack_Interface_MiddlewareApp
{
	private $middleware_app;
	
	// TODO: Document!
	static function with( $middleware_app )
	{
		return new Prack_ContentLength( $middleware_app );
	}
	
	// TODO: Document!
	public function __construct( $middleware_app )
	{
		$this->middleware_app = $middleware_app;
	}
	
	// TODO: Document!
	public function call( $env )
	{
		list( $status, $headers, $body ) = $this->middleware_app->call( $env )->raw();
		$headers = Prack_Utils_HeaderHash::using( $headers );
		
		if ( !Prack_Utils::singleton()->statusWithNoEntityBody()->contains( $status->raw() ) &&
		     !$headers->contains( 'Content-Length' ) &&
		     !$headers->contains( 'Transfer-Encoding' ) &&
		     ( method_exists( $body, 'toAry' ) || method_exists( $body, 'toStr' ) ) )
		{
			if ( method_exists( $body, 'toStr' ) )
				$body = Prb::_Array( array( $body ) );
			
			static $callback = null;
			if ( is_null( $callback ) )
			  $callback = create_function(
			    '$accumulator, $part',
			    'return Prb::_Numeric( $accumulator->raw() + Prack_Utils::singleton()->bytesize( $part ) );'
			);
			
			$length = $body->toAry()->inject( Prb::_Numeric( 0 ), $callback );
			$headers->set( 'Content-Length', $length->toS() );
		}
		
		return Prb::_Array( array( $status, $headers, $body ) );
	}
}