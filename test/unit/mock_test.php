<?php

// TODO: Document!
class MockApp
  implements Prack_Interface_MiddlewareApp
{
	// TODO: Document!
	public function call( &$env )
	{
		$request = new Prack_Request( $env );
		
		$env[ 'mock.postdata' ] = $env[ 'rack.input' ]->read();
		
		$get_params = $request->GET();
		if ( isset( $get_params[ 'error' ] ) )
		{
			$errors = $env[ 'rack.errors' ];
			$errors->puts( $get_params[ 'error' ] );
			$errors->flush();
		}
		
		$status   = isset( $get_params[ 'status' ] ) ? $get_params[ 'status' ] : 200;
		$response = new Prack_Response( serialize( $env ), $status, array( 'Content-Type' => 'text/yaml' ) );
		
		return $response->toArray();
	}
}

// TODO: Document!
class Prack_Mock_RequestTest extends PHPUnit_Framework_TestCase
{
	// TODO: Document!
	static function app()
	{
		return new Prack_Mock_Request( new MockApp() );
	}
	
	/**
	 * It should return a Prack_Mock_Response
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_a_Prack_Mock_Response()
	{
		$mock_request  = self::app();
		$mock_response = $mock_request->get( '' );
		$this->assertTrue( $mock_response instanceof Prack_Mock_Response );
	} // It should return a Prack_Mock_Response
	
	/**
	 * It should be able to only return the environment
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_able_to_only_return_the_environment()
	{
		$env = Prack_Mock_Request::envFor( '' );
		$this->assertTrue( is_array( $env ) );
		$this->assertTrue( array_key_exists( 'rack.input', $env ) );
	} // It should be able to only return the environment
	
	/**
	 * It should provide sensible defaults
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_provide_sensible_defaults()
	{
		$mock_request  = self::app();
		$mock_response = $mock_request->request();
		
		$env = unserialize( $mock_response->getBody() );
		
		$this->assertEquals( 'GET',         $env[ 'REQUEST_METHOD'  ] );
		$this->assertEquals( 'example.org', $env[ 'SERVER_NAME'     ] );
		$this->assertEquals( '80',          $env[ 'SERVER_PORT'     ] );
		$this->assertEquals( '',            $env[ 'QUERY_STRING'    ] );
		$this->assertEquals( '/',           $env[ 'PATH_INFO'       ] );
		$this->assertEquals( 'http',        $env[ 'rack.url_scheme' ] );
		$this->assertTrue( strlen( $env[ 'mock.postdata' ] ) == 0 );
	} // It should provide sensible defaults
	
	/**
	 * It should allow GET/POST/PUT/DELETE
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_allow_GET_POST_PUT_DELETE()
	{
		$mock_request = self::app();
		
		$mock_response = $mock_request->get( '', array( 'input' => 'foo' ) );
		$env = unserialize( $mock_response->getBody() );
		$this->assertEquals( 'GET', $env[ 'REQUEST_METHOD' ] );
		
		$mock_response = $mock_request->post( '', array( 'input' => 'foo' ) );
		$env = unserialize( $mock_response->getBody() );
		$this->assertEquals( 'POST', $env[ 'REQUEST_METHOD' ] );
		
		$mock_response = $mock_request->put( '', array( 'input' => 'foo' ) );
		$env = unserialize( $mock_response->getBody() );
		$this->assertEquals( 'PUT', $env[ 'REQUEST_METHOD' ] );
		
		$mock_response = $mock_request->delete( '', array( 'input' => 'foo' ) );
		$env = unserialize( $mock_response->getBody() );
		$this->assertEquals( 'DELETE', $env[ 'REQUEST_METHOD' ] );
	} // It should allow GET/POST/PUT/DELETE
	
	/**
	 * It should set content length
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_set_content_length()
	{
		$env = Prack_Mock_Request::envFor( '/', array( 'input' => 'foo' ) );
		$this->assertEquals( "3", $env[ 'CONTENT_LENGTH' ] );
	} // It should set content length
	
	/**
	 * It should allow posting
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_allow_posting()
	{
		$mock_request = self::app();
		
		$mock_response = $mock_request->delete( '', array( 'input' => 'foo' ) );
		$env = unserialize( $mock_response->getBody() );
		$this->assertEquals( 'foo', $env[ 'mock.postdata' ] );
		
		$mock_response = $mock_request->delete( '', array( 'input' => Prack_Utils_IO::withString( 'foo' ) ) );
		$env = unserialize( $mock_response->getBody() );
		$this->assertEquals( 'foo', $env[ 'mock.postdata' ] );
	} // It should allow posting
	
	/**
	 * It should use all parts of an URL
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_use_all_parts_of_an_URL()
	{
		$mock_request  = self::app();
		
		$mock_response = $mock_request->get( 'https://bla.example.org:9292/meh/foo?bar' );
		$this->assertTrue( $mock_response instanceof Prack_Mock_Response );
		
		$env = unserialize( $mock_response->getBody() );
		
		$this->assertEquals( 'GET',             $env[ 'REQUEST_METHOD'  ] );
		$this->assertEquals( 'bla.example.org', $env[ 'SERVER_NAME'     ] );
		$this->assertEquals( '9292',            $env[ 'SERVER_PORT'     ] );
		$this->assertEquals( 'bar',             $env[ 'QUERY_STRING'    ] );
		$this->assertEquals( '/meh/foo',        $env[ 'PATH_INFO'       ] );
		$this->assertEquals( 'https',           $env[ 'rack.url_scheme' ] );
	} // It should use all parts of an URL
	
	/**
	 * It should set SSL port and HTTPS flag on when using https
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_set_SSL_port_and_HTTPS_flag_on_when_using_https()
	{
		$mock_request  = self::app();
		
		$mock_response = $mock_request->get( 'https://example.org/foo' );
		$this->assertTrue( $mock_response instanceof Prack_Mock_Response );
		
		$env = unserialize( $mock_response->getBody() );
		
		$this->assertEquals( 'GET',         $env[ 'REQUEST_METHOD'  ] );
		$this->assertEquals( 'example.org', $env[ 'SERVER_NAME'     ] );
		$this->assertEquals( '443',         $env[ 'SERVER_PORT'     ] );
		$this->assertEquals( '',            $env[ 'QUERY_STRING'    ] );
		$this->assertEquals( '/foo',        $env[ 'PATH_INFO'       ] );
		$this->assertEquals( 'https',       $env[ 'rack.url_scheme' ] );
		$this->assertEquals( 'on',          $env[ 'HTTPS'           ] );
	} // It should set SSL port and HTTPS flag on when using https
	
	/**
	 * It should prepend slash to uri path
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_prepend_slash_to_uri_path()
	{
		$mock_request  = self::app();
		
		$mock_response = $mock_request->get( 'foo' );
		$this->assertTrue( $mock_response instanceof Prack_Mock_Response );
		
		$env = unserialize( $mock_response->getBody() );
		
		$this->assertEquals( 'GET',         $env[ 'REQUEST_METHOD'  ] );
		$this->assertEquals( 'example.org', $env[ 'SERVER_NAME'     ] );
		$this->assertEquals( '80',          $env[ 'SERVER_PORT'     ] );
		$this->assertEquals( '',            $env[ 'QUERY_STRING'    ] );
		$this->assertEquals( '/foo',        $env[ 'PATH_INFO'       ] );
		$this->assertEquals( 'http',        $env[ 'rack.url_scheme' ] );
	} // It should prepend slash to uri path
	
	/**
	 * It should properly convert method name to an uppercase string
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_properly_convert_method_name_to_an_uppercase_string()
	{
		$mock_request  = self::app();
		$mock_response = $mock_request->request( 'GeT' );
		
		$env = unserialize( $mock_response->getBody() );
		$this->assertEquals( 'GET', $env[ 'REQUEST_METHOD' ] );
	} // It should properly convert method name to an uppercase string
	
	/**
	 * It should accept params and build query string for GET requests
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_accept_params_and_build_query_string_for_GET_requests()
	{
		$mock_request  = self::app();
		$mock_response = $mock_request->get( '/foo?baz=2', 
		                                     array( 'params' => array( 'foo' => array( 'bar' => '1' ) ) ) );
		
		$env = unserialize( $mock_response->getBody() );
		
		$this->assertEquals( 'GET',  $env[ 'REQUEST_METHOD'  ] );
		$this->assertEquals( '/foo', $env[ 'PATH_INFO'       ] );
		$this->assertEquals( '',     $env[ 'mock.postdata'   ] );
		$this->assertTrue( strpos( $env[ 'QUERY_STRING' ], 'baz=2'      ) !== false );
		$this->assertTrue( strpos( $env[ 'QUERY_STRING' ], 'foo[bar]=1' ) !== false );
	} // It should accept params and build query string for GET requests
	
	/**
	 * It should accept raw input in params for GET requests
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_accept_raw_input_in_params_for_GET_requests()
	{
		$mock_request  = self::app();
		$mock_response = $mock_request->get( '/foo?baz=2', array( 'params' => 'foo[bar]=1' ) );
		
		$env = unserialize( $mock_response->getBody() );
		
		$this->assertEquals( 'GET',  $env[ 'REQUEST_METHOD'  ] );
		$this->assertEquals( '/foo', $env[ 'PATH_INFO'       ] );
		$this->assertEquals( '',     $env[ 'mock.postdata'   ] );
		$this->assertTrue( strpos( $env[ 'QUERY_STRING' ], 'baz=2'      ) !== false );
		$this->assertTrue( strpos( $env[ 'QUERY_STRING' ], 'foo[bar]=1' ) !== false );
	} // It should accept raw input in params for GET requests
	
	/**
	 * It should accept params and build url encoded params for POST requests
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_accept_params_and_build_url_encoded_params_for_POST_requests()
	{
		$mock_request  = self::app();
		$mock_response = $mock_request->post( '/foo', 
		                                      array( 'params' => array( 'foo' => array( 'bar' => '1' ) ) ) );
		
		$env = unserialize( $mock_response->getBody() );
		
		$this->assertEquals( 'POST',                              $env[ 'REQUEST_METHOD' ] );
		$this->assertEquals( '',                                  $env[ 'QUERY_STRING'   ] );
		$this->assertEquals( '/foo',                              $env[ 'PATH_INFO'      ] );
		$this->assertEquals( 'application/x-www-form-urlencoded', $env[ 'CONTENT_TYPE'   ] );
		$this->assertEquals( 'foo[bar]=1',                        $env[ 'mock.postdata'  ] );
	} // It should accept params and build url encoded params for POST requests
	
	/**
	 * It should accept raw input in params for POST requests
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_accept_raw_input_in_params_for_POST_requests()
	{
		$mock_request  = self::app();
		$mock_response = $mock_request->post( '/foo', array( 'params' => 'foo[bar]=1' ) );
		
		$env = unserialize( $mock_response->getBody() );
		
		$this->assertEquals( 'POST',                              $env[ 'REQUEST_METHOD' ] );
		$this->assertEquals( '',                                  $env[ 'QUERY_STRING'   ] );
		$this->assertEquals( '/foo',                              $env[ 'PATH_INFO'      ] );
		$this->assertEquals( 'application/x-www-form-urlencoded', $env[ 'CONTENT_TYPE'   ] );
		$this->assertEquals( 'foo[bar]=1',                        $env[ 'mock.postdata'  ] );
	} // It should accept raw input in params for POST requests
	
	/**
	 * It should accept params and build multipart encoded params for POST requests
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_accept_params_and_build_multipart_encoded_params_for_POST_requests()
	{
		// FIXME: Implement multipart form data processing.
		/*
			files = Rack::Utils::Multipart::UploadedFile.new(File.join(File.dirname(__FILE__), "multipart", "file1.txt"))
			res = Rack::MockRequest.new(app).post("/foo", :params => { "submit-name" => "Larry", "files" => files })
			env = YAML.load(res.body)
			env["REQUEST_METHOD"].should.equal "POST"
			env["QUERY_STRING"].should.equal ""
			env["PATH_INFO"].should.equal "/foo"
			env["CONTENT_TYPE"].should.equal "multipart/form-data; boundary=AaB03x"
			env["mock.postdata"].length.should.equal 206
		*/
		$this->markTestSkipped( 'pending multipart implementation' );
	} // It should accept params and build multipart encoded params for POST requests
	
	/**
	 * It should behave valid according to the Rack spec
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_behave_valid_according_to_the_Rack_spec()
	{
		$mock_request  = self::app();
		$mock_response = $mock_request->get( 'https://bla.example.org:9292/meh/foo?bar', 
		                                     array( 'lint' => true ) );
	} // It should behave valid according to the Rack spec
}

# Rack::MockResponse provides useful helpers for testing your apps.
# Usually, you don't create the MockResponse on your own, but use
# MockRequest.
class Prack_Mock_ResponseTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * It should provide access to the HTTP status
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_provide_access_to_the_HTTP_status()
	{
		$mock_request  = new Prack_Mock_Request( new MockApp() );
		$mock_response = $mock_request->get( '' );
		$this->assertTrue( $mock_response->isSuccessful() );
		$this->assertTrue( $mock_response->isOK() );
		
		$mock_response = $mock_request->get( '/?status=50' );
		$this->assertTrue( $mock_response->isInvalid() );
		
		$mock_response = $mock_request->get( '/?status=100' );
		$this->assertTrue( $mock_response->isInformational() );
		
		$mock_response = $mock_request->get( '/?status=204' );
		$this->assertTrue( $mock_response->isEmpty() );
		
		$mock_response = $mock_request->get( '/?status=403' );
		$this->assertTrue( $mock_response->isForbidden() );
		
		$mock_response = $mock_request->get( '/?status=404' );
		$this->assertFalse( $mock_response->isSuccessful() );
		$this->assertTrue( $mock_response->isClientError() );
		$this->assertTrue( $mock_response->isNotFound() );
		
		$mock_response = $mock_request->get( '/?status=501' );
		$this->assertFalse( $mock_response->isSuccessful() );
		$this->assertTrue( $mock_response->isServerError() );
		
		$mock_response = $mock_request->get( '/?status=307' );
		$this->assertTrue( $mock_response->isRedirect() );
		$this->assertTrue( $mock_response->isRedirection() );
		
		$mock_response = $mock_request->get( '/?status=201', array( 'lint' => true ) );
		$this->assertTrue( $mock_response->isEmpty() );
	} // It should provide access to the HTTP status
	
	/**
	 * It should provide access to the HTTP headers
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_provide_access_to_the_HTTP_headers()
	{
		$mock_request  = new Prack_Mock_Request( new MockApp() );
		$mock_response = $mock_request->get( '' );
		
		$original_headers = $mock_response->getOriginalHeaders();

		$this->assertTrue( $mock_response->contains( 'Content-Type' ) );
		$this->assertEquals( 'text/yaml', $mock_response->getHeaders()->get( 'Content-Type' ) );
		$this->assertEquals( 'text/yaml', $original_headers[ 'Content-Type' ] );
		$this->assertEquals( 'text/yaml', $mock_response->get( 'Content-Type' ) );
		$this->assertEquals( 'text/yaml', $mock_response->contentType() );
		$this->assertGreaterThanOrEqual( 0, $mock_response->contentLength() );
		$this->assertNull( $mock_response->location() );
	} // It should provide access to the HTTP headers
	
	/**
	 * It should provide access to the HTTP body
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_provide_access_to_the_HTTP_body()
	{
		$mock_request  = new Prack_Mock_Request( new MockApp() );
		$mock_response = $mock_request->get( '' );
		
		$this->assertRegExp( '/rack/', $mock_response->getBody() );
		$this->assertGreaterThanOrEqual( 1, $mock_response->matches( '/rack/' ) );
	} // It should provide access to the HTTP body
	
	/**
	 * It should provide access to the Rack errors
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_provide_access_to_the_Rack_errors()
	{
		$mock_request  = new Prack_Mock_Request( new MockApp() );
		$mock_response = $mock_request->get( '/?error=foo', array( 'lint' => true ) );
		$this->assertTrue( $mock_response->isOK() );
		
		$result = $mock_response->getErrors();
		
		$this->assertFalse( $result->isEmpty() );
		$this->assertTrue( $result->contains( 'foo' ) );
	} // It should provide access to the Rack errors
	
	/**
	 * It should optionally make Rack errors fatal
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_optionally_make_Rack_errors_fatal()
	{
		$mock_request  = new Prack_Mock_Request( new MockApp() );
		$mock_response = $mock_request->get( '', array( 'fatal' => true ) );
		
		$env = unserialize( $mock_response->getBody() );
		
		// Cheating for coverage:
		$mock_response->setErrors( $env[ 'rack.errors' ] );
		$env[ 'rack.errors' ]->flush();
		$this->assertEquals( '', (string)$env[ 'rack.errors' ]->string() );
		
		$this->setExpectedException( 'Prack_Error_Mock_Request_FatalWarning' );
		$env[ 'rack.errors' ]->write( 'Error 2' );
	} // It should optionally make Rack errors fatal
	
	/**
	 * It should optionally make Rack errors fatal (part 2)
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_optionally_make_Rack_errors_fatal__part_2_()
	{
		$mock_request  = new Prack_Mock_Request( new MockApp() );
		$mock_response = $mock_request->get( '', array( 'fatal' => true ) );
		
		$env = unserialize( $mock_response->getBody() );
		
		$this->setExpectedException( 'Prack_Error_Mock_Request_FatalWarning' );
		$env[ 'rack.errors' ]->puts( 'Error 2' );
	} // It should optionally make Rack errors fatal (part 2)
	
	/**
	 * It should throw an exception when an unknown method is called, on account of delegation
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_throw_an_exception_when_an_unknown_method_is_called__on_account_of_delegation()
	{
		$this->setExpectedException( 'Prack_Error_Runtime_DelegationFailed' );
		$mock_request  = new Prack_Mock_Request( new MockApp() );
		$mock_response = $mock_request->get( '/?error=foo', array( 'lint' => true ) );
		$mock_response->foobar();
	} // It should throw an exception when an unknown method is called, on account of delegation
	
	/**
	 * It should throw an exception if body is neither a string or Prack_Interface_Enumerable
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_throw_an_exception_if_body_is_neither_a_string_or_Prack_Interface_Enumerable()
	{
		$this->setExpectedException( 'Prack_Error_Type' );
		new Prack_Mock_Response( 200, array(), 3 /* This causes bomb */ );
	} // It should throw an exception if body is neither a string or Prack_Interface_Enumerable
	
}