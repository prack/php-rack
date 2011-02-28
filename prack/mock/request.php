<?php

// TODO: Document!
class Prack_FatalWarner
  implements Prack_Interface_WritableStreamlike
{
	// TODO: Document!
	public function puts()
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
		return Prack::_String();
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
	static function envFor( $url = null, $options = null )
	{
		$url = is_null( $url ) ? Prack::_String() : $url;
		if ( !( $url instanceof Prack_Interface_Stringable ) )
			throw new Prack_Error_Type( 'FAILSAFE: envFor $url must be Stringable' );
		
		$options = is_null( $options ) ? Prack::_Hash() : $options;
		if ( !( $options instanceof Prack_Wrapper_Hash ) )
			throw new Prack_Error_Type( 'FAILSAFE: envFor $options must be Hash' );
		
		$env = array(
			'rack.version'      => Prack::version(),
			'rack.input'        => new Prack_Utils_IO_String(),
			'rack.errors'       => new Prack_Utils_IO_String(),
			'rack.multithread'  => false,
			'rack.multiprocess' => false,
			'rack.run_once'     => true
		);
		
		$components = parse_url( $url->toN() );
		if ( $components === false )
			throw new RuntimeException( "unable to parse uri while creating mock environment: {$uri}" );
		
		$components = Prack::_Hash( $components );
		if ( ( $path = $components->get( 'path' ) ) && substr( $path, 0, 1 ) != '/' )
			$components->set( 'path', "/{$path}" );
		
		// Request method, uppercased ('GET' by default):
		$env[ 'REQUEST_METHOD' ] =
		  $options->contains( 'method' ) ? $options->delete( 'method' )->upcase()
		                                 : Prack::_String( 'GET' );
		
		$env[ 'rack.url_scheme' ] =
		  $components->contains( 'scheme' ) ? Prack::_String( $components->get( 'scheme' ) )
		                                    : Prack::_String( 'http' );
		$env[ 'HTTPS' ] =
		  $env[ 'rack.url_scheme' ]->toN() == 'https' ? Prack::_String( 'on' )
		                                                   : Prack::_String( 'off' );
		$default_port =
		  $env[ 'rack.url_scheme' ]->toN() == 'https' ? Prack::_String( '443' )
		                                                   : Prack::_String( '80'  );
		
		$env[ 'SERVER_NAME' ] = $components->contains( 'host' ) ? Prack::_String( $components->get( 'host' ) )
		                                                        : Prack::_String( 'example.org' );
		$env[ 'SERVER_PORT' ] = $components->contains( 'port' ) ? Prack::_String( $components->get( 'port' ) )
		                                                        : $default_port;
		
		// Script name, path info, query string:
		$path_info        = Prack::_String( $components->get( 'path' ) );
		$path_info_viable = !$path_info->isEmpty();
		
		$env[ 'SCRIPT_NAME'  ] = $options->contains( 'script_name' ) ? $options->delete( 'script_name' )
		                                                             : Prack::_String();
		$env[ 'PATH_INFO'    ] = $path_info_viable                   ? $path_info
		                                                             : Prack::_String( '/' );
		$env[ 'QUERY_STRING' ] = Prack::_String( (string)$components->get( 'query' ) );
		$env[ 'rack.errors'  ] = $options->delete( 'fatal' ) == true ? new Prack_FatalWarner()
		                                                             : new Prack_Utils_IO_String();
		
		// FIXME: Implement query building and multipart form data processing.
		/*
		if ( $params = $options->delete( 'params' ) )
		{
			if ( $env[ 'REQUEST_METHOD' ]->toN() == 'GET' )
			{
				if ( $params instanceof Prack_Interface_Stringable )
				{
					parse_str( $params->toN(), $params );
					$params = Prack::_Hash( $params );
				}
				
				parse_str( $env[ 'QUERY_STRING' ]->toN(), $params_from_query_string );
				$params = $params->merge( Prack::_Hash( $params_from_query_string ) );
				
				$env[ 'QUERY_STRING' ] = Prack::_String( http_build_query( $params->toN() ) );
			}
			else if ( !$options->contains( 'input' ) )
			{
				$options->set( 'CONTENT_TYPE', Prack::_String( 'application/x-www-form-urlencoded' ) );
				if ( $params instanceof Prack_Interface_Enumerable )
				{
					// FIXME: Implement multipart form data processing.
					// START FIXME
					# Ruby code, for reference: 
					if data = Utils::Multipart.build_multipart(params)
					  opts[ :input ] = data
					  opts[ "CONTENT_LENGTH" ] ||= data.length.to_s
					  opts[ "CONTENT_TYPE" ] = "multipart/form-data; boundary=#{Utils::Multipart::MULTIPART_BOUNDARY}"
					else
					  opts[ :input ] = Utils.build_nested_query(params)
					end
					// END FIXME
					if ( $multipart = false )
						echo "TODO: Implement multipart.";
					else
					{
						$query = urldecode( http_build_query( $params->toN() ) );
						$options->set( 'input', Prack::_String( $query ) );
					}
				}
				else
					$options->set( 'input', $params );
			}
		}
		*/
		
		if ( !$options->contains( 'input' ) )
			$options->set( 'input', Prack::_String() );
		
		$input = $options->delete( 'input' );
		if ( $input instanceof Prack_Wrapper_String )
			$rack_input = Prack_Utils_IO::withString( $input );
		else if ( $input instanceof Prack_Interface_ReadableStreamlike )
			$rack_input = $input;
		else
		{
			$input_type = is_object( $input ) ? get_class( $input ) : gettype( $input );
			throw new Prack_Error_Type( "Provided rack input of type {$input} is neither String nor ReadableStreamlike" );
		}
		
		$env[ 'rack.input' ] = $rack_input;
		
		if ( !isset( $env[ 'CONTENT_LENGTH' ] ) )
			$env[ 'CONTENT_LENGTH' ] = Prack::_String( (string)$rack_input->length() );
		
		foreach ( $options->toN() as $field => $value )
			$env[ $field ] = $value;
		
		return Prack::_Hash( $env );
	}
	
	// TODO: Document!
	function __construct( $middleware_app )
	{
		$this->middleware_app = $middleware_app;
	}
	
	// TODO: Document!
	public function get( $uri, $options = null )
	{
		return $this->request( Prack::_String( 'GET' ), $uri, $options );
	}
	
	// TODO: Document!
	public function post( $uri, $options = null )
	{
		return $this->request( Prack::_String( 'POST' ), $uri, $options );
	}
	
	// TODO: Document!
	public function put( $uri, $options = null )
	{
		return $this->request( Prack::_String( 'PUT' ), $uri, $options );
	}
	
	// TODO: Document!
	public function delete( $uri, $options = null )
	{
		return $this->request( Prack::_String( 'DELETE' ), $uri, $options );
	}
	
	// TODO: Document!
	public function request( $method, $uri = null, $options = null )
	{
		if ( is_null( $uri ) )
			$uri = Prack::_String();
		if ( is_null( $options ) )
			$options = Prack::_Hash();
		
		if ( !( $options instanceof Prack_Wrapper_Hash ) )
			throw new Prack_Error_Type( 'FAILSAFE: request $options must be Prack_Wrapper_Hash' );
		
		if ( $lint = $options->delete( 'lint' ) )
			$middleware_app = new Prack_Lint( $this->middleware_app );
		else
			$middleware_app = $this->middleware_app;
		
		$options = $options->merge(
			Prack::_Hash( array( 'method' => $method ) )
		);
		
		$env    = self::envFor( $uri, $options );
		$errors = $env->get( 'rack.errors' );
		
		list( $status, $headers, $body ) = $middleware_app->call( $env );
		
		$reflection = new ReflectionClass( 'Prack_Mock_Response' );
		return $reflection->newInstanceArgs( array( $status, $headers, $body, $errors ) );
	}
}