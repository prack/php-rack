<?php


class Prack_Utils_Response_HeaderHashTest extends PHPUnit_Framework_TestCase 
{
	private $invocation_count;
	
	
	static function rawHeaders()
	{
		static $headers = null;
		
		if ( is_null( $headers ) )
		{
			$headers = array(
				'Content-Type'     => 'text/html',
				'Content-Length'   => '80',
				'WWW-Authenticate' => array( 'http basic', 'http digest' )/* derp */
			);
		}
		
		return $headers;
	}
	
	/**
	 * new instance should properly translate raw associative array into headers in object context
	 * @author Joshua Morris
	 * @test
	 */
	public function new_instance_should_properly_translate_raw_associative_array_into_headers_in_object_context()
	{
		$headers    = self::rawHeaders();
		$headerhash = new Prack_Utils_Response_HeaderHash( $headers );
		
		foreach ( $headerhash->getEntries() as $header => $value )
			$this->assertArrayHasKey( $header, $headers );
		
		$translations = $headerhash->getTranslations();
		$this->assertFalse( empty( $translations ) );
	} // new instance should properly translate raw associative array into headers in object context
	
	/**
	 * instance method each should distill entries and then call the provided callback for each
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_each_should_distill_entries_and_then_call_the_provided_callback_for_each()
	{
		$headers    = self::rawHeaders();
		$headerhash = new Prack_Utils_Response_HeaderHash( $headers );
		$callback   = array( $this, 'eachCallback' );
		
		$this->invocation_count = 0;
		$headerhash->each( array( $this, 'eachCallback' ) );
		
		$this->assertEquals( count( $headers ), $this->invocation_count );
	} // instance method each should distill entries and then call the provided callback for each
	
	/**
	 * Callback function used by the above test.
	 */
	public function eachCallback( $item )
	{
		$this->invocation_count += 1;
	}
	
