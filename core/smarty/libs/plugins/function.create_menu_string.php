<?php
/**
 * Smarty plugin
 * 
 * @package Smarty
 * @subpackage PluginsFunction
 */

/**
 * Smarty {create_menu_string} function plugin
 * 
 * Type:     function<br>
 * Name:     create_menu_string<br>
 * Purpose:  Prints the list of menus <ul> and <li> tags generated from
 *            the passed parameters
 * 
 * @author Ramanathan <ramanathan@ebs.in> 
 * @param array $params parameters
 * Input:<br>
 *            - Menu input array - associative array
 *            - menu string
 *            - parent sub menu
 * @param object $template template object
 * @return string 
 * @uses smarty_function_escape_special_chars()
 */

function smarty_function_create_menu_string($givenArray,$template,&$stringVal="",$parentSubMenu='N')
{
	global $CONF;

	require_once(SMARTY_PLUGINS_DIR . 'shared.escape_special_chars.php');
	
	$menu=$givenArray['inputArray'];
	$checkReference=$givenArray['checkReference'];

	$submenuPos = isset($menu[0]['submenu_pos']) ? $menu[0]['submenu_pos'] : '';
	
	if($stringVal==""){
		$stringVal.="<ul class='menuzord-menu'>";
	} else{
		$stringVal.="<ul class='dropdown'>";
	}
	
	foreach ($menu as $key=>$value) 
	{
		if(isset($value['menu_name']))
			$name = $value['menu_name'];

		if(isset($value['submenu_name']))
			$name = $value['submenu_name'];
	
		if(isset($value['link'])){
			if(!empty($value['link']) && $value['link']!="#"){
				
				if(strpos($value['link'],'controllerInitScript') === false)
					$link = $CONF['path']['sitePath'].$CONF['site']['executeFile']."/".$value['link'];
				else
					$link = "javascript:".$value['link'].";";
			}
			else
				$link = "javascript:;";
		}

		if(isset($value['submenu_link'])){
			if(!empty($value['submenu_link']) && $value['submenu_link']!="#"){
				if(strpos($value['submenu_link'],'controllerInitScript') === false)
					$link = $CONF['path']['sitePath'].$CONF['site']['executeFile']."/".$value['submenu_link'];
				else
					$link = "javascript:".$value['submenu_link'].";";
			}
			else
				$link = "javascript:;";
		}
			
		$icon = '';
		if(isset($value['icon']))
			$icon = $value['icon'];

		if(isset($value['submenu_icon']))
			$icon = $value['submenu_icon'];


		if(isset($value['link']) && !isset($value['submenu_link'])){
			if(isset($value['submenuArray']))
				$stringVal.='<li><a href="'.$link.'">'.$name.'</a>';
			else
				$stringVal.='<li><a href="'.$link.'">'.$name.'</a>';
		}

		if(isset($value['submenu_link'])){
			if(isset($value['submenuArray']))
				$stringVal.='<li><a href="'.$link.'">'.$name.'</a>';
			else
				$stringVal.='<li><a href="'.$link.'">'.$name.'</a>';
		}

		if (isset($value['submenuArray']) && is_array($value['submenuArray'])){
		
			$parentSubmenu='N';
			if(isset($value['parentSubmenu']) && $value['parentSubmenu']=='Y'){
				$parentSubmenu='Y';
			}

			$passInput=array("inputArray"=>$value['submenuArray'],"checkReference"=>"N");
			smarty_function_create_menu_string($passInput,$template,$stringVal,$parentSubmenu);
		}

		$stringVal.="</li>";
	}

	
	$stringVal.="</ul>";
	if($checkReference=="Y")
	{
		//$stringVal.="</ul>";
	}
	if($checkReference=="Y")
	{
		return $stringVal;
	}
}
?>
