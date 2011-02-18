<?php

// TODO: Document!
class MyRequest extends Prack_Request
{
	public function params()
	{
		return array( 'foo' => 'bar');
	}
}

// TODO: Document!
class Prack_RequestTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * It should wrap the rack variables
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_wrap_the_rack_variables()
	{
		$env     = Prack_Mock_Request::envFor( 'http://example.com:8080/' );
		$request = new Prack_Request( $env );
		
		$this->assertTrue( method_exists( $request->body(), 'gets' ) );
		$this->assertEquals( 'http', $request->scheme() );
		$this->assertEquals( 'GET', $request->requestMethod() );
		
		$this->assertTrue( $request->isGet() );
		$this->assertFalse( $request->isPost() );
		$this->assertFalse( $request->isPut() );
		$this->assertFalse( $request->isDelete() );
		$this->assertFalse( $request->isHead() );
		$this->assertFalse( $request->isTrace() );
		$this->assertFalse( $request->isOptions() );
		
		$this->assertEquals( '', $request->scriptName() );
		$this->assertEquals( '/', $request->pathInfo() );
		$this->assertEquals( '', $request->queryString() );
		
		$this->assertEquals( 'example.com', $request->host() );
		$this->assertEquals( 8080, $request->port() );
		
		$this->assertEquals( '0', $request->contentLength() );
		$this->assertNull( $request->contentType() );
		
		$this->assertEquals( null, $request->logger() );
		$this->assertEquals( array(), $request->session() );
		$this->assertEquals( array(), $request->sessionOptions() );
	} // It should wrap the rack variables
	
	/**
	 * It should figure out the correct host
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_figure_out_the_correct_host()
	{
		$request = new Prack_Request( Prack_Mock_Request::envFor( '/', array( 'HTTP_HOST' => 'www2.example.org' ) ) );
		$this->assertEquals( 'www2.example.org', $request->host() );
		
		$request = new Prack_Request( Prack_Mock_Request::envFor( '/', array( 'SERVER_NAME' => 'example.org', 'SERVER_PORT' => '9292' ) ) );
		$this->assertEquals( 'example.org', $request->host() );
		
		$request = new Prack_Request( Prack_Mock_Request::envFor( '/', array( 'HTTP_HOST' => 'localhost:81', 'HTTP_X_FORWARDED_HOST' => 'example.org:9292' ) ) );
		$this->assertEquals( 'example.org', $request->host() );
		
		$env = Prack_Mock_Request::envFor( '/', array( 'SERVER_ADDR' => '192.168.1.1', 'SERVER_PORT' => '9292' ) );
		unset( $env[ 'SERVER_NAME' ] );
		$request = new Prack_Request( $env );
		$this->assertEquals( '192.168.1.1', $request->host() );
		
		$env = Prack_Mock_Request::envFor( '/' );
		unset( $env[ 'SERVER_NAME' ] );
		
		$request = new Prack_Request( $env );
		$this->assertEquals( '', $request->host() );
	} // It should figure out the correct host
	
	/**
	 * It should parse the query string
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_parse_the_query_string()
	{
		$request = new Prack_Request( Prack_Mock_Request::envFor( '/?foo=bar&quux=bla' ) );
		$this->assertEquals( 'foo=bar&quux=bla', $request->queryString() );
		$this->assertEquals( array( 'foo' => 'bar', 'quux' => 'bla' ), $request->GET() );
		$this->assertTrue( count( $request->POST() ) == 0 );
		$this->assertEquals( array( 'foo' => 'bar', 'quux' => 'bla' ), $request->params() );
	} // It should parse the query string
	
	/**
	 * It should throw an exception if rack.input is missing
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_throw_an_exception_if_rack_input_is_missing()
	{
		$request = new Prack_Request( array() );
		$this->setExpectedException( 'Prack_Error_Runtime_RackInputMissing' );
		$request->POST();
	} // It should throw an exception if rack.input is missing
	
	/**
	 * It should parse POST data when method is POST and no Content-Type given
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_parse_POST_data_when_method_is_POST_and_no_Content_Type_given()
	{
		$request = new Prack_Request(
			Prack_Mock_Request::envFor( '/?foo=quux', array(
				'REQUEST_METHOD' => 'POST',
				'input'          => 'foo=bar&quux=bla'
			) )
		);
		
		$this->assertNull( $request->contentType() );
		$this->assertNull( $request->mediaType() );
		$this->assertEquals( 'foo=quux', $request->queryString() );
		$this->assertEquals( array( 'foo' => 'quux' ), $request->GET() );
		$this->assertEquals( array( 'foo' => 'bar', 'quux' => 'bla' ), $request->POST() );
		$this->assertEquals( array( 'foo' => 'bar', 'quux' => 'bla' ), $request->params() );
	} // It should parse POST data when method is POST and no Content-Type given
	
	/**
	 * It should parse POST data with explicit content type regardless of method
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_parse_POST_data_with_explicit_content_type_regardless_of_method()
	{
		$request = new Prack_Request(
			Prack_Mock_Request::envFor( '/', array(
				'CONTENT_TYPE' => 'application/x-www-form-urlencoded;foo=bar',
				'input'        => 'foo=bar&quux=bla'
			) )
		);
		
		$this->assertEquals( 'application/x-www-form-urlencoded;foo=bar', $request->contentType() );
		$this->assertEquals( 'application/x-www-form-urlencoded', $request->mediaType() );
		$media_type_params = $request->mediaTypeParams();
		$this->assertEquals( 'bar', $media_type_params[ 'foo' ] );
		$this->assertEquals( array( 'foo' => 'bar', 'quux' => 'bla' ), $request->POST() );
		$this->assertEquals( array( 'foo' => 'bar', 'quux' => 'bla' ), $request->params() );
	} // It should parse POST data with explicit content type regardless of method
	
	/**
	 * It should not parse POST data when media type is not form-data
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_parse_POST_data_when_media_type_is_not_form_data()
	{
		$request = new Prack_Request(
			Prack_Mock_Request::envFor( '/?foo=quux', array(
				'REQUEST_METHOD' => 'POST',
				'CONTENT_TYPE'   => 'text/plain;charset=utf-8',
				'input'          => 'foo=bar&quux=bla'
			) )
		);
		
		$this->assertEquals( 'text/plain;charset=utf-8', $request->contentType() );
		$this->assertEquals( 'utf-8', $request->contentCharset() );
		$this->assertEquals( 'text/plain', $request->mediaType() );
		$media_type_params = $request->mediaTypeParams();
		$this->assertEquals( 'utf-8', $media_type_params[ 'charset' ] );
		$this->assertTrue( count( $request->POST() ) == 0 );
		$this->assertEquals( array( 'foo' => 'quux' ), $request->params() );
		$this->assertEquals( 'foo=bar&quux=bla', $request->body()->read() );
	} // It should not parse POST data when media type is not form-data
	
	/**
	 * It should parse POST data on PUT when media type is form-data
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_parse_POST_data_on_PUT_when_media_type_is_form_data()
	{
		$request = new Prack_Request(
			Prack_Mock_Request::envFor( '/?foo=quux', array(
				'REQUEST_METHOD' => 'PUT',
				'CONTENT_TYPE'   => 'application/x-www-form-urlencoded',
				'input'          => 'foo=bar&quux=bla'
			) )
		);
		
		$this->assertEquals( array( 'foo' => 'bar', 'quux' => 'bla' ), $request->POST() );
		$this->assertEquals( 'foo=bar&quux=bla', $request->body()->read() );
	} // It should parse POST data on PUT when media type is form-data
	
	/**
	 * It should rewind input after parsing POST data
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_rewind_input_after_parsing_POST_data()
	{
		// Create a rewindable stream:
		$stream = fopen( 'php://memory', 'x+b' );
		fputs( $stream, 'foo=bar&quux=bla' );
		rewind( $stream );
		$rewindable_input = new Prack_RewindableInput( $stream );
		
		$request = new Prack_Request(
			Prack_Mock_Request::envFor( '/', array(
				'CONTENT_TYPE'   => 'application/x-www-form-urlencoded;foo=bar',
				'input'          => $rewindable_input
			) )
		);
		
		$this->assertEquals( array( 'foo' => 'bar', 'quux' => 'bla' ), $request->params() );
		$this->assertEquals( 'foo=bar&quux=bla', $rewindable_input->read() );
		
		fclose( $stream );
	} // It should rewind input after parsing POST data
	
	/**
	 * It should clean up Safari's ajax POST body
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_clean_up_Safari_s_ajax_POST_body()
	{
		$request = new Prack_Request(
			Prack_Mock_Request::envFor( '/', array(
				'REQUEST_METHOD' => 'POST',
				'input'          => "foo=bar&quux=bla\0"
			) )
		);
		$this->assertEquals( array( 'foo' => 'bar', 'quux' => 'bla' ), $request->POST() );
	} // It should clean up Safari's ajax POST body
	
	/**
	 * It should get value by key from params with getParam
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_get_value_by_key_from_params_with_getParam()
	{
		$request = new Prack_Request( Prack_Mock_Request::envFor( '?foo=quux' ) );
		$this->assertEquals( 'quux', $request->getParam( 'foo' ) );
	} // It should get value by key from params with getParam
	
	/**
	 * It should set value to key on params with setParam
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_set_value_to_key_on_params_with_setParam()
	{
		$request = new Prack_Request( Prack_Mock_Request::envFor( '?foo=duh' ) );
		
		$this->assertEquals( 'duh', $request->getParam( 'foo' ) );
		$this->assertEquals( array( 'foo' => 'duh' ), $request->params() );
		
		$request->setParam( 'foo', 'bar' );
		$this->assertEquals( array( 'foo' => 'bar' ), $request->params() );
		$this->assertEquals( 'bar', $request->getParam( 'foo' ) );
	} // It should set value to key on params with setParam
	
	/**
	 * It should return values for the keys in the order given from valuesAt
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_values_for_the_keys_in_the_order_given_from_valuesAt()
	{
		$request = new Prack_Request( Prack_Mock_Request::envFor( '?foo=baz&wun=der&bar=ful' ) );
		$this->assertEquals( array( 'baz' ), $request->valuesAt( 'foo' ) );
		$this->assertEquals( array( 'baz', 'der' ), $request->valuesAt( 'foo', 'wun' ) );
		$this->assertEquals( array( 'ful', 'baz', 'der' ), $request->valuesAt( 'bar', 'foo', 'wun') );
	} // It should return values for the keys in the order given from valuesAt
	
	/**
	 * It should extract referrer correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_extract_referrer_correctly()
	{
		$request = new Prack_Request(
			Prack_Mock_Request::envFor( '/', array( 'HTTP_REFERER' => '/some/path' ) )
		);
		$this->assertEquals( '/some/path', $request->referer() );
		
		$request = new Prack_Request( Prack_Mock_Request::envFor( '/' ) );
		$this->assertEquals( '/', $request->referer() );
	} // It should extract referrer correctly
	
	/**
	 * It should alias referer to referrer
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_alias_referer_to_referrer()
	{
		$request = new Prack_Request(
			Prack_Mock_Request::envFor( '/', array( 'HTTP_REFERER' => '/some/path' ) )
		);
		$this->assertEquals( '/some/path', $request->referrer() );
		
		$request = new Prack_Request( Prack_Mock_Request::envFor( '/' ) );
		$this->assertEquals( '/', $request->referrer() );
	} // It should alias referer to referrer
	
	/**
	 * It should extract user agent correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_extract_user_agent_correctly()
	{
		$request = new Prack_Request(
			Prack_Mock_Request::envFor( '/', array( 'HTTP_USER_AGENT' => 'Mozilla/4.0 (compatible)' ) )
		);
		$this->assertEquals( 'Mozilla/4.0 (compatible)', $request->userAgent() );
		
		$request = new Prack_Request( Prack_Mock_Request::envFor( '/' ) );
		$this->assertNull( $request->userAgent() );
	} // It should extract user agent correctly
	
	/**
	 * It should cache, but invalidates the cache
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_cache__but_invalidates_the_cache()
	{
		$request = new Prack_Request(
			Prack_Mock_Request::envFor( '/?foo=quux', array(
				'CONTENT_TYPE'   => 'application/x-www-form-urlencoded',
				'input'          => 'foo=bar&quux=bla'
			) )
		);
		
		$this->assertEquals( array( 'foo' => 'quux' ), $request->GET() );
		$this->assertEquals( array( 'foo' => 'quux' ), $request->GET() );
		$env = &$request->getEnv();
		$env[ 'QUERY_STRING' ] = 'bla=foo';
		$this->assertEquals( array( 'bla' => 'foo' ), $request->GET() );
		$this->assertEquals( array( 'bla' => 'foo' ), $request->GET() );
		
		// Create a rewindable stream:
		$stream = fopen( 'php://memory', 'x+b' );
		fputs( $stream, 'foo=bla&quux=bar' );
		rewind( $stream );
		$rewindable_input = new Prack_RewindableInput( $stream );
		
		$this->assertEquals( array( 'foo' => 'bar', 'quux' => 'bla' ), $request->POST() );
		$this->assertEquals( array( 'foo' => 'bar', 'quux' => 'bla' ), $request->POST() );
		$env = &$request->getEnv();
		$env[ 'rack.input' ] = $rewindable_input;
		$this->assertEquals( array( 'foo' => 'bla', 'quux' => 'bar' ), $request->POST() );
		$this->assertEquals( array( 'foo' => 'bla', 'quux' => 'bar' ), $request->POST() );
		
		fclose( $stream );
	} // It should cache, but invalidates the cache
	
	/**
	 * It should figure out if called via XHR
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_figure_out_if_called_via_XHR()
	{
		$request = new Prack_Request( Prack_Mock_Request::envFor( '' ) );
		$this->assertFalse( $request->isXhr() );
		
		$request = new Prack_Request(
			Prack_Mock_Request::envFor( '', array( 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest' ) )
		);
		$this->assertTrue( $request->isXhr() );
	} // It should figure out if called via XHR
		
	/**
	 * It should parse cookies
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_parse_cookies()
	{
		$request = new Prack_Request(
			Prack_Mock_Request::envFor( '', array( 'HTTP_COOKIE' => 'foo=bar;quux=h&m' ) )
		);
		
		$this->assertEquals( array( 'foo' => 'bar', 'quux' => 'h&m' ), $request->cookies() );
		$this->assertEquals( array( 'foo' => 'bar', 'quux' => 'h&m' ), $request->cookies() );
		$env = &$request->getEnv();
		unset( $env[ 'HTTP_COOKIE' ] );
		$this->assertEquals( array(), $request->cookies() );
	} // It should parse cookies
	
	/**
	 * It should parse cookies according to RFC 2109
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_parse_cookies_according_to_RFC_2109()
	{
		$request = new Prack_Request(
			Prack_Mock_Request::envFor( '', array( 'HTTP_COOKIE' => 'foo=bar;foo=car' ) )
		);
		// FIXME: aaand http_parse_cookie() is broken. COOL.
		//$this->assertEquals( array( 'foo' => 'bar' ), $request->cookies() );
	} // It should parse cookies according to RFC 2109
	
	/**
	 * It should provide setters
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_provide_setters()
	{
		$request = new Prack_Request( Prack_Mock_Request::envFor( '' ) );
		
		$this->assertEquals( '', $request->scriptName() );
		$request->setScriptName( '/foo' );
		$this->assertEquals( '/foo', $request->scriptName() );
		$env = &$request->getEnv();
		$this->assertEquals( '/foo', $env[ 'SCRIPT_NAME' ] );
		
		$this->assertEquals( '/', $request->pathInfo() );
		$request->setPathInfo( '/foo' );
		$this->assertEquals( '/foo', $request->pathInfo() );
		$env = &$request->getEnv();
		$this->assertEquals( '/foo', $env[ 'PATH_INFO' ] );
	} // It should provide setters
	
	/**
	 * It should provide the original env
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_provide_the_original_env()
	{
		$request = new Prack_Request( $env = Prack_Mock_Request::envFor( '' ) );
		$this->assertEquals( $env, $request->getEnv() );
	} // It should provide the original env
	
	/**
	 * It should restore the URL
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_restore_the_URL()
	{
		$request = new Prack_Request( Prack_Mock_Request::envFor( '' ) );
		$this->assertEquals( 'http://example.org/', $request->url() );
		$request = new Prack_Request( Prack_Mock_Request::envFor( '', array( 'SCRIPT_NAME' => '/foo' ) ) );
		$this->assertEquals( 'http://example.org/foo/', $request->url() );
		$request = new Prack_Request( Prack_Mock_Request::envFor( '/foo' ) );
		$this->assertEquals( 'http://example.org/foo', $request->url() );
		$request = new Prack_Request( Prack_Mock_Request::envFor( '?foo' ) );
		$this->assertEquals( 'http://example.org/?foo', $request->url() );
		$request = new Prack_Request( Prack_Mock_Request::envFor( 'http://example.org:8080/' ) );
		$this->assertEquals( 'http://example.org:8080/', $request->url() );
		$request = new Prack_Request( Prack_Mock_Request::envFor( 'https://example.org/' ) );
		$this->assertEquals( 'https://example.org/', $request->url() );
		
		$request = new Prack_Request( Prack_Mock_Request::envFor( 'https://example.org:8080/foo?foo' ) );
		$this->assertEquals( 'https://example.org:8080/foo?foo', $request->url() );
	} // It should restore the URL
	
	/**
	 * It should restore the full path
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_restore_the_full_path()
	{
		$request = new Prack_Request( Prack_Mock_Request::envFor( '' ) );
		$this->assertEquals( '/', $request->fullpath() );
		$request = new Prack_Request( Prack_Mock_Request::envFor( '', array( 'SCRIPT_NAME' => '/foo' ) ) );
		$this->assertEquals( '/foo/', $request->fullpath() );
		$request = new Prack_Request( Prack_Mock_Request::envFor( '/foo' ) );
		$this->assertEquals( '/foo', $request->fullpath() );
		$request = new Prack_Request( Prack_Mock_Request::envFor( '?foo' ) );
		$this->assertEquals( '/?foo', $request->fullpath() );
		$request = new Prack_Request( Prack_Mock_Request::envFor( 'http://example.org:8080/' ) );
		$this->assertEquals( '/', $request->fullpath() );
		$request = new Prack_Request( Prack_Mock_Request::envFor( 'https://example.org/' ) );
		$this->assertEquals( '/', $request->fullpath() );
		
		$request = new Prack_Request( Prack_Mock_Request::envFor( 'https://example.org:8080/foo?foo' ) );
		$this->assertEquals( '/foo?foo', $request->fullpath() );
	} // It should restore the full path
	
	/**
	 * It should handle multiple media type parameters
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_multiple_media_type_parameters()
	{
		$request = new Prack_Request(
			Prack_Mock_Request::envFor( '/', array( 'CONTENT_TYPE' => 'text/plain; foo=BAR,baz=bizzle dizzle;BLING=bam' ) )
		);
		$media_type_params = $request->mediaTypeParams();
		
		$this->assertFalse( $request->isFormData() );
		$this->assertArrayHasKey( 'foo', $media_type_params );
		$this->assertEquals( 'BAR', $media_type_params[ 'foo' ] );
		$this->assertArrayHasKey( 'baz', $media_type_params );
		$this->assertEquals( 'bizzle dizzle', $media_type_params[ 'baz' ] );
		$this->assertArrayNotHasKey( 'BLING', $media_type_params );
		$this->assertArrayHasKey( 'bling', $media_type_params );
		$this->assertEquals( 'bam', $media_type_params[ 'bling' ] );
	} // It should handle multiple media type parameters
	
	// should parse multipart form data
	
	/**
	 * It should parse Accept-Encoding correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_parse_Accept_Encoding_correctly()
	{
		$this->assertEquals( array(), $this->parser( null ) );
		
		$this->assertEquals( array( array( 'compress', 1.0 ), array( 'gzip', 1.0 ) ), 
		                     $this->parser( 'compress, gzip' ) );
		$this->assertEquals( array(), $this->parser( '' ) );
		$this->assertEquals( array( array( '*', 1.0 ) ), 
		                     $this->parser( '*' ) );
		$this->assertEquals( array( array( 'compress', 0.5 ), array( 'gzip', 1.0 ) ), 
		                     $this->parser( 'compress;q=0.5, gzip;q=1.0' ) );
		$this->assertEquals( array( array( 'gzip', 1.0 ), array( 'identity', 0.5 ), array( '*', 0 ) ), 
		                     $this->parser( 'gzip;q=1.0, identity; q=0.5, *;q=0' ) );
		
		$this->setExpectedException( 'Prack_Error_Request_AcceptEncodingInvalid' );
		$this->parser( 'gzip ; q=1.0' );
	} // It should parse Accept-Encoding correctly
	
	/**
	 * Used by the above test in lieu of lambdas.
	 */
	public function parser( $accept_encoding )
	{
		$request = new Prack_Request( Prack_Mock_Request::envFor( '', array( 'HTTP_ACCEPT_ENCODING' => $accept_encoding ) ) );
		return $request->acceptEncoding();
	}
	
	// should provide ip information
	
	/**
	 * It should allow subclass request to be instantiated after parent request
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_allow_subclass_request_to_be_instantiated_after_parent_request()
	{
		$env = Prack_Mock_Request::envFor( '/?foo=bar' );
		
		$request1 = new Prack_Request( $env );
		$this->assertEquals( array( 'foo' => 'bar' ), $request1->GET() );
		$this->assertEquals( array( 'foo' => 'bar' ), $request1->params() );
		
		$request2 = new MyRequest( $env );
		$this->assertEquals( array( 'foo' => 'bar' ), $request2->GET() );
		$this->assertEquals( array( 'foo' => 'bar' ), $request2->params() );
	} // It should allow subclass request to be instantiated after parent request
	
	/**
	 * It should allow parent request to be instantiated after subclass request
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_allow_parent_request_to_be_instantiated_after_subclass_request()
	{
		$env = Prack_Mock_Request::envFor( '/?foo=bar' );
		
		$request1 = new MyRequest( $env );
		$this->assertEquals( array( 'foo' => 'bar' ), $request1->GET() );
		$this->assertEquals( array( 'foo' => 'bar' ), $request1->params() );

		$request2 = new Prack_Request( $env );
		$this->assertEquals( array( 'foo' => 'bar' ), $request2->GET() );
		$this->assertEquals( array( 'foo' => 'bar' ), $request2->params() );
	} // It should allow parent request to be instantiated after subclass request
	
	/**
	 * It should not strip escaped character from parameters when accessed as string
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_strip_escaped_character_from_parameters_when_accessed_as_string()
	{
		$characters = range( 0x20, 0x7E );
		
		foreach ( $characters as $a )
		{
			$b   = chr( $a );
			$c   = urlencode( $b );
			$url = "/?foo={$c}bar{$c}";
			$env = Prack_Mock_Request::envFor( $url );
			
			$request = new Prack_Request( $env );
			$this->assertEquals( array( 'foo' => "{$b}bar{$b}" ), $request->GET(), 'Error on {$b}; {$c}' );
		}
	} // It should not strip escaped character from parameters when accessed as string
}