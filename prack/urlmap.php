<?php

// TODO: Document!
class Prack_URLMap implements Prack_IMiddlewareApp
{
	const ENTRY_ELEMENT_COUNT = 4;
	
	private $entries;
	
	// TODO: Document!
	function __construct( $builders )
	{
		$this->remap( $builders );
	}
	
	// TODO: Document!
	public function remap( $builders )
	{
		$this->entries = array();
		
		foreach ( $builders as $builder )
			array_push( $this->entries, $builder->toArray() );
		
		$comparison_function = create_function('$a,$b', 'return strlen($b[ 0 ].$b[ 1 ]) - strlen($a[ 0 ].$a[ 1 ]);');
		usort( $this->entries, $comparison_function );
		
		return $this->entries;
	}
	
	// TODO: Document!
	// This is where requests get routed.
	public function call( &$env )
	{
		$env_server_name = $env[ 'SERVER_NAME' ];
		$env_server_port = $env[ 'SERVER_PORT' ];
		$env_path        = $env[ 'PATH_INFO'   ];
		$env_script_name = $env[ 'SCRIPT_NAME' ];
		$env_http_host   = isset( $env[ 'HTTP_HOST' ] ) ? $env[ 'HTTP_HOST' ] : '';
		
		try
		{
			foreach ( $this->entries as $entry )
			{
				list( $entry_host, $entry_location, $entry_matcher, $entry_middleware_app ) = $entry;
				
				// All the conditions for which we'd consider the request host as a 'match':
				$entry_host_viable  = ($entry_host      == $env_http_host);
				$server_name_viable = ($env_server_name == $env_http_host);
				$http_host_viable   = empty( $entry_host) &&
				                      ( $env_http_host == $env_server_name ||
				                        $env_http_host == $env_server_name.':'.$env_server_port );
				
				// Skip the current entry if none of these strategies evaluate to true:
				if ( !( $entry_host_viable || $server_name_viable || $http_host_viable ) )
						continue;
				
				// Each entry has a regex pattern to match against. Check if the request URI matches:
				$matches = array();
				if ( !( preg_match( $entry_matcher, (string)$env_path, $matches ) && isset( $matches[ 1 ] ) ) )
					continue;
				
				// If the request URI matches, the remainder (i.e. $match[ 1 ]) should start with a '/':
				if ( !( empty( $matches[ 1 ] ) || substr( $matches[ 1 ], 0, 1 ) == '/' ) )
					continue;
				
				// If we got here, we found a matching route. Given:
				//   a) we're matching against '/admin/panel'
				//   b) the request URI (minus host) is '/admin/panel/foo'
				// This next line will change the environment properties thusly:
				//   SCRIPT_NAME => '/admin/panel'
				//   PATH_INFO   => '/foo'
				// Note that any query string won't make it into PATH_INFO because the web server will put it in QUERY_STRING.
				$env = array_merge( $env, array( 'SCRIPT_NAME' => $env_script_name.$entry_location, 'PATH_INFO' => $matches[ 1 ] ) );
				
				// Call the middleware the entry refers to, providing it the newly modified environment.
				return $entry_middleware_app->call( $env );
			}
			return array( 404, array( 'Content-Type' => 'text/html', 'X-Cascade' => "pass" ), array( "Not Found: {$env_path}" ) );
		}
		catch (Exception $e)
		{
			$env = array_merge( $env, array( 'PATH_INFO' => $env_path, 'SCRIPT_NAME' => $env_script_name) );
			throw $e;
		}
	}
	
	// TODO: Document!
	public function &getEntries()
	{
		return $this->entries;
	}
}