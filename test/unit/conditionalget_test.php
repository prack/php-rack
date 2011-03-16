<?php

// TODO: Document!
class Prack_ConditionalGetTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * It should set a 304 status and truncate body when If-Modified-Since hits
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_set_a_304_status_and_truncate_body_when_If_Modified_Since_hits()
	{
		$timestamp = Prb::_Time()->httpdate();
		$middleware_app = Prack_ConditionalGet::with(
		  new Prack_Test_Echo(
		    Prb::_Numeric( 200 ),
		    Prb::_Hash( array( 'Last-Modified' => $timestamp ) ),
		    Prb::_Array( array( Prb::_String( 'TEST' ) ) )
		  )
		);
		
		$response = Prack_Mock_Request::with( $middleware_app )
		  ->get( Prb::_String( '/' ), Prb::_Hash( array( 'HTTP_IF_MODIFIED_SINCE' => $timestamp ) ) );
		
		$this->assertEquals( 304, $response->getStatus()->raw() );
		$this->assertTrue( $response->getBody()->isEmpty() );
	} // It should set a 304 status and truncate body when If-Modified-Since hits
	
	/**
	 * It should set a 304 status and truncate body when If-None-Match hits
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_set_a_304_status_and_truncate_body_when_If_None_Match_hits()
	{
		$middleware_app = Prack_ConditionalGet::with(
		  new Prack_Test_Echo(
		    Prb::_Numeric( 200 ),
		    Prb::_Hash( array( 'Etag' => Prb::_String( '1234' ) ) ),
		    Prb::_Array( array( Prb::_String( 'TEST' ) ) )
		  )
		);
		
		$response = Prack_Mock_Request::with( $middleware_app )
		  ->get( Prb::_String( '/' ), Prb::_Hash( array( 'HTTP_IF_NONE_MATCH' => Prb::_String( '1234' ) ) ) );
		
		$this->assertEquals( 304, $response->getStatus()->raw() );
		$this->assertTrue( $response->getBody()->isEmpty() );
	} // It should set a 304 status and truncate body when If-None-Match hits
	
	
	/**
	 * It should not affect non-GET/HEAD requests
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_affect_non_GET_HEAD_requests()
	{
		$middleware_app = Prack_ConditionalGet::with(
		  new Prack_Test_Echo(
		    Prb::_Numeric( 200 ),
		    Prb::_Hash( array( 'Etag' => Prb::_String( '1234' ) ) ),
		    Prb::_Array( array( Prb::_String( 'TEST' ) ) )
		  )
		);
		
		$response = Prack_Mock_Request::with( $middleware_app )
		  ->post( Prb::_String( '/' ), Prb::_Hash( array( 'HTTP_IF_NONE_MATCH' => Prb::_String( '1234' ) ) ) );
		
		$this->assertEquals( 200, $response->getStatus()->raw() );
		$this->assertEquals( 'TEST', $response->getBody()->raw() );
	} // It should not affect non-GET/HEAD requests
}