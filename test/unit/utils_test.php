<?php


class Prack_UtilsTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * It should escape correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_escape_correctly()
	{
		$this->assertEquals( 'fo%3Co%3Ebar', Prack_Utils::singleton()->escape( Prb::_String( 'fo<o>bar' ) )->toN() );
		$this->assertEquals( 'a+space', Prack_Utils::singleton()->escape( Prb::_String( 'a space' ) )->toN() );
		$this->assertEquals( 'q1%212%22%27w%245%267%2Fz8%29%3F%5C',
		                     Prack_Utils::singleton()->escape( Prb::_String( "q1!2\"'w$5&7/z8)?\\" ) )->toN() );
		
	} // It should escape correctly
	
	/**
	 * It should escape correctly for multibyte characters
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_escape_correctly_for_multibyte_characters()
	{
		// FIXME: Implement unpack functionality on strings.
		$unpacked = unpack( 'a*', "\xE3\x81\xBE\xE3\x81\xA4\xE3\x82\x82\xE3\x81\xA8" ); // Matsumoto
		$this->assertEquals( Prb::_String( "%E3%81%BE%E3%81%A4%E3%82%82%E3%81%A8" ),
		                     Prack_Utils::singleton()->escape( Prb::_String( $unpacked[ 1 ] ) ) );
		$unpacked = unpack( 'a*', "\xE3\x81\xBE\xE3\x81\xA4 \xE3\x82\x82\xE3\x81\xA8" ); // Matsumoto
		$this->assertEquals( Prb::_String( "%E3%81%BE%E3%81%A4+%E3%82%82%E3%81%A8" ),
		                     Prack_Utils::singleton()->escape( Prb::_String( $unpacked[ 1 ] ) ) );
	} // It should escape correctly for multibyte characters
	
	/**
	 * It should unescape correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_unescape_correctly()
	{
		$this->assertEquals( 'fo<o>bar', Prack_Utils::singleton()->unescape( Prb::_String( 'fo%3Co%3Ebar' ) )->toN() );
		$this->assertEquals( 'a space', Prack_Utils::singleton()->unescape( Prb::_String( 'a+space' ) )->toN() );
		$this->assertEquals( 'a space', Prack_Utils::singleton()->unescape( Prb::_String( 'a%20space' ) )->toN() );
		$this->assertEquals( "q1!2\"'w$5&7/z8)?\\",
		                     Prack_Utils::singleton()->unescape( Prb::_String( 'q1%212%22%27w%245%267%2Fz8%29%3F%5C' ) )->toN() );
	} // It should unescape correctly
	
	/**
	 * It should parse query strings correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_parse_query_strings_correctly()
	{
		$this->assertEquals( array( 'foo' => Prb::_String( 'bar' ) ),
		                     Prack_Utils::singleton()->parseQuery( Prb::_String( 'foo=bar' ) )->toN() );
		$this->assertEquals( array( 'foo' => Prb::_String( '"bar"' ) ),
		                     Prack_Utils::singleton()->parseQuery( Prb::_String( 'foo="bar"' ) )->toN() );
		$this->assertEquals( array( 'foo' => Prb::_Array( Prb::_String( 'bar' ), Prb::_String( 'quux' ) ) ),
		                     Prack_Utils::singleton()->parseQuery( Prb::_String( 'foo=bar&foo=quux' ) )->toN() );
		$this->assertEquals( array( 'foo' => Prb::_String( '1' ), 'bar' => Prb::_String( '2' ) ),
		                     Prack_Utils::singleton()->parseQuery( Prb::_String( 'foo=1&bar=2' ) )->toN() );
		$this->assertEquals( array( 'my weird field' => Prb::_String( "q1!2\"'w$5&7/z8)?" ) ),
		                     Prack_Utils::singleton()->parseQuery( Prb::_String( 'my+weird+field=q1%212%22%27w%245%267%2Fz8%29%3F' ) )->toN() );
		$this->assertEquals( array( 'foo=baz' => Prb::_String( 'bar' ) ),
		                     Prack_Utils::singleton()->parseQuery( Prb::_String( 'foo%3Dbaz=bar' ) )->toN() );
	} // It should parse query strings correctly
	
	/**
	 * It should parse nested query strings correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_parse_nested_query_strings_correctly()
	{
		$this->assertEquals( Prb::_Hash( array( 'foo' => null ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::_String( 'foo' ) ) );
		$this->assertEquals( Prb::_Hash( array( 'foo' => Prb::_String() ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::_String( 'foo=' ) ) );
		$this->assertEquals( Prb::_Hash( array( 'foo' => Prb::_String( 'bar' ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::_String( 'foo=bar' ) ) );
		$this->assertEquals( Prb::_Hash( array( 'foo' => Prb::_String( '"bar"' ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::_String( 'foo="bar"' ) ) );
		
		$this->assertEquals( Prb::_Hash( array( 'foo' => Prb::_String( 'quux' ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::_String( 'foo=bar&foo=quux' ) ) );
		$this->assertEquals( Prb::_Hash( array( 'foo' => Prb::_String() ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::_String( 'foo&foo=' ) ) );
		$this->assertEquals( Prb::_Hash( array( 'foo' => Prb::_String( '1' ), 'bar' => Prb::_String( '2' ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::_String( 'foo=1&bar=2' ) ) );
		$this->assertEquals( Prb::_Hash( array( 'foo' => Prb::_String( '1' ), 'bar' => Prb::_String( '2' ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::_String( '&foo=1&bar=2' ) ) );
		$this->assertEquals( Prb::_Hash( array( 'foo' => null, 'bar' => Prb::_String() ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::_String( 'foo&bar=' ) ) );
		$this->assertEquals( Prb::_Hash( array( 'foo' => Prb::_String( 'bar' ), 'baz' => Prb::_String() ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::_String( 'foo=bar&baz=' ) ) );
		$this->assertEquals( Prb::_Hash( array( 'my weird field' => Prb::_String( "q1!2\"'w$5&7/z8)?" ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::_String( "my+weird+field=q1%212%22%27w%245%267%2Fz8%29%3F" ) ) );
		
		$this->assertEquals( Prb::_Hash( array( 'foo' => Prb::_Array( array( Prb::_String() ) ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::_String( "foo[]=" ) ) );
		$this->assertEquals( Prb::_Hash( array( 'foo' => Prb::_Array( array( Prb::_String( 'bar' ) ) ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::_String( "foo[]=bar" ) ) );
		
		$this->assertEquals( Prb::_Hash( array( 'foo' => Prb::_Array( array( Prb::_String( '1' ), Prb::_String( '2' ) ) ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::_String( "foo[]=1&foo[]=2" ) ) );
		$this->assertEquals(
		  Prb::_Hash( array(
		    'foo' => Prb::_String( 'bar' ),
		    'baz' => Prb::_Array( array(
		      Prb::_String( '1' ),
		      Prb::_String( '2' ),
		      Prb::_String( '3' )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::_String( 'foo=bar&baz[]=1&baz[]=2&baz[]=3' ) )
		);
		$this->assertEquals(
		  Prb::_Hash( array(
		    'foo' => Prb::_Array( array( Prb::_String( 'bar' ) ) ),
		    'baz' => Prb::_Array( array(
		      Prb::_String( '1' ),
		      Prb::_String( '2' ),
		      Prb::_String( '3' )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::_String( 'foo[]=bar&baz[]=1&baz[]=2&baz[]=3' ) )
		);
		
		$this->assertEquals(
		  Prb::_Hash( array(
		    'x' => Prb::_Hash( array(
		      'y' => Prb::_Hash( array(
		        'z' => Prb::_String( '1' )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::_String( 'x[y][z]=1' ) )
		);
		$this->assertEquals(
		  Prb::_Hash( array(
		    'x' => Prb::_Hash( array(
		      'y' => Prb::_Hash( array(
		        'z' => Prb::_Array( array(
		          Prb::_String( '1' )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::_String( 'x[y][z][]=1' ) )
		);
		$this->assertEquals(
		  Prb::_Hash( array(
		    'x' => Prb::_Hash( array(
		      'y' => Prb::_Hash( array(
		        'z' => Prb::_String( '2' )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::_String( 'x[y][z]=1&x[y][z]=2' ) )
		);
		$this->assertEquals(
		  Prb::_Hash( array(
		    'x' => Prb::_Hash( array(
		      'y' => Prb::_Hash( array(
		        'z' => Prb::_Array( array(
		          Prb::_String( '1' ), Prb::_String( '2' )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::_String( 'x[y][z][]=1&x[y][z][]=2' ) )
		);
		
		$this->assertEquals(
		  Prb::_Hash( array(
		    'x' => Prb::_Hash( array(
		      'y' => Prb::_Array( array(
		        Prb::_Hash( array( 'z' => Prb::_String( '1' ) ) )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::_String( 'x[y][][z]=1' ) )
		);
		$this->assertEquals(
		  Prb::_Hash( array(
		    'x' => Prb::_Hash( array(
		      'y' => Prb::_Array( array(
		        Prb::_Hash( array( 
		          'z' => Prb::_Array( array( 
		            Prb::_String( '1' )
		          ) )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::_String( 'x[y][][z][]=1' ) )
		);
		$this->assertEquals(
		  Prb::_Hash( array(
		    'x' => Prb::_Hash( array(
		      'y' => Prb::_Array( array(
		        Prb::_Hash( array( 
		          'z' => Prb::_String( '1' ),
		          'w' => Prb::_String( '2' )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::_String( 'x[y][][z]=1&x[y][][w]=2' ) )
		);
		
		$this->assertEquals(
		  Prb::_Hash( array(
		    'x' => Prb::_Hash( array(
		      'y' => Prb::_Array( array(
		        Prb::_Hash( array( 
		          'v' => Prb::_Hash( array(
		            'w' => Prb::_String( '1 ')
		          ) )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::_String( 'x[y][][v][w]=1' ) )
		);
		$this->assertEquals(
		  Prb::_Hash( array(
		    'x' => Prb::_Hash( array(
		      'y' => Prb::_Array( array(
		        Prb::_Hash( array( 
		          'z' => Prb::_String( '1' ),
		          'v' => Prb::_Hash( array( 
		            'w' => Prb::_String( '2' )
		          ) )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::_String( 'x[y][][z]=1&x[y][][v][w]=2' ) )
		);
		
		$this->assertEquals(
		  Prb::_Hash( array(
		    'x' => Prb::_Hash( array(
		      'y' => Prb::_Array( array(
		        Prb::_Hash( array( 
		          'z' => Prb::_String( '1' ),
		        ) ),
		        Prb::_Hash( array( 
		          'z' => Prb::_String( '2' ),
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::_String( 'x[y][][z]=1&x[y][][z]=2' ) )
		);
		$this->assertEquals(
		  Prb::_Hash( array(
		    'x' => Prb::_Hash( array(
		      'y' => Prb::_Array( array(
		        Prb::_Hash( array( 
		          'z' => Prb::_String( '1' ),
		          'w' => Prb::_String( 'a' )
		        ) ),
		        Prb::_Hash( array( 
		          'z' => Prb::_String( '2' ),
		          'w' => Prb::_String( '3' )
		        ) ),
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::_String( 'x[y][][z]=1&x[y][][w]=a&x[y][][z]=2&x[y][][w]=3' ) )
		);
		
		try
		{
			Prack_Utils::singleton()->parseNestedQuery( Prb::_String( 'x[y]=1&x[y]z=2' ) );
		} catch ( Prb_Exception_Type $e1 ) { }
		
		if ( isset( $e1 ) )
			$this->assertEquals( "expected Prb_Hash (got Prb_String) for param 'y'",
			                     $e1->getMessage() );
		else
			$this->fail( "Expecting exception on malformed query string: expected hash, got string." );
		
		try
		{
			Prack_Utils::singleton()->parseNestedQuery( Prb::_String( 'x[y]=1&x[]=1' ) );
		} catch ( Prb_Exception_Type $e2 ) { }
		
		if ( isset( $e2 ) )
			$this->assertEquals( "expected Prb_Array (got Prb_Hash) for param 'x'",
			                     $e2->getMessage() );
		else
			$this->fail( "Expecting exception on malformed query string: expected array, got hash." );
		
		try
		{
			Prack_Utils::singleton()->parseNestedQuery( Prb::_String( 'x[y]=1&x[y][][w]=2' ) );
		} catch ( Prb_Exception_Type $e3 ) { }
		
		if ( isset( $e3 ) )
			$this->assertEquals( "expected Prb_Array (got Prb_String) for param 'y'",
			                     $e3->getMessage() );
		else
			$this->fail( "Expecting exception on malformed query string: expected array, got string." );
	} // It should parse nested query strings correctly
	
	/**
	 * It should build query strings correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_build_query_strings_correctly()
	{
		$this->assertEquals(
		  'foo=bar',
		  Prack_Utils::singleton()->buildQuery(
		    Prb::_Hash( array( 'foo' => Prb::_String( 'bar' ) ) )
		  )->toN()
		);
		$this->assertEquals(
		  'foo=bar&foo=quux',
		  Prack_Utils::singleton()->buildQuery(
		    Prb::_Hash( array(
		      'foo' => Prb::_Array( array(
		        Prb::_String( 'bar'  ),
		        Prb::_String( 'quux' )
		      ) )
		    ) )
		  )->toN()
		);
		$this->assertEquals(
		  'my+weird+field=q1%212%22%27w%245%267%2Fz8%29%3F',
		  Prack_Utils::singleton()->buildQuery(
		    Prb::_Hash( array(
		      'my weird field' => Prb::_String( "q1!2\"'w$5&7/z8)?" )
		    ) )
		  )->toN()
		);
	} // It should build query strings correctly
	
	/**
	 * It should build nested query strings correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_build_nested_query_strings_correctly()
	{
		$this->assertEquals(
		  'foo',
		  Prack_Utils::singleton()->buildNestedQuery(
		    Prb::_Hash( array( 'foo' => null ) )
		  )->toN()
		);
		$this->assertEquals(
		  'foo=',
		  Prack_Utils::singleton()->buildNestedQuery(
		    Prb::_Hash( array( 'foo' => Prb::_String() ) )
		  )->toN()
		);
		$this->assertEquals(
		  'foo=bar',
		  Prack_Utils::singleton()->buildNestedQuery(
		    Prb::_Hash( array( 'foo' => Prb::_String( 'bar' ) ) )
		  )->toN()
		);
		
		$this->assertEquals(
		  'foo=1&bar=2',
		  Prack_Utils::singleton()->buildNestedQuery(
		    Prb::_Hash( array(
		      'foo' => Prb::_String( '1' ),
		      'bar' => Prb::_String( '2' )
		    ) )
		  )->toN()
		);
		$this->assertEquals(
		  'my+weird+field=q1%212%22%27w%245%267%2Fz8%29%3F',
		  Prack_Utils::singleton()->buildNestedQuery(
		    Prb::_Hash( array(
		      'my weird field' => Prb::_String( "q1!2\"'w$5&7/z8)?" ),
		    ) )
		  )->toN()
		);
		
		$this->assertEquals(
		  'foo[]',
		  Prack_Utils::singleton()->buildNestedQuery(
		    Prb::_Hash( array(
		      'foo' => Prb::_Array( array( null ) ),
		    ) )
		  )->toN()
		);
		$this->assertEquals(
		  'foo[]=',
		  Prack_Utils::singleton()->buildNestedQuery(
		    Prb::_Hash( array(
		      'foo' => Prb::_Array( array( Prb::_String() ) ),
		    ) )
		  )->toN()
		);
		$this->assertEquals(
		  'foo[]=bar',
		  Prack_Utils::singleton()->buildNestedQuery(
		    Prb::_Hash( array(
		      'foo' => Prb::_Array( array( Prb::_String( 'bar' ) ) ),
		    ) )
		  )->toN()
		);
		
		# Test that build_nested_query performs the inverse
		# function of parse_nested_query.
		foreach( array(
		  Prb::_Hash( array( 'foo' => null, 'bar' => Prb::_String() ) ),
		  Prb::_Hash( array( 'foo' => Prb::_String( 'bar' ), 'baz' => Prb::_String() ) ),
		  Prb::_Hash( array( 'foo' => Prb::_Array( array( Prb::_String() ) ) ) ),
		  Prb::_Hash( array( 'foo' => Prb::_Array( array( Prb::_String( 'bar' ) ) ) ) ),
		  Prb::_Hash( array(
		    'foo' => Prb::_String( 'bar' ),
		    'baz' => Prb::_Array( array(
		      Prb::_String( '1' ),
		      Prb::_String( '2' ),
		      Prb::_String( '3' )
		    ) )
		  ) ),
		  Prb::_Hash( array(
		    'foo' => Prb::_Array( array( Prb::_String( 'bar' ) ) ),
		    'baz' => Prb::_Array( array(
		      Prb::_String( '1' ),
		      Prb::_String( '2' ),
		      Prb::_String( '3' )
		    ) )
		  ) ),
		  Prb::_Hash( array( 'foo' => Prb::_Array( array( Prb::_String( '1' ), Prb::_String( '2' ) ) ) ) ),
		  Prb::_Hash( array(
		    'x' => Prb::_Hash( array(
		      'y' => Prb::_Hash( array(
		        'z' => Prb::_String( '1' )
		      ) )
		    ) )
		  ) ),
		  Prb::_Hash( array(
		    'x' => Prb::_Hash( array(
		      'y' => Prb::_Hash( array(
		        'z' => Prb::_Array( array(
		          Prb::_String( '1' )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prb::_Hash( array(
		    'x' => Prb::_Hash( array(
		      'y' => Prb::_Hash( array(
		        'z' => Prb::_Array( array(
		          Prb::_String( '1' ), Prb::_String( '2' )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prb::_Hash( array(
		    'x' => Prb::_Hash( array(
		      'y' => Prb::_Array( array(
		        Prb::_Hash( array( 'z' => Prb::_String( '1' ) ) )
		      ) )
		    ) )
		  ) ),
		  Prb::_Hash( array(
		    'x' => Prb::_Hash( array(
		      'y' => Prb::_Array( array(
		        Prb::_Hash( array( 
		          'z' => Prb::_Array( array( 
		            Prb::_String( '1' )
		          ) )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prb::_Hash( array(
		    'x' => Prb::_Hash( array(
		      'y' => Prb::_Array( array(
		        Prb::_Hash( array( 
		          'z' => Prb::_String( '1' ),
		          'w' => Prb::_String( '2' )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prb::_Hash( array(
		    'x' => Prb::_Hash( array(
		      'y' => Prb::_Array( array(
		        Prb::_Hash( array( 
		          'v' => Prb::_Hash( array(
		            'w' => Prb::_String( '1')
		          ) )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prb::_Hash( array(
		    'x' => Prb::_Hash( array(
		      'y' => Prb::_Array( array(
		        Prb::_Hash( array( 
		          'z' => Prb::_String( '1' ),
		          'v' => Prb::_Hash( array( 
		            'w' => Prb::_String( '2' )
		          ) )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prb::_Hash( array(
		    'x' => Prb::_Hash( array(
		      'y' => Prb::_Array( array(
		        Prb::_Hash( array( 
		          'z' => Prb::_String( '1' ),
		        ) ),
		        Prb::_Hash( array( 
		          'z' => Prb::_String( '2' ),
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prb::_Hash( array(
		    'x' => Prb::_Hash( array(
		      'y' => Prb::_Array( array(
		        Prb::_Hash( array( 
		          'z' => Prb::_String( '1' ),
		          'w' => Prb::_String( 'a' )
		        ) ),
		        Prb::_Hash( array( 
		          'z' => Prb::_String( '2' ),
		          'w' => Prb::_String( '3' )
		        ) ),
		      ) )
		    ) )
		  ) )
		) as $params ) {
			$query_string = Prack_Utils::singleton()->buildNestedQuery( $params );
			$this->assertEquals( $params, Prack_Utils::singleton()->parseNestedQuery( $query_string ) );
		}
		
		try
		{
			Prack_Utils::singleton()->buildNestedQuery( Prb::_string( 'foo=bar' ) );
		} catch ( Exception $e4 ) {}
		
		if ( isset( $e4 ) )
			$this->assertEquals( 'value must be a Prb_Hash', $e4->getMessage() );
		else
			$this->fail( "Expected exception on nested query building when query isn't nested." );
	} // It should build nested query strings correctly
}