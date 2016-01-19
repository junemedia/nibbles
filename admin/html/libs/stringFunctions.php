<?php

//script functions library

/************** Put this function in Functions include file ***************/
function ascii_encode($string)  {
	for ($i=0; $i < strlen($string); $i++) {
		$encoded .= '&#'.ord(substr($string,$i)).';';
	}
	return $encoded;
}
/************** Put this function in Functions include file ***************/


/****** Function to encode Form Data into Hex to post it via socket ****/
/****  Used in Add Redirect script ****/
function hex_encode ($email_address)    {
	$encoded = bin2hex("$email_address");
	$encoded = chunk_split($encoded, 2, '%');
	$encoded = '%' . substr($encoded, 0, strlen($encoded) - 1);
	return $encoded;
}

/*****************************************/


?>