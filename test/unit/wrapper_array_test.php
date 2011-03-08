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
	 * @callback
	 */
	public function addToItems( $item )
	{
		array_push( $this->items, $item );
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
		
		$wrapper = Prack::_Array( $items );
		$wrapper->each( $callback );
		
		$this->assertEquals( $items, $this->items );
	} // It should handle each
	
	/**
	 * It should handle set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_set()
	{
		$wrapper = Prack::_Array( array( 'foo', 'bar' ) );
		$wrapper->set( 1, 'cow' );
		$this->assertEquals( array( 'foo', 'cow' ), $wrapper->toN() );
		
		$wrapper = Prack::_Array( array( 'foo', 'bar' ) );
		$wrapper->set( 4, 'baz' );
		$this->assertEquals( array( 'foo', 'bar', null, null, 'baz' ), $wrapper->toN() );
	} // It should handle set
	
	
	/**
	 * It should handle get
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_get()
	{
		$wrapper = Prack::_Array( array( 'foo', 'bar', 'baz' ) );
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
		$wrapper = Prack::_Array( array( 'foo', 'bar', 'baz' ) );
		$this->assertNull( $wrapper->get( -4 ) );
	} // It should return null on get with a negative index that is out of bounds
	
	/**
	 * It should throw an exception on set with a negative index that is out of bounds
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_throw_an_exception_on_set_with_a_negative_index_that_is_out_of_bounds()
	{
		$wrapper = Prack::_Array( array( 'foo', 'bar', 'baz' ) );
		
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
		$wrapper = Prack::_Array( array( 'foo', 'bar' ) );
		$wrapper->push( 'baz' );
		$this->assertEquals( array( 'foo', 'bar', 'baz' ), $wrapper->toN() );
		$this->assertTrue( $wrapper->length() == 3 );
	} // It should handle push
	
	/**
	 * It should handle pop
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_pop()
	{
		$wrapper = Prack::_Array( array( 'foo', 'bar', 'baz' ) );
		$result  = $wrapper->pop();
		$this->assertEquals( 'baz', $result );
		$this->assertEquals( array( 'foo', 'bar' ), $wrapper->toN() );
		$this->assertTrue( $wrapper->size() == 2 );
	} // It should handle pop
	
	/**
	 * It should handle unshift
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_unshift()
	{
		$wrapper = Prack::_Array( array( 'bar', 'baz' ) );
		$wrapper->unshift( 'foo' );
		$this->assertEquals( array( 'foo', 'bar', 'baz' ), $wrapper->toN() );
		$this->assertTrue( $wrapper->count() == 3 );
	} // It should handle unshift
	
	/**
	 * It should handle shift
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_shift()
	{
		$wrapper = Prack::_Array( array( 'foo', 'bar', 'baz' ) );
		$result  = $wrapper->shift();
		$this->assertEquals( 'foo', $result );
		$this->assertEquals( array( 'bar', 'baz' ), $wrapper->toN() );
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
		$wrapper = Prack::_Array( array() );
		
		$this->assertTrue( $wrapper->isEmpty() );
		
		$wrapper->push( 'foo' );
		$wrapper->pop();
		
		$this->assertTrue( $wrapper->isEmpty() );
	} // It should know if it's empty
	
	/**
	 * It should handle slice
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_slice()
	{
		$wrapper = Prack::_Array( array( 'foo', 'bar', 'baz' ) );
		
		$this->assertEquals( Prack::_Array( array( 'foo', 'bar' ) ), $wrapper->slice(  0, 1 ) );
		$this->assertEquals( Prack::_Array( array( 'foo', 'bar' ) ), $wrapper->slice(  0, -2 ) );
		$this->assertEquals( Prack::_Array( array( 'foo', 'bar' ) ), $wrapper->slice( -3, -2 ) );
		$this->assertEquals( Prack::_Array( array( 'foo', 'bar' ) ), $wrapper->slice( -3,  1 ) );
		$this->assertEquals( Prack::_Array( array( 'baz' ) ),        $wrapper->slice(  2,  4 ) ); // range should be clipped to array size
		$this->assertNull( $wrapper->slice( -5, 1 ) ); // out of bounds
	} // It should handle slice
	
	/**
	 * It should handle compact
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_compact()
	{
		$wrapper = Prack::_Array( array( null, 'foo', null, 'bar', 'baz', null, null, null ) );
		$this->assertEquals( Prack::_Array( array( 'foo', 'bar', 'baz' ) ), $wrapper->compact() );
	} // It should handle compact
	
		/**
	 * It should handle inject
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_inject()
	{
		$wrapper = Prack::_Array( array( 'foo', 'bar', 'baz' ) );
		
		$callback = create_function( '$accumulator,$item', 'return $accumulator.$item;' );
		$this->assertEquals( 'hifoobarbaz', $wrapper->inject( 'hi', $callback ) );
		
		$wrapper = Prack::_Array( array(
		  Prack::_Array( array( 'cow', 'cud' ) ),
		  Prack::_Array( array( 'lol', 'wut' ) )
		) );
		$callback = create_function( '$accumulator,$first,$second', 'return $accumulator.$first.$second;' );
		$this->assertEquals( 'hicowcudlolwut', $wrapper->inject( 'hi', $callback ) );
	} // It should handle inject
	
	/**
	 * It should return null for compare when incomparable
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_null_for_compare_when_incomparable()
	{
		$left  = Prack::_Array( array( 'foo', 'bar', 'baz' ) );
		$right = Prack::_Array( array( 'foo', 'bar', 'baz' ) );
		$this->assertNull( $left->compare( $right ) );
	} // It should return null for compare when incomparable
	
	/**
	 * It should handle eachIndex
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_handle_eachIndex()
	{
		$wrapper = Prack::_Array( array( 'foo', 'bar', 'baz' ) );
		
		$callback = array( $this, 'addToItems' );
		$wrapper->eachIndex( $callback );
		
		$this->assertEquals( array( 0, 1, 2 ), $this->items );
	} // It should handle eachIndex
}