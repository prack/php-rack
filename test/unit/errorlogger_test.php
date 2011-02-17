<?php

require_once join( DIRECTORY_SEPARATOR, array(dirname(__FILE__), '..', 'support', 'testhelper.php') );

// TODO: Document!
class Loggable
{
	function __toString() { return "as string"; }
}

// TODO: Document!
class Unloggable
{
	// Does not implement __toString()
}

// TODO: Document!
class Prack_ErrorLoggerTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * instance method close should always throw an exception
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_close_should_always_throw_an_exception()
	{
		$this->setExpectedException( 'Prack_Error_ErrorLogger_StreamCannotBeClosed' );
		$error_logger = Prack_ErrorLogger::standard();
		$error_logger->close();
	} // instance method close should always throw an exception
	
	/**
	 * instance method puts should write a string representation of first argument to the specified stream
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_puts_should_write_a_string_representation_of_first_argument_to_the_specified_stream()
	{
		$stream       = tmpfile();
		$error_logger = new Prack_ErrorLogger( $stream );
		$loggable     = new Loggable();
		
		$error_logger->puts( $loggable );
		
		rewind( $stream );
		
		$this->assertEquals( (string)$loggable, fgets( $stream ) );
	} // instance method puts should write a string representation of first argument to the specified stream
	
	/**
	 * instance method puts should throw an exception unless object implements magic method toString
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_puts_should_throw_an_exception_unless_object_implements_magic_method_toString()
	{
		$this->setExpectedException( 'Prack_Error_ErrorLogger_UnloggableValue' );
		
		$error_logger = Prack_ErrorLogger::standard();
		$unloggable   = new Unloggable();
		
		$error_logger->puts( $unloggable );
	} // instance method puts should throw an exception unless object implements magic method toString
	
	/**
	 * instance method write should write its value without coercing to string
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_write_should_write_its_value_without_coercing_to_string()
	{
		$stream       = tmpfile();
		$error_logger = new Prack_ErrorLogger( $stream );
		$numeric      = 4;
		
		$error_logger->write( $numeric );
		
		rewind( $stream );
		
		$this->assertEquals( $numeric, (int)fgets( $stream ) );
	} // instance method write should write its value without coercing to string
	
	/**
	 * instance method flush should flush the contained stream
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_flush_should_flush_the_contained_stream()
	{
		// This is basically untestable, but it works per PHP... or we're in trouble.
		// So, essentially, this is for code coverage purpose.
		$stream       = tmpfile();
		$error_logger = new Prack_ErrorLogger( $stream );
		$gibberish    = TestHelper::gibberish( 12 * 1024 );
		
		$error_logger->write( $gibberish );
		$error_logger->flush();
		
		rewind( $stream );
		
		$this->assertEquals( $gibberish, fgets( $stream ) );
	} // instance method flush should flush the contained stream
}