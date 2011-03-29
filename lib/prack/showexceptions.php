<?php

// TODO: Document!
# Rack::ShowExceptions catches all exceptions raised from the app it
# wraps.  It shows a useful backtrace with the sourcefile and
# clickable context, the whole Rack environment and the request
# data.
#
# Be careful when you use this on public-facing sites as it could
# reveal information helpful to attackers.
class Prack_ShowExceptions
  implements Prack_I_MiddlewareApp
{
	const CONTEXT = 7;
	
	private $middleware_app;
	
	// TODO: Document!
	static function template()
	{
		static $template = null;
		
		if ( is_null( $template ) )
			$template = join( DIRECTORY_SEPARATOR, array( dirname( __FILE__ ), 'showexceptions.template' ) );
		
		return $template;
	}
	
	// TODO: Document!
	function __construct( $middleware_app )
	{
		$this->middleware_app = $middleware_app;
		$this->template       = self::template();
	}
	
	// TODO: Document!
	public function call( &$env )
	{
		try
		{
			$response = $this->middleware_app->call( $env );
		}
		catch ( Exception $e )
		{ 
			$backtrace = $this->pretty( $env, $e );
			return array(
			  500,
			  array( 'Content-Type' => 'text/html', 'Content-Length' => strlen( join( '', $backtrace ) ) ),
			  $backtrace
			);
		}
		return $response;
	}
	
	// TODO: Document!
	public function pretty( $env, $exception )
	{
		$request = new Prack_Request( $env );
		$path    = preg_replace( '/\/+/', '/', $request->scriptName().$request->pathInfo() );
		$frames  = array();
		
		// Gather up the frames by iterating over them.
		foreach( $exception->getTrace() as $line )
		{
			// PHPUnit's stack traces are ridiculous. The regexp match here removes those lines from consideration.
			// This avoids a lot of file IO, since lines from the files aren't loaded.
			if ( !isset( $line[ 'file' ] ) || !isset( $line[ 'line' ] ) || preg_match( '/PHPUnit|phpunit/', $line[ 'file' ] ) )
				continue;
			
			$frame = new stdClass();
			
			$frame->filename =      $line[ 'file'     ];
			$frame->lineno   = (int)$line[ 'line'     ];
			$frame->function =      $line[ 'function' ];
			
			try
			{
				$lineno = $frame->lineno - 1;
				$lines  = Prb_IO::withFile( $frame->filename, Prb_IO_File::NO_CREATE_READ )->readlines();
				
				$frame->pre_context_lineno  = max( $lineno - self::CONTEXT, 0 );
				$frame->pre_context         = array_slice( $lines, $frame->pre_context_lineno, $lineno - $frame->pre_context_lineno );
				$frame->context_line        = rtrim( $lines[ $lineno ] );
				$frame->post_context_lineno = min( $lineno + self::CONTEXT, count( $lines ) );
				$frame->post_context        = array_slice( $lines, $lineno + 1, $frame->post_context_lineno - $lineno );
			}
			catch ( Exception $lineerror ) {
				continue;
			}
			
			array_push( $frames, $frame );
		}
		
		$environment = $env;
		
		ob_start();
			$se = $this;
			include $this->template;
		$result = ob_get_clean();
		
		return array( $result );
	}
	
	// TODO: Document!
	public function h( $item )
	{
		return Prack_Utils::singleton()->escapeHtml( $item );
	}
}