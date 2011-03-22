<?php

// TODO: Document!
class Prack_Utils_HeaderHashTest extends PHPUnit_Framework_TestCase 
{
	private $invocation_count;
	
	/**
	 * It should retain header case
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_retain_header_case()
	{
		$headerhash = Prack_Utils_HeaderHash::using(
		  Prb::Hsh( array(
		    'Content-MD5' => Prb::Str( 'd5ff4e2a0 ...' )
		  ) )
		);
		
		$headerhash->set( 'ETag', Prb::Str( 'Boo!' ) );
		
		$expected = Prb::Hsh( array(
		  'Content-MD5' => Prb::Str( 'd5ff4e2a0 ...' ),
		  'ETag'        => Prb::Str( 'Boo!' )
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
		$headerhash = Prack_Utils_HeaderHash::using(
		  Prb::Hsh( array(
		    'Content-MD5' => Prb::Str( 'd5ff4e2a0 ...' )
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
		$left = Prack_Utils_HeaderHash::using(
		  Prb::Hsh( array( 
		    'ETag'           => Prb::Str( 'HELLO' ),
		    'content-length' => Prb::Str( '123'   )
		  ) )
		);
		
		$expected = $right = Prack_Utils_HeaderHash::using(
		  Prb::Hsh( array( 
		    'Etag'           => Prb::Str( 'WORLD' ),
		    'Content-Length' => Prb::Str( '321'   ),
		    'Foo'            => Prb::Str( 'BAR'   )
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
		$headerhash = Prack_Utils_HeaderHash::using(
		  Prb::Hsh( array( 'Foo-Bar' => Prb::Str( 'baz' ) ) )
		);
		
		$headerhash->set( 'foo-bar', Prb::Str( 'bizzle' ) );
		
		$this->assertEquals( 'bizzle', $headerhash->get( 'FOO-BAR' )->raw() );
		$this->assertEquals( 1, $headerhash->length() );
		$this->assertEquals(
		  Prb::Hsh( array(
		    'foo-bar' => Prb::Str( 'bizzle' )
		  ) ),
		  $headerhash->toHash()
		);
	} // It should overwrite case insensitively and assume the new key's case
	
	/**
	 * It should be converted to real Prb_Hash
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_converted_to_real_Prb_Hash()
	{
		$headerhash = Prack_Utils_HeaderHash::using(
		  Prb::Hsh( array( 'foo' => Prb::Str( 'bar' ) ) )
		);
		$this->assertEquals( 'Prb_Hash', get_class( $headerhash->toHash() ) );
	} // It should be converted to real Prb_Hash
	
	/**
	 * It should convert values to Prb_String when converting to Prb_Hash
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_convert_values_to_Prb_String_when_converting_to_Prb_Hash()
	{
		$headerhash = Prack_Utils_HeaderHash::using(
		  Prb::Hsh( array(
		    'foo' => Prb::Ary( array(
		      Prb::Str( 'bar' ), Prb::Str( 'baz' )
		    ) )
		  ) )
		);
		$this->assertEquals( array( 'foo' => Prb::Str( "bar\nbaz" ) ), $headerhash->toHash()->raw() );
	} // It should convert values to Prb_String when converting to Prb_Hash
	
	/**
	 * It should replace correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_replace_correctly()
	{
		$headerhash  = Prack_Utils_HeaderHash::using(
		  Prb::Hsh( array( 'Foo-Bar' => Prb::Str( 'baz' ) ) )
		);
		$replacement = Prb::Hsh( array( 'foo' => Prb::Str( 'bar' ) ) );
		$headerhash->replace( $replacement );
		$this->assertEquals( 'bar', $headerhash->get( 'foo' )->raw() );
	} // It should replace correctly
	
	/**
	 * It should be able to delete the given key case-sensitively
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_able_to_delete_the_given_key_case_sensitively()
	{
		$headerhash = Prack_Utils_HeaderHash::using(
		  Prb::Hsh( array( 'foo' => Prb::Str( 'bar' ) ) )
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
		$headerhash = Prack_Utils_HeaderHash::using(
		  Prb::Hsh( array( 'foo' => Prb::Str( 'bar' ) ) )
		);
		$this->assertEquals( 'bar', $headerhash->delete( 'Foo' )->raw() );
	} // It should return the deleted value when delete is called on an existing key
	
	/**
	 * It should return null when delete is called on a non-existant key
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_null_when_delete_is_called_on_a_non_existant_key()
	{
		$headerhash = Prack_Utils_HeaderHash::using(
		  Prb::Hsh( array( 'foo' => Prb::Str( 'bar' ) ) )
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
		$one = Prack_Utils_HeaderHash::using(
		  Prb::Hsh( array( 'foo' => Prb::Str( 'bar' ) ) )
		);
		$two = Prack_Utils_HeaderHash::using( $one );
		
		$this->assertSame( $two, $one );
	} // It should avoid unnecessary object creation if possible
	
	/**
	 * It should create an object with an array otherwise
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_create_an_object_with_an_array_otherwise()
	{
		$this->assertNotNull( Prack_Utils_HeaderHash::using(
		  Prb::Hsh( array( 'foo' => Prb::Str( 'bar' ) ) )
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
		  'Prack_Utils_HeaderHash',
		  null,                               // non-stubbed methods
		  array(                              // constructor arguments
		    Prb::Hsh( array(
		      'foo' => Prb::Ary( array( Prb::Str( 'foo' ) ) ),
		      'bar' => Prb::Ary( array( Prb::Str( 'bar' ) ) )
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
		  'Prack_Utils_HeaderHash',
		  array( 'contains' ),                // non-stubbed methods
		  array( Prb::Hsh() )             // constructor arguments
		);
		
		$headerhash_mock->expects( $this->exactly( 2 ) )
		                ->method( 'contains' )
		                ->with( $this->equalTo( 'foo' ) );
		
		$headerhash_mock->hasKey( 'foo' );
		$headerhash_mock->isMember( 'foo' );
	} // It should alias hasKey and isMember to contains
}