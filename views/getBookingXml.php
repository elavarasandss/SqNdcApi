<?php
/**
	@File Name 		:	getBookingXml.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	getBookingXml input
*/

function getBookingXml($thisObj)
{
	$_Ainput 						= array();
		
	$_Ainput['EndUserIp'] 			= $thisObj->_Oconf['site']['endUserIp'];
	$_Ainput['TokenId'] 			= $thisObj->_Ssignature;
	$_Ainput['PNR'] 				= $thisObj->_Ainput['pnr'];
	$_Ainput['BookingId'] 			= $thisObj->_Ainput['bookingId'];
	
	return json_encode($_Ainput);
}
?>