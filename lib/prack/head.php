<?php

// TODO: Document!
class Prack_Head
  implements Prack_I_MiddlewareApp
{
	private $middleware_app;
	
	// TODO: Document!
	static function with( $middleware_app )
	{
		return new Prack_Head( $middleware_app );
	}
	
	// TODO: Document!
	function __construct( $middleware_app )
	{
		$this->middleware_app = $middleware_app;
	}
	
	// TODO: Document!
	public function call( $env )
	{
		list( $status, $headers, $body ) = $this->middleware_app->call( $env )->raw();
		
		if ( $env->get( 'REQUEST_METHOD' )->raw() == 'HEAD' )
			return Prb::Ary( array( $status, $headers, Prb::Ary() ) );
		
		return Prb::Ary( array( $status, $headers, $body ) );
	}
}