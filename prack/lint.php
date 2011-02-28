<?php

// TODO: Document!
function prack_lint_assert( $expr, $message )
{
	if ( assert( $expr ) == false )
		throw new Prack_Error_Lint( $message );
}

// TODO: Document!
class Prack_Lint_InputWrapper
  implements Prack_Interface_ReadableStreamlike
{
	private $input;
	private $on_each;
	
	// TODO: Document!
	function __construct( $input )
	{
		$this->input = $input;
	}
	
	// TODO: Document!
	## * +gets+ must be called without arguments and return a string,
	##   or +null+ on EOF.
	public function gets()
	{
		$args = func_get_args();
		prack_lint_assert( count( $args ) == 0,
		                   'rack.input method gets called with arguments' );
		
		$result = $this->input->gets();
		prack_lint_assert( is_null( $result ) || $result instanceof Prack_Wrapper_String,
		                   "rack.input method gets didn't return a Prack_Wrapper_String" );
		return $result;
	}
	
	// TODO: Document!
	## * +read+ behaves like IO#read. Its signature is <tt>read([length, [buffer]])</tt>.
	##   If given, +length+ must be an non-negative Integer (>= 0) or +null+, and +buffer+ must
	##   be a String and may not be null. If +length+ is given and not null, then this method
	##   reads at most +length+ bytes from the input stream. If +length+ is not given or null,
	##   then this method reads all data until EOF.
	##   When EOF is reached, this method returns null if +length+ is given and not null, or ""
	##   if +length+ is not given or is null.
	##   If +buffer+ is given, then the read data will be placed into +buffer+ instead of a
	##   newly created String object.
	public function read( $length = null, $buffer = null )
	{
		$args = func_get_args();
		
		prack_lint_assert( count( $args ) <= 2, "rack.input method read called with too many arguments" );
		
		if ( count( $args ) >= 1 )
		{
			prack_lint_assert( is_integer( reset( $args ) ) || is_null( reset( $args ) ),
			                   "rack.input method read called with non-integer and non-null length" );
			prack_lint_assert( is_null( reset( $args ) ) || reset( $args ) >= 0,
			                   "rack.input method read called with a negative length" );
		}
		if ( count( $args ) >= 2 )
			prack_lint_assert( $args[ 1 ] instanceof Prack_Wrapper_String,
		                     "rack.input method read called with non-string buffer" );
		
		$result = call_user_func_array( array( $this->input, 'read' ), $args );
		
		prack_lint_assert( is_null( $result ) || $result instanceof Prack_Wrapper_String,
		                   "rack.input method read didn't return null or a Prack_Wrapper_String" );
		if ( array_key_exists( 0, $args ) && is_null( $args[ 0 ] ) )
			prack_lint_assert( isset( $result ), "rack.input method read( null ) returned null on EOF" );
		
		return $result;
	}
	
	// TODO: Document!
	## * +each+ must be called with exactly one callback argument and only yield Strings.
	public function each( $callback )
	{
		$this->on_each = $callback;
		$this->input->each( array( $this, 'onEach' ) );
	}
	
	// TODO: Document!
	public function onEach( $line )
	{
		prack_lint_assert( $line instanceof Prack_Wrapper_String, "rack.input method each didn't yield a Prack_Wrapper_String" );
		call_user_func( $this->on_each, $line );
	}
	
	// TODO: Document!
	## * +rewind+ must be called without arguments. It rewinds the input
	##   stream back to the beginning. It must not raise Prack_Error_System_ErrnoESPIPE:
	##   that is, it may not be a pipe or a socket. Therefore, handler
	##   developers must buffer the input data into some rewindable object
	##   if the underlying input stream is not rewindable.
	public function rewind()
	{
		$args = func_get_args();
		
		prack_lint_assert( count( $args ) == 0,
		                   "rack.input method rewind called with arguments" );
		
		$is_rewindable = false;
		try
		{
			$this->input->rewind();
			$is_rewindable = true;
		}
		catch ( Prack_Error_System_ErrnoESPIPE $e ) {}
		
		prack_lint_assert( $is_rewindable, "rack.input method rewind threw Prack_System_Error_ESPIPE" );
	}
	
	// TODO: Document!
	public function close()
	{
		prack_lint_assert( false, "rack.input method close must not be called" );
	}
	
}

