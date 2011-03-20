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

* <tt>Auth_Basic</tt>: Basic HTTP authentication
* <tt>Auth_Digest</tt>: HTTP digest authentication
* <tt>Builder</tt>: Fluent interface for building middleware app stacks in a domain
* <tt>Cascade</tt>: Chains a request to a list of middleware apps, accepting the first viable response
* <tt>ConditionalGet</tt>: uses Etags and HTTP headers to respond without body if appropriate
* <tt>ContentLength</tt>: ensures a response has a proper Content-Length header
* <tt>ContentType</tt>: ensures a response has a proper Content-Type header
* <tt>ETag</tt>: checksums a page's contents
* <tt>File</tt>: serve static files via Prack
* <tt>Logger</tt>: Adds a logger to the environment for downstream apps to use
* <tt>MethodOverride</tt>: allows HTTP request method override via hidden form input or HTTP header
* <tt>Mime</tt>: a default and configurable set of MIME types
* <tt>Mock_Request</tt>: Fake requests for testing
* <tt>Mock_Response</tt>: Fake responses for testing, delegates some methods
* <tt>RewindableInput</tt>: Adds rewindability to any stream
* <tt>Runtime</tt>: records the runtime of a partial or full stack of middleware apps
* <tt>ShowExceptions</tt>: Catches uncaught exceptions and show them as pretty HTML with context
* <tt>URLMap</tt>: Used by Builder to map middleware (stacks) to a URL endpoints
* <tt>Utils_HeaderHash</tt>: A case-insensitive, multiple-value supporting assoc array wrapper
* <tt>Interfaces</tt>: MiddlewareApp

Working perfectly, but not feature-complete
-------------------------------------------

* <tt>Lint</tt>: Ensures response sanity. (pending</tt>: sessions)
* <tt>Request</tt>: Actual request (pending</tt>: multipart form data)
* <tt>Response</tt>: Actual response, delegates some methods (pending</tt>: cookie management)
* <tt>Utils</tt>: This is gonna have a lot of stuff in it, some of which comes natively from PHP (pending</tt>: multipart, cookies, encoding selection)

Works, but isn't properly/entirely tested
-----------------------------------------

* <tt>ModPHP_Compat</tt>: jiggers <tt>$\_SERVER</tt> into an acceptable request environment for Prack

To Do
-----

* Consider an MD5 implementation that doesn't require a whole string to be in-memory
* Documentation on when Prack uses PHP primitives vs. Prb wrappers
* Rack config
* Sessions
* Cookies
* Multipart-form-data processing
* Prack Attack (Rack Lobster analog)
* Everything else in Ruby Rack :)

Running Tests
=============

This project is designed using test-driven development. I've made the test
method names as descriptive and consistent as possible, so please check them
out as documentation until the project matures a bit more.

To run tests:
<pre>
	git clone https://github.com/prack/php-rack.git
	cd Prack
	phpunit
</pre>

Of course, you must have PHPUnit installed, preferably alongside XDebug. I'm using
PHPUnit 3.5.


Getting started
===============

All Prack applications must conform to the <tt>Prack\_Interface_MiddlewareApp</tt> interface,
which is stupidly easy to implement:

	interface Prack_Interface_MiddlewareApp
	{
		public function call( $env ); // $env is a Prb_Hash
	}

I put this interface in place for 5.2-compatibility, but when 5.3 is implemented,
I will revisit whether to drop it so we can also include lambdas as first-class middleware apps.

<tt>call</tt> MUST return a <tt>Prb_Array</tt> as its response with the following items, in this order</tt>:

<pre>
1. Prb_Numeric                                          (status)
2. Prb_Hash                                             (headers)
3. Prb_Interface_Enumerable OR Prb_Interface_Stringable (body)
</pre>


See Prack's [sandbox](http://github.com/prack/sandbox) for a working demo app!

If all this confuses you, grok the [Rack spec](http://rack.rubyforge.org/doc/SPEC.html "Rack Interface Specification").
Prack works exactly the same way, even in the environment variable names it uses.


Things I'm would love guidance on/help with
===========================================

* The <tt>Prack_Lint</tt> implementation and tests is very... pragmatic. The outcome is the same as Rack's,
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
