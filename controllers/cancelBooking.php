<?php
/**
	@File Name 		:	cancelBooking.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	cancelBooking service
*/
class cancelBooking extends Execute
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
		$this->_SrequestUrl = $this->_Oconf['userSettings']['apiUrl']['cancelPnr'];
	}
	
    public function _doCancelBooking()
	{
		$_Bstatus  = true;
		$_Smessage = '';
		$_Adata	   = array();
		$_Areturn  = array();
		
		$this->_modifyData();
				
		$this->_Ainput['bookingId'] 		= 0;
		$this->_Ainput['bookingDetailsId']	= 0;
		/* Rubesh 26-02-2024
		// $_AbookingInfo = $this->getBookingDetails($this->_Ainput['pnr']);
		
		// if(isset($_AbookingInfo[0]['universal_pnr']) && !empty($_AbookingInfo[0]['universal_pnr'])){
			
		// 	$this->_Ainput['bookingId']			= $_AbookingInfo[0]['universal_pnr'];
		// 	$this->_Ainput['bookingDetailsId']	= $_AbookingInfo[0]['booking_details_id'];
		// }
		/* Rubesh  */
		$this->_setData();
		
		$_Aresult = $this->_executeService();
	
		$_AbookingResult = Common::xmlstrToArray($_Aresult);
		$_AbookingResult1 = $_AbookingResult['Body']['OrderCancelRS'];
		
    echo "<pre>";print_r($_AbookingResult);die;
		$_Aresponse = array();
		
		if(isset($_Aresult['Response']['ResponseStatus']) && $_Aresult['Response']['ResponseStatus'] == 1){
			
			$_Aresponse = $_Aresult['Response'];
			
			if(!empty($this->_Ainput['bookingDetailsId'])){
				
				$sqlUpdateStatus = "UPDATE booking_details SET booking_status = 3 WHERE booking_details_id = '".$this->_Ainput['bookingDetailsId']."' LIMIT 1";
			
				$this->_Odb->executeQuery($sqlUpdateStatus);
			}
		}
		else{
			$_Bstatus = false;
			
			if(isset($_Aresult['Response']['Error']['ErrorMessage']) && !empty($_Aresult['Response']['Error']['ErrorMessage'])){
				$_Smessage = $_Aresult['Response']['Error']['ErrorMessage'];
			}
			else{
				$_Smessage = 'Cancel Booking Error';
			}
		}
		
		$_Areturn = array
					(
						'status' => $_Bstatus,
						'data'   => $_Aresponse,
						'msg'    => $_Smessage,
					);
					
		return $_Areturn;
	}
}
?>	