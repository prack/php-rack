<?php

// TODO: Document!
class Prack_FileTest extends PHPUnit_Framework_TestCase 
{
	// TODO: Document!
	static function docroot()
	{
		static $docroot = null;
		
		if ( is_null( $docroot ) )
			$docroot = join( DIRECTORY_SEPARATOR, array( dirname( __FILE__ ) ) );
		
		return $docroot;
	}
	
	/**
	 * It should serve files
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_serve_files()
	{
		$response = Prack_Mock_Request::with( Prack_File::with( self::docroot() ) )->get( '/cgi/test', null, array( 'lint' => true ) );
		$this->assertTrue( $response->isOK() );
		$this->assertTrue( $response->match( '/ruby/' ) );
	} // It should serve files
	
	/**
	 * It should set Last-Modified header
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_set_Last_Modified_header()
	{
		$response = Prack_Mock_Request::with(
		  new Prack_File( self::docroot() ) )->get( '/cgi/test', null, array( 'lint' => true ) );
		$path = join( DIRECTORY_SEPARATOR, array( self::docroot(), '/cgi/test' ) );
		
		$this->assertTrue( $response->isOK() );
		$this->assertEquals( Prb::Time( filemtime( $path ) )->httpdate(), $response->get( 'Last-Modified' ) );
	} // It should set Last-Modified header
	
	/**
	 * It should serve files with URL encoded filenames
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_serve_files_with_URL_encoded_filenames()
	{
		$response = Prack_Mock_Request::with(
		  new Prack_File( self::docroot() ) )->get( '/cgi/%74%65%73%74', null, array( 'lint' => true ) );
		$this->assertTrue( $response->isOK() );
		$this->assertTrue( $response->match( '/ruby/' ) );
	} // It should serve files with URL encoded filenames
	
	/**
	 * It should not allow directory traversal
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_allow_directory_traversal()
	{
		$response = Prack_Mock_Request::with(
		  new Prack_File( self::docroot() ) )->get( '/cgi/../test', null, array( 'lint' => true ) );
		$this->assertTrue( $response->isForbidden() );
	} // It should not allow directory traversal
	
	/**
	 * It should not allow directory traversal with encoded periods
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_allow_directory_traversal_with_encoded_periods()
	{
		$response = Prack_Mock_Request::with(
		  new Prack_File( self::docroot() ) )->get( '/%2E%2E/README', null, array( 'lint' => true ) );
		$this->assertTrue( $response->isForbidden() );
	} // It should not allow directory traversal with encoded periods
	
	/**
	 * It should 404 if it can't find the file
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_404_if_it_can_t_find_the_file()
	{
		$response = Prack_Mock_Request::with(
		  new Prack_File( self::docroot() ) )->get( '/cgi/blubb', null, array( 'lint' => true ) );
		$this->assertTrue( $response->isNotFound() );
	} // It should 404 if it can't find the file
	
	/**
	 * It should detect Prb_Exception_System
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_detect_Prb_Exception_System()
	{
		$response = Prack_Mock_Request::with(
		 new Prack_File( self::docroot() ) )->get( '/cgi', null, array( 'lint' => true ) );
		$this->assertTrue( $response->isNotFound() );
	} // It should detect Prb_Exception_System
	
	/**
	 * It should return bodies that respond to toPath
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_return_bodies_that_respond_to_toPath()
	{
		$env = Prack_Mock_Request::envFor( '/cgi/test' );
		list( $status, $headers, $body ) = Prack_File::with( self::docroot() )->call( $env );
		
		$path = join( '', array( self::docroot(), '/cgi/test' ) );
		$this->assertEquals( 200, $status );
		$this->assertTrue( method_exists( $body, 'toPath' ) );
		$this->assertEquals( $path, $body->toPath() );
	} // It should return bodies that respond to toPath
	
	/**
	 * It should respond to toPath
	 *
	 * The functionality tested here pertains to Prack_Sendfile.
	 *
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_respond_to_toPath()
	{
		$middleware_app = Prack_File::with( dirname( __FILE__ ) );
		$this->assertTrue( method_exists( $middleware_app, 'toPath' ) );
	} // It should respond to toPath
}