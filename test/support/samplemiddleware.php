<?php

class SampleMiddleware
  implements Prack_Interface_MiddlewareApp
{
	private $app;
	private $message;
	
	function __construct( $app = null, $message = "Hello world!" ) {
		$this->app     = $app;
		$this->message = $message;
	}
	
	public function call( &$env )
	{
		return array( 200, array( 'Content-Type' => 'text/html' ), $this->message );
	}
}