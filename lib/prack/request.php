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
			
			return array( $key, (float)( $attr_viable ? $attr : 1.0 ) );
		}
		
		throw new Prack_Exception_Request_AcceptEncodingInvalid();
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
			$form_data_media_types =
			  array( 'application/x-www-form-urlencoded', 'multipart/form-data' );
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
			$parseable_data_media_types =
			  array( 'multipart/related', 'multipart/mixed' );
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
	
	public function contentLength()  { return @$this->env[ 'CONTENT_LENGTH'  ]; }
	public function contentType()    { return @$this->env[ 'CONTENT_TYPE'    ]; }
	public function body()           { return @$this->env[ 'rack.input'      ]; }
	public function scheme()         { return @$this->env[ 'rack.url_scheme' ]; }
	public function scriptName()     { return @$this->env[ 'SCRIPT_NAME'     ]; }
	public function pathInfo()       { return @$this->env[ 'PATH_INFO'       ]; }
	public function port()           { return @$this->env[ 'SERVER_PORT'     ]; }
	public function requestMethod()  { return @$this->env[ 'REQUEST_METHOD'  ]; }
	public function queryString()    { return @$this->env[ 'QUERY_STRING'    ]; }
	public function logger()         { return @$this->env[ 'rack.logger'     ]; }
	// public function session()        { return $this->env[ 'rack.session'         ]; }
	// public function sessionOptions() { return $this->env[ 'rack.session.options' ]; }
	
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
		
		$components = preg_split( '/\s*[;,]\s*/', $content_type, 2 );
		return strtolower( $components[ 0 ] );
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
			return array();
		
		$components = preg_split( '/\s*[;,]\s*/', $content_type );
		array_shift( $components );  // Ditch the MIME type.
		
		$function   = create_function( '$s', '$split = preg_split( \'/=/\', $s, 2 ); return array( $split[0], $split[1] );' );
		$components = array_map( $function, $components );
		
		$mediaTypeParams = array();
		foreach ( $components as $component )
			$mediaTypeParams[ strtolower( $component[ 0 ] ) ] = $component[ 1 ];
		
		return $mediaTypeParams;
	}
	
	// TODO: Document!
	# The character set of the request body if a "charset" media type
	# parameter was given, or nil if no "charset" was specified. Note
	# that, per RFC2616, text/* media types that specify no explicit
	# charset are to be considered ISO-8859-1.
	public function contentCharset()
	{
		$media_type_params = $this->mediaTypeParams();
		return @$media_type_params[ 'charset' ];
	}
	
	// TODO: Document!
	public function hostWithPort()
	{
		if ( $forwarded = @$this->env[ 'HTTP_X_FORWARDED_HOST' ] )
		{
			$forwarded = preg_split( '/,\s?/', $forwarded, 2 );
			return end( $forwarded );
		}
		
		if ( @$this->env[ 'HTTP_HOST' ] )
			return $this->env[ 'HTTP_HOST' ];
		
		if ( @$this->env[ 'SERVER_NAME' ] )
			return $this->env[ 'SERVER_NAME' ];
		
		$host = (string)@$this->env[ 'SERVER_ADDR' ];
		$port = (string)@$this->env[ 'SERVER_PORT' ];
		
		return "{$host}:{$port}";
	}
	
	// TODO: Document!
	public function host()
	{
		return preg_replace( '/:\d+$/', '', $this->hostWithPort() );
	}
	
	// TODO: Document!
	public function setScriptName( $script_name )
	{
		$this->env[ 'SCRIPT_NAME' ] = $script_name;
	}
	
	// TODO: Document!
	public function setPathInfo( $path_info )
	{
		$this->env[ 'PATH_INFO' ] = $path_info;
	}
	
	// TODO: Document!
	public function isDelete()
	{
		return ( $this->requestMethod() == 'DELETE' );
	}
	
	// TODO: Document!
	public function isGet()
	{
		return ( $this->requestMethod() == 'GET' );
	}
	
	// TODO: Document!
	public function isHead()
	{
		return ( $this->requestMethod() == 'HEAD' );
	}
	
	// TODO: Document!
	public function isOptions()
	{
		return ( $this->requestMethod() == 'OPTIONS' );
	}
	
	// TODO: Document!
	public function isPost()
	{
		return ( $this->requestMethod() == 'POST' );
	}
	
	// TODO: Document!
	public function isPut()
	{
		return ( $this->requestMethod() == 'PUT' );
	}
	
	// TODO: Document!
	public function isTrace()
	{
		return ( $this->requestMethod() == 'TRACE' );
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
		$request_method  = @$this->env[ 'rack.methodoverride.original_method' ]
		  ? $this->env[ 'rack.methodoverride.original_method' ]
		  : $this->env[ 'REQUEST_METHOD' ];
		
		return ( $request_method == 'POST' && is_null( $type ) ) || in_array( $type, self::formDataMediaTypes() );
	}
	
	// TODO: Document!
	public function isParseableData()
	{
		return in_array( $this->mediaType(), self::parseableDataMediaTypes() );
	}
	
	// TODO: Document!
	# Returns the data recieved in the query string.
	public function &GET()
	{
		if ( @$this->env[ 'rack.request.query_string' ] === $this->queryString() )
		{
			$return = &$this->env[ 'rack.request.query_hash' ];
			return $return;
		}
		
		$this->env[ 'rack.request.query_string' ] = $this->queryString();
		$this->env[ 'rack.request.query_hash'   ] = $this->parseQuery( $this->queryString() );
		
		$return = &$this->env[ 'rack.request.query_hash' ];
		return $return;
	}
	
	// TODO: Document!
	# Returns the data recieved in the request body.
	#
	# This method support both application/x-www-form-urlencoded and
	# multipart/form-data.
	public function &POST()
	{
		if ( !@$this->env[ 'rack.input' ] )
			throw new Prack_Exception_Runtime_RackInputMissing( 'no rack.input when processing POST data' );
			
		else if ( @$this->env[ 'rack.request.form_input' ] === $this->env[ 'rack.input' ] )
		{
			$return = &$this->env[ 'rack.request.form_hash' ];
			return $return;
		}
		
		else if ( $this->isFormData() || $this->isParseableData() )
		{
			$this->env[ 'rack.request.form_input' ] = $this->env[ 'rack.input' ];
			
			// FIXME: Implement multipart processing
			// if ( ! ( $this->env[ 'rack.request.form_hash' ] = $this->parseMultipart() ) )
			$multipart = false;
			if ( $multipart )
				die("FIXME: Implement multipart.");
			else
			{
				$form_vars = $this->env[ 'rack.input' ]->read();
				$form_vars = preg_replace( '/\0\z/', '', $form_vars );
				
				$this->env[ 'rack.request.form_vars' ] = $form_vars;
				$this->env[ 'rack.request.form_hash' ] = $this->parseQuery( $form_vars );
				$this->env[ 'rack.input' ]->rewind();
			}
			
			$return = &$this->env[ 'rack.request.form_hash' ];
			return $return;
		}
		
		$return = array();
		return $return;
	}
	
	// TODO: Document!
	# The union of GET and POST data.
	public function &params()
	{
		try
		{
			$get = &$this->GET();
			$get = array_merge( $get, $this->POST() );
			return $get;
		}
		catch ( Prack_Error_EOF $e1 )
		{
			return $this->GET();
		}
	}
	
	// TODO: Document!
	public function getParam( $k )
	{
		$params = $this->params();
		return $params[ $k ];
	}
	
	// TODO: Document!
	public function setParam( $k, $v )
	{
		$params = &$this->params();
		$params[ (string)$k ] = $v;
	}
	
	// TODO: Document!
	public function valuesAt()
	{
		$keys   = func_get_args();
		$params = &$this->params();
		
		$values = array();
		foreach ( $keys as $key )
			array_push( $values, @$params[ $key ] );
		
		return $values;
	}
	
	// TODO: Document!
	# the referer of the client or '/'
	public function referer()
	{
		return ( @$this->env[ 'HTTP_REFERER' ] ) ? $this->env[ 'HTTP_REFERER' ] : '/';
	}
	
	// TODO: Document!
	public function referrer()
	{
		return $this->referer();
	}
	
	// TODO: Document!
	public function userAgent()
	{
		return ( @$this->env[ 'HTTP_USER_AGENT' ] ) ? $this->env[ 'HTTP_USER_AGENT' ] : null;
	}
	
	// TODO: Document!
	public function cookies()
	{
		if ( !@$this->env[ 'HTTP_COOKIE' ] )
			return array();

		if ( @$this->env[ 'rack.request.cookie_string' ] && $this->env[ 'rack.request.cookie_string' ] === $this->env[ 'HTTP_COOKIE' ] )
			return @$this->env[ 'rack.request.cookie_hash' ];
		else
		{
			$cookie_string = $this->env[ 'HTTP_COOKIE' ];
			$cookie        = http_parse_cookie( $cookie_string ); // FIXME: Implement cookie parsing.
			
			$this->env[ 'rack.request.cookie_string' ] = $cookie_string;
			$this->env[ 'rack.request.cookie_hash'   ] = $cookie->cookies;
		}
		
		return @$this->env[ 'rack.request.cookie_hash' ];
	}
	
	// TODO: Document!
	public function isXhr()
	{
		return ( @$this->env[ 'HTTP_X_REQUESTED_WITH' ] && $this->env[ 'HTTP_X_REQUESTED_WITH' ] === 'XMLHttpRequest' );
	}
	
	// TODO: Document!
	# Tries to return a remake of the original request URL as a string.
	public function url()
	{
		$scheme = $this->scheme();
		$port   = (int)( $this->port() );
		
		$url  = $scheme.'://';
		$url .= $this->host();
		
		if ( ( $scheme == 'https' && $port != 443 ) ||
		     ( $scheme == 'http'  && $port != 80  )    )
			$url .= ':'.$port;
			
		$url .= $this->fullpath();
		
		return $url;
	}
	
	// TODO: Document!
	public function path()
	{
		return $this->scriptName().$this->pathInfo();
	}
	
	// TODO: Document!
	public function fullpath()
	{
		$query_string = $this->queryString();
		if ( empty( $query_string ) )
			return $this->path();
		
		return $this->path().'?'.$this->queryString();
	}
	
	// TODO: Document!
	public function acceptEncoding()
	{
		$accept_encoding = (string)@$this->env[ 'HTTP_ACCEPT_ENCODING' ];
		
		// If there are no matches, preg_split returns an array with one empty string.
		// This is absolutely fucking stupid, but hey.
		$candidates = preg_split( '/,\s*/', $accept_encoding );
		if ( $candidates == array( '' ) )
			return array();
			
		static $callback = null;
		if ( is_null( $callback ) )
			$callback = array( 'Prack_Request', 'processAcceptEncodingCandidate' );
		
		return array_map( $callback, $candidates );
	}
	
	// TODO: Document!
	public function ip()
	{
		if ( $address = @$this->env[ 'HTTP_X_FORWARDED_FOR' ] )
		{
			$address = explode( ',', $address );
			$address = preg_grep( '/\d\./', $address );
			return isset( $address ) ? reset( $address ) : trim( (string)@$this->env[ 'REMOTE_ADDR' ] );
		}
		
		return @$this->env[ 'REMOTE_ADDR' ];
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