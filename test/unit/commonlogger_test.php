<?php

// TODO: Document!
class Prack_CommonLoggerTest extends PHPUnit_Framework_TestCase 
{
	// TODO: Document!
	static function obj()
	{
		return 'foobar';
	}
	
	// TODO: Document!
	static function length()
	{
		return strlen( self::obj() );
	}
	
	// TODO: Document!
	static function app()
	{
		return new Prack_Test_Echo(
		  200,
		  array( 'Content-Type' => 'text/html', 'Content-Length' => self::length() ),
		  array( self::obj() )
		);
	}
	
	// TODO: Document!
	static function appWithoutLength()
	{
		return new Prack_Test_Echo(
		  200,
		  array( 'Content-Type' => 'text/html' ),
		  array()
		);
	}
	
	// TODO: Document!
	static function appWithZeroLength()
	{
		return new Prack_Test_Echo(
		  200,
		  array( 'Content-Type' => 'text/html', 'Content-Length' => '0' ),
		  array()
		);
	}

	/**
	 * It should log to rack.errors by default
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_log_to_rack_errors_by_default()
	{
		$response = Prack_Mock_Request::with(
		  Prack_CommonLogger::with( self::app() ) )->get( '/' );
		
		$expected_length = self::length();
		$this->assertFalse( '' === $response->getErrors() );
		$this->assertRegExp( "/\"GET \/ \" 200 {$expected_length}/", $response->getErrors() );
	} // It should log to rack.errors by default
	
	/**
	 * It should log to anything with write method
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_log_to_anything_with_write_method()
	{
		$log = Prb_IO::withString();
		$response = Prack_Mock_Request::with(
		  Prack_CommonLogger::with( self::app(), $log ) )->get( '/' );
		
		$expected_length = self::length();
		$this->assertRegExp( "/\"GET \/ \" 200 {$expected_length}/", $log->string() );
	} // It should log to anything with write method
	
	/**
	 * It should log a dash for content length if header is missing
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_log_a_dash_for_content_length_if_header_is_missing()
	{
		$response = Prack_Mock_Request::with(
		  Prack_CommonLogger::with( self::appWithoutLength() ) )->get( '/' );
		
		$this->assertFalse( '' === $response->getErrors() );
		$this->assertRegExp( "/\"GET \/ \" 200 - /", $response->getErrors() );
	} // It should log a dash for content length if header is zero
	
	/**
	 * It should log a dash for content length if header is missing
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_log_a_dash_for_content_length_if_header_is_zero()
	{
		$response = Prack_Mock_Request::with( Prack_CommonLogger::with( self::appWithZeroLength() ) )
		  ->get( '/' );
		
		$this->assertFalse( '' === $response->getErrors() );
		$this->assertRegExp( "/\"GET \/ \" 200 - /", $response->getErrors() );
	} // It should log a dash for content length if header is zero
}