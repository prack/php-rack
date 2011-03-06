<?php


class Prack_Utils_Response_HeaderHashTest extends PHPUnit_Framework_TestCase 
{
	private $invocation_count;
	
	/**
	 * It should retain header case
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_retain_header_case()
	{
		$headerhash = Prack_Utils_Response_HeaderHash::using(
		  Prack::_Hash( array(
		    'Content-MD5' => Prack::_String( 'd5ff4e2a0 ...' )
		  ) )
		);
		
		$headerhash->set( 'ETag', Prack::_String( 'Boo!' ) );
		
		$expected = Prack::_Hash( array(
		  'Content-MD5' => Prack::_String( 'd5ff4e2a0 ...' ),
		  'ETag'        => Prack::_String( 'Boo!' )
		) );
		
		$this->assertEquals( $expected, $headerhash->toHash() );
	} // It should retain header case
	
	/**
	 * It should check existence of keys case insensitively
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_check_existence_of_keys_case_insensitively()
	{
		$headerhash = Prack_Utils_Response_HeaderHash::using(
		  Prack::_Hash( array(
		    'Content-MD5' => Prack::_String( 'd5ff4e2a0 ...' )
		  ) )
		);
		
		$this->assertFalse( $headerhash->contains( 'ETag' ) );
		$this->assertTrue( $headerhash->contains( 'content-md5' ) );
	} // It should check existence of keys case insensitively
	
	/**
	 * It should merge case-insensitively
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_merge_case_insensitively()
	{
		$left = Prack_Utils_Response_HeaderHash::using(
		  Prack::_Hash( array( 
		    'ETag'           => Prack::_String( 'HELLO' ),
		    'content-length' => Prack::_String( '123'   )
		  ) )
		);
		
		$expected = $right = Prack_Utils_Response_HeaderHash::using(
		  Prack::_Hash( array( 
		    'Etag'           => Prack::_String( 'WORLD' ),
		    'Content-Length' => Prack::_String( '321'   ),
		    'Foo'            => Prack::_String( 'BAR'   )
		  ) )
		);
		
		$merged = $left->merge( $right );
		
		$this->assertEquals( $expected, $merged );
	} // It should merge case-insensitively

	/**
	 * It should overwrite case insensitively and assume the new key's case
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_overwrite_case_insensitively_and_assume_the_new_key_s_case()
	{
		$headerhash = Prack_Utils_Response_HeaderHash::using(
		  Prack::_Hash( array( 'Foo-Bar' => Prack::_String( 'baz' ) ) )
		);
		
		$headerhash->set( 'foo-bar', Prack::_String( 'bizzle' ) );
		
		$this->assertEquals( 'bizzle', $headerhash->get( 'FOO-BAR' )->toN() );
		$this->assertEquals( 1, $headerhash->length() );
		$this->assertEquals(
		  Prack::_Hash( array(
		    'foo-bar' => Prack::_String( 'bizzle' )
		  ) ),
		  $headerhash->toHash()
		);
	} // It should overwrite case insensitively and assume the new key's case
	
	/**
	 * It should be converted to real Prack_Wrapper_Hash
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_converted_to_real_Prack_Wrapper_Hash()
	{
		$headerhash = Prack_Utils_Response_HeaderHash::using(
		  Prack::_Hash( array( 'foo' => Prack::_String( 'bar' ) ) )
		);
		$this->assertEquals( 'Prack_Wrapper_Hash', get_class( $headerhash->toHash() ) );
	} // It should be converted to real Prack_Wrapper_Hash
	
	/**
	 * It should convert values to Prack_Wrapper_String when converting to Prack_Wrapper_Hash
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_convert_values_to_Prack_Wrapper_String_when_converting_to_Prack_Wrapper_Hash()
	{
		$headerhash = Prack_Utils_Response_HeaderHash::using(
		  Prack::_Hash( array(
		    'foo' => Prack::_Array( array(
		      Prack::_String( 'bar' ), Prack::_String( 'baz' )
		    ) )
		  ) )
		);
		$this->assertEquals( array( 'foo' => Prack::_String( "bar\nbaz" ) ), $headerhash->toHash()->toN() );
	} // It should convert values to Prack_Wrapper_String when converting to Prack_Wrapper_Hash
	
	/**
	 * It should replace correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_replace_correctly()
	{
		$headerhash  = Prack_Utils_Response_HeaderHash::using(
		  Prack::_Hash( array( 'Foo-Bar' => Prack::_String( 'baz' ) ) )
		);
		$replacement = Prack::_Hash( array( 'foo' => Prack::_String( 'bar' ) ) );
		$headerhash->replace( $replacement );
		$this->assertEquals( 'bar', $headerhash->get( 'foo' )->toN() );
	} // It should replace correctly
	
	/**
	 * It should be able to delete the given key case-sensitively
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_able_to_delete_the_given_key_case_sensitively()
	{
		$headerhash = Prack_Utils_Response_HeaderHash::using(
		  Prack::_Hash( array( 'foo' => Prack::_String( 'bar' ) ) )
		);
		$headerhash->delete( 'foo' );
		$this->assertNull( $headerhash->get( 'foo' ) );
		$this->assertNull( $headerhash->get( 'FOO' ) );
	} // It should be able to delete the given key case-sensitively
	
	/**
	 * It should return the deleted value when delete is called on an existing key
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_the_deleted_value_when_delete_is_called_on_an_existing_key()
	{
		$headerhash = Prack_Utils_Response_HeaderHash::using(
		  Prack::_Hash( array( 'foo' => Prack::_String( 'bar' ) ) )
		);
		$this->assertEquals( 'bar', $headerhash->delete( 'Foo' )->toN() );
	} // It should return the deleted value when delete is called on an existing key
	
	/**
	 * It should return null when delete is called on a non-existant key
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_null_when_delete_is_called_on_a_non_existant_key()
	{
		$headerhash = Prack_Utils_Response_HeaderHash::using(
		  Prack::_Hash( array( 'foo' => Prack::_String( 'bar' ) ) )
		);
		$this->assertNull( $headerhash->delete( 'Hello' ) );
	} // It should return null when delete is called on a non-existant key
	
	/**
	 * It should avoid unnecessary object creation if possible
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_avoid_unnecessary_object_creation_if_possible()
	{
		$one = Prack_Utils_Response_HeaderHash::using(
		  Prack::_Hash( array( 'foo' => Prack::_String( 'bar' ) ) )
		);
		$two = Prack_Utils_Response_HeaderHash::using( $one );
		
		$this->assertSame( $two, $one );
	} // It should avoid unnecessary object creation if possible
	
	/**
	 * It should create an object with an array otherwise
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_create_an_object_with_an_array_otherwise()
	{
		$this->assertNotNull( Prack_Utils_Response_HeaderHash::using(
		  Prack::_Hash( array( 'foo' => Prack::_String( 'bar' ) ) )
		) );
	} // It should create an object with an array otherwise
	
	/**
	 * It should distill values when responding to each
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_distill_values_when_responding_to_each()
	{
		// FIXME: Figure out why expectation isn't being met.
		/*
		$headerhash_mock = $this->getMock(
		  'Prack_Utils_Response_HeaderHash',
		  null,                               // non-stubbed methods
		  array(                              // constructor arguments
		    Prack::_Hash( array(
		      'foo' => Prack::_Array( array( Prack::_String( 'foo' ) ) ),
		      'bar' => Prack::_Array( array( Prack::_String( 'bar' ) ) )
		    ) )
		  )
		);
		
		$headerhash_mock->expects( $this->exactly( 2 ) )
		                ->method( 'distill' );
		
		$headerhash_mock->each( create_function( '$k,$v', 'return;' ) );
		*/
		$this->markTestSkipped( 'figure out PHPUnit mocks' );
	} // It should distill values when responding to each
	
	/**
	 * It should alias hasKey and isMember to contains
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_alias_hasKey_and_isMember_to_contains()
	{
		$headerhash_mock = $this->getMock(
		  'Prack_Utils_Response_HeaderHash',
		  array( 'contains' ),                // non-stubbed methods
		  array( Prack::_Hash() )             // constructor arguments
		);
		
		$headerhash_mock->expects( $this->exactly( 2 ) )
		                ->method( 'contains' )
		                ->with( $this->equalTo( 'foo' ) );
		
		$headerhash_mock->hasKey( 'foo' );
		$headerhash_mock->isMember( 'foo' );
	} // It should alias hasKey and isMember to contains
}