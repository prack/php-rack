Prack
=====

Prack is Ruby's Rack ported to PHP 5.2+, designed against Ruby Rack's own test suite.

See Prack's [livesite](http://github.com/prack/livesite) for a demo! _Prack is very
well tested and probably suited for your next project!_

You can learn more about Rack on [its homepage](http://rack.rubyforge.org/ "Rack Homepage").
The webserver interface specification upon which Rack and Prack are built is 
[here](http://rack.rubyforge.org/doc/SPEC.html "Rack Specification").

Dependencies
============

Prack is built on top of a "Rubification" library called
[php-ruby](http://github.com/prack/php-rb "Prb Homepage") ("Prb" for short). After a recent
rework, Prack's dependence on Prb has been _dramatically_ reduced. Prack still makes heavy
use of the following Prb functionality internally:

* Time
* Logger
* IO

But not so much of the other stuff anything else. This means that you don't need to know
anything about Prb, except maybe its logger.

Check out Prack's [livesite](http://github.com/prack/livesite) for info on the logger.


Progress
========

Fully-tested and Production Ready
---------------------------------

* <tt>Auth_Basic</tt>: basic (login/password) HTTP authentication
* <tt>Auth_Digest</tt>: digest (md5-based) HTTP authentication
* <tt>Builder</tt>: fluent interface for building middleware app stacks in a domain
* <tt>Cascade</tt>: chains a request to a list of middleware apps, accepting the first viable response
* <tt>CommonLogger</tt>: forward request to an app and log the request as Apache common log format
* <tt>ConditionalGet</tt>: uses Etags and HTTP headers to respond without body if appropriate
* <tt>Config</tt>: allows environment configuration before running middleware apps
* <tt>ContentLength</tt>: ensures a response has a proper Content-Length header
* <tt>ContentType</tt>: ensures a response has a proper Content-Type header
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
* <tt>ShowExceptions</tt>: catches uncaught exceptions and show them as pretty HTML with context
* <tt>Static</tt>: static asset server
* <tt>URLMap</tt>: used by Builder to map middleware (stacks) to a URL endpoints
* <tt>Utils_HeaderHash</tt>: case-insensitive, multiple-value supporting assoc array wrapper
* <tt>Interfaces</tt>: MiddlewareApp

Works perfectly, but Lacks a Feature or Two
-------------------------------------------

* <tt>Directory</tt>: directory listing of a filepath (pending: symbolic links)
* <tt>Lint</tt>: Ensures response sanity. (pending</tt>: sessions)
* <tt>Request</tt>: Actual request (pending</tt>: multipart form data)
* <tt>Response</tt>: Actual response, delegates some methods (pending</tt>: cookie management)
* <tt>Utils</tt>: This is gonna have a lot of stuff in it, some of which comes natively from PHP (pending</tt>: multipart, cookies, encoding selection)

Works, but isn't properly/entirely tested
-----------------------------------------

* <tt>ModPHP_Compat</tt>: jiggers <tt>$\_SERVER</tt> into an acceptable request environment for Prack; renders response arrays

To Do
-----

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

All Prack applications must conform to the <tt>Prack\_I_MiddlewareApp</tt> interface,
which is stupidly easy to implement:

	interface Prack_I_MiddlewareApp
	{
		public function call( &$env ); // $env is an array
	}

I put this interface in place for 5.2-compatibility, but when Prack is reworked for 5.3,
this interface may be dropped for <tt>__invoke</tt> and straightup lambdas (like Rack).

<tt>call</tt> MUST return an <tt>array</tt> as its response with the following items, in this order:

<pre>
1. status  - integer
2. headers - array
3. body    - string, array of strings, or a Prb_I_Enumerable
</pre>


See Prack's [livesite](http://github.com/prack/livesite) for a working demo app!

If all this confuses you, grok the [Rack spec](http://rack.rubyforge.org/doc/SPEC.html "Rack Interface Specification").
Prack works exactly the same way, even in the environment variable names it uses.


Things I'm would love guidance on/help with
===========================================

* String encoding in Ruby is very different from PHP. I'm not sure about all the ramifications
of this.
* PHP runs in the context of the Apache web server. Ruby is much more general-purpose than PHP,
so Prack doesn't yet have a way to start up a server. This will probably involve a custom SAPI,
which I totally don't want to write. In the context of Apache PHP, we can get there 80% of the
way with a compatibility layer: currently, ModPHP_Compat.


Acknowledgments
===============

Thanks to the Ruby Rack team for all their hard work on Rack.