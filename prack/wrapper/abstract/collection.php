<?php

/**
 * @package Prack_Wrapper
 * @abstract
 */
abstract class Prack_Wrapper_Abstract_Collection
{
	protected $array;
	
	// TODO: Document!
	function __construct( $array = array() )
	{
		$this->array = $array;
	}
	
	// TODO: Document!
	public function delete( $key )
	{
		$result = isset( $this->array[ $key ] ) ? $this->array[ $key ]
		                                        : null;
		unset( $this->array[ $key ] );
		return $result;
	}
	
	// TODO: Document!
	public function each( $callback )
	{
		if ( !is_callable( $callback ) )
			throw new Prack_Error_Callback();
	}
	
	// TODO: Document!
	public function length()
	{
		return count( $this->array );
	}
	
	public function count() { return $this->length(); }
	public function size()  { return $this->length(); }
	
	// TODO: Document!
	public function isEmpty()
	{
		return ( $this->length() == 0 );
	}
	
	// TODO: Document!
	public function getWrapped()
	{
		return $this->array;
	}
	
	/**
	 * Translates a negative index to a positive one.
	 * 
	 * Negative indexes, starting with -1, refer to the last object
	 * in an array. However, unlike positive indexes, out-of-range
	 * negative indexes cannot be used. For example, if an array
	 * contains 3 items, an index of -1 would translate to 2 (the
	 * largest index in the collection), and an index of -5 will
	 * result in null.
	 *
	 * A positive index of 6, on the other hand, is translated to 6,
	 * since out-of-bound positive indexes are allowed for certain
	 * operations.
	 *
	 * @access private
	 * @param integer $index index to be translated
	 * @return mixed integer if successfully translated; otherwise, null
	 */
	protected function translate( $index )
	{
		$translated = ( $index < 0 ) ? $this->length() + $index
		                             : $index;
		
		return ( $translated >= 0 ) ? $translated : null;
	}
	
	/**
	 * Translates a negative index into a positive one, raising
	 * an exception if the translation results in null.
	 * 
	 * @access private
	 * @param integer $index index to be translated
	 * @return integer
	 * @throws Prack_Error_Index
	 */
	protected function translateBang( $index )
	{
		$translated = $this->translate( $index );
		
		if ( is_null( $translated ) )
		{
			$class  = get_class( $this );
			$lowest = -$this->length();
			throw new Prack_Error_Index( "index too small for {$class}; minimum {$lowest}" );
		}
		
		return $translated;
	}
}