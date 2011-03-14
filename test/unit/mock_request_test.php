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
		$mock_response = $mock_request->get( Prb::_String() );
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
		$this->assertTrue( $env instanceof Prb_Hash );
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
		$mock_response = $mock_request->request( Prb::_String( 'GET' ) );
		
		$env = unserialize( $mock_response->getBody()->raw() );
		
		$this->assertEquals( 'GET',         $env->get( 'REQUEST_METHOD'  )->raw() );
		$this->assertEquals( 'example.org', $env->get( 'SERVER_NAME'     )->raw() );
		$this->assertEquals( '80',          $env->get( 'SERVER_PORT'     )->raw() );
		$this->assertEquals( '',            $env->get( 'QUERY_STRING'    )->raw() );
		$this->assertEquals( '/',           $env->get( 'PATH_INFO'       )->raw() );
		$this->assertEquals( 'http',        $env->get( 'rack.url_scheme' )->raw() );
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
		  Prb::_String(),
		  Prb::_Hash( array( 'input' => Prb::_String( 'foo' ) ) )
		);
		$env = unserialize( $mock_response->getBody()->raw() );
		$this->assertEquals( 'GET', $env->get( 'REQUEST_METHOD' )->raw() );
		
		$mock_response = $mock_request->post(
		  Prb::_String(),
		  Prb::_Hash( array( 'input' => Prb::_String( 'foo' ) ) )
		);
		$env = unserialize( $mock_response->getBody()->raw() );
		$this->assertEquals( 'POST', $env->get( 'REQUEST_METHOD' )->raw() );
		
		$mock_response = $mock_request->put(
		  Prb::_String(),
		  Prb::_Hash( array( 'input' => Prb::_String( 'foo' ) ) )
		);
		$env = unserialize( $mock_response->getBody()->raw() );
		$this->assertEquals( 'PUT', $env->get( 'REQUEST_METHOD' )->raw() );
		
		$mock_response = $mock_request->delete(
			Prb::_String(),
			Prb::_Hash( array( 'input' => Prb::_String( 'foo' ) ) )
		);
		$env = unserialize( $mock_response->getBody()->raw() );
		$this->assertEquals( 'DELETE', $env->get( 'REQUEST_METHOD' )->raw() );
	} // It should allow GET/POST/PUT/DELETE
	
	/**
	 * It should set content length
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_set_content_length()
	{
		$env = Prack_Mock_Request::envFor(
			Prb::_String( '/' ), Prb::_Hash( array( 'input' => Prb::_string( 'foo' ) ) )
		);
		$this->assertEquals( "3", $env->get( 'CONTENT_LENGTH' )->raw() );
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
		  Prb::_String(),
		  Prb::_Hash( array( 'input' => Prb::_String( 'foo' ) ) )
		);
		$env = unserialize( $mock_response->getBody()->raw() );
		$this->assertEquals( 'foo', $env->get( 'mock.postdata' )->raw() );
		
		$mock_response = $mock_request->delete(
		  Prb::_String(),
		  Prb::_Hash( array( 'input' => Prb_IO::withString( Prb::_String( 'foo' ) ) ) )
		);
		$env = unserialize( $mock_response->getBody()->raw() );
		$this->assertEquals( 'foo', $env->get( 'mock.postdata' )->raw() );
	} // It should allow posting
	
	/**
	 * It should use all parts of an URL
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_use_all_parts_of_an_URL()
	{
		$mock_request  = self::app();
		
		$mock_response = $mock_request->get( Prb::_String( 'https://bla.example.org:9292/meh/foo?bar' ) );
		$this->assertTrue( $mock_response instanceof Prack_Mock_Response );
		
		$env = unserialize( $mock_response->getBody()->raw() );
		
		$this->assertEquals( 'GET',             $env->get( 'REQUEST_METHOD'  )->raw() );
		$this->assertEquals( 'bla.example.org', $env->get( 'SERVER_NAME'     )->raw() );
		$this->assertEquals( '9292',            $env->get( 'SERVER_PORT'     )->raw() );
		$this->assertEquals( 'bar',             $env->get( 'QUERY_STRING'    )->raw() );
		$this->assertEquals( '/meh/foo',        $env->get( 'PATH_INFO'       )->raw() );
		$this->assertEquals( 'https',           $env->get( 'rack.url_scheme' )->raw() );
	} // It should use all parts of an URL
	
	/**
	 * It should set SSL port and HTTPS flag on when using https
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_set_SSL_port_and_HTTPS_flag_on_when_using_https()
	{
		$mock_request  = self::app();
		
		$mock_response = $mock_request->get( Prb::_String( 'https://example.org/foo' ) );
		$this->assertTrue( $mock_response instanceof Prack_Mock_Response );
		
		$env = unserialize( $mock_response->getBody()->raw() );
		
		$this->assertEquals( 'GET',         $env->get( 'REQUEST_METHOD'  )->raw() );
		$this->assertEquals( 'example.org', $env->get( 'SERVER_NAME'     )->raw() );
		$this->assertEquals( '443',         $env->get( 'SERVER_PORT'     )->raw() );
		$this->assertEquals( '',            $env->get( 'QUERY_STRING'    )->raw() );
		$this->assertEquals( '/foo',        $env->get( 'PATH_INFO'       )->raw() );
		$this->assertEquals( 'https',       $env->get( 'rack.url_scheme' )->raw() );
		$this->assertEquals( 'on',          $env->get( 'HTTPS'           )->raw() );
	} // It should set SSL port and HTTPS flag on when using https
	
	/**
	 * It should prepend slash to uri path
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_prepend_slash_to_uri_path()
	{
		$mock_request  = self::app();
		$mock_response = $mock_request->get( Prb::_String( 'foo' ) );
		
		$this->assertTrue( $mock_response instanceof Prack_Mock_Response );
		
		$env = unserialize( $mock_response->getBody()->raw() );
		
		$this->assertEquals( 'GET',         $env->get( 'REQUEST_METHOD'  )->raw() );
		$this->assertEquals( 'example.org', $env->get( 'SERVER_NAME'     )->raw() );
		$this->assertEquals( '80',          $env->get( 'SERVER_PORT'     )->raw() );
		$this->assertEquals( '',            $env->get( 'QUERY_STRING'    )->raw() );
		$this->assertEquals( '/foo',        $env->get( 'PATH_INFO'       )->raw() );
		$this->assertEquals( 'http',        $env->get( 'rack.url_scheme' )->raw() );
	} // It should prepend slash to uri path
	
	/**
	 * It should properly convert method name to an uppercase string
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_properly_convert_method_name_to_an_uppercase_string()
	{
		$mock_request  = self::app();
		$mock_response = $mock_request->request( Prb::_String( 'GeT' ) );
		
		$env = unserialize( $mock_response->getBody()->raw() );
		
		$this->assertEquals( 'GET', $env->get( 'REQUEST_METHOD' )->raw() );
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
		  Prb::_String( '/foo?baz=2' ),
		  Prb::_Hash( array( 
		    'params' => Prb::_Hash( array(
		      'foo' => Prb::_Hash( array(
		        'bar' => Prb::_String( '1' )
		      ) )
		    ) )
		  ) )
		);
		
		$env = unserialize( $mock_response->getBody()->raw() );
		
		$this->assertEquals( 'GET',  $env->get( 'REQUEST_METHOD'  )->raw() );
		$this->assertEquals( '/foo', $env->get( 'PATH_INFO'       )->raw() );
		$this->assertEquals( '',     $env->get( 'mock.postdata'   )->raw() );
		$this->assertTrue( $env->get( 'QUERY_STRING' )->contains( Prb::_String( 'baz=2'      ) ) );
		$this->assertTrue( $env->get( 'QUERY_STRING' )->contains( Prb::_String( 'foo[bar]=1' ) ) );
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
		  Prb::_String( '/foo?baz=2' ),
		  Prb::_Hash( array(
		    'params' => Prb::_String( 'foo[bar]=1' )
		  ) )
		);
		
		$env = unserialize( $mock_response->getBody()->raw() );
		
		$this->assertEquals( 'GET',  $env->get( 'REQUEST_METHOD'  )->raw() );
		$this->assertEquals( '/foo', $env->get( 'PATH_INFO'       )->raw() );
		$this->assertEquals( '',     $env->get( 'mock.postdata'   )->raw() );
		$this->assertTrue( $env->get( 'QUERY_STRING' )->contains( Prb::_String( 'baz=2'      ) ) );
		$this->assertTrue( $env->get( 'QUERY_STRING' )->contains( Prb::_String( 'foo[bar]=1' ) ) );
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
		  Prb::_String( '/foo' ),
		  Prb::_Hash( array(
		    'params' => Prb::_Hash( array(
		      'foo' => Prb::_Hash( array(
		        'bar' => Prb::_String( '1' )
		      ) )
		    ) )
		  ) )
		);
		
		$env = unserialize( $mock_response->getBody()->raw() );
		
		$this->assertEquals( 'POST',                              $env->get( 'REQUEST_METHOD' )->raw() );
		$this->assertEquals( '',                                  $env->get( 'QUERY_STRING'   )->raw() );
		$this->assertEquals( '/foo',                              $env->get( 'PATH_INFO'      )->raw() );
		$this->assertEquals( 'application/x-www-form-urlencoded', $env->get( 'CONTENT_TYPE'   )->raw() );
		$this->assertEquals( 'foo[bar]=1',                        $env->get( 'mock.postdata'  )->raw() );
	} // It should accept params and build url encoded params for POST requests
	
	/**
	 * It should accept raw input in params for POST requests
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_accept_raw_input_in_params_for_POST_requests()
	{
		$mock_request  = self::app();
		$mock_response = $mock_request->post( Prb::_String( '/foo' ), Prb::_Hash( array( 'params' => Prb::_String( 'foo[bar]=1' ) ) ) );
		
		$env = unserialize( $mock_response->getBody()->raw() );
		
		$this->assertEquals( 'POST',                              $env->get( 'REQUEST_METHOD' )->raw() );
		$this->assertEquals( '',                                  $env->get( 'QUERY_STRING'   )->raw() );
		$this->assertEquals( '/foo',                              $env->get( 'PATH_INFO'      )->raw() );
		$this->assertEquals( 'application/x-www-form-urlencoded', $env->get( 'CONTENT_TYPE'   )->raw() );
		$this->assertEquals( 'foo[bar]=1',                        $env->get( 'mock.postdata'  )->raw() );
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
		  Prb::_String( 'https://bla.example.org:9292/meh/foo?bar' ),
		  Prb::_Hash( array( 'lint' => true ) )
		);
	} // It should behave valid according to the Rack spec
	
		/**
	 * It should throw an exception if uri is not a Prb_String
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_throw_an_exception_if_uri_is_not_a_Prb_String()
	{
		$this->setExpectedException( 'Prb_Exception_Type' );
		$mock_request  = self::app();
		$mock_response = $mock_request->get(
		  'https://bla.example.org:9292/meh/foo?bar',
		  Prb::_Hash( array( 'lint' => true ) )
		);
	} // It should throw an exception if uri is not a Prb_String
	
	/**
	 * It should throw an exception if options is not a Prb_Hash
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_throw_an_exception_if_options_is_not_a_Prb_Hash()
	{
		$this->setExpectedException( 'Prb_Exception_Type' );
		$mock_request  = self::app();
		$mock_response = $mock_request->get(
		  Prb::_String( 'https://bla.example.org:9292/meh/foo?bar' ),
		  array()
		);
	} // It should throw an exception if headers is not a Prb_Hash
	
		/**
	 * It should throw an exception if rack.input is neither Prb_Interface_Stringable nor Prb_Interface_ReadableStreamlike
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_throw_an_exception_if_rack_input_is_neither_Prb_Interface_Stringable_nor_Prb_Interface_ReadableStreamlike()
	{
		$this->setExpectedException( 'Prb_Exception_Type' );
		
		$mock_request  = new Prack_Mock_Request( new Prack_Test_EnvSerializer() );
		$mock_response = $mock_request->get(
			Prb::_String(),
			Prb::_Hash( array( 'input' => Prb::_Array() ) )
		);
	} // It should throw an exception if rack.input is neither Prb_Interface_Stringable nor Prb_Interface_ReadableStreamlike
}