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
		$headerhash = Prack_Utils_HeaderHash::using( array( 'Content-MD5' => 'd5ff4e2a0 ...' ) );
		$headerhash->set( 'ETag', 'Boo!' );
		$this->assertEquals( array( 'Content-MD5' => 'd5ff4e2a0 ...', 'ETag' => 'Boo!' ), $headerhash->raw() );
	} // It should retain header case
	
	/**
	 * It should check existence of keys case insensitively
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_check_existence_of_keys_case_insensitively()
	{
		$headerhash = Prack_Utils_HeaderHash::using( array( 'Content-MD5' => 'd5ff4e2a0 ...' ) );
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
		$left     = Prack_Utils_HeaderHash::using( array( 'ETag' => 'HELLO', 'content-length' => '123' ) );
		$expected = $right = array( 'Etag' => 'WORLD', 'Content-Length' => '321', 'Foo' => 'BAR' );
		$merged   = $left->merge( $right );
		$this->assertEquals( $expected, $merged->raw() );
	} // It should merge case-insensitively

	/**
	 * It should overwrite case insensitively and assume the new key's case
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_overwrite_case_insensitively_and_assume_the_new_key_s_case()
	{
		$headerhash = Prack_Utils_HeaderHash::using( array( 'Foo-Bar' => 'baz' ) );
		$headerhash->set( 'foo-bar', 'bizzle' );
		$this->assertEquals( 'bizzle', $headerhash->get( 'FOO-BAR' ) );
		$this->assertEquals( 1, $headerhash->length() );
		$this->assertEquals( array( 'foo-bar' => 'bizzle' ), $headerhash->raw() );
	} // It should overwrite case insensitively and assume the new key's case
	
	/**
	 * It should convert values to Prb_String when converting to Prb_Hash
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_convert_values_to_Prb_String_when_converting_to_Prb_Hash()
	{
		$headerhash = Prack_Utils_HeaderHash::using( array( 'foo' => array( 'bar', 'baz' ) ) );
		$this->assertEquals( array( 'foo' => "bar\nbaz" ), $headerhash->raw() );
	} // It should convert values to Prb_String when converting to Prb_Hash
	
	/**
	 * It should replace correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_replace_correctly()
	{
		$headerhash  = Prack_Utils_HeaderHash::using( array( 'Foo-Bar' => 'baz' ) );
		$replacement = array( 'foo' => 'bar' );
		$headerhash->replace( $replacement );
		$this->assertEquals( 'bar', $headerhash->get( 'foo' ) );
	} // It should replace correctly
	
	/**
	 * It should be able to delete the given key case-sensitively
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_able_to_delete_the_given_key_case_sensitively()
	{
		$headerhash = Prack_Utils_HeaderHash::using( array( 'foo' => 'bar' ) );
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
		$headerhash = Prack_Utils_HeaderHash::using( array( 'foo' => 'bar' ) );
		$this->assertEquals( 'bar', $headerhash->delete( 'Foo' ) );
	} // It should return the deleted value when delete is called on an existing key
	
	/**
	 * It should return null when delete is called on a non-existant key
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_null_when_delete_is_called_on_a_non_existant_key()
	{
		$headerhash = Prack_Utils_HeaderHash::using( array( 'foo' => 'bar' ) );
		$this->assertNull( $headerhash->delete( 'Hello' ) );
	} // It should return null when delete is called on a non-existant key
	
	/**
	 * It should avoid unnecessary object creation if possible
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_avoid_unnecessary_object_creation_if_possible()
	{
		$one = Prack_Utils_HeaderHash::using( array( 'foo' => 'bar' ) );
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
		$this->assertNotNull( Prack_Utils_HeaderHash::using( array( 'foo' => 'bar' ) ) );
	} // It should create an object with an array otherwise
}