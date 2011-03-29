<?php

// TODO: Document!
class Prack_ETagTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * It should set ETag if none is set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_set_ETag_if_none_is_set()
	{
		$middleware_app = new Prack_Test_Echo( 200, array( 'Content-Type' => 'text/plain' ), array( 'Hello, World!' ) );
		$env = array();
		$response = Prack_ETag::with( $middleware_app )->call( $env );
		$this->assertEquals( "\"65a8e27d8879283831b664bd8b7f0ad4\"", @$response[ 1 ][ 'ETag' ] );
	} // It should set ETag if none is set
	
	/**
	 * It should not change ETag if it is already set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_change_ETag_if_it_is_already_set()
	{
		$middleware_app = new Prack_Test_Echo(
		  200,
		  array( 'Content-Type' => 'text/plain', 'ETag' => '"abc"' ),
		  array( 'Hello, World!' )
		);
		$env = array();
		$response = Prack_ETag::with( $middleware_app )->call( $env );
		$this->assertEquals( "\"abc\"", @$response[ 1 ][ 'ETag' ] );
	} // It should not change ETag if it is already set
}