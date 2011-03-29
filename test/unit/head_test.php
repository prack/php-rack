<?php

// TODO: Document!
class Prack_HeadTest extends PHPUnit_Framework_TestCase 
{
	// TODO: Document!
	static function responseFor( $headers = array() )
	{
		$middleware_app = new Prack_Test_Echo( 200, array( 'Content-type' => 'test/plain', 'Content-length' => '3' ), array( 'foo' ) );
		$request  = Prack_Mock_Request::envFor( '/', $headers );
		$response = Prack_Head::with( $middleware_app )->call( $request );
		return $response;
	}
	
	/**
	 * It should pass GET, POST, PUT, DELETE, OPTIONS, TRACE requests
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_pass_GET__POST__PUT__DELETE__OPTIONS__TRACE_requests()
	{
		$methods = array( 'GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'TRACE' );
		
		foreach ( $methods as $method )
		{
			$response = self::responseFor( array( 'REQUEST_METHOD' => $method ) );
			$this->assertEquals( 200,                                                              $response[ 0 ] );
			$this->assertEquals( array( 'Content-type' => 'test/plain', 'Content-length' => '3' ), $response[ 1 ] );
			$this->assertEquals( array( 'foo' ),                                                   $response[ 2 ] );
		}
	} // It should pass GET, POST, PUT, DELETE, OPTIONS, TRACE requests
	
	/**
	 * It should remove body from HEAD requests
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_remove_body_from_HEAD_requests()
	{
		$response = self::responseFor( array( 'REQUEST_METHOD' => 'HEAD' ) );
		$this->assertEquals( 200,                                                              $response[ 0 ] );
		$this->assertEquals( array( 'Content-type' => 'test/plain', 'Content-length' => '3' ), $response[ 1 ] );
		$this->assertEquals( array(),                                                          $response[ 2 ] );
	} // It should remove body from HEAD requests
}