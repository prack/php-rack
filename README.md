Prack
=====

Ruby's Rack ported to PHP 5.2+.

A PHP 5.3-only version will be implemented in its own branch in the long-term,
but work-related projects require PHP 5.2 support for now.

You can learn more about Rack on [its homepage](http://rack.rubyforge.org/ "Rack Homepage").

The webserver interface specification upon which Rack and Prack are built is 
[here](http://rack.rubyforge.org/doc/SPEC.html "Web Server Interface Specification").


Running Tests
-------------

This project is designed using test-driven development. I've made the test
method names as descriptive and consistent as possible, so please check them
out for further information until the project matures a bit more.

To run tests:
	git clone https://onethirtyfive@github.com/onethirtyfive/prack.git
	cd Prack
	phpunit

Of course, you must have PHPUnit installed, preferably alongside XDebug. I'm using
PHPUnit 3.5.


Getting started
---------------

To date, Prack has two classes: Prack_Builder and PrackURLMap.

Prack_Builder is the class for setting up middleware:

	require 'autoload.php' // This must be required relative to where you've placed
	                       // Prack in your website document root.
	
	$domain = Prack_Builder::domain();
	
	// Let's construct a simple domain behind a hypothetical HTTP auth middleware:
	$domain->
	  using( 'HTTPAuthentication' )
	    ->withArgs( array( 'SomeMiddlewareConfigClass', 'authenticate' ) )
	  run( new EchoServer() );

This example assumes the existence of several classes:

	class HTTPAuthentication implements Prack_IMiddlewareApp
	{
		private $app;       // The app which this one encompasses.
		private $callback;  // Internal middleware state: the callback to invoke when
		                    // middleware is invoked.
		
		function __construct( $app, $callback )
		{
			$this->app      = $app;
			$this->callback = $callback;
		}
		
		public function call( &$env )
		{
			$auth_header = $env['HTTP_AUTHORIZATION']; // For simplicity.
			
			// ... process credentials into:
			$username = 'admin';  /* username should obviously be extracted from the request. */
			$password = 'secret'; /* same for password. derp. */
			
			if ( is_callable( $this->callback ) ) // an array of class, method should eval to true
				$authorized = call_user_func_array( $callback, array( $username, $password ) );
				
			if ( !$authorized )
				return array( 401, array( 'WWW-Authenticate' => /* for simplicity */ ), 'Unauthorized' );
			
			// And finally, if they're authorized, forward the request to the enclosed app,
			// returning its value.
			$response =  $this->app->call( $env );
			
			// Note that it's possible to modify the response here if you want, on its way 'out.'
			
			return $response;
		}
	}

All middleware must conform to the Prack_IMiddlewareApp interface, which is stupidly
straight-forward:

		interface Prack_IMiddlewareApp
		{
			public function call( &$env );
		}

I put this restriction in place for 5.2-compatibility, but when 5.3 is implemented,
I will revisit the decision on account of the viability of anonymous functions.

The middleware configuration class might look like this:

	class SomeMiddlewareConfigClass
	{
		static public function authenticate( $username, $password )
		{
			return ( $username == 'admin' && $password == 'secret' );
		}
	}

Obviously, this callback-based structure is due to lack of closures in PHP 5.2; however,
I think it will be possible to jimmy in anonymous functions easily with this approach
at a later time.

The builder is also responsible for building a domain. It's invoked thusly, when you
want to construct a middleware stack by 'mapping' out applications on your site:

	require 'autoload.php';
	
	$domain = Prack_Builder::domain();
	
	$domain->
	  map( '/admin' )->wherein()->
	    using( 'HTTPAuthentication' )
	      ->withArgs( array( 'SomeMiddlewareConfigClass', 'callbackFunction' ) )->
	    using( 'Benchmarker' )
	      ->withArgs()
	    run( new WebsiteAdminApplication() )
	  map( '/' )->wherein()->
	    run( new WebsiteApplication() );

Prack_Builder supports nested mapping.

You can also supply hosts in in the call to map(), i.e. 'http://example.org/admin'.
HTTP and HTTPS, as with Ruby's Rack, are the only protocols supported.

All requests to '/admin', for example, will be routed through the mapped middleware.


To Do
-----

A whole, whole lot, including but not limited to:

* Multipart-form processing
* Lint middleware, for ensuring request sanity
* Actual implementation of HTTP auth (basic, digest, etc.) and other essential middleware
* E-tag generation
* Middleware to make apache's mod_php compatible with the Rack specfication
* Stream and logger wrappers
* HTTP request method override (for REST) middleware

And a whole ton more, added as I have time.
