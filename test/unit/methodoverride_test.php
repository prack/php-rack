<?php

// TODO: Document!
class Prack_MethodOverrideTest_ReqMaker
  implements Prack_I_MiddlewareApp
{
	private $app;
	
	// TODO: Document!
	public function call( &$env )
	{
		return Prack_Request::with( $env );
	}
}

class Prack_MethodOverrideTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * It should not affect GET requests
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_affect_GET_requests()
	{
		$env = Prack_Mock_Request::envFor( '/?_method=delete', array( 'method' => 'GET' ) );
		$middleware_app = Prack_MethodOverride::with( new Prack_MethodOverrideTest_ReqMaker() );
		$request        = $middleware_app->call( $env );
		$env            = $request->getEnv();
		$this->assertEquals( 'GET', $env[ 'REQUEST_METHOD' ] );
	} // It should not affect GET requests
	
	/**
	 * It should modify REQUEST_METHOD for POST requests when _method parameter is set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_modify_REQUEST_METHOD_for_POST_requests_when__method_parameter_is_set()
	{
		$env = Prack_Mock_Request::envFor(
		  '/', array( 'method' => 'POST', 'input' => '_method=put' ) );
		$middleware_app = Prack_MethodOverride::with( new Prack_MethodOverrideTest_ReqMaker() );
		$request        = $middleware_app->call( $env );
		$env            = $request->getEnv();
		$this->assertEquals( 'PUT', $env[ 'REQUEST_METHOD' ] );
	} // It should modify REQUEST_METHOD for POST requests when _method parameter is set
	
	/**
	 * It should modify REQUEST_METHOD for POST requests when X-HTTP-Method-Override is set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_modify_REQUEST_METHOD_for_POST_requests_when_X_HTTP_Method_Override_is_set()
	{
		$env = Prack_Mock_Request::envFor(
		  '/', array( 'method' => 'POST', 'HTTP_X_HTTP_METHOD_OVERRIDE' => 'PUT' ) );
		$middleware_app = Prack_MethodOverride::with( new Prack_MethodOverrideTest_ReqMaker() );
		$request        = $middleware_app->call( $env );
		$env            = $request->getEnv();
		$this->assertEquals( 'PUT', $env[ 'REQUEST_METHOD' ] );
	} // It should modify REQUEST_METHOD for POST requests when X-HTTP-Method-Override is set
	
	/**
	 * It should not modify REQUEST_METHOD if the method is unknown
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_modify_REQUEST_METHOD_if_the_method_is_unknown()
	{
		$env = Prack_Mock_Request::envFor(
		  '/', array( 'method' => 'POST', 'input' => '_method=foo' ) );
		$middleware_app = Prack_MethodOverride::with( new Prack_MethodOverrideTest_ReqMaker() );
		$request        = $middleware_app->call( $env );
		$env            = $request->getEnv();
		$this->assertEquals( 'POST', $env[ 'REQUEST_METHOD' ] );
	} // It should not modify REQUEST_METHOD if the method is unknown
	
	/**
	 * It should not modify REQUEST_METHOD when _method is nil
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_modify_REQUEST_METHOD_when__method_is_nil()
	{
		$env = Prack_Mock_Request::envFor(
		  '/', array( 'method' => 'POST', 'input' => 'foo=bar' ) );
		$middleware_app = Prack_MethodOverride::with( new Prack_MethodOverrideTest_ReqMaker() );
		$request        = $middleware_app->call( $env );
		$env            = $request->getEnv();
		$this->assertEquals( 'POST', $env[ 'REQUEST_METHOD' ] );
	} // It should not modify REQUEST_METHOD when _method is nil
	
	/**
	 * It should store the original REQUEST_METHOD prior to overriding
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_store_the_original_REQUEST_METHOD_prior_to_overriding()
	{
		$env = Prack_Mock_Request::envFor(
		  '/', array( 'method' => 'POST', 'input' => '_method=options' ) );
		$middleware_app = Prack_MethodOverride::with( new Prack_MethodOverrideTest_ReqMaker() );
		$request        = $middleware_app->call( $env );
		$env            = $request->getEnv();
		$this->assertEquals( 'POST', $env[ 'rack.methodoverride.original_method' ] );
	} // It should store the original REQUEST_METHOD prior to overriding
}