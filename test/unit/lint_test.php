<?php

// TODO: Document!
class Prack_LintTest_WeirdIO
  implements Prack_Interface_ReadableStreamlike
{
	public function gets()                                 { return 42; }
	public function read( $length = null, $buffer = null ) { return 23; }
	
	public function each( $callback )
	{
		call_user_func( $callback, 23 );
		call_user_func( $callback, 42 );
	}
	
	public function rewind()
	{
		throw new Prack_Error_System_ErrnoESPIPE();
	}
}

// TODO: Document!
class Prack_LintTest_EOFWeirdIO
  implements Prack_Interface_ReadableStreamlike
{
	public function gets()                                 { return null; }
	public function read( $length = null, $buffer = null ) { return null; }
	public function each( $callback )                      { /* noop, isn't called */ }
	public function rewind()                               { /* noop, isn't called */ }
}

// TODO: Document!
class Prack_LintTest extends PHPUnit_Framework_TestCase 
{
	// TODO: Document!
	static function noop( $item )
	{
		return;
	}
	
	// TODO: Document!
	static function env()
	{
		$args = func_get_args();
		array_unshift( $args, Prack::_String( '/' ) );
		return call_user_func_array( array( 'Prack_Mock_Request', 'envFor' ), $args );
	}
	
	/**
	 * It should pass valid request
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_pass_valid_request()
	{
		Prack_Lint::with(
			new TestEcho()
		)->call( self::env() );
	} // It should pass valid request
	
	/**
	 * It should notice fatal errors
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_notice_fatal_errors()
	{
		try
		{
			Prack_Lint::with(
			  new TestEcho()
			)->call( null );
		} catch ( Exception $e ) { }
		
		if ( isset( $e ) )
		{
			$this->assertRegExp( '/No env given/', $e->getMessage() );
			return;
		}
		
		$this->fail( 'Expected exception on null $env.' );
	} // It should notice fatal errors
	
	/**
	 * It should notice environment errors
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_notice_environment_errors()
	{
		$lint = Prack_Lint::with( new TestEcho() );
		
		try
		{
			$lint->call( 5 );
		} catch ( Prack_Error_Lint $e1 ) { }
		
		if ( isset( $e1 ) )
			$this->assertRegExp( '/not a Prack_Wrapper_Hash/', $e1->getMessage() );
		else
		{
			$this->fail( 'Expected exception on non-array $env.' );
			return;
		}
		
		try
		{
			$env = self::env();
			$env->delete( 'REQUEST_METHOD' );
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e2 ) { }
		
		if ( isset( $e2 ) )
			$this->assertRegExp( '/missing required key REQUEST_METHOD/', $e2->getMessage() );
		else
		{
			$this->fail( 'Expected exception on $env when missing key REQUEST_METHOD.' );
			return;
		}
		
		try
		{
			$env = self::env();
			$env->delete( 'SERVER_NAME' );
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e3 ) { }
		
		if ( isset( $e3 ) )
			$this->assertRegExp( '/missing required key SERVER_NAME/', $e3->getMessage() );
		else
		{
			$this->fail( 'Expected exception on $env when missing key SERVER_NAME.' );
			return;
		}
		
		try
		{
			$env = self::env(
			  Prack::_Hash( array(
			    'HTTP_CONTENT_TYPE' => Prack::_String( 'text/plain' )
			  ) )
			);
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e4 ) { }
		
		if ( isset( $e4 ) )
			$this->assertRegExp( '/contains HTTP_CONTENT_TYPE/', $e4->getMessage() );
		else
		{
			$this->fail( 'Expected exception on $env with key HTTP_CONTENT_TYPE.' );
			return;
		}
		
		try
		{
			$env = self::env(
			  Prack::_Hash( array(
			    'HTTP_CONTENT_LENGTH' => Prack::_String( '42' )
			  ) )
			);
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e5 ) { }
		
		if ( isset( $e5 ) )
			$this->assertRegExp( '/contains HTTP_CONTENT_LENGTH/', $e5->getMessage() );
		else
		{
			$this->fail( 'Expected exception on $env with key HTTP_CONTENT_LENGTH.' );
			return;
		}
		
		try
		{
			$env = self::env(
			  Prack::_Hash( array(
			    'FOO' => 'bar' // Not a wrapped string.
			  ) )
			);
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e6 ) { }
		
		if ( isset( $e6 ) )
			$this->assertRegExp( '/non-string value/', $e6->getMessage() );
		else
		{
			$this->fail( 'Expected exception on $env with non-string value.' );
			return;
		}
		
		try
		{
			$env = self::env(
			  Prack::_Hash( array(
			    'rack.version' => "0.2"
			  ) )
			);
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e7 ) { }
		
		if ( isset( $e7 ) )
			$this->assertRegExp( '/must be a Prack_Wrapper_Array/', $e7->getMessage() );
		else
		{
			$this->fail( 'Expected exception on $env where rack.version is not an array.' );
			return;
		}
		
		try
		{
			$env = self::env(
			  Prack::_Hash( array(
			    'rack.url_scheme' => Prack::_String( 'gopher' )
			  ) )
			);
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e8 ) { }
		
		if ( isset( $e8 ) )
			$this->assertRegExp( '/url_scheme unknown/', $e8->getMessage() );
		else
		{
			$this->fail( 'Expected exception on $env where rack.url_scheme is not http or https.' );
			return;
		}
		
		// FIXME: Implement sessions.
		/*
		try
		{
			$env  = self::env( array( 'rack.session' => new stdClass() ) );
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e9 ) { }
		
		if ( isset( $e9 ) )
			$this->assertRegExp( '/must conform to Prack_Interface_Session/', $e9->getMessage() );
		else
		{
			$this->fail( 'Expected exception on $env where rack.session does not conform to Prack_Interface_Session.' );
			return;
		}
		*/
		
