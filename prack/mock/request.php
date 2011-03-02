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
	static function envFor( $url = null, $options = null )
	{
		$url = is_null( $url ) ? Prack::_String() : $url;
		if ( !( $url instanceof Prack_Wrapper_String ) )
			throw new Prack_Error_Type( 'FAILSAFE: envFor $url must be Prack_Wrapper_String' );
		$options = is_null( $options ) ? Prack::_Hash() : $options;
		if ( !( $options instanceof Prack_Wrapper_Hash ) )
			throw new Prack_Error_Type( 'FAILSAFE: envFor $options must be Prack_Wrapper_Hash' );
		
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
		$env[ 'rack.errors'  ] = $options->delete( 'fatal' ) == true ? new Prack_Mock_FatalWarner()
		                                                             : new Prack_Utils_IO_String();
		
		// FIXME: Implement query building and multipart form data processing.
		if ( $params = $options->delete( 'params' ) )
		{
			if ( $env[ 'REQUEST_METHOD' ]->toN() == 'GET' )
			{
				if ( $params instanceof Prack_Wrapper_String )
					$params = Prack_Utils::i()->parseNestedQuery( $params );
				
				// FIXME: Implement update on Prack_Wrapper_Hash, also mergeInPlace.
				$params = $params->merge( Prack_Utils::i()->parseNestedQuery( $env[ 'QUERY_STRING' ] ) );
				
				$env[ 'QUERY_STRING' ] = Prack_Utils::i()->buildNestedQuery( $params );
			}
			else if ( !$options->contains( 'input' ) )
			{
				$options->set( 'CONTENT_TYPE', Prack::_String( 'application/x-www-form-urlencoded' ) );
				if ( $params instanceof Prack_Wrapper_Hash )
				{
					// FIXME: Implement multipart form data processing.
					if ( $multipart = false )
						die("FIXME: Implement multipart.");
					else
						$options->set( 'input', Prack_Utils::i()->buildNestedQuery( $params ) );
				}
				else
					$options->set( 'input', $params );
			}
		}
		
		if ( !$options->contains( 'input' ) )
			$options->set( 'input', Prack::_String() );
		
		$input = $options->delete( 'input' );
		if ( $input instanceof Prack_Interface_Stringable )
			$rack_input = Prack_Utils_IO::withString( $input->toS() );
		else if ( $input instanceof Prack_Interface_ReadableStreamlike )
			$rack_input = $input;
		else
		{
			$input_type = is_object( $input ) ? get_class( $input ) : gettype( $input );
			throw new Prack_Error_Type( "Provided rack input of type {$input_type} is neither String nor ReadableStreamlike" );
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
		$uri = is_null( $uri ) ? Prack::_String() : $uri;
		if ( !( $uri instanceof Prack_Wrapper_String ) )
			throw new Prack_Error_Type( 'FAILSAFE: mock request $uri must be Prack_Wrapper_String' );
		
		$options = is_null( $options ) ? Prack::_Hash() : $options;
		if ( !( $options instanceof Prack_Wrapper_Hash ) )
			throw new Prack_Error_Type( 'FAILSAFE: mock request $options must be Prack_Wrapper_Hash' );
		
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
		
		return new Prack_Mock_Response( $status, $headers, $body, $errors );
	}
}