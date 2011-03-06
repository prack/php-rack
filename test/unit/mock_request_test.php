<?php

// TODO: Document!
class Prack_Mock_RequestTest extends PHPUnit_Framework_TestCase
{
	// TODO: Document!
	static function app()
	{
		return new Prack_Mock_Request( new Prack_Test_EnvSerializer() );
	}
	
	/**
	 * It should return a Prack_Mock_Response
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_a_Prack_Mock_Response()
	{
		$mock_request  = self::app();
		$mock_response = $mock_request->get( Prack::_String() );
		$this->assertTrue( $mock_response instanceof Prack_Mock_Response );
	} // It should return a Prack_Mock_Response
	
	/**
	 * It should be able to only return the environment
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_able_to_only_return_the_environment()
	{
		$env = Prack_Mock_Request::envFor();
		$this->assertTrue( $env instanceof Prack_Wrapper_Hash );
		$this->assertTrue( $env->contains( 'rack.input' ) );
	} // It should be able to only return the environment
	
	/**
	 * It should provide sensible defaults
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_provide_sensible_defaults()
	{
		$mock_request  = self::app();
		$mock_response = $mock_request->request( Prack::_String( 'GET' ) );
		
		$env = unserialize( $mock_response->getBody()->toN() );
		
		$this->assertEquals( 'GET',         $env->get( 'REQUEST_METHOD'  )->toN() );
		$this->assertEquals( 'example.org', $env->get( 'SERVER_NAME'     )->toN() );
		$this->assertEquals( '80',          $env->get( 'SERVER_PORT'     )->toN() );
		$this->assertEquals( '',            $env->get( 'QUERY_STRING'    )->toN() );
		$this->assertEquals( '/',           $env->get( 'PATH_INFO'       )->toN() );
		$this->assertEquals( 'http',        $env->get( 'rack.url_scheme' )->toN() );
		$this->assertTrue( $env->get( 'mock.postdata' )->isEmpty() );
	} // It should provide sensible defaults
	
	/**
	 * It should allow GET/POST/PUT/DELETE
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_allow_GET_POST_PUT_DELETE()
	{
		$mock_request = self::app();
		
		$mock_response = $mock_request->get(
		  Prack::_String(),
		  Prack::_Hash( array( 'input' => Prack::_String( 'foo' ) ) )
		);
		$env = unserialize( $mock_response->getBody()->toN() );
		$this->assertEquals( 'GET', $env->get( 'REQUEST_METHOD' )->toN() );
		
		$mock_response = $mock_request->post(
		  Prack::_String(),
		  Prack::_Hash( array( 'input' => Prack::_String( 'foo' ) ) )
		);
		$env = unserialize( $mock_response->getBody()->toN() );
		$this->assertEquals( 'POST', $env->get( 'REQUEST_METHOD' )->toN() );
		
		$mock_response = $mock_request->put(
		  Prack::_String(),
		  Prack::_Hash( array( 'input' => Prack::_String( 'foo' ) ) )
		);
		$env = unserialize( $mock_response->getBody()->toN() );
		$this->assertEquals( 'PUT', $env->get( 'REQUEST_METHOD' )->toN() );
		
		$mock_response = $mock_request->delete(
			Prack::_String(),
			Prack::_Hash( array( 'input' => Prack::_String( 'foo' ) ) )
		);
		$env = unserialize( $mock_response->getBody()->toN() );
		$this->assertEquals( 'DELETE', $env->get( 'REQUEST_METHOD' )->toN() );
	} // It should allow GET/POST/PUT/DELETE
	
	/**
	 * It should set content length
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_set_content_length()
	{
		$env = Prack_Mock_Request::envFor(
			Prack::_String( '/' ), Prack::_Hash( array( 'input' => Prack::_string( 'foo' ) ) )
		);
		$this->assertEquals( "3", $env->get( 'CONTENT_LENGTH' )->toN() );
	} // It should set content length
	
	/**
	 * It should allow posting
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_allow_posting()
	{
		$mock_request = self::app();
		
		$mock_response = $mock_request->delete(
		  Prack::_String(),
		  Prack::_Hash( array( 'input' => Prack::_String( 'foo' ) ) )
		);
		$env = unserialize( $mock_response->getBody()->toN() );
		$this->assertEquals( 'foo', $env->get( 'mock.postdata' )->toN() );
		
		$mock_response = $mock_request->delete(
		  Prack::_String(),
		  Prack::_Hash( array( 'input' => Prack_Utils_IO::withString( Prack::_String( 'foo' ) ) ) )
		);
		$env = unserialize( $mock_response->getBody()->toN() );
		$this->assertEquals( 'foo', $env->get( 'mock.postdata' )->toN() );
	} // It should allow posting
	
	/**
	 * It should use all parts of an URL
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_use_all_parts_of_an_URL()
	{
		$mock_request  = self::app();
		
		$mock_response = $mock_request->get( Prack::_String( 'https://bla.example.org:9292/meh/foo?bar' ) );
		$this->assertTrue( $mock_response instanceof Prack_Mock_Response );
		
		$env = unserialize( $mock_response->getBody()->toN() );
		
		$this->assertEquals( 'GET',             $env->get( 'REQUEST_METHOD'  )->toN() );
		$this->assertEquals( 'bla.example.org', $env->get( 'SERVER_NAME'     )->toN() );
		$this->assertEquals( '9292',            $env->get( 'SERVER_PORT'     )->toN() );
		$this->assertEquals( 'bar',             $env->get( 'QUERY_STRING'    )->toN() );
		$this->assertEquals( '/meh/foo',        $env->get( 'PATH_INFO'       )->toN() );
		$this->assertEquals( 'https',           $env->get( 'rack.url_scheme' )->toN() );
	} // It should use all parts of an URL
	
	/**
	 * It should set SSL port and HTTPS flag on when using https
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_set_SSL_port_and_HTTPS_flag_on_when_using_https()
	{
		$mock_request  = self::app();
		
		$mock_response = $mock_request->get( Prack::_String( 'https://example.org/foo' ) );
		$this->assertTrue( $mock_response instanceof Prack_Mock_Response );
		
		$env = unserialize( $mock_response->getBody()->toN() );
		
		$this->assertEquals( 'GET',         $env->get( 'REQUEST_METHOD'  )->toN() );
		$this->assertEquals( 'example.org', $env->get( 'SERVER_NAME'     )->toN() );
		$this->assertEquals( '443',         $env->get( 'SERVER_PORT'     )->toN() );
		$this->assertEquals( '',            $env->get( 'QUERY_STRING'    )->toN() );
		$this->assertEquals( '/foo',        $env->get( 'PATH_INFO'       )->toN() );
		$this->assertEquals( 'https',       $env->get( 'rack.url_scheme' )->toN() );
		$this->assertEquals( 'on',          $env->get( 'HTTPS'           )->toN() );
	} // It should set SSL port and HTTPS flag on when using https
	
	/**
	 * It should prepend slash to uri path
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_prepend_slash_to_uri_path()
	{
		$mock_request  = self::app();
		$mock_response = $mock_request->get( Prack::_String( 'foo' ) );
		
		$this->assertTrue( $mock_response instanceof Prack_Mock_Response );
		
		$env = unserialize( $mock_response->getBody()->toN() );
		
		$this->assertEquals( 'GET',         $env->get( 'REQUEST_METHOD'  )->toN() );
		$this->assertEquals( 'example.org', $env->get( 'SERVER_NAME'     )->toN() );
		$this->assertEquals( '80',          $env->get( 'SERVER_PORT'     )->toN() );
		$this->assertEquals( '',            $env->get( 'QUERY_STRING'    )->toN() );
		$this->assertEquals( '/foo',        $env->get( 'PATH_INFO'       )->toN() );
		$this->assertEquals( 'http',        $env->get( 'rack.url_scheme' )->toN() );
	} // It should prepend slash to uri path
	
	/**
	 * It should properly convert method name to an uppercase string
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_properly_convert_method_name_to_an_uppercase_string()
	{
		$mock_request  = self::app();
		$mock_response = $mock_request->request( Prack::_String( 'GeT' ) );
		
		$env = unserialize( $mock_response->getBody()->toN() );
		
		$this->assertEquals( 'GET', $env->get( 'REQUEST_METHOD' )->toN() );
	} // It should properly convert method name to an uppercase string
	
	/**
	 * It should accept params and build query string for GET requests
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_accept_params_and_build_query_string_for_GET_requests()
	{
		$mock_request  = self::app();
		$mock_response = $mock_request->get(
		  Prack::_String( '/foo?baz=2' ),
		  Prack::_Hash( array( 
		    'params' => Prack::_Hash( array(
		      'foo' => Prack::_Hash( array(
		        'bar' => Prack::_String( '1' )
		      ) )
		    ) )
		  ) )
		);
		
		$env = unserialize( $mock_response->getBody()->toN() );
		
		$this->assertEquals( 'GET',  $env->get( 'REQUEST_METHOD'  )->toN() );
		$this->assertEquals( '/foo', $env->get( 'PATH_INFO'       )->toN() );
		$this->assertEquals( '',     $env->get( 'mock.postdata'   )->toN() );
		$this->assertTrue( $env->get( 'QUERY_STRING' )->contains( Prack::_String( 'baz=2'      ) ) );
		$this->assertTrue( $env->get( 'QUERY_STRING' )->contains( Prack::_String( 'foo[bar]=1' ) ) );
	} // It should accept params and build query string for GET requests
	
	/**
	 * It should accept raw input in params for GET requests
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_accept_raw_input_in_params_for_GET_requests()
	{
		$mock_request  = self::app();
		$mock_response = $mock_request->get(
		  Prack::_String( '/foo?baz=2' ),
		  Prack::_Hash( array(
		    'params' => Prack::_String( 'foo[bar]=1' )
		  ) )
		);
		
		$env = unserialize( $mock_response->getBody()->toN() );
		
		$this->assertEquals( 'GET',  $env->get( 'REQUEST_METHOD'  )->toN() );
		$this->assertEquals( '/foo', $env->get( 'PATH_INFO'       )->toN() );
		$this->assertEquals( '',     $env->get( 'mock.postdata'   )->toN() );
		$this->assertTrue( $env->get( 'QUERY_STRING' )->contains( Prack::_String( 'baz=2'      ) ) );
		$this->assertTrue( $env->get( 'QUERY_STRING' )->contains( Prack::_String( 'foo[bar]=1' ) ) );
	} // It should accept raw input in params for GET requests
	
	/**
	 * It should accept params and build url encoded params for POST requests
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_accept_params_and_build_url_encoded_params_for_POST_requests()
	{
		$mock_request  = self::app();
		$mock_response = $mock_request->post(
		  Prack::_String( '/foo' ),
		  Prack::_Hash( array(
		    'params' => Prack::_Hash( array(
		      'foo' => Prack::_Hash( array(
		        'bar' => Prack::_String( '1' )
		      ) )
		    ) )
		  ) )
		);
		
		$env = unserialize( $mock_response->getBody()->toN() );
		
		$this->assertEquals( 'POST',                              $env->get( 'REQUEST_METHOD' )->toN() );
		$this->assertEquals( '',                                  $env->get( 'QUERY_STRING'   )->toN() );
		$this->assertEquals( '/foo',                              $env->get( 'PATH_INFO'      )->toN() );
		$this->assertEquals( 'application/x-www-form-urlencoded', $env->get( 'CONTENT_TYPE'   )->toN() );
		$this->assertEquals( 'foo[bar]=1',                        $env->get( 'mock.postdata'  )->toN() );
	} // It should accept params and build url encoded params for POST requests
	
	/**
	 * It should accept raw input in params for POST requests
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_accept_raw_input_in_params_for_POST_requests()
	{
		$mock_request  = self::app();
		$mock_response = $mock_request->post( Prack::_String( '/foo' ), Prack::_Hash( array( 'params' => Prack::_String( 'foo[bar]=1' ) ) ) );
		
		$env = unserialize( $mock_response->getBody()->toN() );
		
		$this->assertEquals( 'POST',                              $env->get( 'REQUEST_METHOD' )->toN() );
		$this->assertEquals( '',                                  $env->get( 'QUERY_STRING'   )->toN() );
		$this->assertEquals( '/foo',                              $env->get( 'PATH_INFO'      )->toN() );
		$this->assertEquals( 'application/x-www-form-urlencoded', $env->get( 'CONTENT_TYPE'   )->toN() );
		$this->assertEquals( 'foo[bar]=1',                        $env->get( 'mock.postdata'  )->toN() );
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
		$mock_response = $mock_request->get(
		  Prack::_String( 'https://bla.example.org:9292/meh/foo?bar' ),
		  Prack::_Hash( array( 'lint' => true ) )
		);
	} // It should behave valid according to the Rack spec
	
		/**
	 * It should throw an exception if uri is not a Prack_Wrapper_String
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_throw_an_exception_if_uri_is_not_a_Prack_Wrapper_String()
	{
		$this->setExpectedException( 'Prack_Error_Type' );
		$mock_request  = self::app();
		$mock_response = $mock_request->get(
		  'https://bla.example.org:9292/meh/foo?bar',
		  Prack::_Hash( array( 'lint' => true ) )
		);
	} // It should throw an exception if uri is not a Prack_Wrapper_String
	
	/**
	 * It should throw an exception if options is not a Prack_Wrapper_Hash
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_throw_an_exception_if_options_is_not_a_Prack_Wrapper_Hash()
	{
		$this->setExpectedException( 'Prack_Error_Type' );
		$mock_request  = self::app();
		$mock_response = $mock_request->get(
		  Prack::_String( 'https://bla.example.org:9292/meh/foo?bar' ),
		  array()
		);
	} // It should throw an exception if headers is not a Prack_Wrapper_Hash
	
		/**
	 * It should throw an exception if rack.input is neither Prack_Interface_Stringable nor Prack_Interface_ReadableStreamlike
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_throw_an_exception_if_rack_input_is_neither_Prack_Interface_Stringable_nor_Prack_Interface_ReadableStreamlike()
	{
		$this->setExpectedException( 'Prack_Error_Type' );
		
		$mock_request  = new Prack_Mock_Request( new Prack_Test_EnvSerializer() );
		$mock_response = $mock_request->get(
			Prack::_String(),
			Prack::_Hash( array( 'input' => Prack::_Array() ) )
		);
	} // It should throw an exception if rack.input is neither Prack_Interface_Stringable nor Prack_Interface_ReadableStreamlike
}