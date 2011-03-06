<?php

// TODO: Document!
class Prack_Utils_IO_StringTest extends PHPUnit_Framework_TestCase 
{
	private $string_io;
	private $items;
	
	/**
	 * Set up a IO instance before each test
	 * @author Joshua Morris
	 */
	function setUp()
	{
		$this->string_io = Prack_Utils_IO::withString( Prack::_String( 'hello world' ) );
	}
	
	/**
	 * Destroy the previously created IO instance after each test
	 * @author Joshua Morris
	 */
	function tearDown()
	{
		$this->string_io->close();
		$this->string_io = null;
	}
	
	/**
	 * It should throw an exception if the string is too big
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_throw_an_exception_if_the_string_is_too_big()
	{
		$max_length = Prack_Utils_IO_String::MAX_STRING_LENGTH;
		$gibberish  = TestHelper::gibberish( 4096 );
		$iterations = floor( (float)$max_length / (float)( $gibberish->length() ) + 1 ); // Just a bit over the limit!
		
		ob_start();
			for ( $i = 0; $i < $iterations; $i++ )
				echo $gibberish->toN();
		$bigass_string = Prack::_String( ob_get_contents() );
		
		ob_end_clean();
		
		$this->setExpectedException( 'Prack_Error_Runtime_StringTooBigForStringIO' );
		new Prack_Utils_IO_String( $bigass_string );
	} // It should throw an exception if the string is too big
	
	/**
	 * It should be creatable without a string
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_creatable_without_a_string()
	{
		$this->string_io = Prack_Utils_IO::withString();
		$this->assertEquals( '', $this->string_io->read()->toN() );
	} // It should be creatable without a string
	
	/**
	 * It should be able to handle read
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_able_to_handle_read()
	{
		$this->assertEquals( 'hello world', $this->string_io->read()->toN() );
		
		$this->string_io->close();
		try
		{
			$this->string_io->read();
		} 
		catch ( Prack_Error_IO $e )
		{
			return;
		}
		
		$this->fail( 'Expected exception from read() on read-closed stream.' );
	} // It should be able to handle read
	
	/**
	 * It should be able to handle read( null )
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_able_to_handle_read_with_null()
	{
		$this->assertEquals( 'hello world', $this->string_io->read( null )->toN() );
	} // It should be able to handle read( null )
	
	/**
	 * It should be able to handle read( length )
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_able_to_handle_read_with_length()
	{
		$this->assertEquals( 'h', $this->string_io->read( 1 )->toN() );
	} // It should be able to handle read( length )
	
	/**
	 * It should be able to handle read( length, buffer )
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_able_to_handle_read_with_length_and_buffer()
	{
		$buffer = Prack::_String();
		$result = $this->string_io->read( 1, $buffer );
		$this->assertEquals( 'h', $result->toN() );
		$this->assertSame( $buffer, $result );
	} // It should be able to handle read( length, buffer )
	
	/**
	 * It should be able to handle read( null, buffer )
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_able_to_handle_read_with_null_and_buffer()
	{
		$buffer = Prack::_String();
		$result = $this->string_io->read( null, $buffer );
		$this->assertEquals( 'hello world', $result->toN() );
		$this->assertSame( $buffer, $result );
	} // It should be able to handle read( null, buffer )
	
	/**
	 * It should rewind to the beginning when rewind is called
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_rewind_to_the_beginning_when_rewind_is_called()
	{
		$this->string_io->read( 1 );
		$this->string_io->rewind();
		$this->assertEquals( 'hello world', $this->string_io->read()->toN() );
	} // It should rewind to the beginning when rewind is called
	
	/**
	 * It should be able to handle gets
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_able_to_handle_gets()
	{
		$this->assertEquals( 'hello world', $this->string_io->gets()->toN() );
		
		$this->string_io->close();
		try
		{
			$this->string_io->gets();
		} 
		catch ( Prack_Error_IO $e )
		{
			return;
		}
		
		$this->fail( 'Expected exception from read() on read-closed stream.' );
	} // It should be able to handle gets
	
	/**
	 * It should be able to handle each
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_able_to_handle_each()
	{
		$callback = array( $this, 'addToItems' );
		
		$this->items = Prack::_Array();
		
		$this->string_io->each( $callback );
		$this->assertEquals( array( Prack::_String( 'hello world' ) ), $this->items->toN() );
		$this->assertEquals( count( $this->items ), $this->string_io->getLineNo() );
		
		$this->string_io->close();
		try
		{
			$this->string_io->each( $callback );
		} 
		catch ( Prack_Error_IO $e )
		{
			return;
		}
		
		$this->fail( 'Expected exception from each() on read-closed stream.' );
	} // It should be able to handle each
	
	/**
	 * This function is used by the above test as a callback
	 */
	public function addToItems( $item )
	{
		$this->items->concat( $item );
	}
	
	/**
	 * It should throw an exception on each if callback is not callable
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_throw_an_exception_on_each_if_callback_is_not_callable()
	{
		$callback = array( $this, 'lolnowai' );
		$this->setExpectedException( 'Prack_Error_Callback' );
		$this->string_io->each( $callback );
	} // It should throw an exception on each if callback is not callable
	
	/**
	 * It should handle read on really big strings
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_read_on_really_big_strings()
	{
		$string    = TestHelper::gibberish( Prack_Utils_IO::CHUNK_SIZE * 2 );
		$string_io = Prack_Utils_IO::withString( $string );
		$this->assertEquals( $string, $string_io->read() );
	} // It should handle read on really big strings
	
	/**
	 * It should handle write
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_write()
	{
		$this->string_io->write( Prack::_String( 'EASTER EGG FOR JASON' ) );
		$this->string_io->rewind();
		
		$this->assertEquals( 'EASTER EGG FOR JASON', $this->string_io->read()->toN() );
		
		$this->string_io->close();
		try
		{
			$this->string_io->write( Prack::_String( 'denied' ) );
		} 
		catch ( Prack_Error_IO $e )
		{
			return;
		}
		
		$this->fail( 'Expected exception from write() on read-closed stream.' );
	} // It should handle write
	
	/**
	 * It should be possible to call close immediately
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_possible_to_call_close_immediately()
	{
		$this->string_io->close();
	} // It should be possible to call close immediately
	
	/**
	 * It should be possible to call close multiple times
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_possible_to_call_close_multiple_times()
	{
		$this->string_io->close();
		$this->string_io->close();
	} // It should be possible to call close multiple times
}