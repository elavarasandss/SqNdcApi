<?php
/**
  @File Name  	: smartyConnect.php
  @Author 		:	Ramanathan M <rammrkv@gmail.com>
  @Created Date	:	2015-08-20 10:10 PM
  @Description	: Smarty object will 
*/
class smartyConnect extends Smarty {

    // Smarty connection constructor
    function __construct()
    {  
        parent::__construct();

		$this->compile_check = true;
		$this->debugging = false;
        $this->setTemplateDir($GLOBALS['CONF']['path']['basePath'].'views');
        $this->setCompileDir($GLOBALS['CONF']['path']['basePath'].'core/smarty/templates_c');
    }

    // Smarty singleton function
    public static function singleton()
    {
        static $instance;

        // If the instance is not there, create one
        if (!isset($instance)) {
            $instance = new smartyConnect();
        }
        return $instance;
    }
}
?>