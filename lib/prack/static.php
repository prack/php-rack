<?php

// TODO: Document!
# The Rack::Static middleware intercepts requests for static files
# (javascript files, images, stylesheets, etc) based on the url prefixes
# passed in the options, and serves them using a Rack::File object. This
# allows a Rack stack to serve both static and dynamic content.
#
# Examples:
#     use Rack::Static, :urls => ["/media"]
#     will serve all requests beginning with /media from the "media" folder
#     located in the current directory (ie media/*).
#
#     use Rack::Static, :urls => ["/css", "/images"], :root => "public"
#     will serve all requests beginning with /css or /images from the folder
#     "public" in the current directory (ie public/css/* and public/images/*)
class Prack_Static
  implements Prack_I_MiddlewareApp
{
	private $middleware_app;
	private $urls;
	private $file_server;
	private $path;
	
	// TODO: Document!
	static function with( $middleware_app, $options = array() )
	{
		return new Prack_Static( $middleware_app, $options );
	}
	
	// TODO: Document!
	function __construct( $middleware_app, $options = array() )
	{
		$this->middleware_app = $middleware_app;
		$this->urls           = @$options[ 'urls' ] ? $options[ 'urls' ] : array( '/favicon.ico' );
		$this->file_server    = Prack_File::with( @$options[ 'root' ] ? $options[ 'root' ] : getcwd() );
	}
	
	// TODO: Document!
	public function call( &$env )
	{
		$this->path = (string)@$env[ 'PATH_INFO' ];
		$can_serve  = false;
		
		foreach( $this->urls as $url )
		{
			$quoted    = preg_quote( $url, '/' );
			$can_serve = (bool)preg_match( "/^{$quoted}/", $this->path );
			if ( $can_serve === true )
				break;
		}
		
		if ( $can_serve )
			return $this->file_server->call( $env );
		
		return $this->middleware_app->call( $env );
	}
}