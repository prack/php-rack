<?php

// TODO: Document!
class Prack_ModPHP_Compat
{
	// TODO: Document!
	static function singleton()
	{
		static $singleton = null;
		
		if ( is_null( $singleton ) )
			$singleton = new Prack_ModPHP_Compat();
		
		return $singleton;
	}
	
	// TODO: Document!
	public function extractEnv( $server )
	{
		$env = array();
		foreach ( $server as $variable => $value )
			$env[ $variable ] = $value;
		
		// Undoing PHP's stupid auto-processing of credentials.
		$auth_user = @$env[ 'PHP_AUTH_USER' ];
		$auth_pass = @$env[ 'PHP_AUTH_PW'   ];
		if ( $auth_user && $auth_pass )
			$env[ 'HTTP_AUTHORIZATION' ] = 'Basic '.base64_encode( $auth_user.':'.$auth_pass );
		
		$env[ 'rack.version'       ] = Prack::version();
		$env[ 'rack.input'         ] = Prb_IO::withStream( fopen( 'php://stdin',  'r+b' ) );
		$env[ 'rack.errors'        ] = Prb_IO::withStream( fopen( 'php://stderr', 'w+b' ) );
		$env[ 'rack.multithread'   ] = 0;
		$env[ 'rack.multiprocess'  ] = 1;
		$env[ 'rack.run_once'      ] = 1;
		$env[ 'SCRIPT_NAME'        ] = '';
		$env[ 'PATH_INFO'          ] = @$server[ 'REDIRECT_URL' ] ? $server[ 'REDIRECT_URL' ] : '/';
		
		return $env;
	}
	
	// TODO: Document!
	public function render( $response )
	{
		list( $status, $headers, $body ) = $response;
		
		// Send the status header.
		header( 'x', null, (int)$status );
		
		// Send other headers.
		foreach ( $headers as $header => $value )
			header( "{$header}:{$value}", false );
		
		$callback = array( $this, 'onRender' );
		
		if ( is_string( $body ) )
			$body = array( $body );
		else if ( $body instanceof Prb_I_Enumerable )
		{
			static $callback = null;
			if ( is_null( $callback ) )
				$callback = create_function( '$part', 'echo $part;' );
			$body->each( $callback );
		}
		else if ( is_array( $body ) )
		{
			foreach( $body as $part )
				echo $part;
		}
	}
}
/*
	class Middleware_RackCompliance extends Prack_App {
		public function call(&$env) {
			// Rack variables:
			
			if (isset($env['CONTENT_TYPE_OVERRIDE']) && !empty($env['REDIRECT_OLD_CONTENT_TYPE'])) {
				$env[self::CONTENTTYPE] = $env['REDIRECT_OLD_CONTENT_TYPE'];
				unset($env['REDIRECT_OLD_CONTENT_TYPE']);
			}
			
			if (isset($env['AUTHORIZATION_OVERRIDE']) && !empty($env['REDIRECT_OLD_AUTHORIZATION'])) {
				$env[self::HTTPAUTHORIZATION] = $env['REDIRECT_OLD_AUTHORIZATION'];
				unset($env['REDIRECT_OLD_AUTHORIZATION']);
			}
		}
	}
*/