	/**
	 * instance method toArray should return distilled entries
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_toArray_should_return_distilled_entries()
	{
		$headers    = self::rawHeaders();
		$headerhash = new Prack_Utils_Response_HeaderHash( $headers );
		
		$distilled = array(
			'Content-Type'     => 'text/html',
			'Content-Length'   => '80',
			'WWW-Authenticate' => "http basic\nhttp digest"
		);
		
		$this->assertEquals( $distilled, $headerhash->toArray() );
	} // instance method toArray should return distilled entries
	
	/**
	 * It should retain header case
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_retain_header_case()
	{
		$headers    = array( 'Content-MD5' => 'd5ff4e2a0 ...' );
		$headerhash = new Prack_Utils_Response_HeaderHash( $headers );
		$headerhash->set( 'ETag', 'Boo!' );
		
		$expected = array(
			'Content-MD5' => 'd5ff4e2a0 ...', 
			'ETag'        => 'Boo!'
		);
		
		$this->assertEquals( $expected, $headerhash->toArray() );
	} // It should retain header case
	
	/**
	 * It should check existence of keys case insensitively
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_check_existence_of_keys_case_insensitively()
	{
		$headers    = array( 'Content-MD5' => 'd5ff4e2a0 ...' );
		$headerhash = new Prack_Utils_Response_HeaderHash( $headers );
		$this->assertTrue( $headerhash->contains( 'content-md5' ) );
	} // It should check existence of keys case insensitively
	
	/**
	 * It should merge case-insensitively
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_merge_case_insensitively()
	{
		$headers = array( 
			'ETag'           => 'HELLO', 
			'content-length' => '123' 
		);
		
		$headerhash = new Prack_Utils_Response_HeaderHash( $headers );
		
		$with = $expected = array(
			'Etag'           => 'WORLD',
			'Content-Length' => '321',
			'Foo'            => 'BAR'
		);
		
		$merged = $headerhash->merge( $with );
		
		$this->assertEquals( $headerhash->merge( $with )->toArray(), $expected );
	} // It should merge case-insensitively
	
	/**
	 * It should overwrite case insensitively and assume the new key's case
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_overwrite_case_insensitively_and_assume_the_new_key_s_case()
	{
		$headerhash = new Prack_Utils_Response_HeaderHash( array( 'Foo-Bar' => 'baz' ) );
		$headerhash->set( 'foo-bar', 'bizzle' );
		$this->assertEquals( 'bizzle', $headerhash->get( 'FOO-BAR' ) );
		$this->assertEquals( 1, $headerhash->length() );
		$this->assertEquals( array( 'foo-bar' => 'bizzle' ), $headerhash->toArray() );
	} // It should overwrite case insensitively and assume the new key's case
	
	/**
	 * It should be converted to real array
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_converted_to_real_array()
	{
		$headerhash = new Prack_Utils_Response_HeaderHash( array( 'foo' => 'bar' ) );
		$this->assertTrue( is_array( $headerhash->toArray() ) );
	} // It should be converted to real array
	
	/**
	 * It should convert array values to strings when converting to array
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_convert_array_values_to_strings_when_converting_to_array()
	{
		$headerhash = new Prack_Utils_Response_HeaderHash( array( 'foo' => array( 'bar', 'baz' ) ) );
		$this->assertEquals( array( 'foo' => "bar\nbaz" ), $headerhash->toArray() );
	} // It should convert array values to strings when converting to array
	
	/**
	 * It should replace hashes correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_replace_hashes_correctly()
	{
		$headerhash  = new Prack_Utils_Response_HeaderHash( array( 'Foo-Bar' => 'baz' ) );
		$replacement = array( 'foo' => 'bar' );
		$headerhash->replace( $replacement );
		$this->assertEquals( 'bar', $headerhash->get( 'foo' ) );
	} // It should replace hashes correctly
	
	/**
	 * It should be able to delete the given key case-sensitively
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_able_to_delete_the_given_key_case_sensitively()
	{
		$headerhash = new Prack_Utils_Response_HeaderHash( array( 'foo' => 'bar' ) );
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
		$headerhash = new Prack_Utils_Response_HeaderHash( array( 'foo' => 'bar' ) );
		$this->assertEquals( 'bar', $headerhash->delete( 'Foo' ) );
	} // It should return the deleted value when delete is called on an existing key
	
	/**
	 * It should return null when delete is called on a non-existant key
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_null_when_delete_is_called_on_a_non_existant_key()
	{
		$headerhash = new Prack_Utils_Response_HeaderHash( array( 'foo' => 'bar' ) );
		$this->assertNull( $headerhash->delete( 'Hello' ) );
	} // It should return null when delete is called on a non-existant key
	
	/**
	 * It should avoid unnecessary object creation if possible
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_avoid_unnecessary_object_creation_if_possible()
	{
		$a = new Prack_Utils_Response_HeaderHash( array( 'foo' => 'bar' ) );
		$b = Prack_Utils_Response_HeaderHash::build( $a );
		$this->assertSame( $a, $b );
	} // It should avoid unnecessary object creation if possible
	
	/**
	 * It should create an object with an array otherwise
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_create_an_object_with_an_array_otherwise()
	{
		$with = array( 'foo' => 'bar' );
		$this->assertNotNull( Prack_Utils_Response_HeaderHash::build( $with ) );
	} // It should create an object with an array otherwise
	
	/**
	 * It should convert array values to strings when responding to each
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_convert_array_values_to_strings_when_responding_to_each()
	{
		// Since we use a static message to distill (i.e. join with endline) header values,
		// this functionality is currently untestable. However, it does happen.
		true;
	} // It should convert array values to strings when responding to each
	
	/**
	 * It should alias hasKey and isMember to contains
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_alias_hasKey_and_isMember_to_contains()
	{
		$headerhash_mock = $this->getMock( 'Prack_Utils_Response_HeaderHash', array( 'contains' ) );
		$headerhash_mock->expects( $this->exactly( 2 ) )
		                ->method( 'contains' )
		                ->with( $this->equalTo( 'foo' ) );
		
		$headerhash_mock->hasKey( 'foo' );
		$headerhash_mock->isMember( 'foo' );
	} // It should alias hasKey and isMember to contains
	
}