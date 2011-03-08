<?php


class Prack_Wrapper_StringTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * It should upcase properly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_upcase_properly()
	{
		$wrapper = Prack::_String( 'foO' );
		$this->assertEquals( 'FOO', $wrapper->upcase()->toN() );
	} // It should upcase properly
	
	/**
	 * It should downcase properly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_downcase_properly()
	{
		$wrapper = Prack::_String( 'FoO' );
		$this->assertEquals( 'foo', $wrapper->downcase()->toN() );
	} // It should downcase properly
}