<?php

// TODO: Document!
class Prack_Auth_Digest_Nonce
{
	static $private_key = null;
	static $time_limit  = null;
	
	private $timestamp;
	private $given_digest;
	
	// TODO: Document!
	static function privateKey()
	{
		if ( is_null( self::$private_key ) )
			self::$private_key = '';
		
		return self::$private_key;
	}
	
	// TODO: Document!
	static function setPrivateKey( $pk )
	{
		self::$private_key = $pk;
	}
	
	// TODO: Document!
	static function timeLimit()
	{
		if ( is_null( self::$time_limit ) )
			self::$time_limit = null;
		
		return self::$time_limit;
	}
	
	// TODO: Document!
	static function setTimeLimit( $tl )
	{
		self::$time_limit = $tl;
	}
	
	// TODO: Document!
	static function parse( $string )
	{
		$split = @preg_split( '/ /', @base64_decode( $string ), 2 );
		return Prack_Auth_Digest_Nonce::with( Prb::Time( (int)$split[ 0 ] ), $split[ 1 ] );
	}
	
	// TODO: Document!
	static function with( $time = null, $given_digest = null )
	{
		return new Prack_Auth_Digest_Nonce( $time, $given_digest );
	}
	
	// TODO: Document!
	function __construct( $time = null, $given_digest = null )
	{
		if ( is_null( $time ) )
			$time = Prb::Time();
		
		$this->timestamp    = $time->getSeconds();
		$this->given_digest = $given_digest;
	}
	
	// TODO: Document!
	public function raw()
	{
		return base64_encode( join( ' ', array( (string)$this->timestamp, $this->digest() ) ) );
	}
	
	// TODO: Document!
	public function digest()
	{
		return md5( join( ':', array( $this->timestamp, self::privateKey() ) ) );
	}
	
	// TODO: Document!
	public function isValid()
	{
		return ( $this->digest() == $this->given_digest );
	}
	
	// TODO: Document!
	public function isStale()
	{
		$tl = self::timeLimit();
		return isset( $tl ) && ( $this->timestamp->raw() - Prb::Time()->getSeconds()->raw() < $tl->raw() );
	}
	
	// TODO: Document!
	public function isFresh()
	{
		return !( $this->isStale() );
	}
}
