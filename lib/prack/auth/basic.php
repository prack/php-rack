<?php

// TODO: Document!
class Prack_Auth_Basic_Request extends Prack_Auth_Abstract_Request
{
	private $credentials;
	
	// TODO: Document!
	public function isBasic()
	{
		return ( $this->scheme() == 'basic' );
	}
	
	// TODO: Document!
	public function credentials()
	{
		if ( is_null( $this->credentials ) )
			$this->credentials = @preg_split( '/:/', @base64_decode( $this->params() ), 2 );
		return $this->credentials;
	}
	
	// TODO: Document!
	public function username()
	{
		$credentials = $this->credentials();
		return reset( $credentials );
	}
}

// TODO: Document!
# Rack::Auth::Basic implements HTTP Basic Authentication, as per RFC 2617.
#
# Initialize with the Rack application that you want protecting,
# and a block that checks if a username and password pair are valid.
#
# See also: <tt>example/protectedlobster.rb</tt>
class Prack_Auth_Basic extends Prack_Auth_Abstract_Handler
  implements Prack_I_MiddlewareApp
{
	// TODO: Document!
	static function with( $middleware_app, $realm = null, $callback = null )
	{
		return new Prack_Auth_Basic( $middleware_app, $realm, $callback );
	}
	
	// TODO: Document!
	public function call( &$env )
	{
		$auth = new Prack_Auth_Basic_Request( $env );
		
		if ( !$auth->isProvided() )
			return $this->unauthorized();
		
		if ( !$auth->isBasic() )
			return $this->badRequest();
		
		if ( $this->isValid( $auth ) )
		{
			$env[ 'REMOTE_USER' ] = $auth->username();
			return $this->middleware_app->call( $env );
		}
		
		return $this->unauthorized();
	}
	
	// TODO: Document!
	public function isValid( $auth )
	{
		return call_user_func_array( $this->callback, $auth->credentials() );
	}
	
	// TODO: Document!
	protected function challenge()
	{
		return sprintf( 'Basic realm="%s"', $this->realm() );
	}
}