<?php

// TODO: Document!
class Prack_RewindableInputTest extends PHPUnit_Framework_TestCase 
{
	private $rewindable_input;
	private $callback_invocation_count;
	
	/**
	 * Set up a IO instance before each test
	 * @author Joshua Morris
	 */
	function setUp()
	{
		$this->rewindable_input = new Prack_RewindableInput( Prack_Utils_IO::withString( 'hello world' ) );
	}
	
	/**
	 * Destroy the previously created IO instance after each test
	 */
	function tearDown()
	{
		$this->rewindable_input->close();
		$this->rewindable_input = null;
	}
	
	/**
	 * It should be creatable without a string
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_creatable_without_a_string()
	{
		$this->rewindable_input = Prack_Utils_IO::withString( '' );
		$this->assertEquals( '', $this->rewindable_input->read() );
	} // It should be creatable without a string
	
	/**
	 * It should be able to handle read
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_able_to_handle_read()
	{
		$this->assertEquals( 'hello world', $this->rewindable_input->read() );
		
		$this->rewindable_input->close();
		try
		{
			$this->rewindable_input->read();
		} 
		catch ( Prack_Error_Runtime_IOError $e )
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
		$this->assertEquals( 'hello world', $this->rewindable_input->read( null ) );
	} // It should be able to handle read( null )
	
	/**
	 * It should be able to handle read( length )
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_able_to_handle_read_with_length()
	{
		$this->assertEquals( 'h', $this->rewindable_input->read( 1 ) );
	} // It should be able to handle read( length )
	
	/**
	 * It should be able to handle read( length, buffer )
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_able_to_handle_read_with_length_and_buffer()
	{
		$buffer = '';
		$result = $this->rewindable_input->read( 1, $buffer );
		$this->assertEquals( 'h', $result );
		$this->assertSame( $buffer, $result );
	} // It should be able to handle read( length, buffer )
	
	/**
	 * It should be able to handle read( null, buffer )
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_able_to_handle_read_with_null_and_buffer()
	{
		$buffer = '';
		$result = $this->rewindable_input->read( null, $buffer );
		$this->assertEquals( 'hello world', $result );
		$this->assertSame( $buffer, $result );
	} // It should be able to handle read( null, buffer )
	
	/**
	 * It should rewind to the beginning when rewind is called
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_rewind_to_the_beginning_when_rewind_is_called()
	{
		$this->rewindable_input->read( 1 );
		$this->rewindable_input->rewind();
		$this->assertEquals( 'hello world', $this->rewindable_input->read() );
	} // It should rewind to the beginning when rewind is called
	
	/**
	 * It should be able to handle gets
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_able_to_handle_gets()
	{
		$this->assertEquals( 'hello world', $this->rewindable_input->gets() );
		
		$this->rewindable_input->close();
		try
		{
			$this->rewindable_input->gets();
		} 
		catch ( Prack_Error_Runtime_IOError $e )
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
		$callback = array( $this, 'eachCallback' );
		
		$this->callback_state = array();
		$this->rewindable_input->each( $callback );
		$this->assertEquals( array( 'hello world' ), $this->callback_state );
		
		$this->rewindable_input->close();
		try
		{
			$this->rewindable_input->each( $callback );
		} 
		catch ( Prack_Error_Runtime_IOError $e )
		{
			return;
		}
		
		$this->fail( 'Expected exception from read() on read-closed stream.' );
	} // It should be able to handle each
	
	/**
	 * This function is used by the above test as a callback
	 */
	public function eachCallback( $item )
	{
		array_push( $this->callback_state, $item );
	}
	
	/**
	 * It should handle really big strings
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_really_big_strings()
	{
		$string = TestHelper::gibberish( Prack_Utils_IO::CHUNK_SIZE * 2 );
		$rewindable_input    = Prack_Utils_IO::withString( $string );
		$this->assertEquals( $string, $rewindable_input->read() );
	} // It should handle really big strings
	
	/**
	 * It should be possible to call close when no data has been buffered yet
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_possible_to_call_close_when_no_data_has_been_buffered_yet()
	{
		$this->rewindable_input->close();
	} // It should be possible to call close when no data has been buffered yet
	
	/**
	 * It should be possible to call close multiple times
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_possible_to_call_close_multiple_times()
	{
		$this->rewindable_input->close();
		$this->rewindable_input->close();
	} // It should be possible to call close multiple times
	
	/**
	 * It should not buffer into a Prack_Utils_IO_Tempfile if no data has been read yet
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_buffer_into_a_Prack_Utils_IO_Tempfile_if_no_data_has_been_read_yet()
	{
		$this->assertNull( $this->rewindable_input->getRewindableIO() );
	} // It should not buffer into a Prack_Utils_IO_Tempfile if no data has been read yet
	
	/**
	 * It should buffer into a Prack_Utils_IO_Tempfile when data has been consumed for the first time
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_buffer_into_a_Prack_Utils_IO_Tempfile_when_data_has_been_consumed_for_the_first_time()
	{
		$this->rewindable_input->read( 1 );
		$tempfile = $this->rewindable_input->getRewindableIO();
		$this->assertNotNull( $tempfile );
		$this->rewindable_input->read( 1 );
		$tempfile2 = $this->rewindable_input->getRewindableIO();
		$this->assertEquals( $tempfile->getPath(), $tempfile2->getPath() );
	} // It should buffer into a Prack_Utils_IO_Tempfile when data has been consumed for the first time
	
	/**
	 * It should close the underlying tempfile upon calling close
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_close_the_underlying_tempfile_upon_calling_close()
	{
		$this->rewindable_input->read( 1 );
		$tempfile = $this->rewindable_input->getRewindableIO();
		$this->rewindable_input->close();
		$this->assertTrue( $tempfile->isClosed() );
	} // It should close the underlying tempfile upon calling close
}