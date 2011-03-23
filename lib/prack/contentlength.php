<?php

// TODO: Document!
class Prack_ContentLength
  implements Prack_I_MiddlewareApp
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
		     ( $body instanceof Prb_I_Arraylike || $body instanceof Prb_I_Stringlike ) )
		{
			if ( $body instanceof Prb_I_Stringlike )
				$body = Prb::Ary( array( $body ) );
			
			static $callback = null;
			if ( is_null( $callback ) )
			  $callback = create_function(
			    '$accumulator, $part',
			    'return Prb::Num( $accumulator->raw() + Prack_Utils::singleton()->bytesize( $part ) );'
			);
			
			$length = $body->toAry()->inject( Prb::Num( 0 ), $callback );
			$headers->set( 'Content-Length', $length->toS() );
		}
		
		return Prb::Ary( array( $status, $headers, $body ) );
	}
}