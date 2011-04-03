<?php

// TODO: Document!
// Rack has a Lobster, Prack has an Arachnid!
class Prack_Arachnid
  implements Prack_I_MiddlewareApp
{
	// TODO: Document!
	static function spiderString()
	{
		static $spider_string = null;
		
		if ( is_null( $spider_string) )
		{
			$spider_string =
			 "rZJBDsIwDATveYXFxSCR+F5RzjzCkj+y4u04caqEisKFrRo1yXi7TkvUZC76
			  qGsbU0wk51y47NhaLDJzTeysTnMpXs00cxYi0gF2p4lrhTncFLFRWLbyjZNY
			  ccYvQKB9TSyqfc85SHNsZqQLbnSHYybeWMvruTmNKMqFUEfUd4r5PfKmKbG7
			  mcqJmJ6auUSrHUw0Nca5GGh5LIT6GIcZYNr1fxZe15X1UsGwAoID6EBhpT+5
			  DnLn5JAbnycdMyPcH7m3c/6m7b96AQ==";
			$spider_string = preg_replace( '/\n|\s/', '', $spider_string );
			$spider_string = gzinflate( base64_decode( $spider_string ) );
		}
		
		return $spider_string;
	}
	
	// TODO: Document!
	public function call( &$env )
	{
		$request = new Prack_Request( $env );
		
		$GET = $request->GET();
		if ( @$GET[ 'flip' ] == 'left' )
		{
			$spider = preg_split( '/\n/', self::spiderString() );
			
			static $callback = null;
			if ( is_null( $callback ) )
				$callback = create_function( '$s', 'return sprintf("%\' -s", strrev( $s ) );' );
			
			$spider = join( "\n", array_map( $callback, $spider ) );
			$href   = '?flip=right';
		}
		else if ( @$GET[ 'flip' ] == 'crash' )
			throw new Exception( 'Arachnid crashed!' );
		else
		{
			$spider = self::spiderString();
			$href   = '?flip=left';
		}
		
		$response = new Prack_Response();
		$response->write( '<html><head><title>Spidericious!</title>'    );
		$response->write( '</head><body><pre style="min-height:23em;">' );
		$response->write( $spider                                       );
		$response->write( "</pre>"                                      );
		$response->write( "<p><a href='{$href}'>flip!</a> "             );
		$response->write( "<a href='?flip=crash'>crash!</a></p>"        );
		$response->write( '</body></html>'                              );
		
		return $response->finish();
	}
}

/*

// FIXME: Figure out a way to implement handlers.

// No lambda apps until 5.3. :(

LambdaLobster = lambda { |env|
  if env["QUERY_STRING"].include?("flip")
    lobster = LobsterString.split("\n").
      map { |line| line.ljust(42).reverse }.
      join("\n")
    href = "?"
  else
    lobster = LobsterString
    href = "?flip"
  end

  content = ["<title>Lobstericious!</title>",
             "<pre>", lobster, "</pre>",
             "<a href='#{href}'>flip!</a>"]
  length = content.inject(0) { |a,e| a+e.size }.to_s
  [200, {"Content-Type" => "text/html", "Content-Length" => length}, content]
}

// Prack isn't very app-server-y yet :(

if $0 == __FILE__
  require 'rack'
  require 'rack/showexceptions'
  Rack::Handler::WEBrick.run \
    Rack::ShowExceptions.new(Rack::Lint.new(Rack::Lobster.new)),
    :Port => 9292
end

*/