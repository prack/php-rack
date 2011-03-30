<?php

// TODO: Document!
class Prack_File
  implements Prack_I_MiddlewareApp, Prb_I_Enumerable
{
	private $root;
	private $path;
	private $path_info;
	
	// TODO: Document!
	static function with( $root )
	{
		return new Prack_File( $root );
	}
	
	// TODO: Document!
	public function __construct( $root )
	{
		$this->root = $root;
	}
	
	// TODO: Document!
	public function call( &$env )
	{
		$clone = clone $this;
		return $clone->_call( $env );
	}
	
	// TODO: Document!
	public function _call( &$env )
	{
		$this->path_info = Prack_Utils::singleton()->unescape( $env[ 'PATH_INFO' ] );
		
		if ( strpos( $this->path_info, '..' ) !== false )
			return $this->forbidden();
		
		$joined     = join( DIRECTORY_SEPARATOR, array( $this->root, $this->path_info ) );
		$ds         = preg_quote( DIRECTORY_SEPARATOR, '/' );
		$this->path = preg_replace( "/{$ds}+/", DIRECTORY_SEPARATOR, $joined );
		
		try
		{
			// FIXME: Handle errors here?
			if ( file_exists( $this->path ) && is_file( $this->path ) && is_readable( $this->path ) )
				return $this->serving();
			else
				throw new Prb_Exception_System_ErrnoEPERM( "file {$this->path} does not exist or is unreadable" );
		}
		catch( Prb_Exception_System $e )
		{
			return $this->notFound();
		}
	}
	
	// TODO: Document!
	public function forbidden()
	{
		$body = "Forbidden\n";
		return array(
		  403,
		  array(
		    'Content-Type'   => 'text/plain',
		    'Content-Length' => (string)strlen( $body ),
		    'X-Cascade'      => 'pass'
		  ),
		  array( $body )
		);
	}
	
	// TODO: Document!
	# NOTE:
	#   We check via File::size? whether this file provides size info
	#   via stat (e.g. /proc files often don't), otherwise we have to
	#   figure it out by reading the whole file into memory. And while
	#   we're at it we also use this as body then.
	public function serving()
	{
		if ( $size = filesize( $this->path ) )
			$body = $this;
		else
		{
			$body = array( Prb_IO::withFile( $this->path, Prb_IO_File::NO_CREATE_READ )->read() );
			$size = Prack_Utils::singleton()->bytesize( reset( $body ) );
		}
		
		$pathinfo  = pathinfo( $this->path );
		$extension = isset( $pathinfo[ 'extension' ] ) ? '.'.$pathinfo[ 'extension' ] : null;
		
		return array(
		  200,
		  array(
		    'Last-Modified'  => Prb::Time( filemtime( $this->path ) )->httpdate(),
		    'Content-Type'   => Prack_Mime::mimeType( $extension, 'text/plain' ),
		    'Content-Length' => (string)$size
		  ),
		  $body
		);
	}
	
	// TODO: Document!
	public function notFound()
	{
		$body = "File not found: {$this->path_info}\n";
		return array(
		  404,
		  array(
		    'Content-Type'   => 'text/plain',
		    'Content-Length' => (string)strlen( $body ),
		    'X-Cascade'      => 'pass'
		  ),
		  array( $body )
		);
	}
	
	// TODO: Document!
	public function each( $callback )
	{
		if ( !is_callable( $callback ) )
			throw new Prb_Error_Callback( 'each $callback is not callable' );
		
		$file = Prb_IO::withFile( $this->path, Prb_IO_File::NO_CREATE_READ );
		while ( $part = $file->read( 8192 ) )
			call_user_func( $callback, $part );
	}
	
	// TODO: Document!
	public function root()
	{
		return $this->root;
	}
	
	// TODO: Document!
	public function setRoot( $root )
	{
		$this->root = $root;
	}
	
	// TODO: Document!
	public function path()
	{
		return $this->path;
	}
	
	// TODO: Document!
	public function toPath()
	{
		return $this->path();
	}
	
	// TODO: Document!
	public function setPath( $path )
	{
		$this->path = $path;
	}
}
