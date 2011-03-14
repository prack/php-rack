<?php

// TODO: Document!
class Prack_Utils
{
	private $obq_p;
	private $obnq_p;
	
	// TODO: Document!
	static function singleton()
	{
		static $instance = null;
		
		if ( is_null( $instance ) )
			$instance = new Prack_Utils();
		
		return $instance;
	}
	
	// TODO: Document!
	public function HTMLEscapes()
	{
		static $html_escapes = null;
		
		if ( is_null( $html_escapes ) )
		{
			$html_escapes = Prb::_Hash( array(
				'&'  => Prb::_String( "&amp;"  ),
				'<'  => Prb::_String( "&lt;"   ),
				'>'  => Prb::_String( "&gt;"   ),
				'\'' => Prb::_String( "&#39;"  ),
				'"'  => Prb::_String( "&quot;" )
			) );
		}
		
		return $html_escapes;
	}
	
	// TODO: Document!
	public function HTMLEscapesPattern()
	{
		static $html_escapes_pattern = null;
		
		if ( is_null( $html_escapes_pattern ) )
		{
			$callback = create_function(
			  '$i', 'return Prb::_String( preg_quote( $i->raw() ) );'
			);
			
			$html_escapes_pattern =
			  $this->HTMLEscapes()
			       ->keys()->map( $callback )
			       ->join( Prb::_String( '|' ) );
			$html_escapes_pattern =
			  "/{$html_escapes_pattern->raw()}/";
		}
		
		return $html_escapes_pattern;
	}
	
	// TODO: Document!
	public function escapeHTML( $string )
	{
		static $callback = null;
		
		if ( is_null( $callback ) )
			$callback = create_function(
			  '$i', 'return Prack_Utils::singleton()->HTMLEscapes()->get( $i[ 0 ] )->raw();'
			);
		
		return $string->toS()->gsub( $this->HTMLEscapesPattern(), $callback )->raw();
	}
	
	// TODO: Document!
	public function statusWithNoEntityBody()
	{
		static $swneb = null;
		
		if ( is_null( $swneb ) )
		{
			$swneb = range( 100, 199 );
			array_push( $swneb, 204 );
			array_push( $swneb, 304 );
			$swneb = Prb::_Array( $swneb );
		}
		
		return $swneb;
	}
	
	// TODO: Document!
  # Performs URI escaping so that you can construct proper
  # query strings faster.  Use this rather than the cgi.rb
  # version since it's faster.  (Stolen from Camping).
	public function escape( $string )
	{
		static $callback = null;
		
		if ( is_null( $callback ) )
			$callback = create_function(
			  '$m', '$u=unpack(\'H*\',$m[0]); return \'%\'.strtoupper(implode(\'%\', str_split($u[1],2)));'
			);
		
		return $string->toS()
		  ->gsub( '/([^ a-zA-Z0-9_.-]+)/', $callback )
		  ->tr( Prb::_String( ' ' ), Prb::_String( '+' ) );
	}
	
	// TODO: Document!
	# Unescapes a URI escaped string. (Stolen from Camping).
	public function unescape( $string )
	{
		static $callback = null;
		
		if ( is_null( $callback ) )
			$callback = create_function(
			  '$m', 'return pack( \'H*\', preg_replace( \'/%/\', \'\', $m[0] ) );'
			);
		
		return $string->toS()
		  ->tr( Prb::_String( '+' ), Prb::_String( ' ' ) )
		  ->gsub( '/((?:%[0-9a-fA-F]{2})+)/', $callback );
	}
	
	const DEFAULT_SEP = '/[&;] */';
	
	// TODO: Document!
  # Stolen from Mongrel, with some small modifications:
  # Parses a query string by breaking it up at the '&'
  # and ';' characters.  You can also use this to parse
  # cookies by changing the characters used in the second
  # parameter (which defaults to '&;').
	public function parseQuery( $query_string, $delimiter = null )
	{
		$this->params = Prb::_Hash();
		
		if ( is_null( $query_string ) )
			$query_string = Prb::_String();
		
		$callback = array( $this, 'onParseQuery' );
		$query_string->split( isset( $delimiter ) ? "/[{$d}] */" : self::DEFAULT_SEP )
		             ->each( $callback );
		
		return $this->params;
	}
	
	// TODO: Document!
	public function onParseQuery( $param )
	{
		static $callback = null;
		
		if ( is_null( $callback ) )
		  $callback = create_function( '$x', 'return Prack_Utils::singleton()->unescape( $x );' );
		
		list( $key, $value ) = $param->split( '/=/', 2 )
		                             ->map( $callback )->raw();
		
		$key = $key->raw();
		
		if ( $current = $this->params->get( $key ) )
		{
			if ( $current instanceof Prb_Array )
				$this->params->concat( $key, $value );
			else
				$this->params->set( $key, Prb::_Array( $current, $value ) );
		}
		else
			$this->params->set( $key, $value );
	}
	
	// TODO: Document!
	public function parseNestedQuery( $query_string, $delimiter = null )
	{
		$this->params = Prb::_Hash();
		
		if ( is_null( $query_string ) )
			$query_string = Prb::_String();
		
		$callback = array( $this, 'onParseNestedQuery' );
		$query_string->split( isset( $delimiter ) ? "/[{$d}] */" : self::DEFAULT_SEP )
		             ->each( $callback );
		
		return $this->params;
	}
	
	// TODO: Document!
	public function onParseNestedQuery( $param )
	{
		$processed = $this->unescape( $param )->split( '/=/', 2 );
		
		$key   = $processed->get( 0 );
		$value = $processed->get( 1 );
		
		$this->normalizeParams( $this->params, $key, $value );
	}
	
