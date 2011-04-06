Related Projects
================

The other two projects related to php-rack ("Prack") are:

* <tt>livesite</tt>: A website running on Prack. \([repo](https://github.com/prack/livesite)\)
* <tt>php-ruby</tt>: Used internally (and minimally) by Prack to simplify some routines.
\([repo](https://github.com/prack/php-rb)\)

I'm putting these at the top of this README in the hopes that they get some traffic. The livesite
is a wonderful way to understand Prack.

Prack
=====

php-rack ("Prack") is a _partial_, conceptual port of Ruby's Rack. It does differ in some key
ways, as explained below in "Differences from Rack." However, it allows coders to structure
their code similarly to Rack application stacks. Future versions of Prack will hopefully
bring it more in line with Rack's core functionality. You can learn more about Rack on
[its homepage](http://rack.rubyforge.org/ "Rack Homepage").

The Rack webserver interface
specification, upon which Rack and Prack are built, is
[here](http://rack.rubyforge.org/doc/SPEC.html "Rack Specification").

Overview
========

Anyone familiar with Ruby Rack will see similarities in this explanation.

Basically, all code used by Prack implements the interface <tt>Prack\_I_MiddlewareApp</tt>,
which defines one function:

	public function call( $env );

This function takes one argument <tt>$env</tt>, which is passed by reference, and contains
all data extracted from the request. Literally everything--cookies, request headers, server
environment--goes into <tt>$env</tt>. You _can_ still use superglobals, but not doing so
is highly recommended, as Prack's middleware ignores them.

Middleware apps must always, without exception, return an array of three elements:

1. status code (an integer)
2. response headers (an associative array<sup>*array</sup>)
3. the response body (one of: string, array of strings, or Prb\_I\_Enumerable<sup>*prb</sup>)

Because we have a standard interface with one function <tt>call</tt> which returns an array,
we are able to chain middleware applications together:

	         -> ┌──────────────────┐ -> ┌────────────┐
	  server    │ prack_auth_basic │    │ controller │
	         <- └──────────────────┘ <- └────────────┘

Middleware apps typically call the next app in the chain thusly:

	list( $status, $headers, $body ) = $this->middleware_app->call( $env );

within their own call method. The <tt>$middleware_app</tt> property is set upon object
construction. An astute reader might ask "OK, great, so how does this stack get built?"
For this task, we have a custom middleware app called <tt>Builder</tt>, which is used
to construct a domain. A properly configured virtual host serving Prack code routes all
incoming requests through <tt>rackup.php</tt>, which would have code like this for the
above-depicted stack:

	// Hypothetical rackup.php:
	
	include "lib/php-rack/autoload.php";
	include "lib/php-ruby/autoload.php";
	include "lib/myproject/autoload.php";
	
	function onAuthBasicAuthenticate( $username, $password )
	{
		return ( $password == 'secret' );
	}
	
	// Build our domain at root-level (mapped to '/' in our website)
	$domain = Prack_Builder::domain()
	  ->using( 'Prack_Auth_Basic' )->withArgs( 'Admin Area' )->andCallback( 'onAuthBasicAuthenticate' )->push()
	  ->run( new Controller() );
	
	// Our builder implements Prack_I_MiddlewareApp.
	list( $status, $headers, $body ) = $domain->call( $env );
	
	// Handling the response:
	$modphp_compat = Prack_ModPHP_Compat::singleton();
	$env           = $modphp_compat->extractEnv( $_SERVER );
	
	$modphp_compat->render( $domain->call( $env ) );

Prack has tons of useful middleware. For a complete list, see "Prack Implementation."

Writing your own Prack-compatible Middleware Apps
=================================================

Continuing with the above example, we're gonna need to write the 'controller' middleware.

Obviously, for 'Controller', we're gonna have to write the class:

	class Controller
	  implements Prack_I_MiddlewareApp
	{
		private $message;
		
		function __construct( $message = 'Hello there!' )
		{
			$this->message = $message;
		}
		
		public function call( &$env )
		{
			return array( 200, array( 'Content-Type' => 'text/html' ), array( $this->message ) );
		}
	}

<tt>Controller</tt> is invoked at the end of our middleware stack. If it had another middleware
app to call, it would be constructed thusly:

	class Controller
	  implements Prack_I_MiddlewareApp
	{
		private $middleware_app;
		private $message;
		
		function __construct( $middleware_app, $message = 'Hello there!' )
		{
			$this->middleware_app = $middleware_app;
			$this->message        = $message;
		}
		
		public function call( &$env )
		{
			// Just forward request without modifications:
			list( $status, $headers, $body ) = $this->middleware_app( $env );
			
			// Prack_Response wraps a response and exposes a lot of utility:
			$response = Prack_Response::with( $body, $status, $headers );
			if ( $response->get( 'Content-Type' ) == 'text/html' )
			{
				if ( $response->isOK() )
				{
					$modified_response_body = preg_replace( '/<body>/', '<body>'.$this->message, $response->getBody(), 1 );
					$body                   = array( $modified_response_body );
				}
				else if ( $response->isForbidden() )
					$body = array( 'Fine, then.' ); // Nuke the callee's response for snark!
				else
					$body = array( $response->getBody() );
				
				// Prack_Response gets and sets headers case-insensitively:
				$response->set( 'content-length', (string)strlen( $body[ 0 ] ) );
			}
			
			return $response->finish(); // converts response object to expected array.
		}
	}

And our rackup.php would have this modification:

	// Build our domain at root-level (mapped to '/' in our website)
	$domain = Prack_Builder::domain()
	  ->using( 'Prack_Auth_Basic' )->withArgs( 'Admin Area' )->andCallback( 'onAuthBasicAuthenticate' )->push()
	  ->using( 'Controller'       )->withArgs( 'This is my message!' )->push()
	  ->run( new WhatControllerCalls() );

With the resulting middleware stack:

	         -> ┌──────────────────┐ -> ┌────────────┐ -> ┌─────────────────────┐
	  server    │ prack_auth_basic │    │ controller │    │ whatcontrollercalls │
	         <- └──────────────────┘ <- └────────────┘ <- └─────────────────────┘

If it seems weird that we're wrapping the response body in an <tt>array</tt>, it's not:
we need an array for future compatibility plans. (Objects implementing ArrayAccess will let
us duck-type responses by treating them as arrays.) Prack\_Response handles <tt>string</tt>
as the third item in a response <tt>array</tt>, but this functionality is going to be deprecated.

<sup>*array</sup> Response header arrays are key-value pairs of header => value. When a header has multiple
values, the value is endline-separated, and in the response they are sent as multiple same-named
headers mapped one to one with the values.

<sup>*prb</sup> php-ruby ("Prb") is a library prack uses internally to simplify its operations. It will
likely be deprecated at some point in the future. Prb is used mostly for its IO, Logger,
and Time classes.

Differences from Rack
=====================

In Ruby's Rack, an 'app' is any callable object. It can be an object instance or anonymous
function ("lambda")--either way, Ruby's language semantics allow any object with a <tt>call</tt>
method to be called directly, similar to PHP 5.3's <tt>\_\_invoke</tt>.

In Prack, 'middleware apps' are PHP object instances which implement the interface
<tt>Prack\_I\_MiddlewareApp</tt>. This means that prack middleware apps are not at all 'callable'
at an object-level. They are not closures and won't be until Prack is refactored for 5.3+.

Prack is currently a port of Rack's core middleware suite,
fully tested, with a tiny compatibility layer for PHP code running in <tt>mod\_php</tt>.
It does not offer handlers for starting servers, since most PHP code already runs in the context
of a hosting fileserver like Apache, Nginx, or Lighttpd.

Eventually, I hope to implement a daemonized version of Prack a la
[Appserver-in-PHP](https://github.com/indeyets/appserver-in-php), which will function more like
Ruby. This will have to be accomplished via forking, and will almost certainly require language
features present in 5.3+.

Requirements and Dependencies
=============================

Prack is currently implemented in a way that allows it to run on PHP5.2+. It will eventually be
restructured for 5.3+, which will cause some notable changes in the codebase.

Prack is built on top of a "Rubification" library called
[php-ruby](http://github.com/prack/php-rb "Prb Homepage") ("Prb" for short). After a recent
rework, Prack's dependence on Prb has been _dramatically_ reduced. Prack still makes heavy
use of the following Prb functionality internally:

* Time
* Logger
* IO

But not so much of anything else. This means that you don't need to know anything about Prb,
except maybe its logger. Documentation forthcoming for <tt>Prb_Logger</tt>. Feel free to look
at its Prb implementation.

_Plans are to ditch this library for the 5.3+ rewrite. It is not my goal to make PHP behave like
Ruby. However, 5.2's SPL is not suitable for Prack._

Prack Implementation
====================

The following core Rack components have been, are being, or will not be ported to Prack.

Fully-tested and Production Ready
---------------------------------

* <tt>Arachnid</tt>: cute ascii art a la <tt>Rack::Lobster</tt> _(use with showexceptions!)_
* <tt>Auth_Basic</tt>: basic (login/password) HTTP authentication
* <tt>Auth_Digest</tt>: digest (md5-based) HTTP authentication
* <tt>Builder</tt>: fluent interface for building middleware app stacks in a domain
* <tt>Cascade</tt>: chains a request to a list of middleware apps, accepting the first viable response
* <tt>CommonLogger</tt>: forward request to an app and log the request as Apache common log format
* <tt>ConditionalGet</tt>: uses Etags and HTTP headers to respond without body if appropriate
* <tt>Config</tt>: allows environment configuration before running middleware apps
* <tt>ContentLength</tt>: ensures a response has a proper Content-Length header
* <tt>ContentType</tt>: ensures a response has a proper Content-Type header
* <tt>Deflater<sup>*deflater</sup></tt>: gzip- and deflate-encoded responses via middleware
* <tt>ETag</tt>: checksums a page's contents
* <tt>File</tt>: serve static files via Prack
* <tt>Head</tt>: removes body from response for requests using HEAD method
* <tt>Logger</tt>: adds a logger to the environment for downstream apps to use
* <tt>MethodOverride</tt>: allows HTTP request method override via hidden form input or HTTP header
* <tt>Mime</tt>: a default and configurable set of MIME types
* <tt>Mock_Request</tt>: fake requests for testing
* <tt>Mock_Response</tt>: fake responses for testing, delegates some methods
* <tt>NullLogger</tt>: no-op logger. eats log messages.
* <tt>RewindableInput</tt>: adds rewindability to any stream
* <tt>Runtime</tt>: records the runtime of a partial or full stack of middleware apps
* <tt>Sendfile</tt>: mod_sendfile support
* <tt>ShowExceptions</tt>: catches uncaught exceptions/errors and show them as pretty HTML with context
* <tt>Static</tt>: static asset server
* <tt>URLMap</tt>: used by Builder to map middleware (stacks) to a URL endpoints
* <tt>Utils_HeaderHash</tt>: case-insensitive, multiple-value supporting assoc array wrapper
* Interfaces: <tt>MiddlewareApp</tt>

<sup>*deflater</sup> PHP's <tt>ob\_gzhandler</tt> works just fine, but it relies on global state
in <tt>$\_SERVER</tt>, namely the <tt>HTTP\_ACCEPT_ENCODING</tt> header. This is why global state
is bad--it removes the possibility of doing things in a self-contained way.

Works perfectly, but Lacks a Feature or Two
-------------------------------------------

* <tt>Directory</tt>: directory listing of a filepath (pending: symbolic links)
* <tt>Lint</tt>: Ensures response sanity. (pending</tt>: sessions)
* <tt>Request</tt>: Actual request (pending</tt>: multipart form data)
* <tt>Response</tt>: Actual response, delegates some methods (pending</tt>: cookie management)
* <tt>Utils</tt>: This is gonna have a lot of stuff in it, some of which comes natively from PHP (pending</tt>: multipart, cookies)

Works, but isn't properly/entirely tested
-----------------------------------------

* <tt>ModPHP_Compat</tt>: jiggers <tt>$\_SERVER</tt> into an acceptable request environment for Prack; renders response arrays

Currently Unimplemented
-----------------------

* Sessions
* Cookies
* Multipart-form-data processing
* <tt>Handler</tt>: This application server code isn't yet included in Prack.
* <tt>Recursive</tt>: Relies too much on lambdas to be viable for Prack in 5.2.

Will Not Implement
------------------

_These middleware apps do not make sense in a PHP context._

* <tt>Lock</tt>: PHP doesn't have multithreading.
* <tt>Reloader</tt>: PHP doesn't let developers undefine classes. Reloading will be accomplished another way.

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

If all this confuses you, grok the [Rack spec](http://rack.rubyforge.org/doc/SPEC.html "Rack Interface Specification").
Prack works exactly the same way, even in the environment variable names it uses.


Things I'm would love guidance on/help with
===========================================

* String encoding in Ruby is different from PHP. I'm not sure if this is an issue.
* Writing an application server wrapper for PHP and Prack.

Acknowledgments
===============

Thanks to the Ruby Rack team for all their hard work on Rack.