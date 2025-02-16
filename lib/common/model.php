<?php
/**
	@File Name 	:	model.php
	@Author 		:	Ramanathan M <rammrkv@gmail.com>
	@Created Date	:	2015-08-21 10:10 PM
	@Description:	Model process file will create instance for the required class
*/
class model
{
	public static function getObject($class='',$instanceVal='')
    {	
        if(!empty($class)){
		
        	fileInclude("models/".$class.".php");
	        
			if(!empty($instanceVal) && $instanceVal == 'NEW'){
				$instance = new $class;
			}
			else{
				$instance = call_user_func($class.'::singleton');
			}
			
			$instance->_Odb 			= DBConnect::singleton();
			$instance->_StableName 		= $GLOBALS['CONF']['table'][$class];
			$instance->_Oconf 			= $GLOBALS['CONF'];

			return $instance;
        }
        else{
        	return false;
        }
    }
}
?>