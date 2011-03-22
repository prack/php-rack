<?php


// TODO: Document!
class Prack_URLMapTest extends PHPUnit_Framework_TestCase 
{
	// TODO: Document!
	static function appWithEval( $eval )
	{
		return new Prack_Test_Echo(
		  Prb::Num( 200 ),
		  Prb::Hsh(),
		  Prb::Ary( array( Prb::Str() ) ),
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
		  Prb::Hsh( array(
		    'http://foo.org/bar' => $middleware_app,
		    '/foo'               => $middleware_app,
		    '/foo/bar'           => $middleware_app,
		  ) )
		);
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::Str( '/' ) );
		$this->assertTrue( $mock_response->isNotFound() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::Str( '/qux' ) );
		$this->assertTrue( $mock_response->isNotFound() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::Str( '/foo' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( '/foo', $mock_response->get( 'X-ScriptName' )->raw() );
		$this->assertEquals( ''    , $mock_response->get( 'X-PathInfo'   )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::Str( '/foo/' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( '/foo', $mock_response->get( 'X-ScriptName' )->raw() );
		$this->assertEquals( '/'   , $mock_response->get( 'X-PathInfo'   )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::Str( '/foo/bar' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( '/foo/bar', $mock_response->get( 'X-ScriptName' )->raw() );
		$this->assertEquals( ''        , $mock_response->get( 'X-PathInfo'   )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::Str( '/foo/bar/' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( '/foo/bar', $mock_response->get( 'X-ScriptName' )->raw() );
		$this->assertEquals( '/'       , $mock_response->get( 'X-PathInfo'   )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::Str( '/foo///bar//quux' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( '/foo/bar', $mock_response->get( 'X-ScriptName' )->raw() );
		$this->assertEquals( '//quux'  , $mock_response->get( 'X-PathInfo'   )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get(
		  Prb::Str( '/foo/quux' ),
		  Prb::Hsh( array( 'SCRIPT_NAME' => Prb::Str( '/bleh' ) ) )
		);
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( '/bleh/foo', $mock_response->get( 'X-ScriptName' )->raw() );
		$this->assertEquals( '/quux'    , $mock_response->get( 'X-PathInfo'   )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get(
		  Prb::Str( '/bar' ),
		  Prb::Hsh( array( 'HTTP_HOST' => Prb::Str( 'foo.org' ) ) )
		);
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( '/bar', $mock_response->get( 'X-ScriptName' )->raw() );
		$this->assertTrue( $mock_response->get( 'X-PathInfo' )->isEmpty() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get(
		  Prb::Str( '/bar/' ),
		  Prb::Hsh( array( 'HTTP_HOST' => Prb::Str( 'foo.org' ) ) )
		);
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( '/bar', $mock_response->get( 'X-ScriptName' )->raw() );
		$this->assertEquals( '/'   , $mock_response->get( 'X-PathInfo'   )->raw() );
	} // It dispatches paths correctly
	
	/**
	 * It dispatches hosts correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_dispatches_hosts_correctly()
	{
		$url_map = Prack_URLMap::with(
		  Prb::Hsh( array( 
		    'http://foo.org/' => self::appWithEval('
		      $this->headers->set( "Content-Type", Prb::Str( "text/plain" ) );
		      $this->headers->set( "X-Position",   Prb::Str( "foo.org"    ) );
		      $this->headers->set( "X-Host",       $env->contains( "HTTP_HOST") ? $env->get( "HTTP_HOST"   )
		                                                                        : $env->get( "SERVER_NAME" ) );
		    '),
		    'http://subdomain.foo.org/' => self::appWithEval('
		      $this->headers->set( "Content-Type", Prb::Str( "text/plain"        ) );
		      $this->headers->set( "X-Position",   Prb::Str( "subdomain.foo.org" ) );
		      $this->headers->set( "X-Host",       $env->contains( "HTTP_HOST") ? $env->get( "HTTP_HOST"   )
		                                                                        : $env->get( "SERVER_NAME" ) );
		    '),
		    'http://bar.org/' => self::appWithEval('
		      $this->headers->set( "Content-Type", Prb::Str( "text/plain" ) );
		      $this->headers->set( "X-Position",   Prb::Str( "bar.org"    ) );
		      $this->headers->set( "X-Host",       $env->contains( "HTTP_HOST") ? $env->get( "HTTP_HOST"   )
		                                                                        : $env->get( "SERVER_NAME" ) );
		    '),
		    '/' => self::appWithEval('
		      $this->headers->set( "Content-Type", Prb::Str( "text/plain"  ) );
		      $this->headers->set( "X-Position",   Prb::Str( "default.org" ) );
		      $this->headers->set( "X-Host",       $env->contains( "HTTP_HOST" ) ? $env->get( "HTTP_HOST"   )
		                                                                         : $env->get( "SERVER_NAME" ) );
		    '),
		  ) )
		);
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::Str( '/' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( 'default.org', $mock_response->get( 'X-Position' )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get(
		  Prb::Str( '/' ),
		  Prb::Hsh( array( 'HTTP_HOST' => Prb::Str( 'bar.org' ) ) )
		);
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( 'bar.org', $mock_response->get( 'X-Position' )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get(
		  Prb::Str( '/' ),
		  Prb::Hsh( array( 'HTTP_HOST' => Prb::Str( 'foo.org' ) ) )
		);
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( 'foo.org', $mock_response->get( 'X-Position' )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get(
		  Prb::Str( '/' ),
		  Prb::Hsh( array(
		    'HTTP_HOST'   => Prb::Str( 'subdomain.foo.org' ),
		    'SERVER_NAME' => Prb::Str( 'foo.org'           )
		  ) )
		);
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( 'subdomain.foo.org', $mock_response->get( 'X-Position' )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::Str( 'http://foo.org' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( 'default.org', $mock_response->get( 'X-Position' )->raw() );
		
		//FAILS
		$mock_response = Prack_Mock_Request::with( $url_map )->get(
		  Prb::Str( '/' ),
		  Prb::Hsh( array( 'HTTP_HOST' => Prb::Str( 'example.org' ) ) )
		);
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( 'default.org', $mock_response->get( 'X-Position' )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get(
		  Prb::Str( '/' ),
		  Prb::Hsh( array(
		    'HTTP_HOST'   => Prb::Str( 'example.org:9292' ),
		    'SERVER_PORT' => Prb::Str( '9292'             )
		  ) )
		);
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( 'default.org', $mock_response->get( 'X-Position' )->raw() );
	} // It dispatches hosts correctly
	
	/**
	 * It should be nestable
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_nestable()
	{
		$url_map = Prack_URLMap::with( Prb::Hsh( array (
		  '/foo'  => Prack_URLMap::with( Prb::Hsh( array(
		    '/bar'  => Prack_URLMap::with( Prb::Hsh( array(
		      '/quux' => self::appWithEval('
		        $this->headers->set( "Content-Type", Prb::Str( "text/plain"    ) );
		        $this->headers->set( "X-Position",   Prb::Str( "/foo/bar/quux" ) );
		        $this->headers->set( "X-PathInfo",   $env->get( "PATH_INFO"   )        );
		        $this->headers->set( "X-ScriptName", $env->get( "SCRIPT_NAME" )        );
		      ')
		    ) ) )
		  ) ) )
		) ) );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::Str( '/foo/bar' ) );
		$this->assertTrue( $mock_response->isNotFound() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::Str( '/foo/bar/quux' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( '/foo/bar/quux', $mock_response->get( 'X-Position'   )->raw() );
		$this->assertEquals(              '', $mock_response->get( 'X-PathInfo'   )->raw() );
		$this->assertEquals( '/foo/bar/quux', $mock_response->get( 'X-ScriptName' )->raw() );
	} // It should be nestable
	
	/**
	 * It should route root apps correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_route_root_apps_correctly()
	{
		$url_map = Prack_URLMap::with(
		  Prb::Hsh( array( 
		    '/' => self::appWithEval('
		      $this->headers->set( "Content-Type", Prb::Str( "text/plain" ) );
		      $this->headers->set( "X-Position",   Prb::Str( "root"       ) );
		      $this->headers->set( "X-PathInfo",   $env->get( "PATH_INFO"   )     );
		      $this->headers->set( "X-ScriptName", $env->get( "SCRIPT_NAME" )     );
		    '),
		    '/foo' => self::appWithEval('
		      $this->headers->set( "Content-Type", Prb::Str( "text/plain" ) );
		      $this->headers->set( "X-Position",   Prb::Str( "foo"        ) );
		      $this->headers->set( "X-PathInfo",   $env->get( "PATH_INFO"   )     );
		      $this->headers->set( "X-ScriptName", $env->get( "SCRIPT_NAME" )     );
		    '),
		  ) )
		);
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::Str( '/foo/bar' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals(  'foo', $mock_response->get( 'X-Position'   )->raw() );
		$this->assertEquals( '/bar', $mock_response->get( 'X-PathInfo'   )->raw() );
		$this->assertEquals( '/foo', $mock_response->get( 'X-ScriptName' )->raw() );

		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::Str( '/foo' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals(  'foo', $mock_response->get( 'X-Position'   )->raw() );
		$this->assertEquals(     '', $mock_response->get( 'X-PathInfo'   )->raw() );
		$this->assertEquals( '/foo', $mock_response->get( 'X-ScriptName' )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::Str( '/bar' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( 'root', $mock_response->get( 'X-Position'   )->raw() );
		$this->assertEquals( '/bar', $mock_response->get( 'X-PathInfo'   )->raw() );
		$this->assertEquals(     '', $mock_response->get( 'X-ScriptName' )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::Str() );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( 'root', $mock_response->get( 'X-Position'   )->raw() );
		$this->assertEquals(    '/', $mock_response->get( 'X-PathInfo'   )->raw() );
		$this->assertEquals(     '', $mock_response->get( 'X-ScriptName' )->raw() );
	} // It should route root apps correctly
	
	/**
	 * It should not squeeze slashes
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_squeeze_slashes()
	{
		$url_map = Prack_URLMap::with(
		  Prb::Hsh( array( 
		    '/' => self::appWithEval('
		      $this->headers->set( "Content-Type", Prb::Str( "text/plain" ) );
		      $this->headers->set( "X-Position",   Prb::Str( "root"       ) );
		      $this->headers->set( "X-PathInfo",   $env->get( "PATH_INFO"   )   );
		      $this->headers->set( "X-ScriptName", $env->get( "SCRIPT_NAME" )   );
		    '),
		    '/foo' => self::appWithEval('
		      $this->headers->set( "Content-Type", Prb::Str( "text/plain" ) );
		      $this->headers->set( "X-Position",   Prb::Str( "foo"        ) );
		      $this->headers->set( "X-PathInfo",   $env->get( "PATH_INFO"   )   );
		      $this->headers->set( "X-ScriptName", $env->get( "SCRIPT_NAME" )   );
		    '),
		  ) )
		);
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::Str( '/http://example.org/bar' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals(                    'root', $mock_response->get( 'X-Position'   )->raw() );
		$this->assertEquals( '/http://example.org/bar', $mock_response->get( 'X-PathInfo'   )->raw() );
		$this->assertEquals(                        '', $mock_response->get( 'X-ScriptName' )->raw() );
	} // It should not squeeze slashes
}