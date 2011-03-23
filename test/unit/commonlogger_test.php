<?php

// TODO: Document!
class Prack_CommonLoggerTest extends PHPUnit_Framework_TestCase 
{
	// TODO: Document!
	static function obj()
	{
		return Prb::Str( 'foobar' );
	}
	
	// TODO: Document!
	static function length()
	{
		return Prack_CommonLoggerTest::obj()->length();
	}
	
	// TODO: Document!
	static function app()
	{
		return new Prack_Test_Echo(
		  Prb::Num( 200 ),
		  Prb::Hsh( array(
		    'Content-Type'   => Prb::Str( 'text/html' ),
		    'Content-Length' => Prb::Str( self::length() )
		  ) ),
		  Prb::Ary( array( self::obj() ) )
		);
	}
	
	// TODO: Document!
	static function appWithoutLength()
	{
		return new Prack_Test_Echo(
		  Prb::Num( 200 ),
		  Prb::Hsh( array(
		    'Content-Type'   => Prb::Str( 'text/html' ),
		  ) ),
		  Prb::Ary()
		);
	}
	
	// TODO: Document!
	static function appWithZeroLength()
	{
		return new Prack_Test_Echo(
		  Prb::Num( 200 ),
		  Prb::Hsh( array(
		    'Content-Type'   => Prb::Str( 'text/html' ),
		    'Content-Length' => Prb::Str( '0' )
		  ) ),
		  Prb::Ary()
		);
	}

	/**
	 * It should log to rack.errors by default
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_log_to_rack_errors_by_default()
	{
		$response = Prack_Mock_Request::with( Prack_CommonLogger::with( self::app() ) )
		  ->get( Prb::Str( '/' ) );
		$this->assertFalse( $response->getErrors()->isEmpty() );
		
		$expected_length = self::length();
		$this->assertTrue( $response->getErrors()->match( "/\"GET \/ \" 200 {$expected_length}/" ) );
	} // It should log to rack.errors by default
	
	/**
	 * It should log to anything with write method
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_log_to_anything_with_write_method()
	{
		$log = Prb_IO::withString();
		$response = Prack_Mock_Request::with( Prack_CommonLogger::with( self::app(), $log ) )
		  ->get( Prb::Str( '/' ) );
		
		$expected_length = self::length();
		$this->assertTrue( $log->string()->match( "/\"GET \/ \" 200 {$expected_length}/" ) );
	} // It should log to anything with write method
	
	/**
	 * It should log a dash for content length if header is missing
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_log_a_dash_for_content_length_if_header_is_missing()
	{
		$response = Prack_Mock_Request::with( Prack_CommonLogger::with( self::appWithoutLength() ) )
		  ->get( Prb::Str( '/' ) );
		
		$this->assertFalse( $response->getErrors()->isEmpty() );
		$this->assertTrue( $response->getErrors()->match( "/\"GET \/ \" 200 - /" ) );
	} // It should log a dash for content length if header is zero
	
	/**
	 * It should log a dash for content length if header is missing
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_log_a_dash_for_content_length_if_header_is_zero()
	{
		$response = Prack_Mock_Request::with( Prack_CommonLogger::with( self::appWithZeroLength() ) )
		  ->get( Prb::Str( '/' ) );
		
		$this->assertFalse( $response->getErrors()->isEmpty() );
		$this->assertTrue( $response->getErrors()->match( "/\"GET \/ \" 200 - /" ) );
	} // It should log a dash for content length if header is zero
}