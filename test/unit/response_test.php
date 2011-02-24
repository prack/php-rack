<?php

class SomeOtherIterator
  implements Prack_Interface_Enumerable
{
	private $array;
	
	// TODO: Document!
	function __construct( $array )
	{
		$this->array = $array;
	}
	
	// TODO: Document!
	public function each( $callback )
	{
		if ( !is_callable( $callback ) )
			throw new Prack_Error_Callback();
		
		array_walk( $this->array, $callback );
	}
}

// TODO: Document!
class Prack_ResponseTest extends PHPUnit_Framework_TestCase 
{
	private $parts;
	private $buffer;
	
	/**
	 * @callback
	 */
	public function addToParts( $item )
	{
		array_push( $this->parts, $item );
	}
	
	/**
	 * @callback
	 */
	public function addToBuffer( $item )
	{
		$this->buffer .= $item;
	}
	
	/**
	 * @callback
	 */
	public function noop( $response )
	{
		return;
	}
	
	/**
	 * @callback
	 */
	public function readAndUpdateContentLength( $item )
	{
		$env[ 'Content-Length' ] += strlen( $item );
	}
	
	/**
	 * It should have sensible default values
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_have_sensible_default_values()
	{
		$callback = array( $this, 'addToParts' );
		
		$response = new Prack_Response();
		list( $status, $headers, $body ) = $response->finish();
		$this->assertEquals( 200, $response->getStatus() );
		$this->assertEquals( array( 'Content-Type' => 'text/html' ), $headers );
		$this->parts = array();
		$body->each( $callback );
		foreach ( $this->parts as $part )
			$this->assertEquals( '', $part );
		
		$response = new Prack_Response();
		list( $status, $headers, $body ) = $response->toArray();
		$this->assertEquals( 200, $response->getStatus() );
		$this->assertEquals( array( 'Content-Type' => 'text/html' ), $headers );
		$this->parts = array();
		$body->each( $callback );
		foreach ( $this->parts as $part )
			$this->assertEquals( '', $part );
	} // It should have sensible default values
	
	/**
	 * It can be written to
	 * @author Joshua Morris
	 * @test
	 */
	public function It_can_be_written_to()
	{
		$callback = array( $this, 'writeToResponse' );
		$response = new Prack_Response( array(), 200, array(), $callback );
		
		$response->write( 'foo' );
		$response->write( 'bar' );
		$response->write( 'baz' );
		
		list( $status, $headers, $body ) = $response->finish();
		
		$this->parts = array();
		$callback = array( $this, 'addToParts' );
		$body->each( $callback );
		
		$this->assertEquals( array( 'foo', 'bar', 'baz' ), $this->parts );
	} // It can be written to
	
	/**
	 * It can set and read headers
	 * @author Joshua Morris
	 * @test
	 */
	public function It_can_set_and_read_headers()
	{
		$response = new Prack_Response();
		$this->assertEquals( 'text/html', $response->get( 'Content-Type' ) );
		$response->set( 'Content-Type', 'text/plain' );
		$this->assertEquals( 'text/plain', $response->get( 'Content-Type' ) );
	} // It can set and read headers
	
	/**
	 * It can set cookies
	 * @author Joshua Morris
	 * @test
	 */
	public function It_can_set_cookies()
	{
		/*
		$response = new Prack_Response();
		$response->setCookie( 'foo', 'bar' );
		$this->assertEquals( 'foo=bar', $response->get( 'Set-Cookie' ) );
		$response->setCookie( 'foo2', 'bar2' );
		$this->assertEquals( implode( "\n", array( 'foo=bar', 'foo2=bar2' ) ), $response->get( 'Set-Cookie' ) );
		$response->setCookie( 'foo3', 'bar3' );
		$this->assertEquals( implode( "\n", array( 'foo=bar', 'foo2=bar2', 'foo3=bar3' ) ), $response->get( 'Set-Cookie' ) );
		*/
		$this->markTestSkipped( 'pending cookie implementation' );
	} // It can set cookies
	
