<?php
/**
	@File Name 		:	include.php
	@Author 		:	Ramanathan M <ramanathan@dss.com.sg>
	@Created Date	:	2015-12-21 10:55 AM
	@Description	:	Include all the required files
*/
	require_once "config/conf.inc.php";
	require_once "lib/common/logWrite.php";	
	require_once "lib/common/fileInclude.php";
	fileInclude("lib/common/Common.php");
	fileInclude("lib/common/class.nusoap.php");
	fileInclude("lib/common/class.wsdlcache.php");
	fileInclude('core/smarty/libs/SmartyModified.class.php');     // Smarty base class file
	fileInclude('lib/common/smartyConnect.php');			// Smarty connection file
	fileInclude('core/pear/DB.php');						// Pear DB connection file
	fileInclude('lib/common/DBConnect.php');
	fileInclude('lib/common/controllerGet.php');
	fileInclude('lib/common/model.php');
	fileInclude('lib/controller/Controller.php');
	fileInclude('lib/controller/Execute.php');
?>