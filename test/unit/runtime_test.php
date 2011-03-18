<?php

// TODO: Document!
class Prack_RuntimeTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * It sets X-Runtime if none is set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_sets_X_Runtime_if_none_is_set()
	{
		$middleware_app = new Prack_Test_Echo(
		  Prb::_Numeric( 200 ),
		  Prb::_Hash( array( 'Content-Type' => Prb::_String( 'text/plain' ) ) ),
		  Prb::_String( 'Hello, World!' )
		);
		
		$response = Prack_Runtime::with( $middleware_app )->call( Prb::_Hash() );
		$this->assertTrue( $response->get( 1 )->get( 'X-Runtime' )->match( '/[\d\.]+/' ) );
	} // It sets X-Runtime if none is set
	
	/**
	 * It doesn't set the X-Runtime if it is already set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_doesn_t_set_the_X_Runtime_if_it_is_already_set()
	{
		$middleware_app = new Prack_Test_Echo(
		  Prb::_Numeric( 200 ),
		  Prb::_Hash( array(
		    'Content-Type' => Prb::_String( 'text/plain' ),
		    'X-Runtime'    => Prb::_String( 'foobar'     )
		  ) ),
		  Prb::_String( 'Hello, World!' )
		);
		
		$response = Prack_Runtime::with( $middleware_app )->call( Prb::_Hash() );
		$this->assertEquals( 'foobar', $response->get( 1 )->get( 'X-Runtime' )->raw() );
	} // It doesn't set the X-Runtime if it is already set
	
	/**
	 * It should allow a suffix to be set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_allow_a_suffix_to_be_set()
	{
		$middleware_app = new Prack_Test_Echo(
		  Prb::_Numeric( 200 ),
		  Prb::_Hash( array( 'Content-Type' => Prb::_String( 'text/plain' ) ) ),
		  Prb::_String( 'Hello, World!' )
		);
		
		$response = Prack_Runtime::with( $middleware_app, "Test" )->call( Prb::_Hash() );
		$this->assertTrue( $response->get( 1 )->get( 'X-Runtime-Test' )->match( '/[\d\.]+/' ) );
	} // It should allow a suffix to be set
	
	/**
	 * It should allow multiple timers to be set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_allow_multiple_timers_to_be_set()
	{
		$middleware_app = new Prack_Test_Echo(
		  Prb::_Numeric( 200 ),
		  Prb::_Hash( array( 'Content-Type' => Prb::_String( 'text/plain' ) ) ),
		  Prb::_String( 'Hello, World!' ),
		  'sleep( 0.025 );'
		);
		
		$runtime = Prack_Runtime::with( $middleware_app, "App" );
		
		for ( $i = 0; $i < 50; $i++ )
			$runtime = Prack_Runtime::with( $runtime, (string)$i );
		
		$runtime  = Prack_Runtime::with( $runtime, "All" );
		$response = $runtime->call( Prb::_Hash() );
		
		$this->assertTrue( $response->get( 1 )->get( 'X-Runtime-App' )->match( '/[\d\.]+/' ) );
		$this->assertTrue( $response->get( 1 )->get( 'X-Runtime-All' )->match( '/[\d\.]+/' ) );
		
		$this->assertGreaterThan(
			(float)( $response->get( 1 )->get( 'X-Runtime-App' )->raw() ),
			(float)( $response->get( 1 )->get( 'X-Runtime-All' )->raw() )
		);
		
	} // It should allow multiple timers to be set
}