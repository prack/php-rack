<?php


// TODO: Document!
class Prack_URLMapTest extends PHPUnit_Framework_TestCase 
{
	// TODO: Document!
	static function appWithEval( $eval )
	{
		return new Prack_Test_Echo(
		  200,
		  Prack::_Hash(),
		  Prack::_Array( array( Prack::_String() ) ),
		  $eval
		);
	}
	
	/**
	 * It dispatches paths correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_dispatches_paths_correctly()
	{
		$middleware_app = self::appWithEval('
		  $this->headers->set( "X-ScriptName",  $env->get( "SCRIPT_NAME" ) );
		  $this->headers->set( "X-PathInfo",    $env->get( "PATH_INFO"   ) );
		  $this->headers->set( "X-ContentType", $env->get( "text/plain"  ) );
		');
		
		$url_map = Prack_URLMap::with(
		  Prack::_Hash( array(
		    'http://foo.org/bar' => $middleware_app,
		    '/foo'               => $middleware_app,
		    '/foo/bar'           => $middleware_app,
		  ) )
		);
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prack::_String( '/' ) );
		$this->assertTrue( $mock_response->isNotFound() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prack::_String( '/qux' ) );
		$this->assertTrue( $mock_response->isNotFound() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prack::_String( '/foo' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( '/foo', $mock_response->get( 'X-ScriptName' )->toN() );
		$this->assertEquals( ''    , $mock_response->get( 'X-PathInfo'   )->toN() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prack::_String( '/foo/' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( '/foo', $mock_response->get( 'X-ScriptName' )->toN() );
		$this->assertEquals( '/'   , $mock_response->get( 'X-PathInfo'   )->toN() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prack::_String( '/foo/bar' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( '/foo/bar', $mock_response->get( 'X-ScriptName' )->toN() );
		$this->assertEquals( ''        , $mock_response->get( 'X-PathInfo'   )->toN() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prack::_String( '/foo/bar/' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( '/foo/bar', $mock_response->get( 'X-ScriptName' )->toN() );
		$this->assertEquals( '/'       , $mock_response->get( 'X-PathInfo'   )->toN() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prack::_String( '/foo///bar//quux' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( '/foo/bar', $mock_response->get( 'X-ScriptName' )->toN() );
		$this->assertEquals( '//quux'  , $mock_response->get( 'X-PathInfo'   )->toN() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get(
		  Prack::_String( '/foo/quux' ),
		  Prack::_Hash( array( 'SCRIPT_NAME' => Prack::_String( '/bleh' ) ) )
		);
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( '/bleh/foo', $mock_response->get( 'X-ScriptName' )->toN() );
		$this->assertEquals( '/quux'    , $mock_response->get( 'X-PathInfo'   )->toN() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get(
		  Prack::_String( '/bar' ),
		  Prack::_Hash( array( 'HTTP_HOST' => Prack::_String( 'foo.org' ) ) )
		);
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( '/bar', $mock_response->get( 'X-ScriptName' )->toN() );
		$this->assertTrue( $mock_response->get( 'X-PathInfo' )->isEmpty() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get(
		  Prack::_String( '/bar/' ),
		  Prack::_Hash( array( 'HTTP_HOST' => Prack::_String( 'foo.org' ) ) )
		);
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( '/bar', $mock_response->get( 'X-ScriptName' )->toN() );
		$this->assertEquals( '/'   , $mock_response->get( 'X-PathInfo'   )->toN() );
	} // It dispatches paths correctly
	
	/**
	 * It dispatches hosts correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_dispatches_hosts_correctly()
	{
		$url_map = Prack_URLMap::with(
		  Prack::_Hash( array( 
		    'http://foo.org/' => self::appWithEval('
		      $this->headers->set( "Content-Type", Prack::_String( "text/plain" ) );
		      $this->headers->set( "X-Position",   Prack::_String( "foo.org"    ) );
		      $this->headers->set( "X-Host",       $env->contains( "HTTP_HOST") ? $env->get( "HTTP_HOST"   )
		                                                                        : $env->get( "SERVER_NAME" ) );
		    '),
		    'http://subdomain.foo.org/' => self::appWithEval('
		      $this->headers->set( "Content-Type", Prack::_String( "text/plain"        ) );
		      $this->headers->set( "X-Position",   Prack::_String( "subdomain.foo.org" ) );
		      $this->headers->set( "X-Host",       $env->contains( "HTTP_HOST") ? $env->get( "HTTP_HOST"   )
		                                                                        : $env->get( "SERVER_NAME" ) );
		    '),
		    'http://bar.org/' => self::appWithEval('
		      $this->headers->set( "Content-Type", Prack::_String( "text/plain" ) );
		      $this->headers->set( "X-Position",   Prack::_String( "bar.org"    ) );
		      $this->headers->set( "X-Host",       $env->contains( "HTTP_HOST") ? $env->get( "HTTP_HOST"   )
		                                                                        : $env->get( "SERVER_NAME" ) );
		    '),
		    '/' => self::appWithEval('
		      $this->headers->set( "Content-Type", Prack::_String( "text/plain"  ) );
		      $this->headers->set( "X-Position",   Prack::_String( "default.org" ) );
		      $this->headers->set( "X-Host",       $env->contains( "HTTP_HOST" ) ? $env->get( "HTTP_HOST"   )
		                                                                         : $env->get( "SERVER_NAME" ) );
		    '),
		  ) )
		);
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prack::_String( '/' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( 'default.org', $mock_response->get( 'X-Position' )->toN() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get(
		  Prack::_String( '/' ),
		  Prack::_Hash( array( 'HTTP_HOST' => Prack::_String( 'bar.org' ) ) )
		);
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( 'bar.org', $mock_response->get( 'X-Position' )->toN() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get(
		  Prack::_String( '/' ),
		  Prack::_Hash( array( 'HTTP_HOST' => Prack::_String( 'foo.org' ) ) )
		);
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( 'foo.org', $mock_response->get( 'X-Position' )->toN() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get(
		  Prack::_String( '/' ),
		  Prack::_Hash( array(
		    'HTTP_HOST'   => Prack::_String( 'subdomain.foo.org' ),
		    'SERVER_NAME' => Prack::_String( 'foo.org'           )
		  ) )
		);
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( 'subdomain.foo.org', $mock_response->get( 'X-Position' )->toN() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prack::_String( 'http://foo.org' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( 'default.org', $mock_response->get( 'X-Position' )->toN() );
		
		//FAILS
		$mock_response = Prack_Mock_Request::with( $url_map )->get(
		  Prack::_String( '/' ),
		  Prack::_Hash( array( 'HTTP_HOST' => Prack::_String( 'example.org' ) ) )
		);
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( 'default.org', $mock_response->get( 'X-Position' )->toN() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get(
		  Prack::_String( '/' ),
		  Prack::_Hash( array(
		    'HTTP_HOST'   => Prack::_String( 'example.org:9292' ),
		    'SERVER_PORT' => Prack::_String( '9292'             )
		  ) )
		);
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( 'default.org', $mock_response->get( 'X-Position' )->toN() );
	} // It dispatches hosts correctly
	
	/**
	 * It should be nestable
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_nestable()
	{
		$url_map = Prack_URLMap::with( Prack::_Hash( array (
		  '/foo'  => Prack_URLMap::with( Prack::_Hash( array(
		    '/bar'  => Prack_URLMap::with( Prack::_Hash( array(
		      '/quux' => self::appWithEval('
		        $this->headers->set( "Content-Type", Prack::_String( "text/plain"    ) );
		        $this->headers->set( "X-Position",   Prack::_String( "/foo/bar/quux" ) );
		        $this->headers->set( "X-PathInfo",   $env->get( "PATH_INFO"   )        );
		        $this->headers->set( "X-ScriptName", $env->get( "SCRIPT_NAME" )        );
		      ')
		    ) ) )
		  ) ) )
		) ) );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prack::_String( '/foo/bar' ) );
		$this->assertTrue( $mock_response->isNotFound() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prack::_String( '/foo/bar/quux' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( '/foo/bar/quux', $mock_response->get( 'X-Position'   )->toN() );
		$this->assertEquals(              '', $mock_response->get( 'X-PathInfo'   )->toN() );
		$this->assertEquals( '/foo/bar/quux', $mock_response->get( 'X-ScriptName' )->toN() );
	} // It should be nestable
	
	/**
	 * It should route root apps correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_route_root_apps_correctly()
	{
		$url_map = Prack_URLMap::with(
		  Prack::_Hash( array( 
		    '/' => self::appWithEval('
		      $this->headers->set( "Content-Type", Prack::_String( "text/plain" ) );
		      $this->headers->set( "X-Position",   Prack::_String( "root"       ) );
		      $this->headers->set( "X-PathInfo",   $env->get( "PATH_INFO"   )     );
		      $this->headers->set( "X-ScriptName", $env->get( "SCRIPT_NAME" )     );
		    '),
		    '/foo' => self::appWithEval('
		      $this->headers->set( "Content-Type", Prack::_String( "text/plain" ) );
		      $this->headers->set( "X-Position",   Prack::_String( "foo"        ) );
		      $this->headers->set( "X-PathInfo",   $env->get( "PATH_INFO"   )     );
		      $this->headers->set( "X-ScriptName", $env->get( "SCRIPT_NAME" )     );
		    '),
		  ) )
		);
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prack::_String( '/foo/bar' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals(  'foo', $mock_response->get( 'X-Position'   )->toN() );
		$this->assertEquals( '/bar', $mock_response->get( 'X-PathInfo'   )->toN() );
		$this->assertEquals( '/foo', $mock_response->get( 'X-ScriptName' )->toN() );

		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prack::_String( '/foo' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals(  'foo', $mock_response->get( 'X-Position'   )->toN() );
		$this->assertEquals(     '', $mock_response->get( 'X-PathInfo'   )->toN() );
		$this->assertEquals( '/foo', $mock_response->get( 'X-ScriptName' )->toN() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prack::_String( '/bar' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( 'root', $mock_response->get( 'X-Position'   )->toN() );
		$this->assertEquals( '/bar', $mock_response->get( 'X-PathInfo'   )->toN() );
		$this->assertEquals(     '', $mock_response->get( 'X-ScriptName' )->toN() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prack::_String() );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( 'root', $mock_response->get( 'X-Position'   )->toN() );
		$this->assertEquals(    '/', $mock_response->get( 'X-PathInfo'   )->toN() );
		$this->assertEquals(     '', $mock_response->get( 'X-ScriptName' )->toN() );
	} // It should route root apps correctly
	
	/**
	 * It should not squeeze slashes
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_squeeze_slashes()
	{
		$url_map = Prack_URLMap::with(
		  Prack::_Hash( array( 
		    '/' => self::appWithEval('
		      $this->headers->set( "Content-Type", Prack::_String( "text/plain" ) );
		      $this->headers->set( "X-Position",   Prack::_String( "root"       ) );
		      $this->headers->set( "X-PathInfo",   $env->get( "PATH_INFO"   )     );
		      $this->headers->set( "X-ScriptName", $env->get( "SCRIPT_NAME" )     );
		    '),
		    '/foo' => self::appWithEval('
		      $this->headers->set( "Content-Type", Prack::_String( "text/plain" ) );
		      $this->headers->set( "X-Position",   Prack::_String( "foo"        ) );
		      $this->headers->set( "X-PathInfo",   $env->get( "PATH_INFO"   )     );
		      $this->headers->set( "X-ScriptName", $env->get( "SCRIPT_NAME" )     );
		    '),
		  ) )
		);
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prack::_String( '/http://example.org/bar' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals(                    'root', $mock_response->get( 'X-Position'   )->toN() );
		$this->assertEquals( '/http://example.org/bar', $mock_response->get( 'X-PathInfo'   )->toN() );
		$this->assertEquals(                        '', $mock_response->get( 'X-ScriptName' )->toN() );
	} // It should not squeeze slashes
}