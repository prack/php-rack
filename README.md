_This project is very much a work in progress. Feedback is welcome._

Prack
=====

Prack is Ruby's Rack ported to PHP 5.2+, vetted against the ported Ruby specification (test suite).

You can learn more about Rack on [its homepage](http://rack.rubyforge.org/ "Rack Homepage").
The webserver interface specification upon which Rack and Prack are built is 
[here](http://rack.rubyforge.org/doc/SPEC.html "Rack Specification").

I'm doing my best to design all the callback functions to be future-proof.
If you're running 5.3, you may be able to just drop in anonymous functions and enjoy
the benefits of closures, but I can't guarantee anything.


Dependencies
============

Prack is built on top of a "Rubification" library called
[php-ruby](http://github.com/prack/php-rb "Prb Homepage") ("Prb" for short).

For information on how to use it yourself, visit the above link. You may want to see
phpunit.bootstrap.php file for an example of how to include Prb.


Progress
========

Working and Shippable
---------------------

* Auth_Basic: Basic HTTP authentication
* MethodOverride: allows HTTP request method override via hidden form input or HTTP header
* Logger: Adds a logger to the environment for downstream apps to use
* Builder: Fluent interface for building middleware stacks in a domain
* URLMap: Used by Builder to map middleware (stacks) to a URL endpoints
* Mock_Request: Fake requests for testing
* Mock_Response: Fake responses for testing, delegates some methods
* RewindableInput: Adds rewindability to any stream
* ShowExceptions: Catches uncaught exceptions and show them as pretty HTML with context
* Utils_HeaderHash: A case-insensitive, multiple-value supporting assoc array wrapper
* Mime: a default and configurable set of MIME types
* Interfaces: MiddlewareApp

Working perfectly, but not feature-complete
-------------------------------------------

* Lint: Ensures response sanity. (pending: sessions)
* Request: Actual request (pending: multipart form data)
* Response: Actual response, delegates some methods (pending: cookie management)
* Utils: This is gonna have a lot of stuff in it, some of which comes natively from PHP (pending: multipart, cookies, encoding selection)

Works, but doesn't conform to Ruby Rack Spec
--------------------------------------------

Nothing to list here. See above for things which aren't quite feature complete, but conform to the Ruby spec in their implementation so far.

To Do
-----

* Documentation on when Prack uses PHP primitives vs. Prb wrappers
* Prack\_Apache\_Compat to make apache's mod_php (mostly) compatible with the Rack specfication
* Rack config
* Sessions
* Cookies
* Multipart-form-data processing
* Prack Attack (Rack Lobster analog)
* E-tag generation
* Actual implementation of HTTP auth (digest)
* Everything else in Ruby Rack :)


Running Tests
=============

This project is designed using test-driven development. I've made the test
method names as descriptive and consistent as possible, so please check them
out as documentation until the project matures a bit more.

To run tests:
	git clone https://github.com/prack/php-rack.git
	cd Prack
	phpunit

Of course, you must have PHPUnit installed, preferably alongside XDebug. I'm using
PHPUnit 3.5.


Getting started
===============

All Prack applications must conform to the Prack\_Interface_MiddlewareApp interface, which is
stupidly easy to implement:

	interface Prack_Interface_MiddlewareApp
	{
		public function call( $env ); // $env is a Prb_Hash
	}

I put this interface in place for 5.2-compatibility, but when 5.3 is implemented,
I will revisit whether to drop it so we can also include lambdas as first-class middleware apps.

Prack_Builder is the what you use to set up an application stack:

	require 'autoload.php' // This must be required relative to where you've placed
	                       // Prack in your website document root.
	
	// Let's construct a simple domain using a few middleware apps, outside-in,
	// finally running a hypothetical middleware app of class 'EchoServer' :
	$domain =
	  Prack_Builder::domain()
	    ->using( 'Prack_Apache_Compat' )->build()
	    ->using( 'Prack_Lint' )->build()
	    ->using( 'HTTPAuthentication' )->withCallback( array( 'SomeMiddlewareConfigClass', 'authenticate' ) )->build()
	    ->run( new EchoServer() )
	->toMiddlewareApp();
	
	list( $status, $headers, $body ) = $domain->call( $env )->raw();

Note: Prack_Builder supports nested mapping, just like Ruby Rack.

All requests to your site will be routed through Prack\_Apache\_Compat, then Prack_Lint, then
HTTPAuthentication, ending up finally in EchoServer, which is responsible for returning the
Rack-spec-compliant response back up through the stack. (Whew.)

Unfortunately, Apache isn't a rack-compliant server, and since I haven't written a mod\_php\_rack yet,
the 'Prack\_Apache_Compat' middleware is necessary. It reads the $\_SERVER (and possibly $\_ENV)
superglobals to clobber together an $env which conforms (mostly) to the Rack specification. This
isn't a problem in the test environment, since Prack\_Mock_Request implements a fake env generator.

This is also why Prack\_Apache_Compat is still unimplemented. :P

The above stack assumes the existence of several classes. Here's a look at a hypothetical
HTTPAuthenticate middleware app:

	// A skeleton of what a middleware app would look like:
	class HTTPAuthentication
	  implements Prack_Interface_MiddlewareApp
	{
		private $app;       // The middleware app this middleware app encompasses,
		                    //   aka. the next middleware app in the stack.
		private $callback;  // Internal middleware app state: the callback to invoke when
		                    // this middleware app is 'called' in series. It's up to this
		                    // middleware app to decide when and how to invoke this.
		
		// Once you've specified your stack with builder, this will be invoked automatically
		// when the toMiddlewareApp method is called on your domain. (It constructs the stack.)
		function __construct( $app, $callback )
		{
			$this->app      = $app;
			$this->callback = $callback;
		}
		
		public function call( $env )
		{
			$auth_header = $env->get( 'HTTP_AUTHORIZATION' );
			
			// ... process credentials into:
			$username = 'admin';  /* username should obviously be extracted from the request. */
			$password = 'secret'; /* same for password. derp. */
			
			if ( is_callable( $this->callback ) ) // an array of class, method should eval to true
				$authorized = call_user_func( $callback, $username, $password );
			else
				throw new Prb_Exception_Callback( "Can't call http auth callback." );
			
			if ( !$authorized )
				return Prb::_Array( array(
				  Prb::_Numeric( 401 ),
				  Prb::_Hash( array( 'WWW-Authenticate' => /* header */ ) ),
				  Prb::_String( 'Unauthorized' );
				) );
			
			// And finally, if they're authorized, forward the request to the enclosed app,
			// returning its value as an Prb_Array of:
			//   1.              (Prb_Numeric)$status
			//   2.                 (Prb_Hash)$headers
			//   3. (Prb_Interface_Enumerable)$body    // Body can also be Prb_Interface_Stringable
			list( $status, $headers, $body ) = $this->app->call( $env )->raw();
			
			// Modify $status, $headers, or $body as you see fit here.
			
			return Prb::_Array( array( $status, $headers, $body ) );
		}
	}

The middleware configuration class might look like this:

	class SomeMiddlewareConfigClass
	{
		static function authenticate( $username, $password )
		{
			return ( $username == 'admin' && $password == 'secret' );
		}
	}

If all this confuses you, grok the [Rack spec](http://rack.rubyforge.org/doc/SPEC.html "Rack Interface Specification").
Prack works exactly the same way, even in the environment variable names it uses.


Things I'm would love guidance on/help with
===========================================

* The Prack_Lint implementation and tests is very... pragmatic. The outcome is the same as Rack's,
but the code isn't pretty. If any awesome PHP coders could take a look, I'd be eternally grateful.
* What about string encoding in PHP vs. Ruby? I'm not sure how to handle this, so I'm ignoring it
on account of the fact that PHP strings have no intrinsic encoding. I'm assuming they'll just
function as binaries.
* Obviously, PHP runs in the context of the Apache web server. Prack may not be useful as a
bootstrapping utility (a la Rack), or it may be, but the code structure benefits of a Rack-like
approach are enough for me to think this should be useful.
* Bridging to/from Ruby middleware. Hybrid stacks... possible?


Acknowledgments
===============

Thanks to the Ruby Rack team for all their hard work on Rack, and thanks to the Python folks
who dreamed up WSGI. And thanks to Matz for making such an amazing language.
