<?php


// TODO: Document!
class Prack_URLMapTest extends PHPUnit_Framework_TestCase 
{
	// TODO: Document!
	static function appWithEval( $eval )
	{
		return new Prack_Test_Echo(
		  Prb::_Numeric( 200 ),
		  Prb::_Hash(),
		  Prb::_Array( array( Prb::_String() ) ),
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
		  Prb::_Hash( array(
		    'http://foo.org/bar' => $middleware_app,
		    '/foo'               => $middleware_app,
		    '/foo/bar'           => $middleware_app,
		  ) )
		);
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::_String( '/' ) );
		$this->assertTrue( $mock_response->isNotFound() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::_String( '/qux' ) );
		$this->assertTrue( $mock_response->isNotFound() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::_String( '/foo' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( '/foo', $mock_response->get( 'X-ScriptName' )->raw() );
		$this->assertEquals( ''    , $mock_response->get( 'X-PathInfo'   )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::_String( '/foo/' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( '/foo', $mock_response->get( 'X-ScriptName' )->raw() );
		$this->assertEquals( '/'   , $mock_response->get( 'X-PathInfo'   )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::_String( '/foo/bar' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( '/foo/bar', $mock_response->get( 'X-ScriptName' )->raw() );
		$this->assertEquals( ''        , $mock_response->get( 'X-PathInfo'   )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::_String( '/foo/bar/' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( '/foo/bar', $mock_response->get( 'X-ScriptName' )->raw() );
		$this->assertEquals( '/'       , $mock_response->get( 'X-PathInfo'   )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::_String( '/foo///bar//quux' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( '/foo/bar', $mock_response->get( 'X-ScriptName' )->raw() );
		$this->assertEquals( '//quux'  , $mock_response->get( 'X-PathInfo'   )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get(
		  Prb::_String( '/foo/quux' ),
		  Prb::_Hash( array( 'SCRIPT_NAME' => Prb::_String( '/bleh' ) ) )
		);
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( '/bleh/foo', $mock_response->get( 'X-ScriptName' )->raw() );
		$this->assertEquals( '/quux'    , $mock_response->get( 'X-PathInfo'   )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get(
		  Prb::_String( '/bar' ),
		  Prb::_Hash( array( 'HTTP_HOST' => Prb::_String( 'foo.org' ) ) )
		);
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( '/bar', $mock_response->get( 'X-ScriptName' )->raw() );
		$this->assertTrue( $mock_response->get( 'X-PathInfo' )->isEmpty() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get(
		  Prb::_String( '/bar/' ),
		  Prb::_Hash( array( 'HTTP_HOST' => Prb::_String( 'foo.org' ) ) )
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
		  Prb::_Hash( array( 
		    'http://foo.org/' => self::appWithEval('
		      $this->headers->set( "Content-Type", Prb::_String( "text/plain" ) );
		      $this->headers->set( "X-Position",   Prb::_String( "foo.org"    ) );
		      $this->headers->set( "X-Host",       $env->contains( "HTTP_HOST") ? $env->get( "HTTP_HOST"   )
		                                                                        : $env->get( "SERVER_NAME" ) );
		    '),
		    'http://subdomain.foo.org/' => self::appWithEval('
		      $this->headers->set( "Content-Type", Prb::_String( "text/plain"        ) );
		      $this->headers->set( "X-Position",   Prb::_String( "subdomain.foo.org" ) );
		      $this->headers->set( "X-Host",       $env->contains( "HTTP_HOST") ? $env->get( "HTTP_HOST"   )
		                                                                        : $env->get( "SERVER_NAME" ) );
		    '),
		    'http://bar.org/' => self::appWithEval('
		      $this->headers->set( "Content-Type", Prb::_String( "text/plain" ) );
		      $this->headers->set( "X-Position",   Prb::_String( "bar.org"    ) );
		      $this->headers->set( "X-Host",       $env->contains( "HTTP_HOST") ? $env->get( "HTTP_HOST"   )
		                                                                        : $env->get( "SERVER_NAME" ) );
		    '),
		    '/' => self::appWithEval('
		      $this->headers->set( "Content-Type", Prb::_String( "text/plain"  ) );
		      $this->headers->set( "X-Position",   Prb::_String( "default.org" ) );
		      $this->headers->set( "X-Host",       $env->contains( "HTTP_HOST" ) ? $env->get( "HTTP_HOST"   )
		                                                                         : $env->get( "SERVER_NAME" ) );
		    '),
		  ) )
		);
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::_String( '/' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( 'default.org', $mock_response->get( 'X-Position' )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get(
		  Prb::_String( '/' ),
		  Prb::_Hash( array( 'HTTP_HOST' => Prb::_String( 'bar.org' ) ) )
		);
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( 'bar.org', $mock_response->get( 'X-Position' )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get(
		  Prb::_String( '/' ),
		  Prb::_Hash( array( 'HTTP_HOST' => Prb::_String( 'foo.org' ) ) )
		);
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( 'foo.org', $mock_response->get( 'X-Position' )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get(
		  Prb::_String( '/' ),
		  Prb::_Hash( array(
		    'HTTP_HOST'   => Prb::_String( 'subdomain.foo.org' ),
		    'SERVER_NAME' => Prb::_String( 'foo.org'           )
		  ) )
		);
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( 'subdomain.foo.org', $mock_response->get( 'X-Position' )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::_String( 'http://foo.org' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( 'default.org', $mock_response->get( 'X-Position' )->raw() );
		
		//FAILS
		$mock_response = Prack_Mock_Request::with( $url_map )->get(
		  Prb::_String( '/' ),
		  Prb::_Hash( array( 'HTTP_HOST' => Prb::_String( 'example.org' ) ) )
		);
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( 'default.org', $mock_response->get( 'X-Position' )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get(
		  Prb::_String( '/' ),
		  Prb::_Hash( array(
		    'HTTP_HOST'   => Prb::_String( 'example.org:9292' ),
		    'SERVER_PORT' => Prb::_String( '9292'             )
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
		$url_map = Prack_URLMap::with( Prb::_Hash( array (
		  '/foo'  => Prack_URLMap::with( Prb::_Hash( array(
		    '/bar'  => Prack_URLMap::with( Prb::_Hash( array(
		      '/quux' => self::appWithEval('
		        $this->headers->set( "Content-Type", Prb::_String( "text/plain"    ) );
		        $this->headers->set( "X-Position",   Prb::_String( "/foo/bar/quux" ) );
		        $this->headers->set( "X-PathInfo",   $env->get( "PATH_INFO"   )        );
		        $this->headers->set( "X-ScriptName", $env->get( "SCRIPT_NAME" )        );
		      ')
		    ) ) )
		  ) ) )
		) ) );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::_String( '/foo/bar' ) );
		$this->assertTrue( $mock_response->isNotFound() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::_String( '/foo/bar/quux' ) );
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
		  Prb::_Hash( array( 
		    '/' => self::appWithEval('
		      $this->headers->set( "Content-Type", Prb::_String( "text/plain" ) );
		      $this->headers->set( "X-Position",   Prb::_String( "root"       ) );
		      $this->headers->set( "X-PathInfo",   $env->get( "PATH_INFO"   )     );
		      $this->headers->set( "X-ScriptName", $env->get( "SCRIPT_NAME" )     );
		    '),
		    '/foo' => self::appWithEval('
		      $this->headers->set( "Content-Type", Prb::_String( "text/plain" ) );
		      $this->headers->set( "X-Position",   Prb::_String( "foo"        ) );
		      $this->headers->set( "X-PathInfo",   $env->get( "PATH_INFO"   )     );
		      $this->headers->set( "X-ScriptName", $env->get( "SCRIPT_NAME" )     );
		    '),
		  ) )
		);
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::_String( '/foo/bar' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals(  'foo', $mock_response->get( 'X-Position'   )->raw() );
		$this->assertEquals( '/bar', $mock_response->get( 'X-PathInfo'   )->raw() );
		$this->assertEquals( '/foo', $mock_response->get( 'X-ScriptName' )->raw() );

		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::_String( '/foo' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals(  'foo', $mock_response->get( 'X-Position'   )->raw() );
		$this->assertEquals(     '', $mock_response->get( 'X-PathInfo'   )->raw() );
		$this->assertEquals( '/foo', $mock_response->get( 'X-ScriptName' )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::_String( '/bar' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals( 'root', $mock_response->get( 'X-Position'   )->raw() );
		$this->assertEquals( '/bar', $mock_response->get( 'X-PathInfo'   )->raw() );
		$this->assertEquals(     '', $mock_response->get( 'X-ScriptName' )->raw() );
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::_String() );
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
		  Prb::_Hash( array( 
		    '/' => self::appWithEval('
		      $this->headers->set( "Content-Type", Prb::_String( "text/plain" ) );
		      $this->headers->set( "X-Position",   Prb::_String( "root"       ) );
		      $this->headers->set( "X-PathInfo",   $env->get( "PATH_INFO"   )   );
		      $this->headers->set( "X-ScriptName", $env->get( "SCRIPT_NAME" )   );
		    '),
		    '/foo' => self::appWithEval('
		      $this->headers->set( "Content-Type", Prb::_String( "text/plain" ) );
		      $this->headers->set( "X-Position",   Prb::_String( "foo"        ) );
		      $this->headers->set( "X-PathInfo",   $env->get( "PATH_INFO"   )   );
		      $this->headers->set( "X-ScriptName", $env->get( "SCRIPT_NAME" )   );
		    '),
		  ) )
		);
		
		$mock_response = Prack_Mock_Request::with( $url_map )->get( Prb::_String( '/http://example.org/bar' ) );
		$this->assertTrue( $mock_response->isOK() );
		$this->assertEquals(                    'root', $mock_response->get( 'X-Position'   )->raw() );
		$this->assertEquals( '/http://example.org/bar', $mock_response->get( 'X-PathInfo'   )->raw() );
		$this->assertEquals(                        '', $mock_response->get( 'X-ScriptName' )->raw() );
	} // It should not squeeze slashes
}