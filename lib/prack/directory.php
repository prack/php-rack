<?php

// TODO: Document!
# Rack::Directory serves entries below the +root+ given, according to the
# path info of the Rack request. If a directory is found, the file's contents
# will be presented in an html based index. If a file is found, the env will
# be passed to the specified +app+.
#
# If +app+ is not specified, a Rack::File of the same +root+ will be used.
class Prack_Directory
  implements Prack_I_MiddlewareApp, Prb_I_Enumerable
{
	private $files;
	private $root;
	private $path;
	private $path_info;
	private $script_info;
	
	// TODO: Document!
	static function dirFile()
	{
		$dir_file = null;
		
		if ( is_null( $dir_file ) )
			$dir_file = "<tr><td class='name'><a href='%s'>%s</a></td><td class='size'>%s</td><td class='type'>%s</td><td class='mtime'>%s</td></tr>";
		
		return $dir_file;
	}
	
	// TODO: Document!
	static function dirPage()
	{
		$dir_page = null;
		
		if ( is_null( $dir_page ) )
			$dir_page = <<<PAGE
<html><head>
  <title>%s</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <style type='text/css'>
body { padding: 0; margin: 0 }
h1 { padding-left: 0.5em; margin: 0.5em auto; }
table { width:100%%; padding: 1em; }
.name { text-align:left; }
.size, .mtime { text-align:right; }
.size { padding-right: 2em; }
.type { width:11em; }
.mtime { width:15em; }
  </style>
</head><body>
<h1>%s</h1>
<hr />
<table>
  <tr>
    <th class='name'>Name</th>
    <th class='size'>Size</th>
    <th class='type'>Type</th>
    <th class='mtime'>Last Modified</th>
  </tr>
%s
</table>
<hr />
</body></html>
PAGE;
		
		return $dir_page;
	}
	
	// TODO: Document!
	static function filesizeFormat()
	{
		$filesize_format = null;
		
		if ( is_null( $filesize_format ) )
			$filesize_format = array(
			  array( '%.1fT', 1 << 40 ),
			  array( '%.1fG', 1 << 30 ),
			  array( '%.1fM', 1 << 20 ),
			  array( '%.1fK', 1 << 10 )
			);
		
		return $filesize_format;
	}
	
	// TODO: Document!
	static function with( $root, $middleware_app = null)
	{
		return new Prack_Directory( $root, $middleware_app );
	}
	
	// TODO: Document!
	function __construct( $root, $middleware_app = null )
	{
		$this->root           = realpath( $root );
		$this->middleware_app = $middleware_app ? $middleware_app : Prack_File::with( $this->root );
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
		$this->env         = &$env;
		$this->script_name = $env[ 'SCRIPT_NAME' ];
		$this->path_info   = Prack_Utils::singleton()->unescape( $env[ 'PATH_INFO' ] );
		
		if ( $forbidden = $this->checkForbidden() )
			return $forbidden;
		
		$joined     = join( DIRECTORY_SEPARATOR, array( $this->root, $this->path_info ) );
		$ds         = preg_quote( DIRECTORY_SEPARATOR, '/' );
		$this->path = preg_replace( "/{$ds}+/", DIRECTORY_SEPARATOR, $joined );
		
		return $this->listPath();
	}
	
	// TODO: Document!
	public function checkForbidden()
	{
		// return unless @path_info.include? '..'
		if ( !( strpos( $this->path_info, '..' ) !== false ) )
			return null;
		
		$body = "Forbidden\n";
		$size = Prack_Utils::singleton()->bytesize( $body );
		
		return array(
		  403,
		  array( 'Content-Type' => 'text/plain', 'Content-Length' => (string)$size, 'X-Cascade' => 'pass' ),
		  array( $body )
		);
	}
	
	// TODO: Document!
	public function listDirectory()
	{
		$this->files = array( array( '../', 'Parent Directory', '', '', '' ) );
		$glob        = glob( $this->path.'*' );
		
		sort( $glob );
		
		foreach( $glob as $node )
		{
			$stat = stat( $node );
			if ( is_null( $stat ) )
				continue;
			
			$pathinfo = pathinfo( $node );
			$basename =     (string)@$pathinfo[ 'basename'  ];
			$ext      = '.'.(string)@$pathinfo[ 'extension' ];
			
			$joined   = join( DIRECTORY_SEPARATOR, array( $this->script_name, $this->path_info, $basename ) );
			$ds       = preg_quote( DIRECTORY_SEPARATOR, '/' );
			$url      = preg_replace( "/{$ds}+/", DIRECTORY_SEPARATOR, $joined );
			
			$size     = $stat[ 'size' ];
			$type     = ( ( $stat[ 'mode' ] & 0170000) == 040000 ) ? 'directory' : Prack_Mime::mimeType( $ext );
			$size     = ( $type == 'directory' ) ? '-' : $this->formatFilesize( $size );
			$mtime    = http_date( $stat[ 'mtime' ] );
			
			if ( $type == 'directory' )
			{
				$basename .= '/';
				$url      .= '/';
			}
			
			array_push( $this->files, array( $url, $basename, $size, $type, $mtime ) );
		}
		
		return array( 200, array( 'Content-Type' => 'text/html; charset=utf-8' ), $this );
	}
	
	// TODO: Document!
	public function listPath()
	{
		try
		{
			if ( is_readable( $this->path ) )
			{
				if ( is_file( $this->path ) )
					return $this->middleware_app->call( $this->env );
				if ( is_dir( $this->path ) )
				{
					$this->path = rtrim( $this->path, DIRECTORY_SEPARATOR ).DIRECTORY_SEPARATOR;
					return $this->listDirectory();
				}
			}
		
			throw new Prb_Exception_System_ErrnoENOENT( 'No such file or directory' );
		}
		catch ( Prb_Exception_System_ErrnoENOENT $e )
		{
			return $this->entityNotFound();
		}
	}
	
	// TODO: Document!
	public function entityNotFound()
	{
		$body = "Entity not found: {$this->path_info}\n";
		$size = Prack_Utils::singleton()->bytesize( $body );
		return array(
		  404,
		  array( 'Content-Type' => 'text/plain', 'Content-Length' => (string)$size, 'X-Cascade' => 'pass '),
		  array( $body )
		);
	}
	
	// TODO: Document!
	public function each( $callback )
	{
		$quoted    = preg_quote( $this->root, '/' );
		$show_path = preg_replace( "/^{$quoted}/", '', $this->path, 1 );
		
		static $format_callback = null;
		if ( is_null( $format_callback ) )
			$format_callback = create_function( '$i', 'return vsprintf( Prack_Directory::dirFile(), $i );' );
		
		$files = join( "\n", array_map( $format_callback, $this->files ) );
		$page  = sprintf( self::dirPage(), $show_path, $show_path, $files );
		
		foreach( preg_split( "/\n/", $page) as $line )
			call_user_func( $callback, $line );
	}
	
	// TODO: Document!
	public function formatFilesize( $int )
	{
		foreach( self::filesizeFormat() as $format_size )
			if ( $int >= $format_size[ 1 ] )
				return sprintf( $format_size[ 0 ], (float)$int / $format_size[ 1 ] );
		
		return (string)$int.'B';
	}

	// TODO: Document!
	public function getFiles()
	{
		return $this->files;
	}
	
	// TODO: Document!
	public function getRoot()
	{
		return $this->root;
	}
	
	// TODO: Document!
	public function setRoot( $root )
	{
		$this->root = $root;
	}
	
	// TODO: Document!
	public function getPath()
	{
		return $this->path;
	}
	
	// TODO: Document!
	public function setPath( $path)
	{
		$this->path = $path;
	}
}