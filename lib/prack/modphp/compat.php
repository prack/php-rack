<?php

// TODO: Document!
class Prack_ModPHP_Compat
{
	const RACK_VERSION      = 'rack.version'     ;
	const RACK_URLSCHEME    = 'rack.url_scheme'  ;
	const RACK_INPUT        = 'rack.input'       ;
	const RACK_ERRORS       = 'rack.errors'      ;
	const RACK_MULTITHREAD  = 'rack.multithread' ;
	const RACK_MULTIPROCESS = 'rack.multiprocess';
	const RACK_RUNONCE      = 'rack.runonce'     ;
	
	// TODO: Document!
	static function singleton()
	{
		static $singleton = null;
		
		if ( is_null( $singleton ) )
			$singleton = new Prack_ModPHP_Compat();
		
		return $singleton;
	}
	
	// TODO: Document!
	public function rackEnvFrom( $server )
	{
		$env = Prb::_Hash();
		
		foreach ( $server as $variable => $value )
			$env->set( $variable, Prb::_String( $value ) );
		
		// Undoing PHP's stupid auto-processing of credentials.
		$auth_user = $env->delete( 'PHP_AUTH_USER' );
		$auth_pass = $env->delete( 'PHP_AUTH_PW'   );
		if ( $auth_user && $auth_pass )
		{
			$env->set(
			  'X_HTTP_AUTHORIZATION',
			  Prb::_String( 'Basic ' )->concat(
			    Prb::_Array( array(
			      $auth_user, $auth_pass
			    ) )->join( Prb::_String( ':' ) )->base64Encode()
			  )
			);
		}
		
		$env->set( 'SCRIPT_NAME', Prb::_String() );
		$env->set( 'PATH_INFO',   Prb::_String( $server[ 'REDIRECT_X_PRACK_PATHINFO' ] ) );
		
		return $env;
	}
	
	// TODO: Document!
	public function render( $response )
	{
		list( $status, $headers, $body ) = $response->toA()->raw();
		
		// Send the status header.
		header( 'x', null, $status->raw() );
		
		// Send other headers.
		foreach ( $headers->toHash()->raw() as $header => $value )
			header( "{$header}:{$value->raw()}", false );
		
		$callback = array( $this, 'onRender' );
		
		if ( $body instanceof Prb_Interface_Stringable )
			$body = Prb::_Array( array( $body->toS() ) );
		
		if ( $headers->contains( 'X-Runtime' ) )
			echo "<h3>Run time: {$headers->get( "X-Runtime" )->raw()}</h3>";
		
		$body->each( $callback );
	}
	
	// TODO: Document!
	public function onRender( $body_part )
	{
		echo $body_part->raw();
	}
}
/*
<?php
	class Middleware_RackCompliance extends Prack_App {
		protected $environment = array();
		
		const HTTPAUTHORIZATION = 'HTTP_AUTHORIZATION';
		
		// Required server variables:
		// Note: There will also be any number of HTTP_* header variables set.
		const REQUESTMETHOD     = 'REQUEST_METHOD' ;
		const SCRIPTNAME        = 'SCRIPT_NAME'    ;
		const PATHINFO          = 'PATH_INFO'      ;
		const QUERYSTRING       = 'QUERY_STRING'   ;
		const SERVERNAME        = 'SERVER_NAME'    ;
		const SERVERPORT        = 'SERVER_PORT'    ;
		const CONTENTTYPE       = 'CONTENT_TYPE'   ; // Special case.
		
		// Required Rack-specific variables:
		
		// Additional Rack-specific variables:
		const RACK_SESSION      = 'rack.session'     ;
		const RACK_LOGGER       = 'rack.logger'      ;
		
		public static function urlScheme($uri) {
			$matches = array();
			preg_match('/^(\w+):\/\//', $uri, $matches); // Parse URL protocol
			return $matches[1];
		}

		public function call(&$env) {
			// Server variables;
			$env[self::SCRIPTNAME]        = '';
			$env[self::PATHINFO]          = $env['SCRIPT_URL'];
			
			// Rack variables:
			$env[self::RACK_VERSION]      = array(1, 1);
			$env[self::RACK_URLSCHEME]    = self::urlScheme($server_variables['SCRIPT_URI']);
			$env[self::RACK_INPUT]        = fopen('php://input',  'r');
			$env[self::RACK_ERRORS]       = fopen('php://stderr', 'w');
			$env[self::RACK_MULTITHREAD]  = false;
			$env[self::RACK_MULTIPROCESS] = false;
			$env[self::RACK_RUNONCE]      = false;
			
			if (isset($env['CONTENT_TYPE_OVERRIDE']) && !empty($env['REDIRECT_OLD_CONTENT_TYPE'])) {
				$env[self::CONTENTTYPE] = $env['REDIRECT_OLD_CONTENT_TYPE'];
				unset($env['REDIRECT_OLD_CONTENT_TYPE']);
			}
			
			if (isset($env['AUTHORIZATION_OVERRIDE']) && !empty($env['REDIRECT_OLD_AUTHORIZATION'])) {
				$env[self::HTTPAUTHORIZATION] = $env['REDIRECT_OLD_AUTHORIZATION'];
				unset($env['REDIRECT_OLD_AUTHORIZATION']);
			}
			
			$rawresponse = $this->app->call($env);
			$response    = new Prack_Response($rawresponse[2], $rawresponse[0], $rawresponse[1]);
			
			return $response->finish();
		}
	}
	*/