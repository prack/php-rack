<?php

// TODO: Document!
class Prack_MethodOverride
  implements Prack_Interface_MiddlewareApp
{
	private $middleware_app;
	
	const METHOD_OVERRIDE_PARAM_KEY   = '_method';
	const HTTP_METHOD_OVERRIDE_HEADER = 'HTTP_X_HTTP_METHOD_OVERRIDE';
	
	// TODO: Document!
	static function httpMethods()
	{
		static $http_methods = null;
		
		if ( is_null( $http_methods ) )
		{
			$http_methods = Prb::_Array( array(
			  Prb::_String( 'GET'     ),
			  Prb::_String( 'HEAD'    ),
			  Prb::_String( 'PUT'     ),
			  Prb::_String( 'POST'    ),
			  Prb::_String( 'DELETE'  ),
			  Prb::_String( 'OPTIONS' )
			) );
		}
		
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
	public function call( $env )
	{
		if ( $env->get( 'REQUEST_METHOD' )->toN() == 'POST' )
		{
			$request = Prack_Request::with( $env );
			$method  = $request->POST()->contains( self::METHOD_OVERRIDE_PARAM_KEY )
			  ? $request->POST()->get( self::METHOD_OVERRIDE_PARAM_KEY   )
			  : $env->get( self::HTTP_METHOD_OVERRIDE_HEADER );
			$method = isset( $method ) ? $method->toS()->upcase() : Prb::_String();
			if ( self::httpMethods()->contains( $method ) )
			{
				$env->set( 'rack.methodoverride.original_method', $env->get( 'REQUEST_METHOD' ) );
				$env->set( 'REQUEST_METHOD', $method );
			}
		}
		
		return $this->middleware_app->call( $env );
	}
}
