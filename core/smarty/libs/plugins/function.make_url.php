<?php
/**
 * Smarty plugin
 * 
 * @package Smarty
 * @subpackage PluginsFunction
 */

/**
 * Smarty {make_url} function plugin
 * 
 * Type:     function<br>
 * Name:     make_url<br>
 * Purpose:  make redirect url dyanmically
 * 
 * @author vinoth <vinothvetrivel@gmail.com> 
 * @param  string $params 
 * @param  string $action 
 * @return string 
 * @uses smarty_function_escape_special_chars()
 */

function smarty_function_make_url($params)
{
	global $CONF;
	
	require_once(SMARTY_PLUGINS_DIR . 'shared.escape_special_chars.php');

	if(!empty($params['process']) && !empty($params['action'])){
		return $CONF['default']['webApplicationURL'].$CONF['default']['executeFile'].'/'.$params['process'].'/'.$params['action'];
	}
	elseif(!empty($params['process'])){
		return $CONF['default']['webApplicationURL'].$CONF['default']['executeFile'].'/'.$params['process'].'/';
	}
	else{
		return $CONF['default']['webApplicationURL'].$CONF['default']['executeFile'];
	}
}
?>