	// TODO: Document!
	public function normalizeParams( $params, $name, $value = null )
	{
		$name->match( '/\A[\[\]]*([^\[\]]+)\]?(.*)/', $matches );
		
		$match_one = $matches[ 1 ];
		$match_two = $matches[ 2 ];
		
		// square-bracketed named group:
		if ( !isset( $match_one[ 0 ] ) )
			return; // end of recursion
		
		$key   = $match_one[ 0 ];
		$after = Prb::_String( $match_two[ 0 ] );
		
		if ( $after->isEmpty() )
			$params->set( $key, isset( $value ) ? $value : null );
		
		else if ( $after->raw() == '[]' )
		{
			if ( !$params->contains( $key ) )
				$params->set( $key, Prb::_Array() );
			
			$operand = $params->get( $key );
			if ( !( $operand instanceof Prb_Array ) )
			{
				$operand_type = is_object( $operand ) ? get_class( $operand ) : gettype( $operand );
				throw new Prb_Exception_Type( "expected Prb_Array (got {$operand_type}) for param '{$key}'" );
			}
			
			$operand->concat( $value );
		}
		
		else if ( $after->match( '/^\[\]\[([^\[\]]+)\]$/m', $matches ) || $after->match( '/^\[\](.+)$/m', $matches ) )
		{
			// $matches reassigned here:
			$child_key = Prb::_String( $matches[ 1 ][ 0 ] );
			
			if ( !$params->contains( $key ) )
				$params->set( $key, Prb::_Array() );
			
			$operand = $params->get( $key );
			if ( !( $operand instanceof Prb_Array ) )
			{
				$operand_type = is_object( $operand ) ? get_class( $operand ) : gettype( $operand );
				throw new Prb_Exception_Type( "expected Prb_Array (got {$operand_type}) for param '{$key}'" );
			}
			
			$last_param = $params->get( $key )->last();
			if ( $last_param instanceof Prb_Hash && !$last_param->contains( $child_key->raw() ) )
				$this->normalizeParams( $last_param, $child_key, $value );
			else
				$operand->concat( $this->normalizeParams( Prb::_Hash(), $child_key, $value ) );
		}
		else
		{
			if ( !$params->contains( $key ) )
				$params->set( $key, Prb::_Hash() );
			
			$operand = $params->get( $key );
			if ( !( $operand instanceof Prb_Hash ) )
			{
				$operand_type = is_object( $operand ) ? get_class( $operand ) : gettype( $operand );
				throw new Prb_Exception_Type( "expected Prb_Hash (got {$operand_type}) for param '{$key}'" );
			}
			
			$params->set( $key, $this->normalizeParams( $operand, $after, $value) );
		}
		
		return $params;
	}
	
	// TODO: Document!
	public function buildQuery( $params )
	{
		$callback = array( $this, 'onBuildQuery' );
		return $params->map( $callback )
		              ->join( Prb::_String( '&' ) );
	}
	
	// TODO: Document!
	public function onBuildQuery( $key, $value )
	{
		if ( $value instanceof Prb_Array )
		{
			$this->obq_k = $key;
			$callback    = array( $this, 'onBuildQueryArrayIteration' );
			return $this->buildQuery( $value->collect( $callback ) );
		}
		
		return Prb::_String( "{$this->escape( Prb::_String( $key ) )->raw()}={$this->escape( $value )->raw()}" );
	}
	
	// TODO: Document!
	public function onBuildQueryArrayIteration( $item )
	{
		return Prb::_Array( array( $this->obq_k, $item ) );
	}
	
	// TODO: Document!
	public function buildNestedQuery( $params )
	{
		$this->obnq_p = array();
		return $this->_buildNestedQuery( $params );
	}
	
	// TODO: Document!
	public function _buildNestedQuery( $value, $prefix = null )
	{
		if ( $value instanceof Prb_Hash )
		{
			if ( isset( $prefix ) )
				array_push( $this->obnq_p, $prefix );
			$callback = array( $this, 'onBuildNestedQueryHashIteration' );
			$result   = $value->map( $callback )->join( Prb::_String( '&' ) );
			array_pop( $this->obnq_p );
			return $result;
		}
		else if ( $value instanceof Prb_Array )
		{
			if ( isset( $prefix ) )
				array_push( $this->obnq_p, $prefix );
			$callback = array( $this, 'onBuildNestedQueryArrayIteration' );
			$result   = $value->map( $callback )->join( Prb::_String( '&' ) );
			array_pop( $this->obnq_p );
			return $result;
		}
		else if ( $value instanceof Prb_String )
		{
			if ( is_null( $prefix ) )
				throw new Prb_Exception_Argument( 'value must be a Prb_Hash' );
			return Prb::_String( $prefix."={$this->escape( $value )->raw()}" );
		}
		
		return Prb::_String( $prefix );
	}
	
	// TODO: Document!
	public function onBuildNestedQueryHashIteration( $key, $value )
	{
		$escaped_key = $this->escape( Prb::_String( $key ) )->raw();
		if ( empty( $this->obnq_p ) )
			$prefix = $escaped_key;
		else
			$prefix = $this->obnq_p;
		
		$prefix = is_array( $prefix ) ? end( $this->obnq_p )."[{$escaped_key}]" : $escaped_key;
		return $this->_buildNestedQuery( $value, $prefix );
	}
	
	// TODO: Document!
	public function onBuildNestedQueryArrayIteration( $item )
	{
		$prefix = end( $this->obnq_p );
		return $this->_buildNestedQuery( $item, "{$prefix}[]" );
	}
}