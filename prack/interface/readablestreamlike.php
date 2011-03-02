<?php

// TODO: Document!
interface Prack_Interface_ReadableStreamlike
{
	public function gets();
	public function read( $length = null, $buffer = null );
	public function each( $callback );
	public function rewind();
}