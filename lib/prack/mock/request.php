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
		
		return $parts;
	}
	
	// TODO: Document!
	static function envFor( $url = '', $options = array() )
	{
		$components = self::parseURL( $url );
		
		$env = array(
			'rack.version'      => Prack::version(),
			'rack.input'        => new Prb_IO_String(),
			'rack.errors'       => new Prb_IO_String(),
			'rack.multithread'  => 0,
			'rack.multiprocess' => 0,
			'rack.run_once'     => 1
		);
		
		$path = @$components[ 'path' ];
		if ( $path && substr( $path, 0, 1 ) != '/' )
			$components[ 'path' ] = "/{$path}";
		else if ( is_null( $path ) || empty( $path ) )
			$components[ 'path' ] = '/';

		$env[ 'REQUEST_METHOD'  ] = @$options[ 'method' ] ? strtoupper( $options[ 'method' ] )  : 'GET' ;
		$env[ 'rack.url_scheme' ] = @$components[ 'scheme' ] ? $components[ 'scheme' ] : 'http';
		$env[ 'HTTPS'           ] = $env[ 'rack.url_scheme' ] == 'https' ? 'on' : 'off';
		$env[ 'SERVER_NAME'     ] = @$components[ 'host' ] ? $components[ 'host' ] : 'example.org';
		$env[ 'SERVER_PORT'     ] = @$components[ 'port' ] ? $components[ 'port' ] : ($env[ 'HTTPS' ] == 'on' ? '443' : '80');
		$env[ 'SCRIPT_NAME'     ] = (string)@$options[ 'script_name' ];
		$env[ 'PATH_INFO'       ] = $components[ 'path' ];
		$env[ 'QUERY_STRING'    ] = (string)@$components[ 'query' ];
		$env[ 'rack.errors'     ] = @$options[ 'fatal' ] ? new Prack_Mock_FatalWarner() : new Prb_IO_String();
		
		unset( $options[ 'method' ]      );
		unset( $options[ 'scheme' ]      );
		unset( $options[ 'script_name' ] );
		unset( $options[ 'fatal'  ]      );
		
		// FIXME: Implement query building and multipart form data processing.
		if ( $params = @$options[ 'params' ] )
		{
			$utils = Prack_Utils::singleton();
			
			if ( $env[ 'REQUEST_METHOD' ] == 'GET' )
			{
				if ( is_string( $params ) )
					$params = $utils->parseNestedQuery( $params );
				
				$params                = array_merge( $params, $utils->parseNestedQuery( $env[ 'QUERY_STRING' ] ) );
				$env[ 'QUERY_STRING' ] = $utils->buildNestedQuery( $params );
			}
			else if ( !@$options[ 'input' ] )
			{
				$options[ 'CONTENT_TYPE' ] = 'application/x-www-form-urlencoded';
				
				if ( is_array( $params ) )
				{
					// FIXME: Implement multipart form data processing.
					if ( $multipart = false )
						die("FIXME: Implement multipart.");
					else
						$options[ 'input' ] = $utils->buildNestedQuery( $params );
				}
				else
					$options[ 'input' ] = $params;
			}
		}
		
		if ( !@$options[ 'input' ] )
			$options[ 'input' ] = '';
		
		if ( is_string( $options[ 'input' ] ) )
		{
			$rack_input        = Prb_IO::withString( $options[ 'input' ] );
			$rack_input_length = strlen( $options[ 'input' ] );
		}
		else if ( $options[ 'input' ] instanceof Prb_I_ReadableStreamlike )
		{
			$rack_input        = $options[ 'input' ];
			$rack_input_length = $rack_input->length();
		}
		else
			throw new Prb_Exception_Type( 'rack.input not provided' );
		
		$env[ 'rack.input' ] = $rack_input;
		
		unset( $options[ 'input' ] );
		
		if ( !@$env[ 'CONTENT_LENGTH' ] )
			$env[ 'CONTENT_LENGTH' ] = (string)$rack_input_length;
		
		foreach ( $options as $key => $value )
			$env[ $key ] = $value;
		
		return $env;
	}
	
	// TODO: Document!
	function __construct( $middleware_app )
	{
		if ( !( $middleware_app instanceof Prack_I_MiddlewareApp ) )
			throw new Prb_Exception_Type( 'FAILSAFE: __construct $middleware_app not a Prack_I_MiddlewareApp' );
		
		$this->middleware_app = $middleware_app;
	}
	
	// TODO: Document!
	public function get( $uri = null, $options = null )
	{
		return $this->request( 'GET', $uri, $options );
	}
	
	// TODO: Document!
	public function post( $uri, $options = null )
	{
		return $this->request( 'POST', $uri, $options );
	}
	
	// TODO: Document!
	public function put( $uri, $options = null )
	{
		return $this->request( 'PUT', $uri, $options );
	}
	
	// TODO: Document!
	public function delete( $uri, $options = null )
	{
		return $this->request( 'DELETE', $uri, $options );
	}
	
	// TODO: Document!
	public function request( $method, $uri = null, $options = null )
	{
		$uri     = $uri     ? (string)$uri     : '';
		$options = $options ?  (array)$options : array();
		
		if ( $lint = @$options[ 'lint' ] )
			$middleware_app = Prack_Lint::with( $this->middleware_app );
		else
			$middleware_app = $this->middleware_app;
		
		unset( $options[ 'lint' ] );
		
		$env    = self::envFor( $uri, array_merge( $options, array( 'method' => $method ) ) );
		$errors = $env[ 'rack.errors' ];
		
		list( $status, $headers, $body ) = $middleware_app->call( $env );
		
		return new Prack_Mock_Response( $status, $headers, $body, $errors );
	}
}