<?php
/**
	@File Name 		:	getCalendarFare.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	getCalendarFare service
*/
class getCalendarFare extends Execute
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
		$this->_SrequestUrl = $this->_Oconf['userSettings']['apiUrl']['getCalendarFare'];
	}
	
    public function _doGetCalendarFare()
	{
		$_Bstatus  = true;
		$_Smessage = '';
		$_Adata	   = array();
		$_Areturn  = array();
		
		$this->_modifyData();
		$this->_setData();
		
		$_AcalendeFareResult= $this->_executeService();
		
		#echo "<pre>";print_r($_AcalendeFareResult);exit;
		
		$_AupdatePrice = array();
		
		if(isset($_AcalendeFareResult['Response']['ResponseStatus']) && $_AcalendeFareResult['Response']['ResponseStatus'] == 1 && isset($_AcalendeFareResult['Response']['SearchResults']) && count($_AcalendeFareResult['Response']['SearchResults']) > 0){
			
			$_Adata = $_AcalendeFareResult['Response']['SearchResults'];
			
			$_OupdateCalendarFare	= controllerGet::getObject('updateCalendarFare',$this);				
			$_AupdateCalendarFare	= $_OupdateCalendarFare->_doUpdateCalendarFare();
			
			$_Adata['updatedFare']  = $_AupdateCalendarFare;
			
			#echo "<pre>";print_r($_AupdateCalendarFare);exit;
		}
		else{
			$_Bstatus = false;
			
			if(isset($_AcalendeFareResult['Response']['Error']['ErrorMessage']) && !empty($_AcalendeFareResult['Response']['Error']['ErrorMessage'])){
				$_Smessage = $_AcalendeFareResult['Response']['Error']['ErrorMessage'];
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