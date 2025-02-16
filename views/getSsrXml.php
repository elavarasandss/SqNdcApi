<?php
/**
	@File Name 		:	getSsrXml.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	getSsrXml input
*/

function getSsrXml($thisObj)
{
	$_Ainput 						= array();
		
	$_Ainput['EndUserIp'] 			= $thisObj->_Oconf['site']['endUserIp'];
	$_Ainput['TokenId'] 			= $thisObj->_Ssignature;
	$_Ainput['TraceId'] 			= $thisObj->_Ainput['flightTraceId'];
	$_Ainput['ResultIndex'] 		= $thisObj->_Ainput['flightResultIndex'];
	
	return json_encode($_Ainput);
}
?>