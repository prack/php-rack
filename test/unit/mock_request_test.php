<?php

// TODO: Document!
class Prack_Mock_RequestTest extends PHPUnit_Framework_TestCase
{
	// TODO: Document!
	static function middlewareApp()
	{
		return Prack_Mock_Request::with( new Prack_Test_EnvSerializer() );
	}
	
	/**
	 * It should return a Prack_Mock_Response
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_a_Prack_Mock_Response()
	{
		$this->assertTrue( self::middlewareApp()->get() instanceof Prack_Mock_Response );
	} // It should return a Prack_Mock_Response
	
	/**
	 * It should be able to only return the environment
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_able_to_only_return_the_environment()
	{
		$env = Prack_Mock_Request::envFor();
		$this->assertTrue( is_array( $env ) );
		$this->assertNotNull( @$env[ 'rack.input' ] );
	} // It should be able to only return the environment
	
	/**
	 * It should provide sensible defaults
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_provide_sensible_defaults()
	{
		$response = self::middlewareApp()->request( 'GET' );
		
		$env = unserialize( $response->getBody() );
		$this->assertEquals( 'GET',         $env[ 'REQUEST_METHOD'  ] );
		$this->assertEquals( 'example.org', $env[ 'SERVER_NAME'     ] );
		$this->assertEquals( '80',          $env[ 'SERVER_PORT'     ] );
		$this->assertEquals( '',            $env[ 'QUERY_STRING'    ] );
		$this->assertEquals( '/',           $env[ 'PATH_INFO'       ] );
		$this->assertEquals( 'http',        $env[ 'rack.url_scheme' ] );
		
		$mock_postdata = $env[ 'mock.postdata' ];
		$this->assertTrue( empty( $mock_postdata ) );
	} // It should provide sensible defaults
	
	/**
	 * It should allow GET/POST/PUT/DELETE
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_allow_GET_POST_PUT_DELETE()
	{
		$response = self::middlewareApp()->get( '', array( 'input' => 'foo' ) );
		$env      = unserialize( $response->getBody() );
		$this->assertEquals( 'GET', $env[ 'REQUEST_METHOD' ] );
		
		$response = self::middlewareApp()->post( '', array( 'input' => 'foo' ) );
		$env      = unserialize( $response->getBody() );
		$this->assertEquals( 'POST', $env[ 'REQUEST_METHOD' ] );
		
		$response = self::middlewareApp()->put( '', array( 'input' => 'foo' ) );
		$env      = unserialize( $response->getBody() );
		$this->assertEquals( 'PUT', $env[ 'REQUEST_METHOD' ] );
		
		$response = self::middlewareApp()->delete( '', array( 'input' => 'foo' ) );
		$env      = unserialize( $response->getBody() );
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
		$this->assertEquals( '3', $env[ 'CONTENT_LENGTH' ] );
	} // It should set content length
	
	/**
	 * It should allow posting
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_allow_posting()
	{
		$response = self::middlewareApp()->delete( '/', array( 'input' => 'foo' ) );
		$env      = unserialize( $response->getBody() );
		$this->assertEquals( 'foo', $env[ 'mock.postdata' ] );
		
		$response = self::middlewareApp()->delete( '', array( 'input' => Prb_IO::withString( 'foo' ) ) );
		$env      = unserialize( $response->getBody() );
		$this->assertEquals( 'foo', $env[ 'mock.postdata' ] );
	} // It should allow posting
	
	/**
	 * It should use all parts of an URL
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_use_all_parts_of_an_URL()
	{
		$response = self::middlewareApp()->get( 'https://bla.example.org:9292/meh/foo?bar' );
		
		$this->assertTrue( $response instanceof Prack_Mock_Response );
		
		$env = unserialize( $response->getBody() );
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
		$response = self::middlewareApp()->get( 'https://example.org/foo' );
		
		$this->assertTrue( $response instanceof Prack_Mock_Response );
		
		$env = unserialize( $response->getBody() );
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
		$response = self::middlewareApp()->get( 'foo' );
		
		$this->assertTrue( $response instanceof Prack_Mock_Response );
		
		$env = unserialize( $response->getBody() );
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
		$response = self::middlewareApp()->request( 'GeT' );
		
		$env = unserialize( $response->getBody() );
		$this->assertEquals( 'GET', $env[ 'REQUEST_METHOD' ] );
	} // It should properly convert method name to an uppercase string
	
	/**
	 * It should accept params and build query string for GET requests
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_accept_params_and_build_query_string_for_GET_requests()
	{
		$response = self::middlewareApp()->get( '/foo?baz=2', array( 'params' => array( 'foo' => array( 'bar' => '1' ) ) ) );
		
		$env = unserialize( $response->getBody() );
		$this->assertEquals( 'GET',  $env[ 'REQUEST_METHOD' ] );
		$this->assertEquals( '/foo', $env[ 'PATH_INFO'      ] );
		$this->assertEquals( '',     $env[ 'mock.postdata'  ] );
		$this->assertTrue( is_integer( strpos( $env[ 'QUERY_STRING' ], 'baz=2'      ) ) );
		$this->assertTrue( is_integer( strpos( $env[ 'QUERY_STRING' ], 'foo[bar]=1' ) ) );
	} // It should accept params and build query string for GET requests
	
	/**
	 * It should accept raw input in params for GET requests
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_accept_raw_input_in_params_for_GET_requests()
	{
		$response = self::middlewareApp()->get( '/foo?baz=2', array( 'params' => 'foo[bar]=1' ) );
		
		$env = unserialize( $response->getBody() );
		$this->assertEquals( 'GET',  $env[ 'REQUEST_METHOD' ] );
		$this->assertEquals( '/foo', $env[ 'PATH_INFO'      ] );
		$this->assertEquals( '',     $env[ 'mock.postdata'  ] );
		$this->assertTrue( is_integer( strpos( $env[ 'QUERY_STRING' ], 'baz=2'      ) ) );
		$this->assertTrue( is_integer( strpos( $env[ 'QUERY_STRING' ], 'foo[bar]=1' ) ) );
	} // It should accept raw input in params for GET requests
	
	/**
	 * It should accept params and build url encoded params for POST requests
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_accept_params_and_build_url_encoded_params_for_POST_requests()
	{
		$response = self::middlewareApp()->post( '/foo', array( 'params' => array( 'foo' => array( 'bar' => '1' ) ) ) );
		
		$env = unserialize( $response->getBody() );
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
		$response = self::middlewareApp()->post( '/foo', array( 'params' => 'foo[bar]=1' ) );
		
		$env = unserialize( $response->getBody() );
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
		self::middlewareApp()->get( 'https://bla.example.org:9292/meh/foo?bar', array( 'lint' => true ) );
	} // It should behave valid according to the Rack spec
}