<?php

// TODO: Document!
# Rack::Request provides a convenient interface to a Rack
# environment.  It is stateless, the environment +env+ passed to the
# constructor will be directly modified.
#
#   req = Rack::Request.new(env)
#   req.post?
#   req.params["data"]
#
# The environment hash passed will store a reference to the Request object
# instantiated so that it will only instantiate if an instance of the Request
# object doesn't already exist.
class Prack_Request 
{
	private $env;
	private $params;
	
	// TODO: Document!
	static function processAcceptEncodingCandidate( $candidate )
	{
		if ( preg_match_all( '/\A([^\s,]+?)(?:;\s*q=(\d+(?:\.\d+)?))?\z/', (string)$candidate, $matches ) )
		{
			$key         = $matches[ 1 ][ 0 ];
			$attr        = $matches[ 2 ][ 0 ];
			$attr_viable = ( isset( $attr ) && strlen( $attr ) > 0 );
			
			return Prack::_Array( array(
				Prack::_String( $key ), (float)( $attr_viable ? $attr : 1.0 )
			) );
		}
		
		throw new Prack_Error_Request_AcceptEncodingInvalid();
	}
	
	// TODO: Document!
	# The set of form-data media-types. Requests that do not indicate
	# one of the media types presents in this list will not be eligible
	# for form-data / param parsing.
	static function formDataMediaTypes()
	{
		static $form_data_media_types = null;
		
		if ( is_null( $form_data_media_types ) )
		{
			$form_data_media_types = Prack::_Array( array(
				Prack::_String( 'application/x-www-form-urlencoded' ),
				Prack::_String( 'multipart/form-data'               )
			) );
		}
		
		return $form_data_media_types;
	}
	
	// TODO: Document!
	# The set of media-types. Requests that do not indicate
	# one of the media types presents in this list will not be eligible
	# for param parsing like soap attachments or generic multiparts
	static function parseableDataMediaTypes()
	{
		static $parseable_data_media_types = null;
		
		if ( is_null( $parseable_data_media_types ) )
		{
			$parseable_data_media_types = Prack::_Array( array(
				Prack::_String( 'multipart/related' ),
				Prack::_String( 'multipart/mixed'   )
			) );
		}
		
		return $parseable_data_media_types;
	}
	
	// TODO: Document!
	static function with( $env )
	{
		return new Prack_Request( $env );
	}
	
	// TODO: Document!
	function __construct( $env )
	{
		$this->env = $env;
	}
	
	public function contentLength()  { return $this->env->get( 'CONTENT_LENGTH'       ); }
	public function contentType()    { return $this->env->get( 'CONTENT_TYPE'         ); }
	public function body()           { return $this->env->get( 'rack.input'           ); }
	public function scheme()         { return $this->env->get( 'rack.url_scheme'      ); }
	public function scriptName()     { return $this->env->get( 'SCRIPT_NAME'          ); }
	public function pathInfo()       { return $this->env->get( 'PATH_INFO'            ); }
	public function port()           { return $this->env->get( 'SERVER_PORT'          ); }
	public function requestMethod()  { return $this->env->get( 'REQUEST_METHOD'       ); }
	public function queryString()    { return $this->env->get( 'QUERY_STRING'         ); }
	public function logger()         { return $this->env->get( 'rack.logger'          ); }
	// public function session()        { return $this->env->get( 'rack.session'         ); }
	// public function sessionOptions() { return $this->env->get( 'rack.session.options' ); }
	
	// TODO: Document!
	# The media type (type/subtype) portion of the CONTENT_TYPE header
	# without any media type parameters. e.g., when CONTENT_TYPE is
	# "text/plain;charset=utf-8", the media-type is "text/plain".
	#
	# For more information on the use of media types in HTTP, see:
	# http://www.w3.org/Protocols/rfc2616/rfc2616-sec3.html#sec3.7
	public function mediaType()
	{
		$content_type = $this->contentType();
		if ( is_null( $content_type ) )
			return null;
		
		$components = preg_split( '/\s*[;,]\s*/', $content_type->toN(), 2 );
		return Prack::_String( strtolower( $components[ 0 ] ) );
	}
	
