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
		$this->docroot = Prb::_String( dirname( __FILE__ ) );
		
		$this->app1 = Prack_File::with( $this->docroot );
		
		$this->app2 = Prack_URLMap::with(
		  Prb::_Hash( array(
		    '/crash' => new Prack_Test_Echo(
		      Prb::_Numeric( 200 ),
		      Prb::_Hash( array( 'Content-Type' => Prb::_String( 'text/plain' ) ) ),
		      Prb::_Array( array( Prb::_String( '' ) ) ),
		      'throw new Exception( "boom" );'
		    )
		  ) )
		);
		
		$this->app3 = Prack_URLMap::with(
		  Prb::_Hash( array(
		    '/foo' => new Prack_Test_Echo(
		      Prb::_Numeric( 200 ),
		      Prb::_Hash( array( 'Content-Type' => Prb::_String( 'text/plain' ) ) ),
		      Prb::_Array( array( Prb::_String( '' ) ) )
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
		  Prb::_Array( array(
		    $this->app1,
		    $this->app2,
		    $this->app3
		  ) )
		);
		
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::_String( '/cgi/test' ) );
		$this->assertTrue( $response->isOK() );
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::_String( '/foo' ) );
		$this->assertTrue( $response->isOK() );
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::_String( '/toobad' ) );
		$this->assertTrue( $response->isNotFound() );
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::_String( '/cgi/../bla' ) );
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
		  Prb::_Array( array(
		    $this->app1,
		    $this->app2,
		    $this->app3
		  ) ),
		  Prb::_Array( array( Prb::_Numeric( 404 ), Prb::_Numeric( 403 ) ) )
		);
		
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::_String( '/cgi/../bla' ) );
		$this->assertTrue( $response->isNotFound() );
	} // It should dispatch onward on whatever is passed
	
	/**
	 * It should return 404 if empty
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_404_if_empty()
	{
		$cascade  = Prack_Cascade::with( Prb::_Array() );
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::_String( '/' ) );
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
		  Prb::_Array(),
		  Prb::_Array( array( Prb::_Numeric( 404 ), Prb::_Numeric( 403 ) ) )
		);
		
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::_String( '/' ) );
		$this->assertTrue( $response->isNotFound() );
		
		$cascade->concat( $this->app2 );
		
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::_String( '/cgi/test' ) );
		$this->assertTrue( $response->isNotFound() );
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::_String( '/cgi/../test' ) );
		$this->assertTrue( $response->isNotFound() );
		
		$cascade->concat( $this->app1 );
		
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::_String( '/cgi/test' ) );
		$this->assertTrue( $response->isOK() );
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::_String( '/cgi/../test' ) );
		$this->assertTrue( $response->isForbidden() );
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::_String( '/foo' ) );
		$this->assertTrue( $response->isNotFound() );
		
		$cascade->concat( $this->app3 );
		
		$response = Prack_Mock_Request::with( $cascade )->get( Prb::_String( '/foo' ) );
		$this->assertTrue( $response->isOK() );
	} // It should append new app
}