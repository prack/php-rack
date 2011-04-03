<?php

// TODO: Document!
# = Sendfile
#
# The Sendfile middleware intercepts responses whose body is being
# served from a file and replaces it with a server specific X-Sendfile
# header. The web server is then responsible for writing the file contents
# to the client. This can dramatically reduce the amount of work required
# by the Ruby backend and takes advantage of the web server's optimized file
# delivery code.
#
# In order to take advantage of this middleware, the response body must
# respond to +to_path+ and the request must include an X-Sendfile-Type
# header. Rack::File and other components implement +to_path+ so there's
# rarely anything you need to do in your application. The X-Sendfile-Type
# header is typically set in your web servers configuration. The following
# sections attempt to document
#
# === Nginx
#
# Nginx supports the X-Accel-Redirect header. This is similar to X-Sendfile
# but requires parts of the filesystem to be mapped into a private URL
# hierarachy.
#
# The following example shows the Nginx configuration required to create
# a private "/files/" area, enable X-Accel-Redirect, and pass the special
# X-Sendfile-Type and X-Accel-Mapping headers to the backend:
#
#   location ~ /files/(.*) {
#     internal;
#     alias /var/www/$1;
#   }
#
#   location / {
#     proxy_redirect     off;
#
#     proxy_set_header   Host                $host;
#     proxy_set_header   X-Real-IP           $remote_addr;
#     proxy_set_header   X-Forwarded-For     $proxy_add_x_forwarded_for;
#
#     proxy_set_header   X-Sendfile-Type     X-Accel-Redirect;
#     proxy_set_header   X-Accel-Mapping     /files/=/var/www/;
#
#     proxy_pass         http://127.0.0.1:8080/;
#   }
#
# Note that the X-Sendfile-Type header must be set exactly as shown above. The
# X-Accel-Mapping header should specify the name of the private URL pattern,
# followed by an equals sign (=), followed by the location on the file system
# that it maps to. The middleware performs a simple substitution on the
# resulting path.
#
# See Also: http://wiki.codemongers.com/NginxXSendfile
#
# === lighttpd
#
# Lighttpd has supported some variation of the X-Sendfile header for some
# time, although only recent version support X-Sendfile in a reverse proxy
# configuration.
#
#   $HTTP["host"] == "example.com" {
#      proxy-core.protocol = "http"
#      proxy-core.balancer = "round-robin"
#      proxy-core.backends = (
#        "127.0.0.1:8000",
#        "127.0.0.1:8001",
#        ...
#      )
#
#      proxy-core.allow-x-sendfile = "enable"
#      proxy-core.rewrite-request = (
#        "X-Sendfile-Type" => (".*" => "X-Sendfile")
#      )
#    }
#
# See Also: http://redmine.lighttpd.net/wiki/lighttpd/Docs:ModProxyCore
#
# === Apache
#
# X-Sendfile is supported under Apache 2.x using a separate module:
#
# http://tn123.ath.cx/mod_xsendfile/
#
# Once the module is compiled and installed, you can enable it using
# XSendFile config directive:
#
#   RequestHeader Set X-Sendfile-Type X-Sendfile
#   ProxyPassReverse / http://localhost:8001/
#   XSendFile on
class Prack_Sendfile
  implements Prack_I_MiddlewareApp
{
	// TODO: Document!
	static function with( $middleware_app, $variation = null )
	{
		return new Prack_Sendfile( $middleware_app, $variation );
	}
	
	// TODO: Document!
	function __construct( $middleware_app, $variation = null )
	{
		$this->middleware_app = $middleware_app;
		$this->variation      = $variation;
	}
	
	// TODO: Document!
	public function call( &$env )
	{
		list( $status, $headers, $body ) = $this->middleware_app->call( $env );
		
		$headers = Prack_Utils_HeaderHash::using( $headers );
		if ( method_exists( $body, 'toPath' ) )
		{
			switch ( $type = $this->variation( $env ) )
			{
				case 'X-Accel-Redirect':
					$path = realpath( $body->toPath() );
					if ( $url = $this->mapAccelPath( $env, $path ) )
					{
						$headers->set( $type, $url );
						$body = array();
					}
					else
						$env[ 'rack.errors' ]->write( "X-Accel-Mapping header missing.\n" );
					break;
				case 'X-Sendfile':
				case 'X-Lighttpd-Send-File':
					$path = realpath( $body->toPath() );
					$headers->set( $type, $path );
					$body = array();
					break;
				case '':
				case null:
					break;
				default:
					$env[ 'rack.errors' ]->concat( "Unknown x-sendfile variation: '{$variation}'\n" );
			}
		}
		
		return array( $status, $headers->raw(), $body );
	}
	
	// TODO: Document!
	private function variation( $env )
	{
		return isset( $this->variation )
		  ? $this->variation
		  : ( @$env[ 'sendfile.type' ] ? $env[ 'sendfile.type' ] : @$env[ 'HTTP_X_SENDFILE_TYPE' ] );
	}
	
	// TODO: Document!
	private function mapAccelPath( $env, $file )
	{
		if ( $mapping = @$env[ 'HTTP_X_ACCEL_MAPPING' ] )
		{
			list( $internal, $external ) = array_map( 'trim', preg_split( '/=/', $mapping, 2 ) );
			$quoted = preg_quote( $internal, '/' );
			return preg_replace( "/^{$quoted}/i", $external, $file, 1 );
		}
		
		return null;
	}
}