	// TODO: Document!
	# The media type parameters provided in CONTENT_TYPE as a Hash, or
	# an empty Hash if no CONTENT_TYPE or media-type parameters were
	# provided.  e.g., when the CONTENT_TYPE is "text/plain;charset=utf-8",
	# this method responds with the following Hash:
	#   { 'charset' => 'utf-8' }
	public function mediaTypeParams()
	{
		$content_type = $this->contentType();
		if ( is_null( $content_type ) )
			return Prack::_Array();
		
		$components = preg_split( '/\s*[;,]\s*/', $content_type->toN() );
		array_shift( $components );  // Ditch the MIME type.
		
		$function   = create_function( '$s', '$split = preg_split( \'/=/\', $s, 2 ); return array( $split[0], $split[1] );' );
		$components = array_map( $function, $components );
		
		$mediaTypeParams = Prack::_Hash();
		foreach ( $components as $component )
			$mediaTypeParams->set(
				strtolower( $component[ 0 ] ),
				Prack::_String( $component[ 1 ] )
			);
		
		return $mediaTypeParams;
	}
	
	// TODO: Document!
	# The character set of the request body if a "charset" media type
	# parameter was given, or nil if no "charset" was specified. Note
	# that, per RFC2616, text/* media types that specify no explicit
	# charset are to be considered ISO-8859-1.
	public function contentCharset()
	{
		return $this->mediaTypeParams()->get( 'charset' );
	}
	
	// TODO: Document!
	public function hostWithPort()
	{
		if ( $forwarded = $this->env->get( 'HTTP_X_FORWARDED_HOST' ) )
		{
			$forwarded = preg_split( '/,\s?/', $forwarded->toN(), 2 );
			return Prack::_String( end( $forwarded ) );
		}
		
		if ( $this->env->contains( 'HTTP_HOST' ) )
			return $this->env->get( 'HTTP_HOST' );
		
		if ( $this->env->contains( 'SERVER_NAME' ) ) 
			return $this->env->get( 'SERVER_NAME' );
		
		$host = $this->env->contains( 'SERVER_ADDR' ) ? $this->env->get( 'SERVER_ADDR' )->toN()
		                                              : '';
		$port = $this->env->contains( 'SERVER_PORT' ) ? $this->env->get( 'SERVER_PORT' )->toN()
		                                              : '';
		return Prack::_String( "{$host}:{$port}" );
	}
	
	// TODO: Document!
	public function host()
	{
		return Prack::_String( preg_replace( '/:\d+$/', '', $this->hostWithPort()->toN() ) );
	}
	
	// TODO: Document!
	public function setScriptName( $script_name )
	{
		$this->env->set( 'SCRIPT_NAME', $script_name );
	}
	
	// TODO: Document!
	public function setPathInfo( $path_info )
	{
		$this->env->set( 'PATH_INFO', $path_info );
	}
	
	// TODO: Document!
	public function isDelete()
	{
		return ( $this->requestMethod()->toN() == 'DELETE' );
	}
	
	// TODO: Document!
	public function isGet()
	{
		return ( $this->requestMethod()->toN() == 'GET' );
	}
	
	// TODO: Document!
	public function isHead()
	{
		return ( $this->requestMethod()->toN() == 'HEAD' );
	}
	
	// TODO: Document!
	public function isOptions()
	{
		return ( $this->requestMethod()->toN() == 'OPTIONS' );
	}
	
	// TODO: Document!
	public function isPost()
	{
		return ( $this->requestMethod()->toN() == 'POST' );
	}
	
	// TODO: Document!
	public function isPut()
	{
		return ( $this->requestMethod()->toN() == 'PUT' );
	}
	
	// TODO: Document!
	public function isTrace()
	{
		return ( $this->requestMethod()->toN() == 'TRACE' );
	}
	
