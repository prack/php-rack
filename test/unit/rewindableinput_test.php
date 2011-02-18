<?php

// TODO: Document!
class Prack_RewindableInputTest extends PHPUnit_Framework_TestCase 
{
	private $callback_invocation_count;
	
	/**
	 * new instance should be made rewindable whenever a read operation method is invoked
	 * @author Joshua Morris
	 * @test
	 */
	public function new_instance_should_be_made_rewindable_whenever_a_read_operation_method_is_invoked()
	{
		$methods_to_test = array( 'gets', 'read', 'each', 'rewind' );
		
		foreach ( $methods_to_test as $method )
		{
			$stream = fopen( 'php://memory', 'x+b');
			
			fwrite( $stream, TestHelper::gibberish() );
			rewind( $stream );
			
			$rewindable_input = new Prack_RewindableInput( $stream );
			$rewindable_input->$method();
			
			$this->assertNotNull( $rewindable_input->getRewindableIO() );
			
			$rewindable_input->close();
			
			fclose( $stream );
		}
	} // new instance should be made rewindable whenever a read operation method is invoked
	
	/**
	 * instance method each should invoke the specified callback for each line in stream
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_each_should_invoke_the_specified_callback_for_each_line_in_stream()
	{
		$stream = fopen( 'php://memory', 'x+b');
		
		fwrite( $stream, "Line 1\n" );
		fwrite( $stream, "Line 2\n" );
		fwrite( $stream, "Line 3\n" );
		fwrite( $stream, "Line 4\n" );
		
		rewind( $stream );
		
		$this->callback_invocation_count = 0;
		
		$rewindable_input = new Prack_RewindableInput( $stream );
		$rewindable_input->each( array( $this, 'eachCallback' ) );
		$rewindable_input->close();
		
		$this->assertTrue( $this->callback_invocation_count == 4 );
		fclose( $stream );
	} // instance method each should invoke the specified callback for each line in stream
	
	/**
	 * Callback used by above test for each instance method of Prack_RewindableInput
	 */
	public function eachCallback( $item )
	{
		$this->callback_invocation_count += 1;
	}
	
	/**
	 * instance method getLength should return the correct size of bytes previously written to rewindable stream
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_getLength_should_return_the_correct_size_of_bytes_previously_written_to_rewindable_stream()
	{
		$stream = fopen( 'php://memory', 'x+b');
		
		fwrite( $stream, TestHelper::gibberish() );
		$length = ftell( $stream );
		
		rewind( $stream );
		
		$rewindable_input = new Prack_RewindableInput( $stream );
		$this->assertEquals( $length, $rewindable_input->getLength() );
		
		$rewindable_input->close();
		fclose( $stream );
	} // instance method getLength should return the correct size of bytes previously written to rewindable stream
}