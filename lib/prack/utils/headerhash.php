<?php

// TODO: Document!
class Prack_Utils_HeaderHash
{
	private $headers;
	private $names;
	
	// TODO: Document!
	static function using( $headers )
	{
		if ( $headers instanceof Prack_Utils_HeaderHash )
			return $headers;
		else if ( !is_array( $headers ) )
			throw new Exception( 'FAILSAFE: $headers is neither Prack_Utils_HeaderHash nor array' );
		
		return new Prack_Utils_HeaderHash( $headers );
	}
	
	// TODO: Document!
	function __construct( $headers = array() )
	{
		$this->headers = $headers;
		$this->names   = array();
		
		foreach ( $headers as $key => $value )
			$this->set( $key, $value );
	}
	
	// Note: Ruby method is confusing in its implementation.
	public function get( $key )
	{
		return @$this->headers[ @$this->names[ strtolower( $key ) ] ];
	}
	
	// TODO: Document!
	public function set( $key, $value )
	{
		$this->delete( $key );
		$this->names[ strtolower( $key ) ] = $key;
		$this->names[ $key ]               = $key;
		$this->headers[ $key ] = $value;
	}
	
	// TODO: Document!
	public function delete( $key )
	{
		$canonical = strtolower( $key );
		$result    = @$this->headers[ $this->names[ $canonical ] ];
		
		unset( $this->headers[ @$this->names[ $canonical ] ] );
		unset( $this->names[ $canonical ] );
		
		foreach ( $this->names as $key => $value )
			if ( strtolower( $key ) == $canonical )
				unset( $this->names[ $key ] );
		
		return $result;
	}
	
	// TODO: Document!
	public function contains( $key )
	{
		return ( @$this->names[ $key ] || @$this->names[ strtolower( $key ) ] );
	}
	
	// TODO: Document!
	public function length()
	{
		return count( $this->headers );
	}
	
	// TODO: Document!
	public function mergeInPlace( $other )
	{
		foreach ( $other as $key => $value )
			$this->set( $key, $value );
		
		return $this;
	}
	
	// TODO: Document!
	public function merge( $other )
	{
		$headerhash = clone $this;
		$headerhash->mergeInPlace( $other );
		return $headerhash;
	}
	
	// TODO: Document!
	public function clear()
	{
		$this->headers = array();
		return $this;
	}
	
	// TODO: Document!
	public function replace( $other )
	{
		$this->clear();
		foreach ( $other as $key => $value )
			$this->set( $key, $value );
		return $this;
	}
	
		// TODO: Document!
	public function raw()
	{
		$distilled = array();
		
		foreach ( $this->headers as $key => $value )
		{
			if ( is_array( $value ) )
				$value = join( "\n", $value );
			$distilled[ $key ] = $value;
		}
		
		return $distilled;
	}
}
