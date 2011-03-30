<?php

// TODO: Document!
class Prack_ContentLengthTest_VariableLength
  implements Prb_I_Enumerable
{
	private $function;
	
	// TODO: Document!
	public function __construct( $function )
	{
		$this->function = $function;
	}
	
	// TODO: Document!
	public function each( $callback )
	{
		call_user_func( $this->function, $callback );
	}
}

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
		$middleware_app = new Prack_Test_Echo( 200, array( 'Content-Type' => 'text/plain' ), 'Hello, World!' );
		$env = array();
		$response = Prack_ContentLength::with( $middleware_app )->call( $env );
		$this->assertEquals( '13', $response[ 1 ][ 'Content-Length' ] );
	} // It should set Content-Length on String bodies if none is set
	
	/**
	 * It should set Content-Length on Array bodies if none is set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_set_Content_Length_on_Array_bodies_if_none_is_set()
	{
		$middleware_app = new Prack_Test_Echo( 200, array( 'Content-Type' => 'text/plain' ), array( 'Hello, World!' ) );
		$env = array();
		$response = Prack_ContentLength::with( $middleware_app )->call( $env );
		$this->assertEquals( '13', $response[ 1 ][ 'Content-Length' ] );
	} // It should set Content-Length on Array bodies if none is set
	
	/**
	 * It should not set Content-Length on variable length bodies
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_set_Content_Length_on_variable_length_bodies()
	{
		$function       = create_function( '', 'return "Hello World";' );
		$body           = new Prack_ContentLengthTest_VariableLength( $function );
		$middleware_app = new Prack_Test_Echo( 200, array( 'Content-Type' => 'text/plain' ), $body );
		
		$env = array();
		$response = Prack_ContentLength::with( $middleware_app )->call( $env );
		
		$this->assertNull( @$response[ 1 ][ 'Content-Length' ] );
	} // It should not set Content-Length on variable length bodies
	
	/**
	 * It should not change Content-Length if it is already set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_change_Content_Length_if_it_is_already_set()
	{
		$middleware_app = new Prack_Test_Echo( 200, array( 'Content-Type' => 'text/plain', 'Content-Length' => '1' ), array() );
		$env = array();
		$response = Prack_ContentLength::with( $middleware_app )->call( $env );
		$this->assertEquals( '1', $response[ 1 ][ 'Content-Length' ] );
	} // It should not change Content-Length if it is already set
	
	/**
	 * It should not set Content-Length on 304 responses
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_set_Content_Length_on_304_responses()
	{
		$middleware_app = new Prack_Test_Echo( 304, array( 'Content-Type' => 'text/plain' ), array() );
		$env = array();
		$response = Prack_ContentLength::with( $middleware_app )->call( $env );
		$this->assertNull( @$response[ 1 ][ 'Content-Length' ] );
	} // It should not set Content-Length on 304 responses
	
	/**
	 * It should not set Content-Length when Transfer-Encoding is chunked
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_set_Content_Length_when_Transfer_Encoding_is_chunked()
	{
		$middleware_app = new Prack_Test_Echo( 304, array( 'Transfer-Encoding' => 'chunked' ), array() );
		$env = array();
		$response = Prack_ContentLength::with( $middleware_app )->call( $env );
		$this->assertNull( @$response[ 1 ][ 'Content-Length' ] );
	} // It should not set Content-Length when Transfer-Encoding is chunked
}