<?php
/**
	@File Name 		:	priceRbdXml.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	priceRbdXml input
*/

function priceRbdXml($thisObj)
{
	$_Ainput 						= array();
		
	$_Ainput['EndUserIp'] 			= $thisObj->_Oconf['site']['endUserIp'];
	$_Ainput['TokenId'] 			= $thisObj->_Ssignature;
	$_Ainput['TraceId'] 			= $thisObj->_Ainput['flightTraceId'];
	$_Ainput['AdultCount'] 			= $thisObj->_Ainput['ADT'];
	$_Ainput['ChildCount'] 			= $thisObj->_Ainput['CHD'];
	$_Ainput['InfantCount'] 		= $thisObj->_Ainput['INF'];
	$_Ainput['AirSearchResult'] 	= array($thisObj->_Ainput['priceRbd']);
	
	#echo "<pre>";print_r($_Ainput);exit;
	
	return json_encode($_Ainput);
}
?>