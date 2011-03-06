<?php

/**
 * @package Prack_Wrapper
 * @abstract
 */
abstract class Prack_Wrapper_Abstract_Collection
{
	const DELEGATE = 'Prack_DelegateFor_Collection';
	
	protected $array;
	
	// TODO: Document!
	function __construct( $array = array() )
	{
		$this->array = $array;
	}
	
	// TODO: Document!
	function __call( $method, $args )
	{
		if ( method_exists( self::DELEGATE, $method ) )
		{
			array_unshift( $args, $this );
			return call_user_func_array( array( self::DELEGATE, $method ), $args );
		}
		
		$this_class = get_class( $this );
		throw new Prack_Error_Runtime_DelegationFailed( "cannot delegate {$method} in {$this_class}" );
	}

	abstract public function collect( $callback );
	
	// TODO: Document!
	public function length()
	{
		return sizeof( $this->array );
	}
	
	public function count() { return $this->length(); }
	public function size()  { return $this->length(); }
	
	// TODO: Document!
	public function isEmpty()
	{
		return ( $this->length() == 0 );
	}
	
	// TODO: Document!
	public function toN()
	{
		return $this->array;
	}
}