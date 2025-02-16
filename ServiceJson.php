<?php
/**
	@File Name 		:	Service.php
	@Author 		:	Ramanathan M <ramanathan@dss.com.sg>
	@Created Date	:	2015-12-21 10:55 AM
	@Description	:	Service of the portal. All the request will go through this file
*/

	ini_set("display_errors",0);

	ini_set("output_buffering", "4096");
	ini_set("implicit_flush", "1");
	ini_set("memory_limit", "10000M");
	ini_set("max_execution_time", "0");
	date_default_timezone_set("Asia/Calcutta");
	set_time_limit(600);

	require_once('lib/common/include.php');	// Required include file, which have all the files which is necessary for portal

	function process($_Ainput)
	{
		$_Ocontroller	= Controller::singleton();
		$_Aoutput		= $_Ocontroller->process($_Ainput);
		
		$_Joutput		= "[JRESP]".json_encode($_Aoutput)."[/JRESP]";
		
		echo $_Joutput;exit;
	}
	
	$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
	
	$_Ainput = array();
	if(!empty($HTTP_RAW_POST_DATA)){
		$_Ainput  = json_decode($HTTP_RAW_POST_DATA,true);
	}
	
	process($_Ainput);
?>