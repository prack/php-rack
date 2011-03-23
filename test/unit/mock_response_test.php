<?php

class Prack_Mock_ResponseTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * It should provide access to the HTTP status
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_provide_access_to_the_HTTP_status()
	{
		$mock_request  = new Prack_Mock_Request( new Prack_Test_EnvSerializer() );
		$mock_response = $mock_request->get( Prb::Str() );
		$this->assertTrue( $mock_response->isSuccessful() );
		$this->assertTrue( $mock_response->isOK() );
		
		$mock_response = $mock_request->get( Prb::Str( '/?status=50' ) );
		$this->assertTrue( $mock_response->isInvalid() );
		
		$mock_response = $mock_request->get( Prb::Str( '/?status=100' ) );
		$this->assertTrue( $mock_response->isInformational() );
		
		$mock_response = $mock_request->get( Prb::Str( '/?status=204' ) );
		$this->assertTrue( $mock_response->isEmpty() );
		
		$mock_response = $mock_request->get( Prb::Str( '/?status=403' ) );
		$this->assertTrue( $mock_response->isForbidden() );
		
		$mock_response = $mock_request->get( Prb::Str( '/?status=404' ) );
		$this->assertFalse( $mock_response->isSuccessful() );
		$this->assertTrue( $mock_response->isClientError() );
		$this->assertTrue( $mock_response->isNotFound() );
		
		$mock_response = $mock_request->get( Prb::Str( '/?status=501' ) );
		$this->assertFalse( $mock_response->isSuccessful() );
		$this->assertTrue( $mock_response->isServerError() );
		
		$mock_response = $mock_request->get( Prb::Str( '/?status=307' ) );
		$this->assertTrue( $mock_response->isRedirect() );
		$this->assertTrue( $mock_response->isRedirection() );
		
		$mock_response = $mock_request->get( Prb::Str( '/?status=201' ), Prb::Hsh( array( 'lint' => true ) ) );
		$this->assertTrue( $mock_response->isEmpty() );
	} // It should provide access to the HTTP status
	
	/**
	 * It should provide access to the HTTP headers
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_provide_access_to_the_HTTP_headers()
	{
		$mock_request  = new Prack_Mock_Request( new Prack_Test_EnvSerializer() );
		$mock_response = $mock_request->get( Prb::Str() );
		
		$original_headers = $mock_response->getOriginalHeaders();

		$this->assertTrue( $mock_response->contains( 'Content-Type' ) );
		$this->assertEquals( 'text/yaml', $mock_response->getHeaders()->get( 'Content-Type' )->raw() );
		$this->assertEquals( 'text/yaml', $original_headers->get( 'Content-Type' )->raw() );
		$this->assertEquals( 'text/yaml', $mock_response->get( 'Content-Type' )->raw() );
		$this->assertEquals( 'text/yaml', $mock_response->contentType()->raw() );
		$this->assertGreaterThanOrEqual( 0, $mock_response->contentLength() );
		$this->assertNull( $mock_response->location() );
	} // It should provide access to the HTTP headers
	
	/**
	 * It should provide access to the HTTP body
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_provide_access_to_the_HTTP_body()
	{
		$mock_request  = new Prack_Mock_Request( new Prack_Test_EnvSerializer() );
		$mock_response = $mock_request->get( Prb::Str() );
		
		$this->assertTrue( $mock_response->getBody()->match( '/rack/' ) );
		$this->assertTrue( $mock_response->match( '/rack/' ) );
	} // It should provide access to the HTTP body
	
	/**
	 * It should provide access to the Rack errors
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_provide_access_to_the_Rack_errors()
	{
		$mock_request  = new Prack_Mock_Request( new Prack_Test_EnvSerializer() );
		$mock_response = $mock_request->get(
		  Prb::Str( '/?error=foo' ),
		  Prb::Hsh( array( 'lint' => true ) )
		);
		
		$errors = $mock_response->getErrors();
	
		$this->assertTrue( $mock_response->isOK() );
		$this->assertFalse( $errors->isEmpty() );
		$this->assertTrue( $errors->contains( Prb::Str( 'foo' ) ) );
	} // It should provide access to the Rack errors
	
	/**
	 * It should optionally make Rack errors fatal
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_optionally_make_Rack_errors_fatal()
	{
		$mock_request  = new Prack_Mock_Request( new Prack_Test_EnvSerializer() );
		$mock_response = $mock_request->get(
			Prb::Str(),
			Prb::Hsh( array( 'fatal' => true ) )
		);
		
		$env = unserialize( $mock_response->getBody()->raw() );
		
		// Cheating for coverage:
		$mock_response->setErrors( $env->get( 'rack.errors' ) );
		$env->get( 'rack.errors' )->flush();
		$this->assertEquals( '', $env->get( 'rack.errors' )->string()->raw() );
		
		$this->setExpectedException( 'Prack_Exception_Mock_Response_FatalWarning' );
		$env->get( 'rack.errors' )->write( Prb::Str( 'Error 1' ) );
	} // It should optionally make Rack errors fatal
	
	/**
	 * It should optionally make Rack errors fatal (part 2)
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_optionally_make_Rack_errors_fatal__part_2_()
	{
		$mock_request  = new Prack_Mock_Request( new Prack_Test_EnvSerializer() );
		$mock_response = $mock_request->get(
			Prb::Str(),
			Prb::Hsh( array( 'fatal' => true ) )
		);
		
		$env = unserialize( $mock_response->getBody()->raw() );
		
		$this->setExpectedException( 'Prack_Exception_Mock_Response_FatalWarning' );
		$env->get( 'rack.errors' )->puts( Prb::Str( 'Error 2' ) );
	} // It should optionally make Rack errors fatal (part 2)
	
	/**
	 * It should throw an exception when an unknown method is called, on account of delegation
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_throw_an_exception_when_an_unknown_method_is_called__on_account_of_delegation()
	{
		$this->setExpectedException( 'Prb_Exception_Runtime_DelegationFailed' );
		$mock_request  = new Prack_Mock_Request( new Prack_Test_EnvSerializer() );
		$mock_response = $mock_request->get( Prb::Str( '/?error=foo' ), Prb::Hsh( array( 'lint' => true ) ) );
		$mock_response->foobar();
	} // It should throw an exception when an unknown method is called, on account of delegation
	
	/**
	 * It should throw an exception if body is neither a Prb_I_Stringlike or Prb_I_Enumerable
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_throw_an_exception_if_body_is_neither_a_Prb_I_Stringlike_or_Prb_I_Enumerable()
	{
		$this->setExpectedException( 'Prb_Exception_Type' );
		new Prack_Mock_Response( 200, Prb::Hsh(), 3 );
	} // It should throw an exception if body is neither a Prb_I_Stringlike or Prb_I_Enumerable
	
	/**
	 * It should throw an exception if headers is not a Prb_Hash
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_throw_an_exception_if_headers_is_not_a_Prb_Hash()
	{
		$this->setExpectedException( 'Prb_Exception_Type' );
		new Prack_Mock_Response( 200, array(), Prb::Str() );
	} // It should throw an exception if headers is not a Prb_Hash
}
