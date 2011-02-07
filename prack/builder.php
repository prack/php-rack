<?php

class Prack_Builder
{
	private $context;
	private $middleware;
	
	// Fluent interface state:
	private $parent_builder;
	private $mw_class;
	
	function __construct($parent_builder = null) 
	{
		$this->parent_builder = $parent_builder;
		$this->context        = null;
		$this->middleware     = array();
	}
	
	public static function build()
	{
		return new Prack_Builder();
	}
	
	public function using($mw_class)
	{
		$this->mw_class = $mw_class;
		return $this;
	}
	
	public function withArgs() 
	{
		if ( empty( $this->mw_class ) )
			throw new Prack_Error_FluentInterfacePreconditionFailed();
		
		$class = $this->mw_class;
		$args  = func_get_args();
		
		if( empty( $args ) )
			$middleware = new $class;
		else 
		{
			$reflection = new ReflectionClass($class);
			$middleware = $reflection->newInstanceArgs($args);
		}
		
		array_push($this->middleware, $middleware);
		
		return $this;
	}
	
	public function run() 
	{
		return $this->context;
	}
	
	public function getContext() 
	{
		return $this->context;
	}
	
	public function getMiddleware() 
	{
		return $this->middleware;
	}
	
	public function getStateMiddlewareClass() 
	{
		return $this->mw_class;
	}
}