// TODO: Document!
class Prack_Lint_ErrorWrapper
  implements Prack_Interface_WritableStreamlike
{
	private $error;
	
	// TODO: Document!
	function __construct( $error )
	{
		$this->error = $error;
	}
	
	// TODO: Document!
  ## * +puts+ must be called with a single argument that responds to +to_s+.
	public function puts()
	{
		$args = func_get_args();
		call_user_func_array( array( $this->error, 'puts' ), $args );
	}
	
	// TODO: Document!
	## * +write+ must be called with a single argument that is a String.
	public function write( $string )
	{
		prack_lint_assert( $string instanceof Prack_Wrapper_String,
		                   "rack.errors method write not called with a Prack_Wrapper_String" );
		$this->error->write( $string );
	}
	
	// TODO: Document!
  ## * +flush+ must be called without arguments and must be called
  ##   in order to make the error appear for sure.
	public function flush()
	{
		$this->error->flush();
	}
	
	// TODO: Document!
	public function close()
	{
		$args = func_get_args();
		prack_lint_assert( false, "rack.errors method close must not be called" );
	}
}

class Prack_Lint
  implements Prack_Interface_MiddlewareApp, Prack_Interface_Enumerable
{
	private $assert_option_active;
	private $assert_option_warning;
	private $assert_option_bail;
	private $assert_option_quiet_eval;
	private $assert_option_callback;
	
	private $middleware_app;
	private $content_length;
	private $body;
	private $callback;
	private $closed;
	private $bytes;
	
	// TODO: Document!
	static function with( $middleware_app )
	{
		return new Prack_Lint( $middleware_app );
	}
	
	// TODO: Document!
	function __construct( $middleware_app )
	{
		$this->middleware_app = $middleware_app;
		$this->content_length = null;
	}
	
	// TODO: Document!
	public function call( $env )
	{
		$clone = clone( $this );
		return $clone->_call( $env );
	}
	
	// TODO: Document!
	public function _call( $env )
	{
		$this->pushAssertOptions();
		
		## It takes exactly one argument, the *environment*
		prack_lint_assert( isset( $env ), 'No env given' );
		$this->checkEnv( $env );
		
		$env->set( 'rack.input',  new Prack_Lint_InputWrapper( $env->get( 'rack.input'  ) ) );
		$env->set( 'rack.errors', new Prack_Lint_ErrorWrapper( $env->get( 'rack.errors' ) ) );
		
		## and returns an Array of exactly three values:
		list( $status, $headers, $this->body ) = $this->middleware_app->call( $env );
		if ( is_array( $this->body ) )
			$this->body = Prack::_Array( $this->body ); // Make body OO-enumerable
		
		## The *status*,
		$this->checkStatus( $status );
		
		## the *headers*,
		$this->checkHeaders( $headers );
		
		## and the *body*.
		$this->checkContentType( $status, $headers );
		$this->checkContentLength( $status, $headers );
		$this->head_request = ( $env->get( 'REQUEST_METHOD' )->toN() == 'HEAD' );
		
		$this->popAssertOptions();
		
		return array( $status, $headers, $this );
	}
	
	// TODO: Document!
	public function verifyContentLength( $bytes )
	{
		if ( $this->head_request )
			prack_lint_assert( $bytes == 0,
		                     'Response body was given for HEAD request, but should be empty' );
		else if ( $this->content_length )
			prack_lint_assert( $this->content_length->toN() == $bytes,
		                     "Content-Length header was {$this->content_length->toN()}, but should be {$bytes}" );
	}
	
	// TODO: Document!
	## === The Body
	public function each( $callback )
	{
		$this->callback = $callback;
		$this->closed   = false;
		$this->bytes    = 0;
		
		## The Body must respond to +each+
		prack_lint_assert( $this->body instanceof Prack_Interface_Enumerable,
		                   'Response body must respond to each' );
		
		$this->body->each( array( $this, 'tabulate' ) );
		
		$this->verifyContentLength( $this->bytes );
		
		## If the Body responds to +close+, it will be called after iteration.
		##
		## If the Body responds to +to_path+, it must return a String
		## identifying the location of a file whose contents are identical
		## to that produced by calling +each+; this may be used by the
		## server as an alternative, possibly more efficient way to
		## transport the response.
		
		if ( method_exists( $this->body, 'toPath' ) )
			prack_lint_assert( file_exists( $body->toPath() ),
			                   'The file identified by body.to_path does not exist' );

		##
		## The Body commonly is an Array of Strings, the application
		## instance itself, or a File-like object.
	}
	
	public function tabulate( $part )
	{
		## and must only yield String values.
		$as_string = print_r( $part, true );
		prack_lint_assert( $part instanceof Prack_Wrapper_String,
		                   "Body yielded non-string value {$as_string}" );
		
		$this->bytes += $part->length();
		
		call_user_func( $this->callback, $part );
	}

	// TODO: Document!
	public function checkEnv( $env )
	{ 
		## The environment must be an instance of Hash that includes
		## CGI-like headers.  The application is free to modify the
		## environment.
		$env_type = is_object( $env ) ? get_class( $env ) : gettype( $env );
		prack_lint_assert( $env instanceof Prack_Wrapper_Hash, "env is not a Prack_Wrapper_Hash, but {$env_type}" );
		
		##
		## The environment is required to include these variables
		## (adopted from PEP333), except when they'd be empty, but see
		## below.
		
		## <tt>REQUEST_METHOD</tt>:: The HTTP request method, such as
		##                           "GET" or "POST". This cannot ever
		##                           be an empty string, and so is
		##                           always required.
		
		## <tt>SCRIPT_NAME</tt>:: The initial portion of the request
		##                        URL's "path" that corresponds to the
		##                        application object, so that the
		##                        application knows its virtual
		##                        "location". This may be an empty
		##                        string, if the application corresponds
		##                        to the "root" of the server.
		
		## <tt>PATH_INFO</tt>:: The remainder of the request URL's
		##                      "path", designating the virtual
		##                      "location" of the request's target
		##                      within the application. This may be an
		##                      empty string, if the request URL targets
		##                      the application root and does not have a
		##                      trailing slash. This value may be
		##                      percent-encoded when I originating from
		##                      a URL.
		
		## <tt>QUERY_STRING</tt>:: The portion of the request URL that
		##                         follows the <tt>?</tt>, if any. May be
		##                         empty, but is always required!
		
		## <tt>SERVER_NAME</tt>, <tt>SERVER_PORT</tt>:: When combined with <tt>SCRIPT_NAME</tt> and <tt>PATH_INFO</tt>, these variables can be used to complete the URL. Note, however, that <tt>HTTP_HOST</tt>, if present, should be used in preference to <tt>SERVER_NAME</tt> for reconstructing the request URL.  <tt>SERVER_NAME</tt> and <tt>SERVER_PORT</tt> can never be empty strings, and so are always required.
		
		## <tt>HTTP_</tt> Variables:: Variables corresponding to the
		##                            client-supplied HTTP request
		##                            headers (i.e., variables whose
		##                            names begin with <tt>HTTP_</tt>). The
		##                            presence or absence of these
		##                            variables should correspond with
		##                            the presence or absence of the
		##                            appropriate HTTP header in the
		##                            request.
		
		## In addition to this, the Rack environment must include these
		## Rack-specific variables:
		
		## <tt>rack.version</tt>:: The Array [1,1], representing this version of Rack.
		## <tt>rack.url_scheme</tt>:: +http+ or +https+, depending on the request URL.
		## <tt>rack.input</tt>:: See below, the input stream.
		## <tt>rack.errors</tt>:: See below, the error stream.
		## <tt>rack.multithread</tt>:: true if the application object may be simultaneously invoked by another thread in the same process, false otherwise.
		## <tt>rack.multiprocess</tt>:: true if an equivalent application object may be simultaneously invoked by another process, false otherwise.
		## <tt>rack.run_once</tt>:: true if the server expects (but does not guarantee!) that the application will only be invoked this one time during the life of its containing process. Normally, this will only be true for a server based on CGI (or something similar).
		##
		
		## Additional environment specifications have approved to
		## standardized middleware APIs.  None of these are required to
		## be implemented by the server.
		
		## <tt>rack.session</tt>:: A hash like interface for storing request session data.
		##                         The store must implement:
		if ( $session = $env->get( 'rack.session' ) )
		{
			// FIXME: Implement sessions.
			/*
			if ( is_array( $session ) )
				$session = new Prack_Utils_Session( $session );
			
			$as_string = print_r( $session, true );
			prack_lint_assert( $session instanceof Prack_Interface_Session,
			                   "session {$as_string} must conform to Prack_Interface_Session" );
			*/
		}
		
		## <tt>rack.logger</tt>:: A common object interface for logging messages.
		##                        The object must implement:
		if ( $logger = $env->get( 'rack.logger' ) )
		{
			// FIXME: Implement logger.
			/*
			$as_string = print_r( $logger, true );
			prack_lint_assert( $logger instanceof Prack_Interface_Logger,
			                   "logger {$as_string} must conform to Prack_Interface_Logger" );
			*/
		}
		
		## The server or the application can store their own data in the
		## environment, too.  The keys must contain at least one dot,
		## and should be prefixed uniquely.  The prefix <tt>rack.</tt>
		## is reserved for use with the Rack core distribution and other
		## accepted specifications and must not be used otherwise.
		##
		$required_keys = array(
		  'REQUEST_METHOD', 'SERVER_NAME', 'SERVER_PORT', 'QUERY_STRING',
		  'rack.version', 'rack.input', 'rack.errors',
		  'rack.multithread', 'rack.multiprocess', 'rack.run_once'
		);
		
		foreach ( $required_keys as $header )
			prack_lint_assert( $env->contains( $header ), "env missing required key {$header}" );
		
		## The environment must not contain the keys
		## <tt>HTTP_CONTENT_TYPE</tt> or <tt>HTTP_CONTENT_LENGTH</tt>
		## (use the versions without <tt>HTTP_</tt>).
		foreach ( array( 'HTTP_CONTENT_TYPE', 'HTTP_CONTENT_LENGTH' ) as $header )
		{
			$useable = substr( $header, 5, strlen( $header ) - 1);
			prack_lint_assert( !$env->contains( $header ), "env contains {$header}, must use {$useable}" );
		}
		
		## The CGI keys (named without a period) must have String values.
		foreach ( $env->toN() as $key => $value )
		{
			if ( strpos( $key, '.' ) )
				continue;
			$as_string = is_object( $value ) ? get_class( $value ) : gettype( $value );
			prack_lint_assert( $value instanceof Prack_Wrapper_String, "env variable {$key} has non-string value {$as_string}" );
		}
		
		##
		## There are the following restrictions:
		
		## * <tt>rack.version</tt> must be an array of Integers.
		$rack_version = $env->get( 'rack.version' );
		$version_type = is_object( $rack_version ) ? get_class( $rack_version ) : gettype( $rack_version );
		prack_lint_assert( is_array( $rack_version ), "rack.version must be a Prack_Wrapper_Array, was {$version_type}" );
		
		## * <tt>rack.url_scheme</tt> must either be +http+ or +https+.
		
		$url_scheme      = $env->get( 'rack.url_scheme' );
		$url_scheme_type = is_object( $url_scheme ) ? get_class( $url_scheme ) : gettype( $url_scheme );
		prack_lint_assert( in_array( $env->get( 'rack.url_scheme' )->toN(), array( 'http', 'https' ) ),
		                   "rack.url_scheme unknown: {$url_scheme_type}"
		);
		
		## * There must be a valid input stream in <tt>rack.input</tt>.
		$this->checkInput( $env->get( 'rack.input' ) );
		## * There must be a valid error stream in <tt>rack.errors</tt>.
		$this->checkError( $env->get( 'rack.errors' ) );
		
		## * The <tt>REQUEST_METHOD</tt> must be a valid token.
		$rm_pattern = "/\A[0-9A-Za-z!\#$%&'*+.^_`|~-]+\z/";
		prack_lint_assert( $env->get( 'REQUEST_METHOD' )->matches( $rm_pattern ),
		                   "REQUEST_METHOD unknown: {$env->get( 'REQUEST_METHOD' )->toN()}" );
		
		## * The <tt>SCRIPT_NAME</tt>, if non-empty, must start with <tt>/</tt>
		$sn_pattern = '/\A\//';
		prack_lint_assert( !$env->contains( 'SCRIPT_NAME' ) || $env->get( 'SCRIPT_NAME' )->isEmpty() || $env->get( 'SCRIPT_NAME' )->matches( $sn_pattern ),
		                   'SCRIPT_NAME must start with /' );
		
		## * The <tt>PATH_INFO</tt>, if non-empty, must start with <tt>/</tt>
		$pi_pattern = '/\A\//';
		prack_lint_assert( !$env->contains( 'PATH_INFO' ) || $env->get( 'PATH_INFO' )->isEmpty() || $env->get( 'PATH_INFO' )->matches( $pi_pattern ),
		                   'PATH_INFO must start with /' );
		
		## * The <tt>CONTENT_LENGTH</tt>, if given, must consist of digits only.
		$cl_pattern = '/\A\d+\z/';
		prack_lint_assert( !$env->contains( 'CONTENT_LENGTH' ) || $env->get( 'CONTENT_LENGTH' )->matches( $cl_pattern ),
		                   "Invalid CONTENT_LENGTH: {$env->get( 'CONTENT_LENGTH' )->toN()}" );
		
		## * One of <tt>SCRIPT_NAME</tt> or <tt>PATH_INFO</tt> must be
		##   set.  <tt>PATH_INFO</tt> should be <tt>/</tt> if
		##   <tt>SCRIPT_NAME</tt> is empty.
		prack_lint_assert( $env->contains( 'SCRIPT_NAME' ) && !$env->get( 'SCRIPT_NAME' )->isEmpty() ||
		                   $env->contains( 'PATH_INFO'   ) && !$env->get( 'PATH_INFO'   )->isEmpty(),
		                   "One of SCRIPT_NAME or PATH_INFO must be set (make PATH_INFO '/' if SCRIPT_NAME is empty)" );
		
		##   <tt>SCRIPT_NAME</tt> never should be <tt>/</tt>, but instead be empty.
		prack_lint_assert( $env->contains( 'SCRIPT_NAME' ) && $env->get( 'SCRIPT_NAME' )->toN() != '/',
		                   "SCRIPT_NAME cannot be '/', make it '' and PATH_INFO '/'" );
	}
	
	// TODO: Document!
	## === The Input Stream
	##
	## The input stream is an IO-like object which contains the raw HTTP
	## POST data.
	public function checkInput( $input )
	{
		// TODO: Assess if these encoding concerns are relevant to PHP.
		/*
			## When applicable, its external encoding must be "ASCII-8BIT" and it
			## must be opened in binary mode, for Ruby 1.9 compatibility.
			assert("rack.input #{input} does not have ASCII-8BIT as its external encoding") {
			input.external_encoding.name == "ASCII-8BIT"
			} if input.respond_to?(:external_encoding)
			assert("rack.input #{input} is not opened in binary mode") {
			input.binmode?
			} if input.respond_to?(:binmode?)
		*/
		
		## The input stream must respond to +gets+, +each+, +read+ and +rewind+.
		$as_string = is_object( $input ) ? get_class( $input ) : gettype( $input );
		prack_lint_assert( ( $input instanceof Prack_Interface_ReadableStreamlike ),
		                   "rack.input {$as_string} is not a readable streamlike" );
	}
	
	// TODO: Document!
	## === The Error Stream
	public function checkError( $error )
	{
		## The error stream must respond to +puts+, +write+ and +flush+.
		$as_string = is_object( $error ) ? get_class( $error ) : gettype( $error );
		prack_lint_assert( $error instanceof Prack_Interface_WritableStreamlike,
		                   "rack.errors {$as_string} is not a writable streamlike" );
	}
	
	// TODO: Document!
	## == The Response
	public function checkStatus( $status)
	{
		## This is an HTTP status. When parsed as integer (+to_i+), it must be
		## greater than or equal to 100.
		prack_lint_assert( (int)$status >= 100, 'Status must be >= 100 seen as integer' );
	}
	
	// TODO: Document!
	## === The Headers
	public function checkHeaders( $header )
	{
		## The header must respond to +each+, and yield values of key and value.
		$header_type = is_object( $header ) ? get_class( $header ) : "primitive type ".gettype( $header );
		prack_lint_assert( $header instanceof Prack_Interface_Enumerable,
			                 "headers object should conform to Prack_Interface_Enumerable, but doesn't (got {$header_type} as headers)" );
		
		$header->each( array( $this, 'checkHeader' ) );
	}
	
	public function checkHeader( $key, $value )
	{
		## The header keys must be Strings.
		$key_type = is_object( $key ) ? get_class( $key ) : gettype( $key );
		prack_lint_assert( is_string( $key ),
		                   "header key must be a string, was {$key_type}" );
		
		## The header must not contain a +Status+ key,
		prack_lint_assert( strtolower( $key ) != 'status',
		                   "header must not contain Status" );
		## contain keys with <tt>:</tt> or newlines in their name,
		prack_lint_assert( preg_match( '/[:\n]/', $key ) === 0,
		                   "header names must not contain : or \\n" );
		
		## contain keys names that end in <tt>-</tt> or <tt>_</tt>,
		prack_lint_assert( preg_match( '/[-_]\z/', $key ) === 0,
		                               'header names must not end in - or _' );
		
		## but only contain keys that consist of
		## letters, digits, <tt>_</tt> or <tt>-</tt> and start with a letter.
		prack_lint_assert( preg_match( '/\A[a-zA-Z][a-zA-Z0-9_-]*\z/', $key ),
		                               "Invalid header name: {$key}" );
		
		## The values of the header must be Strings,
		$value_type = is_object( $value ) ? get_class( $value ) : gettype( $value );
		prack_lint_assert( $value instanceof Prack_Wrapper_String,
		                   "a header value must be a Prack_Wrapper_String, but the value of '{$key}' is a {$value_type}" );
		
		## consisting of lines (for multiple header values, e.g. multiple
		## <tt>Set-Cookie</tt> values) seperated by "\n".
		$exploded = $value->split( "/\n/" );
		foreach ( $exploded->toN() as $key => $value )
			prack_lint_assert( preg_match( '/[\000-\037]/', $value ) === 0,
		                     "invalid header value {$key}: {$value_type}" );
	}
	
	// TODO: Document!
	## === The Content-Type
	public function checkContentType( $status, $headers )
	{
		foreach ( $headers->toN() as $key => $value )
		{
			## There must be a <tt>Content-Type</tt>, except when the
			## +Status+ is 1xx, 204 or 304, in which case there must be none
			## given.
			if ( strtolower( $key ) == 'content-type' )
			{
				prack_lint_assert( !in_array( (int)$status, Prack_Utils::statusWithNoEntityBody() ),
				                   "Content-Type header found in {$status} response, not allowed" );
				return;
			}
		}
		
		prack_lint_assert( in_array( (int)$status, Prack_Utils::statusWithNoEntityBody() ),
		                   "No Content-Type header found" );
	}
	
	// TODO: Document!
	## === The Content-Length
	public function checkContentLength( $status, $headers )
	{
		foreach ( $headers->toN() as $key => $value )
		{
			## There must not be a <tt>Content-Length</tt> header when the
			## +Status+ is 1xx, 204 or 304.
			if ( strtolower( $key ) == 'content-length' )
			{
				prack_lint_assert( !in_array( (int)$status, Prack_Utils::statusWithNoEntityBody() ),
				                   "Content-Length header found in {$status} response, not allowed" );
				$this->content_length = $value;
			}
		}
	}
	
	// TODO: Document!
	private function pushAssertOptions()
	{
		$this->assert_option_active     = assert_options( ASSERT_ACTIVE,      1    );
		$this->assert_option_warning    = assert_options( ASSERT_WARNING,     0    );
		$this->assert_option_bail       = assert_options( ASSERT_BAIL,        0    );
		$this->assert_option_quiet_eval = assert_options( ASSERT_QUIET_EVAL,  0    );
		$this->assert_option_callback   = assert_options( ASSERT_CALLBACK,    null );
	}
	
	// TODO: Document!
	private function popAssertOptions()
	{
		assert_options( ASSERT_ACTIVE,      $this->assert_option_active     );
		assert_options( ASSERT_WARNING,     $this->assert_option_warning    );
		assert_options( ASSERT_BAIL,        $this->assert_option_bail       );
		assert_options( ASSERT_QUIET_EVAL,  $this->assert_option_quiet_eval );
		assert_options( ASSERT_CALLBACK,    $this->assert_option_callback   );
	}
}