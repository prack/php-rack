<?php

// TODO: Document!
class Prack_Wrapper_ArrayTest extends PHPUnit_Framework_TestCase 
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
		$items    = array( 'foo', 'bar' );
		
		$wrapper = Prack_Wrapper_Array::with( $items );
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
	
	/**
	 * It should handle set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_set()
	{
		$wrapper = Prack_Wrapper_Array::with( array( 'foo', 'bar' ) );
		$wrapper->set( 1, 'cow' );
		$this->assertEquals( array( 'foo', 'cow' ), $wrapper->getWrapped() );
		
		$wrapper = Prack_Wrapper_Array::with( array( 'foo', 'bar' ) );
		$wrapper->set( 4, 'baz' );
		$this->assertEquals( array( 'foo', 'bar', null, null, 'baz' ), $wrapper->getWrapped() );
	} // It should handle set
	
	
	/**
	 * It should handle get
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_get()
	{
		$wrapper = Prack_Wrapper_Array::with( array( 'foo', 'bar', 'baz' ) );
		$this->assertEquals( 'foo', $wrapper->get( 0 ) );
		$this->assertEquals( 'bar', $wrapper->get( 1 ) );
		$this->assertEquals( 'baz', $wrapper->get( 2 ) );
		$this->assertNull( $wrapper->get( 42 ) );
	} // It should handle get
	
	/**
	 * It should return null on get with a negative index that is out of bounds
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_null_on_get_with_a_negative_index_that_is_out_of_bounds()
	{
		$wrapper = Prack_Wrapper_Array::with( array( 'foo', 'bar', 'baz' ) );
		$this->assertNull( $wrapper->get( -4 ) );
	} // It should return null on get with a negative index that is out of bounds
	
	/**
	 * It should throw an exception on set with a negative index that is out of bounds
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_throw_an_exception_on_set_with_a_negative_index_that_is_out_of_bounds()
	{
		$wrapper = Prack_Wrapper_Array::with( array( 'foo', 'bar', 'baz' ) );
		
		try
		{
			$wrapper->set( -4, 'invalid' );
		}
		catch ( Prack_Error_Index $e ) {}
		
		if ( isset( $e ) )
			$this->assertRegExp( '/minimum -3/', $e->getMessage() );
		else
			$this->fail( 'Expected exception on set with out-of-bounds negative index.' );
	} // It should throw an exception on set with a negative index that is out of bounds
	
	/**
	 * It should handle push
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_push()
	{
		$wrapper = Prack_Wrapper_Array::with( array( 'foo', 'bar' ) );
		$wrapper->push( 'baz' );
		$this->assertEquals( array( 'foo', 'bar', 'baz' ), $wrapper->getWrapped() );
		$this->assertTrue( $wrapper->length() == 3 );
	} // It should handle push
	
	/**
	 * It should handle pop
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_pop()
	{
		$wrapper = Prack_Wrapper_Array::with( array( 'foo', 'bar', 'baz' ) );
		$result  = $wrapper->pop();
		$this->assertEquals( 'baz', $result );
		$this->assertEquals( array( 'foo', 'bar' ), $wrapper->getWrapped() );
		$this->assertTrue( $wrapper->size() == 2 );
	} // It should handle pop
	
	/**
	 * It should handle unshift
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_unshift()
	{
		$wrapper = Prack_Wrapper_Array::with( array( 'bar', 'baz' ) );
		$wrapper->unshift( 'foo' );
		$this->assertEquals( array( 'foo', 'bar', 'baz' ), $wrapper->getWrapped() );
		$this->assertTrue( $wrapper->count() == 3 );
	} // It should handle unshift
	
	/**
	 * It should handle shift
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_shift()
	{
		$wrapper = Prack_Wrapper_Array::with( array( 'foo', 'bar', 'baz' ) );
		$result  = $wrapper->shift();
		$this->assertEquals( 'foo', $result );
		$this->assertEquals( array( 'bar', 'baz' ), $wrapper->getWrapped() );
	} // It should handle shift
	
	/**
	 * It should output useful information on inspect
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_output_useful_information_on_inspect()
	{
		
	} // It should output useful information on inspect
	
	/**
	 * It should know if it's empty
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_know_if_it_s_empty()
	{
		$wrapper = Prack_Wrapper_Array::with( array() );
		
		$this->assertTrue( $wrapper->isEmpty() );
		
		$wrapper->push( 'foo' );
		$wrapper->pop();
		
		$this->assertTrue( $wrapper->isEmpty() );
	} // It should know if it's empty
}

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
		
		$wrapper = Prack_Wrapper_Hash::with( $items );
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
		$wrapper = Prack_Wrapper_Hash::with( array( 'foo' => 'bar', 'baz' => 'bat' ) );
		$wrapper->set( 'foo', 'cow' );
		$wrapper->set( 'bar', 'bar' );
		$this->assertEquals( array( 'foo' => 'cow', 'baz' => 'bat', 'bar' => 'bar' ), $wrapper->getWrapped() );
	} // It should handle set
	
	/**
	 * It should handle get
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_get()
	{
		$wrapper = Prack_Wrapper_Hash::with( array( 'foo' => 'bar', 'baz' => 'bat', 'cow' => 'cud' ) );
		$this->assertEquals( 'bar', $wrapper->get( 'foo' ) );
		$this->assertEquals( 'bat', $wrapper->get( 'baz' ) );
		$this->assertEquals( 'cud', $wrapper->get( 'cow' ) );
		
		$wrapper->setDefault( 'default value' );
		$this->assertEquals( $wrapper->getDefault(), $wrapper->get( 'Macguyver' ) );
	} // It should handle get
	
	/**
	 * It should know if it's empty
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_know_if_it_s_empty()
	{
		$wrapper = Prack_Wrapper_Hash::with( array() );
		$this->assertTrue( $wrapper->isEmpty() );
		
		$wrapper->set( 'foo', 'bar' );
		
		$this->assertEquals( 'bar', $wrapper->delete( 'foo' ) );
		$this->assertTrue( $wrapper->isEmpty() );
	} // It should know if it's empty
}

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
		
		$wrapper = Prack_Wrapper_Set::with( $items );
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