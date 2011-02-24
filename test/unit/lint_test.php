<?php

// TODO: Document!
class Prack_LintTest_Echo
  implements Prack_Interface_MiddlewareApp
{
	private $status;
	private $headers;
	private $body;
	private $eval;
	
	// TODO: Document!
	function __construct( $status = 200, $headers = array( 'Content-Type' => 'test/plain' ), $body = array( 'foo' ) )
	{
		$this->status  = $status;
		$this->headers = $headers;
		$this->body    = $body;
		$this->eval    = null;
	}
	
	// TODO: Document!
	public function call( &$env )
	{
		if ( $this->eval )
			eval( $this->eval );
		
		return array( $this->status, $this->headers, $this->body );
	}
	
	public function setEval( $eval )
	{
		$this->eval = $eval;
	}
}

// TODO: Document!
class Prack_LintTest_WeirdIO
  implements Prack_Interface_ReadableStreamlike
{
	// TODO: Document!
	public function gets()
	{
		return 42;
	}
	
	// TODO: Document!
	public function read( $length = null, &$buffer = null )
	{
		return 23;
	}
	
	// TODO: Document!
	public function each( $callback )
	{
		call_user_func( $callback, 23 );
		call_user_func( $callback, 42 );
	}
	
	// TODO: Document!
	public function rewind()
	{
		throw new Prack_Error_System_ErrnoESPIPE();
	}
}

