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
		throw new RuntimeException( "attempt to access missing key {$method} in Prack_Auth_Digest_MockRequest" );
	}
	
	// TODO: Document!
	public function nonce()
	{
		return Prack_Auth_Digest_Nonce::parse( $this->params->get( 'nonce' ) );
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
				$remote_user   = (string)@$env[ \'REMOTE_USER\' ];
			  $this->body    = array( "Hi {$remote_user}" ); '
		);
	}
	
	// TODO: Document!
	public function protectedMiddlewareApp()
	{
		$callback = create_function(
		  '$username',
		  '$lookup = array( "Alice" => "correct-password" ); return @$lookup[ $username ];'
		);
		
		$middleware_app = Prack_Auth_Digest_MD5::with( $this->unprotectedMiddlewareApp(), null, $callback );
		$middleware_app->setRealm( $this->realm() );
		$middleware_app->setOpaque( 'this-should-be-secret' );
		
		return $middleware_app;
	}
	
	// TODO: Document!
	public function protectedMiddlewareAppWithHashedPasswords()
	{
		$callback = array( $this, 'onHashedPasswords' );
		
		$middleware_app = Prack_Auth_Digest_MD5::with( $this->unprotectedMiddlewareApp(), null, $callback );
		$middleware_app->setRealm( $this->realm() );
		$middleware_app->setOpaque( 'this-should-be-secret' );
		$middleware_app->setPasswordsHashed( true );
		
		return $middleware_app;
	}
	
	// TODO: Document!
	public function onHashedPasswords( $username )
	{
		return ( isset( $username ) && $username == 'Alice' )
		  ? md5( "Alice:".$this->realm().":correct-password" )
		  : null;
	}
	
	// TODO: Document!
	public function partiallyProtectedMiddlewareApp()
	{
		return Prack_URLMap::with(
		  array( '/' => $this->unprotectedMiddlewareApp(), '/protected' => $this->protectedMiddlewareApp() ) );
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
			$headers = array();
		
		$response = $this->request->request( $method, $path, $headers );
		
		if ( is_callable( $callback ) )
			call_user_func( $callback, $response );
		
		return $response;
	}
	
	// TODO: Document!
	public function requestWithDigestAuth( $method, $path, $username, $password, $options = array(), $callback = null )
	{
		if ( is_null( $options ) )
			$options = array();
		
		$request_options = array();
		
		if ( @$options[ 'input' ] )
			$request_options[ 'input' ] = @$options[ 'input' ];
		unset( $options[ 'input' ] );
		
		$response = $this->request( $method, $path, $request_options );
		
		if ( $response->getStatus() != 401 )
			return $response;
		
		if ( @$options[ 'wait' ] )
			sleep( $wait );
		unset( $options[ 'wait' ] );
		
		$split     = preg_split( '/ /', $response->get( 'WWW-Authenticate' ), 2 );
		$challenge = end( $split );
		
		$params = Prack_Auth_Digest_Params::parse( $challenge );
		$params->set( 'username', $username       );
		$params->set( 'nc',       '00000001'      );
		$params->set( 'cnonce',   'nonsensenonce' );
		$params->set( 'uri',      $path           );
		$params->set( 'method',   $method         );
		
		foreach ( $options as $key => $value )
			$params->set( $key, $value );
		
		$params->set( 'response', Prack_Auth_Digest_MockRequest::with( $params )->response( $password ) );
		
		return $this->request(
			$method,
		  $path,
		  array_merge( $request_options, array( 'HTTP_AUTHORIZATION' => 'Digest '.$params->raw() ) ),
		  $callback
		);
	}
	
	// TODO: Document!
	public function assertDigestAuthChallenge( $response )
	{
		$this->assertTrue( $response->isClientError() );
		$this->assertEquals( 401, $response->getStatus() );
		$this->assertTrue( $response->contains( 'WWW-Authenticate' ) );
		$this->assertRegExp( '/^Digest /', $response->get( 'WWW-Authenticate' ) );
		$this->assertEquals( '', $response->getBody() );
	}
	
	// TODO: Document!
	public function assertBadRequest( $response )
	{
		$this->assertTrue( $response->isClientError() );
		$this->assertEquals( 400, $response->getStatus() );
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
		$this->request( 'GET', '/', null, $callback );
	} // It should challenge when no credentials are specified
	
	/**
	 * It should return application output if correct credentials given
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_application_output_if_correct_credentials_given()
	{
		$callback = array( $this, 'onCorrectCredentials' );
		$this->requestWithDigestAuth( 'GET', '/', 'Alice', 'correct-password', null, $callback );
	} // It should return application output if correct credentials given
	
	// TODO: Document!
	public function onCorrectCredentials( $response )
	{
		$this->assertEquals( 200, $response->getStatus() );
		$this->assertEquals( 'Hi Alice', $response->getBody() );
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
		$this->requestWithDigestAuth( 'GET', '/', 'Alice', 'correct-password', null, $callback );
	} // It should return application output if correct credentials given (hashed passwords)
	
	/**
	 * It should rechallenge if incorrect username given
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_rechallenge_if_incorrect_username_given()
	{
		$callback = array( $this, 'assertDigestAuthChallenge' );
		$this->requestWithDigestAuth( 'GET', '/', 'Bob', 'correct-password', null, $callback );
	} // It should rechallenge if incorrect username given
	
	/**
	 * It should rechallenge if incorrect password given
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_rechallenge_if_incorrect_password_given()
	{
		$callback = array( $this, 'assertDigestAuthChallenge' );
		$this->requestWithDigestAuth( 'GET', '/', 'Bob', 'wrong-password', null, $callback );
	} // It should rechallenge if incorrect password given
	
	/**
	 * It should rechallenge with stale parameter if nonce is stale
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_rechallenge_with_stale_parameter_if_nonce_is_stale()
	{
		$callback = array( $this, 'assertDigestAuthChallenge' );
		Prack_Auth_Digest_Nonce::setTimeLimit( 1 );
		
		try
		{
			$this->requestWithDigestAuth( 'GET', '/', 'Bob', 'correct-password', array( 'wait' => 2 ), $callback );
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
		$this->requestWithDigestAuth( 'GET', '/', 'Alice', 'correct-password', array( 'qop' => 'auth-int' ), $callback );
	} // It should return 400 Bad Request if incorrect qop given
	
	/**
	 * It should return 400 Bad Request if incorrect URI given
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_400_Bad_Request_if_incorrect_URI_given()
	{
		$callback = array( $this, 'assertBadRequest' );
		$this->requestWithDigestAuth( 'GET', '/', 'Alice', 'correct-password', array( 'uri' => '/foo' ), $callback );
	} // It should return 400 Bad Request if incorrect URI given
	
	/**
	 * It should return 400 Bad Request if different auth scheme used
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_400_Bad_Request_if_different_auth_scheme_used()
	{
		$callback = array( $this, 'assertBadRequest' );
		$this->request( 'GET', '/', array( 'HTTP_AUTHORIZATION' => 'Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==' ), $callback );
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
		$this->request( 'GET', '/', null, $callback );
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
		$this->request( 'GET', '/protected', null, $callback );
	} // It should challenge when no credentials are specified for protected path
	
	/**
	 * It should return application output if correct credentials given for protected path
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_application_output_if_correct_credentials_given_for_protected_path()
	{
		$callback = array( $this, 'onCorrectCredentials' );
		$this->requestWithDigestAuth( 'POST', '/protected', 'Alice', 'correct-password', null, $callback );
	} // It should return application output if correct credentials given for protected path
	
	/**
	 * It should return application output if correct credentials given for POST
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_application_output_if_correct_credentials_given_for_POST()
	{
		$callback = array( $this, 'onCorrectCredentials' );
		$this->requestWithDigestAuth( 'POST', '/protected', 'Alice', 'correct-password', null, $callback );
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
		$this->requestWithDigestAuth( 'POST', '/protected', 'Alice', 'correct-password', array( 'input' => '_method=put' ), $callback );
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