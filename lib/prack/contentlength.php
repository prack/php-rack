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
	public function call( &$env )
	{
		list( $status, $headers, $body ) = $this->middleware_app->call( $env );
		$headers = Prack_Utils_HeaderHash::using( $headers );
		
		if ( !in_array( $status, Prack_Utils::singleton()->statusWithNoEntityBody() ) &&
		     !@$headers->contains( 'Content-Length' ) && !$headers->contains( 'Transfer-Encoding' )
		     && ( is_string( $body ) || is_array( $body ) || $body instanceof Prb_I_Enumerable ) )
		{
			if ( is_string( $body ) )
				$body = array( $body );
			
			static $callback = null;
			if ( is_null( $callback ) )
			  $callback = create_function(
			    '$accumulator, $part',
			    'return $accumulator + Prack_Utils::singleton()->bytesize( $part );'
			  );
			
			$accumulator = 0;
			if ( $body instanceof Prb_I_Enumerable )
				$length = $body->toAry()->inject( 0, $callback );
			else
			{
				$accumulator = 0;
				foreach ( $body as $part )
					$accumulator = call_user_func( $callback, $accumulator, $part );
			}
			$headers->set( 'Content-Length', (string)$accumulator );
		}
		
		return array( $status, $headers->raw(), $body );
	}
}