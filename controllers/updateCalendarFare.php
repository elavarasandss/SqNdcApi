<?php
/**
	@File Name 		:	updateCalendarFare.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	updateCalendarFare service
*/
class updateCalendarFare extends Execute
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
		$this->_SrequestUrl = $this->_Oconf['userSettings']['apiUrl']['updateCalendarFare'];
	}
	
    public function _doUpdateCalendarFare()
	{
		$_Bstatus  = true;
		$_Smessage = '';
		$_Adata	   = array();
		$_Areturn  = array();
		
		$this->_modifyData();
		$this->_setData();
		
		$_AupdateCalendeFareResult = $this->_executeService();
		
		$_AupdatePrice = array();
		
		if(isset($_AupdateCalendeFareResult['Response']['ResponseStatus']) && $_AupdateCalendeFareResult['Response']['ResponseStatus'] == 1 && isset($_AupdateCalendeFareResult['Response']['SearchResults']) && count($_AupdateCalendeFareResult['Response']['SearchResults']) > 0){
			
			$_Adata = $_AupdateCalendeFareResult['Response']['SearchResults'];
		}
		else{
			$_Bstatus = false;
			
			if(isset($_AupdateCalendeFareResult['Response']['Error']['ErrorMessage']) && !empty($_AupdateCalendeFareResult['Response']['Error']['ErrorMessage'])){
				$_Smessage = $_AupdateCalendeFareResult['Response']['Error']['ErrorMessage'];
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