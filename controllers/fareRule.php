<?php
/**
	@File Name 		:	fareRule.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	fareRule service
*/
class fareRule extends Execute
{
	
	function __construct()
	{
		parent::__construct();
	}
	
	public static function &singleton()
    {
        static $instance;

        // If the instance is not there, create one
        if (!isset($instance)) {
            $class = __CLASS__;
            $instance = new $class();
        }
        return $instance;
    }
	
	public function _modifyData()
	{
		$this->_SrequestUrl = $this->_Oconf['userSettings']['apiUrl']['fareRule'];
	}
	
    public function _doFareRule()
	{
		$_Bstatus  = true;
		$_Smessage = '';
		$_Adata	   = array();
		$_Areturn  = array();
		
		$this->_modifyData();
		$this->_setData();
		
		$_AfareRuleResult = $this->_executeService();
		
		$_AfareRules = array();
		
		if(isset($_AfareRuleResult['Response']['ResponseStatus']) && $_AfareRuleResult['Response']['ResponseStatus'] == 1 && isset($_AfareRuleResult['Response']['FareRules']) && !empty($_AfareRuleResult['Response']['FareRules'])){
			$_AfareRules = $_AfareRuleResult['Response']['FareRules'];
		}
		else{
			$_Bstatus = false;
			
			if(isset($_AfareRuleResult['Response']['Error']['ErrorMessage']) && !empty($_AfareRuleResult['Response']['Error']['ErrorMessage'])){
				$_Smessage = $_AfareRuleResult['Response']['Error']['ErrorMessage'];
			}
			else{
				$_Smessage = 'No fare rules found';
			}
		}
		
		$_Areturn = array
					(
						'status' => $_Bstatus,
						'data'   => $_AfareRules,
						'msg'    => $_Smessage,
					);
					
		return $_Areturn;
	}
}
?>