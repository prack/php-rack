<?php

// TODO: Document!
class Prack_LoggerTest extends PHPUnit_Framework_TestCase
{
	/**
	 * It should log to rack.errors
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_log_to_rack_errors()
	{
		$middleware_app = new Prack_Test_Echo(
			200, array( 'Content-Type' ), array( 'Hello, World!' ),
			' $logger = $env[ "rack.logger" ];
			  $logger->debug( "Created logger" );
			  $logger->info( "Program started" );
			  $logger->warn( "Nothing to do!" ); '
		);
		
		$errors = Prb_IO::withString();
		$env    = array( 'rack.errors' => $errors );
		
		Prack_Logger::with( $middleware_app )->call( $env );
		
		$this->assertRegExp( '/INFO -- : Program started/', $errors->string() );
		$this->assertRegExp( '/WARN -- : Nothing to do/'  , $errors->string() );
	} // It should log to rack.errors
}