		// FIXME: Implement logger.
		/*
		try
		{
			$env  = self::env( array( 'rack.logger' => new stdClass() ) );
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e10 ) { }
		
		if ( isset( $e10 ) )
			$this->assertRegExp( '/must conform to Prack_Interface_Logger/', $e10->getMessage() );
		else
		{
			$this->fail( 'Expected exception on $env where rack.logger does not conform to Prack_Interface_Logger.' );
			return;
		}
		*/
		
		try
		{
			$env  = self::env(
			  Prack::_Hash( array(
			    'REQUEST_METHOD' => Prack::_String( 'FUBAR?' )
			  ) )
			);
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e11 ) { }
		
		if ( isset( $e11 ) )
			$this->assertRegExp( '/REQUEST_METHOD/', $e11->getMessage() );
		else
		{
			$this->fail( 'Expected exception on $env where REQUEST_METHOD is invalid token.' );
			return;
		}
		
		try
		{
			$env  = self::env(
			  Prack::_Hash( array(
			    'SCRIPT_NAME' => Prack::_String( 'howdy' )
			  ) )
			);
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e12 ) { }
		
		if ( isset( $e12 ) )
			$this->assertRegExp( '/must start with/', $e12->getMessage() );
		else
		{
			$this->fail( 'Expected exception on $env where SCRIPT_NAME does not begin with /.' );
			return;
		}
		
		try
		{
			$env = self::env(
			  Prack::_Hash( array(
			    'PATH_INFO' => Prack::_String( '../foo' )
			  ) )
			);
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e13 ) { }
		
		if ( isset( $e13 ) )
			$this->assertRegExp( '/must start with/', $e13->getMessage() );
		else
		{
			$this->fail( 'Expected exception on $env where PATH_INFO does not begin with /.' );
			return;
		}
		
		try
		{
			$env = self::env(
			  Prack::_Hash( array(
			    'CONTENT_LENGTH' => Prack::_String( 'xcii' )
			  ) )
			);
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e14 ) { }
		
		if ( isset( $e14 ) )
			$this->assertRegExp( '/Invalid CONTENT_LENGTH/', $e14->getMessage() );
		else
		{
			$this->fail( 'Expected exception on $env where CONTENT_LENGTH does not contain digits only.' );
			return;
		}
		
		try
		{
			$env = self::env();
			$env->delete( 'PATH_INFO'   );
			$env->delete( 'SCRIPT_NAME' );
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e15 ) { }
		