	// TODO: Document!
	# Determine whether the request body contains form-data by checking
	# the request Content-Type for one of the media-types:
	# "application/x-www-form-urlencoded" or "multipart/form-data". The
	# list of form-data media types can be modified through the
	# +FORM_DATA_MEDIA_TYPES+ array.
	#
	# A request body is also assumed to contain form-data when no
	# Content-Type header is provided and the request_method is POST.
	public function isFormData()
	{
		$type            = $this->mediaType();
		$method_override = "rack.methodoverride.original_method";
		$request_method  = $this->env->contains( $method_override ) ? $this->env->get( $method_override )
		                                                            : $this->env->get( 'REQUEST_METHOD' );
		return ( $request_method->toN() == 'POST' && 
		         is_null( $type ) || self::formDataMediaTypes()->contains( $type ) );
	}
	
	// TODO: Document!
	public function isParseableData()
	{
		return self::parseableDataMediaTypes()->contains( $this->mediaType() );
	}
	
	// TODO: Document!
	# Returns the data recieved in the query string.
	public function GET()
	{
		if ( $this->env->get( 'rack.request.query_string' ) == $this->queryString() )
			return $this->env->get( 'rack.request.query_hash' );
		
		$this->env->set( 'rack.request.query_string', $this->queryString() );
		$this->env->set( 'rack.request.query_hash'  , $this->parseQuery( $this->queryString() ) );
		
		return $this->env->get( 'rack.request.query_hash' );
	}
	
	// TODO: Document!
	# Returns the data recieved in the request body.
	#
	# This method support both application/x-www-form-urlencoded and
	# multipart/form-data.
	public function POST()
	{
		if ( $this->env->get( 'rack.input' ) == null )
			throw new Prack_Error_Runtime_RackInputMissing( 'no rack.input when processing POST data' );
			
		else if ( $this->env->get( 'rack.request.form_input' ) == $this->env->get( 'rack.input' ) )
			return $this->env->get( 'rack.request.form_hash' );
		
		else if ( $this->isFormData() || $this->isParseableData() )
		{
			$this->env->set( 'rack.request.form_input', $this->env->get( 'rack.input' ) );
			
			// FIXME: Implement multipart processing
			// if ( ! ( $this->env->get( 'rack.request.form_hash' ) = $this->parseMultipart() ) )
			$multipart = false;
			if ( $multipart )
				die("FIXME: Implement multipart.");
			else
			{
				// FIXME: Implement preg_replace on Prack_Wrapper_String
				$form_vars = $this->env->get( 'rack.input' )->read();
				$form_vars = Prack::_String( preg_replace( '/\0\z/', '', $form_vars->toN() ) );
				
				$this->env->set( 'rack.request.form_vars', $form_vars );
				$this->env->set( 'rack.request.form_hash', $this->parseQuery( $form_vars ) );
				$this->env->get( 'rack.input' )->rewind();
			}
			return $this->env->get( 'rack.request.form_hash' );
		}
		
		return Prack::_Hash();
	}
	
	// TODO: Document!
	# The union of GET and POST data.
	public function params()
	{
		try
		{
			$get  = $this->GET();
			$post = $this->POST();
			return $get->update( $post );;
		}
		catch ( Prack_Error_EOF $e1 )
		{
			return $this->GET();
		}
	}
	
	// TODO: Document!
	public function getParam( $k )
	{
		return $this->params()->get( $k );
	}
	
	// TODO: Document!
	public function setParam( $k, $v )
	{
		$this->params()->set( (string)$k, $v );
	}
	
	// TODO: Document!
	public function valuesAt()
	{
		$keys = func_get_args();
		return call_user_func_array( array( $this->params(), 'valuesAt' ), $keys );
	}
	
	// TODO: Document!
	# the referer of the client or '/'
	public function referer()
	{
		return $this->env->contains( 'HTTP_REFERER' ) ? $this->env->get( 'HTTP_REFERER' ) : Prack::_String( '/' );
	}
	
