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
	public function call( &$env )
	{
		// PHP 5.2 Hack: call_user_func doesn't pass by reference. call_user_func_array can.
		call_user_func_array( $this->callback, array( &$env ) );
		return $this->middleware_app->call( $env );
	}
}
