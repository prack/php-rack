<?php

/**
 * Class wrapping a PHP as a key/value store object.
 * 
 * This class enforces array key values as strings. When
 * accessing values in the array, all key references are coerced
 * to string values.
 *
 * @package Prack_Wrapper
 */
class Prack_Wrapper_Hash extends Prack_Wrapper_Abstract_Collection
	implements Prack_Interface_Enumerable
{
	private $default;
	
	// TODO: Document!
	static function with( $array )
	{
		return new Prack_Wrapper_Hash( $array );
	}
	
	// TODO: Document!
	public function get( $key )
	{
		$key = (string)$key;
		if ( !array_key_exists( $key, $this->array ) )
			return isset( $this->default ) ? $this->default : null;
		
		return $this->array[ $key ];
	}
	
	// TODO: Document!
	public function set( $key, $item )
	{
		$this->array[ (string)$key ] = $item;
	}
	
	// TODO: Document!
	public function each( $callback )
	{
		parent::each( $callback );
		
		foreach ( $this->array as $key => $value )
			call_user_func( $callback, $key, $value );
	}
	
	// TODO: Document!
	public function getDefault()
	{
		return $this->default;
	}
	
	// TODO: Document!
	public function setDefault( $default = null )
	{
		$this->default = $default;
	}
}