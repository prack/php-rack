<?php

// TODO: Document!
class Prack_NullLoggerTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * It should act as a noop logger
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_act_as_a_noop_logger()
	{
		$middleware_app = new Prack_Test_Echo(
			200,
			array( 'Content-Type' => 'text/plain' ),
			array( 'Hello, World!' ),
			'$env[ \'rack.logger\' ]->warn( \'b00m\' );'
		);
		
		$logger = Prack_NullLogger::with( $middleware_app );
		$env    = array();
		
		// Implicit test: should not throw exception.
		$logger->call( $env );
	} // It should act as a noop logger
}
