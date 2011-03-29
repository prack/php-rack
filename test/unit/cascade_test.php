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
		$this->app1 = Prack_File::with( dirname( __FILE__ ) );
		$this->app2 = Prack_URLMap::with(
		  array( '/crash' => new Prack_Test_Echo( 200, array( 'Content-Type' => 'text/plain' ), array( '' ), 'throw new Exception( "boom" );' ) ) );
		$this->app3 = Prack_URLMap::with(
		  array( '/foo' => new Prack_Test_Echo( 200, array( 'Content-Type' => 'text/plain' ), array( '' ) ) ) );
	}
	
	/**
	 * It should dispatch onward on 404 by default
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_dispatch_onward_on_404_by_default()
	{
		$cascade = Prack_Cascade::with( array( $this->app1, $this->app2, $this->app3 ) );
		
		$response = Prack_Mock_Request::with( $cascade )->get( '/cgi/test' );
		$this->assertTrue( $response->isOK() );
		$response = Prack_Mock_Request::with( $cascade )->get( '/foo' );
		$this->assertTrue( $response->isOK() );
		$response = Prack_Mock_Request::with( $cascade )->get( '/toobad' );
		$this->assertTrue( $response->isNotFound() );
		$response = Prack_Mock_Request::with( $cascade )->get( '/cgi/../bla' );
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
		  array( $this->app1, $this->app2, $this->app3 ), array( 404, 403 )
		);
		
		$response = Prack_Mock_Request::with( $cascade )->get( '/cgi/../bla' );
		$this->assertTrue( $response->isNotFound() );
	} // It should dispatch onward on whatever is passed
	
	/**
	 * It should return 404 if empty
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_404_if_empty()
	{
		$cascade  = Prack_Cascade::with( array() );
		$response = Prack_Mock_Request::with( $cascade )->get( '/' );
		$this->assertTrue( $response->isNotFound() );
	} // It should return 404 if empty
	
	/**
	 * It should append new app
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_append_new_app()
	{
		$cascade = Prack_Cascade::with( array(), array( 404, 403 ) );
		
		$response = Prack_Mock_Request::with( $cascade )->get( '/' );
		$this->assertTrue( $response->isNotFound() );
		
		$cascade->concat( $this->app2 );
		
		$response = Prack_Mock_Request::with( $cascade )->get( '/cgi/test' );
		$this->assertTrue( $response->isNotFound() );
		$response = Prack_Mock_Request::with( $cascade )->get( '/cgi/../test' );
		$this->assertTrue( $response->isNotFound() );
		
		$cascade->concat( $this->app1 );
		
		$response = Prack_Mock_Request::with( $cascade )->get( '/cgi/test' );
		$this->assertTrue( $response->isOK() );
		$response = Prack_Mock_Request::with( $cascade )->get( '/cgi/../test' );
		$this->assertTrue( $response->isForbidden() );
		$response = Prack_Mock_Request::with( $cascade )->get( '/foo' );
		$this->assertTrue( $response->isNotFound() );
		
		$cascade->concat( $this->app3 );
		
		$response = Prack_Mock_Request::with( $cascade )->get( '/foo' );
		$this->assertTrue( $response->isOK() );
	} // It should append new app
}