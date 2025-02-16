<?php
/**
	@File Name 		:	logonXml.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	logonXml input
*/

function logonXml($thisObj)
{
	$_Ainput 				= array();
		
	$_Ainput['ClientId'] 	= $thisObj->_Asettings['apiCredentials']['clientId'];
	$_Ainput['UserName'] 	= $thisObj->_Asettings['apiCredentials']['userName'];
	$_Ainput['Password'] 	= $thisObj->_Asettings['apiCredentials']['password'];
	$_Ainput['EndUserIp'] 	= $thisObj->_Oconf['site']['endUserIp'];
	
	return json_encode($_Ainput);
}
?>