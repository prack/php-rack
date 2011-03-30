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
		
		$header_hash = Prack_Utils_HeaderHash::using( $headers );
		if ( !in_array( $status, Prack_Utils::singleton()->statusWithNoEntityBody() ) &&
		     !$header_hash->contains( 'Content-Length' ) && !$header_hash->contains( 'Transfer-Encoding' )
		     && ( is_string( $body ) || is_array( $body ) || $body instanceof Prb_Enumerable ) )
		{
			$response = Prack_Response::with( $body, $status, $headers );
			
			static $callback = null;
			if ( is_null( $callback ) )
			  $callback = create_function(
			    '$accumulator, $part',
			    'return $accumulator + Prack_Utils::singleton()->bytesize( $part );'
			  );
			
			$accumulator = 0;
			foreach ( $response->getBody() as $part )
				$accumulator = call_user_func( $callback, $accumulator, $part );
			
			$response->set( 'Content-Length', (string)$accumulator );
			
			list( $status, $headers, $body ) = $response->finish();
		}
		
		return array( $status, $headers, $body );
	}
}