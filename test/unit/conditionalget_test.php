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
		  new Prack_Test_Echo( 200, array( 'Last-Modified' => $timestamp ), array( 'TEST' ) ) );
		
		$response = Prack_Mock_Request::with( $middleware_app )->get( '/', array( 'HTTP_IF_MODIFIED_SINCE' => $timestamp ) );
		$this->assertEquals( 304, $response->getStatus() );
		$this->assertEquals( '', $response->getBody() );
	} // It should set a 304 status and truncate body when If-Modified-Since hits
	
	/**
	 * It should set a 304 status and truncate body when If-None-Match hits
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_set_a_304_status_and_truncate_body_when_If_None_Match_hits()
	{
		$middleware_app = Prack_ConditionalGet::with(
		  new Prack_Test_Echo( 200, array( 'Etag' => '1234' ), array( 'TEST' ) ) );
		
		$response = Prack_Mock_Request::with( $middleware_app )->get( '/', array( 'HTTP_IF_NONE_MATCH' => '1234' ) );
		$this->assertEquals( 304, $response->getStatus() );
		$this->assertEquals( '', $response->getBody() );
	} // It should set a 304 status and truncate body when If-None-Match hits
	
	
	/**
	 * It should not affect non-GET/HEAD requests
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_affect_non_GET_HEAD_requests()
	{
		$middleware_app = Prack_ConditionalGet::with(
		  new Prack_Test_Echo( 200, array( 'Etag' => '1234' ), array( 'TEST' ) ) );
		
		$response = Prack_Mock_Request::with( $middleware_app )->post( '/', array( 'HTTP_IF_NONE_MATCH' => '1234' ) );
		$this->assertEquals( 200, $response->getStatus() );
		$this->assertEquals( 'TEST', $response->getBody() );
	} // It should not affect non-GET/HEAD requests
}