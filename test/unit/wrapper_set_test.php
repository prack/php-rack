<?php

// TODO: Document!
class Prack_Wrapper_SetTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * It should handle each
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_each()
	{
		$this->items = array();
		
		$callback = array( $this, 'addToItems' );
		$items    = array( 'foo', 'bar' );
		
		$wrapper = Prack::_Set( $items );
		$wrapper->each( $callback );
		
		$this->assertEquals( $items, $this->items );
	} // It should handle each
	
	/**
	 * This function is used as a callback for the above test.
	 */
	public function addToItems( $item )
	{
		array_push( $this->items, $item );
	}
}