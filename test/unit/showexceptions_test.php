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
		
		$request  = Prack_Mock_Request::with( new Prack_ShowExceptions( $exception_raising_middleware ) );
		$response = $request->get( '/' );
		
		$this->assertTrue( $response->isServerError() );
		$this->assertEquals( 500, $response->getStatus() );
		
		$this->assertTrue( $response->match( '/RuntimeException/' ) );
		$this->assertTrue( $response->match( '/ShowExceptions/'   ) );
	} // It catches exceptions
	
	/**
	 * It optionally handles PHP errors, rethrowing them as exceptions
	 * @author Joshua Morris
	 * @test
	 */
	public function It_optionally_handles_PHP_errors__rethrowing_them_as_exceptions()
	{
		$exception_raising_middleware = new Prack_Test_Echo();
		$exception_raising_middleware->setEval( '$lint = new Prack_Lint(); echo (string) $lint;' );
		
		$handled  = E_ERROR | E_WARNING;
		$request  = Prack_Mock_Request::with( new Prack_ShowExceptions( $exception_raising_middleware, $handled ) );
		$response = $request->get( '/' );
		
		$this->assertTrue( $response->isServerError() );
		$this->assertEquals( 500, $response->getStatus() );
		
		$this->assertTrue( $response->match( '/RuntimeException/' ) );
		$this->assertTrue( $response->match( '/ShowExceptions/'   ) );
	} // It optionally handles PHP errors, rethrowing them as exceptions
}
