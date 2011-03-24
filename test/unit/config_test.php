<?php

// TODO: Document!
class Prack_ConfigTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * It should accept a callback that modifies the environment
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_accept_a_callback_that_modifies_the_environment()
	{
		$echo = new Prack_Test_Echo(
		  Prb::Num( 200 ),
		  Prb::Hsh( array( 'Content-Type' => Prb::Str( 'text/plain' ) ) ),
		  Prb::Ary(),
		  ' $this->body = Prb::Ary( array(
		      $env->contains( "greeting" )
		        ? $env->get( "greeting" )
		        : Prb::Str()
		    ) ); '
		);
		
		$callback = array( $this, 'onConfig' );
		$middleware_app = Prack_Builder::domain()
		  ->using( 'Prack_Lint'          )->build()
		  ->using( 'Prack_ContentLength' )->build()
		  ->using( 'Prack_Config'        )->withArgs( $callback )->build()
		  ->run( $echo )
		->toMiddlewareApp();
		
		$response = Prack_Mock_Request::with( $middleware_app )->get( Prb::Str( '/' ) );
		$this->assertEquals( 'hello', $response->getBody()->raw() );
	} // It should accept a callback that modifies the environment
	
	// TODO: Document!
	public function onConfig( $env )
	{
		$env->set( 'greeting', Prb::Str( 'hello' ) );
	}
}