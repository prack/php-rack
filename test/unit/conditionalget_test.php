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
		$timestamp = Prb::Time()->httpdate();
		$middleware_app = Prack_ConditionalGet::with(
		  new Prack_Test_Echo(
		    Prb::Num( 200 ),
		    Prb::Hsh( array( 'Last-Modified' => $timestamp ) ),
		    Prb::Ary( array( Prb::Str( 'TEST' ) ) )
		  )
		);
		
		$response = Prack_Mock_Request::with( $middleware_app )
		  ->get( Prb::Str( '/' ), Prb::Hsh( array( 'HTTP_IF_MODIFIED_SINCE' => $timestamp ) ) );
		
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
		    Prb::Num( 200 ),
		    Prb::Hsh( array( 'Etag' => Prb::Str( '1234' ) ) ),
		    Prb::Ary( array( Prb::Str( 'TEST' ) ) )
		  )
		);
		
		$response = Prack_Mock_Request::with( $middleware_app )
		  ->get( Prb::Str( '/' ), Prb::Hsh( array( 'HTTP_IF_NONE_MATCH' => Prb::Str( '1234' ) ) ) );
		
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
		    Prb::Num( 200 ),
		    Prb::Hsh( array( 'Etag' => Prb::Str( '1234' ) ) ),
		    Prb::Ary( array( Prb::Str( 'TEST' ) ) )
		  )
		);
		
		$response = Prack_Mock_Request::with( $middleware_app )
		  ->post( Prb::Str( '/' ), Prb::Hsh( array( 'HTTP_IF_NONE_MATCH' => Prb::Str( '1234' ) ) ) );
		
		$this->assertEquals( 200, $response->getStatus()->raw() );
		$this->assertEquals( 'TEST', $response->getBody()->raw() );
	} // It should not affect non-GET/HEAD requests
}