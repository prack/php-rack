<?php

// TODO: Document!
# Rack::MockRequest helps testing your Rack application without
# actually using HTTP.
#
# After performing a request on a URL with get/post/put/delete, it
# returns a MockResponse with useful helper methods for effective
# testing.
#
# You can pass a hash with additional configuration to the
# get/post/put/delete.
# <tt>:input</tt>:: A String or IO-like to be used as rack.input.
# <tt>:fatal</tt>:: Raise a FatalWarning if the app writes to rack.errors.
# <tt>:lint</tt>:: If true, wrap the application in a Rack::Lint.
class Prack_Mock_Request
{
	private $middleware_app;
	
	// TODO: Document!
	static function with( $middleware_app )
	{
		return new Prack_Mock_Request( $middleware_app );
	}
	
	// TODO: Document!
	/*
	  Taken from PHP message boards. Thanks to submitter
	  theoriginalmarksimpson at gmail dot com
	*/
	static function parseURL( $url )
	{
		static $pattern = null;
		
		if ( is_null( $pattern ) )
		{
			$pattern  = "(?:([a-z0-9+-._]+)://)?";
			$pattern .= "(?:";
			$pattern .=   "(?:((?:[a-z0-9-._~!$&'()*+,;=:]|%[0-9a-f]{2})*)@)?";
			$pattern .=   "(?:\[((?:[a-z0-9:])*)\])?";
			$pattern .=   "((?:[a-z0-9-._~!$&'()*+,;=]|%[0-9a-f]{2})*)";
			$pattern .=   "(?::(\d*))?";
			$pattern .=   "(/(?:[a-z0-9-._~!$&'()*+,;=:@/]|%[0-9a-f]{2})*)?";
			$pattern .=   "|";
			$pattern .=   "(/?";
			$pattern .=     "(?:[a-z0-9-._~!$&'()*+,;=:@]|%[0-9a-f]{2})+";
			$pattern .=     "(?:[a-z0-9-._~!$&'()*+,;=:@\/]|%[0-9a-f]{2})*";
			$pattern .=    ")?";
			$pattern .= ")";
			$pattern .= "(?:\?((?:[a-z0-9-._~!$&'()*+,;=:\/?@]|%[0-9a-f]{2})*))?";
			$pattern .= "(?:#((?:[a-z0-9-._~!$&'()*+,;=:\/?@]|%[0-9a-f]{2})*))?";
		}
		
		preg_match( "`{$pattern}`i", $url, $matches );
		
		$parts = array(
		  "scheme"    => null,
		  "userinfo"  => null,
		  "authority" => null,
		  "host"      => null,
		  "port"      => null,
		  "path"      => null,
		  "query"     => null,
		  "fragment"  => null
		);
		
		switch ( count ( $matches ) ) {
		  case 10: $parts[ 'fragment' ] = $matches[ 9 ];
		  case 9:  $parts[ 'query'    ] = $matches[ 8 ];
		  case 8:  $parts[ 'path'     ] = $matches[ 7 ];
		  case 7:  $parts[ 'path'     ] = $matches[ 6 ] . $parts[ 'path' ];
		  case 6:  $parts[ 'port'     ] = $matches[ 5 ];
		  case 5:  $parts[ 'host'     ] = $matches[ 3 ] ? "[".$matches[ 3 ]."]":$matches[ 4 ];
		  case 4:  $parts[ 'userinfo' ] = $matches[ 2 ];
		  case 3:  $parts[ 'scheme'   ] = $matches[ 1 ];
		}
		
		// This function wrongly matches a host too eagerly if no scheme is provided.
		// Actually, what it matches as the host should be treated as the path.
		if ( $parts[ 'host' ] == 'foo' || is_null( $parts[ 'scheme' ] && isset( $parts[ 'host' ] ) ) )
		{
			$parts[ 'path' ] = $parts[ 'host' ];
			$parts[ 'host' ] = null;
		}
		
		$parts[ 'authority' ] = ( $parts[ 'userinfo' ] ? $parts[ 'userinfo' ]."@" : "" ).
		                          $parts[ 'host'     ] .
		                        ( $parts[ 'port'     ] ? ":".$parts[ 'port' ] : "" );
		
		$parts = array_filter( $parts, 'strlen' );
		
		return Prb::_Hash( $parts );
	}
	
