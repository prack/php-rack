<?php

// TODO: Document!
class Prack_Utils
{
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
			$html_escapes = array(
				'&'  => "&amp;",
				'<'  => "&lt;",
				'>'  => "&gt;",
				'\'' => "&#39;",
				'"'  => "&quot;"
			);
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
			  '$i', 'return Prb::Str( preg_quote( $i->raw() ) );'
			);
			
			$html_escapes_pattern =
			  $this->HTMLEscapes()
			       ->keys()->map( $callback )
			       ->join( Prb::Str( '|' ) );
			$html_escapes_pattern =
			  "/{$html_escapes_pattern->raw()}/";
		}
		
		return $html_escapes_pattern;
	}
	
	// TODO: Document!
	public function escapeHTML( $string )
	{
		return strtr( $string, self::HTMLEscapes() );
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
		}
		
		return $swneb;
	}
	
	// TODO: Document!
	public function bytesize( $string )
	{
		return strlen( $string );
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
		
		$string = preg_replace_callback( '/([^ a-zA-Z0-9_.-]+)/', $callback, $string );
		
		return strtr( $string, ' ', '+' );
	}
	
	// TODO: Document!
	# Unescapes a URI escaped string. (Stolen from Camping).
	public function unescape( $string )
	{
		static $callback = null;
		if ( is_null( $callback ) )
			$callback = create_function( '$m', 'return pack( \'H*\', preg_replace( \'/%/\', \'\', $m[0] ) );' );
		
		$translated = strtr( $string, '+', ' ' );
		return preg_replace_callback( '/((?:%[0-9a-fA-F]{2})+)/', $callback, $translated );
	}
	
	const DEFAULT_SEP = '/[&;] */';
	
	/**
	 * Parses a query string without recursing.
	 *
	 * Uses an optional delimiter string containing characters on which to split.
	 * After splitting, escapes key value pairs and puts them in an array.
	 *
	 * @author Joshua Morris
	 * @access public
	 * @param $query_string string request query string
	 * @param $delimiter string list of characters upon which to split query string
	 * @return array shallow array of escaped keys and values from query string
	 */
	public function parseQuery( $query_string, $delimiter = null )
	{
		$query_string = (string)$query_string;
		$params       = array();
		
		$split = preg_split( isset( $delimiter ) ? $delimiter : self::DEFAULT_SEP, $query_string );
		foreach( $split as $param )
		{
			static $callback = null;
			if ( is_null( $callback ) )
			  $callback = create_function( '$x', 'return Prack_Utils::singleton()->unescape( $x );' );
		
			@list( $key, $value ) = preg_split( '/=/', $param, 2 );
			@list( $key, $value ) = array_map( $callback, array( $key, $value ) );
		
			if ( $current = @$params[ $key ] )
			{
				if ( is_array( $current ) )
					array_push( $current, $key, $value );
				else
				  $params[ $key ] = array( $current, $value );
			}
			else
				$params[ $key ] = $value;
		}
		
		return $params;
	}
	
	/**
	 * Parse a query with nested values (recursively) into an array.
	 *
	 * This function splits parameters by the provided delimeter(s)--'&;' by default--
	 * and normalizes each of the split parameters recursively via callback.
	 *
	 * With a different delimiter, this function can also be used to parse cookies.
	 * This approach is used elsewhere in Prack_Utils.
	 *
	 * @author Joshua Morris
	 * @access public
	 * @param $query_string string the query string to be parsed
	 * @param $delimiter string set of characters by which to split parameters
	 * @return array possibly multidimensional array containing query values
	 */
	public function parseNestedQuery( $query_string, $delimiter = null )
	{
		$params = array( 'type' => 'assoc', 'values' => array() );
		$split  = preg_split( $delimiter ? "/[{$d}] */" : self::DEFAULT_SEP, (string)$query_string );
		
		foreach ( $split as $param )
		{
			$key_value = preg_split( '/=/', $this->unescape( $param ), 2 );
			$this->normalizeParams( $params, @$key_value[ 0 ], @$key_value[ 1 ] );
		}
		
		$this->cleanParams( $params );
		
		return $params;
	}
	
	// TODO: Document!
	public function buildQuery( $params )
	{
		$query = array();
		
		foreach ( $params as $name => $param )
		{
			if ( is_array( $param ) )
			{
				$intermediate = array();
				foreach ( $param as $value )
					array_push( $intermediate, "{$this->escape( $name )}={$this->escape( $value )}" );
				array_push( $query, join( '&', $intermediate ) );
			}
			else
				array_push( $query, "{$this->escape( $name )}={$this->escape( $param )}" );
		}
		
		return join( '&', $query );
	}
	
	// TODO: Document!
	public function buildNestedQuery( $params )
	{
		$dirty = $params; // copy params
		return $this->_buildNestedQuery( $this->dirtyParams( $dirty ) );
	}
	
	// TODO: Document!
	private function _buildNestedQuery( $param, $prefix = null )
	{
		if ( is_array( $param ) && @$param[ 'type' ] == 'assoc' )
		{
			$mapped = array();
			
			foreach ( $param[ 'values' ] as $key => $value )
			{
				$_prefix = $prefix ? "{$prefix}[{$this->escape( $key )}]" : $this->escape( $key );
				array_push( $mapped, $this->_buildNestedQuery( $value, $_prefix ) );
			}
				
			return join( '&', $mapped );
		}
		else if ( is_array( $param ) && @$param[ 'type' ] == 'indexed' )
		{
			$mapped = array();
			
			foreach ( $param[ 'values' ] as $key => $value )
				array_push( $mapped, $this->_buildNestedQuery( $value, "{$prefix}[]" ) );
			
			return join( '&', $mapped );
		}
		else if ( is_string( $param ) )
		{
			if ( is_null( $prefix ) )
				throw new Prb_Exception_Argument( "param must be an array of type 'assoc' (got {$param})" );
			
			return $prefix."={$this->escape( $param )}";
		}
		
		return $prefix;
	}
	
	/**
	 * Normalize an individual parameter, adding its value to the overall params array.
	 * 
	 * Recursively processes hash- and array-based param names, creating arrays
	 * on each recurse suited to holding that level's values.
	 *
	 * Arrays generated on each level of recursion have two keys: 'type' and 'values'.
	 * 'type' is one of two values ('indexed' or 'assoc'), indicating whether that level's
	 * values are stored as a simple list, or as a lookup. A TypeError exception is thrown
	 * if, while processing, normalizeParams() encounters an inappropriate container for
	 * the values it's trying to add. This can be caused by a malformed query string, and
	 * should probably result in a "400 Bad Request" response.
	 *
	 * @author Joshua Morris
	 * @access private
	 * @param &$params array array of 'type' and 'values' keys for this level's values
	 * @param $name string param name at this recursion level
	 * @param $value mixed null or primitive: if set, used as value to be put in this level's collection
	 * @return array return value used for recursion only--this function modifies &$params by reference
	 * @throws TypeError
	 */
	private function normalizeParams( &$params, $name, $value = null )
	{
		$values = &$params[ 'values' ]; // this recursion level's values
		
		preg_match_all( '/\A[\[\]]*([^\[\]]+)\]?(.*)/', $name, $matches );
		
		if ( !@$matches[ 1 ][ 0 ] )
			return;
		
		$key    = $matches[ 1 ][ 0 ];   // first chunk of square-bracket-delimited text in $name
		$after  = $matches[ 2 ][ 0 ];   // remainder of name after first match
		
		// no remaining characters in $name
		if ( empty( $after ) )
			$values[ $key ] = $value;
		
		// $after contains exactly '[]', indicating a 'push' onto this param level's 'values'
		// this also asserts that this level's 'values' are an indexed array.
		else if ( $after == '[]' )
		{
			if ( !@$values[ $key ] )
				$values[ $key ] = array( 'type' => 'indexed', 'values' => array() );
			
			$intermediate = &$values[ $key ];
			if ( !is_array( $intermediate ) || @$intermediate[ 'type' ] != 'indexed' )
				throw new Prb_Exception_Type( "expected type 'indexed' (got {$intermediate[ 'type' ]}) for param '{$key}'" );
			
			array_push( $intermediate[ 'values' ], $value );
		}
		
		// $after contains '[][key]' or '[]garbage'
		// as with above, we're still putting things in arrays. this control structure
		// sets the last of this level's values to be an associative array tuple:
		//   populating it with normalized values from the child key, recursively
		// -OR-
		//   creating the tuple, and then populating it recursively
		else if ( preg_match_all( '/^\[\]\[([^\[\]]+)\]$/m', $after, $matches ) || preg_match_all( '/^\[\](.+)$/m', $after, $matches ) )
		{
			if ( !@$values[ $key ] )
				$values[ $key ] = array( 'type' => 'indexed', 'values' => array() );
			
			$intermediate = &$values[ $key ];
			if ( !is_array( $intermediate ) || @$intermediate[ 'type' ] != 'indexed' )
				throw new Prb_Exception_Type( "expected type 'indexed' (got {$intermediate[ 'type' ]}) for param '{$key}'" );
			
			$child_key         = $matches[ 1 ][ 0 ];
			$intermediate_keys = array_keys( $intermediate[ 'values' ] );
			$last_key          =  array_pop( $intermediate_keys        );
			
			$last = null;
			if ( isset( $last_key ) )
				$last = &$intermediate[ 'values' ][ $last_key ];
			
			if ( ( is_array( $last ) && @$last[ 'type' ] == 'assoc' ) && !array_key_exists( $child_key, $last[ 'values' ] ) )
				$this->normalizeParams( $last, $child_key, $value );
			else
			{
				$intermediate_values = &$intermediate[ 'values' ];
				$subparams           = array( 'type' => 'assoc', 'values' => array() );
				array_push( $intermediate_values, $this->normalizeParams( $subparams, $child_key, $value ) );
			}
		}
		
		// otherwise, recurse with an associative array:
		else
		{
			if ( !@$values[ $key ] )
				$values[ $key ] = array( 'type' => 'assoc', 'values' => array() );
			
			$intermediate = &$values[ $key ];
			if ( !is_array( $intermediate ) || @$intermediate[ 'type' ] != 'assoc' )
				throw new Prb_Exception_Type( "expected type 'assoc' (got {$intermediate[ 'type' ]}) for param '{$key}'" );
			
			$values[ $key ] = $this->normalizeParams( $intermediate, $after, $value );
		}
		
		return $params;
	}
	
	// TODO: Document!
	public function selectBestEncoding( $available_encodings, $accept_encoding )
	{
		# http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
		
		$expanded_accept_encoding = array();
		foreach ( $accept_encoding as $ae )
		{
			list( $encoding, $q ) = $ae;
			
			$intermediate = array();
			if ( $encoding == '*' )
			{
				$gathered = array();
				foreach( $accept_encoding as $ae )
					array_push( $gathered, $ae[ 0 ] );
				
				$intermediate = array_diff( $available_encodings, $gathered );
				foreach ( $intermediate as $key => $_encoding )
					$intermediate[ $key ] = array( $_encoding, $q );
			}
			else
				$intermediate = array( array( $encoding, $q ) );
			
			array_push( $expanded_accept_encoding, $intermediate );
		}
		
		$accumulator = array();
		foreach ( $expanded_accept_encoding as $list )
			$accumulator = array_merge( $accumulator, $list );
		
		$expanded_accept_encoding = $accumulator;
		
		$proxies = array();
		foreach ( $expanded_accept_encoding as $encoding_candidate )
			array_push( $proxies, -$encoding_candidate[ 1 ] );
		
		asort( $proxies, SORT_NUMERIC );
		
		// Workaround for PHP's arbitrary ordering of same-valued elements.
		$encoding_candidates = array();
		foreach( array_unique( $proxies ) as $negative_q )
		{
			$keys = array_keys( $proxies, $negative_q );
			sort( $keys );
			foreach( $keys as $key )
				array_push( $encoding_candidates, $expanded_accept_encoding[ $key ][ 0 ] );
		}
		
		if ( !( in_array( 'identity', $encoding_candidates ) ) )
			array_push( $encoding_candidates, 'identity' );
		
		$invalid_candidates = array();
		foreach( $expanded_accept_encoding as $aea )
			if ( $aea[ 1 ] == 0.0 )
				array_push( $invalid_candidates, $aea[ 0 ] );
		
		$encoding_candidates = array_diff( $encoding_candidates, $invalid_candidates );
		$intersection        = array_intersect( $encoding_candidates, $available_encodings  );
		$winner              = reset( $intersection );
		
		return $winner;
	}

	/**
	 * Recursively removes 'type' and 'values' keys from parsed params.
	 * 
	 * This function removes query-processing information from an array, leaving
	 * only the parsed query results. This step is necessary on account of PHP's
	 * lack of distinction between indexed and associative arrays.
	 * 
	 * @author Joshua Morris
	 * @access private
	 * @param &$item the current param set to be cleaned
	 */
	private function cleanParams( &$item )
	{
		unset( $item[ 'type' ] );
		$item = $item[ 'values' ];
		
		foreach ( $item as $key => &$value )
		{
			if ( is_array( $value ) && @$value[ 'type' ] && @$value[ 'values' ] )
				$this->cleanParams( $value );
		}
	}

	/**
	 * Recursively adds 'type' and 'values' keys to params.
	 * 
	 * This function adds query-processing information to an array. This step
	 * is necessary on account of PHP's lack of distinction between indexed
	 * and associative arrays.
	 * 
	 * @author Joshua Morris
	 * @access private
	 * @param &$item the current param set to be cleaned
	 */
	private function dirtyParams( &$item )
	{
		if ( is_array( $item ) )
		{
			$has_numeric_keys = false;
			$has_string_keys  = false;
			foreach ( array_keys( $item ) as $key )
			{
				if ( is_string( $key ) )
					$has_string_keys = true;
				if ( is_numeric( $key ) )
					$has_numeric_keys = true;
			}
			
			if ( $has_string_keys && $has_numeric_keys )
				throw new Prb_Exception_Type( 'dirtyParams $item cannot have both string and numeric keys' );
			
			$item   = array( 'type' => $has_string_keys ? 'assoc' : 'indexed', 'values' => $item );
			$values = &$item[ 'values' ];
			
			foreach ( $values as $key => &$value )
			{
				if ( is_array( $value ) )
					$values[ $key ] = $this->dirtyParams( $value );
			}
		}
		
		return $item;
	}
}