<?php

// TODO: Document!
class Prack_ArachnidTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * It should look like a spider
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_look_like_a_spider()
	{
		$response = Prack_Mock_Request::with( new Prack_Arachnid() )->get( '/' );
		$upright_spider = preg_quote( '._| :H: |\'-', '/' );
		
		$this->assertTrue( $response->isOK() );
		$this->assertRegExp( "/{$upright_spider}/", $response->getBody() );
		$this->assertRegExp( '/\?flip/', $response->getBody() );
		$this->assertRegExp( '/crash/', $response->getBody() );
	} // It should look like a spider
	
	/**
	 * It should be flippable
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_be_flippable()
	{
		$response = Prack_Mock_Request::with( new Prack_Arachnid() )->get( '/?flip=left' );
		$flipped_spider = preg_quote( '-\'| :H: |_.', '/' );

		$this->assertTrue( $response->isOK() );
		$this->assertRegExp( "/{$flipped_spider}/", $response->getBody() );
	} // It should be flippable
	
	/**
	 * It should provide crashing for testing purposes
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_provide_crashing_for_testing_purposes()
	{
		$this->setExpectedException( 'Exception' );
		$response = Prack_Mock_Request::with( new Prack_Arachnid() )->get( '/?flip=crash' );
	} // It should provide crashing for testing purposes
}

/*

// No lambda apps until 5.3. :(

describe Rack::Lobster::LambdaLobster do
  should "be a single lambda" do
    Rack::Lobster::LambdaLobster.should.be.kind_of Proc
  end

  should "look like a lobster" do
    res = Rack::MockRequest.new(Rack::Lobster::LambdaLobster).get("/")
    res.should.be.ok
    res.body.should.include "(,(,,(,,,("
    res.body.should.include "?flip"
  end

  should "be flippable" do
    res = Rack::MockRequest.new(Rack::Lobster::LambdaLobster).get("/?flip")
    res.should.be.ok
    res.body.should.include "(,,,(,,(,("
  end
end

*/