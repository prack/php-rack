<?php

/**
 * Class wrapping a PHP array as indexed-only array object.
 * 
 * This class enforces array key values as integers. When
 * accessing values in the array, all key references are coerced
 * to integer values. Also, unlike traditional PHP arrays, index
 * values are continuous: if a value is inserted at an index greater
 * than the current capacity, intermediate keys will be created and
 * their respective values will be set to null.
 *
 * @package Prack_Wrapper
 */
class Prack_Wrapper_Array extends Prack_Wrapper_Abstract_Collection
  implements Prack_Interface_Enumerable
{
	// TODO: Document!
	static function with( $array )
	{
		return new Prack_Wrapper_Array( $array );
	}
	
	// TODO: Document!
	public function get( $key )
	{
		$key = (int)$key;
		if ( !array_key_exists( $key, $this->array ) )
			return null;
		
		return $this->array[ $key ];
	}
	
	// TODO: Document!
	public function set( $index, $item )
	{
		$index  = $this->translateBang( (int)$index );
		$length = $this->length();
		
		if ( $index > $length )
			for ( $i = 0; $i < ( $index - $length ); $i++ )
				$this->array[ $length + $i ] = null;
		
		$this->array[ $index ] = $item;
	}
	
	// TODO: Document!
	public function each( $callback )
	{
		parent::each( $callback );
		
		foreach ( $this->array as $key => $item )
			call_user_func( $callback, $item );
	}
	
	// TODO: Document!
	public function push( $item )
	{
		array_push( $this->array, $item );
	}
	
	// TODO: Document!
	public function pop()
	{
		return array_pop( $this->array );
	}
	
	// TODO: Document!
	public function unshift( $item )
	{
		array_unshift( $this->array, $item );
	}
	
	// TODO: Document!
	public function shift()
	{
		return array_shift( $this->array );
	}
}