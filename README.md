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
* Auth_Digest: HTTP digest authentication
* Builder: Fluent interface for building middleware app stacks in a domain
* Cascade: Chains a request to a list of middleware apps, accepting the first viable response
* ConditionalGet: uses Etags and HTTP headers to respond without body if appropriate
* ContentLength: ensures a response has a proper Content-Length header
* ContentType: ensures a response has a proper Content-Type header
* ETag: checksums a page's contents
* File: serve static files via Prack
* Logger: Adds a logger to the environment for downstream apps to use
* MethodOverride: allows HTTP request method override via hidden form input or HTTP header
* Mime: a default and configurable set of MIME types
* Mock_Request: Fake requests for testing
* Mock_Response: Fake responses for testing, delegates some methods
* RewindableInput: Adds rewindability to any stream
* Runtime: records the runtime of a partial or full stack of middleware apps
* ShowExceptions: Catches uncaught exceptions and show them as pretty HTML with context
* URLMap: Used by Builder to map middleware (stacks) to a URL endpoints
* Utils_HeaderHash: A case-insensitive, multiple-value supporting assoc array wrapper
* Interfaces: MiddlewareApp

Working perfectly, but not feature-complete
-------------------------------------------

* Lint: Ensures response sanity. (pending: sessions)
* Request: Actual request (pending: multipart form data)
* Response: Actual response, delegates some methods (pending: cookie management)
* Utils: This is gonna have a lot of stuff in it, some of which comes natively from PHP (pending: multipart, cookies, encoding selection)

Works, but isn't properly/entirely tested
-----------------------------------------

* ModPHP_Compat: jiggers $\_SERVER into an acceptable request environment for Prack

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

See Prack's [sandbox](http://github.com/prack/sandbox) for a working demo app!

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
