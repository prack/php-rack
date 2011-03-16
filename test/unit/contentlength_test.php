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
		  Prb::_Numeric( 200 ),
		  Prb::_Hash( array( 'Content-Type' => Prb::_String( 'text/plain' ) ) ),
		  Prb::_String( 'Hello, World!' )
		);
		
		$response = Prack_ContentLength::with( $middleware_app )->call( Prb::_Hash() );
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
		  Prb::_Numeric( 200 ),
		  Prb::_Hash( array( 'Content-Type' => Prb::_String( 'text/plain' ) ) ),
		  Prb::_Array( array( Prb::_String( 'Hello, World!' ) ) )
		);
		
		$response = Prack_ContentLength::with( $middleware_app )->call( Prb::_Hash() );
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
		  Prb::_Numeric( 200 ),
		  Prb::_Hash( array( 'Content-Type' => Prb::_String( 'text/plain' ) ) ),
		  new stdClass() // doesn't respond to toStr or toAry
		);
		
		$response = Prack_ContentLength::with( $middleware_app )->call( Prb::_Hash() );
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
		  Prb::_Numeric( 200 ),
		  Prb::_Hash( array(
		    'Content-Type'   => Prb::_String( 'text/plain' ),
		    'Content-Length' => Prb::_String( '1'          )
		  ) ),
		  Prb::_Array()
		);
		
		$response = Prack_ContentLength::with( $middleware_app )->call( Prb::_Hash() );
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
		  Prb::_Numeric( 304 ),
		  Prb::_Hash( array( 'Content-Type' => Prb::_String( 'text/plain' ) ) ),
		  Prb::_Array()
		);
		
		$response = Prack_ContentLength::with( $middleware_app )->call( Prb::_Hash() );
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
		  Prb::_Numeric( 304 ),
		  Prb::_Hash( array( 'Transfer-Encoding' => Prb::_String( 'chunked' ) ) ),
		  Prb::_Array()
		);
		
		$response = Prack_ContentLength::with( $middleware_app )->call( Prb::_Hash() );
		$this->assertNull( $response->get( 1 )->get( 'Content-Length' ) );
	} // It should not set Content-Length when Transfer-Encoding is chunked
}