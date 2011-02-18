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
		if ( preg_match_all( '/\A([^\s,]+?)(?:;\s*q=(\d+(?:\.\d+)?))?\z/', $candidate, $matches ) )
		{
			$candidate    = $matches[ 1 ][ 0 ];
			$value        = $matches[ 2 ][ 0 ];
			$value_viable = ( isset( $value ) && strlen( $value ) > 0 );
			return array( $candidate, (float)( $value_viable ? $value : 1.0 ) );
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
			$form_data_media_types = array(
				'application/x-www-form-urlencoded',
				'multipart/form-data'
			);
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
		
		if (empty( $parseable_data_media_types ) )
		{
			$parseable_data_media_types = array(
				'multipart/related',
				'multipart/mixed'
			);
		}
		
		return $parseable_data_media_types;
	}
	
	// TODO: Document!
	function __construct( $env )
	{
		$this->env = $env;
	}
	
	public function contentLength()
	{
		return isset( $this->env[ 'CONTENT_LENGTH' ] ) ? $this->env[ 'CONTENT_LENGTH' ] : null;
	}
	
	public function contentType()
	{
		return isset( $this->env[ 'CONTENT_TYPE' ] ) ? $this->env[ 'CONTENT_TYPE' ] : null;
	}
	
	public function body()          { return         $this->env[ 'rack.input'      ]; }
	public function scheme()        { return         $this->env[ 'rack.url_scheme' ]; }
	public function scriptName()    { return (string)$this->env[ 'SCRIPT_NAME'     ]; }
	public function pathInfo()      { return (string)$this->env[ 'PATH_INFO'       ]; }
	public function port()          { return    (int)$this->env[ 'SERVER_PORT'     ]; }
	public function requestMethod() { return         $this->env[ 'REQUEST_METHOD'  ]; }
	public function queryString()   { return (string)$this->env[ 'QUERY_STRING'    ]; }
	
	public function logger()
	{ 
		return isset( $this->env[ 'rack.logger' ] ) ? $this->env[ 'rack.logger' ] : null;
	}

	public function session()
	{ 
		return isset( $this->env[ 'rack.session' ] ) ? $this->env[ 'rack.session' ] : array();
	}
	
	public function sessionOptions()
	{ 
		return isset( $this->env[ 'rack.session' ] ) ? $this->env[ 'rack.session.options' ] : array();
	}
	
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
		return strtolower( reset( $components ) );
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
		return isset( $media_type_params ) ? $media_type_params[ 'charset' ] : null;
	}
	
	// TODO: Document!
	public function hostWithPort()
	{
		$f_header  = 'HTTP_X_FORWARDED_HOST';
		$forwarded = isset( $this->env[ $f_header ] ) ? $this->env[ $f_header ] : null;
		if ( isset( $forwarded ) )
		{
			$forwarded = preg_split( '/,\s?/', $forwarded, 2 );
			return end( $forwarded );
		}
		
		if ( isset( $this->env[ 'HTTP_HOST' ] ) )
			return $this->env[ 'HTTP_HOST' ];
		
		$host = null;
		if ( isset( $this->env[ 'SERVER_NAME' ] ) )
			$host = $this->env[ 'SERVER_NAME' ];
		else if ( isset( $this->env[ 'SERVER_ADDR' ] ) )
			$host = $this->env[ 'SERVER_ADDR' ];
		
		return "{$host}:{$this->env[ 'SERVER_PORT' ]}";
	}
	
	// TODO: Document!
	public function host()
	{
		return preg_replace( '/:\d+$/', '', $this->hostWithPort() );
	}
	
	// TODO: Document!
	public function setScriptName( $script_name )
	{
		$this->env[ 'SCRIPT_NAME' ] = (string)$script_name;
	}
	
	// TODO: Document!
	public function setPathInfo( $path_info )
	{
		$this->env[ 'PATH_INFO' ] = (string)$path_info;
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
		$method_override = "rack.methodoverride.original_method";
		$request_method  = isset( $this->env[ $method_override ] ) ? $this->env[ $method_override ] :
		                                                             $this->env[ 'REQUEST_METHOD' ];
		return ( $request_method == 'POST' && is_null( $type ) ) || in_array( $type, self::formDataMediaTypes() );
	}
	
	// TODO: Document!
	public function isParseableData()
	{
		return in_array( $this->mediaType(), self::parseableDataMediaTypes() );
	}
	
	// TODO: Document!
	# Returns the data recieved in the query string.
	public function GET()
	{
		if ( isset( $this->env[ 'rack.request.query_string' ] ) && $this->env[ 'rack.request.query_string' ] == $this->queryString() )
			return $this->env[ 'rack.request.query_hash' ];
		
		parse_str( $this->queryString(), $vars );
		
		$this->env[ 'rack.request.query_string' ] = $this->queryString();
		$this->env[ 'rack.request.query_hash'   ] = $vars;
		
		return $vars;
	}
	
	// TODO: Document!
	# Returns the data recieved in the request body.
	#
	# This method support both application/x-www-form-urlencoded and
	# multipart/form-data.
	public function POST()
	{
		if ( !isset( $this->env[ 'rack.input' ] ) || is_null( $this->env[ 'rack.input' ] ) )
			throw new Prack_Error_Runtime_RackInputMissing();
		
		$form_input = isset( $this->env[ 'rack.request.form_input' ] ) ? $this->env[ 'rack.request.form_input' ] :
		                                                                 null;
		
		// TODO: Document wtf this is doing.
		if ( $form_input == $this->env[ 'rack.input' ] )
			return isset( $this->env[ 'rack.request.form_hash' ] ) ? $this->env[ 'rack.request.form_hash' ] : null;
		
		if ( $this->isFormData() || $this->isParseableData() )
		{
			$this->env[ 'rack.request.form_input' ] = $this->env[ 'rack.input' ];
			
			// TODO: After implementing multipart:
			// if ( ! ( $this->env[ 'rack.request.form_hash' ] = $this->parseMultipart() ) )
			$multipart = false;
			if ( !$multipart )
			{
				$form_vars = $this->env[ 'rack.input' ]->read();
				
				// Fix for Safari Ajax postings that always append \0
				$form_vars = preg_replace( '/\0\z/', '', $form_vars );
				
				parse_str( $form_vars, $form_hash );
				$this->env[ 'rack.request.form_vars' ] = $form_vars;
				$this->env[ 'rack.request.form_hash' ] = $form_hash;
				$this->env[ 'rack.input' ]->rewind();
			}
			return $this->env[ 'rack.request.form_hash' ];
		}
		return array();
	}
	
	// TODO: Document!
	# The union of GET and POST data.
	public function params()
	{
		if ( is_null( $this->params ) )
			$this->params = array_merge( $this->GET(), $this->POST() );
		return $this->params;
	}
	
	// TODO: Document!
	public function getParam( $k )
	{
		if ( is_null( $this->params ) )
			$this->params();
		return $this->params[ $k ];
	}
	
	// TODO: Document!
	public function setParam( $k, $v )
	{
		if ( is_null( $this->params ) )
			$this->params();
		$this->params[ (string)$k ] = $v;
	}
	
	// TODO: Document!
	public function valuesAt()
	{
		$result = array();
		$keys   = func_get_args();
		$params = $this->params();
		
		foreach ( $keys as $key )
			$result[ ] = $params[ $key ];
		
		return $result;
	}
	
	// TODO: Document!
	# the referer of the client or '/'
	public function referer()
	{
		return isset( $this->env[ 'HTTP_REFERER' ] ) ? $this->env[ 'HTTP_REFERER' ] : '/';
	}
	
	// TODO: Document!
	public function referrer()
	{
		return $this->referer();
	}
	
	// TODO: Document!
	public function userAgent()
	{
		return isset( $this->env[ 'HTTP_USER_AGENT'] ) ? $this->env[ 'HTTP_USER_AGENT' ] : null;
	}
	
	// TODO: Document!
	public function cookies()
	{
		if ( !isset( $this->env[ 'HTTP_COOKIE' ] ) )
			return array();

		if ( isset( $this->env[ 'rack.request.cookie_string' ] ) && 
		     $this->env[ 'rack.request.cookie_string' ] == $this->env[ 'HTTP_COOKIE' ] )
			return $this->env[ 'rack.request.cookie_hash' ];
		else
		{
			$this->env[ 'rack.request.cookie_string' ] = $this->env[ 'HTTP_COOKIE' ];
			
			$cookie = http_parse_cookie( $this->env[ 'HTTP_COOKIE' ] );
			$this->env[ 'rack.request.cookie_hash' ] = $cookie->cookies; ;
		}
		
		return $this->env[ 'rack.request.cookie_hash' ];
	}
	
	// TODO: Document!
	public function isXhr()
	{
		return isset( $this->env[ 'HTTP_X_REQUESTED_WITH' ] ) && ( $this->env[ 'HTTP_X_REQUESTED_WITH' ] == 'XMLHttpRequest' );
	}
	
	// TODO: Document!
	# Tries to return a remake of the original request URL as a string.
	public function url()
	{
		$scheme = $this->scheme();
		$port   = $this->port();
		
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
		$path         = $this->path();
		return empty( $query_string ) ? $path : "{$path}?{$query_string}";
	}
	
	// TODO: Document!
	public function acceptEncoding()
	{
		$accept_encoding = isset( $this->env[ 'HTTP_ACCEPT_ENCODING' ] ) ? (string)$this->env[ 'HTTP_ACCEPT_ENCODING' ] : null;
		
		if ( is_null( $accept_encoding ) || empty( $accept_encoding ) )
			return array();
		
		$candidates = preg_split( '/,\s*/', $accept_encoding );
		$callback   = array( 'Prack_Request', 'processAcceptEncodingCandidate' );
		
		return array_map( $callback, $candidates );
	}
	
	// TODO: Document!
	// FIXME: The unit tests for this function require Prack_Lint to be implemented. That'll take a while.
	/*
	
	public function ip()
	{
		$address = $this->env[ 'HTTP_X_FORWARDED_FOR' ];
		if ( isset( $address ) )
		{
			$address = preg_split( '/,/', $address );
			$address = preg_grep( '/\d\./', $address );
			return isset( $address[ 0 ] ) ? $address[ 0 ] : strip( (string)$this->env[ 'REMOTE_ADDR' ]);
		}
		
		return isset( $this->env[ 'REMOTE_ADDR' ] ) ? $this->env[ 'REMOTE_ADDR' ] : null;
	}
	
	*/
	
	// TODO: Document!
	public function &getEnv()
	{
		return $this->env;
	}
}