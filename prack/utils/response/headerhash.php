<?php

// TODO: Document!
class Prack_Utils_Response_HeaderHash extends Prack_Wrapper_Hash
  implements Prack_Interface_Enumerable
{
	private $names;
	private $on_each;
	
	// TODO: Document!
	static function using( $headers )
	{
		$headers = is_null( $headers ) ? Prack::_Hash() : $headers;
		if ( !( $headers instanceof Prack_Wrapper_Hash ) )
			throw new Prack_Error_Type( 'FAILSAFE: $headers is not a Prack_Wrapper_Hash or any subclass in method using ' );
			
		if ( $headers instanceof Prack_Utils_Response_HeaderHash )
			return $headers;
		return new Prack_Utils_Response_HeaderHash( $headers );
	}
	
	// TODO: Document!
	function __construct( $headers )
	{
		parent::__construct( $headers->toN() );
		
		$this->names = Prack::_Hash();
		
		foreach ( $headers->toN() as $key => $value )
			$this->set( $key, $value );
	}
	
	// TODO: Document!
	public function each( $callback )
	{
		if ( !is_callable( $callback ) )
			throw new Prack_Error_Callback();
		
		$this->on_each = $callback;
		$callback      = array( $this, 'onEach' );
		
		parent::each( $callback );
		
		$this->on_each = null;
	}
	
	// TODO: Document!
	public function onEach( $key, $value )
	{
		call_user_func( $this->on_each, $key, $this->distill( $value ) );
	}
	
	// TODO: Document!
	public function toHash()
	{
		$distilled = Prack::_Hash();
		
		foreach ( $this->array as $key => $value )
			$distilled->set( $key, $this->distill( $value ) );
			
		return $distilled;
	}
	
	// Note: Ruby method is confusing in its implementation.
	public function get( $key )
	{
		return parent::get( $this->names->get( strtolower( $key ) ) );
	}
	
	// TODO: Document!
	public function set( $key, $value )
	{
		$this->delete( $key );
		$this->names->set( strtolower( $key ), $key );
		$this->names->set( $key, $key );
		
		parent::set( $key, $value );
	}
	
	// TODO: Document!
	public function delete( $key )
	{
		$canonical = strtolower( $key );
		$result    = parent::delete( $this->names->delete( $canonical ) );
		
		foreach ( $this->names->toN() as $key => $value )
		{
			if ( strtolower( $key ) == $canonical )
				$this->names->delete( $key );
		}
		
		return $result;
	}
	
	// TODO: Document!
	public function contains( $key )
	{
		return ( $this->names->contains( $key ) || $this->names->contains( strtolower( $key ) ) );
	}
	
	// TODO: Document!
	public function mergeInPlace( $other )
	{
		$callback = array( $this, 'onMergeInPlace' );
		$other->each( $callback );
		return $this;
	}
	
	// TODO: Document!
	public function onMergeInPlace( $key, $value )
	{
		$this->set( $key, $value );
	}
	
	// TODO: Document!
	public function merge( $other )
	{
		$headerhash = clone $this;
		$headerhash->mergeInPlace( $other );
		return $headerhash;
	}
	
	// TODO: Document!
	public function replace( $other )
	{
		$this->clear();
		foreach ( $other->toN() as $key => $value )
			$this->set( $key, $value );
		return $this;
	}
	
	// TODO: Document!
	public function getNames()
	{
		return $this->names;
	}
	
	// TODO: Document!
	public function getEntries()
	{
		return $this->entries;
	}
	
		// TODO: Document!
	private function distill( $value )
	{
		if ( is_object( $value ) && method_exists( $value, 'toAry' ) )
			return $value->toAry()->join( Prack::_String( "\n" ) );
		else if ( $value instanceof Prack_Wrapper_String )
			return $value;
		throw new Prack_Error_Type( 'FAILSAFE: distill argument must be an object and respond to toAry' );
	}
}