	// TODO: Document!
	static function envFor( $url = null, $options = null )
	{
		$url = is_null( $url ) ? Prb::_String() : $url;
		if ( !( $url instanceof Prb_String ) )
			throw new Prb_Exception_Type( 'FAILSAFE: envFor $url must be Prb_String' );
		$options = is_null( $options ) ? Prb::_Hash() : $options;
		if ( !( $options instanceof Prb_Hash ) )
			throw new Prb_Exception_Type( 'FAILSAFE: envFor $options must be Prb_Hash' );
		
		$env = array(
			'rack.version'      => Prack::version(),
			'rack.input'        => new Prb_IO_String(),
			'rack.errors'       => new Prb_IO_String(),
			'rack.multithread'  => false,
			'rack.multiprocess' => false,
			'rack.run_once'     => true
		);
		
		$components = self::parseURL( $url->raw() );
		if ( ( $path = $components->get( 'path' ) ) && substr( $path, 0, 1 ) != '/' )
			$components->set( 'path', "/{$path}" );
		
		// Request method, uppercased ('GET' by default):
		$env[ 'REQUEST_METHOD' ] =
		  $options->contains( 'method' ) ? $options->delete( 'method' )->upcase()
		                                 : Prb::_String( 'GET' );
		
		$env[ 'rack.url_scheme' ] =
		  $components->contains( 'scheme' ) ? Prb::_String( $components->get( 'scheme' ) )
		                                    : Prb::_String( 'http' );
		$env[ 'HTTPS' ] =
		  $env[ 'rack.url_scheme' ]->raw() == 'https' ? Prb::_String( 'on' )
		                                              : Prb::_String( 'off' );
		$default_port =
		  $env[ 'rack.url_scheme' ]->raw() == 'https' ? Prb::_String( '443' )
		                                              : Prb::_String( '80'  );
		
		$env[ 'SERVER_NAME' ] = $components->contains( 'host' ) ? Prb::_String( $components->get( 'host' ) )
		                                                        : Prb::_String( 'example.org' );
		$env[ 'SERVER_PORT' ] = $components->contains( 'port' ) ? Prb::_String( $components->get( 'port' ) )
		                                                        : $default_port;
		
		// Script name, path info, query string:
		$path_info        = Prb::_String( $components->get( 'path' ) );
		$path_info_viable = !$path_info->isEmpty();
		
		$env[ 'SCRIPT_NAME'  ] = $options->contains( 'script_name' ) ? $options->delete( 'script_name' )
		                                                             : Prb::_String();
		$env[ 'PATH_INFO'    ] = $path_info_viable                   ? $path_info
		                                                             : Prb::_String( '/' );
		$env[ 'QUERY_STRING' ] = Prb::_String( (string)$components->get( 'query' ) );
		$env[ 'rack.errors'  ] = $options->delete( 'fatal' ) == true ? new Prack_Mock_FatalWarner()
		                                                             : new Prb_IO_String();
		
		// FIXME: Implement query building and multipart form data processing.
		if ( $params = $options->delete( 'params' ) )
		{
			if ( $env[ 'REQUEST_METHOD' ]->raw() == 'GET' )
			{
				if ( $params instanceof Prb_String )
					$params = Prack_Utils::singleton()->parseNestedQuery( $params );
				
				// FIXME: Implement update on Prb_Hash, also mergeInPlace.
				$params = $params->merge( Prack_Utils::singleton()->parseNestedQuery( $env[ 'QUERY_STRING' ] ) );
				
				$env[ 'QUERY_STRING' ] = Prack_Utils::singleton()->buildNestedQuery( $params );
			}
			else if ( !$options->contains( 'input' ) )
			{
				$options->set( 'CONTENT_TYPE', Prb::_String( 'application/x-www-form-urlencoded' ) );
				if ( $params instanceof Prb_Hash )
				{
					// FIXME: Implement multipart form data processing.
					if ( $multipart = false )
						die("FIXME: Implement multipart.");
					else
						$options->set( 'input', Prack_Utils::singleton()->buildNestedQuery( $params ) );
				}
				else
					$options->set( 'input', $params );
			}
		}
		
		if ( !$options->contains( 'input' ) )
			$options->set( 'input', Prb::_String() );
		
		$input = $options->delete( 'input' );
		if ( $input instanceof Prb_Interface_Stringable )
			$rack_input = Prb_IO::withString( $input->toS() );
		else if ( $input instanceof Prb_Interface_ReadableStreamlike )
			$rack_input = $input;
		else
		{
			$input_type = is_object( $input ) ? get_class( $input ) : gettype( $input );
			throw new Prb_Exception_Type( "Provided rack input of type {$input_type} is neither String nor ReadableStreamlike" );
		}
		
		$env[ 'rack.input' ] = $rack_input;
		
		if ( !isset( $env[ 'CONTENT_LENGTH' ] ) )
			$env[ 'CONTENT_LENGTH' ] = Prb::_String( (string)$rack_input->length() );
		
		foreach ( $options->raw() as $field => $value )
			$env[ $field ] = $value;
		
		return Prb::_Hash( $env );
	}
	
	// TODO: Document!
	function __construct( $middleware_app )
	{
		if ( !( $middleware_app instanceof Prack_Interface_MiddlewareApp ) )
			throw new Prb_Exception_Type( 'FAILSAFE: __construct $middleware_app not a Prack_Interface_MiddlewareApp' );
		
		$this->middleware_app = $middleware_app;
	}
	
	// TODO: Document!
	public function get( $uri, $options = null )
	{
		return $this->request( Prb::_String( 'GET' ), $uri, $options );
	}
	
	// TODO: Document!
	public function post( $uri, $options = null )
	{
		return $this->request( Prb::_String( 'POST' ), $uri, $options );
	}
	
	// TODO: Document!
	public function put( $uri, $options = null )
	{
		return $this->request( Prb::_String( 'PUT' ), $uri, $options );
	}
	
	// TODO: Document!
	public function delete( $uri, $options = null )
	{
		return $this->request( Prb::_String( 'DELETE' ), $uri, $options );
	}
	
	// TODO: Document!
	public function request( $method, $uri = null, $options = null )
	{
		$uri = is_null( $uri ) ? Prb::_String() : $uri;
		if ( !( $uri instanceof Prb_String ) )
			throw new Prb_Exception_Type( 'FAILSAFE: mock request $uri must be Prb_String' );
		
		$options = is_null( $options ) ? Prb::_Hash() : $options;
		if ( !( $options instanceof Prb_Hash ) )
			throw new Prb_Exception_Type( 'FAILSAFE: mock request $options must be Prb_Hash' );
		
		if ( $lint = $options->delete( 'lint' ) )
			$middleware_app = new Prack_Lint( $this->middleware_app );
		else
			$middleware_app = $this->middleware_app;
		
		$options = $options->merge(
			Prb::_Hash( array( 'method' => $method ) )
		);
		
		$env    = self::envFor( $uri, $options );
		$errors = $env->get( 'rack.errors' );
		
		list( $status, $headers, $body ) = $middleware_app->call( $env );
		
		return new Prack_Mock_Response( $status, $headers, $body, $errors );
	}
}