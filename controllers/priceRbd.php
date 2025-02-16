<?php
/**
	@File Name 		:	priceRbd.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	priceRbd service
*/
class priceRbd extends Execute
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
		$this->_SrequestUrl = $this->_Oconf['userSettings']['apiUrl']['priceRbd'];
	}
	
    public function _doPriceRbd()
	{
		$_Bstatus  = true;
		$_Smessage = '';
		$_Adata	   = array();
		$_Areturn  = array();
		
		$this->_modifyData();
		$this->_setData();
		
		$_IpaxCount			= $this->_Ainput['ADT'] + $this->_Ainput['CHD'];
		
		$_ApriceRbdResult = $this->_executeService();
		
		$_AupdatePrice = array();
		
		if(isset($_ApriceRbdResult['Response']['ResponseStatus']) && $_ApriceRbdResult['Response']['ResponseStatus'] == 1 && isset($_ApriceRbdResult['Response']['Results']) && count($_ApriceRbdResult['Response']['Results']) > 0){
			
			$_Adata = $_ApriceRbdResult['Response']['Results'][0][0];
		}
		else{
			$_Bstatus = false;
			
			if(isset($_ApriceRbdResult['Response']['Error']['ErrorMessage']) && !empty($_ApriceRbdResult['Response']['Error']['ErrorMessage'])){
				$_Smessage = $_ApriceRbdResult['Response']['Error']['ErrorMessage'];
			}
			else{
				$_Smessage = 'No flights found';
			}
		}
		
		$_Areturn = array
					(
						'status' => $_Bstatus,
						'data'   => $_Adata,
						'msg'    => $_Smessage,
					);
					
		return $_Areturn;
	}
}
?>