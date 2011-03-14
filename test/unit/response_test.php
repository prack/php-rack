<?php

class Prack_ResponseTest_Iterator
  implements Prb_Interface_Enumerable
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
			throw new Prb_Exception_Callback();
		
		$this->array->each( $callback );
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
		$this->parts->concat( $item );
	}
	
	/**
	 * @callback
	 */
	public function addToBuffer( $item )
	{
		$this->buffer->concat( $item );
	}
	
	/**
	 * @callback
	 */
	public function noop( $response )
	{
		return;
	}
	
	/**
	 * It should have sensible default values
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_have_sensible_default_values()
	{
		$response = Prack_Response::with();
		
		list( $status, $headers, $body ) = $response->toA()->raw();
		
		$this->assertEquals( 200, $status->raw() );
		$this->assertEquals( array( 'Content-Type' => Prb::_String( 'text/html' ) ),
		                     $headers->raw() );
		
		$this->parts = Prb::_Array();
		$body->each( array( $this, 'addToParts' ) );
		
		foreach ( $this->parts->raw() as $part )
			$this->assertTrue( $part->isEmpty() );
	} // It should have sensible default values
	
	/**
	 * It can be written to
	 * @author Joshua Morris
	 * @test
	 */
	public function It_can_be_written_to()
	{
		$callback = array( $this, 'writeToResponse' );
		
		$response = Prack_Response::with( null, null, null, $callback );
		$response->write( Prb::_String( 'foo' ) );
		$response->write( Prb::_String( 'bar' ) );
		$response->write( Prb::_String( 'baz' ) );
		$response->finish();
		
		list( $status, $headers, $body ) = $response->toA()->raw();
		
		$this->parts = Prb::_Array();
		$body->each( array( $this, 'addToParts' ) );
		
		$expected = array(
			Prb::_String( 'foo' ), Prb::_String( 'bar' ), Prb::_String( 'baz' )
		);
		
		$this->assertEquals( $expected, $this->parts->raw() );
	} // It can be written to
	
	/**
	 * It can set and read headers
	 * @author Joshua Morris
	 * @test
	 */
	public function It_can_set_and_read_headers()
	{
		$response = new Prack_Response();
		$this->assertEquals( 'text/html', $response->get( 'Content-Type' )->raw() );
		$response->set( 'Content-Type', Prb::_String( 'text/plain' ) );
		$this->assertEquals( 'text/plain', $response->get( 'Content-Type' )->raw() );
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
		$response->redirect( Prb::_String( '/foo' ) );
		list( $status, $headers, $body ) = $response->toA()->raw();
		
		$this->assertEquals( 302, $status->raw() );
		$this->assertEquals( '/foo', $response->get( 'Location' )->raw() );
		
		$response = new Prack_Response();
		$response->redirect( Prb::_String( '/foo' ), Prb::_Numeric( 307 ) );
		list( $status, $headers, $body ) = $response->raw();
		
		$this->assertEquals( 307, $status->raw() );
	} // It can do redirects
	
	/**
	 * It has a useful constructor
	 * @author Joshua Morris
	 * @test
	 */
	public function It_has_a_useful_constructor()
	{
		$callback = array( $this, 'addToBuffer' );
		
		$response = new Prack_Response( Prb::_String( 'foo' ) );
		list( $status, $headers, $body ) = $response->toA()->raw();
		$this->buffer = Prb::_String();
		$body->each( $callback );
		$this->assertEquals( Prb::_String( 'foo' ), $this->buffer );
		
		$response = new Prack_Response(
			Prb::_Array( array(
				Prb::_String( 'foo' ),
				Prb::_String( 'bar' )
			)
		) );
		
		list( $status, $headers, $body ) = $response->raw();
		$this->buffer = Prb::_String();
		$body->each( $callback );
		$this->assertEquals( 'foobar', $this->buffer->raw() );
		
		$response = new Prack_Response(
			new Prack_ResponseTest_Iterator( 
				Prb::_Array( array(
					Prb::_String( 'foo' ),
					Prb::_String( 'bar' ) 
		 		) )
		) );
		
		list( $status, $headers, $body ) = $response->raw();
		$this->buffer = Prb::_String();
		$body->each( $callback );
		$this->assertEquals( 'foobar', $this->buffer->raw() );
		
		$response = new Prack_Response( Prb::_Array(), Prb::_Numeric( 500 ) );
		$this->assertEquals( 500, $response->getStatus()->raw() );
		
		$response = new Prack_Response( Prb::_Array(), Prb::_String( "200 OK" ) );
		$this->assertEquals( 200, $response->getStatus()->raw() );
	} // It has a useful constructor
	
	/**
	 * It has a constructor that can take a callback
	 * @author Joshua Morris
	 * @test
	 */
	public function It_has_a_constructor_that_can_take_a_callback()
	{
		$callback = array( $this, 'configureResponse' );
		$response = Prack_Response::with(
			null, null, null, $callback
		);
		
		list( $status, $headers, $body ) = $response->toA()->raw();
		
		$callback = array( $this, 'addToBuffer' );
		$this->buffer = Prb::_String();
		$body->each( $callback );
		
		$this->assertEquals( 'foo', $this->buffer->raw() );
		$this->assertEquals( 404, $response->getStatus()->raw() );
	} // It has a constructor that can take a callback
	
	/**
	 * @callback
	 */
	public function configureResponse( $response )
	{
		$response->setStatus( Prb::_Numeric( 404 ) );
		$response->write( Prb::_String( 'foo' ) );
	}
	
	/**
	 * It doesn't return invalid responses
	 * @author Joshua Morris
	 * @test
	 */
	public function It_doesn_t_return_invalid_responses()
	{
		$response = new Prack_Response(
			Prb::_Array( array(
				Prb::_String( 'foo' ),
				Prb::_String( 'bar' )
			) ),
			Prb::_Numeric( 204 )
		);
		
		list( $status, $headers, $body ) = $response->toA()->raw();
		
		$callback = array( $this, 'addToBuffer' );
		$this->buffer = Prb::_String();
		$body->each( $callback );
		$this->assertTrue( $this->buffer->isEmpty() );
		
		$this->setExpectedException( 'Prb_Exception_Type' );
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
		$response->write( Prb::_String( 'foo' ) );
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
		$response->setStatus( Prb::_Numeric( 200 ) );
		$this->assertTrue( $response->isSuccessful() );
		$this->assertTrue( $response->isOK() );
		
		$response->setStatus( Prb::_Numeric( 404 ) );
		$this->assertFalse( $response->isSuccessful() );
		$this->assertTrue( $response->isClientError() );
		$this->assertTrue( $response->isNotFound() );
		
		$response->setStatus( Prb::_Numeric( 501 ) );
		$this->assertFalse( $response->isSuccessful() );
		$this->assertTrue( $response->isServerError() );
		
		$response->setStatus( Prb::_Numeric( 307 ) );
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
		$response->set( 'Content-Type', Prb::_String( 'text/yaml' ) );
		
		$this->assertTrue( $response->contains( 'Content-Type' ) );
		$this->assertEquals( 'text/yaml', $response->getHeaders()->get( 'Content-Type' )->raw() );
		$this->assertEquals( 'text/yaml', $response->get( 'Content-Type' )->raw() );
		$this->assertEquals( 'text/yaml', $response->contentType()->raw() );
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
		$response->setStatus( Prb::_Numeric( 200 ) );
		$response->finish();
		$this->assertNull( $response->get( 'Content-Length' ) );
		
		$response = new Prack_Response();
		$response->setStatus( Prb::_Numeric( 200 ) );
		$response->set( 'Content-Length', Prb::_String( '10' ) );
		$response->finish();
		$this->assertEquals( Prb::_String( '10' ), $response->get( 'Content-Length' ) );
	} // It does not add or change Content-Length within finish
	
	/**
	 * It updates Content-Length when body appended to using write
	 * @author Joshua Morris
	 * @test
	 */
	public function It_updates_Content_Length_when_body_appended_to_using_write()
	{
		$response = new Prack_Response();
		$response->setStatus( Prb::_Numeric( 200 ) );
		$this->assertNull( $response->get( 'Content-Length' ) );
		$response->write( Prb::_String( 'Hi' ) );
		$this->assertEquals( '2', $response->get( 'Content-Length' )->raw() );
		$response->write( Prb::_String( ' there' ) );
		$this->assertEquals( '8', $response->get( 'Content-Length' )->raw() );
	} // It updates Content-Length when body appended to using write
	
	/**
	 * It should throw an exception when an unknown method is called, on account of delegation
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_throw_an_exception_when_an_unknown_method_is_called__on_account_of_delegation()
	{
		$this->setExpectedException( 'Prb_Exception_Runtime_DelegationFailed' );
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
		$string_io = Prb_IO::withString( Prb::_String( 'Hello, world!' ) );
		
		$response->setBody( $string_io );
		$response->setLength( $string_io->length() );
		$response->set( 'Content-Length', Prb::_String( (string)$string_io->length() ) );
		$response->finish();
		$response->close();
		
		$this->assertTrue( $response->getBody()->isClosed() );
		$this->assertEquals( $string_io->length(), $response->contentLength() );
		$this->assertEquals( $string_io->length(), $response->getLength() );
	} // It should handle a non-standard response body.
}