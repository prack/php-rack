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
	public function call( $env )
	{
		try
		{
			$response = $this->middleware_app->call( $env );
		}
		catch ( Exception $e )
		{ 
			$backtrace = $this->pretty( $env, $e );
			
			return Prb::Ary( array(
			  Prb::Num( 500 ),
			  Prb::Hsh( array(
			    'Content-Type'   => Prb::Str( 'text/html' ),
			    'Content-Length' => Prb::Str( (string)( $backtrace->join()->size() ) )
			  ) ),
			  $backtrace
			) );
		}
		return $response;
	}
	
	// TODO: Document!
	public function pretty( $env, $exception )
	{
		$request = new Prack_Request( $env );
		$path    = Prb::Str( $request->scriptName()->raw() . $request->pathInfo()->raw() )->squeeze( '/' );
		
		// Gather up the frames by iterating over them.
		$callback    = array( $this, 'collectFrames' );
		$frames      = Prb::Ary( $exception->getTrace() )->collect( $callback )->compact();
		$environment = $env;
		
		ob_start();
			$se = $this;
			include $this->template;
		$result = ob_get_clean();
		
		return Prb::Ary( array( Prb::Str( $result ) ) );
	}
	
	// TODO: Document!
	public function collectFrames( $line )
	{
		// PHPUnit's stack traces are ridiculous. The regexp match here removes those lines from consideration.
		// This avoids a lot of file IO, since lines from the files aren't loaded.
		if ( !isset( $line[ 'file' ] ) || !isset( $line[ 'line' ] ) || preg_match( '/PHPUnit|phpunit/', $line[ 'file' ] ) )
			return null;
			
		$frame = new stdClass();
		$frame->filename =      $line[ 'file'     ];
		$frame->lineno   = (int)$line[ 'line'     ];
		$frame->function =      $line[ 'function' ];
		
		try
		{
			$lineno = $frame->lineno - 1;
			$lines  = Prb_IO::withFile( Prb::Str( $frame->filename ), Prb_IO_File::NO_CREATE_READ )->readlines();
			
			$frame->pre_context_lineno  = max( $lineno - self::CONTEXT, 0 );
			$frame->pre_context         = $lines->slice( $frame->pre_context_lineno, $lineno, true );
			$frame->context_line        = $lines->get( $lineno )->chomp();
			$frame->post_context_lineno = min( $lineno + self::CONTEXT, $lines->size() );
			$frame->post_context        = $lines->slice( $lineno + 1, $frame->post_context_lineno );
		}
		catch ( Exception $lineerror ) {}
		
		return $frame;
	}
	
	// TODO: Document!
	public function h( $item )
	{
		$item = $item instanceof Prb_String ? $item : Prb::Str( (string)$item );
		return Prack_Utils::singleton()->escapeHtml( $item );
	}
}