// TODO: Document!
class Prack_LintTest_EOFWeirdIO
  implements Prack_Interface_ReadableStreamlike
{
	// TODO: Document!
	public function gets()
	{
		return null;
	}
	
	// TODO: Document!
	public function read( $length = null, &$buffer = null )
	{
		return null;
	}
	
	// TODO: Document!
	public function each( $callback )
	{
	}
	
	// TODO: Document!
	public function rewind()
	{
	}
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
		array_unshift( $args, '/' );
		return call_user_func_array( array( 'Prack_Mock_Request', 'envFor' ), $args );
	}
	
	/**
	 * It should pass valid request
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_pass_valid_request()
	{
		$lint = new Prack_Lint( new Prack_LintTest_Echo() );
		$env  = self::env();
		$lint->call( $env );
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
			$lint = new Prack_Lint( new Prack_LintTest_Echo() );
			$env  = null;
			$lint->call( $env );
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
		try
		{
			$lint = new Prack_Lint( new Prack_LintTest_Echo() );
			$env  = 5;
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e1 ) { }
		
		if ( isset( $e1 ) )
			$this->assertRegExp( '/not an array/', $e1->getMessage() );
		else
		{
			$this->fail( 'Expected exception on non-array $env.' );
			return;
		}
		
		try
		{
			$lint = new Prack_Lint( new Prack_LintTest_Echo() );
			$env  = self::env();
			unset( $env[ 'REQUEST_METHOD' ] );
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
			$lint = new Prack_Lint( new Prack_LintTest_Echo() );
			$env  = self::env();
			unset( $env[ 'SERVER_NAME' ] );
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
			$lint = new Prack_Lint( new Prack_LintTest_Echo() );
			$env  = self::env( array( 'HTTP_CONTENT_TYPE' => 'text/plain' ) );
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
			$lint = new Prack_Lint( new Prack_LintTest_Echo() );
			$env  = self::env( array( 'HTTP_CONTENT_LENGTH' => '42' ) );
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
			$lint = new Prack_Lint( new Prack_LintTest_Echo() );
			$env  = self::env( array( 'FOO' => array() ) );
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
			$lint = new Prack_Lint( new Prack_LintTest_Echo() );
			$env  = self::env( array( 'rack.version' => "0.2" ) );
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e7 ) { }
		
		if ( isset( $e7 ) )
			$this->assertRegExp( '/must be an array/', $e7->getMessage() );
		else
		{
			$this->fail( 'Expected exception on $env where rack.version is not an array.' );
			return;
		}
		
		try
		{
			$lint = new Prack_Lint( new Prack_LintTest_Echo() );
			$env  = self::env( array( 'rack.url_scheme' => 'gopher' ) );
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
			$lint = new Prack_Lint( new Prack_LintTest_Echo() );
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
			$lint = new Prack_Lint( new Prack_LintTest_Echo() );
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
			$lint = new Prack_Lint( new Prack_LintTest_Echo() );
			$env  = self::env( array( 'REQUEST_METHOD' => 'FUBAR?' ) );
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
			$lint = new Prack_Lint( new Prack_LintTest_Echo() );
			$env  = self::env( array( 'SCRIPT_NAME' => 'howdy' ) );
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
			$lint = new Prack_Lint( new Prack_LintTest_Echo() );
			$env  = self::env( array( 'PATH_INFO' => '../foo' ) );
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
			$lint = new Prack_Lint( new Prack_LintTest_Echo() );
			$env  = self::env( array( 'CONTENT_LENGTH' => 'xcii' ) );
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
			$lint = new Prack_Lint( new Prack_LintTest_Echo() );
			$env  = self::env();
			unset( $env[ 'PATH_INFO'   ] );
			unset( $env[ 'SCRIPT_NAME' ] );
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
			$lint = new Prack_Lint( new Prack_LintTest_Echo() );
			$env  = self::env( array( 'SCRIPT_NAME' => '/' ) );
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
			$lint = new Prack_Lint( new Prack_LintTest_Echo() );
			$env  = self::env( array( 'rack.input' => '' ) );
			$lint->call( $env );
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
			$lint = new Prack_Lint( new Prack_LintTest_Echo() );
			$env  = self::env( array( 'rack.errors' => '' ) );
			$lint->call( $env );
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
		try
		{
			$middleware_app = new Prack_LintTest_Echo( 'cc' ); // 'cc' as status arg
			$lint           = new Prack_Lint( $middleware_app );
			$env            = self::env( array() );
			$lint->call( $env );
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
			$middleware_app = new Prack_LintTest_Echo( 42 ); // 'cc' as status arg
			$lint           = new Prack_Lint( $middleware_app );
			$env            = self::env( array() );
			$lint->call( $env );
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
		try
		{
			$middleware_app = new Prack_LintTest_Echo( 200, new stdClass(), array() );
			$lint           = new Prack_Lint( $middleware_app );
			$env            = self::env( array() );
			$lint->call( $env );
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
			$middleware_app = new Prack_LintTest_Echo( 200, array( 1 => false ), array() );
			$lint           = new Prack_Lint( $middleware_app );
			$env            = self::env( array() );
			$lint->call( $env );
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
			$middleware_app = new Prack_LintTest_Echo( 200, array( 'Status' => '404' ), array() );
			$lint           = new Prack_Lint( $middleware_app );
			$env            = self::env( array() );
			$lint->call( $env );
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
			$middleware_app = new Prack_LintTest_Echo( 200, array( 'Content-Type:' => 'text/plain' ), array() );
			$lint           = new Prack_Lint( $middleware_app );
			$env            = self::env( array() );
			$lint->call( $env );
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
			$middleware_app = new Prack_LintTest_Echo( 200, array( 'Content-' => 'text/plain' ), array() );
			$lint           = new Prack_Lint( $middleware_app );
			$env            = self::env( array() );
			$lint->call( $env );
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
			$middleware_app = new Prack_LintTest_Echo( 200, array( '..%%quark%%..' => 'text/plain' ), array() );
			$lint           = new Prack_Lint( $middleware_app );
			$env            = self::env( array() );
			$lint->call( $env );
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
			$middleware_app = new Prack_LintTest_Echo( 200, array( 'Foo' => new stdClass() ), array() );
			$lint           = new Prack_Lint( $middleware_app );
			$env            = self::env( array() );
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e27 ) { }
		
		if ( isset( $e27 ) )
			$this->assertEquals( "a header value must be a string, but the value of 'Foo' is a stdClass", $e27->getMessage() );
		else
		{
			$this->fail( 'Expected exception on headers if the value is not a string.' );
			return;
		}
		
		try
		{
			$middleware_app = new Prack_LintTest_Echo( 200, array( 'Foo' => array( 1, 2, 3 ) ), array() );
			$lint           = new Prack_Lint( $middleware_app );
			$env            = self::env( array() );
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e28 ) { }
		
		if ( isset( $e28 ) )
			$this->assertEquals( "a header value must be a string, but the value of 'Foo' is a array", $e28->getMessage() );
		else
		{
			$this->fail( 'Expected exception on headers if the value is not a string.' );
			return;
		}
		
		try
		{
			$middleware_app = new Prack_LintTest_Echo( 200, array( 'Foo' => "text\000plain" ), array() );
			$lint           = new Prack_Lint( $middleware_app );
			$env            = self::env( array() );
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e29 ) { }
		
		if ( isset( $e29 ) )
			$this->assertRegExp( '/invalid header/', $e29->getMessage() );
		else
		{
			$this->fail( 'Expected exception on headers if the value contains disallowed characters.' );
			return;
		}
		
		# line ends (010) should be allowed in header values.
		$ok_headers     = array( 'Foo-Bar' => "one\ntwo\nthree", 'Content-Length' => '0', 'Content-Type' => 'text/plain' );
		$middleware_app = new Prack_LintTest_Echo( 200, $ok_headers, array() );
		$lint           = new Prack_Lint( $middleware_app );
		$env            = self::env( array() );
		$lint->call( $env );
	} // It should notice header errors
	
	/**
	 * It should notice content-type errors
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_notice_content_type_errors()
	{
		try
		{
			$middleware_app = new Prack_LintTest_Echo( 200, array( 'Content-length' => '0' ), array() );
			$lint           = new Prack_Lint( $middleware_app );
			$env            = self::env( array() );
			$lint->call( $env );
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
				$middleware_app = new Prack_LintTest_Echo( $no_body_status, 
				                                           array( 'Content-type' => 'text/plain', 'Content-length' => '0' ), 
				                                           array() );
				$lint = new Prack_Lint( $middleware_app );
				$env  = self::env( array() );
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
		foreach( array( 100, 101, 204, 304 ) as $no_body_status )
		{
			try
			{
				$middleware_app = new Prack_LintTest_Echo( $no_body_status, array( 'Content-length' => '0' ), array() );
				$lint = new Prack_Lint( $middleware_app );
				$env  = self::env( array() );
				$lint->call( $env );
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
				$middleware_app = new Prack_LintTest_Echo( 200, 
				                                           array( 'Content-type' => 'text/plain', 'Content-length' => '1' ), 
				                                           array() );
				$lint = new Prack_Lint( $middleware_app );
				$env  = self::env( array() );
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
			$middleware_app = new Prack_LintTest_Echo( 200, 
			                                           array( 'Content-type' => 'text/plain', 'Content-length' => '3' ), 
			                                           array( 1, 2, 3 ) );
			$lint = new Prack_Lint( $middleware_app );
			$env  = self::env( array() );
			list( $status, $headers, $lint ) = $lint->call( $env );
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
		try
		{
			$middleware_app = new Prack_LintTest_Echo( 201, 
			                                           array( 'Content-type' => 'text/plain', 'Content-length' => '0' ), 
			                                           array() );
			$middleware_app->setEval( '$env[ \'rack.input\' ]->gets( "\r\n" );' );
			
			$lint = new Prack_Lint( $middleware_app );
			$env  = self::env( array() );
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
			$middleware_app = new Prack_LintTest_Echo( 201, 
			                                           array( 'Content-type' => 'text/plain', 'Content-length' => '0' ), 
			                                           array() );
			$middleware_app->setEval( '$env[ \'rack.input\' ]->read( 1, 2, 3 );' );
			
			$lint = new Prack_Lint( $middleware_app );
			$env  = self::env( array() );
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
			$middleware_app = new Prack_LintTest_Echo( 201, 
			                                           array( 'Content-type' => 'text/plain', 'Content-length' => '0' ), 
			                                           array() );
			$middleware_app->setEval( '$env[ \'rack.input\' ]->read( "foo" );' );
			
			$lint = new Prack_Lint( $middleware_app );
			$env  = self::env( array() );
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
			$middleware_app = new Prack_LintTest_Echo( 201, 
			                                           array( 'Content-type' => 'text/plain', 'Content-length' => '0' ), 
			                                           array() );
			$middleware_app->setEval( '$env[ \'rack.input\' ]->read( -1 );' );
			
			$lint = new Prack_Lint( $middleware_app );
			$env  = self::env( array() );
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
			$middleware_app = new Prack_LintTest_Echo( 201, 
			                                           array( 'Content-type' => 'text/plain', 'Content-length' => '0' ), 
			                                           array() );
			$middleware_app->setEval( '$env[ \'rack.input\' ]->read( null, null );' );
			
			$lint = new Prack_Lint( $middleware_app );
			$env  = self::env( array() );
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
			$middleware_app = new Prack_LintTest_Echo( 201, 
			                                           array( 'Content-type' => 'text/plain', 'Content-length' => '0' ), 
			                                           array() );
			$middleware_app->setEval( '$env[ \'rack.input\' ]->read( null, 1 );' );
			
			$lint = new Prack_Lint( $middleware_app );
			$env  = self::env( array() );
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
			$middleware_app = new Prack_LintTest_Echo( 201, 
			                                           array( 'Content-type' => 'text/plain', 'Content-length' => '0' ), 
			                                           array() );
			$middleware_app->setEval( '$env[ \'rack.input\' ]->rewind( 0 );' );
			
			$lint = new Prack_Lint( $middleware_app );
			$env  = self::env( array() );
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
			$middleware_app = new Prack_LintTest_Echo( 201, 
			                                           array( 'Content-type' => 'text/plain', 'Content-length' => '0' ), 
			                                           array() );
			$middleware_app->setEval( '$env[ \'rack.input\' ]->gets();' );
			
			$lint = new Prack_Lint( $middleware_app );
			$env  = self::env( array( 'rack.input' => new Prack_LintTest_WeirdIO() ) );
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e42 ) { }
		
		if ( isset( $e42 ) )
			$this->assertRegExp( "/rack.input method gets didn't return a string/", $e42->getMessage() );
		else
		{
			$this->fail( 'Expected exception when gets called on non-string-returning IO.' );
			return;
		}
		
		try
		{
			$middleware_app = new Prack_LintTest_Echo( 201, 
			                                           array( 'Content-type' => 'text/plain', 'Content-length' => '0' ), 
			                                           array() );
			$middleware_app->setEval( '$env[ \'rack.input\' ]->each( array( \'Prack_LintTest\', \'noop\' ) );' );
			
			$lint = new Prack_Lint( $middleware_app );
			$env  = self::env( array( 'rack.input' => new Prack_LintTest_WeirdIO() ) );
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e43 ) { }
			
		if ( isset( $e43 ) )
			$this->assertRegExp( "/rack.input method each didn't yield a string/", $e43->getMessage() );
		else
		{
			$this->fail( 'Expected exception when gets called on IO which yields non-string values.' );
			return;
		}
			
		try
		{
			$middleware_app = new Prack_LintTest_Echo( 201, 
			                                           array( 'Content-type' => 'text/plain', 'Content-length' => '0' ), 
			                                           array() );
			$middleware_app->setEval( '$env[ \'rack.input\' ]->read();' );
			
			$lint = new Prack_Lint( $middleware_app );
			$env  = self::env( array( 'rack.input' => new Prack_LintTest_WeirdIO() ) );
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e44 ) { }
		
		if ( isset( $e44 ) )
			$this->assertRegExp( "/read didn't return null or a string/", $e44->getMessage() );
		else
		{
			$this->fail( 'Expected exception when read returns neither a string or null.' );
			return;
		}
		
		try
		{
			$middleware_app = new Prack_LintTest_Echo( 201, 
			                                           array( 'Content-type' => 'text/plain', 'Content-length' => '0' ), 
			                                           array() );
			$middleware_app->setEval( '$env[ \'rack.input\' ]->read( null );' );
			
			$lint = new Prack_Lint( $middleware_app );
			$env  = self::env( array( 'rack.input' => new Prack_LintTest_EOFWeirdIO() ) );
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
			$middleware_app = new Prack_LintTest_Echo( 201, 
			                                           array( 'Content-type' => 'text/plain', 'Content-length' => '0' ), 
			                                           array() );
			$middleware_app->setEval( '$env[ \'rack.input\' ]->rewind();' );
			
			$lint = new Prack_Lint( $middleware_app );
			$env  = self::env( array( 'rack.input' => new Prack_LintTest_WeirdIO() ) );
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
			$middleware_app = new Prack_LintTest_Echo( 201, 
			                                           array( 'Content-type' => 'text/plain', 'Content-length' => '0' ), 
			                                           array() );
			$middleware_app->setEval( '$env[ \'rack.input\' ]->close();' );
			
			$lint = new Prack_Lint( $middleware_app );
			$env  = self::env( array() );
			$lint->call( $env );
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
		try
		{
			$middleware_app = new Prack_LintTest_Echo( 201, 
			                                           array( 'Content-type' => 'text/plain', 'Content-length' => '0' ), 
			                                           array() );
			$middleware_app->setEval( '$env[ \'rack.errors\' ]->write( 42 );' );
			
			$lint = new Prack_Lint( $middleware_app );
			$env  = self::env( array() );
			$lint->call( $env );
		} catch ( Prack_Error_Lint $e48 ) { }
		
		if ( isset( $e48 ) )
			$this->assertRegExp( "/write not called with a string/", $e48->getMessage() );
		else
		{
			$this->fail( 'Expected exception when method write called with no string argument on error stream.' );
			return;
		}
		
		try
		{
			$middleware_app = new Prack_LintTest_Echo( 201, 
			                                           array( 'Content-type' => 'text/plain', 'Content-length' => '0' ), 
			                                           array() );
			$middleware_app->setEval( '$env[ \'rack.errors\' ]->close();' );
			
			$lint = new Prack_Lint( $middleware_app );
			$env  = self::env( array() );
			$lint->call( $env );
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
		$middleware_app = new Prack_LintTest_Echo( 200, 
		                                           array( 'Content-type' => 'text/plain', 'Content-length' => '3' ), 
		                                           array() );
		$lint = new Prack_Lint( $middleware_app );
		$env  = self::env( array( 'REQUEST_METHOD' => 'HEAD' ) );
		$lint->call( $env );
		
		// End implicit test ^.
		
		try
		{
			$middleware_app = new Prack_LintTest_Echo( 201, 
			                                           array( 'Content-type' => 'text/plain', 'Content-length' => '3' ), 
			                                           array( 'foo' ) );
			$lint = new Prack_Lint( $middleware_app );
			$env  = self::env( array( 'REQUEST_METHOD' => 'HEAD' ) );
			list( $status, $header, $body ) = $lint->call( $env );
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
		$hello_str = "hello world";
		
		// Implicit test 1.
		$middleware_app = new Prack_LintTest_Echo( 201, array( 'Content-type' => 'text/plain', 'Content-length' => '0' ), array() );
		$middleware_app->setEval( '$env[ \'rack.input\' ]->read();' );
		$lint = new Prack_Lint( $middleware_app );
		$env  = self::env( array( 'rack.input' => Prack_Utils_IO::withString( $hello_str ) ) );
		$lint->call( $env );
		
		// Implicit test 2.
		$middleware_app = new Prack_LintTest_Echo( 201, array( 'Content-type' => 'text/plain', 'Content-length' => '0' ), array() );
		$middleware_app->setEval( '$env[ \'rack.input\' ]->read( 0 );' );
		$lint = new Prack_Lint( $middleware_app );
		$env  = self::env( array( 'rack.input' => Prack_Utils_IO::withString( $hello_str ) ) );
		$lint->call( $env );
		
		// Implicit test 3.
		$middleware_app = new Prack_LintTest_Echo( 201, array( 'Content-type' => 'text/plain', 'Content-length' => '0' ), array() );
		$middleware_app->setEval( '$env[ \'rack.input\' ]->read( 1 );' );
		$lint = new Prack_Lint( $middleware_app );
		$env  = self::env( array( 'rack.input' => Prack_Utils_IO::withString( $hello_str ) ) );
		$lint->call( $env );
		
		// Implicit test 4.
		$middleware_app = new Prack_LintTest_Echo( 201, array( 'Content-type' => 'text/plain', 'Content-length' => '0' ), array() );
		$middleware_app->setEval( '$env[ \'rack.input\' ]->read( null );' );
		$lint = new Prack_Lint( $middleware_app );
		$env  = self::env( array( 'rack.input' => Prack_Utils_IO::withString( $hello_str ) ) );
		$lint->call( $env );
		
		// Implicit test 5.
		$middleware_app = new Prack_LintTest_Echo( 201, array( 'Content-type' => 'text/plain', 'Content-length' => '0' ), array() );
		$middleware_app->setEval( '$env[ \'rack.input\' ]->read( null, \'\' );' );
		$lint = new Prack_Lint( $middleware_app );
		$env  = self::env( array( 'rack.input' => Prack_Utils_IO::withString( $hello_str ) ) );
		$lint->call( $env );
		
		// Implicit test 6.
		$middleware_app = new Prack_LintTest_Echo( 201, array( 'Content-type' => 'text/plain', 'Content-length' => '0' ), array() );
		$middleware_app->setEval( '$env[ \'rack.input\' ]->read( 1, \'\' );' );
		$lint = new Prack_Lint( $middleware_app );
		$env  = self::env( array( 'rack.input' => Prack_Utils_IO::withString( $hello_str ) ) );
		$lint->call( $env );
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
		$io      = Prack_Utils_IO::withString( '123' );
		$wrapper = new Prack_Lint_InputWrapper( $io );
		
		$this->assertEquals( '123', $wrapper->read() );
		$this->assertEquals( '',    $wrapper->read() );
		$wrapper->rewind();
		$this->assertEquals( '123', $wrapper->read() );
	} // It should delegate method rewind to the underlying IO object
}
