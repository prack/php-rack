<?php

class Prack_Utils_IO_File extends Prack_Utils_IO
  implements Prack_Interface_ReadableStreamlike, Prack_Interface_WritableStreamlike
{
	// Binary composite values which determine mode:
	const READ       =   1;
	const WRITE      =   2;
	const APPEND     =  16;
	const CREATE     =  32;
	const TRUNCATE   = 256;
	const NOISY      = 512;
	const FORCE_TEXT = 1024; // For text-translation mode in windows. Default is binary.
	
	// All possible file modes:
	const NO_CREATE_READ           =   1;
	const NO_CREATE_READWRITE      =   3;
	const TRUNCATE_AND_WRITE       = 290;
	const TRUNCATE_AND_READWRITE   = 291;
	const APPEND_AS_WRITE          =  50;
	const APPEND_AS_READWRITE      =  51;
	const WRITE_IF_NONEXISTANT     = 514;
	const READWRITE_IF_NONEXISTANT = 515;
	const WRITE_NO_TRUNCATE        =  18;
	const READWRITE_NO_TRUNCATE    =  19;
	
	private $path;
	private $is_binmode;
	
	// TODO: Document!
	static function modeFor( $bitmask )
	{
		static $modes = null;
		
		if ( is_null( $modes ) )
		{
			$modes = array(
			    1 => 'r' , // NO_CREATE_READ
			    3 => 'r+', // NO_CREATE_READWRITE
			  290 => 'w' , // TRUNCATE_AND_WRITE
			  291 => 'w+', // TRUNCATE_AND_READWRITE
			   50 => 'a' , // APPEND_AS_WRITE
			   51 => 'a+', // APPEND_AS_READWRITE
			  514 => 'x' , // WRITE_IF_NONEXISTANT
			  515 => 'x+', // READWRITE_IF_NONEXISTANT
			   18 => 'c' , // WRITE_NO_TRUNCATE
			   19 => 'c+'  // READWRITE_NO_TRUNCATE
			);
		}
		
		return isset( $modes[ $bitmask ] ) ? $modes[ $bitmask ] : null;
	}
	
	// TODO: Document!
	function __construct( $path, $bitmask )
	{
		$this->path        = $path;
		$this->is_binmode  = !(bool)( $bitmask & self::FORCE_TEXT );
		
		// Unset FORCE_TEXT bit if present.
		$bitmask = $bitmask ^ ( $bitmask & self::FORCE_TEXT );
		
		if ( !file_exists( $this->path ) && !(bool)( $bitmask & self::CREATE ) )
			throw new Prack_Error_System_ErrnoENOENT( "file not found for no-create open at {$this->path}" );
		
		$mode  = self::modeFor( $bitmask );
		$mode .= $this->is_binmode ? 'b' : 't';
		
		parent::__construct( fopen( $path, $mode ), true );
		
		$this->is_readable = (bool)( $bitmask & self::READ  );
		$this->is_writable = (bool)( $bitmask & self::WRITE );
	}
	
	// TODO: Document!
	public function chmod( $permissions )
	{
		chmod( $this->path, $permissions );
	}
	
	// TODO: Document!
	public function unlink()
	{
		unlink( $this->path );
	}
	
	public function delete() { return $this->unlink(); }
	
	// TODO: Document!
	public function getPath()
	{
		return $this->path;
	}
	
	// TODO: Document!
	public function isBinMode()
	{
		return $this->is_binmode;
	}
}