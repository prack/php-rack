<?php

// TODO: Document!
class Prack_URLMap
  implements Prack_Interface_MiddlewareApp
{
	const ENTRY_ELEMENT_COUNT = 4;
	
	private $entries;
	
	// TODO: Document!
	static function with( $hash_map )
	{
		return new Prack_URLMap( $hash_map );
	}
	
	// TODO: Document!
	function __construct( $hash_map )
	{
		$this->remap( $hash_map );
	}
	
	// TODO: Document!
	public function remap( $hash_map )
	{
		$callback       = array( $this, 'onRemap' );
		$proxy_callback = create_function( '$h,$l,$m,$a', '
		  return Prack::_Array( array (
		    isset( $h ) ? Prack_Wrapper_Numeric::with( -$h->length() )
		                : Prack_Wrapper_Numeric::with( -PHP_INT_MAX  ),
		    Prack_Wrapper_Numeric::with( -$l->length() )
		  ) );
		');
		$result = $hash_map->map( $callback );
		
		$this->mapping = $result->sortBy( $proxy_callback );
		return $result;
	}

	// TODO: Document!
	public function onRemap( $location, $middleware_app )
	{
		$location = Prack::_String( $location );
		
		if ( $location->match( '/\Ahttps?:\/\/(.*?)(\/.*)/', $matches ) )
		{
			$host     = Prack::_String( $matches[ 1 ][ 0 ] );
			$location = Prack::_String( $matches[ 2 ][ 0 ] );
		}
		else
			$host = null;
		
		if ( $location->slice( 0 )->toN() != '/' )
			throw new Prack_Error_Argument( 'paths need to start with /' );
		
		$location   = $location->chomp( Prack::_String( '/' ) );
		$normalized = preg_replace( '/\//', '\/+', preg_quote( $location->toN() ) );
		$pattern    = "/\A{$normalized}(.*)/";
		
		return Prack::_Array( array(
		  $host, $location, $pattern, $middleware_app
		) );
	}
	
	// TODO: Document!
	// This is where requests get routed.
	public function call( $env )
	{
		$env_server_name = $env->get( 'SERVER_NAME' );
		$env_server_port = $env->get( 'SERVER_PORT' );
		$env_path        = $env->get( 'PATH_INFO'   );
		$env_script_name = $env->get( 'SCRIPT_NAME' );
		$env_http_host   = $env->get( 'HTTP_HOST'   );
		
		try
		{
			foreach ( $this->mapping->toN() as $mapping )
			{
				list( $mapping_host, $mapping_location, $mapping_matcher, $mapping_middleware_app ) = $mapping->toN();
				
				// All the conditions for which we'd consider the request host as a 'match':
				$host_viable = $mapping_host    == $env_http_host ||
				               $env_server_name == $env_http_host ||
				               ( is_null( $mapping_host ) &&
				                      ( $env_http_host->toN() == $env_server_name->toN() ||
				                        $env_http_host->toN() == $env_server_name->toN().':'.$env_server_port->toN() ) );
				
				// Skip the current entry if none of these strategies evaluate to true:
				if ( !$host_viable )
					continue;
				
				// Each entry has a regex pattern to match against. Check if the request URI matches:
				if ( !( preg_match_all( $mapping_matcher, $env_path->toN(), $matches ) > 0 ) )
					continue;
				
				// If the request URI matches, the remainder (i.e. $match[ 1 ]) should start with a '/':
				if ( !( empty( $matches[ 1 ][ 0 ] ) || substr( $matches[ 1 ][ 0 ], 0, 1 ) == '/' ) )
					continue;
				
				// If we got here, we found a matching route. Given:
				//   a) we're matching against '/admin/panel'
				//   b) the request URI (minus host) is '/admin/panel/foo'
				// This next line will change the environment properties thusly:
				//   SCRIPT_NAME => '/admin/panel'
				//   PATH_INFO   => '/foo'
				// Note that any query string won't make it into PATH_INFO because the web server will put it in QUERY_STRING.
				$env->mergeInPlace(
				  Prack::_Hash( array(
				    'SCRIPT_NAME' => Prack::_String( $env_script_name->toN().$mapping_location->toN() ),
				    'PATH_INFO'   => Prack::_String( $matches[ 1 ][ 0 ] )
				  ) )
				);
				
				// Call the middleware the entry refers to, providing it the newly modified environment.
				return $mapping_middleware_app->call( $env );
			}
			
			return array(
			  404,
			  Prack::_Hash( array(
			    'Content-Type' => Prack::_String( 'text/html' ),
			    'X-Cascade'    => Prack::_String( 'pass' ),
			  ) ),
			  Prack::_Array( array( Prack::_String( "Not Found: {$env_path->toN()}" ) ) )
			);
		}
		catch ( Exception $e )
		{
			$env->mergeInPlace(
			  Prack::_Hash( array(
			    'PATH_INFO'   => $env_path,
			    'SCRIPT_NAME' => $env_script_name
			  ) )
			);
			throw $e; // Not sure if this is what we want.
		}
	}
}