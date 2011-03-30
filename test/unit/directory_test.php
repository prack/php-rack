<?php

// TODO: Document!
class Prack_DirectoryTest_FileCatch
  implements Prack_I_MiddlewareApp
{
	// TODO: Document!
	public function call( &$env )
	{
		return array( 200, array( 'Content-Type' => 'text/plain', 'Content-Length' => '7' ), array( 'passed!' ) );
	}
}

// TODO: Document!
class Prack_DirectoryTest extends PHPUnit_Framework_TestCase 
{
	// TODO: Document!
	static function docroot()
	{
		static $docroot = null;
		
		if ( is_null( $docroot ) )
			$docroot = realpath( dirname( __FILE__ ) );
		
		return $docroot;
	}
	
	// TODO: Document!
	static function fileCatch()
	{
		return new Prack_DirectoryTest_FileCatch();
	}
	
	// TODO: Document!
	static function middlewareApp()
	{
		return Prack_Directory::with( self::docroot(), self::fileCatch() );
	}
	
	/**
	 * It should serve directory indices
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_serve_directory_indices()
	{
		$response = Prack_Mock_Request::with(
			new Prack_Lint( self::middlewareApp() ) )->get( '/cgi/' );
		
		$this->assertTrue( $response->isOK() );
		$this->assertRegExp( '/<html><head>/', $response->getBody() );
	} // It should serve directory indices
	
	/**
	 * It should pass to app if file found
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_pass_to_app_if_file_found()
	{
		$response = Prack_Mock_Request::with(
			new Prack_Lint( self::middlewareApp() ) )->get( '/cgi/test' );
		
		$this->assertTrue( $response->isOK() );
		$this->assertRegExp( '/passed!/', $response->getBody() );
	} // It should pass to app if file found
	
	/**
	 * It should serve uri with URL encoded filenames
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_serve_uri_with_URL_encoded_filenames()
	{
		$response = Prack_Mock_Request::with(
			new Prack_Lint( self::middlewareApp() ) )->get( '/%63%67%69/' );
		
		$this->assertTrue( $response->isOK() );
		$this->assertRegExp( '/<html><head>/', $response->getBody() );
		
		$response = Prack_Mock_Request::with(
			new Prack_Lint( self::middlewareApp() ) )->get( '/cgi/%74%65%73%74' );
		
		$this->assertTrue( $response->isOK() );
		$this->assertRegExp( '/passed!/', $response->getBody() );
	} // It should serve uri with URL encoded filenames
	
	/**
	 * It should not allow directory traversal
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_not_allow_directory_traversal()
	{
		$response = Prack_Mock_Request::with(
			new Prack_Lint( self::middlewareApp() ) )->get( '/cgi/../test' );
		
		$this->assertTrue( $response->isForbidden() );
		
		$response = Prack_Mock_Request::with(
			new Prack_Lint( self::middlewareApp() ) )->get( '/cgi/%2E%2E/test' );
		
		$this->assertTrue( $response->isForbidden() );
	} // It should not allow directory traversal
	
	/**
	 * It should 404 if it can't find the file
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_404_if_it_can_t_find_the_file()
	{
		$response = Prack_Mock_Request::with(
			new Prack_Lint( self::middlewareApp() ) )->get( '/cgi/blubb' );
		
		$this->assertTrue( $response->isNotFound() );
	} // It should 404 if it can't find the file
}