	/**
	 * It can set cookies with the same name for multiple domains
	 * @author Joshua Morris
	 * @test
	 */
	public function It_can_set_cookies_with_the_same_name_for_multiple_domains()
	{
		/*
		$response = new Prack_Response();
		$response->setCookie( 'foo', array( 'value' => 'bar', 'domain' => 'sample.example.com' ) );
		$response->setCookie( 'foo', array( 'value' => 'bar', 'domain' => '.example.com' ) );
		$cookie_string = implode( "\n", array( 'foo=bar; domain=sample.example.com', 'foo=bar; domain=.example.com' ) );
		$this->assertEquals( $cookie_string, $response->get( 'Set-Cookie' ) );
		*/
		$this->markTestSkipped( 'pending cookie implementation' );
	} // It can set cookies with the same name for multiple domains
	
	/**
	 * It formats the Cookie expiration date accordingly to RFC 2109
	 * @author Joshua Morris
	 * @test
	 */
	public function It_formats_the_Cookie_expiration_date_accordingly_to_RFC_2109()
	{
		/*
		$response = new Prack_Response();
		$expires  = time() + 10;
		$response->setCookie( 'foo', array( 'value' => 'bar', 'expires' => $expires ) );
		$this->assertRegExp( '/expires=..., \d\d-...-\d\d\d\d \d\d:\d\d:\d\d .../', $response->get( 'Set-Cookie' ) );
		*/
		$this->markTestSkipped( 'pending cookie implementation' );
	} // It formats the Cookie expiration date accordingly to RFC 2109
	
	/**
	 * It can set secure cookies
	 * @author Joshua Morris
	 * @test
	 */
	public function It_can_set_secure_cookies()
	{
		/*
		$response = new Prack_Response();
		$response->setCookie( 'foo', array( 'value' => 'bar', 'secure' => true ) );
		$this->assertEquals( 'foo=bar; secure', $response->get( 'Set-Cookie' ) );
		*/
		$this->markTestSkipped( 'pending cookie implementation' );
	} // It can set secure cookies
	
	/**
	 * It can set http only cookies
	 * @author Joshua Morris
	 * @test
	 */
	public function It_can_set_http_only_cookies()
	{
		/*
		$response = new Prack_Response();
		$response->setCookie( 'foo', array( 'value' => 'bar', 'httponly' => true ) );
		$this->assertEquals( 'foo=bar; HttpOnly', $response->get( 'Set-Cookie' ) );
		*/
		$this->markTestSkipped( 'pending cookie implementation' );
	} // It can set http only cookies
	
	/**
	 * It can delete cookies
	 * @author Joshua Morris
	 * @test
	 */
	public function It_can_delete_cookies()
	{
		/*
		$response = new Prack_Response();
		$response->setCookie( 'foo', 'bar' );
		$response->setCookie( 'foo2', 'bar2' );
		$response->deleteCookie( 'foo' );
		
		$cookie_string = implode( "\n", array(
			'foo2=bar2',
			'foo=; expires=Thu, 01-Jan-1970 00:00:00 GMT'
		) );
		
		$this->assertEquals( $cookie_string, $response->get( 'Set-Cookie' ) );
		*/
		$this->markTestSkipped( 'pending cookie implementation' );
	} // It can delete cookies
	
	
	/**
	 * It can delete cookies with the same name from multiple domains
	 * @author Joshua Morris
	 * @test
	 */
	public function It_can_delete_cookies_with_the_same_name_from_multiple_domains()
	{
		/*
		$response = new Prack_Response();
		
		$response->setCookie( 'foo', array( 'value' => 'bar', 'domain' => 'sample.example.com' ) );
		$response->setCookie( 'foo', array( 'value' => 'bar', 'domain' => '.example.com' ) );
		$cookie_string = implode( "\n", array( 'foo=bar; domain=sample.example.com', 'foo=bar; domain=.example.com' ) );
		$this->assertEquals( $cookie_string, $response->get( 'Set-Cookie' ) );
		
		$response->deleteCookie( 'foo', array( 'domain' => '.example.com' ) );
		$cookie_string = implode( "\n", array( 'foo=bar; domain=sample.example.com', 'foo=; domain=.example.com; expires=expires=Thu, 01-Jan-1970 00:00:00 GMT' ) );
		$this->assertEquals( $cookie_string, $response->get( 'Set-Cookie' ) );
		
		$response->deleteCookie( 'foo', array( 'domain' => 'sample.example.com' ) );
		$cookie_string = implode( "\n", array( 'foo=; domain=sample.example.com; expires=expires=Thu, 01-Jan-1970 00:00:00 GMT', 
		                                       'foo=; domain=.example.com; expires=expires=Thu, 01-Jan-1970 00:00:00 GMT' ) );
		$this->assertEquals( $cookie_string, $response->get( 'Set-Cookie' ) );
		*/
		$this->markTestSkipped( 'pending cookie implementation' );
	} // It can delete cookies with the same name from multiple domains
	
