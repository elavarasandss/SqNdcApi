<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsModifier
 */

/**
 * Smarty truncate modifier plugin
 * Type:     modifier<br>
 * Name:     translate<br>
 * Purpose:  translate a string
 *
 * 
 * @author Vinoth < vinothvetrivel@gmail.com >
 *
 * @param string  $string      input string
 * *
 * @return string transalted string
 */
function smarty_modifier_translate($string='')
{   
    if(!empty($string))
    {
        global $lang;
        $keyArray   =   array_keys($lang);
        $key        =   end($keyArray);
        $str        =   str_replace(' ', '_', $string);
        $str        =   strtolower($str);
        $str        =   trim($str);
        $string     =   isset($lang[$key][$str])?$lang[$key][$str]:$string;
    }
    return $string;
}
