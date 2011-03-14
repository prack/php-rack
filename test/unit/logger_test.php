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
			200,
			Prb::_Hash( array( 'Content-Type' ) ),
			Prb::_Array( array( Prb::_String( 'Hello, World!' ) ) ),
			'
			  $logger = $env->get( "rack.logger" );
			  $logger->debug( "Created logger" );
			  $logger->info( "Program started" );
			  $logger->warn( "Nothing to do!" );
			'
		);
	
		$errors = Prb_IO::withString();
		Prack_Logger::with( $middleware_app )->call(
		  Prb::_Hash( array( 'rack.errors' => $errors ) )
		);
		
		$this->assertTrue( $errors->string()->match( '/INFO -- : Program started/' ) );
		$this->assertTrue( $errors->string()->match( '/WARN -- : Nothing to do/'   ) );
	} // It should log to rack.errors
}
