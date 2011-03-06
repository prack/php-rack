<?php

// TODO: Document!
class Prack_Wrapper_HashTest extends PHPUnit_Framework_TestCase 
{
	private $items;
	
	// TODO: Document!
	function setUp()
	{
		$this->items = array();
	}
	
	/**
	 * It should handle each
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_each()
	{
		$callback = array( $this, 'addToItems' );
		$items    = array( 'foo' => 'bar', 'baz' => 'bat' );
		
		$wrapper = Prack::_Hash( $items );
		$wrapper->each( $callback );
		
		$this->assertEquals( $items, $this->items );
	} // It should handle each
	
	/**
	 * This function is used as a callback for the above test.
	 */
	public function addToItems( $key, $item )
	{
		$this->items[ $key ] = $item;
	}
	
	/**
	 * It should handle set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_set()
	{
		$wrapper = Prack::_Hash( array( 'foo' => 'bar', 'baz' => 'bat' ) );
		$wrapper->set( 'foo', 'cow' );
		$wrapper->set( 'bar', 'bar' );
		$this->assertEquals( array( 'foo' => 'cow', 'baz' => 'bat', 'bar' => 'bar' ), $wrapper->toN() );
	} // It should handle set
	
	/**
	 * It should handle get
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_get()
	{
		$wrapper = Prack::_Hash( array( 'foo' => 'bar', 'baz' => 'bat', 'cow' => 'cud' ) );
		$this->assertEquals( 'bar', $wrapper->get( 'foo' ) );
		$this->assertEquals( 'bat', $wrapper->get( 'baz' ) );
		$this->assertEquals( 'cud', $wrapper->get( 'cow' ) );
		
		$wrapper->setDefault( Prack::_String( 'default value' ) );
		$this->assertEquals( $wrapper->getDefault(), $wrapper->get( 'Macguyver' ) );
	} // It should handle get
	
	/**
	 * It should know if it's empty
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_know_if_it_s_empty()
	{
		$wrapper = Prack::_Hash( array() );
		$this->assertTrue( $wrapper->isEmpty() );
		
		$wrapper->set( 'foo', 'bar' );
		
		$this->assertEquals( 'bar', $wrapper->delete( 'foo' ) );
		$this->assertTrue( $wrapper->isEmpty() );
	} // It should know if it's empty
}