<?php
/**
	@File Name 		:	getFareRules.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	getFareRules service
*/
class getFareRules extends Execute
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
		$this->_Ainput['flightTraceId']		= '';
		$this->_Ainput['flightResultIndex'] = '';
	}
	
    public function _doGetFareRules()
	{
		$_Bstatus	= true;
		$_Smessage	= '';
		$_Adata		= array();
		$_Areturn	= array();
		
		$this->_modifyData();
		
		$_BisSpecialReturn = false;
		
		foreach($this->_Ainput['flightDetails'] as $fKey=>$fVal){
			
			$_AjourneySellKey = explode("@@@",$fVal['journeySellKey']);
			
			if(isset($_AjourneySellKey[2]) && !empty($_AjourneySellKey[2])){
				$_AflightRefultIndex[] = $_AjourneySellKey[2];
			}
			
			if(isset($_AjourneySellKey[3]) && !empty($_AjourneySellKey[3])){
				$_SflightTraceId = $_AjourneySellKey[3];
			}
			
			if(isset($_AjourneySellKey[4]) && $_AjourneySellKey[4] == "Y"){
				$_BisSpecialReturn = true;
			}
		}
		
		$this->_Ainput['flightTraceId'] = $_SflightTraceId;
		
		$_AflightRefultIndex = array_unique($_AflightRefultIndex);
		
		if($_BisSpecialReturn && count($_AflightRefultIndex) > 0){
			$_AflightRefultIndex = array(implode(",",$_AflightRefultIndex));
		}
		
		$_IfareRuleCount	 = count($_AflightRefultIndex);
		
		$_AfareRuleResults = array();
		
		for($i=0;$i<$_IfareRuleCount;$i++){
			
			$this->_Ainput['flightResultIndex'] = $_AflightRefultIndex[$i];
			
			$_OfareRule				= controllerGet::getObject('fareRule',$this);				
			$_AfareRuleResponse	= $_OfareRule->_doFareRule();
			
			if(isset($_AfareRuleResponse['status']) && $_AfareRuleResponse['status'] && isset($_AfareRuleResponse['data']) && count($_AfareRuleResponse['data']) > 0){
				
				foreach($_AfareRuleResponse['data'] as $key=>$val){
					$_AfareRuleResults[] = $val;
				}
			}
			else{
				$_Bstatus	= false;
				$_Smessage .= $_AfareRuleResponse['msg'];
				$_AfareRuleResults = array();
				break;
			}
		}
		
		$_Areturn = array
					(
						'status' => $_Bstatus,
						'data'   => $_AfareRuleResults,
						'msg'    => $_Smessage,
					);
					
		return $_Areturn;
	}
}
?>