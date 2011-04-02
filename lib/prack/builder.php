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
		return new Prack_Builder( $path, $this );
	}
	
	// TODO: Document!
	public function run( $middleware_app )
	{
		if ( $this->fi_class || $this->fi_args || $this->fi_callback )
			throw new Prb_Exception_Argument( 'cannot run middleware app until previous is fully specified--for help, consult Prack_Builder documentation' );
		
		if ( !($middleware_app instanceof Prack_I_MiddlewareApp ) )
			throw new Prb_Exception_Argument( 'run $middleware_map must be an instance of Prack_I_MiddlewareApp' );
		
		array_push( $this->stack, $middleware_app );
		
		return $this;
	}
	
	// TODO: Document!
	public function toMiddlewareApp()
	{
		$last = &$this->lastStackItem();
		if ( is_array( $last ) && $last[ 'type' ] == 'assoc' )
			$last = Prack_URLMap::with( $last[ 'values' ] );
		else if ( !( $last instanceof Prack_I_MiddlewareApp ) )
			throw new RuntimeException( 'you must specify exactly one run directive OR map urls within a given builder' );
		
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
		
		$stack_keys = array_keys( $this->stack );
		array_pop( $stack_keys );
		
		$middleware_app = $last;
		foreach( array_reverse( $stack_keys ) as $key )
		{
			$specification = $this->stack[ $key ];
			if ( !is_array( $specification ) )
				throw new RuntimeException( 'you must specify exactly one run directive OR map urls within a given builder' );
			
			array_unshift( $specification[ 'values' ], $middleware_app );
			$middleware_app = call_user_func_array( $callback, $specification[ 'values' ] );
		}
		
		return $middleware_app;
	}
	
	// TODO: Document!
	public function endMap() {
		$middleware_app = $this->toMiddlewareApp();
		
		if ( $this->parent )
		{
			$this->parent->registerMapping( $this->path, $middleware_app );
			return $this->parent;
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
	public function push()
	{
		if ( is_null( $this->fi_class ) )
			throw new Prb_Exception_Argument( 'attempt to specify middleware app failed: you must first provide the middleware app class with using' );
		
		if ( is_null( $this->fi_args ) )
			$this->fi_args = array();
		
		if ( isset( $this->fi_callback ) && !is_callable( $this->fi_callback ) )
			throw new Prb_Exception_Callback( 'callback specified in middleware app specification is not actually callable: ' );
		
		$this->pushSpecification( $this->fi_class, $this->fi_args, $this->fi_callback );
		$this->resetFluentInterface();
		
		return $this;
	}
	
	// TODO: Document!
	public function call( &$env )
	{
		return $this->toMiddlewareApp()->call( $env );
	}
	
	// TODO: Document!
	public function registerMapping( $path, $middleware_app )
	{
		$last = &$this->lastStackItem();
		
		if ( isset( $last ) && !is_array( $last ) )
			throw new RuntimeException( 'you must specify exactly one run directive OR map urls within a given builder' );
		else if ( !is_array( $last ) || $last[ 'type' ] !== 'assoc' )
		{
			array_push( $this->stack, array( 'type' => 'assoc', 'values' => array() ) );
			return $this->registerMapping( $path, $middleware_app );
		}
		
		$last[ 'values' ][ $path ] = $middleware_app;
	}
	
	// TODO: Document!
	public function pushSpecification( $class, $args, $callback )
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
	
	// TODO: Document!
	private function &lastStackItem()
	{
		$stack_keys = array_keys( $this->stack );
		$last_key   = array_pop( $stack_keys );
		
		$last = null;
		if ( $last_key !== null )
			$last = &$this->stack[ $last_key ];
		
		return $last;
	}
}