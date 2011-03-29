<?php

// TODO: Document!
class Prack_Auth_BasicTest extends PHPUnit_Framework_TestCase 
{
	// TODO: Document!
	public function realm()
	{
		return 'WallysWorld';
	}
	
	// TODO: Document!
	public function unprotectedMiddlewareApp()
	{
		return new Prack_Test_Echo(
			0,
			array(),
			array(),
			' $this->status  = 200;
			  $this->headers = array( "Content-Type" => "text/plain" );
			  $this->body    = array( "Hi {$env[ \'REMOTE_USER\' ]}" );'
		);
	}
	
	// TODO: Document!
	public function protectedMiddlewareApp()
	{
		$callback       = create_function( '$username,$password', 'return "Boss" == $username;' );
		$middleware_app = Prack_Auth_Basic::with( $this->unprotectedMiddlewareApp(), null, $callback );
		$middleware_app->setRealm( $this->realm() );
		return $middleware_app;
	}
	
	// TODO: Document!
	function setUp()
	{
		$this->request = Prack_Mock_Request::with( $this->protectedMiddlewareApp() );
	}
	
	// TODO: Document!
	public function request( $headers, $callback )
	{
		return call_user_func( $callback, $this->request->get( '/', $headers ) );
	}
	
	// TODO: Document!
	public function requestWithBasicAuth( $username, $password, $callback )
	{
		return $this->request(
		  array( 'HTTP_AUTHORIZATION' => 'Basic '.base64_encode( "{$username}:{$password}" ) ), $callback
		);
	}
	
	// TODO: Document!
	public function assertBasicAuthChallenge( $response )
	{
		$this->assertTrue( $response->isClientError() );
		$this->assertEquals( 401, $response->getStatus() );
		$this->assertTrue( $response->contains( 'WWW-Authenticate' ) );
		$this->assertRegExp( "/Basic realm=\"{$this->realm()}\"/", $response->get( 'WWW-Authenticate' ) );
		$this->assertEquals( '', $response->getBody() );
	}
	
	/**
	 * It should challenge correctly when no credentials are specified
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_challenge_correctly_when_no_credentials_are_specified()
	{
		$this->request( array(), array( $this, 'assertBasicAuthChallenge' ) );
	} // It should challenge correctly when no credentials are specified
	
	/**
	 * It should rechallenge if incorrect credentials are specified
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_rechallenge_if_incorrect_credentials_are_specified()
	{
		$this->requestWithBasicAuth( 'joe', 'password', array( $this, 'assertBasicAuthChallenge' ) );
	} // It should rechallenge if incorrect credentials are specified
	
	/**
	 * It should return application output if correct credentials are specified
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_application_output_if_correct_credentials_are_specified()
	{
		$this->requestWithBasicAuth( 'Boss', 'password', array( $this, 'onCorrectCredentials' ) );
	} // It should return application output if correct credentials are specified
	
	// TODO: Document!
	public function onCorrectCredentials( $response )
	{
		$this->assertEquals( 200,       $response->getStatus() );
		$this->assertEquals( 'Hi Boss', $response->getBody()   );
	}
	
	/**
	 * It should return 400 Bad Request if different auth scheme used
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_400_Bad_Request_if_different_auth_scheme_used()
	{
		$this->request( array( 'HTTP_AUTHORIZATION' => 'Digest params' ), array( $this, 'onWrongScheme' ) );
	} // It should return 400 Bad Request if different auth scheme used
	
	// TODO: Document!
	public function onWrongScheme( $response )
	{
		$this->assertTrue( $response->isClientError() );
		$this->assertEquals( 400, $response->getStatus() );
		$this->assertFalse( $response->contains( 'WWW-Authenticate' ) );
	}
	
	/**
	 * It takes realm as an optional constructor arg
	 * @author Joshua Morris
	 * @test
	 */
	public function It_takes_realm_as_an_optional_constructor_arg()
	{
		$middleware_app = Prack_Auth_Basic::with( $this->unprotectedMiddlewareApp(), $this->realm() );
		$this->assertEquals( $this->realm(), $middleware_app->realm() );
	} // It takes realm as an optional constructor arg
}