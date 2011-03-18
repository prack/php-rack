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
		$middleware_app = new Prack_Test_Echo(
		  Prb::_Numeric( 200 ), Prb::_Hash(), Prb::_String( 'Hello, World!' )
		);
		
		list( $status, $headers, $body ) =
		  Prack_ContentType::with( $middleware_app )
		    ->call( Prb::_Hash() )
		    ->raw();
		
		$this->assertEquals( 'text/html', $headers->get( 'Content-Type' )->raw() );
	} // It should set Content-Type to default text/html if none is set
	
	/**
	 * It should set Content-Type to chosen default if none is set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_set_Content_Type_to_chosen_default_if_none_is_set()
	{
		$middleware_app = new Prack_Test_Echo(
		  Prb::_Numeric( 200 ), Prb::_Hash(), Prb::_String( 'Hello, World!' )
		);
		
		list( $status, $headers, $body ) =
		  Prack_ContentType::with( $middleware_app, Prb::_String( 'application/octet-stream' ) )
		    ->call( Prb::_Hash() )
		    ->raw();
		
		$this->assertEquals( 'application/octet-stream', $headers->get( 'Content-Type' )->raw() );
	} // It should set Content-Type to chosen default if none is set
	
	/**
	 * It should not change Content-Type if it is already set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_change_Content_Type_if_it_is_already_set()
	{
				$middleware_app = new Prack_Test_Echo(
		  Prb::_Numeric( 200 ),
		  Prb::_Hash( array(
		    'Content-Type' => Prb::_String( 'foo/bar' )
		  ) ),
		  Prb::_String( 'Hello, World!' )
		);
		
		list( $status, $headers, $body ) =
		  Prack_ContentType::with( $middleware_app )
		    ->call( Prb::_Hash() )
		    ->raw();
		
		$this->assertEquals( 'foo/bar', $headers->get( 'Content-Type' )->raw() );
	} // It should not change Content-Type if it is already set
	
	/**
	 * It should detect Content-Type case insensitive
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_detect_Content_Type_case_insensitive()
	{
		$middleware_app = new Prack_Test_Echo(
		  Prb::_Numeric( 200 ),
		  Prb::_Hash( array(
		    'CONTENT-Type' => Prb::_String( 'foo/bar' )
		  ) ),
		  Prb::_String( 'Hello, World!' )
		);
		
		list( $status, $headers, $body ) =
		  Prack_ContentType::with( $middleware_app )
		    ->call( Prb::_Hash() )
		    ->raw();
		
		$callback = array( $this, 'onSelect' );
		$this->assertEquals(
			Prb::_Array( array(
				Prb::_Array( array( 'CONTENT-Type', Prb::_String( 'foo/bar' ) ) )
			) ),
			$headers->toA()->select( $callback )
		);
	} // It should detect Content-Type case insensitive
	
	
	// TODO: Document!
	public function onSelect( $key, $value )
	{
		return strtolower( $key ) == 'content-type';
	}
}