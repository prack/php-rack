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
	static function with( $middleware_app, $options = null )
	{
		return new Prack_Static( $middleware_app, $options );
	}
	
	// TODO: Document!
	function __construct( $middleware_app, $options = null )
	{
		$options = is_null( $options ) ? Prb::Hsh() : $options;
		if ( !( $options instanceof Prb_Hash ) )
			throw new Prb_Exception_Type( 'FAILSAFE: __construct $options not a Prb_Hash' );
		
		$this->middleware_app = $middleware_app;
		$this->urls           = $options->contains( 'urls' )
		  ? $options->delete( 'urls' )
		  : Prb::Ary( array( Prb::Str( '/favicon.ico' ) ) );
		
		$root = $options->contains( 'root' )
		  ? $options->delete( 'root' )
		  : Prb::Str( getcwd() );
		
		$this->file_server = Prack_File::with( $root );
	}
	
	// TODO: Document!
	public function call( $env )
	{
		$this->path = $env->get( 'PATH_INFO' );
		
		$callback  = array( $this, 'onDetectAny' );
		$can_serve = $this->urls->detectAny( $callback );
		
		if ( $can_serve )
			return $this->file_server->call( $env );
		
		return $this->middleware_app->call( $env );
	}
	
	// TODO: Document!
	public function onDetectAny( $item )
	{
		$quoted = preg_quote( $item->raw(), '/' );
		return (bool)$this->path->match( "/^{$quoted}/" );
	}
}