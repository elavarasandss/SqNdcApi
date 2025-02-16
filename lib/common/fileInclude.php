<?php
/**
	@File Name 		:	fileInclude.php
	@Author 		:	Ramanathan M <rammrkv@gmail.com>
	@Created Date	:	2015-08-20 10:10 PM
	@Description	:	Common function to include the files
*/
function fileInclude($fileName)
{
	if(file_exists($GLOBALS['CONF']['path']['basePath'].$fileName)){
		require_once $GLOBALS['CONF']['path']['basePath'].$fileName;
		return true;
	}
	else{
		logWrite($fileName,"FileIncludeError","a+");
	}
	return false;
}
?>