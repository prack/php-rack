<?php

// TODO: Document!
class Prack_Builder
  implements Prack_Interface_MiddlewareApp
{
	private $path;
	private $parent;
	private $stack;
	
	private $fi_class;
	private $fi_args;
	private $fi_callback;
	
	// TODO: Document!
	static function domain( $callback = null )
	{
		return new Prack_Builder( null, null, $callback );
	}
	
	// TODO: Document!
	function __construct( $path = null, $parent = null, $callback = null )
	{
		$this->path   = $path;
		$this->parent = $parent;
		$this->stack  = Prack::_Array();
		
		$this->fi_class    = null;
		$this->fi_args     = null;
		$this->fi_callback = null;
		
		if ( isset( $callback ) )
		{
			if ( !is_callable( $callback ) )
				throw new Prack_Error_Callback( '__construct $callback is not actually callable' );
			else
				call_user_func( $callback, $this );
		}
	}
	
	// TODO: Document!
	public function map( $path, $callback = null )
	{
		if ( $this->stack->last() instanceof Prack_Wrapper_Hash )
		{
			$child = new Prack_Builder( $path, $this, $callback );
			$this->stack->last()->set( $path, $child );
		}
		else
		{
			$this->stack->concat( Prack::_Hash() );
			return $this->map( $path, $callback );
		}
		
		return $child;
	}
	
	// TODO: Document!
	public function run( $middleware_app )
	{
		$this->stack->concat( $middleware_app );
		return is_null( $this->parent ) ? $this : $this->parent;
	}
	
	// TODO: Document!
	public function toMiddlewareApp()
	{
		if ( $this->stack->last() instanceof Prack_Wrapper_Hash )
			$this->stack->set( -1, Prack_URLMap::with( $this->stack->last() ) );
		
		static $callback = null;
		
		if ( is_null( $callback ) )
			$callback = create_function(
			  '$calling_middleware_app, $class, $args, $callback',
			  '$reflection = new ReflectionClass( $class );
			   $args       = $args->toN();
			   array_unshift( $args, $calling_middleware_app );
			   if ( isset( $callback ) )
			     array_push( $args, $callback );
			   return $reflection->newInstanceArgs( $args );'
			);
		$inner_middleware_app = $this->stack->last();
		return $this->stack->slice( 0, -1, true )->reverse()->inject( $inner_middleware_app, $callback );
	}
	
	// TODO: Document!
	public function using( $class )
	{
		if ( $this->fi_class || $this->fi_args || $this->fi_callback )
			throw new Prack_Error_Argument( 'cannot specify middleware app until previous is fully specified--for help, consult Prack_Builder documentation' );
		
		$this->fi_class = $class;
		return $this;
	}
	
	// TODO: Document!
	public function via( $class)
	{
		return $this->using( $class );
	}
	
	// TODO: Document!
	public function withArgs()
	{
		$args = func_get_args();
		$this->fi_args = $args;
		return $this;
	}
	
	// TODO: Document!
	public function withCallback( $callback )
	{
		$this->fi_callback = $callback;
		return $this;
	}
	
	// TODO: Document!
	public function andCallback( $callback )
	{
		return $this->withCallback( $callback );
	}
	
	// TODO: Document!
	public function build()
	{
		if ( is_null( $this->fi_class ) )
			throw new Prack_Error_Argument( 'attempt to specify middleware app failed: you must first provide the middleware app class with using' );
		
		if ( is_null( $this->fi_args ) )
			$this->fi_args = array();
		
		if ( isset( $this->fi_callback ) && !is_callable( $this->fi_callback ) )
			throw new Prack_Error_Callback( 'callback specified in middleware app specification is not actually callable: ' );
		
		$this->specify( $this->fi_class, Prack::_Array( $this->fi_args ), $this->fi_callback );
		
		$this->fi_class    = null;
		$this->fi_args     = null;
		$this->fi_callback = null;
		
		return $this;
	}
	
	// TODO: Document!
	public function call( $env )
	{
		return $this->toMiddlewareApp()->call( $env );
	}
	
	// TODO: Document!
	private function specify( $class, $args, $callback )
	{
		$this->stack->concat(
		  Prack::_Array( array( $class, $args, $callback ) )
		);
	}
}