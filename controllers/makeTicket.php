<?php
/**
	@File Name 		:	makeTicket.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	makeTicket service
*/
class makeTicket extends Execute
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
	
    public function _doMakeTicket()
	{
		$_Bstatus	= true;
		$_Smessage	= '';
		$_Adata		= array();
		$_Areturn	= array();
		
		$this->_modifyData();
		
		$this->_Ainput['bookingId']			= 0;
		$this->_Ainput['bookingDetailsId']	= 0;
		
		$_AbookingInfo = $this->getBookingDetails($this->_Ainput['pnr']);
		
		if(isset($_AbookingInfo[0]['universal_pnr']) && !empty($_AbookingInfo[0]['universal_pnr'])){
			
			$this->_Ainput['bookingId']			= $_AbookingInfo[0]['universal_pnr'];
			$this->_Ainput['bookingDetailsId']	= $_AbookingInfo[0]['booking_details_id'];
		}
		
		$_OgetBooking	= controllerGet::getObject('getBooking',$this);
		$_AgetBooking	= $_OgetBooking->_doGetBooking();
		
		if(isset($_AgetBooking['status']) && !empty($_AgetBooking['status'])){
			
			if($_AgetBooking['data']['TicketStatus'] == 1){
				
				$_Areturn = array
					(
						'status' => $_Bstatus,
						'data'   => $_AgetBooking['data'],
						'msg'    => $_Smessage,
					);
					
				//return $_Areturn;
			}
		}
		
		$_Oticket			= controllerGet::getObject('ticket',$this);				
		$_AticketResponse	= $_Oticket->_doTicket();
		
		
		$_AticketReturn = array();
		
		if(isset($_AticketResponse['status']) && !empty($_AticketResponse['status'])){
			
			// Parsing
			
			$_AticketReturn = $_AticketResponse['data'];
			
			if(!empty($this->_Ainput['bookingDetailsId']) && isset($_AticketReturn['paxDetails']) && count($_AticketReturn['paxDetails']) > 0){
				
				$_ApassengerDetails = $this->getPassengerDetails($this->_Ainput['bookingDetailsId']);
				$_AtempCheck		= array();
				
				if(count($_ApassengerDetails) > 0 && isset($_ApassengerDetails[0]['passenger_details_id']) && !empty($_ApassengerDetails[0]['passenger_details_id'])){
					
					foreach($_ApassengerDetails as $paxKey=>$paxVal){
						
						$_SticketNumber = '';
						
						foreach($_AticketReturn['paxDetails'] as $ticketPaxKey=>$ticketPaxVal){
							
							if(isset($ticketPaxVal['ticketNumber']) && !empty($ticketPaxVal['ticketNumber']) && !in_array($ticketPaxVal['ticketNumber'],$_AtempCheck)){
								
								$_SticketNumber = $ticketPaxVal['ticketNumber'];
								$_AtempCheck[]	= $_SticketNumber;
								break;
							}
						}
						
						if(!empty($_SticketNumber)){
							
							$sqlTicket = "UPDATE booking_passenger_details SET ticket_number = '".$_SticketNumber."' WHERE passenger_details_id = '".$paxVal['passenger_details_id']."' LIMIT 1";
			
							$this->_Odb->executeQuery($sqlTicket);
						}
					}
				}
				
				$sqlUpdateStatus = "UPDATE booking_details SET booking_status = 1 , ticket_status = 1 WHERE booking_details_id = '".$this->_Ainput['bookingDetailsId']."' LIMIT 1";
			
				$this->_Odb->executeQuery($sqlUpdateStatus);
			}
		}
		else{
			$_Bstatus  = false;
			$_Smessage = $_AticketResponse['msg'];
		}
				
		$_Areturn = array
					(
						'status' => $_Bstatus,
						'data'   => $_AticketReturn,
						'msg'    => $_Smessage,
					);
					
		return $_Areturn;
	}
}
?>