	// TODO: Document!
	public function referrer()
	{
		return $this->referer();
	}
	
	// TODO: Document!
	public function userAgent()
	{
		return $this->env->contains( 'HTTP_USER_AGENT' ) ? $this->env->get( 'HTTP_USER_AGENT' ) : null;
	}
	
	// TODO: Document!
	public function cookies()
	{
		if ( !$this->env->contains( 'HTTP_COOKIE' ) )
			return Prack::_Array();

		if ( ( $cookie_string = $this->env->get( 'rack.request.cookie_string' ) ) && 
		     $cookie_string == $this->env->get( 'HTTP_COOKIE' ) )
			return $this->env->get( 'rack.request.cookie_hash' );
		else
		{
			$cookie_string = $this->env->get( 'HTTP_COOKIE' );
			$cookie        = http_parse_cookie( $cookie_string->toN() ); // FIXME: Implement cookie parsing.
			
			$this->env->set( 'rack.request.cookie_string', $cookie_string  );
			$this->env->set( 'rack.request.cookie_hash',   $cookie->cookies );
		}
		
		return $this->env->get( 'rack.request.cookie_hash' );
	}
	
	// TODO: Document!
	public function isXhr()
	{
		return ( ( $xhr = $this->env->get( 'HTTP_X_REQUESTED_WITH' ) ) && $xhr->toN() == 'XMLHttpRequest' );
	}
	
	// TODO: Document!
	# Tries to return a remake of the original request URL as a string.
	public function url()
	{
		$scheme = $this->scheme();
		$port   = (int)( $this->port()->toN() );
		
		$url  = $scheme->toN().'://';
		$url .= $this->host()->toN();
		
		if ( ( $scheme->toN() == 'https' && $port != 443 ) ||
		     ( $scheme->toN() == 'http'  && $port != 80  )    )
			$url .= ':'.$port;
			
		$url .= $this->fullpath()->toN();
		
		return Prack::_String( $url );
	}
	
	// TODO: Document!
	public function path()
	{
		return Prack::_String( $this->scriptName()->toN().$this->pathInfo()->toN() );
	}
	
	// TODO: Document!
	public function fullpath()
	{
		$query_string = $this->queryString()->toN();
		if ( empty( $query_string ) )
			return $this->path();
		
		return Prack::_String( $this->path()->toN().'?'.$this->queryString()->toN() );
	}
	
	// TODO: Document!
	public function acceptEncoding()
	{
		$accept_encoding = $this->env->contains( 'HTTP_ACCEPT_ENCODING' ) ? $this->env->get( 'HTTP_ACCEPT_ENCODING' )
		                                                                  : Prack::_String();
		
		// If there are no matches, preg_split returns an array with one empty string.
		// This is absolutely fucking stupid, but hey.
		$raw = preg_split( '/,\s*/', (string)$accept_encoding->toN() );
		if ( $raw == array( '' ) )
			return Prack::_Array();
			
		$candidates = Prack::_Array( $raw );
		$callback   = array( 'Prack_Request', 'processAcceptEncodingCandidate' );
		
		return $candidates->collect( $callback );
	}
	
	// TODO: Document!
	public function ip()
	{
		if ( $address = $this->env->get( 'HTTP_X_FORWARDED_FOR' ) )
		{
			$address = explode( ',', $address->toN() );
			$address = preg_grep( '/\d\./', $address ); // FIXME: Implement grep on array wrapper
			return isset( $address ) ? Prack::_String( reset( $address ) )
			                         : Prack::_String( trim( $this->env->get( 'REMOTE_ADDR' )->toN() ) ); // FIXME Implement chomp/trim on String
		}
		
		return $this->env->get( 'REMOTE_ADDR' );
	}
	
	// TODO: Document!
	public function &getEnv()
	{
		return $this->env;
	}
	
	// TODO: Document!
	public function parseQuery( $query_string )
	{
		return Prack_Utils::singleton()->parseNestedQuery( $query_string );
	}
}