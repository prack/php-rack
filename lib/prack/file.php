<?php

// TODO: Document!
class Prack_File
  implements Prack_Interface_MiddlewareApp, Prb_Interface_Enumerable
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
	public function call( $env )
	{
		$clone = clone $this;
		return $clone->_call( $env );
	}
	
	// TODO: Document!
	public function _call( $env )
	{
		$this->path_info = Prack_Utils::singleton()->unescape( $env->get( 'PATH_INFO' ) );
		
		if ( $this->path_info->contains( Prb::_String( '..' ) ) )
			return $this->forbidden();
		
		$this->path = Prb::_String( join(
		  DIRECTORY_SEPARATOR,
		  array( $this->root->raw(), $this->path_info->raw() )
		) );
		
		try
		{
			// FIXME: Handle errors here?
			if ( file_exists( $this->path->raw() ) && is_file( $this->path->raw() ) && is_readable( $this->path->raw() ) )
				return $this->serving();
			else
				throw new Prb_Exception_System_ErrnoEPERM( "file {$this->path->raw()} does not exist or is unreadable" );
		}
		catch( Prb_Exception_System $e )
		{
			return $this->notFound();
		}
	}
	
	// TODO: Document!
	public function forbidden()
	{
		$body = Prb::_String( "Forbidden\n" );
		return Prb::_Array( array(
		  Prb::_Numeric( 403 ),
		  Prb::_Hash( array(
		    'Content-Type'   => Prb::_String( 'text/plain' ),
		    'Content-Length' => Prb::_Numeric( $body->size() )->toS(),
		    'X-Cascade'      => Prb::_String( 'pass' )
		  ) ),
		  Prb::_Array( array( $body ) )
		) );
	}
	
	// TODO: Document!
	# NOTE:
	#   We check via File::size? whether this file provides size info
	#   via stat (e.g. /proc files often don't), otherwise we have to
	#   figure it out by reading the whole file into memory. And while
	#   we're at it we also use this as body then.
	public function serving()
	{
		if ( $size = filesize( $this->path->raw() ) )
			$body = $this;
		else
		{
			$body = Prb::_Array( array(
			  Prb_IO::withFile( $this->path, Prb_IO_File::NO_CREATE_READ )->read()
			) );
			$size = Prack_Utils::singleton()->bytesize( $body->first() );
		}
		
		$pathinfo  = pathinfo( $this->path->raw() );
		$extension = isset( $pathinfo[ 'extension' ] )
		  ? '.'.$pathinfo[ 'extension' ]
		  : null;
		
		return Prb::_Array( array(
		  Prb::_Numeric( 200 ),
		  Prb::_Hash( array(
		    'Last-Modified'  => Prb::_Time( filemtime( $this->path->raw() ) )->httpdate(),
		    'Content-Type'   => Prack_Mime::mimeType( $extension, Prb::_String( 'text/plain' ) ),
		    'Content-Length' => Prb::_Numeric( $size )->toS()
		  ) ),
		  $body
		) );
	}
	
		// TODO: Document!
	public function notFound()
	{
		$body = Prb::_String( "File not found: {$this->path_info->raw()}\n" );
		return Prb::_Array( array(
		  Prb::_Numeric( 404 ),
		  Prb::_Hash( array(
		    'Content-Type'   => Prb::_String( 'text/plain' ),
		    'Content-Length' => Prb::_Numeric( $body->size() )->toS(),
		    'X-Cascade'      => Prb::_String( 'pass' )
		  ) ),
		  Prb::_Array( array( $body ) )
		) );
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
