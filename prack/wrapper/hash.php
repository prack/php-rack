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
	public function get( $key )
	{
		$key = (string)$key;
		if ( !array_key_exists( $key, $this->array ) )
			return $this->getDefault();
		
		return $this->array[ $key ];
	}
	
	// TODO: Document!
	public function set( $key, $item )
	{
		$this->array[ $key ] = $item;
	}
	
	// TODO: Document!
	public function delete( $key )
	{
		$result = isset( $this->array[ $key ] ) ? $this->array[ $key ]
		                                        : $this->getDefault();
		unset( $this->array[ $key ] );
		return $result;
	}
	
		// TODO: Document!
	public function clear()
	{
		$this->array = array();
		return $this;
	}
	
	// TODO: Document!
	public function merge( $other )
	{
		$hash = clone $this;
		$hash->mergeInPlace( $other );
		return $hash;
	}
	
	// TODO: Document!
	public function mergeInPlace( $other )
	{
		foreach ( $other->toN() as $key => $value )
			$this->set( $key, $value );
		return $this;
	}
	
	public function update( $other ) { return $this->mergeInPlace( $other ); }
	
	// TODO: Document!
	public function each( $callback )
	{
		if ( !is_callable( $callback ) )
			throw new Prack_Error_Callback();
		
		// I disagree with array_walk function.
		foreach ( $this->array as $key => $value )
			call_user_func( $callback, $key, $value );
	}
	
	// TODO: Document!
	public function collect( $callback )
	{
		if ( !is_callable( $callback ) )
			throw new Prack_Error_Callback();
		
		$map = array();
		
		// I disagree with array_map function.
		foreach ( $this->array as $key => $item )
			array_push( $map, call_user_func( $callback, $key, $item ) );
		
		return Prack::_Array( $map );
	}
	
	public function map( $callback ) { return $this->collect( $callback ); }
	
	// TODO: Document!
	public function contains( $key )
	{
		return array_key_exists( $key, $this->array );
	}
	
	// TODO: Document!
	public function hasKey  ( $key ) { return $this->contains( $key ); }
	public function isMember( $key ) { return $this->contains( $key ); }
	
	// TODO: Document!
	public function slice()
	{
		$args = func_get_args();
		return call_user_func_array( array( $this, 'valuesAt' ), $args );
	}
	
	// TODO: Document!
	public function getDefault()
	{
		return is_null( $this->default ) ? null : clone $this->default;
	}
	
	// TODO: Document!
	public function setDefault( $default = null )
	{
		$this->default = $default;
	}
}