<?php

// TODO: Document!
class Prack_HeadTest extends PHPUnit_Framework_TestCase 
{
	// TODO: Document!
	static function responseFor( $headers = null )
	{
		$headers        = is_null( $headers ) ? Prb::Hsh() : Prb::Hsh( $headers );
		$middleware_app = new Prack_Test_Echo(
		  Prb::Num( 200 ),
		  Prb::Hsh( array(
		    'Content-type'   => Prb::Str( 'test/plain' ),
		    'Content-length' => Prb::Str( '3'          )
		  ) ),
		  Prb::Ary( array( Prb::Str( 'foo' ) ) )
		);
		
		$request  = Prack_Mock_Request::envFor( Prb::Str( '/' ), $headers );
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
			$response = self::responseFor( array( 'REQUEST_METHOD' => Prb::Str( $method ) ) );
			
			$this->assertEquals( 200, $response->get( 0 )->raw() );
			$this->assertEquals(
			  array(
			    'Content-type'   => Prb::Str( 'test/plain' ),
			    'Content-length' => Prb::Str( '3'          )
			  ),
			  $response->get( 1 )->raw()
			);
			$this->assertEquals(
			  array( Prb::Str( 'foo' ) ),
			  $response->get( 2 )->raw()
			);
		}	
	} // It should pass GET, POST, PUT, DELETE, OPTIONS, TRACE requests
	
	/**
	 * It should remove body from HEAD requests
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_remove_body_from_HEAD_requests()
	{
		$response = self::responseFor( array( 'REQUEST_METHOD' => Prb::Str( 'HEAD' ) ) );
		
		$this->assertEquals( 200, $response->get( 0 )->raw() );
		$this->assertEquals(
		  array(
		    'Content-type'   => Prb::Str( 'test/plain' ),
		    'Content-length' => Prb::Str( '3'          )
		  ),
		  $response->get( 1 )->raw()
		);
		$this->assertEquals(
		  array(),
		  $response->get( 2 )->raw()
		);
	} // It should remove body from HEAD requests
}