<?php


class Prack_FatalWarner
{
	public function puts( $warning )
	{
		throw new Prack_Error_Mock_Request_FatalWarning( $warning );
	}
	
	public function write( $warning )
	{
		throw new Prack_Error_Mock_Request_FatalWarning( $warning );
	}
	
	public function flush()
	{
		// No-op.
		return true;
	}
	
	public function string()
	{
		// Not sure why this is in the Ruby version.
		return '';
	}
}


class Prack_Mock_Request
{
	private $middleware_app;
	
	
	public static function envFor( $uri = '', $options = array() )
	{
		$env = 
			array(
				'rack.version'      => Prack::version(),
				'rack.input'        => fopen( "php://memory", "r+b" ),
				'rack.errors'       => fopen( "php://memory", "w+b" ),
				'rack.multithread'  => false,
				'rack.multiprocess' => false,
				'rack.run_once'     => true
			);
		
		$uri_components   = parse_url( $uri );
		$necessary_fields = array( 'scheme', 'host', 'port', 'path', 'query' );
		foreach ( $necessary_fields as $field )
			$uri_components[ $field ] = isset( $uri_components[ $field ] ) ? $uri_components[ $field ] : '';
		
		$env[ 'REQUEST_METHOD'  ] = !empty( $options[ 'method' ] ) ? strtoupper( (string)$options[ 'method' ] ) : 'GET';
		$env[ 'SERVER_NAME'     ] = !empty( $uri_components[ 'host' ] ) ? $uri_components[ 'host' ] : 'example.org';
		$env[ 'SERVER_PORT'     ] = !empty( $uri_components[ 'port' ] ) ? (string)$uri_components[ 'port' ] : '80';
		$env[ 'QUERY_STRING'    ] = $uri_components[ 'query' ];
		$env[ 'PATH_INFO'       ] = empty( $uri_components[ 'path' ] ) ? '/' : $uri_components[ 'path' ];
		$env[ 'rack.url_scheme' ] = empty( $uri_components[ 'scheme' ] ) ? 'http' : $uri_components[ 'scheme' ];
		$env[ 'HTTPS'           ] = $env[ 'rack.url_scheme' ] == 'https' ? 'on' : 'off';
		$env[ 'SCRIPT_NAME'     ] = isset( $options[ 'script_name' ] ) ? $options[ 'script_name' ] : '';
		
		if ( isset( $options[ 'fatal' ] ) )
			$env[ 'rack.errors' ] = new Prack_FatalWarner();
		else
			$env[ 'rack.errors' ] = new Prack_ErrorLogger( fopen( 'php://memory', 'w+b' ) );
		
		$params = isset( $options[ 'params' ] ) ? $options[ 'params' ] : null;
		if ( !is_null( $params ) )
		{
			if ( $env[ 'REQUEST_METHOD' ] == 'GET' )
			{
				if ( is_string( $params ) )
					parse_str( $params, $params );
					
				parse_str( $env[ 'QUERY_STRING' ], $params_from_query_string );
				$params = array_merge( $params, $params_from_query_string );
				
				$env[ 'QUERY_STRING' ] = http_build_query( $params );
			}
			else if ( !isset( $options[ 'input' ] ) )
			{
				$options[ 'CONTENT_TYPE' ] = 'application/x-www-form-urlencoded';
				if ( is_array( $params ) )
				{
					/* TODO: implement multipart form data parsing.
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
						$options[ 'input' ] = http_build_query( $params );
				}
				else
					$options[ 'input' ] = $params;
			}
		}
		
		if ( !isset( $options[ 'input' ] ) )
			$options[ 'input' ] = '';
		
		if ( is_string( $options[ 'input' ] ) )
		{
			$stream = fopen( 'php://memory', 'x+b' );
			fputs( $stream, $options[ 'input' ] );
			$rack_input = new Prack_RewindableInput( $stream );
			$rack_input->getLength(); // Trigger rewind before we close original stream.
			fclose( $stream );
		}
		// Unlike Ruby, we have to require Prack_RewindableInput here: since the PHP stream
		// functions aren't objects with state, we have no StringIO, and hence no 'length'.
		// Moreover, we have no common interface between PHP stream functions and our
		// rewindable stream (which is full-fledged object, and not manipulated by functions.)
		// Consequently, it's just easier to wrap every PHP stream in an a rewindable.
		else if ( $options[ 'input' ] instanceof Prack_RewindableInput )
			$rack_input = $options[ 'input' ];
		else
			throw new Prack_Error_Mock_Request_RackInputMustBeInstanceOfPrackRewindableInput();
		
		$env[ 'rack.input' ] = $rack_input;
		if ( !isset( $env[ 'CONTENT_LENGTH' ] ) )
			$env[ 'CONTENT_LENGTH' ] = (string)$rack_input->getLength();
		
		foreach ($options as $field => $value)
		{
			if ( is_string( $field ) )
				$env[ $field ] = $value;
		}
		
		return $env;
	}
	
	
	function __construct( $middleware_app )
	{
		$this->middleware_app = $middleware_app;
	}
	
	
	public function get( $uri, $options = array() )
	{
		return $this->request( 'GET', $uri, $options );
	}
	
	
	public function post( $uri, $options = array() )
	{
		return $this->request( 'POST', $uri, $options );
	}
	
	
	public function put( $uri, $options = array() )
	{
		return $this->request( 'PUT', $uri, $options );
	}
	
	
	public function delete( $uri, $options = array() )
	{
		return $this->request( 'DELETE', $uri, $options );
	}
	
	
	public function request( $method = 'GET', $uri = '', $options = array() )
	{
		$env = self::envFor( $uri, array_merge( $options, array( 'method' => $method ) ) );
		
		if ( isset( $options[ 'lint' ] ) )
			$middleware_app = new SampleMiddleware(); // new Prack_Lint( $middleware_app )
		else
			$middleware_app = $this->middleware_app;
		
		list( $status, $header, $body ) = $middleware_app->call( $env );
		
		return new Prack_Mock_Response( $status, $header, $body, $env[ 'rack.errors' ] );
	}
	
	
	public function getMiddlewareApp()
	{
		return $this->middleware_app;
	}
}


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

/*

*/