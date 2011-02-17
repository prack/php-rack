<?php

class Prack_RewindableInputTest extends PHPUnit_Framework_TestCase 
{
	private $callback_invocation_count;
	
	
	public static function gibberish( $length = 128 )
	{
		$aZ09 = array_merge( range( 'A', 'Z' ), range( 'a', 'z' ),range( 0, 9 ) );
		$out  = '';
		for( $c=0; $c < $length; $c++ )
			$out .= (string)$aZ09[ mt_rand( 0, count( $aZ09 ) - 1 ) ];
		return $out;
	}
	
	
	/**
	 * New instance should be made rewindable whenever a read operation method is invoked
	 * @author Joshua Morris
	 * @test
	 */
	public function New_instance_should_be_made_rewindable_whenever_a_read_operation_method_is_invoked()
	{
		$methods_to_test = array( 'gets', 'read', 'each', 'rewind' );
		
		foreach ( $methods_to_test as $method )
		{
			$stream = tmpfile();
			
			fwrite( $stream, self::gibberish() );
			rewind( $stream );
			
			$rewindable_input = new Prack_RewindableInput( $stream );
			$rewindable_input->$method();
			
			$this->assertNotNull( $rewindable_input->getRewindableIO() );
			
			$rewindable_input->close();
			
			fclose( $stream );
		}
	} // New instance should be made rewindable whenever a read operation method is invoked
	
	
	/**
	 * Instance method each should invoke the specified callback for each line in stream
	 * @author Joshua Morris
	 * @test
	 */
	public function Instance_method_each_should_invoke_the_specified_callback_for_each_line_in_stream()
	{
		$stream = tmpfile();
		
		fwrite( $stream, "Line 1\n" );
		fwrite( $stream, "Line 2\n" );
		fwrite( $stream, "Line 3\n" );
		fwrite( $stream, "Line 4\n" );
		rewind( $stream );
		
		$rewindable_input = new Prack_RewindableInput( $stream );
		$rewindable_input->each( array( $this, 'eachCallback' ) );
		$rewindable_input->close();
		
		$this->callback_invocation_count = 0;
		
		$this->assertTrue( $this->callback_invocation_count == 4 );
	} // Instance method each should invoke the specified callback for each line in stream
	
	
	/**
	 * Callback used by above test for each instance method of Prack_RewindableInput
	 */
	public function eachCallback( $item )
	{
		$this->callback_invocation_count += 1;
	}
}