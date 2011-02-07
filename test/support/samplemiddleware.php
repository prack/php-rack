<?php

class SampleMiddleware implements Prack_IMiddlewareApp
{
	function __construct() {
		$args = func_get_args();
	}
	
	public function call(&$env)
	{
		return array(200, array(), array());
	}
}