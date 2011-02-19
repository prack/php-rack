<?php

require_once join( DIRECTORY_SEPARATOR, array(dirname(__FILE__), '..', 'support', 'testhelper.php') );

// TODO: Document!
class Prack_Utils_IO_StringTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * It should throw an exception if the string is too big
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_throw_an_exception_if_the_string_is_too_big()
	{
		$max_length = Prack_Utils_IO_String::MAX_STRING_LENGTH;
		$gibberish  = TestHelper::gibberish( 4096 );
		$iterations = floor( (float)$max_length / (float)strlen( $gibberish ) ) + 1; // Just a bit over the limit!
		
		ob_start();
			for ( $i = 0; $i < $iterations; $i++ )
				echo $gibberish;
		$bigass_string = ob_get_contents();
		
		ob_end_clean();
		
		$this->setExpectedException( 'Prack_Error_Runtime_StringTooBigForStringIO' );
		new Prack_Utils_IO_String( $bigass_string );
	} // It should throw an exception if the string is too big
}