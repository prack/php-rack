<?php

// TODO: Document!
class Prack_ContentLengthTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * It should set Content-Length on String bodies if none is set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_set_Content_Length_on_String_bodies_if_none_is_set()
	{
		$middleware_app = new Prack_Test_Echo(
		  Prb::Num( 200 ),
		  Prb::Hsh( array( 'Content-Type' => Prb::Str( 'text/plain' ) ) ),
		  Prb::Str( 'Hello, World!' )
		);
		
		$response = Prack_ContentLength::with( $middleware_app )->call( Prb::Hsh() );
		$this->assertEquals( '13', $response->get( 1 )->get( 'Content-Length' )->raw() );
	} // It should set Content-Length on String bodies if none is set
	
	/**
	 * It should set Content-Length on Array bodies if none is set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_set_Content_Length_on_Array_bodies_if_none_is_set()
	{
		$middleware_app = new Prack_Test_Echo(
		  Prb::Num( 200 ),
		  Prb::Hsh( array( 'Content-Type' => Prb::Str( 'text/plain' ) ) ),
		  Prb::Ary( array( Prb::Str( 'Hello, World!' ) ) )
		);
		
		$response = Prack_ContentLength::with( $middleware_app )->call( Prb::Hsh() );
		$this->assertEquals( '13', $response->get( 1 )->get( 'Content-Length' )->raw() );
	} // It should set Content-Length on Array bodies if none is set
	
	/**
	 * It should not set Content-Length on variable length bodies
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_set_Content_Length_on_variable_length_bodies()
	{
		$middleware_app = new Prack_Test_Echo(
		  Prb::Num( 200 ),
		  Prb::Hsh( array( 'Content-Type' => Prb::Str( 'text/plain' ) ) ),
		  new stdClass() // doesn't respond to toStr or toAry
		);
		
		$response = Prack_ContentLength::with( $middleware_app )->call( Prb::Hsh() );
		$this->assertNull( $response->get( 1 )->get( 'Content-Length' ) );
	} // It should not set Content-Length on variable length bodies
	
	/**
	 * It should not change Content-Length if it is already set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_change_Content_Length_if_it_is_already_set()
	{
		$middleware_app = new Prack_Test_Echo(
		  Prb::Num( 200 ),
		  Prb::Hsh( array(
		    'Content-Type'   => Prb::Str( 'text/plain' ),
		    'Content-Length' => Prb::Str( '1'          )
		  ) ),
		  Prb::Ary()
		);
		
		$response = Prack_ContentLength::with( $middleware_app )->call( Prb::Hsh() );
		$this->assertEquals( '1', $response->get( 1 )->get( 'Content-Length' )->raw() );
	} // It should not change Content-Length if it is already set
	
	/**
	 * It should not set Content-Length on 304 responses
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_set_Content_Length_on_304_responses()
	{
		$middleware_app = new Prack_Test_Echo(
		  Prb::Num( 304 ),
		  Prb::Hsh( array( 'Content-Type' => Prb::Str( 'text/plain' ) ) ),
		  Prb::Ary()
		);
		
		$response = Prack_ContentLength::with( $middleware_app )->call( Prb::Hsh() );
		$this->assertNull( $response->get( 1 )->get( 'Content-Length' ) );
	} // It should not set Content-Length on 304 responses
	
	/**
	 * It should not set Content-Length when Transfer-Encoding is chunked
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_set_Content_Length_when_Transfer_Encoding_is_chunked()
	{
		$middleware_app = new Prack_Test_Echo(
		  Prb::Num( 304 ),
		  Prb::Hsh( array( 'Transfer-Encoding' => Prb::Str( 'chunked' ) ) ),
		  Prb::Ary()
		);
		
		$response = Prack_ContentLength::with( $middleware_app )->call( Prb::Hsh() );
		$this->assertNull( $response->get( 1 )->get( 'Content-Length' ) );
	} // It should not set Content-Length when Transfer-Encoding is chunked
}