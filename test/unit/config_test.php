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
		  200, array( 'Content-Type' => 'text/plain' ), array(),
		  '$this->body = array( (string)@$env[ "greeting" ] );'
		);
		
		$callback = array( $this, 'onConfig' );
		$middleware_app = Prack_Builder::domain()
		  ->using( 'Prack_Lint'          )->push()
		  ->using( 'Prack_ContentLength' )->push()
		  ->using( 'Prack_Config'        )->withCallback( $callback )->push()
		  ->run( $echo )
		->toMiddlewareApp();
		
		$response = Prack_Mock_Request::with( $middleware_app )->get( '/' );
		$this->assertEquals( 'hello', $response->getBody() );
	} // It should accept a callback that modifies the environment
	
	// TODO: Document!
	public function onConfig( &$env )
	{
		$env[ 'greeting' ] = 'hello';
	}
}