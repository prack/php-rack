<?php

// TODO: Document!
class Prack_CascadeTest extends PHPUnit_Framework_TestCase 
{
	private $docroot;
	
	private $app1;
	private $app2;
	private $app3;
	
	// TODO: Document!
	public function setUp()
	{
		$this->docroot = Prb::Str( dirname( __FILE__ ) );
		
		$this->app1 = Prack_File::with( $this->docroot );
		
		$this->app2 = Prack_URLMap::with(
		  Prb::Hsh( array(
		    '/crash' => new Prack_Test_Echo(
		      Prb::Num( 200 ),
		      Prb::Hsh( array( 'Content-Type' => Prb::Str( 'text/plain' ) ) ),
		      Prb::Ary( array( Prb::Str( '' ) ) ),
		      'throw new Exception( "boom" );'
		    )
		  ) )
		);
		
		$this->app3 = Prack_URLMap::with(
		  Prb::Hsh( array(
		    '/foo' => new Prack_Test_Echo(
		      Prb::Num( 200 ),
		      Prb::Hsh( array( 'Content-Type' => Prb::Str( 'text/plain' ) ) ),
		      Prb::Ary( array( Prb::Str( '' ) ) )
		    )
		  ) )
		);
	}
	
	/**
	 * It should dispatch onward on 404 by default
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_dispatch_onward_on_404_by_default()
	{
		$cascade = Prack_Cascade::with(
		  Prb::Ary( array(
		    $this->app1,
		    $this->app2,
		    $this->app3
		  ) )
		);
		
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::Str( '/cgi/test' ) );
		$this->assertTrue( $response->isOK() );
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::Str( '/foo' ) );
		$this->assertTrue( $response->isOK() );
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::Str( '/toobad' ) );
		$this->assertTrue( $response->isNotFound() );
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::Str( '/cgi/../bla' ) );
		$this->assertTrue( $response->isForbidden() );
	} // It should dispatch onward on 404 by default
	
	/**
	 * It should dispatch onward on whatever is passed
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_dispatch_onward_on_whatever_is_passed()
	{
		$cascade = Prack_Cascade::with(
		  Prb::Ary( array(
		    $this->app1,
		    $this->app2,
		    $this->app3
		  ) ),
		  Prb::Ary( array( Prb::Num( 404 ), Prb::Num( 403 ) ) )
		);
		
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::Str( '/cgi/../bla' ) );
		$this->assertTrue( $response->isNotFound() );
	} // It should dispatch onward on whatever is passed
	
	/**
	 * It should return 404 if empty
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_404_if_empty()
	{
		$cascade  = Prack_Cascade::with( Prb::Ary() );
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::Str( '/' ) );
		$this->assertTrue( $response->isNotFound() );
	} // It should return 404 if empty
	
	/**
	 * It should append new app
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_append_new_app()
	{
		$cascade = Prack_Cascade::with(
		  Prb::Ary(),
		  Prb::Ary( array( Prb::Num( 404 ), Prb::Num( 403 ) ) )
		);
		
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::Str( '/' ) );
		$this->assertTrue( $response->isNotFound() );
		
		$cascade->concat( $this->app2 );
		
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::Str( '/cgi/test' ) );
		$this->assertTrue( $response->isNotFound() );
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::Str( '/cgi/../test' ) );
		$this->assertTrue( $response->isNotFound() );
		
		$cascade->concat( $this->app1 );
		
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::Str( '/cgi/test' ) );
		$this->assertTrue( $response->isOK() );
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::Str( '/cgi/../test' ) );
		$this->assertTrue( $response->isForbidden() );
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::Str( '/foo' ) );
		$this->assertTrue( $response->isNotFound() );
		
		$cascade->concat( $this->app3 );
		
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::Str( '/foo' ) );
		$this->assertTrue( $response->isOK() );
	} // It should append new app
}