<?php
/**
	@File Name 		:	controllerGet.php
	@Author 		:	Ramanathan M <rammrkv@gmail.com>
	@Created Date	:	2015-08-21 10:10 PM
	@Description	:	Cobtroller get process file will create instance for the required class
*/
class controllerGet
{
	public static function getObject($reqAction='',$thisObj,$instanceVal='NEW')
    {	
        if(!empty($reqAction)){
		
			if(isset($GLOBALS['CONF']['api']['actions'][$reqAction]['className'])){
				
				$className      	= $GLOBALS['CONF']['api']['actions'][$reqAction]['className'];
				$actionName     	= $GLOBALS['CONF']['api']['actions'][$reqAction]['actionName'];
				$reponseTagName 	= isset($GLOBALS['CONF']['api']['actions'][$reqAction]['reponseTagName']) ? $GLOBALS['CONF']['api']['actions'][$reqAction]['reponseTagName'] : '';
				$templateName   	= $GLOBALS['CONF']['api']['actions'][$reqAction]['templateName'];
				$headerTemplateName = isset($GLOBALS['CONF']['api']['actions'][$reqAction]['headerTpl']) ? $GLOBALS['CONF']['api']['actions'][$reqAction]['headerTpl'] : 'headerXml.tpl';
				
				fileInclude("controllers/{$className}.php");
				
				if(!empty($instanceVal) && $instanceVal == 'NEW'){
					$instance = new $className;
				}
				else{
					$instance = call_user_func($className.'::singleton');
				}
				
				$instance->_Osmarty 			= $thisObj->_Osmarty;
				$instance->_Odb 				= $thisObj->_Odb;
				$instance->_Oconf 				= $thisObj->_Oconf;
				
				$instance->_Ainput 				= $thisObj->_Ainput;
				$instance->_Asettings 			= $thisObj->_Asettings;
				$instance->_SclassName 			= $className;
				$instance->_SactionName 		= $actionName;
				$instance->_SreponseTagName 	= $reponseTagName;
				
				$instance->_IreferenceId 		= $thisObj->_IreferenceId;
				$instance->_Ssignature 		 	= $thisObj->_Ssignature;
				
				$instance->_StemplateName 		= $templateName;				
				$instance->_SheaderTemplateName = $headerTemplateName;

				return $instance;
			}
        }
        return false;
    }
}
?>