	/**
	 * It can do redirects
	 * @author Joshua Morris
	 * @test
	 */
	public function It_can_do_redirects()
	{
		$response = new Prack_Response();
		$response->redirect( '/foo' );
		list( $status, $headers, $body ) = $response->finish();
		
		$this->assertEquals( 302, $response->getStatus() );
		$this->assertEquals( '/foo', $response->get( 'Location' ) );
		
		$response = new Prack_Response();
		$response->redirect( '/foo', 307 );
		list( $status, $headers, $body ) = $response->finish();
		
		$this->assertEquals( 307, $response->getStatus() );
	} // It can do redirects
	
	/**
	 * It has a useful constructor
	 * @author Joshua Morris
	 * @test
	 */
	public function It_has_a_useful_constructor()
	{
		$callback = array( $this, 'addToBuffer' );
		
		$response = new Prack_Response( 'foo' );
		list( $status, $headers, $body ) = $response->finish();
		$this->buffer = '';
		$body->each( $callback );
		$this->assertEquals( 'foo', $this->buffer );
		
		$response = new Prack_Response( array( 'foo', 'bar' ) );
		list( $status, $headers, $body ) = $response->finish();
		$this->buffer = '';
		$body->each( $callback );
		$this->assertEquals( 'foobar', $this->buffer );
		
		$response = new Prack_Response( new SomeOtherIterator( array( 'foo', 'bar' ) ) );
		list( $status, $headers, $body ) = $response->finish();
		$this->buffer = '';
		$body->each( $callback );
		$this->assertEquals( 'foobar', $this->buffer );
		
		$response = new Prack_Response( array(), 500 );
		$this->assertEquals( 500, $response->getStatus() );
		
		$response = new Prack_Response( array(), "200 OK" );
		$this->assertEquals( 200, $response->getStatus() );
	} // It has a useful constructor
	
	/**
	 * It has a constructor that can take a callback
	 * @author Joshua Morris
	 * @test
	 */
	public function It_has_a_constructor_that_can_take_a_callback()
	{
		$callback = array( $this, 'configureResponse' );
		$response = new Prack_Response( array(), 200, array(), $callback );
		list( $status, $headers, $body ) = $response->finish();
		
		$callback = array( $this, 'addToBuffer' );
		$this->buffer = '';
		$body->each( $callback );
		
		$this->assertEquals( 'foo', $this->buffer );
		$this->assertEquals( 404, $response->getStatus() );
	} // It has a constructor that can take a callback
	
	/**
	 * @callback
	 */
	public function configureResponse( $response )
	{
		$response->setStatus( 404 );
		$response->write( 'foo' );
	}
	
	/**
	 * It doesn't return invalid responses
	 * @author Joshua Morris
	 * @test
	 */
	public function It_doesn_t_return_invalid_responses()
	{
		$response = new Prack_Response( array( 'foo', 'bar' ), 204 );
		list( $status, $headers, $body ) = $response->finish();
		
		$callback = array( $this, 'addToBuffer' );
		$this->buffer = '';
		$body->each( $callback );
		$this->assertTrue( strlen( $this->buffer ) == 0 );
		
		$this->setExpectedException( 'Prack_Error_Type' );
		new Prack_Response( new Prack() ); // Invalid type.
	} // It doesn't return invalid responses
	
