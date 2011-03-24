<?php

// TODO: Document!
class Prack_Config
  implements Prack_I_MiddlewareApp
{
	// TODO: Document!
	function __construct( $middleware_app, $callback )
	{
		$this->middleware_app = $middleware_app;
		$this->callback       = $callback;
	}
	
	// TODO: Document!
	public function call( $env )
	{
		call_user_func( $this->callback, $env );
		return $this->middleware_app->call( $env );
	}
}
