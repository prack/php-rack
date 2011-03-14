<?php

// TODO: Document!
class Prack_Mime
{
	// TODO: Document!
	static function mimeType( $extension, $fallback = null )
	{
		if ( is_null( $fallback ) )
			$fallback = Prb::_String( 'application/octet-stream' );
		
		return self::mimeTypes()->fetch( $extension, $fallback );
	}
	
	// TODO: Document!
	public function mimeTypes()
	{
		static $mimeTypes = null;
		
		if ( is_null( $mimeTypes ) )
		{
			$mimeTypes = Prb::_Array( array(
			  ".3gp"     => Prb::_String( "video/3gpp"                                      ),
			  ".a"       => Prb::_String( "application/octet-stream"                        ),
			  ".ai"      => Prb::_String( "application/postscript"                          ),
			  ".aif"     => Prb::_String( "audio/x-aiff"                                    ),
			  ".aiff"    => Prb::_String( "audio/x-aiff"                                    ),
			  ".asc"     => Prb::_String( "application/pgp-signature"                       ),
			  ".asf"     => Prb::_String( "video/x-ms-asf"                                  ),
			  ".asm"     => Prb::_String( "text/x-asm"                                      ),
			  ".asx"     => Prb::_String( "video/x-ms-asf"                                  ),
			  ".atom"    => Prb::_String( "application/atom+xml"                            ),
			  ".au"      => Prb::_String( "audio/basic"                                     ),
			  ".avi"     => Prb::_String( "video/x-msvideo"                                 ),
			  ".bat"     => Prb::_String( "application/x-msdownload"                        ),
			  ".bin"     => Prb::_String( "application/octet-stream"                        ),
			  ".bmp"     => Prb::_String( "image/bmp"                                       ),
			  ".bz2"     => Prb::_String( "application/x-bzip2"                             ),
			  ".c"       => Prb::_String( "text/x-c"                                        ),
			  ".cab"     => Prb::_String( "application/vnd.ms-cab-compressed"               ),
			  ".cc"      => Prb::_String( "text/x-c"                                        ),
			  ".chm"     => Prb::_String( "application/vnd.ms-htmlhelp"                     ),
			  ".class"   => Prb::_String( "application/octet-stream"                        ),
			  ".com"     => Prb::_String( "application/x-msdownload"                        ),
			  ".conf"    => Prb::_String( "text/plain"                                      ),
			  ".cpp"     => Prb::_String( "text/x-c"                                        ),
			  ".crt"     => Prb::_String( "application/x-x509-ca-cert"                      ),
			  ".css"     => Prb::_String( "text/css"                                        ),
			  ".csv"     => Prb::_String( "text/csv"                                        ),
			  ".cxx"     => Prb::_String( "text/x-c"                                        ),
			  ".deb"     => Prb::_String( "application/x-debian-package"                    ),
			  ".der"     => Prb::_String( "application/x-x509-ca-cert"                      ),
			  ".diff"    => Prb::_String( "text/x-diff"                                     ),
			  ".djv"     => Prb::_String( "image/vnd.djvu"                                  ),
			  ".djvu"    => Prb::_String( "image/vnd.djvu"                                  ),
			  ".dll"     => Prb::_String( "application/x-msdownload"                        ),
			  ".dmg"     => Prb::_String( "application/octet-stream"                        ),
			  ".doc"     => Prb::_String( "application/msword"                              ),
			  ".dot"     => Prb::_String( "application/msword"                              ),
			  ".dtd"     => Prb::_String( "application/xml-dtd"                             ),
			  ".dvi"     => Prb::_String( "application/x-dvi"                               ),
			  ".ear"     => Prb::_String( "application/java-archive"                        ),
			  ".eml"     => Prb::_String( "message/rfc822"                                  ),
			  ".eps"     => Prb::_String( "application/postscript"                          ),
			  ".exe"     => Prb::_String( "application/x-msdownload"                        ),
			  ".f"       => Prb::_String( "text/x-fortran"                                  ),
			  ".f77"     => Prb::_String( "text/x-fortran"                                  ),
			  ".f90"     => Prb::_String( "text/x-fortran"                                  ),
			  ".flv"     => Prb::_String( "video/x-flv"                                     ),
			  ".for"     => Prb::_String( "text/x-fortran"                                  ),
			  ".gem"     => Prb::_String( "application/octet-stream"                        ),
			  ".gemspec" => Prb::_String( "text/x-script.ruby"                              ),
			  ".gif"     => Prb::_String( "image/gif"                                       ),
			  ".gz"      => Prb::_String( "application/x-gzip"                              ),
			  ".h"       => Prb::_String( "text/x-c"                                        ),
			  ".htc"     => Prb::_String( "text/x-component"                                ),
			  ".hh"      => Prb::_String( "text/x-c"                                        ),
			  ".htm"     => Prb::_String( "text/html"                                       ),
			  ".html"    => Prb::_String( "text/html"                                       ),
			  ".ico"     => Prb::_String( "image/vnd.microsoft.icon"                        ),
			  ".ics"     => Prb::_String( "text/calendar"                                   ),
			  ".ifb"     => Prb::_String( "text/calendar"                                   ),
			  ".iso"     => Prb::_String( "application/octet-stream"                        ),
			  ".jar"     => Prb::_String( "application/java-archive"                        ),
			  ".java"    => Prb::_String( "text/x-java-source"                              ),
			  ".jnlp"    => Prb::_String( "application/x-java-jnlp-file"                    ),
			  ".jpeg"    => Prb::_String( "image/jpeg"                                      ),
			  ".jpg"     => Prb::_String( "image/jpeg"                                      ),
			  ".js"      => Prb::_String( "application/javascript"                          ),
			  ".json"    => Prb::_String( "application/json"                                ),
			  ".log"     => Prb::_String( "text/plain"                                      ),
			  ".m3u"     => Prb::_String( "audio/x-mpegurl"                                 ),
			  ".m4v"     => Prb::_String( "video/mp4"                                       ),
			  ".man"     => Prb::_String( "text/troff"                                      ),
			  ".manifest"=> Prb::_String( "text/cache-manifest"                             ),
			  ".mathml"  => Prb::_String( "application/mathml+xml"                          ),
			  ".mbox"    => Prb::_String( "application/mbox"                                ),
			  ".mdoc"    => Prb::_String( "text/troff"                                      ),
			  ".me"      => Prb::_String( "text/troff"                                      ),
			  ".mid"     => Prb::_String( "audio/midi"                                      ),
			  ".midi"    => Prb::_String( "audio/midi"                                      ),
			  ".mime"    => Prb::_String( "message/rfc822"                                  ),
			  ".mml"     => Prb::_String( "application/mathml+xml"                          ),
			  ".mng"     => Prb::_String( "video/x-mng"                                     ),
			  ".mov"     => Prb::_String( "video/quicktime"                                 ),
			  ".mp3"     => Prb::_String( "audio/mpeg"                                      ),
			  ".mp4"     => Prb::_String( "video/mp4"                                       ),
			  ".mp4v"    => Prb::_String( "video/mp4"                                       ),
			  ".mpeg"    => Prb::_String( "video/mpeg"                                      ),
			  ".mpg"     => Prb::_String( "video/mpeg"                                      ),
			  ".ms"      => Prb::_String( "text/troff"                                      ),
			  ".msi"     => Prb::_String( "application/x-msdownload"                        ),
			  ".odp"     => Prb::_String( "application/vnd.oasis.opendocument.presentation" ),
			  ".ods"     => Prb::_String( "application/vnd.oasis.opendocument.spreadsheet"  ),
			  ".odt"     => Prb::_String( "application/vnd.oasis.opendocument.text"         ),
			  ".ogg"     => Prb::_String( "application/ogg"                                 ),
			  ".ogv"     => Prb::_String( "video/ogg"                                       ),
			  ".p"       => Prb::_String( "text/x-pascal"                                   ),
			  ".pas"     => Prb::_String( "text/x-pascal"                                   ),
			  ".pbm"     => Prb::_String( "image/x-portable-bitmap"                         ),
			  ".pdf"     => Prb::_String( "application/pdf"                                 ),
			  ".pem"     => Prb::_String( "application/x-x509-ca-cert"                      ),
			  ".pgm"     => Prb::_String( "image/x-portable-graymap"                        ),
			  ".pgp"     => Prb::_String( "application/pgp-encrypted"                       ),
			  ".pkg"     => Prb::_String( "application/octet-stream"                        ),
			  ".pl"      => Prb::_String( "text/x-script.perl"                              ),
			  ".pm"      => Prb::_String( "text/x-script.perl-module"                       ),
			  ".png"     => Prb::_String( "image/png"                                       ),
			  ".pnm"     => Prb::_String( "image/x-portable-anymap"                         ),
			  ".ppm"     => Prb::_String( "image/x-portable-pixmap"                         ),
			  ".pps"     => Prb::_String( "application/vnd.ms-powerpoint"                   ),
			  ".ppt"     => Prb::_String( "application/vnd.ms-powerpoint"                   ),
			  ".ps"      => Prb::_String( "application/postscript"                          ),
			  ".psd"     => Prb::_String( "image/vnd.adobe.photoshop"                       ),
			  ".py"      => Prb::_String( "text/x-script.python"                            ),
			  ".qt"      => Prb::_String( "video/quicktime"                                 ),
			  ".ra"      => Prb::_String( "audio/x-pn-realaudio"                            ),
			  ".rake"    => Prb::_String( "text/x-script.ruby"                              ),
			  ".ram"     => Prb::_String( "audio/x-pn-realaudio"                            ),
			  ".rar"     => Prb::_String( "application/x-rar-compressed"                    ),
			  ".rb"      => Prb::_String( "text/x-script.ruby"                              ),
			  ".rdf"     => Prb::_String( "application/rdf+xml"                             ),
			  ".roff"    => Prb::_String( "text/troff"                                      ),
			  ".rpm"     => Prb::_String( "application/x-redhat-package-manager"            ),
			  ".rss"     => Prb::_String( "application/rss+xml"                             ),
			  ".rtf"     => Prb::_String( "application/rtf"                                 ),
			  ".ru"      => Prb::_String( "text/x-script.ruby"                              ),
			  ".s"       => Prb::_String( "text/x-asm"                                      ),
			  ".sgm"     => Prb::_String( "text/sgml"                                       ),
			  ".sgml"    => Prb::_String( "text/sgml"                                       ),
			  ".sh"      => Prb::_String( "application/x-sh"                                ),
			  ".sig"     => Prb::_String( "application/pgp-signature"                       ),
			  ".snd"     => Prb::_String( "audio/basic"                                     ),
			  ".so"      => Prb::_String( "application/octet-stream"                        ),
			  ".svg"     => Prb::_String( "image/svg+xml"                                   ),
			  ".svgz"    => Prb::_String( "image/svg+xml"                                   ),
			  ".swf"     => Prb::_String( "application/x-shockwave-flash"                   ),
			  ".t"       => Prb::_String( "text/troff"                                      ),
			  ".tar"     => Prb::_String( "application/x-tar"                               ),
			  ".tbz"     => Prb::_String( "application/x-bzip-compressed-tar"               ),
			  ".tcl"     => Prb::_String( "application/x-tcl"                               ),
			  ".tex"     => Prb::_String( "application/x-tex"                               ),
			  ".texi"    => Prb::_String( "application/x-texinfo"                           ),
			  ".texinfo" => Prb::_String( "application/x-texinfo"                           ),
			  ".text"    => Prb::_String( "text/plain"                                      ),
			  ".tif"     => Prb::_String( "image/tiff"                                      ),
			  ".tiff"    => Prb::_String( "image/tiff"                                      ),
			  ".torrent" => Prb::_String( "application/x-bittorrent"                        ),
			  ".tr"      => Prb::_String( "text/troff"                                      ),
			  ".txt"     => Prb::_String( "text/plain"                                      ),
			  ".vcf"     => Prb::_String( "text/x-vcard"                                    ),
			  ".vcs"     => Prb::_String( "text/x-vcalendar"                                ),
			  ".vrml"    => Prb::_String( "model/vrml"                                      ),
			  ".war"     => Prb::_String( "application/java-archive"                        ),
			  ".wav"     => Prb::_String( "audio/x-wav"                                     ),
			  ".webm"    => Prb::_String( "video/webm"                                      ),
			  ".wma"     => Prb::_String( "audio/x-ms-wma"                                  ),
			  ".wmv"     => Prb::_String( "video/x-ms-wmv"                                  ),
			  ".wmx"     => Prb::_String( "video/x-ms-wmx"                                  ),
			  ".wrl"     => Prb::_String( "model/vrml"                                      ),
			  ".wsdl"    => Prb::_String( "application/wsdl+xml"                            ),
			  ".xbm"     => Prb::_String( "image/x-xbitmap"                                 ),
			  ".xhtml"   => Prb::_String( "application/xhtml+xml"                           ),
			  ".xls"     => Prb::_String( "application/vnd.ms-excel"                        ),
			  ".xml"     => Prb::_String( "application/xml"                                 ),
			  ".xpm"     => Prb::_String( "image/x-xpixmap"                                 ),
			  ".xsl"     => Prb::_String( "application/xml"                                 ),
			  ".xslt"    => Prb::_String( "application/xslt+xml"                            ),
			  ".yaml"    => Prb::_String( "text/yaml"                                       ),
			  ".yml"     => Prb::_String( "text/yaml"                                       ),
			  ".zip"     => Prb::_String( "application/zip"                                 )
			) );
		}
		
		return $mimeTypes;
	}
}
