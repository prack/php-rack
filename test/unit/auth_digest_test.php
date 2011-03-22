<?php

// TODO: Document!
class Prack_Auth_Digest_MockRequest
{
	private $params;
	
	// TODO: Document!
	static function with( $params )
	{
		return new Prack_Auth_Digest_MockRequest( $params );
	}
	
	// TODO: Document!
	function __construct( $params )
	{
		$this->params = $params;
	}
	
	// TODO: Document!
	function __call( $method, $args )
	{
		if ( $this->params->contains( $method ) )
			return $this->params->get( $method );
		throw new RuntimeException( 'attempt to access missing key in Prack_Auth_Digest_MockRequest' );
	}
	
	// TODO: Document!
	public function method()
	{
		return $this->params->get( 'method' );
	}
	
	// TODO: Document!
	public function response( $password )
	{
		return Prack_Auth_Digest_MD5::with( null )->digest( $this, $password );
	}
}

// TODO: Document!
class Prack_Auth_DigestTest extends PHPUnit_Framework_TestCase 
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
				$remote_user   = $env->contains( \'REMOTE_USER\' ) ? $env->get( \'REMOTE_USER\' ) : Prb::Str();
			  $this->body    = Prb::Ary( array( Prb::Str( "Hi {$remote_user->raw()}" ) ) ); '
		);
	}
	
	// TODO: Document!
	public function protectedMiddlewareApp()
	{
		$callback = create_function(
		  '$username',
		  'return Prb::Hsh( array( "Alice" => Prb::Str( "correct-password" ) ) )->get( $username->raw() );'
		);
		
		$middleware_app = Prack_Auth_Digest_MD5::with( $this->unprotectedMiddlewareApp(), null, $callback );
		$middleware_app->setRealm( $this->realm() );
		$middleware_app->setOpaque( Prb::Str( 'this-should-be-secret' ) );
		
		return $middleware_app;
	}
	
	// TODO: Document!
	public function protectedMiddlewareAppWithHashedPasswords()
	{
		$callback = array( $this, 'onHashedPasswords' );
		
		$middleware_app = Prack_Auth_Digest_MD5::with( $this->unprotectedMiddlewareApp(), null, $callback );
		$middleware_app->setRealm( $this->realm() );
		$middleware_app->setOpaque( Prb::Str( 'this-should-be-secret' ) );
		$middleware_app->setPasswordsHashed( true );
		
		return $middleware_app;
	}
	
	// TODO: Document!
	public function onHashedPasswords( $username )
	{
		return isset( $username ) && $username->raw() == 'Alice'
		  ? Prb::Str( md5( "Alice:".$this->realm()->raw().":correct-password" ) )
		  : null;
	}
	
	// TODO: Document!
	public function partiallyProtectedMiddlewareApp()
	{
		return Prack_URLMap::with(
		  Prb::Hsh( array(
		    '/'          => $this->unprotectedMiddlewareApp(),
		    '/protected' => $this->protectedMiddlewareApp()
		  ) )
		);
	}
	
	// TODO: Document!
	public function protectedMiddlewareAppWithMethodOverride()
	{
		return Prack_MethodOverride::with( $this->protectedMiddlewareApp() );
	}
	
	// TODO: Document!
	function setUp()
	{
		$this->request = Prack_Mock_Request::with( $this->protectedMiddlewareApp() );
	}
	
	// TODO: Document!
	public function request( $method, $path, $headers = null, $callback = null )
	{
		if ( is_null( $headers ) )
			$headers = Prb::Hsh();
		
		$response = $this->request->request( $method, $path, $headers );
		
		if ( is_callable( $callback ) )
			call_user_func( $callback, $response );
		
		return $response;
	}
	
	// TODO: Document!
	public function requestWithDigestAuth( $method, $path, $username, $password, $options = null, $callback = null )
	{
		if ( is_null( $options ) )
			$options = Prb::Hsh();
		
		$request_options = Prb::Hsh();
		
		if ( $options->contains( 'input' ) )
			$request_options->set( 'input', $options->delete( 'input' ) );
		
		$response = $this->request( $method, $path, $request_options );
		
		if ( !( $response->getStatus()->raw() == 401 ) )
			return $response;
		
		if ( $wait = $options->delete( 'wait' ) )
			sleep( $wait->raw() );
		
		$challenge = $response->get( 'WWW-Authenticate' )->split( '/ /', 2 )->last();
		
		$params = Prack_Auth_Digest_Params::parse( $challenge );
		
		$params->set( 'username', $username                   );
		$params->set( 'nc',       Prb::Str( '00000001'      ) );
		$params->set( 'cnonce',   Prb::Str( 'nonsensenonce' ) );
		$params->set( 'uri',      $path                       );
		$params->set( 'method',   $method                     );
		
		$params->update( $options );
		$params->set( 'response', Prack_Auth_Digest_MockRequest::with( $params )->response( $password ) );
		
		return $this->request(
		  $method,
		  $path,
		  $request_options->merge(
		    Prb::Hsh( array(
		      'HTTP_AUTHORIZATION' => Prb::Str( 'Digest ' )->concat( $params->toS() )
		    ) )
		  ),
		  $callback
		);
	}
	
	// TODO: Document!
	public function assertDigestAuthChallenge( $response )
	{
		$this->assertTrue( $response->isClientError() );
		$this->assertEquals( 401, $response->getStatus()->raw() );
		$this->assertTrue( $response->contains( 'WWW-Authenticate' ) );
		$this->assertTrue( $response->get( 'WWW-Authenticate' )->match( '/^Digest /') );
		$this->assertTrue( $response->getBody()->isEmpty() );
	}
	
	// TODO: Document!
	public function assertBadRequest( $response )
	{
		$this->assertTrue( $response->isClientError() );
		$this->assertEquals( 400, $response->getStatus()->raw() );
		$this->assertFalse( $response->contains( 'WWW-Authenticate' ) );
	}
	
	/**
	 * It should challenge when no credentials are specified
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_challenge_when_no_credentials_are_specified()
	{
		$callback = array( $this, 'assertDigestAuthChallenge' );
		$this->request( Prb::Str( 'GET' ), Prb::Str( '/' ), null, $callback );
	} // It should challenge when no credentials are specified
	
	/**
	 * It should return application output if correct credentials given
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_application_output_if_correct_credentials_given()
	{
		$callback = array( $this, 'onCorrectCredentials' );
		$this->requestWithDigestAuth(
		  Prb::Str( 'GET'   ),
		  Prb::Str( '/'     ),
		  Prb::Str( 'Alice' ),
		  Prb::Str( 'correct-password' ),
		  null,
		  $callback
		);
	} // It should return application output if correct credentials given
	
	// TODO: Document!
	public function onCorrectCredentials( $response )
	{
		$this->assertEquals( 200, $response->getStatus()->raw() );
		$this->assertEquals( 'Hi Alice', $response->getBody()->toS()->raw() );
	}
	
	/**
	 * It should return application output if correct credentials given (hashed passwords)
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_application_output_if_correct_credentials_given__hashed_passwords_()
	{
		$request  = Prack_Mock_Request::with( $this->protectedMiddlewareAppWithHashedPasswords() );
		$callback = array( $this, 'onCorrectCredentials' );
		
		$this->requestWithDigestAuth(
		  Prb::Str( 'GET'   ),
		  Prb::Str( '/'     ),
		  Prb::Str( 'Alice' ),
		  Prb::Str( 'correct-password' ),
		  null,
		  $callback
		);
	} // It should return application output if correct credentials given (hashed passwords)
	
	/**
	 * It should rechallenge if incorrect username given
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_rechallenge_if_incorrect_username_given()
	{
		$callback = array( $this, 'assertDigestAuthChallenge' );
		$this->requestWithDigestAuth(
		  Prb::Str( 'GET'   ),
		  Prb::Str( '/'     ),
		  Prb::Str( 'Bob'   ),
		  Prb::Str( 'correct-password' ),
		  null,
		  $callback
		);
	} // It should rechallenge if incorrect username given
	
	/**
	 * It should rechallenge if incorrect password given
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_rechallenge_if_incorrect_password_given()
	{
		$callback = array( $this, 'assertDigestAuthChallenge' );
		$this->requestWithDigestAuth(
		  Prb::Str( 'GET'   ),
		  Prb::Str( '/'     ),
		  Prb::Str( 'Bob'   ),
		  Prb::Str( 'wrong-password' ),
		  null,
		  $callback
		);
	} // It should rechallenge if incorrect password given
	
	/**
	 * It should rechallenge with stale parameter if nonce is stale
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_rechallenge_with_stale_parameter_if_nonce_is_stale()
	{
		$callback = array( $this, 'assertDigestAuthChallenge' );
		Prack_Auth_Digest_Nonce::setTimeLimit( Prb::Num( 1 ) );
		
		try
		{
			$this->requestWithDigestAuth(
			  Prb::Str( 'GET'   ),
			  Prb::Str( '/'     ),
			  Prb::Str( 'Bob'   ),
			  Prb::Str( 'correct-password' ),
			  Prb::Hsh( array( 'wait' => Prb::Num( 2 ) ) ),
			  $callback
			);
		}
		catch ( Exception $e )
		{ }
		
		Prack_Auth_Digest_Nonce::setTimeLimit( null );
	} // It should rechallenge with stale parameter if nonce is stale
	
	/**
	 * It should return 400 Bad Request if incorrect qop given
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_400_Bad_Request_if_incorrect_qop_given()
	{
		$callback = array( $this, 'assertBadRequest' );
		
		$this->requestWithDigestAuth(
		  Prb::Str( 'GET'   ),
		  Prb::Str( '/'     ),
		  Prb::Str( 'Alice' ),
		  Prb::Str( 'correct-password' ),
		  Prb::Hsh( array( 'qop' => Prb::Str( 'auth-int' ) ) ),
		  $callback
		);
	} // It should return 400 Bad Request if incorrect qop given
	
	/**
	 * It should return 400 Bad Request if incorrect URI given
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_400_Bad_Request_if_incorrect_URI_given()
	{
		$callback = array( $this, 'assertBadRequest' );
		
		$this->requestWithDigestAuth(
		  Prb::Str( 'GET'   ),
		  Prb::Str( '/'     ),
		  Prb::Str( 'Alice' ),
		  Prb::Str( 'correct-password' ),
		  Prb::Hsh( array( 'uri' => Prb::Str( '/foo' ) ) ),
		  $callback
		);
	} // It should return 400 Bad Request if incorrect URI given
	
	/**
	 * It should return 400 Bad Request if different auth scheme used
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_400_Bad_Request_if_different_auth_scheme_used()
	{
		$callback = array( $this, 'assertBadRequest' );
		
		$this->request(
		  Prb::Str( 'GET'   ),
		  Prb::Str( '/'     ),
		  Prb::Hsh( array( 'HTTP_AUTHORIZATION' => Prb::Str( 'Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==' ) ) ),
		  $callback
		);
	} // It should return 400 Bad Request if different auth scheme used
	
	/**
	 * It should not require credentials for unprotected path
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_require_credentials_for_unprotected_path()
	{
		$this->request = Prack_Mock_Request::with( $this->partiallyProtectedMiddlewareApp() );
		$callback      = array( $this, 'onUnprotectedPath' );
		
		$this->request(
		  Prb::Str( 'GET'   ),
		  Prb::Str( '/'     ),
		  null,
		  $callback
		);
	} // It should not require credentials for unprotected path
	
	// TODO: Document!
	public function onUnprotectedPath( $response )
	{
		$this->assertTrue( $response->isOK() );
	}
	
	/**
	 * It should challenge when no credentials are specified for protected path
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_challenge_when_no_credentials_are_specified_for_protected_path()
	{
		$this->request = Prack_Mock_Request::with( $this->partiallyProtectedMiddlewareApp() );
		$callback      = array( $this, 'assertDigestAuthChallenge' );
		
		$this->request(
		  Prb::Str( 'GET'        ),
		  Prb::Str( '/protected' ),
		  null,
		  $callback
		);
	} // It should challenge when no credentials are specified for protected path
	
	/**
	 * It should return application output if correct credentials given for protected path
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_application_output_if_correct_credentials_given_for_protected_path()
	{
		$callback = array( $this, 'onCorrectCredentials' );
		$this->requestWithDigestAuth(
		  Prb::Str( 'GET'        ),
		  Prb::Str( '/protected' ),
		  Prb::Str( 'Alice'      ),
		  Prb::Str( 'correct-password' ),
		  null,
		  $callback
		);
	} // It should return application output if correct credentials given for protected path
	
	/**
	 * It should return application output if correct credentials given for POST
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_application_output_if_correct_credentials_given_for_POST()
	{
		$callback = array( $this, 'onCorrectCredentials' );
		$this->requestWithDigestAuth(
		  Prb::Str( 'POST'       ),
		  Prb::Str( '/protected' ),
		  Prb::Str( 'Alice'      ),
		  Prb::Str( 'correct-password' ),
		  null,
		  $callback
		);
	} // It should return application output if correct credentials given for POST
	
	/**
	 * It should return application output if correct credentials given for PUT (using method override of POST)
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_application_output_if_correct_credentials_given_for_PUT__using_method_override_of_POST_()
	{
		$this->request = Prack_Mock_Request::with( $this->protectedMiddlewareAppWithMethodOverride() );
		$callback      = array( $this, 'onCorrectCredentials' );
		$this->requestWithDigestAuth(
		  Prb::Str( 'POST'       ),
		  Prb::Str( '/protected' ),
		  Prb::Str( 'Alice'      ),
		  Prb::Str( 'correct-password' ),
		  Prb::Hsh( array( 'input' => Prb::Str( '_method=put' ) ) ),
		  $callback
		);
	} // It should return application output if correct credentials given for PUT (using method override of POST)
	
	/**
	 * It takes realm as optional constructor arg
	 * @author Joshua Morris
	 * @test
	 */
	public function It_takes_realm_as_optional_constructor_arg()
	{
		$middleware_app = Prack_Auth_Digest_MD5::with( $this->unprotectedMiddlewareApp(), $this->realm() );
		$this->assertEquals( $middleware_app->realm(), $this->realm() );
	} // It takes realm as optional constructor arg
}