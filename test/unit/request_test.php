<?php

// TODO: Document!
class Prack_RequestTest_MyRequest extends Prack_Request
{
	public function params()
	{
		return Prb::_Hash( array( 'foo' => Prb::_String( 'bar' ) ) );
	}
}

// TODO: Document!
class Prack_RequestTest_IPInformation
  implements Prack_Interface_MiddlewareApp
{
	// TODO: Document!
	public function call( $env )
	{
		$request  = Prack_Request::with( $env );
		$response = new Prack_Response();
		$response->write( $request->ip() );
		return $response->finish();
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
		$request = Prack_Request::with( 
			Prack_Mock_Request::envFor( Prb::_String( 'http://example.com:8080/' ) )
		);
		
		$this->assertTrue(  $request->isGet()     );
		$this->assertTrue( !$request->isPost()    );
		$this->assertTrue( !$request->isPut()     );
		$this->assertTrue( !$request->isDelete()  );
		$this->assertTrue( !$request->isHead()    );
		$this->assertTrue( !$request->isTrace()   );
		$this->assertTrue( !$request->isOptions() );
		
		$this->assertEquals(        'http',      $request->scheme()->raw()        );
		$this->assertEquals(         'GET',      $request->requestMethod()->raw() );
		$this->assertEquals(            '',      $request->scriptName()->raw()    );
		$this->assertEquals(           '/',      $request->pathInfo()->raw()      );
		$this->assertEquals(            '',      $request->queryString()->raw()   );
		$this->assertEquals( 'example.com',      $request->host()->raw()          );
		$this->assertEquals(          8080, (int)$request->port()->raw()          );
		$this->assertEquals(           '0',      $request->contentLength()->raw() );
		$this->assertEquals(          null,      $request->logger()               );
		$this->assertNull( $request->contentType() );
		// $this->assertEquals( array(), $request->session() );
		// $this->assertEquals( array(), $request->sessionOptions() );
		
		$this->assertTrue( is_object( $request->body() ) && method_exists( $request->body(), 'gets' ) );
	} // It should wrap the rack variables
	
	/**
	 * It should figure out the correct host()
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_figure_out_the_correct_host()
	{
		$request = Prack_Request::with( Prack_Mock_Request::envFor(
			Prb::_String( '/' ),
			Prb::_Hash( array( 'HTTP_HOST' => Prb::_String( 'www2.example.org' ) ) ) 
		) );
		$this->assertEquals( 'www2.example.org', $request->host()->raw() );
		
		$request = Prack_Request::with( Prack_Mock_Request::envFor(
			Prb::_String( '/' ), 
			Prb::_Hash( array(
				'SERVER_NAME' => Prb::_String( 'example.org' ),
				'SERVER_PORT' => Prb::_String( '9292'        )
			) )
		) );
		$this->assertEquals( 'example.org', $request->host()->raw() );
		
		$request = Prack_Request::with( Prack_Mock_Request::envFor(
			Prb::_String( '/' ), 
			Prb::_Hash( array(
				'HTTP_HOST'             => Prb::_String( 'localhost():81'   ),
				'HTTP_X_FORWARDED_HOST' => Prb::_String( 'example.org:9292' )
			) )
		) );
		$this->assertEquals( 'example.org', $request->host()->raw() );
		
		$env = Prack_Mock_Request::envFor(
			Prb::_String( '/' ), 
			Prb::_Hash( array(
				'SERVER_ADDR' => Prb::_String( '192.168.1.1' ),
				'SERVER_PORT' => Prb::_String( '9292' )
			) )
		);
		$env->delete( 'SERVER_NAME' );
		$this->assertEquals( '192.168.1.1', Prack_Request::with( $env )->host()->raw() );
		
		$env = Prack_Mock_Request::envFor( Prb::_String( '/' ) );
		$env->delete( 'SERVER_NAME' );
		$request = Prack_Request::with( $env );
		$this->assertEquals( '', $request->host()->raw() );
	} // It should figure out the correct host()
	
	/**
	 * It should parse the query string
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_parse_the_query_string()
	{
		$request = Prack_Request::with( Prack_Mock_Request::envFor(
			Prb::_String( '/?foo=bar&quux=bla' )
		) );
		
		$this->assertEquals( 'foo=bar&quux=bla', $request->queryString()->raw() );
		$this->assertEquals( array( 'foo' => Prb::_String( 'bar' ), 'quux' => Prb::_String( 'bla' ) ), $request->GET()->raw() );
		$this->assertTrue( $request->POST()->isEmpty() );
		$this->assertEquals( array( 'foo' => Prb::_String( 'bar' ), 'quux' => Prb::_String( 'bla' ) ), $request->params()->raw() );
	} // It should parse the query string
	
	/**
	 * It should throw an exception if rack.input is missing
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_throw_an_exception_if_rack_input_is_missing()
	{
		$request = Prack_Request::with( Prb::_Hash() );
		$this->setExpectedException( 'Prack_Exception_Runtime_RackInputMissing' );
		$request->POST();
	} // It should throw an exception if rack.input is missing
	
	/**
	 * It should parse POST data when method is POST and no Content-Type given
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_parse_POST_data_when_method_is_POST_and_no_Content_Type_given()
	{
		$request = Prack_Request::with( Prack_Mock_Request::envFor(
			Prb::_String( '/?foo=quux' ),
			Prb::_Hash( array(
				'REQUEST_METHOD' => Prb::_String( 'POST' ),
				'input'          => Prb::_String( 'foo=bar&quux=bla' )
			) )
		) );
		
		$this->assertNull( $request->contentType() );
		$this->assertNull( $request->mediaType()   );
		
		$this->assertEquals( 'foo=quux', $request->queryString()->raw() );
		$this->assertEquals( array( 'foo' => Prb::_String( 'quux' ) ), $request->GET()->raw() );
		$this->assertEquals( array( 'foo' => Prb::_String( 'bar' ), 'quux' => Prb::_String( 'bla' ) ), $request->POST()->raw()   );
		$this->assertEquals( array( 'foo' => Prb::_String( 'bar' ), 'quux' => Prb::_String( 'bla' ) ), $request->params()->raw() );
	} // It should parse POST data when method is POST and no Content-Type given
	
	/**
	 * It should parse POST data with explicit content type regardless of method
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_parse_POST_data_with_explicit_content_type_regardless_of_method()
	{
		$request = Prack_Request::with( Prack_Mock_Request::envFor(
			Prb::_String( '/?foo=quux' ),
			Prb::_Hash( array(
				'CONTENT_TYPE' => Prb::_String( 'application/x-www-form-urlencoded;foo=bar' ),
				'input'        => Prb::_String( 'foo=bar&quux=bla' )
			) )
		) );
		
		$this->assertEquals( 'application/x-www-form-urlencoded;foo=bar',
		                     $request->contentType()->raw() );
		$this->assertEquals( 'application/x-www-form-urlencoded',
		                     $request->mediaType()->raw() );
		$this->assertEquals( 'bar',
		                     $request->mediaTypeParams()->get( 'foo' )->raw() );
		$this->assertEquals( array( 'foo' => Prb::_String( 'bar' ), 'quux' => Prb::_String( 'bla' ) ), $request->POST()->raw() );
		$this->assertEquals( array( 'foo' => Prb::_String( 'bar' ), 'quux' => Prb::_String( 'bla' ) ), $request->params()->raw() );
	} // It should parse POST data with explicit content type regardless of method
	
	/**
	 * It should not parse POST data when media type is not form-data
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_parse_POST_data_when_media_type_is_not_form_data()
	{
		$request = Prack_Request::with( Prack_Mock_Request::envFor(
			Prb::_String( '/?foo=quux' ),
			Prb::_Hash( array(
				'REQUEST_METHOD' => Prb::_String( 'POST' ),
				'CONTENT_TYPE'   => Prb::_String( 'text/plain;charset=utf-8' ),
				'input'          => Prb::_String( 'foo=bar&quux=bla' )
			) )
		) );
		
		$this->assertEquals( 'text/plain;charset=utf-8',
		                     $request->contentType()->raw() );
		$this->assertEquals( 'utf-8',
		                     $request->contentCharset()->raw() );
		$this->assertEquals( 'text/plain',
		                     $request->mediaType()->raw() );
		$this->assertEquals( 'utf-8',
		                     $request->mediaTypeParams()->get( 'charset' )->raw() );
		
		$this->assertTrue( $request->POST()->isEmpty() );
		$this->assertEquals( array( 'foo' => Prb::_String( 'quux' ) ), $request->params()->raw() );
		$this->assertEquals( Prb::_String( 'foo=bar&quux=bla' ), $request->body()->read() );
	} // It should not parse POST data when media type is not form-data
	
	/**
	 * It should parse POST data on PUT when media type is form-data
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_parse_POST_data_on_PUT_when_media_type_is_form_data()
	{
		$request = Prack_Request::with( Prack_Mock_Request::envFor(
			Prb::_String( '/?foo=quux' ),
			Prb::_Hash( array(
				'REQUEST_METHOD' => Prb::_String( 'PUT' ),
				'CONTENT_TYPE'   => Prb::_String( 'application/x-www-form-urlencoded' ),
				'input'          => Prb::_String( 'foo=bar&quux=bla' )
			) )
		) );
		
		$this->assertEquals( array( 'foo' => Prb::_String( 'bar' ), 'quux' => Prb::_String( 'bla' ) ),
		                     $request->POST()->raw() );
		$this->assertEquals( Prb::_String( 'foo=bar&quux=bla' ), $request->body()->read() );
	} // It should parse POST data on PUT when media type is form-data
	
	/**
	 * It should rewind input after parsing POST data
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_rewind_input_after_parsing_POST_data()
	{
		// Create a rewindable stream:
		$input   = Prb_IO::withString( Prb::_String( 'foo=bar&quux=bla' ) );
		$request = Prack_Request::with( Prack_Mock_Request::envFor(
			Prb::_String( '/' ),
			Prb::_Hash( array(
				'CONTENT_TYPE' => Prb::_String( 'application/x-www-form-urlencoded;foo=bar' ),
				'input'        => $input
			) )
		) );
		
		$this->assertEquals( array( 'foo' => Prb::_String( 'bar' ), 'quux' => Prb::_String( 'bla' ) ),
		                     $request->params()->raw() );
		$this->assertEquals( Prb::_String( 'foo=bar&quux=bla' ),
		                     $input->read() );
	} // It should rewind input after parsing POST data
	
	/**
	 * It should clean up Safari's ajax POST body
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_clean_up_Safari_s_ajax_POST_body()
	{
		$request = Prack_Request::with( Prack_Mock_Request::envFor(
			Prb::_String( '/' ),
			Prb::_Hash( array(
				'REQUEST_METHOD' => Prb::_String( 'POST' ),
				'input'          => Prb::_String( "foo=bar&quux=bla\0" )
			) )
		) );
		
		$this->assertEquals( array( 'foo' => Prb::_String( 'bar' ), 'quux' => Prb::_String( 'bla' ) ),
		                     $request->POST()->raw() );
	} // It should clean up Safari's ajax POST body
	
	/**
	 * It should get value by key from params with getParam
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_get_value_by_key_from_params_with_getParam()
	{
		$request = Prack_Request::with( 
			Prack_Mock_Request::envFor( Prb::_String( '?foo=quux' ) )
		);
		
		$this->assertEquals( Prb::_String( 'quux' ), $request->getParam( 'foo' ) );
	} // It should get value by key from params with getParam
	
	/**
	 * It should set value to key on params with setParam
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_set_value_to_key_on_params_with_setParam()
	{
		$request = Prack_Request::with( Prack_Mock_Request::envFor(
			Prb::_String( '?foo=duh' )
		) );
		
		$this->assertEquals( Prb::_String( 'duh' ), $request->getParam( 'foo' ) );
		$this->assertEquals( array( 'foo' => Prb::_String( 'duh' ) ), $request->params()->raw() );
		$request->setParam( 'foo', Prb::_String( 'bar' ) );
		$this->assertEquals( array( 'foo' => Prb::_String( 'bar' ) ), $request->params()->raw() );
		$this->assertEquals( Prb::_String( 'bar' ), $request->getParam( 'foo' ) );
	} // It should set value to key on params with setParam
	
	/**
	 * It should return values for the keys in the order given from valuesAt
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_values_for_the_keys_in_the_order_given_from_valuesAt()
	{
		$request = Prack_Request::with( Prack_Mock_Request::envFor(
			Prb::_String( '?foo=baz&wun=der&bar=ful' )
		) );
		
		$this->assertEquals( array( Prb::_String( 'baz' ) ), $request->valuesAt( 'foo' )->raw() );
		$this->assertEquals( array( Prb::_String( 'baz' ), Prb::_String( 'der' ) ),
		                     $request->valuesAt( 'foo', 'wun' )->raw() );
		$this->assertEquals( array( Prb::_String( 'ful' ), Prb::_String( 'baz' ), Prb::_String( 'der' ) ),
		                     $request->valuesAt( 'bar', 'foo', 'wun' )->raw() );
	} // It should return values for the keys in the order given from valuesAt
	
	/**
	 * It should extract referrer correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_extract_referrer_correctly()
	{
		$request = Prack_Request::with( Prack_Mock_Request::envFor(
			Prb::_String( '/' ),
			Prb::_Hash( array( 'HTTP_REFERER' => Prb::_String( '/some/path' ) ) )
		) );
		$this->assertEquals( '/some/path', $request->referer()->raw() );
		
		$request = Prack_Request::with( Prack_Mock_Request::envFor( Prb::_String( '/' ) ) );
		$this->assertEquals( '/', $request->referer()->raw() );
	} // It should extract referrer correctly
	
	/**
	 * It should alias referer to referrer
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_alias_referer_to_referrer()
	{
		$request = Prack_Request::with( Prack_Mock_Request::envFor(
			Prb::_String( '/' ),
			Prb::_Hash( array( 'HTTP_REFERER' => Prb::_String( '/some/path' ) ) )
		) );
		$this->assertEquals( '/some/path', $request->referrer()->raw() );
	} // It should alias referer to referrer
	
	/**
	 * It should extract user agent correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_extract_user_agent_correctly()
	{
		$request = Prack_Request::with( Prack_Mock_Request::envFor(
			Prb::_String( '/' ),
			Prb::_Hash( array( 'HTTP_USER_AGENT' => Prb::_String( 'Mozilla/4.0 (compatible)' ) ) )
		) );
		$this->assertEquals( 'Mozilla/4.0 (compatible)', $request->userAgent()->raw() );
		
		$request = Prack_Request::with( Prack_Mock_Request::envFor( Prb::_String( '/' ) ) );
		$this->assertNull( $request->userAgent() );
	} // It should extract user agent correctly
	
	/**
	 * It should cache, but invalidates the cache
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_cache__but_invalidates_the_cache()
	{
		$request = Prack_Request::with( Prack_Mock_Request::envFor(
			Prb::_String( '/?foo=quux' ),
			Prb::_Hash( array(
				'CONTENT_TYPE' => Prb::_String( 'application/x-www-form-urlencoded' ),
				'input'        => Prb::_String( 'foo=bar&quux=bla' )
			) )
		) );
		
		$this->assertEquals( array( 'foo' => Prb::_String( 'quux' ) ), $request->GET()->raw() );
		$this->assertEquals( array( 'foo' => Prb::_String( 'quux' ) ), $request->GET()->raw() );
		$env = $request->getEnv();
		$env->set( 'QUERY_STRING', Prb::_String( 'bla=foo' ) );
		$this->assertEquals( array( 'bla' => Prb::_String('foo' ) ), $request->GET()->raw() );
		$this->assertEquals( array( 'bla' => Prb::_String('foo' ) ), $request->GET()->raw() );
		$this->assertEquals( array( 'foo' => Prb::_String('bar' ), 'quux' => Prb::_String( 'bla' ) ), $request->POST()->raw() );
		$this->assertEquals( array( 'foo' => Prb::_String('bar' ), 'quux' => Prb::_String( 'bla' ) ), $request->POST()->raw() );
		$env = $request->getEnv();
		$env->set( 'rack.input', Prb_IO::withString( Prb::_String( 'foo=bla&quux=bar' ) ) );
		$this->assertEquals( array( 'foo' => Prb::_String( 'bla' ), 'quux' => Prb::_String( 'bar' ) ), $request->POST()->raw() );
		$this->assertEquals( array( 'foo' => Prb::_String( 'bla' ), 'quux' => Prb::_String( 'bar' ) ), $request->POST()->raw() );
	} // It should cache, but invalidates the cache
	
	/**
	 * It should figure out if called via XHR
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_figure_out_if_called_via_XHR()
	{
		$request = Prack_Request::with( Prack_Mock_Request::envFor(
			Prb::_String( '/?foo=quux' ),
			Prb::_Hash( array(
				'CONTENT_TYPE' => Prb::_String( 'application/x-www-form-urlencoded' ),
			) )
		) );
		
		$request = Prack_Request::with( Prack_Mock_Request::envFor( Prb::_String( '/' ) ) );
		$this->assertFalse( $request->isXhr() );
		
		$request = Prack_Request::with( Prack_Mock_Request::envFor(
			Prb::_String( '' ),
			Prb::_Hash( array(
				'HTTP_X_REQUESTED_WITH' => Prb::_String( 'XMLHttpRequest' )
			) )
		) );
		$this->assertTrue( $request->isXhr() );
	} // It should figure out if called via XHR
		
	/**
	 * It should parse cookies
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_parse_cookies()
	{
		$request = Prack_Request::with( Prack_Mock_Request::envFor(
			Prb::_String( '' ),
			Prb::_Hash( array(
				'HTTP_COOKIE' => Prb::_String( 'foo=bar;quux=h&m' ),
			) )
		) );
		
		$this->assertEquals( array( 'foo' => 'bar', 'quux' => 'h&m' ), $request->cookies() );
		$this->assertEquals( array( 'foo' => 'bar', 'quux' => 'h&m' ), $request->cookies() );
		$request->getEnv()->delete( 'HTTP_COOKIE' );
		$this->assertEquals( array(), $request->cookies()->raw() );
	} // It should parse cookies
	
	/**
	 * It should parse cookies according to RFC 2109
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_parse_cookies_according_to_RFC_2109()
	{
		$request = Prack_Request::with( Prack_Mock_Request::envFor(
			Prb::_String( '' ),
			Prb::_Hash( array(
				'HTTP_COOKIE' => Prb::_String( 'foo=bar;foo=car' ),
			) )
		) );
		
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
		$request = Prack_Request::with( Prack_Mock_Request::envFor( Prb::_String() ) );
		
		$this->assertEquals( '', $request->scriptName()->raw() );
		$request->setScriptName( Prb::_String( '/foo' ) );
		$this->assertEquals( '/foo', $request->scriptName()->raw() );
		$this->assertEquals( '/foo', $request->getEnv()->get( 'SCRIPT_NAME' )->raw() );
		
		$this->assertEquals( '/', $request->pathInfo()->raw() );
		$request->setPathInfo( Prb::_String( '/foo' ) );
		$this->assertEquals( '/foo', $request->pathInfo()->raw() );
		$this->assertEquals( '/foo', $request->getEnv()->get( 'PATH_INFO' )->raw() );
	} // It should provide setters
	
	/**
	 * It should provide the original env
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_provide_the_original_env()
	{
		$request = Prack_Request::with( $env = Prack_Mock_Request::envFor() );
		$this->assertSame( $env, $request->getEnv() );
	} // It should provide the original env
	
	/**
	 * It should restore the URL
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_restore_the_URL()
	{
		$request = Prack_Request::with( Prack_Mock_Request::envFor() );
		$this->assertEquals( 'http://example.org/', $request->url()->raw() );
		
		$request = Prack_Request::with( Prack_Mock_Request::envFor(
			Prb::_String(),
			Prb::_Hash( array( 'SCRIPT_NAME' => Prb::_String( '/foo' ) ) )
		) );
		$this->assertEquals( 'http://example.org/foo/', $request->url()->raw() );
		
		$request = Prack_Request::with( Prack_Mock_Request::envFor( Prb::_String( '/foo' ) ) );
		$this->assertEquals( 'http://example.org/foo', $request->url()->raw() );
		
		$request = Prack_Request::with( Prack_Mock_Request::envFor( Prb::_String( '?foo' ) ) );
		$this->assertEquals( 'http://example.org/?foo', $request->url()->raw() );
		
		$request = Prack_Request::with( Prack_Mock_Request::envFor( Prb::_String( 'http://example.org:8080/' ) ) );
		$this->assertEquals( 'http://example.org:8080/', $request->url()->raw() );
		
		$request = Prack_Request::with( Prack_Mock_Request::envFor( Prb::_String( 'https://example.org/' ) ) );
		$this->assertEquals( 'https://example.org/', $request->url()->raw() );
		
		$request = Prack_Request::with( Prack_Mock_Request::envFor( Prb::_String( 'https://example.org:8080/foo?foo' ) ) );
		$this->assertEquals( 'https://example.org:8080/foo?foo', $request->url()->raw() );
	} // It should restore the URL
	
	/**
	 * It should restore the full path
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_restore_the_full_path()
	{
		$request = Prack_Request::with( Prack_Mock_Request::envFor() );
		$this->assertEquals( '/', $request->fullpath()->raw() );
		
		$request = Prack_Request::with( Prack_Mock_Request::envFor(
				Prb::_String(),
				Prb::_Hash( array( 'SCRIPT_NAME' => Prb::_String( '/foo' ) ) )
		) );
		$this->assertEquals( '/foo/', $request->fullpath()->raw() );
		
		$request = Prack_Request::with( Prack_Mock_Request::envFor( Prb::_String( '/foo' ) ) );
		$this->assertEquals( '/foo', $request->fullpath()->raw() );
		
		$request = Prack_Request::with( Prack_Mock_Request::envFor( Prb::_String( '?foo' ) ) );
		$this->assertEquals( '/?foo', $request->fullpath()->raw() );
		
		$request = Prack_Request::with( Prack_Mock_Request::envFor( Prb::_String( 'http://example.org:8080/' ) ) );
		$this->assertEquals( '/', $request->fullpath()->raw() );
		
		$request = Prack_Request::with( Prack_Mock_Request::envFor( Prb::_String( 'https://example.org/' ) ) );
		$this->assertEquals( '/', $request->fullpath()->raw() );
		
		$request = Prack_Request::with( Prack_Mock_Request::envFor( Prb::_String( 'https://example.org:8080/foo?foo' ) ) );
		$this->assertEquals( '/foo?foo', $request->fullpath()->raw() );
	} // It should restore the full path
	
	/**
	 * It should handle multiple media type parameters
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_multiple_media_type_parameters()
	{
		$request = Prack_Request::with( Prack_Mock_Request::envFor(
				Prb::_String( '/' ), 
				Prb::_Hash( array( 'CONTENT_TYPE' => Prb::_String( 'text/plain; foo=BAR,baz=bizzle dizzle;BLING=bam' ) ) )
		) );
		$media_type_params = $request->mediaTypeParams();
		$this->assertFalse( $request->isFormData() );
		$this->assertTrue( $media_type_params->contains( 'foo' ) );
		$this->assertEquals( 'BAR', $media_type_params->get( 'foo' )->raw() );
		$this->assertTrue( $media_type_params->contains( 'baz' ) );
		$this->assertEquals( 'bizzle dizzle', $media_type_params->get( 'baz' )->raw() );
		$this->assertFalse( $media_type_params->contains( 'BLING' ) );
		$this->assertTrue( $media_type_params->contains( 'bling' ) );
		$this->assertEquals( 'bam', $media_type_params->get( 'bling' )->raw() );
	} // It should handle multiple media type parameters
	
	// should parse multipart form data
	
	/**
	 * It should parse Accept-Encoding correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_parse_Accept_Encoding_correctly()
	{
		$this->assertEquals( Prb::_Array(), $this->parser( Prb::_String() ) );
		
		$expected = Prb::_Array( array(
			Prb::_Array( array( Prb::_String( 'compress' ), 1.0 ) ),
			Prb::_Array( array( Prb::_String( 'gzip'     ), 1.0 ) )
		) );
		$this->assertEquals(
			$expected, $this->parser( Prb::_String( 'compress, gzip' ) )
		);
		
		$expected = Prb::_Array( array(
			Prb::_Array( array( Prb::_String( '*' ), 1.0 ) )
		) );
		$this->assertEquals(
			$expected, $this->parser( Prb::_String( '*' ) )
		);
		
		$expected = Prb::_Array( array(
			Prb::_Array( array( Prb::_String( 'compress' ), 0.5 ) ),
			Prb::_Array( array( Prb::_String( 'gzip'     ), 1.0 ) )
		) );
		$this->assertEquals(
			$expected,
			$this->parser( Prb::_String( 'compress;q=0.5, gzip;q=1.0' ) )
		);
		
		$expected = Prb::_Array( array(
			Prb::_Array( array( Prb::_String( 'gzip'     ), 1.0 ) ),
			Prb::_Array( array( Prb::_String( 'identity' ), 0.5 ) ),
			Prb::_Array( array( Prb::_String( '*'        ),   0 ) )
		) );
		$this->assertEquals( $expected, 
		                     $this->parser( Prb::_String( 'gzip;q=1.0, identity; q=0.5, *;q=0' ) ) );
		
		$this->setExpectedException( 'Prack_Exception_Request_AcceptEncodingInvalid' );
		$this->parser( Prb::_String( 'gzip ; q=1.0' ) );
	} // It should parse Accept-Encoding correctly
	
	/**
	 * Used by the above test in lieu of lambdas.
	 */
	public function parser( $accept_encoding )
	{
		$request = Prack_Request::with( Prack_Mock_Request::envFor(
			Prb::_String(),
			Prb::_Hash( array(
				'HTTP_ACCEPT_ENCODING' => $accept_encoding,
			) )
		) );
		return $request->acceptEncoding();
	}
	
	/**
	 * It should provide ip information
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_provide_ip_information()
	{
		$mock_request  = new Prack_Mock_Request( new Prack_RequestTest_IPInformation() );
		$mock_response = $mock_request->get(
			Prb::_String( '/' ),
			Prb::_Hash( array( 'REMOTE_ADDR' => Prb::_String( '123.123.123.123' ) )
		) );
		$this->assertEquals( '123.123.123.123', $mock_response->getBody()->raw() );
		
		$mock_response = $mock_request->get(
			Prb::_String( '/' ),
			Prb::_Hash( array(
				'REMOTE_ADDR'          => Prb::_String( '123.123.123.123' ),
				'HTTP_X_FORWARDED_FOR' => Prb::_String( '234.234.234.234' )
			) )
		);
		$this->assertEquals( '234.234.234.234', $mock_response->getBody()->raw() );
		
		$mock_response = $mock_request->get(
			Prb::_String( '/' ),
			Prb::_Hash( array(
				'REMOTE_ADDR'          => Prb::_String( '123.123.123.123' ),
				'HTTP_X_FORWARDED_FOR' => Prb::_String( '234.234.234.234,212.212.212.212' )
			) )
		);
		$this->assertEquals( '234.234.234.234', $mock_response->getBody()->raw() );
		
		$mock_response = $mock_request->get(
			Prb::_String( '/' ),
			Prb::_Hash( array(
				'REMOTE_ADDR'          => Prb::_String( '123.123.123.123' ),
				'HTTP_X_FORWARDED_FOR' => Prb::_String( 'unknown,234.234.234.234,212.212.212.212' )
			) )
		);
		$this->assertEquals( '234.234.234.234', $mock_response->getBody()->raw() );
	} // It should provide ip information
	
	/**
	 * It should allow subclass request to be instantiated after parent request
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_allow_subclass_request_to_be_instantiated_after_parent_request()
	{
		$env = Prack_Mock_Request::envFor( Prb::_String( '/?foo=bar' ) );
		
		$request1 = Prack_Request::with( $env );
		$this->assertEquals( array( 'foo' => Prb::_String( 'bar' ) ), $request1->GET()->raw() );
		$this->assertEquals( array( 'foo' => Prb::_String( 'bar' ) ), $request1->params()->raw() );
		
		$request2 = new Prack_RequestTest_MyRequest( $env );
		$this->assertEquals( array( 'foo' => Prb::_String( 'bar' ) ), $request2->GET()->raw() );
		$this->assertEquals( array( 'foo' => Prb::_String( 'bar' ) ), $request2->params()->raw() );
	} // It should allow subclass request to be instantiated after parent request
	
	/**
	 * It should allow parent request to be instantiated after subclass request
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_allow_parent_request_to_be_instantiated_after_subclass_request()
	{
		$env = Prack_Mock_Request::envFor( Prb::_String( '/?foo=bar' ) );
		
		$request1 = new Prack_RequestTest_MyRequest( $env );
		$this->assertEquals( array( 'foo' => Prb::_String( 'bar' ) ), $request1->GET()->raw() );
		$this->assertEquals( array( 'foo' => Prb::_String( 'bar' ) ), $request1->params()->raw() );
		
		$request2 = Prack_Request::with( $env );
		$this->assertEquals( array( 'foo' => Prb::_String( 'bar' ) ), $request2->GET()->raw() );
		$this->assertEquals( array( 'foo' => Prb::_String( 'bar' ) ), $request2->params()->raw() );
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
			$b       = chr( $a );
			$c       = urlencode( $b );
			$url     = Prb::_String( "/?foo={$c}bar{$c}" );
			$request = Prack_Request::with( Prack_Mock_Request::envFor( $url ) );
			$this->assertEquals( array( 'foo' => Prb::_string( "{$b}bar{$b}" ) ), $request->GET()->raw(), "Error on {$b}; {$c}" );
		}
	} // It should not strip escaped character from parameters when accessed as string
}