	/**
	 * It knows if it's empty
	 * @author Joshua Morris
	 * @test
	 */
	public function It_knows_if_it_s_empty()
	{
		$response = new Prack_Response();
		$response->write( 'foo' );
		$this->assertFalse( $response->isEmpty() );
		
		$response = new Prack_Response();
		$this->assertTrue( $response->isEmpty() );
		$response->finish();
		$this->assertTrue( $response->isEmpty() );
		
		$callback = array( $this, 'noop' );
		$response = new Prack_Response();
		$this->assertTrue( $response->isEmpty() );
		$response->finish( $callback );
		$this->assertFalse( $response->isEmpty() );
	} // It knows if it's empty
	
	/**
	 * It should provide access to the HTTP status
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_provide_access_to_the_HTTP_status()
	{
		$response = new Prack_Response();
		$response->setStatus( 200 );
		$this->assertTrue( $response->isSuccessful() );
		$this->assertTrue( $response->isOK() );
		
		$response->setStatus( 404 );
		$this->assertFalse( $response->isSuccessful() );
		$this->assertTrue( $response->isClientError() );
		$this->assertTrue( $response->isNotFound() );
		
		$response->setStatus( 501 );
		$this->assertFalse( $response->isSuccessful() );
		$this->assertTrue( $response->isServerError() );
		
		$response->setStatus( 307 );
		$this->assertTrue( $response->isRedirect() );
	} // It should provide access to the HTTP status
	
	/**
	 * It should provide access to the HTTP headers
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_provide_access_to_the_HTTP_headers()
	{
		$response = new Prack_Response();
		$response->set( 'Content-Type', 'text/yaml' );
		
		$this->assertTrue( $response->contains( 'Content-Type' ) );
		$this->assertEquals( 'text/yaml', $response->getHeaders()->get( 'Content-Type' ) );
		$this->assertEquals( 'text/yaml', $response->get( 'Content-Type' ) );
		$this->assertEquals( 'text/yaml', $response->contentType() );
		$this->assertNull( $response->contentLength() );
		$this->assertNull( $response->location() );
	} // It should provide access to the HTTP headers
	
	/**
	 * It does not add or change Content-Length within finish
	 * @author Joshua Morris
	 * @test
	 */
	public function It_does_not_add_or_change_Content_Length_within_finish()
	{
		$response = new Prack_Response();
		$response->setStatus( 200 );
		$response->finish();
		$this->assertNull( $response->get( 'Content-Length' ) );
		
		$response = new Prack_Response();
		$response->setStatus( 200 );
		$response->set( 'Content-Length', '10' );
		$response->finish();
		$this->assertEquals( 10, $response->get( 'Content-Length' ) );
	} // It does not add or change Content-Length within finish
	
	/**
	 * It updates Content-Length when body appended to using write
	 * @author Joshua Morris
	 * @test
	 */
	public function It_updates_Content_Length_when_body_appended_to_using_write()
	{
		$response = new Prack_Response();
		$response->setStatus( 200 );
		$this->assertNull( $response->get( 'Content-Length' ) );
		$response->write( 'Hi' );
		$this->assertEquals( '2', $response->get( 'Content-Length' ) );
		$response->write( ' there' );
		$this->assertEquals( '8', $response->get( 'Content-Length' ) );
	} // It updates Content-Length when body appended to using write
	
	/**
	 * It should throw an exception when an unknown method is called, on account of delegation
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_throw_an_exception_when_an_unknown_method_is_called__on_account_of_delegation()
	{
		$this->setExpectedException( 'Prack_Error_Runtime_DelegationFailed' );
		$response = new Prack_Response();
		$response->foobar();
	} // It should throw an exception when an unknown method is called, on account of delegation
	
	/**
	 * It should handle a non-standard response body.
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_a_non_standard_response_body_()
	{
		$response  = new Prack_Response();
		$string_io = Prack_Utils_IO::withString( 'Hello, world!' );
		
		$callback = array( $this, 'readAndUpdateContentLength' );
		$response->setBody( $string_io );
		$response->setLength( $string_io->length() );
		$response->set( 'Content-Length', $string_io->length() );
		$response->finish();
		$response->close();
		
		$this->assertTrue( $response->getBody()->isClosed() );
		$this->assertEquals( $string_io->length(), $response->contentLength() );
		$this->assertEquals( $string_io->length(), $response->getLength() );
	} // It should handle a non-standard response body.
}