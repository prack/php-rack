<?php

// TODO: Document!
class Prack_Auth_Digest_Params extends Prb_Hash
{
	// TODO: Document!
	static function parse( $string )
	{
		$callback = array( 'Prack_Auth_Digest_Params', 'onParse' );
		return Prack_Auth_Digest_Params::splitHeaderValue( $string )->inject(
		  new Prack_Auth_Digest_Params(),
		  $callback
		);
	}
	
	// TODO: Document!
	static function onParse( $header, $param )
	{
		$split = $param->split( '/=/', 2 );
		$header->set(
		  $split->get( 0 )->raw(),
		  Prack_Auth_Digest_Params::dequote( $split->get( 1 ) )
		);
		return $header;
	}
	
	// TODO: Document!
	static function dequote( $string )
	{
		$return = $string->match( '/\A"(.*)"\Z/', $matches ) ? Prb::Str( $matches[ 1 ][ 0 ] ) : $string;
		
		static $callback = null;
		if ( is_null( $callback ) )
			$callback = create_function( '$m', 'return $m;' );
		
		$return = $return->gsub( "/\\\\(.)/", $callback );
		
		return $return;
	}
	
	// TODO: Document!
	static function splitHeaderValue( $string )
	{
		static $callback = null;
		if ( is_null( $callback ) )
			$callback = create_function( '$v', 'return $v;' );
		
		return $string->scan( '/(\w+\=(?:"[^\"]+"|[^,]+))/' )->collect( $callback );
	}
	
	// TODO: Document!
	static function unquoted()
	{
		static $unquoted = null;
		
		if ( is_null( $unquoted ) )
		{
			$unquoted = Prb::Ary( array(
				Prb::Str( 'nc'    ),
				Prb::Str( 'stale' ),
			) );
		}
		
		return $unquoted;
	}
	
	// TODO: Document!
	function __construct( $callback = null )
	{
		parent::__construct();
		
		if ( is_callable( $callback ) )
			call_user_func( $callback, $this );
	}
	
	// TODO: Document!
	public function get( $key )
	{
		return parent::get( $key );
	}
	
	// TODO: Document!
	public function set( $key, $value )
	{
		return parent::set( $key, $value->toS() );
	}
	
	// TODO: Document!
	public function toS()
	{
		static $callback = null;
		if ( is_null( $callback ) )
			$callback = array( $this, 'onToS' );
		
		return $this->inject( Prb::Ary(), $callback )->join( Prb::Str( ', ' ) );
	}
	
	// TODO: Document!
	public function onToS( $parts, $key, $value )
	{
		$parts->concat(
		  Prb::Str( "{$key}=" )->concat( self::unquoted()->contains( $key ) ? $value->toS() : $this->quote( $value ) )
		);
		return $parts;
	}
	
	// TODO: Document!
	public function quote( $string )
	{
		$callback = null;
		if ( is_null( $callback ) )
			$callback = create_function( '$m', 'return "\\{$m}";' );
		
		return Prb::Str( '"' )
		  ->concat( $string->gsub( '/[\\\"]/', $callback ) )
		  ->concat( Prb::Str( '"' ) );
	}
}
