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
}