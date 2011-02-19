<?php

interface Prack_Utils_IO_IReadable
{
	public function read( $length = null, &$buffer = null );
}