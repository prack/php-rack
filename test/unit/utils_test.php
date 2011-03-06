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
		$this->assertEquals( 'fo%3Co%3Ebar', Prack_Utils::singleton()->escape( Prack::_String( 'fo<o>bar' ) )->toN() );
		$this->assertEquals( 'a+space', Prack_Utils::singleton()->escape( Prack::_String( 'a space' ) )->toN() );
		$this->assertEquals( 'q1%212%22%27w%245%267%2Fz8%29%3F%5C',
		                     Prack_Utils::singleton()->escape( Prack::_String( "q1!2\"'w$5&7/z8)?\\" ) )->toN() );
		
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
		$this->assertEquals( Prack::_String( "%E3%81%BE%E3%81%A4%E3%82%82%E3%81%A8" ),
		                     Prack_Utils::singleton()->escape( Prack::_String( $unpacked[ 1 ] ) ) );
		$unpacked = unpack( 'a*', "\xE3\x81\xBE\xE3\x81\xA4 \xE3\x82\x82\xE3\x81\xA8" ); // Matsumoto
		$this->assertEquals( Prack::_String( "%E3%81%BE%E3%81%A4+%E3%82%82%E3%81%A8" ),
		                     Prack_Utils::singleton()->escape( Prack::_String( $unpacked[ 1 ] ) ) );
	} // It should escape correctly for multibyte characters
	
	/**
	 * It should unescape correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_unescape_correctly()
	{
		$this->assertEquals( 'fo<o>bar', Prack_Utils::singleton()->unescape( Prack::_String( 'fo%3Co%3Ebar' ) )->toN() );
		$this->assertEquals( 'a space', Prack_Utils::singleton()->unescape( Prack::_String( 'a+space' ) )->toN() );
		$this->assertEquals( 'a space', Prack_Utils::singleton()->unescape( Prack::_String( 'a%20space' ) )->toN() );
		$this->assertEquals( "q1!2\"'w$5&7/z8)?\\",
		                     Prack_Utils::singleton()->unescape( Prack::_String( 'q1%212%22%27w%245%267%2Fz8%29%3F%5C' ) )->toN() );
	} // It should unescape correctly
	
	/**
	 * It should parse query strings correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_parse_query_strings_correctly()
	{
		$this->assertEquals( array( 'foo' => Prack::_String( 'bar' ) ),
		                     Prack_Utils::singleton()->parseQuery( Prack::_String( 'foo=bar' ) )->toN() );
		$this->assertEquals( array( 'foo' => Prack::_String( '"bar"' ) ),
		                     Prack_Utils::singleton()->parseQuery( Prack::_String( 'foo="bar"' ) )->toN() );
		$this->assertEquals( array( 'foo' => Prack::_Array( Prack::_String( 'bar' ), Prack::_String( 'quux' ) ) ),
		                     Prack_Utils::singleton()->parseQuery( Prack::_String( 'foo=bar&foo=quux' ) )->toN() );
		$this->assertEquals( array( 'foo' => Prack::_String( '1' ), 'bar' => Prack::_String( '2' ) ),
		                     Prack_Utils::singleton()->parseQuery( Prack::_String( 'foo=1&bar=2' ) )->toN() );
		$this->assertEquals( array( 'my weird field' => Prack::_String( "q1!2\"'w$5&7/z8)?" ) ),
		                     Prack_Utils::singleton()->parseQuery( Prack::_String( 'my+weird+field=q1%212%22%27w%245%267%2Fz8%29%3F' ) )->toN() );
		$this->assertEquals( array( 'foo=baz' => Prack::_String( 'bar' ) ),
		                     Prack_Utils::singleton()->parseQuery( Prack::_String( 'foo%3Dbaz=bar' ) )->toN() );
	} // It should parse query strings correctly
	
	/**
	 * It should parse nested query strings correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_parse_nested_query_strings_correctly()
	{
		$this->assertEquals( Prack::_Hash( array( 'foo' => null ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prack::_String( 'foo' ) ) );
		$this->assertEquals( Prack::_Hash( array( 'foo' => Prack::_String() ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prack::_String( 'foo=' ) ) );
		$this->assertEquals( Prack::_Hash( array( 'foo' => Prack::_String( 'bar' ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prack::_String( 'foo=bar' ) ) );
		$this->assertEquals( Prack::_Hash( array( 'foo' => Prack::_String( '"bar"' ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prack::_String( 'foo="bar"' ) ) );
		
		$this->assertEquals( Prack::_Hash( array( 'foo' => Prack::_String( 'quux' ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prack::_String( 'foo=bar&foo=quux' ) ) );
		$this->assertEquals( Prack::_Hash( array( 'foo' => Prack::_String() ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prack::_String( 'foo&foo=' ) ) );
		$this->assertEquals( Prack::_Hash( array( 'foo' => Prack::_String( '1' ), 'bar' => Prack::_String( '2' ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prack::_String( 'foo=1&bar=2' ) ) );
		$this->assertEquals( Prack::_Hash( array( 'foo' => Prack::_String( '1' ), 'bar' => Prack::_String( '2' ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prack::_String( '&foo=1&bar=2' ) ) );
		$this->assertEquals( Prack::_Hash( array( 'foo' => null, 'bar' => Prack::_String() ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prack::_String( 'foo&bar=' ) ) );
		$this->assertEquals( Prack::_Hash( array( 'foo' => Prack::_String( 'bar' ), 'baz' => Prack::_String() ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prack::_String( 'foo=bar&baz=' ) ) );
		$this->assertEquals( Prack::_Hash( array( 'my weird field' => Prack::_String( "q1!2\"'w$5&7/z8)?" ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prack::_String( "my+weird+field=q1%212%22%27w%245%267%2Fz8%29%3F" ) ) );
		
		$this->assertEquals( Prack::_Hash( array( 'foo' => Prack::_Array( array( Prack::_String() ) ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prack::_String( "foo[]=" ) ) );
		$this->assertEquals( Prack::_Hash( array( 'foo' => Prack::_Array( array( Prack::_String( 'bar' ) ) ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prack::_String( "foo[]=bar" ) ) );
		
		$this->assertEquals( Prack::_Hash( array( 'foo' => Prack::_Array( array( Prack::_String( '1' ), Prack::_String( '2' ) ) ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prack::_String( "foo[]=1&foo[]=2" ) ) );
		$this->assertEquals(
		  Prack::_Hash( array(
		    'foo' => Prack::_String( 'bar' ),
		    'baz' => Prack::_Array( array(
		      Prack::_String( '1' ),
		      Prack::_String( '2' ),
		      Prack::_String( '3' )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prack::_String( 'foo=bar&baz[]=1&baz[]=2&baz[]=3' ) )
		);
		$this->assertEquals(
		  Prack::_Hash( array(
		    'foo' => Prack::_Array( array( Prack::_String( 'bar' ) ) ),
		    'baz' => Prack::_Array( array(
		      Prack::_String( '1' ),
		      Prack::_String( '2' ),
		      Prack::_String( '3' )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prack::_String( 'foo[]=bar&baz[]=1&baz[]=2&baz[]=3' ) )
		);
		
		$this->assertEquals(
		  Prack::_Hash( array(
		    'x' => Prack::_Hash( array(
		      'y' => Prack::_Hash( array(
		        'z' => Prack::_String( '1' )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prack::_String( 'x[y][z]=1' ) )
		);
		$this->assertEquals(
		  Prack::_Hash( array(
		    'x' => Prack::_Hash( array(
		      'y' => Prack::_Hash( array(
		        'z' => Prack::_Array( array(
		          Prack::_String( '1' )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prack::_String( 'x[y][z][]=1' ) )
		);
		$this->assertEquals(
		  Prack::_Hash( array(
		    'x' => Prack::_Hash( array(
		      'y' => Prack::_Hash( array(
		        'z' => Prack::_String( '2' )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prack::_String( 'x[y][z]=1&x[y][z]=2' ) )
		);
		$this->assertEquals(
		  Prack::_Hash( array(
		    'x' => Prack::_Hash( array(
		      'y' => Prack::_Hash( array(
		        'z' => Prack::_Array( array(
		          Prack::_String( '1' ), Prack::_String( '2' )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prack::_String( 'x[y][z][]=1&x[y][z][]=2' ) )
		);
		
		$this->assertEquals(
		  Prack::_Hash( array(
		    'x' => Prack::_Hash( array(
		      'y' => Prack::_Array( array(
		        Prack::_Hash( array( 'z' => Prack::_String( '1' ) ) )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prack::_String( 'x[y][][z]=1' ) )
		);
		$this->assertEquals(
		  Prack::_Hash( array(
		    'x' => Prack::_Hash( array(
		      'y' => Prack::_Array( array(
		        Prack::_Hash( array( 
		          'z' => Prack::_Array( array( 
		            Prack::_String( '1' )
		          ) )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prack::_String( 'x[y][][z][]=1' ) )
		);
		$this->assertEquals(
		  Prack::_Hash( array(
		    'x' => Prack::_Hash( array(
		      'y' => Prack::_Array( array(
		        Prack::_Hash( array( 
		          'z' => Prack::_String( '1' ),
		          'w' => Prack::_String( '2' )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prack::_String( 'x[y][][z]=1&x[y][][w]=2' ) )
		);
		
		$this->assertEquals(
		  Prack::_Hash( array(
		    'x' => Prack::_Hash( array(
		      'y' => Prack::_Array( array(
		        Prack::_Hash( array( 
		          'v' => Prack::_Hash( array(
		            'w' => Prack::_String( '1 ')
		          ) )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prack::_String( 'x[y][][v][w]=1' ) )
		);
		$this->assertEquals(
		  Prack::_Hash( array(
		    'x' => Prack::_Hash( array(
		      'y' => Prack::_Array( array(
		        Prack::_Hash( array( 
		          'z' => Prack::_String( '1' ),
		          'v' => Prack::_Hash( array( 
		            'w' => Prack::_String( '2' )
		          ) )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prack::_String( 'x[y][][z]=1&x[y][][v][w]=2' ) )
		);
		
		$this->assertEquals(
		  Prack::_Hash( array(
		    'x' => Prack::_Hash( array(
		      'y' => Prack::_Array( array(
		        Prack::_Hash( array( 
		          'z' => Prack::_String( '1' ),
		        ) ),
		        Prack::_Hash( array( 
		          'z' => Prack::_String( '2' ),
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prack::_String( 'x[y][][z]=1&x[y][][z]=2' ) )
		);
		$this->assertEquals(
		  Prack::_Hash( array(
		    'x' => Prack::_Hash( array(
		      'y' => Prack::_Array( array(
		        Prack::_Hash( array( 
		          'z' => Prack::_String( '1' ),
		          'w' => Prack::_String( 'a' )
		        ) ),
		        Prack::_Hash( array( 
		          'z' => Prack::_String( '2' ),
		          'w' => Prack::_String( '3' )
		        ) ),
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prack::_String( 'x[y][][z]=1&x[y][][w]=a&x[y][][z]=2&x[y][][w]=3' ) )
		);
		
		try
		{
			Prack_Utils::singleton()->parseNestedQuery( Prack::_String( 'x[y]=1&x[y]z=2' ) );
		} catch ( Prack_Error_Type $e1 ) { }
		
		if ( isset( $e1 ) )
			$this->assertEquals( "expected Prack_Wrapper_Hash (got Prack_Wrapper_String) for param 'y'",
			                     $e1->getMessage() );
		else
			$this->fail( "Expecting exception on malformed query string: expected hash, got string." );
		
		try
		{
			Prack_Utils::singleton()->parseNestedQuery( Prack::_String( 'x[y]=1&x[]=1' ) );
		} catch ( Prack_Error_Type $e2 ) { }
		
		if ( isset( $e2 ) )
			$this->assertEquals( "expected Prack_Wrapper_Array (got Prack_Wrapper_Hash) for param 'x'",
			                     $e2->getMessage() );
		else
			$this->fail( "Expecting exception on malformed query string: expected array, got hash." );
		
		try
		{
			Prack_Utils::singleton()->parseNestedQuery( Prack::_String( 'x[y]=1&x[y][][w]=2' ) );
		} catch ( Prack_Error_Type $e3 ) { }
		
		if ( isset( $e3 ) )
			$this->assertEquals( "expected Prack_Wrapper_Array (got Prack_Wrapper_String) for param 'y'",
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
		    Prack::_Hash( array( 'foo' => Prack::_String( 'bar' ) ) )
		  )->toN()
		);
		$this->assertEquals(
		  'foo=bar&foo=quux',
		  Prack_Utils::singleton()->buildQuery(
		    Prack::_Hash( array(
		      'foo' => Prack::_Array( array(
		        Prack::_String( 'bar'  ),
		        Prack::_String( 'quux' )
		      ) )
		    ) )
		  )->toN()
		);
		$this->assertEquals(
		  'my+weird+field=q1%212%22%27w%245%267%2Fz8%29%3F',
		  Prack_Utils::singleton()->buildQuery(
		    Prack::_Hash( array(
		      'my weird field' => Prack::_String( "q1!2\"'w$5&7/z8)?" )
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
		    Prack::_Hash( array( 'foo' => null ) )
		  )->toN()
		);
		$this->assertEquals(
		  'foo=',
		  Prack_Utils::singleton()->buildNestedQuery(
		    Prack::_Hash( array( 'foo' => Prack::_String() ) )
		  )->toN()
		);
		$this->assertEquals(
		  'foo=bar',
		  Prack_Utils::singleton()->buildNestedQuery(
		    Prack::_Hash( array( 'foo' => Prack::_String( 'bar' ) ) )
		  )->toN()
		);
		
		$this->assertEquals(
		  'foo=1&bar=2',
		  Prack_Utils::singleton()->buildNestedQuery(
		    Prack::_Hash( array(
		      'foo' => Prack::_String( '1' ),
		      'bar' => Prack::_String( '2' )
		    ) )
		  )->toN()
		);
		$this->assertEquals(
		  'my+weird+field=q1%212%22%27w%245%267%2Fz8%29%3F',
		  Prack_Utils::singleton()->buildNestedQuery(
		    Prack::_Hash( array(
		      'my weird field' => Prack::_String( "q1!2\"'w$5&7/z8)?" ),
		    ) )
		  )->toN()
		);
		
		$this->assertEquals(
		  'foo[]',
		  Prack_Utils::singleton()->buildNestedQuery(
		    Prack::_Hash( array(
		      'foo' => Prack::_Array( array( null ) ),
		    ) )
		  )->toN()
		);
		$this->assertEquals(
		  'foo[]=',
		  Prack_Utils::singleton()->buildNestedQuery(
		    Prack::_Hash( array(
		      'foo' => Prack::_Array( array( Prack::_String() ) ),
		    ) )
		  )->toN()
		);
		$this->assertEquals(
		  'foo[]=bar',
		  Prack_Utils::singleton()->buildNestedQuery(
		    Prack::_Hash( array(
		      'foo' => Prack::_Array( array( Prack::_String( 'bar' ) ) ),
		    ) )
		  )->toN()
		);
		
		# Test that build_nested_query performs the inverse
		# function of parse_nested_query.
		foreach( array(
		  Prack::_Hash( array( 'foo' => null, 'bar' => Prack::_String() ) ),
		  Prack::_Hash( array( 'foo' => Prack::_String( 'bar' ), 'baz' => Prack::_String() ) ),
		  Prack::_Hash( array( 'foo' => Prack::_Array( array( Prack::_String() ) ) ) ),
		  Prack::_Hash( array( 'foo' => Prack::_Array( array( Prack::_String( 'bar' ) ) ) ) ),
		  Prack::_Hash( array(
		    'foo' => Prack::_String( 'bar' ),
		    'baz' => Prack::_Array( array(
		      Prack::_String( '1' ),
		      Prack::_String( '2' ),
		      Prack::_String( '3' )
		    ) )
		  ) ),
		  Prack::_Hash( array(
		    'foo' => Prack::_Array( array( Prack::_String( 'bar' ) ) ),
		    'baz' => Prack::_Array( array(
		      Prack::_String( '1' ),
		      Prack::_String( '2' ),
		      Prack::_String( '3' )
		    ) )
		  ) ),
		  Prack::_Hash( array( 'foo' => Prack::_Array( array( Prack::_String( '1' ), Prack::_String( '2' ) ) ) ) ),
		  Prack::_Hash( array(
		    'x' => Prack::_Hash( array(
		      'y' => Prack::_Hash( array(
		        'z' => Prack::_String( '1' )
		      ) )
		    ) )
		  ) ),
		  Prack::_Hash( array(
		    'x' => Prack::_Hash( array(
		      'y' => Prack::_Hash( array(
		        'z' => Prack::_Array( array(
		          Prack::_String( '1' )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack::_Hash( array(
		    'x' => Prack::_Hash( array(
		      'y' => Prack::_Hash( array(
		        'z' => Prack::_Array( array(
		          Prack::_String( '1' ), Prack::_String( '2' )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack::_Hash( array(
		    'x' => Prack::_Hash( array(
		      'y' => Prack::_Array( array(
		        Prack::_Hash( array( 'z' => Prack::_String( '1' ) ) )
		      ) )
		    ) )
		  ) ),
		  Prack::_Hash( array(
		    'x' => Prack::_Hash( array(
		      'y' => Prack::_Array( array(
		        Prack::_Hash( array( 
		          'z' => Prack::_Array( array( 
		            Prack::_String( '1' )
		          ) )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack::_Hash( array(
		    'x' => Prack::_Hash( array(
		      'y' => Prack::_Array( array(
		        Prack::_Hash( array( 
		          'z' => Prack::_String( '1' ),
		          'w' => Prack::_String( '2' )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack::_Hash( array(
		    'x' => Prack::_Hash( array(
		      'y' => Prack::_Array( array(
		        Prack::_Hash( array( 
		          'v' => Prack::_Hash( array(
		            'w' => Prack::_String( '1')
		          ) )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack::_Hash( array(
		    'x' => Prack::_Hash( array(
		      'y' => Prack::_Array( array(
		        Prack::_Hash( array( 
		          'z' => Prack::_String( '1' ),
		          'v' => Prack::_Hash( array( 
		            'w' => Prack::_String( '2' )
		          ) )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack::_Hash( array(
		    'x' => Prack::_Hash( array(
		      'y' => Prack::_Array( array(
		        Prack::_Hash( array( 
		          'z' => Prack::_String( '1' ),
		        ) ),
		        Prack::_Hash( array( 
		          'z' => Prack::_String( '2' ),
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack::_Hash( array(
		    'x' => Prack::_Hash( array(
		      'y' => Prack::_Array( array(
		        Prack::_Hash( array( 
		          'z' => Prack::_String( '1' ),
		          'w' => Prack::_String( 'a' )
		        ) ),
		        Prack::_Hash( array( 
		          'z' => Prack::_String( '2' ),
		          'w' => Prack::_String( '3' )
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
			Prack_Utils::singleton()->buildNestedQuery( Prack::_string( 'foo=bar' ) );
		} catch ( Exception $e4 ) {}
		
		if ( isset( $e4 ) )
			$this->assertEquals( 'value must be a Prack_Wrapper_Hash', $e4->getMessage() );
		else
			$this->fail( "Expected exception on nested query building when query isn't nested." );
	} // It should build nested query strings correctly
}