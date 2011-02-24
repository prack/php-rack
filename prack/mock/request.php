<?php

// TODO: Document!
class Prack_FatalWarner
{
	// TODO: Document!
	public function puts( $warning )
	{
		throw new Prack_Error_Mock_Request_FatalWarning( $warning );
	}
	
	// TODO: Document!
	public function write( $warning )
	{
		throw new Prack_Error_Mock_Request_FatalWarning( $warning );
	}
	
	// TODO: Document!
	public function flush()
	{
		// No-op.
		return true;
	}
	
	// TODO: Document!
	public function string()
	{
		return Prack_Wrapper_String::with( '' );
	}
}

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
	static function &envFor( $uri = '', $options = array() )
	{
		$env = 
			array(
				'rack.version'      => Prack::version(),
				'rack.input'        => new Prack_Utils_IO_String(),
				'rack.errors'       => new Prack_Utils_IO_String(),
				'rack.multithread'  => false,
				'rack.multiprocess' => false,
				'rack.run_once'     => true
			);
		
		$uri_components = parse_url( $uri );
		if ( isset( $uri_components[ 'path' ] ) && substr( $uri_components[ 'path' ], 0, 1 ) != '/' )
			$uri_components[ 'path' ] = "/{$uri_components[ 'path' ]}";
		
		$necessary_fields = array( 'scheme', 'host', 'port', 'path', 'query' );
		foreach ( $necessary_fields as $field )
			$uri_components[ $field ] = isset( $uri_components[ $field ] ) ? $uri_components[ $field ] : '';
		
		$env[ 'rack.url_scheme' ] = !empty( $uri_components[ 'scheme' ] ) ? $uri_components[ 'scheme' ] : 'http';
		$env[ 'REQUEST_METHOD'  ] = !empty( $options[ 'method' ] ) ? strtoupper( (string)$options[ 'method' ] ) : 'GET';
		$env[ 'SERVER_NAME'     ] = !empty( $uri_components[ 'host' ] ) ? $uri_components[ 'host' ] : 'example.org';
		
		// Different from Ruby in that PHP does not infer a default port of 443 for https URLs.
		if ( !empty( $uri_components[ 'port' ] ) )
			$env[ 'SERVER_PORT' ] = (string)$uri_components[ 'port' ];
		else
			$env[ 'SERVER_PORT' ] = ( $env[ 'rack.url_scheme' ] == 'https' ) ? '443' : '80';
		
		$env[ 'QUERY_STRING'    ] = $uri_components[ 'query' ];
		$env[ 'PATH_INFO'       ] = empty( $uri_components[ 'path' ] ) ? '/' : $uri_components[ 'path' ];
		$env[ 'HTTPS'           ] = $env[ 'rack.url_scheme' ] == 'https' ? 'on' : 'off';
		$env[ 'SCRIPT_NAME'     ] = isset( $options[ 'script_name' ] ) ? $options[ 'script_name' ] : '';
		
		if ( isset( $options[ 'fatal' ] ) )
		{
			$env[ 'rack.errors' ] = new Prack_FatalWarner();
			unset( $options[ 'fatal' ] );
		}
		else
			$env[ 'rack.errors' ] = new Prack_Utils_IO_String();
		
		if ( isset( $options[ 'params' ] ) )
		{
			$params = $options[ 'params' ];
			unset( $options[ 'params' ] );
		} else
			$params = null;
		
		if ( !is_null( $params ) )
		{
			if ( $env[ 'REQUEST_METHOD' ] == 'GET' )
			{
				if ( is_string( $params ) )
					parse_str( $params, $params );
					
				parse_str( $env[ 'QUERY_STRING' ], $params_from_query_string );
				$params = array_merge( $params, $params_from_query_string );
				
				$env[ 'QUERY_STRING' ] = urldecode( http_build_query( $params ) );
			}
			else if ( !isset( $options[ 'input' ] ) )
			{
				$options[ 'CONTENT_TYPE' ] = 'application/x-www-form-urlencoded';
				if ( is_array( $params ) )
				{
					// FIXME: Implement multipart form data processing.
					/*
					# Ruby code, for reference: 
					if data = Utils::Multipart.build_multipart(params)
					  opts[ :input ] = data
					  opts[ "CONTENT_LENGTH" ] ||= data.length.to_s
					  opts[ "CONTENT_TYPE" ] = "multipart/form-data; boundary=#{Utils::Multipart::MULTIPART_BOUNDARY}"
					else
					  opts[ :input ] = Utils.build_nested_query(params)
					end
					*/
					$multipart = false; // Band-aid.
					if ( $multipart )
						echo "TODO: Implement multipart.";
					else
						$options[ 'input' ] = urldecode( http_build_query( $params ) );
				}
				else
					$options[ 'input' ] = $params;
			}
		}
		
		if ( !isset( $options[ 'input' ] ) )
			$options[ 'input' ] = '';
		
		if ( is_string( $options[ 'input' ] ) )
			$rack_input = Prack_Utils_IO::withString( $options[ 'input' ] );
		else
			$rack_input = $options[ 'input' ];
			
		unset( $options[ 'input' ] );
		
		$env[ 'rack.input' ] = $rack_input;
		if ( !isset( $env[ 'CONTENT_LENGTH' ] ) )
			$env[ 'CONTENT_LENGTH' ] = (string)$rack_input->length();
		
		foreach ( $options as $field => $value )
		{
			if ( is_string( $field ) )
				$env[ $field ] = $value;
		}
		
		return $env;
	}
	
	// TODO: Document!
	function __construct( $middleware_app )
	{
		$this->middleware_app = $middleware_app;
	}
	
	// TODO: Document!
	public function get( $uri, $options = array() )
	{
		return $this->request( 'GET', $uri, $options );
	}
	
	// TODO: Document!
	public function post( $uri, $options = array() )
	{
		return $this->request( 'POST', $uri, $options );
	}
	
	// TODO: Document!
	public function put( $uri, $options = array() )
	{
		return $this->request( 'PUT', $uri, $options );
	}
	
	// TODO: Document!
	public function delete( $uri, $options = array() )
	{
		return $this->request( 'DELETE', $uri, $options );
	}
	
	// TODO: Document!
	public function request( $method = 'GET', $uri = '', $options = array() )
	{
		if ( isset( $options[ 'lint' ] ) )
		{
			$middleware_app = new Prack_Lint( $this->middleware_app );
			unset( $options[ 'lint' ] );
		}
		else
			$middleware_app = $this->middleware_app;
		
		$env    = self::envFor( $uri, array_merge( $options, array( 'method' => $method ) ) );
		$errors = $env[ 'rack.errors' ];
		
		list( $status, $headers, $body ) = $middleware_app->call( $env );
		
		$reflection = new ReflectionClass( 'Prack_Mock_Response' );
		return $reflection->newInstanceArgs( array( $status, $headers, $body, $errors ) );
	}
}