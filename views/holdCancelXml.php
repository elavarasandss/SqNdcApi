<?php
/**
	@File Name 		:	holdCancelXml.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	holdCancelXml input
*/

function holdCancelXml($thisObj)
{
	$_Ainput 						= array();
		
	$_Ainput['EndUserIp'] 			= $thisObj->_Oconf['site']['endUserIp'];
	$_Ainput['TokenId'] 			= $thisObj->_Ssignature;
	$_Ainput['PNR'] 				= $thisObj->_Ainput['pnr'];
	$_Ainput['BookingId'] 			= $thisObj->_Ainput['bookingId'];
	$_Ainput['Source'] 				= $thisObj->_Ainput['source'];
	
	return json_encode($_Ainput);
}
?>