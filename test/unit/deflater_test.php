<?php

class Prack_DeflaterTest_Enumerable
  implements Prb_I_Enumerable
{
	// TODO: Document!
	public function each( $callback )
	{
		call_user_func( $callback, 'foo' );
		call_user_func( $callback, 'bar' );
	}
}

// TODO: Document!
class Prack_DeflaterTest extends PHPUnit_Framework_TestCase 
{
	private $buffer;
	
	// TODO: Document!
	public function setUp()
	{
		$this->buffer = '';
	}
	
	// TODO: Document!
	public function buildResponse( $status, $body, $accept_encoding, $headers = array() )
	{
		if( is_string( $body ) )
			$body = array( $body );
		
		$middleware_app = new Prack_Test_Echo( $status, array(), $body );
		$env =
		  Prack_Mock_Request::envFor( '', array_merge( $headers, array( 'HTTP_ACCEPT_ENCODING' => $accept_encoding ) ) );
		$response = Prack_Deflater::with( $middleware_app )->call( $env );
		
		return $response;
	}
	
	// TODO: Document!
	public function onEach( $part )
	{
		$this->buffer .= $part;
	}

	/**
	 * It should be able to deflate bodies that respond to each
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_able_to_deflate_bodies_that_respond_to_each()
	{
		$body     = new Prack_DeflaterTest_Enumerable();
		$response = $this->buildResponse( 200, $body, 'deflate' );
		
		$this->assertEquals( 200, $response[ 0 ] );
		$this->assertEquals( array( 'Content-Encoding' => 'deflate', 'Vary' => 'Accept-Encoding' ), $response[ 1 ] );
		
		$response[ 2 ]->each( array( $this, 'onEach' ) );
		
		$this->assertEquals( "K\313\317OJ,\002\000", $this->buffer );
	} // It should be able to deflate bodies that respond to each
	
	/**
	 * It should be able to deflate string bodies
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_able_to_deflate_string_bodies()
	{
		$body     = new Prack_DeflaterTest_Enumerable();
		$response = $this->buildResponse( 200, 'Hello world!', 'deflate' );
		
		$this->assertEquals( 200, $response[ 0 ] );
		$this->assertEquals( array( 'Content-Encoding' => 'deflate', 'Vary' => 'Accept-Encoding' ), $response[ 1 ] );
		
		$response[ 2 ]->each( array( $this, 'onEach' ) );
		
		$this->assertEquals( "\363H\315\311\311W(\317/\312IQ\004\000", $this->buffer );
	} // It should be able to deflate string bodies
	
	/**
	 * It should be able to gzip bodies that respond to each
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_able_to_gzip_bodies_that_respond_to_each()
	{
		$body     = new Prack_DeflaterTest_Enumerable();
		$response = $this->buildResponse( 200, $body, 'gzip' );
		
		$this->assertEquals( 200, $response[ 0 ] );
		$this->assertEquals( array( 'Content-Encoding' => 'gzip', 'Vary' => 'Accept-Encoding' ), $response[ 1 ] );
		
		$response[ 2 ]->each( array( $this, 'onEach' ) );
		$tempfile = Prb_IO::withTempfile();
		$tempfile->write( $this->buffer );
		$tempfile->close();
		
		ob_start();
			readgzfile( $tempfile->getPath() );
		$decompressed = ob_get_clean();
		
		$this->assertEquals( 'foobar', $decompressed );
	} // It should be able to gzip bodies that respond to each
	
	/**
	 * It should be able to fallback to no deflation
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_able_to_fallback_to_no_deflation()
	{
		$response = $this->buildResponse( 200, 'Hello world!', 'superzip' );
		
		$this->assertEquals( 200, $response[ 0 ] );
		$this->assertEquals( array( 'Vary' => 'Accept-Encoding' ), $response[ 1 ] );
		$this->assertEquals( array( 'Hello world!' ), $response[ 2 ] );
	} // It should be able to fallback to no deflation
	
	/**
	 * It should be able to skip when there is no response entity body
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_able_to_skip_when_there_is_no_response_entity_body()
	{
		$response = $this->buildResponse( 304, array(), 'gzip' );
		
		$this->assertEquals( 304, $response[ 0 ] );
		$this->assertEquals( array(), $response[ 1 ] );
		$this->assertEquals( array(), $response[ 2 ] );
	} // It should be able to skip when there is no response entity body
	
	/**
	 * It should handle the lack of an acceptable encoding
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_the_lack_of_an_acceptable_encoding()
	{
		$response1 = $this->buildResponse( 200, 'Hello world!', 'identity;q=0', array( 'PATH_INFO' => '/' ) );
		$this->assertEquals( 406, $response1[ 0 ] );
		$this->assertEquals( array( 'Content-Type' => 'text/plain', 'Content-Length' => '71' ), $response1[ 1 ] );
		$this->assertEquals( array( 'An acceptable encoding for the requested resource / could not be found.' ), $response1[ 2 ] );
		
		$response2 = $this->buildResponse( 200, 'Hello world!', 'identity;q=0', array( 'SCRIPT_NAME' => '/foo', 'PATH_INFO' => '/bar' ) );
		$this->assertEquals( 406, $response2[ 0 ] );
		$this->assertEquals( array( 'Content-Type' => 'text/plain', 'Content-Length' => '78' ), $response2[ 1 ] );
		$this->assertEquals( array( 'An acceptable encoding for the requested resource /foo/bar could not be found.' ), $response2[ 2 ] );
	} // It should handle the lack of an acceptable encoding
	
	/**
	 * It should handle gzip resource with Last-Modified header
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_gzip_resource_with_Last_Modified_header()
	{
		$last_modified  = http_date( time() );
		$middleware_app = new Prack_Test_Echo( 200, array( 'Last-Modified' => $last_modified ), array( 'Hello World!' ) );
		
		$env      = Prack_Mock_Request::envFor( '', array( 'HTTP_ACCEPT_ENCODING' => 'gzip' ) );
		$response = Prack_Deflater::with( $middleware_app )->call( $env );
		
		$this->assertEquals( 200, $response[ 0 ] );
		$this->assertEquals( array( 'Content-Encoding' => 'gzip', 'Vary' => 'Accept-Encoding', 'Last-Modified' => $last_modified ), $response[ 1 ] );
		
		$response[ 2 ]->each( array( $this, 'onEach' ) );
		$tempfile = Prb_IO::withTempfile();
		$tempfile->write( $this->buffer );
		$tempfile->close();
		
		ob_start();
			readgzfile( $tempfile->getPath() );
		$decompressed = ob_get_clean();
		
		$this->assertEquals( 'Hello World!', $decompressed );
	} // It should handle gzip resource with Last-Modified header
	
	/**
	 * It should do nothing when no-transform Cache-Control directive present
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_do_nothing_when_no_transform_Cache_Control_directive_present()
	{
		$middleware_app = new Prack_Test_Echo( 200, array( 'Cache-Control' => 'no-transform' ), array( 'Hello World!' ) );
		
		$env      = Prack_Mock_Request::envFor( '', array( 'HTTP_ACCEPT_ENCODING' => 'gzip' ) );
		$response = Prack_Deflater::with( $middleware_app )->call( $env );
		
		$this->assertEquals( 200, $response[ 0 ] );
		$this->assertNull( @$headers[ 'Content-Encoding' ] );
		$this->assertEquals( 'Hello World!', join( '', $response[ 2 ] ) );
	} // It should do nothing when no-transform Cache-Control directive present
}