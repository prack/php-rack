<?php

// TODO: Document!
class Prack_SendfileTest_Body
  implements Prb_I_Enumerable
{
	// TODO: Document!
	public function toPath()
	{
		return realpath( sys_get_temp_dir() ).'/hello.txt';
	}
	
	// TODO: Document!
	public function each( $callback )
	{
		foreach ( array( 'Hello World' ) as $part )
			call_user_func( $callback, $part );
	}
}

// TODO: Document!
class Prack_SendfileTest extends PHPUnit_Framework_TestCase 
{
	private $request;
	
	// TODO: Document!
	public function sendfileBody()
	{
		return new Prack_SendfileTest_Body();
	}
	
	// TODO: Document!
	public function simpleMiddlewareApp( $body = null )
	{
		if ( is_null( $body ) )
			$body = $this->sendfileBody();
		
		return new Prack_Test_Echo( 200, array( 'Content-Type' => 'text/plain' ), $body );
	}
	
	// TODO: Document!
	public function sendfileMiddlewareApp( $body = null )
	{
		if ( is_null( $body ) )
			$body = $this->sendfileBody();
		
		return Prack_Sendfile::with( $this->simpleMiddlewareApp( $body ) );
	}
	
	// TODO: Document!
	function setUp()
	{
		$this->request = Prack_Mock_Request::with( $this->sendfileMiddlewareApp() );
	}
	
	/**
	 * It does nothing when no X-Sendfile-Type header present
	 * @author Joshua Morris
	 * @test
	 */
	public function It_does_nothing_when_no_X_Sendfile_Type_header_present()
	{
		$response = $this->request->get( '/' );
		$this->assertTrue( $response->isOK() );
		$this->assertEquals( 'Hello World', $response->getBody() );
		$this->assertFalse( $response->contains( 'X-Sendfile' ) );
	} // It does nothing when no X-Sendfile-Type header present
	
	/**
	 * It sets X-Sendfile response header and discards body
	 * @author Joshua Morris
	 * @test
	 */
	public function It_sets_X_Sendfile_response_header_and_discards_body()
	{
		// Workaround, as realpath is the only thing we have to use for a canonical path.
		// ... and it returns false if the file doesn't exist.
		$expected_path = realpath( sys_get_temp_dir() ).'/hello.txt';
		touch( $expected_path );
		
		$response = $this->request->get( '/', array( 'HTTP_X_SENDFILE_TYPE' => 'X-Sendfile' ) );
		$this->assertTrue( $response->isOK() );
		$this->assertEquals( '', $response->getBody() );
		$this->assertEquals( $expected_path, $response->get( 'X-Sendfile' ) );
	} // It sets X-Sendfile response header and discards body
	
	/**
	 * It sets X-Lighttpd-Send-File response header and discards body
	 * @author Joshua Morris
	 * @test
	 */
	public function It_sets_X_Lighttpd_Send_File_response_header_and_discards_body()
	{
		// Workaround, as realpath is the only thing we have to use for a canonical path.
		// ... and it returns false if the file doesn't exist.
		$expected_path = realpath( sys_get_temp_dir() ).'/hello.txt';
		touch( $expected_path );
		
		$response = $this->request->get( '/', array( 'HTTP_X_SENDFILE_TYPE' => 'X-Lighttpd-Send-File' ) );
		$this->assertTrue( $response->isOK() );
		$this->assertEquals( '', $response->getBody() );
		$this->assertEquals( $expected_path, $response->get( 'X-Lighttpd-Send-File' ) );
	} // It sets X-Lighttpd-Send-File response header and discards body
	
	/**
	 * It sets X-Accel-Redirect response header and discards body
	 * @author Joshua Morris
	 * @test
	 */
	public function It_sets_X_Accel_Redirect_response_header_and_discards_body()
	{
		// Workaround, as realpath is the only thing we have to use for a canonical path.
		// ... and it returns false if the file doesn't exist.
		$tempdir       = realpath( sys_get_temp_dir() );
		$expected_path = $tempdir.'/hello.txt';
		touch( $expected_path );
		
		$headers = array(
		  'HTTP_X_SENDFILE_TYPE' => 'X-Accel-Redirect',
		  'HTTP_X_ACCEL_MAPPING' => $tempdir.'/=/foo/bar/'
		);
		
		$response = $this->request->get( '/', $headers );
		$this->assertTrue( $response->isOK() );
		$this->assertEquals( '', $response->getBody() );
		$this->assertEquals( '/foo/bar/hello.txt', $response->get( 'X-Accel-Redirect' ) );
	} // It sets X-Accel-Redirect response header and discards body
	
	/**
	 * It writes to rack.error when no X-Accel-Mapping is specified
	 * @author Joshua Morris
	 * @test
	 */
	public function It_writes_to_rack_error_when_no_X_Accel_Mapping_is_specified()
	{
		$response = $this->request->get( '/', array( 'HTTP_X_SENDFILE_TYPE' => 'X-Accel-Redirect' ) );
		$this->assertTrue( $response->isOK() );
		$this->assertEquals( 'Hello World', $response->getBody() );
		$this->assertNull( $response->get( 'X-Accel-Redirect' ) );
		$this->assertRegexp( '/X-Accel-Mapping/', $response->getErrors() );
	} // It writes to rack.error when no X-Accel-Mapping is specified
	
	/**
	 * It does nothing when body does not respond to toPath
	 * @author Joshua Morris
	 * @test
	 */
	public function It_does_nothing_when_body_does_not_respond_to_toPath()
	{
		$this->request = Prack_Mock_Request::with( $this->sendfileMiddlewareApp( array( 'Not a file...' ) ) );
		$response = $this->request->get( '/' );
		$this->assertEquals( 'Not a file...', $response->getBody() );
		$this->assertNull( $response->get( 'X-Sendfile' ) );
	} // It does nothing when body does not respond to toPath
}