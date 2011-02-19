<?php

require_once join( DIRECTORY_SEPARATOR, array(dirname(__FILE__), '..', 'support', 'samplemiddleware.php') );

// TODO: Document!
class Prack_Mock_RequestTest extends PHPUnit_Framework_TestCase
{
	// TODO: Document!
	static function buildMockRequest()
	{
		$middleware = new SampleMiddleware();
		return new Prack_Mock_Request( $middleware );
	}
	
	/**
	 * default environment should be sane
	 * @author Joshua Morris
	 * @test
	 */
	public function default_environment_should_be_sane()
	{
		// Default environment for mock request.
		$env = Prack_Mock_Request::envFor();
		
		$this->assertArrayHasKey( 'REQUEST_METHOD' , $env );
		$this->assertArrayHasKey( 'SERVER_NAME'    , $env );
		$this->assertArrayHasKey( 'SERVER_PORT'    , $env );
		$this->assertArrayHasKey( 'QUERY_STRING'   , $env );
		$this->assertArrayHasKey( 'PATH_INFO'      , $env );
		$this->assertArrayHasKey( 'rack.url_scheme', $env );
		$this->assertArrayHasKey( 'HTTPS'          , $env );
		$this->assertArrayHasKey( 'SCRIPT_NAME'    , $env );
		$this->assertArrayHasKey( 'rack.errors'    , $env );
		$this->assertArrayHasKey( 'rack.input'     , $env );
	} // default environment should be sane
	
	/**
	 * environment should have good defaults
	 * @author Joshua Morris
	 * @test
	 */
	public function environment_should_have_good_defaults()
	{
		// Default environment for mock request.
		$env = Prack_Mock_Request::envFor();
		
		$this->assertEquals( 'GET'        , $env[ 'REQUEST_METHOD'  ] );
		$this->assertEquals( 'example.org', $env[ 'SERVER_NAME'     ] );
		$this->assertEquals( '80'         , $env[ 'SERVER_PORT'     ] );
		$this->assertEquals( ''           , $env[ 'QUERY_STRING'    ] );
		$this->assertEquals( '/'          , $env[ 'PATH_INFO'       ] );
		$this->assertEquals( 'http'       , $env[ 'rack.url_scheme' ] );
		$this->assertEquals( ''           , $env[ 'SCRIPT_NAME'     ] );
		$this->assertTrue( $env[ 'rack.errors' ] instanceof Prack_ErrorLogger );
	} // environment should have good defaults
	
	/**
	 * static method envFor should set rack.input to be params iff it provides a length on non-GET requests
	 * @author Joshua Morris
	 * @test
	 */
	public function static_method_envFor_should_set_rack_input_to_be_params_iff_it_provides_a_length_on_non_GET_requests()
	{
		$params  = Prack_Utils_IO::withString( '' );
		$options = array( 'params' => $params, 'method' => 'POST' );
		
		$env = Prack_Mock_Request::envFor( null, $options );
		$this->assertEquals( $params, $env[ 'rack.input' ] );
	} // static method envFor should set rack.input to be params iff it provides a length on non-GET requests
	
	/**
	 * static method envFor should throw an exception if the environment variable rack.input does not provide a length
	 * @author Joshua Morris
	 * @test
	 */
	public function static_method_envFor_should_throw_an_exception_if_the_environment_variable_rack_input_does_not_provide_a_length()
	{
		$this->setExpectedException( 'Prack_Error_Mock_Request_RackInputMustRespondToLength' );
		$bad_rack_input = new SampleMiddleware(); // lolwut
		Prack_Mock_Request::envFor( null, array( 'input' => $bad_rack_input ) );
	} // static method envFor should throw an exception if the environment variable rack.input does not provide a length
	
	/**
	 * static method envFor should throw an exception if configured to fail fast
	 * @author Joshua Morris
	 * @test
	 */
	public function static_method_envFor_should_throw_an_exception_if_configured_to_fail_fast()
	{
		$this->setExpectedException( 'Prack_Error_Mock_Request_FatalWarning' );
		
		// If the errors log is written to, fail immediately:
		$options = array( 'fatal' => true );
		$env     = Prack_Mock_Request::envFor( null, $options );
		
		$error_logger = $env[ 'rack.errors' ];
		$error_logger->write( 'EPIC FAIL' );
	} // static method envFor should throw an exception if configured to fail fast
		
	/**
	 * static method envFor should throw an exception if configured to fail fast (part deux)
	 * @author Joshua Morris
	 * @test
	 */
	public function static_method_envFor_should_throw_an_exception_if_configured_to_fail_fast_part_deux()
	{
		$this->setExpectedException( 'Prack_Error_Mock_Request_FatalWarning' );
		
		// If the errors log is written to, fail immediately:
		$options = array( 'fatal' => true );
		$env     = Prack_Mock_Request::envFor( null, $options );
		
		$error_logger = $env[ 'rack.errors' ];
		$error_logger->puts( 'EPIC FAIL' );
	} // static method envFor should throw an exception if configured to fail fast (part deux)
	
	/**
	 * code coverage hack for two no-op methods showing up as red
	 * @author Joshua Morris
	 * @test
	 */
	public function code_coverage_hack_for_two_no_op_methods_showing_up_as_red()
	{
		// If the errors log is written to, fail immediately:
		$options = array( 'fatal' => true );
		$env     = Prack_Mock_Request::envFor( null, $options );
		
		$error_logger = $env[ 'rack.errors' ];
		$error_logger->flush();  // The methods were there in Ruby... not sure why.
		$error_logger->string();
	} // code coverage hack for two no-op methods showing up as red
	
	/**
	 * new instance should own an enclosed middleware app
	 * @author Joshua Morris
	 * @test
	 */
	public function new_instance_should_own_an_enclosed_middleware_app()
	{
		$middleware   = new SampleMiddleware();
		$mock_request = new Prack_Mock_Request( $middleware );
		$this->assertSame( $middleware, $mock_request->getMiddlewareApp() );
	} // new instance should own an enclosed middleware app
	
	/**
	 * request-method-specific request generators should create requests with their respective request methods
	 * @author Joshua Morris
	 * @test
	 */
	public function request_method_specific_request_generators_should_create_their_respective_request_methods()
	{
		$mock_request    = self::buildMockRequest();
		$methods_to_test = array( 'get', 'post', 'put', 'delete' );
		
		foreach ( $methods_to_test as $method )
		{
			$request_uri = 'http://example.org/foo/bar?q1=a&q2=b#fragment';
			$response    = $mock_request->$method( $request_uri, array( 'params' => array( 'id' => 1 ) ) );
			$this->assertTrue( $response instanceof Prack_Mock_Response, $method );
		}
	} // request-method-specific request generators should create their respective request methods
}