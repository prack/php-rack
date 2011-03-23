<?php

// TODO: Document!
# Rack::CommonLogger forwards every request to an +app+ given, and
# logs a line in the Apache common log format to the +logger+, or
# rack.errors by default.
class Prack_CommonLogger
  implements Prack_I_MiddlewareApp
{
	private $middleware_app;
	private $logger;
	
	// TODO: Document!
	static function format()
	{
		static $format = null;
		
		if ( is_null( $format ) )
			$format = Prb::Str( '%s - %s [%s] "%s %s%s %s" %d %s %0.4f\n' );
		
		return $format;
	}
	
	// TODO: Document!
	static function with( $middleware_app, $logger = null )
	{
		return new Prack_CommonLogger( $middleware_app, $logger );
	}
	
	// TODO: Document!
	function __construct( $middleware_app, $logger = null )
	{
		$this->middleware_app = $middleware_app;
		$this->logger         = $logger;
	}
	
	// TODO: Document!
	public function call( $env )
	{
		$began_at = Prb::Time();
		
		list( $status, $headers, $body ) = $this->middleware_app->call( $env )->raw();
		$headers = Prack_Utils_HeaderHash::using( $headers );
		$this->log( $env, $status, $headers, $began_at );
		return Prb::Ary( array( $status, $headers, $body ) );
	}
	
	// TODO: Document!
	private function log( $env, $status, $headers, $began_at )
	{
		$now    = Prb::Time();
		$length = $this->extractContentLength( $headers );
		
		$remote_addr = $env->contains( 'HTTP_X_FORWARDED_FOR' )
		  ? $env->get( 'HTTP_X_FORWARDED_FOR' )
		  : ( $env->contains( 'REMOTE_ADDR' ) ? $env->get( 'REMOTE_ADDR' ) : Prb::Str( '-' ) );
		
		$remote_user = $env->contains( 'REMOTE_USER' ) ? $env->get( 'REMOTE_USER' ) : Prb::Str( '-' );
		
		$logger = isset( $this->logger ) ? $this->logger : $env->get( 'rack.errors' );
		$logger->write(
		  self::format()->sprintf(
		    $remote_addr,
		    $remote_user,
		    $now->strftime( Prb::Str( '%d/%b/%Y %H:%M:%S' ) ),
		    $env->get( 'REQUEST_METHOD' ),
		    $env->get( 'PATH_INFO'      ),
		    ( $env->contains( 'QUERY_STIRNG' ) && !$env->get( 'QUERY_STRING' )->isEmpty() )
		      ? Prb::Str( '?'.$env->get( 'QUERY_STRING' )->raw() )
		      : Prb::Str(),
		    $env->get( 'HTTP_VERSION' ),
		    $status->toS()->slice( 0, 3 ),
		    $length,
		    Prb::Num( $now->raw() - $began_at->raw() )
		  )
		);
	}
	
	// TODO: Document!
	private function extractContentLength( $headers )
	{
		if ( !$headers->contains( 'Content-Length' ) )
			return Prb::Str( '-' );
		
		return ( $headers->get( 'Content-Length' )->toS()->raw() == '0' )
		  ? Prb::Str( '-' )
		  : $headers->get( 'Content-Length' );
	}
}

/*
  class CommonLogger
    # Common Log Format: http://httpd.apache.org/docs/1.3/logs.html#common
    # lilith.local - - [07/Aug/2006 23:58:02] "GET / HTTP/1.1" 500 -
    #             %{%s - %s [%s] "%s %s%s %s" %d %s\n} %
    FORMAT = %{%s - %s [%s] "%s %s%s %s" %d %s %0.4f\n}

    def initialize(app, logger=nil)
      @app = app
      @logger = logger
    end

    def call(env)
      began_at = Time.now
      status, header, body = @app.call(env)
      header = Utils::HeaderHash.new(header)
      log(env, status, header, began_at)
      [status, header, body]
    end

    private

    def log(env, status, header, began_at)
      now = Time.now
      length = extract_content_length(header)

      logger = @logger || env['rack.errors']
      logger.write FORMAT % [
        env['HTTP_X_FORWARDED_FOR'] || env["REMOTE_ADDR"] || "-",
        env["REMOTE_USER"] || "-",
        now.strftime("%d/%b/%Y %H:%M:%S"),
        env["REQUEST_METHOD"],
        env["PATH_INFO"],
        env["QUERY_STRING"].empty? ? "" : "?"+env["QUERY_STRING"],
        env["HTTP_VERSION"],
        status.to_s[0..3],
        length,
        now - began_at ]
    end

    def extract_content_length(headers)
      value = headers['Content-Length'] or return '-'
      value.to_s == '0' ? '-' : value
    end
  end
end

*/