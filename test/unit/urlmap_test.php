<?php


// TODO: Document!
class Prack_URLMapTest extends PHPUnit_Framework_TestCase 
{
	// TODO: Document!
	static function appWithEval( $eval )
	{
		return new Prack_Test_Echo( 200, array(), array( '' ), $eval );
	}
	
	/**
	 * It dispatches paths correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_dispatches_paths_correctly()
	{
		$middleware_app = self::appWithEval('
		  $this->headers[ "X-ScriptName" ] = $env[ "SCRIPT_NAME" ];
		  $this->headers[ "X-PathInfo"   ] = $env[ "PATH_INFO"   ];
		  $this->headers[ "X-ContentType"] = "text/plain";
		');
		
		$url_map = Prack_URLMap::with(
		  array(
		    'http://foo.org/bar' => $middleware_app,
		    '/foo'               => $middleware_app,
		    '/foo/bar'           => $middleware_app,
		  )
		);
		
		$response = Prack_Mock_Request::with( $url_map )->get( '/' );
		$this->assertTrue( $response->isNotFound() );
		
		$response = Prack_Mock_Request::with( $url_map )->get( '/qux' );
		$this->assertTrue( $response->isNotFound() );
		
		$response = Prack_Mock_Request::with( $url_map )->get( '/foo' );
		$this->assertTrue( $response->isOK() );
		$this->assertEquals( '/foo', $response->get( 'X-ScriptName' ) );
		$this->assertEquals( ''    , $response->get( 'X-PathInfo'   ) );
		
		$response = Prack_Mock_Request::with( $url_map )->get( '/foo/' );
		$this->assertTrue( $response->isOK() );
		$this->assertEquals( '/foo', $response->get( 'X-ScriptName' ) );
		$this->assertEquals( '/'   , $response->get( 'X-PathInfo'   ) );
		
		$response = Prack_Mock_Request::with( $url_map )->get( '/foo/bar' );
		$this->assertTrue( $response->isOK() );
		$this->assertEquals( '/foo/bar', $response->get( 'X-ScriptName' ) );
		$this->assertEquals( ''        , $response->get( 'X-PathInfo'   ) );
		
		$response = Prack_Mock_Request::with( $url_map )->get( '/foo/bar/' );
		$this->assertTrue( $response->isOK() );
		$this->assertEquals( '/foo/bar', $response->get( 'X-ScriptName' ) );
		$this->assertEquals( '/'       , $response->get( 'X-PathInfo'   ) );
		
		$response = Prack_Mock_Request::with( $url_map )->get( '/foo///bar//quux' );
		$this->assertTrue( $response->isOK() );
		$this->assertEquals( '/foo/bar', $response->get( 'X-ScriptName' ) );
		$this->assertEquals( '//quux'  , $response->get( 'X-PathInfo'   ) );
		
		$response = Prack_Mock_Request::with( $url_map )->get(
		  '/foo/quux', array( 'SCRIPT_NAME' => '/bleh' ) );
		
		$this->assertTrue( $response->isOK() );
		$this->assertEquals( '/bleh/foo', $response->get( 'X-ScriptName' ) );
		$this->assertEquals( '/quux'    , $response->get( 'X-PathInfo'   ) );
		
		$response = Prack_Mock_Request::with( $url_map )->get(
		  '/bar', array( 'HTTP_HOST' => 'foo.org' ) );
		
		$this->assertTrue( $response->isOK() );
		$this->assertEquals( '/bar', $response->get( 'X-ScriptName' ) );
		$this->assertEquals( '', $response->get( 'X-PathInfo' ) );
		
		$response = Prack_Mock_Request::with( $url_map )->get(
		  '/bar/', array( 'HTTP_HOST' => 'foo.org' ) );
		
		$this->assertTrue( $response->isOK() );
		$this->assertEquals( '/bar', $response->get( 'X-ScriptName' ) );
		$this->assertEquals( '/'   , $response->get( 'X-PathInfo'   ) );
	} // It dispatches paths correctly
	
	/**
	 * It dispatches hosts correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_dispatches_hosts_correctly()
	{
		$url_map = Prack_URLMap::with(
		  array( 
		    'http://foo.org/' => self::appWithEval('
		      $this->headers[ "Content-Type" ] = "text/plain";
		      $this->headers[ "X-Position"   ] = "foo.org";
		      $this->headers[ "X-Host"       ] = @$env[ "HTTP_HOST" ] ? $env[ "HTTP_HOST" ] : $env[ "SERVER_NAME" ];
		    '),
		    'http://subdomain.foo.org/' => self::appWithEval('
		      $this->headers[ "Content-Type" ] = "text/plain";
		      $this->headers[ "X-Position"   ] = "subdomain.foo.org";
		      $this->headers[ "X-Host"       ] = @$env[ "HTTP_HOST" ] ? $env[ "HTTP_HOST" ] : $env[ "SERVER_NAME" ];
		    '),
		    'http://bar.org/' => self::appWithEval('
		      $this->headers[ "Content-Type" ] = "text/plain";
		      $this->headers[ "X-Position"   ] = "bar.org";
		      $this->headers[ "X-Host"       ] = @$env[ "HTTP_HOST" ] ? $env[ "HTTP_HOST" ] : $env[ "SERVER_NAME" ];
		    '),
		    '/' => self::appWithEval('
		      $this->headers[ "Content-Type" ] = "text/plain";
		      $this->headers[ "X-Position"   ] = "default.org";
		      $this->headers[ "X-Host"       ] = @$env[ "HTTP_HOST" ] ? $env[ "HTTP_HOST" ] : $env[ "SERVER_NAME" ];
		    '),
		  )
		);
		
		$response = Prack_Mock_Request::with( $url_map )->get( '/' );
		$this->assertTrue( $response->isOK() );
		$this->assertEquals( 'default.org', $response->get( 'X-Position' ) );
		
		$response = Prack_Mock_Request::with( $url_map )->get( '/', array( 'HTTP_HOST' => 'bar.org' ) );
		$this->assertTrue( $response->isOK() );
		$this->assertEquals( 'bar.org', $response->get( 'X-Position' ) );
		
		$response = Prack_Mock_Request::with( $url_map )->get( '/', array( 'HTTP_HOST' => 'foo.org' ) );
		$this->assertTrue( $response->isOK() );
		$this->assertEquals( 'foo.org', $response->get( 'X-Position' ) );
		
		$response = Prack_Mock_Request::with( $url_map )->get(
		  '/', array( 'HTTP_HOST' => 'subdomain.foo.org', 'SERVER_NAME' => 'foo.org' ) );
		$this->assertTrue( $response->isOK() );
		$this->assertEquals( 'subdomain.foo.org', $response->get( 'X-Position' ) );
		
		$response = Prack_Mock_Request::with( $url_map )->get( 'http://foo.org' );
		$this->assertTrue( $response->isOK() );
		$this->assertEquals( 'default.org', $response->get( 'X-Position' ) );
		
		$response = Prack_Mock_Request::with( $url_map )->get(
		  '/', array( 'HTTP_HOST' => 'example.org' ) );
		$this->assertTrue( $response->isOK() );
		$this->assertEquals( 'default.org', $response->get( 'X-Position' ) );
		
		$response = Prack_Mock_Request::with( $url_map )->get(
		  '/', array( 'HTTP_HOST' => 'example.org:9292', 'SERVER_PORT' => '9292' ) );
		$this->assertTrue( $response->isOK() );
		$this->assertEquals( 'default.org', $response->get( 'X-Position' ) );
	} // It dispatches hosts correctly
	
	/**
	 * It should be nestable
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_nestable()
	{
		$url_map = Prack_URLMap::with( array (
		  '/foo'  => Prack_URLMap::with( array(
		    '/bar'  => Prack_URLMap::with( array(
		      '/quux' => self::appWithEval('
		        $this->headers[ "Content-Type" ] = "text/plain";
		        $this->headers[ "X-Position"   ] = "/foo/bar/quux";
		        $this->headers[ "X-PathInfo"   ] = $env[ "PATH_INFO"   ];
		        $this->headers[ "X-ScriptName" ] = $env[ "SCRIPT_NAME" ];
		      ')
		    ) )
		  ) )
		) );
		
		$response = Prack_Mock_Request::with( $url_map )->get( '/foo/bar' );
		$this->assertTrue( $response->isNotFound() );
		
		$response = Prack_Mock_Request::with( $url_map )->get( '/foo/bar/quux' );
		$this->assertTrue( $response->isOK() );
		$this->assertEquals( '/foo/bar/quux', $response->get( 'X-Position'   ) );
		$this->assertEquals(              '', $response->get( 'X-PathInfo'   ) );
		$this->assertEquals( '/foo/bar/quux', $response->get( 'X-ScriptName' ) );
	} // It should be nestable
	
	/**
	 * It should route root apps correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_route_root_apps_correctly()
	{
		$url_map = Prack_URLMap::with(
		  array( 
		    '/' => self::appWithEval('
		      $this->headers[ "Content-Type" ] = "text/plain";
		      $this->headers[ "X-Position"   ] = "root";
		      $this->headers[ "X-PathInfo"   ] = $env[ "PATH_INFO"   ];
		      $this->headers[ "X-ScriptName" ] = $env[ "SCRIPT_NAME" ];
		    '),
		    '/foo' => self::appWithEval('
		      $this->headers[ "Content-Type" ] = "text/plain";
		      $this->headers[ "X-Position"   ] = "foo";
		      $this->headers[ "X-PathInfo"   ] = $env[ "PATH_INFO"   ];
		      $this->headers[ "X-ScriptName" ] = $env[ "SCRIPT_NAME" ];
		    '),
		  )
		);
		
		$response = Prack_Mock_Request::with( $url_map )->get( '/foo/bar' );
		$this->assertTrue( $response->isOK() );
		$this->assertEquals(  'foo', $response->get( 'X-Position'   ) );
		$this->assertEquals( '/bar', $response->get( 'X-PathInfo'   ) );
		$this->assertEquals( '/foo', $response->get( 'X-ScriptName' ) );

		$response = Prack_Mock_Request::with( $url_map )->get( '/foo' );
		$this->assertTrue( $response->isOK() );
		$this->assertEquals(  'foo', $response->get( 'X-Position'   ) );
		$this->assertEquals(     '', $response->get( 'X-PathInfo'   ) );
		$this->assertEquals( '/foo', $response->get( 'X-ScriptName' ) );
		
		$response = Prack_Mock_Request::with( $url_map )->get( '/bar' );
		$this->assertTrue( $response->isOK() );
		$this->assertEquals( 'root', $response->get( 'X-Position'   ) );
		$this->assertEquals( '/bar', $response->get( 'X-PathInfo'   ) );
		$this->assertEquals(     '', $response->get( 'X-ScriptName' ) );
		
		$response = Prack_Mock_Request::with( $url_map )->get( '' );
		$this->assertTrue( $response->isOK() );
		$this->assertEquals( 'root', $response->get( 'X-Position'   ) );
		$this->assertEquals(    '/', $response->get( 'X-PathInfo'   ) );
		$this->assertEquals(     '', $response->get( 'X-ScriptName' ) );
	} // It should route root apps correctly
	
	/**
	 * It should not squeeze slashes
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_squeeze_slashes()
	{
		$url_map = Prack_URLMap::with(
		  array( 
		    '/' => self::appWithEval('
		      $this->headers[ "Content-Type" ] = "text/plain";
		      $this->headers[ "X-Position"   ] = "root";
		      $this->headers[ "X-PathInfo"   ] = $env[ "PATH_INFO"   ];
		      $this->headers[ "X-ScriptName" ] = $env[ "SCRIPT_NAME" ];
		    '),
		    '/foo' => self::appWithEval('
		      $this->headers[ "Content-Type" ] = "text/plain";
		      $this->headers[ "X-Position"   ] = "foo";
		      $this->headers[ "X-PathInfo"   ] = $env[ "PATH_INFO"   ];
		      $this->headers[ "X-ScriptName" ] = $env[ "SCRIPT_NAME" ];
		    '),
		  )
		);
		
		$response = Prack_Mock_Request::with( $url_map )->get( '/http://example.org/bar' );
		$this->assertTrue( $response->isOK() );
		$this->assertEquals(                    'root', $response->get( 'X-Position'   ) );
		$this->assertEquals( '/http://example.org/bar', $response->get( 'X-PathInfo'   ) );
		$this->assertEquals(                        '', $response->get( 'X-ScriptName' ) );
	} // It should not squeeze slashes
}