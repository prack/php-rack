<?php

// TODO: Document!
class Prack_MethodOverride
  implements Prack_I_MiddlewareApp
{
	private $middleware_app;
	
	const METHOD_OVERRIDE_PARAM_KEY   = '_method';
	const HTTP_METHOD_OVERRIDE_HEADER = 'HTTP_X_HTTP_METHOD_OVERRIDE';
	
	// TODO: Document!
	static function httpMethods()
	{
		static $http_methods = null;
		
		if ( is_null( $http_methods ) )
			$http_methods = array( 'GET', 'HEAD', 'PUT', 'POST', 'DELETE', 'OPTIONS' );
		
		return $http_methods;
	}
	
	// TODO: Document!
	static function with( $middleware_app )
	{
		return new Prack_MethodOverride( $middleware_app );
	}
	
	// TODO: Document!
	function __construct( $middleware_app )
	{
		$this->middleware_app = $middleware_app;
	}
	
	// TODO: Document!
	public function call( &$env )
	{
		if ( @$env[ 'REQUEST_METHOD' ] == 'POST' )
		{
			$request = Prack_Request::with( $env );
			$POST    = $request->POST();
			$method  = @$POST[ self::METHOD_OVERRIDE_PARAM_KEY ]
			  ? (string)@$POST[ self::METHOD_OVERRIDE_PARAM_KEY ]
			  : (string)@$env[ self::HTTP_METHOD_OVERRIDE_HEADER ];
			
			$method = strtoupper( $method );
			if ( in_array( $method, self::httpMethods() ) )
			{
				$env[ 'rack.methodoverride.original_method' ] = $env[ 'REQUEST_METHOD' ];
				$env[ 'REQUEST_METHOD'] = $method;
			}
		}
		
		return $this->middleware_app->call( $env );
	}
}
