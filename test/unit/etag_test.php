<?php

// TODO: Document!
class Prack_ETagTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * It should set ETag if none is set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_set_ETag_if_none_is_set()
	{
		$middleware_app = new Prack_Test_Echo(
		  Prb::Num( 200 ),
		  Prb::Hsh( array( 'Content-Type' => Prb::Str( 'text/plain' ) ) ),
		  Prb::Ary( array( Prb::Str( 'Hello, World!' ) ) )
		);
		$response = Prack_ETag::with( $middleware_app )->call( Prb::Hsh() );
		$this->assertEquals( "\"65a8e27d8879283831b664bd8b7f0ad4\"", $response->get( 1 )->get( 'ETag' )->raw() );
	} // It should set ETag if none is set
	
	/**
	 * It should not change ETag if it is already set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_change_ETag_if_it_is_already_set()
	{
		$middleware_app = new Prack_Test_Echo(
		  Prb::Num( 200 ),
		  Prb::Hsh( array(
		    'Content-Type' => Prb::Str( 'text/plain' ),
		    'ETag'         => Prb::Str( '"abc"' )
		  ) ),
		  Prb::Ary( array( Prb::Str( 'Hello, World!' ) ) )
		);
		$response = Prack_ETag::with( $middleware_app )->call( Prb::Hsh() );
		$this->assertEquals( Prb::Str( "\"abc\"" ), $response->get( 1 )->get( 'ETag' ) );
	} // It should not change ETag if it is already set
}