		if ( isset( $e15 ) )
			$this->assertRegExp( '/One of .* must be set/', $e15->getMessage() );
		else
		{
			$this->fail( 'Expected exception on $env where neither PATH_INFO nor SCRIPT_NAME are set.' );
			return;
		}
		
		try
		{
			$env = self::env(
			  Prack::_Hash( array(
			    'SCRIPT_NAME' => Prack::_String( '/' )
			  ) )
			);
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e16 ) { }
		
		if ( isset( $e16 ) )
			$this->assertRegExp( "/cannot be .* make it ''/", $e16->getMessage() );
		else
		{
			$this->fail( 'Expected exception on $env where neither SCRIPT_NAME is a / (only).' );
			return;
		}
	} // It should notice environment errors
	
	/**
	 * It should notice input errors
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_notice_input_errors()
	{
		/*
			In Rack's Ruby implementation, this test checks for a number
			of features which are not implemented on our stream wrapper classes.
			To wit:
			  1) All Prack stream wrappers are inherently binary, so no 'binmode.'
				2) Prack's string and stream wrappers have no encoding. (yet)
				   Consequently, and in Ruby since the ASCII-8BIT encoding is used,
				   we're already in line with the goals of the Rack specification.
		*/
		
		try
		{
			$env = self::env(
			  Prack::_Hash( array(
			    'rack.input' => Prack::_Array()
			  ) )
			);
			Prack_Lint::with( new TestEcho() )->call( $env );
		} catch ( Prack_Error_Lint $e17 ) { }
		
		if ( isset( $e17 ) )
			$this->assertRegExp( "/not a readable streamlike/", $e17->getMessage() );
		else
		{
			$this->fail( 'Expected exception on $env rack.input if not a readable, stream-like object.' );
			return;
		}
	} // It should notice input errors
	
	/**
	 * It should notice error errors
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_notice_error_errors()
	{
		try
		{
			$env = self::env(
			  Prack::_Hash( array(
			    'rack.errors' => Prack::_String()
			  ) )
			);
			Prack_Lint::with( new TestEcho() )->call( $env );
		} catch ( Prack_Error_Lint $e18 ) { }
		
		if ( isset( $e18 ) )
			$this->assertRegExp( "/not a writable streamlike/", $e18->getMessage() );
		else
		{
			$this->fail( 'Expected exception on $env rack.errors if not a writable, stream-like object.' );
			return;
		}
	} // It should notice error errors
	
	/**
	 * It should notice status errors
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_notice_status_errors()
	{
		$env = self::env();
		
		try
		{
			$middleware_app = new TestEcho( 'cc' ); // 'cc' as status arg
			Prack_Lint::with( $middleware_app )->call( $env );
		} catch ( Prack_Error_Lint $e19 ) { }
		
		if ( isset( $e19 ) )
			$this->assertRegExp( "/must be >= 100 seen as integer/", $e19->getMessage() );
		else
		{
			$this->fail( 'Expected exception on returned status if not >= 100 and integer.' );
			return;
		}
		
		try
		{
			$middleware_app = new TestEcho( 42 ); // 42 as status arg
			Prack_Lint::with( $middleware_app )->call( $env );
		} catch ( Prack_Error_Lint $e20 ) { }
		
		if ( isset( $e20 ) )
			$this->assertRegExp( "/must be >= 100 seen as integer/", $e20->getMessage() );
		else
		{
			$this->fail( 'Expected exception on returned status if not >= 100 and integer.' );
			return;
		}
	} // It should notice status errors
	
	/**
	 * It should notice header errors
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_notice_header_errors()
	{
		$env = self::env();
		
		try
		{
			$middleware_app = new TestEcho( 200, new stdClass(), Prack::_Array() );
			Prack_Lint::with( $middleware_app )->call( $env );
		} catch ( Prack_Error_Lint $e21 ) { }
		
		if ( isset( $e21 ) )
			$this->assertEquals( "headers object should conform to Prack_Interface_Enumerable, but doesn't (got stdClass as headers)", 
			                     $e21->getMessage() );
		else
		{
			$this->fail( 'Expected exception on headers if header argument doesn\'t respond to method each.' );
			return;
		}
		
		try
		{
			$middleware_app = new TestEcho( 200, Prack::_Hash( array( 1 => false ) ), Prack::_Array() );
			Prack_Lint::with( $middleware_app )->call( $env );
		} catch ( Prack_Error_Lint $e22 ) { }
		
		if ( isset( $e22 ) )
			$this->assertEquals( "header key must be a string, was integer", 
			                     $e22->getMessage() );
		else
		{
			$this->fail( 'Expected exception on headers if header key is not a string.' );
			return;
		}
		
		try
		{
			$middleware_app = new TestEcho(
			  200,
			  Prack::_Hash( array( 'Status' => Prack::_String( '404' ) ) ),
			  Prack::_Array()
			);
			Prack_Lint::with( $middleware_app )->call( $env );
		} catch ( Prack_Error_Lint $e23 ) { }
		
		if ( isset( $e23 ) )
			$this->assertRegExp( '/must not contain Status/', $e23->getMessage() );
		else
		{
			$this->fail( 'Expected exception on headers if they contain Status.' );
			return;
		}
		
		try
		{
			$middleware_app = new TestEcho(
			  200,
			  Prack::_Hash( array( 'Content-Type:' => Prack::_String( 'text/plain' ) ) ),
			  Prack::_Array()
			);
			Prack_Lint::with( $middleware_app )->call( $env );
		} catch ( Prack_Error_Lint $e24 ) { }
		
		if ( isset( $e24 ) )
			$this->assertRegExp( '/must not contain :/', $e24->getMessage() );
		else
		{
			$this->fail( 'Expected exception on headers if they end in a colon or endline.' );
			return;
		}
		
		try
		{
			$middleware_app = new TestEcho(
			  200,
			  Prack::_Hash( array( 'Content-' => Prack::_String( 'text/plain' ) ) ),
			  Prack::_Array()
			);
			Prack_Lint::with( $middleware_app )->call( $env );
		} catch ( Prack_Error_Lint $e25 ) { }
		
		if ( isset( $e25 ) )
			$this->assertRegExp( '/must not end/', $e25->getMessage() );
		else
		{
			$this->fail( 'Expected exception on headers if they end in a dash or an underscore.' );
			return;
		}
		
		try
		{
			$middleware_app = new TestEcho(
			  200,
			  Prack::_Hash( array( '..%%quark%%..' => Prack::_String( 'text/plain' ) ) ),
			  Prack::_Array()
			);
			Prack_Lint::with( $middleware_app )->call( $env );
		} catch ( Prack_Error_Lint $e26 ) { }
		
		if ( isset( $e26 ) )
			$this->assertEquals( 'Invalid header name: ..%%quark%%..', $e26->getMessage() );
		else
		{
			$this->fail( 'Expected exception on headers if they end in a dash or an underscore.' );
			return;
		}
		
		try
		{
			$middleware_app = new TestEcho(
			  200,
			  Prack::_Hash( array( 'Foo' => new stdClass() ) ),
			  Prack::_Array()
			);
			Prack_Lint::with( $middleware_app )->call( $env );
		} catch ( Prack_Error_Lint $e27 ) { }
		
		if ( isset( $e27 ) )
			$this->assertEquals( "a header value must be a Prack_Wrapper_String, but the value of 'Foo' is a stdClass", $e27->getMessage() );
		else
		{
			$this->fail( 'Expected exception on headers if the value is not a string.' );
			return;
		}
		
		try
		{
			$middleware_app = new TestEcho(
			  200,
			  Prack::_Hash( array( 'Foo' => Prack::_Array() ) ),
			  Prack::_Array()
			);
			Prack_Lint::with( $middleware_app )->call( $env );
		} catch ( Prack_Error_Lint $e28 ) { }
		
		if ( isset( $e28 ) )
			$this->assertEquals( "a header value must be a Prack_Wrapper_String, but the value of 'Foo' is a Prack_Wrapper_Array", $e28->getMessage() );
		else
		{
			$this->fail( 'Expected exception on headers if the value is not a string.' );
			return;
		}
		
		try
		{
			$middleware_app = new TestEcho(
			  200,
			  Prack::_Hash( array( 'Foo' => Prack::_String( "text\000plain" ) ) ),
			  Prack::_Array()
			);
			Prack_Lint::with( $middleware_app )->call( $env );
		} catch ( Prack_Error_Lint $e29 ) { }
		
		if ( isset( $e29 ) )
			$this->assertRegExp( '/invalid header/', $e29->getMessage() );
		else
		{
			$this->fail( 'Expected exception on headers if the value contains disallowed characters.' );
			return;
		}
		
		// Implicit test:
		
		# line ends (010) should be allowed in header values.
		$ok_headers = Prack::_Hash( array(
		  'Foo-Bar'        => Prack::_String( "one\ntwo\nthree" ),
		  'Content-Length' => Prack::_String( '0' ),
		  'Content-Type'   => Prack::_String( 'text/plain' )
		) );
		Prack_Lint::with(
		  new TestEcho( 200, $ok_headers, Prack::_Array() )
		)->call( $env );
		
		// End implicit test.
	} // It should notice header errors
	
	/**
	 * It should notice content-type errors
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_notice_content_type_errors()
	{
		$env = self::env();
		
		try
		{
			$middleware_app = new TestEcho(
			  200,
			  Prack::_Hash( array( 'Content-length' => Prack::_String( '0' ) ) ),
			  Prack::_Array()
			);
			Prack_Lint::with( $middleware_app )->call( $env );
		} catch ( Prack_Error_Lint $e30 ) { }
		
		if ( isset( $e30 ) )
			$this->assertRegExp( '/No Content-Type/', $e30->getMessage() );
		else
		{
			$this->fail( 'Expected exception on content-length if there is no content-type.' );
			return;
		}
		
		foreach( array( 100, 101, 204, 304 ) as $no_body_status )
		{
			try
			{
				$middleware_app = new TestEcho(
				  $no_body_status, 
				  Prack::_Hash( array(
				    'Content-type'   => Prack::_String( 'text/plain' ),
				    'Content-length' => Prack::_String( '0' )
				  ) ),
				  Prack::_Array()
				);
				$lint = new Prack_Lint( $middleware_app );
				$lint->call( $env );
			} catch ( Prack_Error_Lint $e31 ) { }

			if ( isset( $e31 ) )
				$this->assertRegExp( '/Content-Type header found/', $e31->getMessage() );
			else
			{
				$this->fail( 'Expected exception on content-length if the status requires there be no such header.' );
				return;
			}
		}
	} // It should notice content-type errors
	
	/**
	 * It should notice content-length errors
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_notice_content_length_errors()
	{
		$env = self::env();
		
		foreach( array( 100, 101, 204, 304 ) as $no_body_status )
		{
			try
			{
				$middleware_app = new TestEcho(
				  $no_body_status,
				  Prack::_Hash( array( 'Content-length' => Prack::_String( '0' ) ) ),
				  Prack::_Array()
				);
				Prack_Lint::with( $middleware_app )->call( $env );
			} catch ( Prack_Error_Lint $e32 ) { }
			
			if ( isset( $e32 ) )
				$this->assertRegExp( '/Content-Length header found/', $e32->getMessage() );
			else
			{
				$this->fail( 'Expected exception on content-length if the status requires there be no such header.' );
				return;
			}
			
			try
			{
				$middleware_app = new TestEcho(
				  200, 
				  Prack::_Hash( array(
				    'Content-type'   => Prack::_String( 'text/plain' ),
				    'Content-length' => Prack::_String( '1' )
				  ) ),
				  Prack::_Array()
				);
				$lint = new Prack_Lint( $middleware_app );
				list( $status, $headers, $lint ) = $lint->call( $env );
				$lint->each( null );
			} catch ( Prack_Error_Lint $e33 ) { }
			
			if ( isset( $e33 ) )
				$this->assertRegExp( '/Content-Length header was 1, but should be 0/', $e33->getMessage() );
			else
			{
				$this->fail( 'Expected exception on content-length if its value should be 0.' );
				return;
			}
		}
	} // It should notice content-length errors
	
	/**
	 * It should notice body errors
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_notice_body_errors()
	{
		try
		{
			$middleware_app = new TestEcho(
			  200, 
			  Prack::_Hash( array(
			    'Content-type'   => Prack::_String( 'text/plain' ),
			    'Content-length' => Prack::_String( '3' )
			  ) ),
			  Prack::_Array( array( 1, 2, 3 ) )
			);
			
			$lint = new Prack_Lint( $middleware_app );
			list( $status, $headers, $lint ) = $lint->call( self::env() );
			$lint->each( null );
		} catch ( Prack_Error_Lint $e34 ) { }
		
		if ( isset( $e34 ) )
			$this->assertRegExp( '/yielded non-string/', $e34->getMessage() );
		else
		{
			$this->fail( 'Expected exception on when body yields non-strings.' );
			return;
		}
	} // It should notice body errors
	
	/**
	 * It should notice input handling errors
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_notice_input_handling_errors()
	{
		$env            = self::env();
		$middleware_app = new TestEcho(
		  201, 
		  Prack::_Hash( array(
		    'Content-type'   => Prack::_String( 'text/plain' ),
		    'Content-length' => Prack::_String( '0' )
		  ) ),
		  Prack::_Array()
		);
		$lint = new Prack_Lint( $middleware_app );
		
		try
		{
			$middleware_app->setEval( '$env->get( \'rack.input\' )->gets( "\r\n" );' );
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e35 ) { }
		
		if ( isset( $e35 ) )
			$this->assertRegExp( '/gets called with arguments/', $e35->getMessage() );
		else
		{
			$this->fail( 'Expected exception when gets called with arguments.' );
			return;
		}
		
		try
		{
			$middleware_app->setEval( '$env->get( \'rack.input\' )->read( 1, 2, 3 );' );
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e36 ) { }
		
		if ( isset( $e36 ) )
			$this->assertRegExp( '/read called with too many arguments/', $e36->getMessage() );
		else
		{
			$this->fail( 'Expected exception when read called with too many arguments.' );
			return;
		}
		
		try
		{
			$middleware_app->setEval( '$env->get( \'rack.input\' )->read( "foo" );' );
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e37 ) { }
		
		if ( isset( $e37 ) )
			$this->assertRegExp( '/read called with non-integer and non-null length/', $e37->getMessage() );
		else
		{
			$this->fail( 'Expected exception when read called with non-integer and non-null length.' );
			return;
		}
		
		try
		{
			$middleware_app->setEval( '$env->get( \'rack.input\' )->read( -1 );' );
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e38 ) { }
		
		if ( isset( $e38 ) )
			$this->assertRegExp( '/read called with a negative length/', $e38->getMessage() );
		else
		{
			$this->fail( 'Expected exception when read called with a negative length.' );
			return;
		}
		
		try
		{
			$middleware_app->setEval( '$env->get( \'rack.input\' )->read( null, null );' );
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e39 ) { }
		
		if ( isset( $e39 ) )
			$this->assertRegExp( '/read called with non-string buffer/', $e39->getMessage() );
		else
		{
			$this->fail( 'Expected exception when read called with non-string buffer (case 1).' );
			return;
		}
		
		try
		{
			$middleware_app->setEval( '$env->get( \'rack.input\' )->read( null, 1 );' );
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e40 ) { }
		
		if ( isset( $e40 ) )
			$this->assertRegExp( '/read called with non-string buffer/', $e40->getMessage() );
		else
		{
			$this->fail( 'Expected exception when read called with non-string buffer (case 2).' );
			return;
		}
		
		try
		{
			$middleware_app->setEval( '$env->get( \'rack.input\' )->rewind( 0 );' );
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e41 ) { }
		
		if ( isset( $e41 ) )
			$this->assertRegExp( '/rewind called with arguments/', $e41->getMessage() );
		else
		{
			$this->fail( 'Expected exception when rewind called with arguments.' );
			return;
		}
		
		try
		{
			$middleware_app->setEval( '$env->get( \'rack.input\' )->gets();' );
			$env = self::env( Prack::_Hash( array( 'rack.input' => new Prack_LintTest_WeirdIO() ) ) );
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e42 ) { }
		
		if ( isset( $e42 ) )
			$this->assertRegExp( "/rack.input method gets didn't return a Prack_Wrapper_String/", $e42->getMessage() );
		else
		{
			$this->fail( 'Expected exception when gets called on non-Prack_Wrapper_String-returning IO.' );
			return;
		}
		
		try
		{
			$middleware_app->setEval( '$env->get( \'rack.input\' )->each( array( \'Prack_LintTest\', \'noop\' ) );' );
			$env = self::env( Prack::_Hash( array( 'rack.input' => new Prack_LintTest_WeirdIO() ) ) );
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e43 ) { }
			
		if ( isset( $e43 ) )
			$this->assertRegExp( "/rack.input method each didn't yield a Prack_Wrapper_String/", $e43->getMessage() );
		else
		{
			$this->fail( 'Expected exception when gets called on IO which yields non-Prack_Wrapper_String values.' );
			return;
		}
			
		try
		{
			$middleware_app->setEval( '$env->get( \'rack.input\' )->read();' );
			$env = self::env( Prack::_Hash( array( 'rack.input' => new Prack_LintTest_WeirdIO() ) ) );
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e44 ) { }
		
		if ( isset( $e44 ) )
			$this->assertRegExp( "/read didn't return null or a Prack_Wrapper_String/", $e44->getMessage() );
		else
		{
			$this->fail( 'Expected exception when read returns neither a Prack_Wrapper_String or null.' );
			return;
		}
		
		try
		{
			$middleware_app->setEval( '$env->get( \'rack.input\' )->read( null );' );
			$env = self::env( Prack::_Hash( array( 'rack.input' => new Prack_LintTest_EOFWeirdIO() ) ) );
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e45 ) { }
		
		if ( isset( $e45 ) )
			$this->assertRegExp( "/rack.input method read\( null \) returned null on EOF/", $e45->getMessage() );
		else
		{
			$this->fail( 'Expected exception when method read( null ) returns null on EOF.' );
			return;
		}
		
		try
		{
			$middleware_app->setEval( '$env->get( \'rack.input\' )->rewind();' );
			$env = self::env( Prack::_Hash( array( 'rack.input' => new Prack_LintTest_WeirdIO() ) ) );
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e46 ) { }
		
		if ( isset( $e46 ) )
			$this->assertRegExp( "/rewind threw Prack_System_Error_ESPIPE/", $e46->getMessage() );
		else
		{
			$this->fail( 'Expected exception when method rewind on rack.input throws an exception.' );
			return;
		}
		
		try
		{
			$middleware_app->setEval( '$env->get( \'rack.input\' )->close();' );
			$lint->call( self::env() );
		} catch ( Prack_Error_Lint $e47 ) { }
		
		if ( isset( $e47 ) )
			$this->assertRegExp( "/close must not be called/", $e47->getMessage() );
		else
		{
			$this->fail( 'Expected exception when method close called on rack.input.' );
			return;
		}
		
	} // It should notice input handling errors
	
	/**
	 * It should notice error handling errors
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_notice_error_handling_errors()
	{
		$middleware_app = new TestEcho(
		  201, 
		  Prack::_Hash( array(
		    'Content-type'   => Prack::_String( 'text/plain' ),
		    'Content-length' => Prack::_String( '0' )
		  ) ),
		  Prack::_Array()
		);
		$lint = new Prack_Lint( $middleware_app );
		
		try
		{
			$middleware_app->setEval( '$env->get( \'rack.errors\' )->write( 42 );' );
			$lint->call( self::env() );
		} catch ( Prack_Error_Lint $e48 ) { }
		
		if ( isset( $e48 ) )
			$this->assertRegExp( "/write not called with a Prack_Wrapper_String/", $e48->getMessage() );
		else
		{
			$this->fail( 'Expected exception when method write called with no string argument on error stream.' );
			return;
		}
		
		try
		{
			$middleware_app->setEval( '$env->get( \'rack.errors\' )->close();' );
			$lint->call( self::env() );
		} catch ( Prack_Error_Lint $e49 ) { }
		
		if ( isset( $e49 ) )
			$this->assertRegExp( "/close must not be called/", $e49->getMessage() );
		else
		{
			$this->fail( 'Expected exception when method close called on rack.errors.' );
			return;
		}
	} // It should notice error handling errors
	
	/**
	 * It should notice HEAD errors
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_notice_HEAD_errors()
	{
		$env = self::env(
		  Prack::_Hash( array(
		    'REQUEST_METHOD' => Prack::_String( 'HEAD' )
		  ) )
		);
		
		// Implicit test
		
		$middleware_app = new TestEcho(
		  200,
		  Prack::_Hash( array(
		    'Content-type'   => Prack::_String( 'text/plain' ),
		    'Content-length' => Prack::_String( '3' )
		  ) ),
		  Prack::_Array()
		);
		Prack_Lint::with( $middleware_app )->call( $env );
		
		try
		{
			$middleware_app = new TestEcho(
			  201,
			  Prack::_Hash( array(
			    'Content-type'   => Prack::_String( 'text/plain' ),
			    'Content-length' => Prack::_String( '3' )
			  ) ),
			  Prack::_Array( array( Prack::_String( 'foo' ) ) )
			);
			list( $status, $header, $body ) = Prack_Lint::with( $middleware_app )->call( $env );
			$body->each( array( 'Prack_LintTest', 'noop' ) );
		} catch ( Prack_Error_Lint $e50 ) { }
		
		if ( isset( $e50 ) )
			$this->assertRegExp( "/body was given for HEAD/", $e50->getMessage() );
		else
		{
			$this->fail( 'Expected exception when body given in response to HEAD request.' );
			return;
		}
	} // It should notice HEAD errors
	
	/**
	 * It should pass valid read calls
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_pass_valid_read_calls()
	{
		$hello_str      = Prack::_String( "hello world" );
		$middleware_app = new TestEcho(
		  201,
		  Prack::_Hash( array(
		    'Content-type'   => Prack::_String( 'text/plain' ),
		    'Content-length' => Prack::_String( '0' )
		  ) ),
		  Prack::_Array()
		);
		
		// Implicit test 1.
		$middleware_app->setEval( '$env->get( \'rack.input\' )->read();' );
		$env = self::env(
		  Prack::_Hash( array( 'rack.input' => Prack_Utils_IO::withString( $hello_str ) ) )
		);
		Prack_Lint::with( $middleware_app )->call( $env );
		
		// Implicit test 2.
		$middleware_app->setEval( '$env->get( \'rack.input\' )->read( 0 );' );
		$env = self::env(
		  Prack::_Hash( array( 'rack.input' => Prack_Utils_IO::withString( $hello_str ) ) )
		);
		Prack_Lint::with( $middleware_app )->call( $env );
		
		// Implicit test 3.
		$middleware_app->setEval( '$env->get( \'rack.input\' )->read( 1 );' );
		$env = self::env(
		  Prack::_Hash( array( 'rack.input' => Prack_Utils_IO::withString( $hello_str ) ) )
		);
		Prack_Lint::with( $middleware_app )->call( $env );
		
		// Implicit test 4.
		$middleware_app->setEval( '$env->get( \'rack.input\' )->read( null );' );
		$env = self::env(
		  Prack::_Hash( array( 'rack.input' => Prack_Utils_IO::withString( $hello_str ) ) )
		);
		Prack_Lint::with( $middleware_app )->call( $env );
		
		// Implicit test 5.
		$middleware_app->setEval( '$env->get( \'rack.input\' )->read( null, Prack::_String() );' );
		$env = self::env(
		  Prack::_Hash( array( 'rack.input' => Prack_Utils_IO::withString( $hello_str ) ) )
		);
		Prack_Lint::with( $middleware_app )->call( $env );
		
		// Implicit test 6.
		$middleware_app->setEval( '$env->get( \'rack.input\' )->read( 1, Prack::_String() );' );
		$env = self::env(
		  Prack::_Hash( array( 'rack.input' => Prack_Utils_IO::withString( $hello_str ) ) )
		);
		Prack_Lint::with( $middleware_app )->call( $env );
	} // It should pass valid read calls
}

class Prack_Lint_InputWrapperTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * It should delegate method rewind to the underlying IO object
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_delegate_method_rewind_to_the_underlying_IO_object()
	{
		$io      = Prack_Utils_IO::withString( Prack_Utils_IO::withString( '123' ) );
		$wrapper = new Prack_Lint_InputWrapper( $io );
		
		$this->assertEquals( '123', $wrapper->read()->toN() );
		$this->assertEquals( '',    $wrapper->read()->toN() );
		$wrapper->rewind();
		$this->assertEquals( '123', $wrapper->read()->toN() );
	} // It should delegate method rewind to the underlying IO object
}