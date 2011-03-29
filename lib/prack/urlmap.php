<?php

// TODO: Document!
class Prack_URLMap
  implements Prack_I_MiddlewareApp
{
	const ENTRY_ELEMENT_COUNT = 4;
	
	private $entries;
	
	// TODO: Document!
	static function with( $lookup )
	{
		return new Prack_URLMap( $lookup );
	}
	
	// TODO: Document!
	function __construct( $lookup )
	{
		$this->remap( $lookup );
	}
	
	// TODO: Document!
	public function remap( $lookup )
	{
		$this->mapping = array();
		
		$remaps = array();
		foreach( $lookup as $location => $middleware_app )
		{
			if ( (bool)preg_match_all( '/\Ahttps?:\/\/(.*?)(\/.*)/', $location, $matches ) )
			{
				$host     = $matches[ 1 ][ 0 ];
				$location = $matches[ 2 ][ 0 ];
			}
			else
				$host = null;
			
			if ( substr( $location, 0, 1 ) != '/' )
				throw new Prb_Exception_Argument( 'paths need to start with /' );
			
			$location   = rtrim( $location, '/' );
			$normalized = preg_replace( '/\//', '\/+', preg_quote( $location ) );
			$pattern    = "/\A{$normalized}(.*)/";
			
			array_push( $remaps, array( $host, $location, $pattern, $middleware_app ) );
		}
		
		static $proxy_callback = null;
		if ( is_null( $proxy_callback ) )
		  $proxy_callback = create_function(
		    '$h, $l, $m, $a',
		    'return array ( isset( $h ) ? -strlen( $h ) : -(int)(PHP_INT_MAX / 2), -strlen( $l ) );'
		  );
		
		$proxies = array();
		foreach ( $remaps as $remap )
			array_push( $proxies, call_user_func_array( $proxy_callback, $remap ) );
		
		static $proxy_sort_callback = null;
		if ( is_null( $proxy_sort_callback ) )
		  $proxy_sort_callback = create_function( '$l, $r', 'return abs( array_sum( $r ) ) - abs( array_sum( $l ) ) ;' );
		
		uasort( $proxies, $proxy_sort_callback );
		
		foreach ( $proxies as $key => $value )
			array_push( $this->mapping, $remaps[ $key ] );
		
		return $this->mapping;
	}

	// TODO: Document!
	// This is where requests get routed.
	public function call( &$env )
	{
		$env_server_name = @$env[ 'SERVER_NAME' ];
		$env_server_port = @$env[ 'SERVER_PORT' ];
		$env_path        = @$env[ 'PATH_INFO'   ];
		$env_script_name = @$env[ 'SCRIPT_NAME' ];
		$env_http_host   = @$env[ 'HTTP_HOST'   ];
		
		try
		{
			foreach ( $this->mapping as $mapping )
			{
				list( $mapping_host, $mapping_location, $mapping_matcher, $mapping_middleware_app ) = $mapping;
				
				// All the conditions for which we'd consider the request host as a 'match':
				$host_viable = $mapping_host    == $env_http_host ||
				               $env_server_name == $env_http_host ||
				               ( is_null( $mapping_host ) &&
				                      ( $env_http_host == $env_server_name ||
				                        $env_http_host == $env_server_name.':'.$env_server_port ) );
				
				// Skip the current entry if none of these strategies evaluate to true:
				if ( !$host_viable )
					continue;
				
				// Each entry has a regex pattern to match against. Check if the request URI matches:
				if ( !( preg_match_all( $mapping_matcher, $env_path, $matches ) > 0 ) )
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
				$env = array_merge( $env, array( 'SCRIPT_NAME' => $env_script_name.$mapping_location, 'PATH_INFO' => $matches[ 1 ][ 0 ] ) );
				
				// Call the middleware the entry refers to, providing it the newly modified environment.
				$return = $mapping_middleware_app->call( $env );
				
				$env = array_merge( $env, array( 'PATH_INFO' => $env_path, 'SCRIPT_NAME' => $env_script_name ) );
				
				return $return;
			}
			
			array_merge( $env, array( 'PATH_INFO' => $env_path, 'SCRIPT_NAME' => $env_script_name ) );
			
			return array( 404, array( 'Content-Type' => 'text/html', 'X-Cascade' => 'pass' ), array( "Not Found: {$env_path}" ) );
		}
		catch ( Exception $e )
		{
			$env = array_merge( $env, array( 'PATH_INFO' => $env_path, 'SCRIPT_NAME' => $env_script_name ) );
			throw $e; // Not sure if this is what we want.
		}
	}
}