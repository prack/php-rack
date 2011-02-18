<?php

// TODO: Document!
class Prack_Utils_Response_HeaderHash
{
	private $entries;
	private $translations;
	
	// TODO: Document!
	static function distill( $headerhash )
	{
		$distilled = array();
		
		foreach( $headerhash->getEntries() as $key => $value )
		{
			if ( is_array( $value ) )
				$distilled[ $key ] = implode( "\n", $value );
			else
				$distilled[ $key ] = $value;
		}
		
		return $distilled;
	}
	
	// TODO: Document!
	static function build( $with )
	{
		if ( $with instanceof Prack_Utils_Response_HeaderHash )
			return $with;
		
		return new Prack_Utils_Response_HeaderHash( $with );
	}
	
	// TODO: Document!
	function __construct( $raw = array() )
	{
		$this->entries      = $raw;
		$this->translations = array();
		foreach ( $raw as $key => $value )
			$this->set( $key, $value );
	}
	
	// TODO: Document!
	public function each( $callback )
	{
		$distilled = self::distill( $this );
		
		if ( is_callable( $callback ) )
			array_walk( $distilled, $callback );
	}
	
	// TODO: Document!
	// Note: Associative array.
	public function toArray()
	{
		return self::distill( $this );
	}
	
	// Note: Ruby method is confusing in its implementation.
	public function get( $key )
	{
		if ( array_key_exists( $key, $this->translations ) )
			$translated_key = $this->translations[ $key ];
		else
		{
			$downcased_key  = strtolower( $key );
			$translated_key = isset( $this->translations[ $downcased_key ] ) ? $this->translations[ $downcased_key ] : null;
		}
		
		// If translated_key has a value, then it's guaranteed to have a value in entries.
		return is_null( $translated_key ) ? null : $this->entries[ $translated_key ];
	}
	
	// TODO: Document!
	public function set( $key, $value )
	{
		$this->delete( $key );
		$this->translations[ $key ] = $this->translations[ strtolower( $key ) ] = $key;
		$this->entries[ $key ]      = $value;
	}
	
	// TODO: Document!
	public function delete( $key )
	{
		$canonical   = strtolower( $key );
		$translation = isset ( $this->translations[ $canonical ] ) ? $this->translations[ $canonical ] : null;
		$entry       = isset ( $this->entries[ $translation ] ) ? $this->entries[ $translation ] : null;
		
		unset( $this->translations[ $canonical ] );
		unset( $this->entries[ $translation ] );
		
		foreach ( $this->translations as $key => $value )
		{
			if ( strtolower( $key ) == $canonical )
				unset( $this->translations[ $key ] );
		}
		
		return $entry;
	}
	
	// TODO: Document!
	public function contains( $key )
	{
		return ( array_key_exists( $key, $this->translations ) || array_key_exists( strtolower( $key ), $this->translations ) );
	}
	
	// TODO: Document!
	public function hasKey  ( $key ) { return $this->contains( $key ); }
	
	// TODO: Document!
	public function isMember( $key ) { return $this->contains( $key ); }
	
	// TODO: Document!
	public function merge( $other )
	{
		$headerhash = clone $this;
		$merged     = $headerhash->mergeInPlace( $other );
		return $merged;
	}
	
	// TODO: Document!
	public function mergeInPlace( $other )
	{
		if ( $other instanceof Prack_Utils_Response_HeaderHash )
			$other = self::distill( $other );
		
		// Without closures, we have to do this a bit differently than Ruby.
		foreach ( $other as $key => $value )
			$this->set( $key, $value );
		
		return $this;
	}
	
	// TODO: Document!
	public function replace( $other )
	{
		if ( $other instanceof Prack_Utils_Response_HeaderHash )
			$other = self::distill( $other );
		
		$this->entries = array();
		
		// Without closures, we have to do this a bit differently than Ruby.
		foreach ( $other as $key => $value )
			$this->set( $key, $value );
			
		return $this;
	}
	
	// TODO: Document!
	public function length()
	{
		return count( $this->entries );
	}
	
	// TODO: Document!
	public function getTranslations()
	{
		return $this->translations;
	}
	
	// TODO: Document!
	public function getEntries()
	{
		return $this->entries;
	}
}
