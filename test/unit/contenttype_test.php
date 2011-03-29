<?php

// TODO: Document!
class Prack_ContentTypeTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * It should set Content-Type to default text/html if none is set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_set_Content_Type_to_default_text_html_if_none_is_set()
	{
		$middleware_app = new Prack_Test_Echo( 200, array(), 'Hello, World!' );
		
		$env = array();
		list( $status, $headers, $body ) =
		  Prack_ContentType::with( $middleware_app )->call( $env );
		
		$this->assertEquals( 'text/html', @$headers[ 'Content-Type' ] );
	} // It should set Content-Type to default text/html if none is set
	
	/**
	 * It should set Content-Type to chosen default if none is set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_set_Content_Type_to_chosen_default_if_none_is_set()
	{
		$middleware_app = new Prack_Test_Echo( 200, array(), 'Hello, World!' );
		
		$env = array();
		list( $status, $headers, $body ) =
		  Prack_ContentType::with( $middleware_app, 'application/octet-stream' )->call( $env );
		
		$this->assertEquals( 'application/octet-stream', $headers[ 'Content-Type' ] );
	} // It should set Content-Type to chosen default if none is set
	
	/**
	 * It should not change Content-Type if it is already set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_change_Content_Type_if_it_is_already_set()
	{
		$middleware_app = new Prack_Test_Echo( 200, array( 'Content-Type' => 'foo/bar' ), 'Hello, World!' );
		
		$env = array();
		list( $status, $headers, $body ) = Prack_ContentType::with( $middleware_app )->call( $env );
		
		$this->assertEquals( 'foo/bar', @$headers[ 'Content-Type' ] );
	} // It should not change Content-Type if it is already set
	
	/**
	 * It should detect Content-Type case insensitive
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_detect_Content_Type_case_insensitive()
	{
		$middleware_app = new Prack_Test_Echo( 200, array( 'CONTENT-Type' => 'foo/bar' ), 'Hello, World!' );
		
		$env = array();
		list( $status, $headers, $body ) = Prack_ContentType::with( $middleware_app )->call( $env );
		
		$selected = null;
		foreach ( $headers as $key => $value )
		{
			if ( strtolower( $key ) == 'content-type' )
			{
				$selected = array( $key => $value );
				break;
			}
		}
		
		$this->assertEquals( array( 'CONTENT-Type' => 'foo/bar' ), $selected );
	} // It should detect Content-Type case insensitive
}