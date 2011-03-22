<?php

// TODO: Document!
class Prack_Auth_BasicTest extends PHPUnit_Framework_TestCase 
{
	// TODO: Document!
	public function realm()
	{
		return Prb::Str( 'WallysWorld' );
	}
	
	// TODO: Document!
	public function unprotectedMiddlewareApp()
	{
		return new Prack_Test_Echo(
			Prb::Num( 0 ),
			Prb::Hsh(),
			Prb::Ary(),
			' $this->status  = Prb::Num( 200 );
			  $this->headers = Prb::Hsh( array( "Content-Type" => Prb::Str( "text/plain" ) ) );
			  $this->body    = Prb::Ary( array( Prb::Str( "Hi {$env->get( \'REMOTE_USER\' )->raw()}" ) ) ); '
		);
	}
	
	// TODO: Document!
	public function protectedMiddlewareApp()
	{
		$callback       = create_function( '$username,$password', 'return "Boss" == $username->raw();' );
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
		return call_user_func( $callback, $this->request->get( Prb::Str( '/' ), $headers ) );
	}
	
	// TODO: Document!
	public function requestWithBasicAuth( $username, $password, $callback )
	{
		return $this->request(
		  Prb::Hsh( array( 
		    'HTTP_AUTHORIZATION' => Prb::Str( 'Basic ' )->concat(
		      Prb::Str( "{$username->raw()}:{$password->raw()}" )->base64Encode()
		    )
		  ) ),
		  $callback
		);
	}
	
	// TODO: Document!
	public function assertBasicAuthChallenge( $response )
	{
		$this->assertTrue( $response->isClientError() );
		$this->assertEquals( 401, $response->getStatus()->raw() );
		$this->assertTrue( $response->contains( 'WWW-Authenticate' ) );
		$quoted = preg_quote( $this->realm()->raw() );
		$this->assertTrue( $response->get( 'WWW-Authenticate' )->match( "/Basic realm=\"{$quoted}\"/" ) );
		$this->assertTrue( $response->getBody()->isEmpty() );
	}
	
	/**
	 * It should challenge correctly when no credentials are specified
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_challenge_correctly_when_no_credentials_are_specified()
	{
		$this->request( Prb::Hsh(), array( $this, 'assertBasicAuthChallenge' ) );
	} // It should challenge correctly when no credentials are specified
	
	/**
	 * It should rechallenge if incorrect credentials are specified
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_rechallenge_if_incorrect_credentials_are_specified()
	{
		$this->requestWithBasicAuth(
		  Prb::Str( 'joe'      ),
		  Prb::Str( 'password' ),
		  array( $this, 'assertBasicAuthChallenge' )
		);
	} // It should rechallenge if incorrect credentials are specified
	
	/**
	 * It should return application output if correct credentials are specified
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_application_output_if_correct_credentials_are_specified()
	{
		$this->requestWithBasicAuth(
		  Prb::Str( 'Boss'     ),
		  Prb::Str( 'password' ),
		  array( $this, 'onCorrectCredentials' )
		);
	} // It should return application output if correct credentials are specified
	
	// TODO: Document!
	public function onCorrectCredentials( $response )
	{
		$this->assertEquals( 200,       $response->getStatus()->raw() );
		$this->assertEquals( 'Hi Boss', $response->getBody()->toS()->raw() );
	}
	
	/**
	 * It should return 400 Bad Request if different auth scheme used
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_400_Bad_Request_if_different_auth_scheme_used()
	{
		$this->request(
		  Prb::Hsh( array(
		    'HTTP_AUTHORIZATION' => Prb::Str( 'Digest params' )
		  ) ),
		  array( $this, 'onWrongScheme' )
		);
	} // It should return 400 Bad Request if different auth scheme used
	
	// TODO: Document!
	public function onWrongScheme( $response )
	{
		$this->assertTrue( $response->isClientError() );
		$this->assertEquals( 400, $response->getStatus()->raw() );
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