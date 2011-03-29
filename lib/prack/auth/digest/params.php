<?php

// TODO: Document!
class Prack_Auth_Digest_Params
{
	private $array;
	
	// TODO: Document!
	static function parse( $string )
	{
		$split = Prack_Auth_Digest_Params::splitHeaderValue( $string );
		
		$accumulator = new Prack_Auth_Digest_Params();
		foreach( $split as $value )
		{
			$split = preg_split( '/=/', $value, 2 );
			$accumulator->set( $split[ 0 ], Prack_Auth_Digest_Params::dequote( $split[ 1 ] ) );
		}
		
		return $accumulator;
	}
	
	// TODO: Document!
	static function dequote( $string )
	{
		$return = (bool)preg_match_all( '/\A"(.*)"\Z/', $string, $matches ) ? $matches[ 1 ][ 0 ] : $string;
		
		static $callback = null;
		if ( is_null( $callback ) )
			$callback = create_function( '$m', 'return $m;' );
		
		return preg_replace_callback( "/\\\\(.)/", $callback, $return );
	}
	
	// TODO: Document!
	static function splitHeaderValue( $string )
	{
		preg_match_all( '/(\w+\=(?:"[^\"]+"|[^,]+))/', $string, $matches );
		return $matches[ 1 ];
	}
	
	// TODO: Document!
	static function unquoted()
	{
		static $unquoted = null;
		
		if ( is_null( $unquoted ) )
			$unquoted = array( 'nc', 'stale' );
		
		return $unquoted;
	}
	
	// TODO: Document!
	function __construct( $callback = null )
	{
		$this->array = array();
		
		if ( is_callable( $callback ) )
			call_user_func( $callback, $this );
	}
	
	// TODO: Document!
	public function get( $key )
	{
		return @$this->array[ $key ];
	}
	
	// TODO: Document!
	public function set( $key, $value )
	{
		$this->array[ $key ] = $value;
	}
	
	// TODO: Document!
	public function contains( $key )
	{
		return isset( $this->array[ $key ] );
	}
	
	// TODO: Document!
	public function raw()
	{
		$accumulator = array();
		foreach ( $this->array as $key => $value )
		{
			$param = "{$key}=".( in_array( $key, self::unquoted() ) ? (string)$value : $this->quote( $value ) );
			array_push( $accumulator, $param );
		}
		return join( ', ', $accumulator );
	}
	
	// TODO: Document!
	public function quote( $string )
	{
		static $callback = null;
		if ( is_null( $callback ) )
			$callback = create_function( '$m', 'return "\\{$m}";' );
		return '"'.preg_replace_callback( '/[\\\"]/', $callback, $string ).'"';
	}
}
