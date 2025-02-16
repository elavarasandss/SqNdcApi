<?php
/**
	@File Name 		:	conf.inc.php
	@Author 		:	Ramanathan M <ramanathan@dss.com.sg>
	@Created Date	:	2015-12-21 10:55 AM
	@Description	:	Declaring the config variables basepath,sitepath and include config files.
*/

	global $CONF;
	
	//path define

	$basePath = $_SERVER['DOCUMENT_ROOT'].'/bridges/sqndcairline/';
	$host = '';
	if (isset($_SERVER['HTTP_HOST'])){
		$host = $_SERVER['HTTP_HOST'];
	} else if (isset($_SERVER['SERVER_NAME'])){
		$host = $_SERVER['SERVER_NAME'];
	}
	
	$sitePath = "http://".$host.substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/')+1);
	
	$CONF['path']['basePath'] = $basePath;
	$CONF['path']['sitePath'] = $sitePath;
	$CONF['site']['sessionKey'] = 'TBOAPI';
	
	session_name($CONF['site']['sessionKey']);
	session_start();

	set_include_path(get_include_path() . PATH_SEPARATOR . $CONF['path']['basePath']);
	

	require_once($CONF['path']['basePath'].'config/conf.site.php');
	require_once($CONF['path']['basePath'].'config/conf.database.php');
	require_once($CONF['path']['basePath'].'config/conf.table.php');
	require_once($CONF['path']['basePath'].'config/conf.account.php');
	require_once($CONF['path']['basePath'].'config/conf.api.php');
?>