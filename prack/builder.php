<?php

// TODO: Document!
class Prack_Builder
{
	private $location;         // string containing fully-qualified mount point:
	                           //   If parent is mounted at '/admin' and this object is constructed with a location of
	                           //   '/console', this property will be set to '/admin/console'.
	private $parent;           // Prack_Builder instance which created this one
	private $middleware_stack; // array of indexed 'specification' arrays containing: 
	                           //   [ 0 ]=>string  (middleware class name)
	                           //   [ 1 ]=>array() (args for the specified middleware's constructor)
	private $endpoint;         // Prack_IMiddlewareApp OR indexed array of child builders
	                           //   NOTE: If array, lazily converted to Prack_URLMap in toMiddlewareApp().
	                           //   (Prack_URLMap is an instance of Prack_IMiddlewareApp, and is thus callable middleware)
	private $fi_using_class;   // string containing previous value on call to using(): state for fluent interface.
	
	// TODO: Document!
	function __construct( $location = null, $parent = null )
	{
		if ( isset( $location ) && isset( $parent ) )
		{
			$components     = array_filter( array( $parent->getLocation(), $location ), 'strlen' );
			$this->location = implode( '', $components );
		}
		else
			$this->location = $location;
		
		$this->parent           = $parent;
		$this->middleware_stack = array();
		$this->endpoint         = array();
	}
	
	// TODO: Document!
	static function domain()
	{
		return new Prack_Builder();
	}
	
	// TODO: Document!
	static function chain( $from, $to )
	{
		if ( empty( $from ) ) // Workaround for array_reduce() $initial arg limitations in PHP5.2
			return $to;         // First item passed in will likely be null. In that case, return $to.
		
		$class = $to[ 0 ];
		$args  = $to[ 1 ];
		array_unshift( $args, $from );
		
		$reflection     = new ReflectionClass( $class );
		$middleware_app = $reflection->newInstanceArgs( $args );
		
		return $middleware_app;
	}
	
	// TODO: Document!
	public function using( $middleware_class )
	{
		$this->setFIUsingClass( $middleware_class );
		return $this;
	}
	
	// TODO: Document!
	public function withArgs()
	{
		$middleware_class = $this->getFIUsingClass();
		
		if ( empty( $middleware_class ) )
			throw new Prack_Error_Builder_FluentInterfacePreconditionNotMet( 'withArgs() called without prior using() call' );
		
		$args = func_get_args();
		$this->specify( $middleware_class, $args );
		
		return $this;
	}
	
	// TODO: Document!
	public function run( $middleware_app )
	{
		if ( $this->isShallow() )
			throw new Prack_Error_Builder_ShallowEndpointRedeclared();
		else if ( $this->isDeep() )
			throw new Prack_Error_Builder_BothMapAndRunDeclaredAtSameLevel();
		
		$this->setEndpoint( $middleware_app );
		
		return $this->parent;
	}
	
	// TODO: Document!
	public function map( $location )
	{
		if ( $this->isShallow() )
			throw new Prack_Error_Builder_BothMapAndRunDeclaredAtSameLevel();
		
		$children = &$this->getEndpoint();
		foreach ( $children as $child )
		{
			if ( $location == $child->getLocation() )
				throw new Prack_Error_Builder_DuplicateMapping();
		}
		
		$builder_for_location = new Prack_Builder( $location, $this );
		array_push( $children, $builder_for_location );
		return $builder_for_location;
	}
	
	// TODO: Document!
	public function wherein()
	{
		return $this;
	}
	
	// TODO: Document!
	public function toMiddlewareApp()
	{
		$middleware_stack = $this->getMiddlewareStack();
		
		if ( $this->isShallow() )
			$inner_app = $this->getEndpoint();
		else if ( $this->isDeep() )
			$inner_app = new Prack_URLMap( $this->getEndpoint() );
		else
			throw new Prack_Error_Builder_NoMiddlewareSpecified();
		
		array_push( $middleware_stack, $inner_app );
		
		return array_reduce( array_reverse( $middleware_stack ), array( 'Prack_Builder', 'chain' ) );
	}
	
	// TODO: Document!
	public function toArray()
	{
		$matches  = array();
		$location = $this->getLocation();
		
		$host = '';
		if ( preg_match_all( '/\Ahttps?:\/\/(.*?)(\/.*)/', $location, $matches ) > 0 ) {
			$host     = $matches[ 1 ][0 ];
			$location = $matches[ 2 ][0 ];
		}
		
		if ( substr( $location, 0, 1 ) != '/' )
			throw new Prack_Error_Builder_ResourceLocationInvalid();
		
		$location            = chop( $location, '/' );
		$normalized_location = preg_replace( '/\//', '\/+', preg_quote( $location ) );
		$pattern             = "/\A{$normalized_location}(.*)/";
		
		return array( $host, $location, $pattern, $this->toMiddlewareApp() );
	}
	
	// TODO: Document!
	public function getLocation()
	{
		return $this->location;
	}
	
	// TODO: Document!
	public function getParent()
	{ 
		return $this->parent;
	}
	
	// TODO: Document!
	public function getMiddlewareStack()
	{
		return $this->middleware_stack;
	}
	
	// TODO: Document!
	public function &getEndpoint()
	{ 
		return $this->endpoint;
	}
	
	// TODO: Document!
	public function getFIUsingClass()
	{
		return $this->fi_using_class;
	}
	
	// TODO: Document!
	public function isShallow()
	{
		return ( $this->endpoint instanceof Prack_IMiddlewareApp );
	}
	
	// TODO: Document!
	public function isDeep() 
	{
		return is_array( $this->endpoint ) && !empty( $this->endpoint );
	}
	
	// TODO: Document!
	private function specify( $middleware_class, $args )
	{
		$specification = array( $middleware_class, $args );
		array_push( $this->middleware_stack, $specification );
	}
	
	// TODO: Document!
	private function setEndpoint( $endpoint )
	{
		$this->endpoint = $endpoint;
	}
	
	// TODO: Document!
	private function setFIUsingClass( $fi_using_class )
	{
		$this->fi_using_class = $fi_using_class;
	}
}