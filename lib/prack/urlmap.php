<?php

// TODO: Document!
class Prack_URLMap
  implements Prack_I_MiddlewareApp
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
		  return Prb::Ary( array (
		    isset( $h ) ? Prb::Num( -$h->length() )
		                : Prb::Num( -PHP_INT_MAX  ),
		    Prb::Num( -$l->length() )
		  ) );
		');
		$result = $hash_map->map( $callback );
		
		$this->mapping = $result->sortBy( $proxy_callback );
		return $result;
	}

	// TODO: Document!
	public function onRemap( $location, $middleware_app )
	{
		$location = Prb::Str( $location );
		
		if ( $location->match( '/\Ahttps?:\/\/(.*?)(\/.*)/', $matches ) )
		{
			$host     = Prb::Str( $matches[ 1 ][ 0 ] );
			$location = Prb::Str( $matches[ 2 ][ 0 ] );
		}
		else
			$host = null;
		
		if ( $location->slice( 0 )->raw() != '/' )
			throw new Prb_Exception_Argument( 'paths need to start with /' );
		
		$location   = $location->chomp( Prb::Str( '/' ) );
		$normalized = preg_replace( '/\//', '\/+', preg_quote( $location->raw() ) );
		$pattern    = "/\A{$normalized}(.*)/";
		
		return Prb::Ary( array(
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
			foreach ( $this->mapping->raw() as $mapping )
			{
				list( $mapping_host, $mapping_location, $mapping_matcher, $mapping_middleware_app ) = $mapping->raw();
				
				// All the conditions for which we'd consider the request host as a 'match':
				$host_viable = $mapping_host    == $env_http_host ||
				               $env_server_name == $env_http_host ||
				               ( is_null( $mapping_host ) &&
				                      ( $env_http_host->raw() == $env_server_name->raw() ||
				                        $env_http_host->raw() == $env_server_name->raw().':'.$env_server_port->raw() ) );
				
				// Skip the current entry if none of these strategies evaluate to true:
				if ( !$host_viable )
					continue;
				
				// Each entry has a regex pattern to match against. Check if the request URI matches:
				if ( !( preg_match_all( $mapping_matcher, $env_path->raw(), $matches ) > 0 ) )
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
				  Prb::Hsh( array(
				    'SCRIPT_NAME' => Prb::Str( $env_script_name->raw().$mapping_location->raw() ),
				    'PATH_INFO'   => Prb::Str( $matches[ 1 ][ 0 ] )
				  ) )
				);
				
				// Call the middleware the entry refers to, providing it the newly modified environment.
				$return = $mapping_middleware_app->call( $env );
				
				$env->mergeInPlace(
				  Prb::Hsh( array( 
				    'PATH_INFO'   => $env_path,
				    'SCRIPT_NAME' => $env_script_name
				  ) )
				);
				
				return $return;
			}
			
			$env->mergeInPlace(
			  Prb::Hsh( array( 
			    'PATH_INFO'   => $env_path,
			    'SCRIPT_NAME' => $env_script_name
			  ) )
			);
			
			return Prb::Ary( array(
			  Prb::Num( 404 ),
			  Prb::Hsh( array(
			    'Content-Type' => Prb::Str( 'text/html' ),
			    'X-Cascade'    => Prb::Str( 'pass' ),
			  ) ),
			  Prb::Ary( array( Prb::Str( "Not Found: {$env_path->raw()}" ) ) )
			) );
		}
		catch ( Exception $e )
		{
			$env->mergeInPlace(
			  Prb::Hsh( array(
			    'PATH_INFO'   => $env_path,
			    'SCRIPT_NAME' => $env_script_name
			  ) )
			);
			throw $e; // Not sure if this is what we want.
		}
	}
}