<?php

// TODO: Document!
class Prack_ShowExceptionsTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * It catches exceptions
	 * @author Joshua Morris
	 * @test
	 */
	public function It_catches_exceptions()
	{
		$exception_raising_middleware = new Prack_Test_Echo();
		$exception_raising_middleware->setEval( 'throw new RuntimeException();' );
		
		$mock_request = new Prack_Mock_Request(
			new Prack_ShowExceptions( $exception_raising_middleware )
		);
		
		// Implicit test: should not encounter an exception.
		$mock_response = $mock_request->get( Prack::_String( '/' ) );
		
		$this->assertTrue( $mock_response->isServerError() );
		$this->assertEquals( 500, $mock_response->getStatus() );
		
		$this->assertTrue( $mock_response->match( '/RuntimeException/' ) );
		$this->assertTrue( $mock_response->match( '/ShowExceptions/'   ) );
	} // It catches exceptions
}
