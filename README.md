Prack
=====

Ruby's Rack ported to PHP 5.2+, vetted against the ported Ruby specification (test suite).

You can learn more about Rack on [its homepage](http://rack.rubyforge.org/ "Rack Homepage").

The webserver interface specification upon which Rack and Prack are built is 
[here](http://rack.rubyforge.org/doc/SPEC.html "Web Server Interface Specification").

A PHP 5.3-only version of Prack will be implemented in its own branch in the long-term,
but my work-related projects require PHP 5.2 support for now. This means that a lot of
the stuff accomplished with lambdas in Ruby gets done via callback in Prack. For now.

However, I'm doing my best to design all the callback functions to be future-proof.
(OK, so it's actually 'present-proof' and I'm stuck in the past.) If you're running
5.3, you may be able to just drop in anonymous functions and enjoy all the benefits
of closures, but I can't guarantee anything.


Running Tests
-------------

This project is designed using test-driven development. I've made the test
method names as descriptive and consistent as possible, so please check them
out as documentation until the project matures a bit more.

To run tests:
	git clone https://onethirtyfive@github.com/onethirtyfive/prack.git
	cd Prack
	phpunit

Of course, you must have PHPUnit installed, preferably alongside XDebug. I'm using
PHPUnit 3.5.


Getting started
---------------

Prack_Builder is the class for setting up middleware:

	require 'autoload.php' // This must be required relative to where you've placed
	                       // Prack in your website document root.
	
	$domain = Prack_Builder::domain();
	
	// Let's construct a simple domain using a few middleware apps, outside-in,
	// finally running a hypothetical middleware app of class 'EchoServer' :
	$domain->
	  using( 'Prack_Lint' )
	    ->withArgs()
	  using( 'HTTPAuthentication' )
	    ->withArgs( array( 'SomeMiddlewareConfigClass', 'authenticate' ) )->
	  run( new EchoServer() );

This example assumes the existence of several classes:

	// A skeleton of what a middleware app would look like:
	class HTTPAuthentication
	  implements Prack_Interface_MiddlewareApp
	{
		private $app;       // The middleware app this middleware app encompasses,
		                    //   aka. the next middleware app in the stack.
		private $callback;  // Internal middleware app state: the callback to invoke when
		                    // this middleware app is 'called' in series. It's up to this
		                    // middleware app to decide when and how to invoke this.
		
		function __construct( $app, $callback )
		{
			$this->app      = $app;
			$this->callback = $callback;
		}
		
		public function call( $env )
		{
			$auth_header = $env[ 'HTTP_AUTHORIZATION' ]; // This would, in actuality, take more work.
			
			// ... process credentials into:
			$username = 'admin';  /* username should obviously be extracted from the request. */
			$password = 'secret'; /* same for password. derp. */
			
			if ( is_callable( $this->callback ) ) // an array of class, method should eval to true
				$authorized = call_user_func_array( $callback, array( $username, $password ) );
			else
				throw new Prack_Error_Callback( "Can't call http auth callback." );
			
			if ( !$authorized )
				return array( 401, array( 'WWW-Authenticate' => /* for simplicity */ ), 'Unauthorized' );
			
			// And finally, if they're authorized, forward the request to the enclosed app,
			// returning its value as an array of:
			//   1.                        (int)$status
			//   2.                      (array)$headers
			//   3. (Prack_Interface_Enumerable)$body
			// Note: #3 can also be a primitive string if you want, but it's discouraged. Per the Rack
			//   spec, the response body should be an enumerable containing strings or stringable objects.
			list( $status, $headers, $body ) = $this->app->call( $env );
			
			// Note that it's possible to modify the response here if you want, on its way 'out.'
			
			return $response;
		}
	}

All middleware must conform to the Prack\_Interface_MiddlewareApp interface, which is stupidly
straight-forward:

	interface Prack_Interface_MiddlewareApp
	{
		public function call( $env );
	}

I put this interface in place for 5.2-compatibility, but when 5.3 is implemented,
I will revisit whether to drop it so we can also include lambdas as first-class middleware apps.

The middleware configuration class might look like this:

	class SomeMiddlewareConfigClass
	{
		static function authenticate( $username, $password )
		{
			return ( $username == 'admin' && $password == 'secret' );
		}
	}

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

All requests to '/admin', for example, will be routed through HTTPAuthentication first, 
then Benchmarker, ending up finally in WebsiteAdminApplication, which is responsible
for making the same 3-element array and passing it back up the chain. All requests to 
'/' will be routed directly to the WebsiteApplication.

Prack_Builder supports nested mapping, just like Ruby Rack.

If this confuses you, grok the [Rack spec](http://rack.rubyforge.org/doc/SPEC.html "Web Server Interface Specification").
Prack works exactly the same way, even in the environment variable names it uses.

You can also supply hosts in in the call to map(), i.e. 'http://example.org/admin'.
HTTP and HTTPS, as with Ruby's Rack, are the only protocols supported.


Progress
========


Working and Shippable
---------------------

* Prack\_DelegateFor_Response: quasi-module to provide response objects (mock and actual) a rich vocab of helpers
* Prack\_Mock\_Request: Fake requests for testing
* Prack\_Mock\_Response: Fake responses for testing, delegates some methods
* Prack\_Utils\_Response\_HeaderHash: A case-insensitive, multiple-value supporting assoc array wrapper
* Interfaces: MiddlewareApp, ReadableStreamlike, WritableStreamlike, Enumerable, LengthAware (for streams), Logger

Working perfectly, but not feature-complete
-------------------------------------------

* Prack\_Wrapper\_*: A suite of primitive wrappers, enabling enumeration and more (pending: sets, more core functionality for all wrappers)
* Prack\_Lint\_: Ensures response sanity. (pending: sessions, logger)
* Prack_Request: Actual request (pending: multipart form data)
* Prack_Response: Actual response, delegates some methods (pending: cookie management)
* Prack\_Utils_\_IO\_*: A suite of IO wrappers: string, tempfiles. (pending: php streams, file streams)

Works, but doesn't conform to Ruby Rack Spec
--------------------------------------------

* Prack_Builder: Fluent interface for building middleware stacks in a domain
* Prack_RewindableInput: Wrapper implements rewindability for stdin and other streams
* Prack_URLMap: Used by Builder to map middleware (stacks) to a URL endpoints

Incubating
----------

* Want to clean up error namespace.
* Rework of core Rack functionality using new, highly functional hash, array, and string wrappers
* Documentation on when to use native PHP primitives (array et al.) vs. Prack's types
* Prack_Utils: This is gonna have a lot of stuff in it, some of which comes natively from PHP
* Everything else in Ruby Rack :)


To Do
-----

* Rack config
* PHP stream, File stream wrappers
* Sessions
* Cookies
* Multipart-form-data processing
* Generic Logger
* Middleware to make apache's mod_php compatible with the Rack specfication
* Prack Attack (Rack Lobster analog)
* E-tag generation
* Actual implementation of HTTP auth (basic, digest, etc.) and other essential middleware
* HTTP request method override middleware


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
---------------

Thanks to the Ruby Rack team for all their hard work on Rack, and thanks to the Python folks
who dreamed up WSGI.
