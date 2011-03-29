<?php

// TODO: Document!
class Prack_Builder
  implements Prack_I_MiddlewareApp
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
		$this->stack  = array();
		
		$this->resetFluentInterface();
		
		if ( isset( $callback ) )
		{
			if ( !is_callable( $callback ) )
				throw new Prb_Exception_Callback( '__construct $callback is not actually callable' );
			else
				call_user_func( $callback, $this );
		}
	}
	
	// TODO: Document!
	public function map( $path, $callback = null )
	{
		$stack_keys = array_keys( $this->stack );
		$last_key   = array_pop( $stack_keys );
		
		$last = null;
		if ( $last_key !== null )
			$last = &$this->stack[ $last_key ];
		
		if ( $last && $last[ 'type' ] == 'assoc' )
		{
			$child = new Prack_Builder( $path, $this, $callback );
			$last[ 'values' ][ $path ] = $child;
		}
		else
		{
			array_push( $this->stack, array( 'type' => 'assoc', 'values' => array() ) );
			return $this->map( $path, $callback );
		}
		
		return $child;
	}
	
	// TODO: Document!
	public function run( $middleware_app )
	{
		if ( $this->fi_class || $this->fi_args || $this->fi_callback )
			throw new Prb_Exception_Argument( 'cannot run middleware app until previous is fully specified--for help, consult Prack_Builder documentation' );
		
		if ( !($middleware_app instanceof Prack_I_MiddlewareApp ) )
			throw new Prb_Exception_Argument( 'run $middleware_map must be an instance of Prack_I_MiddlewareApp' );
		
		array_push( $this->stack, $middleware_app );
		
		return is_null( $this->parent ) ? $this : $this->parent;
	}
	
	// TODO: Document!
	public function toMiddlewareApp()
	{
		$stack_keys = array_keys( $this->stack );
		$last_key   = array_pop( $stack_keys );
		
		$last = null;
		if ( $last_key !== null )
			$last = &$this->stack[ $last_key ];
		
		if ( is_array( $last ) && $last[ 'type' ] == 'assoc' )
		{
			$this->stack[ $last_key ] = Prack_URLMap::with( $last[ 'values' ] );
			$last = &$this->stack[ $last_key ];
		}
		
		static $callback = null;
		if ( is_null( $callback ) )
			$callback = create_function(
			  '$calling_middleware_app, $class, $args, $callback',
			  '$reflection = new ReflectionClass( $class );
			   array_unshift( $args, $calling_middleware_app );
			   if ( isset( $callback ) )
			     array_push( $args, $callback );
			   return $reflection->newInstanceArgs( $args );'
			);
		
		$middleware_app = $last;
		foreach( array_reverse( $stack_keys ) as $key )
		{
			$specification = $this->stack[ $key ][ 'values' ];
			array_unshift( $specification, $middleware_app );
			$middleware_app = call_user_func_array( $callback, $specification );
		}
		
		return $middleware_app;
	}
	
	// TODO: Document!
	public function using( $class )
	{
		if ( $this->fi_class || $this->fi_args || $this->fi_callback )
			throw new Prb_Exception_Argument( 'cannot specify middleware app until previous is fully specified--for help, consult Prack_Builder documentation' );
		
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
			throw new Prb_Exception_Argument( 'attempt to specify middleware app failed: you must first provide the middleware app class with using' );
		
		if ( is_null( $this->fi_args ) )
			$this->fi_args = array();
		
		if ( isset( $this->fi_callback ) && !is_callable( $this->fi_callback ) )
			throw new Prb_Exception_Callback( 'callback specified in middleware app specification is not actually callable: ' );
		
		$this->specify( $this->fi_class, $this->fi_args, $this->fi_callback );
		$this->resetFluentInterface();
		
		return $this;
	}
	
	// TODO: Document!
	public function call( &$env )
	{
		return $this->toMiddlewareApp()->call( $env );
	}
	
	// TODO: Document!
	private function specify( $class, $args, $callback )
	{
		array_push( $this->stack, array( 'type' => 'indexed', 'values' => array( $class, $args, $callback ) ) );
	}
	
	// TODO: Document!
	public function resetFluentInterface()
	{
		$this->fi_class    = null;
		$this->fi_args     = null;
		$this->fi_callback = null;
	}
	
}