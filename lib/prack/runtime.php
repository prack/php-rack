<?php

// TODO: Document!
class Prack_Runtime
  implements Prack_Interface_MiddlewareApp
{
	private $middleware_app;
	private $header_name;
	
	// TODO: Document!
	static function with( $middleware_app, $header_name = null )
	{
		return new Prack_Runtime( $middleware_app, $header_name );
	}
	
	// TODO: Document!
	public function __construct( $middleware_app, $header_name = null )
	{
		$this->middleware_app = $middleware_app;
		$this->header_name    = 'X-Runtime'; // raw string since it defines a header
		if ( isset( $header_name ) )
			$this->header_name .= "-{$header_name}";
	}
	
	// TODO: Document!
	public function call( $env )
	{
		$start_time = Prb::_Time()->raw();
		list( $status, $headers, $body ) = $this->middleware_app->call( $env )->raw();
		$request_time = Prb::_Time( Prb::_Time()->raw() - $start_time );
		
		if ( !$headers->contains( $this->header_name ) )
			$headers->set( $this->header_name, Prb::_String( '%0.6f' )->sprintf( $request_time ) );
		
		return Prb::_Array( array( $status, $headers, $body ) );
	}
}