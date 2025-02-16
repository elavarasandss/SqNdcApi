<?php
/**
	@File Name 		:	getBooking.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	getBooking service
*/
class getBooking extends Execute
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
		$this->_SrequestUrl = $this->_Oconf['userSettings']['apiUrl']['getBookingDetails'];
	}
	
    public function _doGetBooking()
	{
		$_Bstatus  = true;
		$_Smessage = '';
		$_Adata	   = array();
		$_Areturn  = array();
		
		$this->_modifyData();
				
		$this->_Ainput['bookingId'] = 0;
		
		$_AbookingInfo = $this->getBookingDetails($this->_Ainput['pnr']);
		
		if(isset($_AbookingInfo[0]['universal_pnr']) && !empty($_AbookingInfo[0]['universal_pnr'])){
			
			$this->_Ainput['bookingId']			= $_AbookingInfo[0]['universal_pnr'];
			$this->_Ainput['markupData']		= json_decode($_AbookingInfo[0]['markup_info']);
			$this->_Ainput['paxCount']			= $_AbookingInfo[0]['adult']+$_AbookingInfo[0]['child'];
			$this->_Ainput['apiCurrency']		= $_AbookingInfo[0]['api_currency'];
			$this->_Ainput['userCurrency']		= $_AbookingInfo[0]['currency'];
			$this->_Ainput['apiExchangeRate']	= $_AbookingInfo[0]['exchange_rate'];
			$this->_Ainput['userExchangeRate']	= $_AbookingInfo[0]['api_exchange_rate'];
			$this->_Ainput['dbBookingStatus']	= $_AbookingInfo[0]['booking_status'];
			
			if(!isset($this->_Ainput['currencyCode'])){
				$this->_Ainput['currencyCode'] = $this->_Ainput['userCurrency'];
			}
		}
		
		$this->_setData();
		
		$_Aresult = $this->_executeService();
		
		//$_Aresult = $this->fun1();
		//$_Aresult = json_decode($_Aresult,true);
		$_Aresponse = array();
		
		if(isset($_Aresult['Response']['ResponseStatus']) && $_Aresult['Response']['ResponseStatus'] == 1){
			// Parsing
			
			$_Aresponse = $_Aresult['Response'];
			$_Aresponse = $this->parseBookingData($_Aresponse);
		}
		else{
			$_Bstatus = false;
			
			if(isset($_Aresult['Response']['Error']['ErrorMessage']) && !empty($_Aresult['Response']['Error']['ErrorMessage'])){
				$_Smessage = $_Aresult['Response']['Error']['ErrorMessage'];
			}
			else{
				$_Smessage = 'No Booking details found';
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
	
	function parseBookingData($_AgivenData)
	{
		//echo "<pre>";print_r($_AgivenData);exit;
		
		$_AreturnData = array();
		$_AmarkupData = isset($this->_Ainput['markupData']) ? $this->_Ainput['markupData'] : array();
		
		$_AflightItinerary				= $_AgivenData['FlightItinerary'];
		
		$_AreturnData['pnr'] 			= $_AflightItinerary['PNR'];
		$_AreturnData['bookingId'] 		= $_AflightItinerary['BookingId'];
		$_AreturnData['bookingStatus'] 	= $_AflightItinerary['Status'];
		$_AreturnData['bookingStatusNo']= $_AflightItinerary['Status'];
		$_AreturnData['ticketStatus'] 	= 0;
		$_IpaxCount						= isset($this->_Ainput['paxCount']) ? $this->_Ainput['paxCount'] : 1;
		$_AreturnData['isLcc'] 			= ($_AflightItinerary['IsLCC'] == true) ? "Y" : "N";
		$_AreturnData['refundable']		= 'N';
		$_AreturnData['source']			= $_AflightItinerary['Source'];
		
		if(isset($_AflightItinerary['NonRefundable']) && $_AflightItinerary['NonRefundable']){
			$_AreturnData['refundable'] = 'N';
		}
		if(isset($_AflightItinerary['NonRefundable']) && !$_AflightItinerary['NonRefundable']){
			$_AreturnData['refundable'] = 'Y';
		}
		
		$_Asegments			= $_AflightItinerary['Segments'];
		$_AtripFlights		= array();
		$_AcarrierCodes		= array();
		$_AflightReference	= array();
		
		// Flight details setting
		
		$_AnewSegments = array();
		
		foreach($_Asegments as $tempSegKey=>$tempSegVal){
			$tripIndicator = $tempSegVal['TripIndicator'] - 1;
			$_AnewSegments[$tripIndicator][] = $tempSegVal;
		}

		foreach($_AnewSegments as $segmentKey=>$segmentVal){
							
			$_IflightScheduleIndex 	= $segmentKey;
			
			$_AflightDetails    	= array();
			$viaFlights				= array();
			$journeySellKey			= '';
			$sellKeyAppend			= '';
			
			foreach($segmentVal as $segmentSubKey=>$segmentSubVal){
				
				//$_IflightScheduleIndex = $segmentSubVal['TripIndicator'] - 1;
				
				$_AbaggageAllowance = array();
				
				if(isset($segmentSubVal['Baggage']) && !empty($segmentSubVal['Baggage'])){
					$segmentSubVal['Baggage'] = strtoupper($segmentSubVal['Baggage']);
					$segmentSubVal['Baggage'] = str_replace("KG","",$segmentSubVal['Baggage']);
					$segmentSubVal['Baggage'] = str_replace(" ","",$segmentSubVal['Baggage']);
					
					if($segmentSubVal['Baggage'] > 0){
						$_AbaggageAllowance['Value'] = $segmentSubVal['Baggage'];
						$_AbaggageAllowance['Unit']  = 'Kilograms';
					}
				}
				
				$viaFlights[$segmentSubKey]['departureStation'] = $segmentSubVal['Origin']['Airport']['AirportCode'];
				$viaFlights[$segmentSubKey]['arrivalStation'] 	= $segmentSubVal['Destination']['Airport']['AirportCode'];
				$viaFlights[$segmentSubKey]['std'] 				= $segmentSubVal['Origin']['DepTime'];
				$viaFlights[$segmentSubKey]['sta'] 				= $segmentSubVal['Destination']['ArrTime'];
				$viaFlights[$segmentSubKey]['state'] 			= $segmentSubVal['Status'];
				$viaFlights[$segmentSubKey]['carrierCode'] 		= $segmentSubVal['Airline']['AirlineCode'];
				$viaFlights[$segmentSubKey]['flightNumber'] 	= $segmentSubVal['Airline']['FlightNumber'];
				$viaFlights[$segmentSubKey]['airlinePnr'] 		= $segmentSubVal['AirlinePNR'];
				
				$_AcarrierCodes[] = $viaFlights[$segmentSubKey]['carrierCode'];
			
				$viaFlights[$segmentSubKey]['classType'] 			= 'Economy';
				$viaFlights[$segmentSubKey]['fareBasisCode'] 		= $segmentSubVal['Airline']['FareClass'];
				$viaFlights[$segmentSubKey]['classOfService'] 		= $segmentSubVal['Airline']['FareClass'];
				$viaFlights[$segmentSubKey]['fareClassOfService']	= $segmentSubVal['Airline']['FareClass'];
				
				$viaFlights[$segmentSubKey]['refundable'] 			= $_AreturnData['refundable'];
			
				$stdExplode 		= explode("T",$viaFlights[$segmentSubKey]['std']);
				$tmpDepartureDate 	= $stdExplode[0];
				$tmpDepartureTime 	= substr($stdExplode[1],0,5);
				
				$viaFlights[$segmentSubKey]['departureDate'] 	= $tmpDepartureDate;
				$viaFlights[$segmentSubKey]['departureTime'] 	= substr($stdExplode[1],0,5);
				
				$staExplode 		= explode("T",$viaFlights[$segmentSubKey]['sta']);
				$tmpArrivalDate 	= $staExplode[0];
				$tmpArrivalTime 	= substr($staExplode[1],0,5);
				
				$viaFlights[$segmentSubKey]['arrivalDate'] 			= $tmpArrivalDate;
				$viaFlights[$segmentSubKey]['arrivalTime'] 			= $tmpArrivalTime;
				
				$viaFlights[$segmentSubKey]['departureTerminal'] 	= $segmentSubVal['Origin']['Airport']['AirportName']." ".$segmentSubVal['Origin']['Airport']['Terminal'];
				$viaFlights[$segmentSubKey]['arrivalTerminal'] 		= $segmentSubVal['Destination']['Airport']['AirportName']." ".$segmentSubVal['Destination']['Airport']['Terminal'];
				
				$viaFlights[$segmentSubKey]['capacity'] 			= 0;
				$viaFlights[$segmentSubKey]['sold'] 				= 0;
				
				$_StempSellKey = $viaFlights[$segmentSubKey]['carrierCode']."~".$viaFlights[$segmentSubKey]['flightNumber']."~".$viaFlights[$segmentSubKey]['departureStation']."~".$viaFlights[$segmentSubKey]['std']."~".$viaFlights[$segmentSubKey]['arrivalStation']."~".$viaFlights[$segmentSubKey]['sta']."~".$segmentSubVal['SegmentIndicator'];
				
				$journeySellKey .= $sellKeyAppend.$_StempSellKey;
				
				$sellKeyAppend = '~~~';
				
				$viaFlights[$segmentSubKey]['segmentSellKey'] = $_StempSellKey;
				
				if(count($_AbaggageAllowance) > 0){
					$viaFlights[$segmentSubKey]['baggageAllowance'] = $_AbaggageAllowance;
				}
				
				if($segmentSubKey == 0){
					$journeyDepartureStation 	= $viaFlights[$segmentSubKey]['departureStation'];
					$journeyDepartureDate 		= $tmpDepartureDate;
					$journeyDepartureTime 		= $tmpDepartureTime;
					$journeyStd 				= $viaFlights[$segmentSubKey]['std'];
				}
				
				if((count($segmentVal) -1) == $segmentSubKey){
					$journeyArrivalStation 		= $viaFlights[$segmentSubKey]['arrivalStation'];
					$journeyArrivalDate 		= $tmpArrivalDate;
					$journeyArrivalTime 		= $tmpArrivalTime;
					$journeySta 				= $viaFlights[$segmentSubKey]['sta'];
				}
				
				$_StempSegmentKey = $viaFlights[$segmentSubKey]['departureStation'].$viaFlights[$segmentSubKey]['arrivalStation'];
				
				$_AflightReference[$_StempSegmentKey] = $viaFlights[$segmentSubKey];				
			}
			
			$journeySellKey .= "@@@".$_AreturnData['isLcc'];
			
			$_AflightDetails['departureStation']= $journeyDepartureStation;
			$_AflightDetails['departureDate'] 	= $journeyDepartureDate;
			$_AflightDetails['departureTime'] 	= $journeyDepartureTime;
			$_AflightDetails['std'] 			= $journeyStd;
			$_AflightDetails['arrivalStation'] 	= $journeyArrivalStation;
			$_AflightDetails['arrivalDate'] 	= $journeyArrivalDate;
			$_AflightDetails['arrivalTime'] 	= $journeyArrivalTime;
			$_AflightDetails['sta'] 			= $journeySta;
			$_AflightDetails['stops'] 			= count($viaFlights)-1;
			$_AflightDetails['journeySellKey'] 	= trim($journeySellKey);
			$_AflightDetails['viaFlights'] 		= $viaFlights;
			$_AflightDetails['refundable'] 		= $_AreturnData['refundable'];
			
			$_StempSegmentKey = $journeyDepartureStation.$journeyArrivalStation;
				
			$_AflightReference[$_StempSegmentKey] = $viaFlights[0];
			
			$_AtripFlights[$_IflightScheduleIndex] = $_AflightDetails;
		}

		// Fare settings
						
		$_AfareInfo			= $_AflightItinerary['Fare'];
		
		$_SapiCurrency 		= $_AfareInfo['Currency'];
		$_IapiExchangeRate 	= isset($GLOBALS['CONF']['exchangeRate'][$_SapiCurrency]) ? $GLOBALS['CONF']['exchangeRate'][$_SapiCurrency] : 1;
		
		$_SuserCurrency 	= $this->_Ainput['currencyCode'];
		$_IuserExchangeRate	= isset($GLOBALS['CONF']['exchangeRate'][$_SuserCurrency]) ? $GLOBALS['CONF']['exchangeRate'][$_SuserCurrency] : 1;
		
		$_SdefaultCurrency 	= $GLOBALS['CONF']['site']['defaultCurrency'];
		
		if(isset($this->_Ainput['apiCurrency']) && !empty($this->_Ainput['apiCurrency'])){
			$_SapiCurrency = $this->_Ainput['apiCurrency'];
		}
		if(isset($this->_Ainput['userCurrency']) && !empty($this->_Ainput['userCurrency'])){
			$_SuserCurrency = $this->_Ainput['userCurrency'];
		}
		
		if(isset($this->_Ainput['apiExchangeRate']) && !empty($this->_Ainput['apiExchangeRate'])){
			$_IapiExchangeRate = $this->_Ainput['apiExchangeRate'];
		}
		if(isset($this->_Ainput['userExchangeRate']) && !empty($this->_Ainput['userExchangeRate'])){
			$_IuserExchangeRate = $this->_Ainput['userExchangeRate'];
		}
		
		$_AreturnData['userCurrency'] 		= $_SuserCurrency;
		$_AreturnData['userExchangeRate'] 	= $_IuserExchangeRate;
		$_AreturnData['defaultCurrency'] 	= $_SdefaultCurrency;
		$_AreturnData['apiCurrency'] 		= $_SapiCurrency;
		$_AreturnData['apiExchangeRate'] 	= $_IapiExchangeRate;
		
		/*$_IapiExchangeRate = 1;
		$_IuserExchangeRate= 1;*/
		
		// Api fares
		
		$_IapiBaseFare 			= $_AfareInfo['OfferedFare'] - $_AfareInfo['Tax']- $_AfareInfo['AdditionalTxnFeePub'];//$_AfareInfo['BaseFare'];
		$_IapiTax				= $_AfareInfo['Tax'];
		//$_IapiTotalFare			= $_AfareInfo['PublishedFare'];
		$_IapiTotalFare			= $_AfareInfo['OfferedFare'];//$_AfareInfo['PublishedFare'];
		$_IapiOfferFare			= $_AfareInfo['PublishedFare'];
		
		$_IapiPublishedFare		= $_AfareInfo['PublishedFare'];
		$_IapiOfferedFare		= $_AfareInfo['OfferedFare'];
		$_IapiCommissionEarned	= $_AfareInfo['CommissionEarned'];
		$_IapiServiceFee		= $_AfareInfo['ServiceFee'];
		$_IapiPLBEarned			= $_AfareInfo['PLBEarned'];
		$_IapiIncentiveEarned	= $_AfareInfo['IncentiveEarned'];
		$_IapiTdsOnCommission	= $_AfareInfo['TdsOnCommission'];
		$_IapiTdsOnPLB			= $_AfareInfo['TdsOnPLB'];
		$_IapiTdsOnIncentive	= $_AfareInfo['TdsOnIncentive'];
		
		$_IapiFee				= $_AfareInfo['OtherCharges']+$_AfareInfo['ServiceFee']+$_AfareInfo['AdditionalTxnFeePub']+$_AfareInfo['PGCharge'];
		$_IapiOtherFee			= $_AfareInfo['OtherCharges']+$_AfareInfo['ServiceFee'];
		
		/*$_IapiFee				= $_AfareInfo['OtherCharges']+$_AfareInfo['AdditionalTxnFeePub']+$_AfareInfo['PGCharge'];
		$_IapiOtherFee			= $_AfareInfo['OtherCharges'];*/
		
		$_IapiOtherFeePerPax	= $_IapiOtherFee / $_IpaxCount;
		
		$_IapiBaggageFare		= $_AfareInfo['TotalBaggageCharges'];
		$_IapiMealsFare			= $_AfareInfo['TotalMealCharges'];
		$_IapiSeatFare			= $_AfareInfo['TotalSeatCharges'];
		
		// Default fares
		
		$_IdefaultBaseFare  	= $_IapiBaseFare / $_IapiExchangeRate;
		$_IdefaultBaseFare  	= Common::getRoundedFare($_IdefaultBaseFare,$_SdefaultCurrency);
		
		$_IdefaultTax			= $_IapiTax / $_IapiExchangeRate;
		$_IdefaultTax  			= Common::getRoundedFare($_IdefaultTax,$_SdefaultCurrency);
		
		$_IdefaultFee			= $_IapiFee / $_IapiExchangeRate;
		$_IdefaultFee  			= Common::getRoundedFare($_IdefaultFee,$_SdefaultCurrency);
		
		$_IdefaultOtherFee		= $_IapiOtherFee / $_IapiExchangeRate;
		$_IdefaultOtherFee		= Common::getRoundedFare($_IdefaultOtherFee,$_SdefaultCurrency);
		
		$_IdefaultOtherFeePerPax= $_IapiOtherFeePerPax / $_IapiExchangeRate;
		$_IdefaultOtherFeePerPax= Common::getRoundedFare($_IdefaultOtherFeePerPax,$_SdefaultCurrency);
		
		$_IdefaultTotalFare			= $_IapiTotalFare / $_IapiExchangeRate;
		$_IdefaultTotalFare			= Common::getRoundedFare($_IdefaultTotalFare,$_SdefaultCurrency);
		
		$_IdefaultPublishedFare		= $_IapiPublishedFare / $_IapiExchangeRate;
		$_IdefaultPublishedFare		= Common::getRoundedFare($_IdefaultPublishedFare,$_SdefaultCurrency);
		
		$_IdefaultOfferedFare		= $_IapiOfferedFare / $_IapiExchangeRate;
		$_IdefaultOfferedFare		= Common::getRoundedFare($_IdefaultOfferedFare,$_SdefaultCurrency);
		
		$_IdefaultCommissionEarned	= $_IapiCommissionEarned / $_IapiExchangeRate;
		$_IdefaultCommissionEarned	= Common::getRoundedFare($_IdefaultCommissionEarned,$_SdefaultCurrency);
		
		$_IdefaultServiceFee		= $_IapiServiceFee / $_IapiExchangeRate;
		$_IdefaultServiceFee		= Common::getRoundedFare($_IdefaultServiceFee,$_SdefaultCurrency);
		
		//---------------------//
		
		$_IdefaultPLBEarned				= $_IapiPLBEarned / $_IapiExchangeRate;
		$_IdefaultPLBEarned				= Common::getRoundedFare($_IdefaultPLBEarned,$_SdefaultCurrency);
		
		$_IdefaultIncentiveEarned		= $_IapiIncentiveEarned / $_IapiExchangeRate;
		$_IdefaultIncentiveEarned		= Common::getRoundedFare($_IdefaultIncentiveEarned,$_SdefaultCurrency);
		
		$_IdefaultTdsOnCommission		= $_IapiTdsOnCommission / $_IapiExchangeRate;
		$_IdefaultTdsOnCommission		= Common::getRoundedFare($_IdefaultTdsOnCommission,$_SdefaultCurrency);
		
		$_IdefaultTdsOnPLB				= $_IapiTdsOnPLB / $_IapiExchangeRate;
		$_IdefaultTdsOnPLB				= Common::getRoundedFare($_IdefaultTdsOnPLB,$_SdefaultCurrency);
		
		$_IdefaultTdsOnIncentive		= $_IapiTdsOnIncentive / $_IapiExchangeRate;
		$_IdefaultTdsOnIncentive		= Common::getRoundedFare($_IdefaultTdsOnIncentive,$_SdefaultCurrency);
		
		
		//---------------------//
/* 		$_IapiPublishedFare		= $_AfareInfo['PublishedFare'];
		$_IapiOfferedFare		= $_AfareInfo['OfferedFare'];
		$_IapiCommissionEarned	= $_AfareInfo['CommissionEarned'];
		$_IapiServiceFee		= $_AfareInfo['ServiceFee']; */
		
		$_IdefaultBaggageFare	= $_IapiBaggageFare / $_IapiExchangeRate;
		$_IdefaultBaggageFare	= Common::getRoundedFare($_IdefaultBaggageFare,$_SdefaultCurrency);
		
		$_IdefaultMealsFare		= $_IapiMealsFare / $_IapiExchangeRate;
		$_IdefaultMealsFare		= Common::getRoundedFare($_IdefaultMealsFare,$_SdefaultCurrency);
		
		$_IdefaultSeatFare		= $_IapiSeatFare / $_IapiExchangeRate;
		$_IdefaultSeatFare		= Common::getRoundedFare($_IdefaultSeatFare,$_SdefaultCurrency);
		
		// User fares
		
		$_IuserBaseFare  		= $_IdefaultBaseFare * $_IuserExchangeRate;
		$_IuserBaseFare			= Common::getRoundedFare($_IuserBaseFare,$_SuserCurrency);
		
		$_IuserTax				= $_IdefaultTax * $_IuserExchangeRate;
		$_IuserTax				= Common::getRoundedFare($_IuserTax,$_SuserCurrency);
		
		$_IuserFee				= $_IdefaultFee * $_IuserExchangeRate;
		$_IuserFee				= Common::getRoundedFare($_IuserFee,$_SuserCurrency);
		
		$_IuserOtherFee			= $_IdefaultOtherFee * $_IuserExchangeRate;
		$_IuserOtherFee			= Common::getRoundedFare($_IuserOtherFee,$_SuserCurrency);
		
		$_IuserOtherFeePerPax	= $_IdefaultOtherFeePerPax * $_IuserExchangeRate;
		$_IuserOtherFeePerPax	= Common::getRoundedFare($_IuserOtherFeePerPax,$_SuserCurrency);
		
		$_IuserTotalFare		= $_IdefaultTotalFare * $_IuserExchangeRate;
		$_IuserTotalFare		= Common::getRoundedFare($_IuserTotalFare,$_SuserCurrency);
		
		$_IuserPublishedFare	= $_IdefaultPublishedFare * $_IuserExchangeRate;
		$_IuserPublishedFare	= Common::getRoundedFare($_IuserPublishedFare,$_SuserCurrency);
		
		$_IuserOfferedFare		= $_IdefaultOfferedFare * $_IuserExchangeRate;
		$_IuserOfferedFare		= Common::getRoundedFare($_IuserOfferedFare,$_SuserCurrency);
		
		$_IuserCommissionEarned	= $_IdefaultCommissionEarned * $_IuserExchangeRate;
		$_IuserCommissionEarned	= Common::getRoundedFare($_IuserCommissionEarned,$_SuserCurrency);
		
		$_IuserServiceFee		= $_IdefaultServiceFee * $_IuserExchangeRate;
		$_IuserServiceFee		= Common::getRoundedFare($_IuserServiceFee,$_SuserCurrency);
		
		//---------------------------//
		$_IuserPLBEarned			= $_IdefaultPLBEarned * $_IuserExchangeRate;
		$_IuserPLBEarned			= Common::getRoundedFare($_IuserPLBEarned,$_SuserCurrency);
		
		$_IuserIncentiveEarned		= $_IdefaultIncentiveEarned * $_IuserExchangeRate;
		$_IuserIncentiveEarned		= Common::getRoundedFare($_IuserIncentiveEarned,$_SuserCurrency);
		
		$_IuserTdsOnCommission		= $_IdefaultTdsOnCommission * $_IuserExchangeRate;
		$_IuserTdsOnCommission		= Common::getRoundedFare($_IuserTdsOnCommission,$_SuserCurrency);
		
		$_IuserTdsOnPLB				= $_IdefaultTdsOnPLB * $_IuserExchangeRate;
		$_IuserTdsOnPLB				= Common::getRoundedFare($_IuserTdsOnPLB,$_SuserCurrency);
		
		$_IuserTdsOnIncentive		= $_IdefaultTdsOnIncentive * $_IuserExchangeRate;
		$_IuserTdsOnIncentive		= Common::getRoundedFare($_IuserTdsOnIncentive,$_SuserCurrency);
		
		//--------------------------//
		
		$_IuserBaggageFare		= $_IdefaultBaggageFare * $_IuserExchangeRate;
		$_IuserBaggageFare		= Common::getRoundedFare($_IuserBaggageFare,$_SuserCurrency);
		
		$_IuserMealsFare		= $_IdefaultMealsFare * $_IuserExchangeRate;
		$_IuserMealsFare		= Common::getRoundedFare($_IuserMealsFare,$_SuserCurrency);
		
		$_IuserSeatFare			= $_IdefaultSeatFare * $_IuserExchangeRate;
		$_IuserSeatFare			= Common::getRoundedFare($_IuserSeatFare,$_SuserCurrency);
		
		if($_SapiCurrency == $_SuserCurrency){
							
			$_IuserBaseFare  		= $_IapiBaseFare;
			$_IuserTax				= $_IapiTax;
			$_IuserFee				= $_IapiFee;
			$_IuserOtherFee			= $_IapiOtherFee;
			$_IuserOtherFeePerPax	= $_IapiOtherFeePerPax;							
			$_IuserTotalFare		= $_IapiTotalFare;
			$_IuserBaggageFare		= $_IapiBaggageFare;
			$_IuserMealsFare		= $_IapiMealsFare;
			$_IuserSeatFare			= $_IapiSeatFare;
			
			$_IuserPublishedFare	= $_IapiPublishedFare;
			$_IuserOfferedFare		= $_IapiOfferedFare;
			$_IuserCommissionEarned	= $_IapiCommissionEarned;
			$_IuserServiceFee		= $_IapiServiceFee;
			
			$_IuserPLBEarned		= $_IapiPLBEarned;
			$_IuserIncentiveEarned	= $_IapiIncentiveEarned;
			$_IuserTdsOnCommission	= $_IapiTdsOnCommission;  
			$_IuserTdsOnPLB			= $_IapiTdsOnPLB;
			$_IuserTdsOnIncentive	= $_IapiTdsOnIncentive;	
		
		}
		
		// Markup setting
						
		$_IuserTotalMarkup	 = 0;
		$_IuserperPaxMarkup	 = 0;
		$_SmarkupAddIn	 	 = '';
		$_SmarkupTaxComponent= '';
		
		if(isset($_AmarkupData['markupValue']) && !empty($_AmarkupData['markupValue'])){
			
			$_IuserTotalMarkup	= $_AmarkupData['markupValue'] * $_IuserExchangeRate;
			$_IuserTotalMarkup	= Common::getRoundedFare($_IuserTotalMarkup,$_SuserCurrency);
			
			$_IuserperPaxMarkup	= $_AmarkupData['perPaxMarkup'] * $_IuserExchangeRate;
			$_IuserperPaxMarkup	= Common::getRoundedFare($_IuserperPaxMarkup,$_SuserCurrency);
			
			$_IuserTotalFare 	= $_IuserTotalFare + $_IuserTotalMarkup;
			
			$_SmarkupAddIn		= $_AmarkupData['markupAddIn'];
			
			if($_AmarkupData['markupAddIn'] == "BF"){
				$_IuserBaseFare = $_IuserBaseFare + $_IuserTotalMarkup;
			}
			else if($_AmarkupData['markupAddIn'] == "TAX"){
				$_IuserTax 				= $_IuserTax + $_IuserTotalMarkup;
				$_SmarkupTaxComponent 	= $_AmarkupData['markupTaxComponent'];
			}
		}
		
		// Passenger , pax fares and takbreakup setting
		
		$_ApassengerDetails		= array();
		$_ApaxCheckTemp			= array();
		$_ApaxFares				= array();
		$_AapiPaxFares			= array();
		$_AdefaultPaxFares		= array();
		$_AtaxBreakUp			= array();
		$_AapiTaxBreakUp		= array();
		$_AdefaultTaxBreakUp	= array();
		
		$_Apassengers			= $_AflightItinerary['Passenger'];
		
		foreach($_Apassengers as $paxKey=>$paxVal){
			
			$_SpaxType = '';
			
			if($paxVal['PaxType'] == 1){
				$_SpaxType = 'ADT';
			}
			else if($paxVal['PaxType'] == 2){
				$_SpaxType = 'CHD';
			}
			else if($paxVal['PaxType'] == 3){
				$_SpaxType = 'INF';
			}
			
			$_Sgender = 'Male';
			
			if($paxVal['Gender'] == 1){
				$_Sgender = 'Male';
			}
			else if($paxVal['Gender'] == 2){
				$_Sgender = 'Female';
			}
			
			$_Adob = explode("T",$paxVal['DateOfBirth']);
			
			
			$_Atemp						= array();
			
			$_Atemp['title']			= $paxVal['Title'];
			$_Atemp['firstName']		= $paxVal['FirstName'];
			$_Atemp['lastName']			= $paxVal['LastName'];
			$_Atemp['paxType']			= $_SpaxType;
			$_Atemp['dob']				= $_Adob[0];
			$_Atemp['gender']			= $_Sgender;
			$_Atemp['passportNo']		= $paxVal['PassportNo'];
			
			if(isset($paxVal['PassportExpiry']) && !empty($paxVal['PassportExpiry'])){
				$_ApassportExpiry			= explode("T",$paxVal['PassportExpiry']);
				$_Atemp['passportExpiry']	= $_ApassportExpiry[0];
			}
			
			$_Atemp['ticketId']					= 0;
			$_Atemp['ticketNumber']				= "";
			$_Atemp['ticketIssueDate']			= "0000-00-00";
			$_Atemp['ticketStatus']				= "";
			$_Atemp['ticketRemarks']			= "";
			$_Atemp['ticketValidatingAirline']	= "";
			
			if(isset($paxVal['Ticket']['TicketNumber']) && !empty($paxVal['Ticket']['TicketNumber'])){
				
				$_Atemp['ticketId']					= $paxVal['Ticket']['TicketId'];
				$_Atemp['ticketNumber']				= $paxVal['Ticket']['TicketNumber'];
				$_Atemp['ticketIssueDate']			= $paxVal['Ticket']['IssueDate'];
				$_Atemp['ticketStatus']				= $paxVal['Ticket']['Status'];
				$_Atemp['ticketRemarks']			= $paxVal['Ticket']['Remarks'];
				$_Atemp['ticketValidatingAirline']	= $paxVal['Ticket']['ValidatingAirline'];
				
				if($_AreturnData['isLcc'] == "N"){
					$_Atemp['ticketNumber'] = $_Atemp['ticketValidatingAirline'].$_Atemp['ticketNumber'];
				}
				
				if($_Atemp['ticketStatus'] == "OK"){
					$_AreturnData['ticketStatus'] = 1;
				}
			}
			
			$_Atemp['ssr'] = array();
			
			// SSR settings
			
			if(isset($paxVal['Baggage']) && !empty($paxVal['Baggage'])){
				
				foreach($paxVal['Baggage'] as $bagTempKey=>$bagTempVal){
					
					$_IapiAmount = $bagTempVal['Price'];
					
					//if($_IapiAmount > 0){
						
						// Default fares
						
						$_IdefaultAmount	= $_IapiAmount / $_IapiExchangeRate;
						$_IdefaultAmount	= Common::getRoundedFare($_IdefaultAmount,$_SdefaultCurrency);
						
						// User fares
						
						$_IuserAmount		= $_IdefaultAmount * $_IuserExchangeRate;
						$_IuserAmount		= Common::getRoundedFare($_IuserAmount,$_SuserCurrency);
						
						$_AtempData = array();
						
						$_AtempData['origin']			= $bagTempVal['Origin'];
						$_AtempData['destination']		= $bagTempVal['Destination'];
						$_AtempData['code']				= $bagTempVal['Code'];
						$_AtempData['wayType']			= $bagTempVal['WayType'];
						$_AtempData['description']		= "Baggage ".$bagTempVal['Weight']." Kg";
						$_AtempData['amount']			= $_IuserAmount;
						$_AtempData['apiAmount']		= $_IapiAmount;
						$_AtempData['defaultAmount']	= $_IdefaultAmount;
						$_AtempData['orgDescription']	= $bagTempVal['Description'];
						$_AtempData['weight']			= $bagTempVal['Weight'];
						
						$_SsectorKey = $_AtempData['origin'].$_AtempData['destination'];
						
						$_AtempData['carrierCode']		= isset($_AflightReference[$_SsectorKey]['carrierCode']) ? $_AflightReference[$_SsectorKey]['carrierCode'] : '';
						$_AtempData['flightNumber']		= isset($_AflightReference[$_SsectorKey]['flightNumber']) ? $_AflightReference[$_SsectorKey]['flightNumber'] : '';
						
						if($_SapiCurrency == $_SuserCurrency){
							$_AtempData['amount'] = $_IapiAmount;
						}
						
						$_Atemp['ssr'][] = $_AtempData;
					//}					
				}
			}
			
			if(isset($paxVal['MealDynamic']) && !empty($paxVal['MealDynamic'])){
				
				foreach($paxVal['MealDynamic'] as $mealTempKey=>$mealTempVal){
					
					$_IapiAmount = isset($mealTempVal['Price']) ? $mealTempVal['Price'] : 0;
						
					//if($_IapiAmount > 0){
							
						// Default fares
						
						$_IdefaultAmount	= $_IapiAmount / $_IapiExchangeRate;
						$_IdefaultAmount	= Common::getRoundedFare($_IdefaultAmount,$_SdefaultCurrency);
						
						// User fares
						
						$_IuserAmount		= $_IdefaultAmount * $_IuserExchangeRate;
						$_IuserAmount		= Common::getRoundedFare($_IuserAmount,$_SuserCurrency);
						
						$_AtempData = array();
						
						$_AtempData['origin']			= isset($mealTempVal['Origin']) ? $mealTempVal['Origin'] : '';
						$_AtempData['destination']		= isset($mealTempVal['Destination']) ? $mealTempVal['Destination'] : '';
						$_AtempData['code']				= $mealTempVal['Code'];
						$_AtempData['wayType']			= isset($mealTempVal['WayType']) ? $mealTempVal['WayType'] : '';
						$_AtempData['description']		= isset($mealTempVal['AirlineDescription']) ? $mealTempVal['AirlineDescription'] : $mealTempVal['Code'];
						$_AtempData['amount']			= $_IuserAmount;
						$_AtempData['apiAmount']		= $_IapiAmount;
						$_AtempData['defaultAmount']	= $_IdefaultAmount;
						$_AtempData['orgDescription']	= isset($mealTempVal['Description']) ? $mealTempVal['Description'] : $mealTempVal['Code'];
						
						$_SsectorKey = $_AtempData['origin'].$_AtempData['destination'];
						
						$_AtempData['carrierCode']		= isset($_AflightReference[$_SsectorKey]['carrierCode']) ? $_AflightReference[$_SsectorKey]['carrierCode'] : '';
						$_AtempData['flightNumber']		= isset($_AflightReference[$_SsectorKey]['flightNumber']) ? $_AflightReference[$_SsectorKey]['flightNumber'] : '';
						
						if($_SapiCurrency == $_SuserCurrency){
							$_AtempData['amount'] = $_IapiAmount;
						}
						
						$_Atemp['ssr'][] = $_AtempData;
					//}					
				}
			}
			
			if(isset($paxVal['SeatDynamic']) && !empty($paxVal['SeatDynamic'])){
				
				foreach($paxVal['SeatDynamic'] as $seatTempKey=>$seatTempVal){
					
					$_IapiPrice		= $seatTempVal['Price'];
								
					// Default fares
				
					$_IdefaultPrice	= $_IapiPrice / $_IapiExchangeRate;
					$_IdefaultPrice	= Common::getRoundedFare($_IdefaultPrice,$_SdefaultCurrency);
					
					// User fares
					
					$_IuserPrice		= $_IdefaultPrice * $_IuserExchangeRate;
					$_IuserPrice		= Common::getRoundedFare($_IuserPrice,$_SuserCurrency);
					
					$seatTempVal['amount']  		= $_IuserPrice;
					$seatTempVal['apiAmount']		= $_IapiPrice;
					$seatTempVal['defaultAmount']	= $_IdefaultPrice;
					$seatTempVal['origin']			= $seatTempVal['Origin'];
					$seatTempVal['destination']		= $seatTempVal['Destination'];
					$seatTempVal['carrierCode']		= $seatTempVal['AirlineCode'];
					$seatTempVal['flightNumber']	= $seatTempVal['FlightNumber'];
					$seatTempVal['code']			= $seatTempVal['Code'];
					$seatTempVal['description']		= "Seat ".$seatTempVal['Code'];
						
					if(!isset($seatTempVal['Text'])){
						$seatTempVal['Text'] = '';
					}
					
					if($_SapiCurrency == $_SuserCurrency){
						$seatTempVal['amount'] = $_IapiPrice;
					}
					
					unset($seatTempVal['Currency']);
					unset($seatTempVal['Price']);
					unset($seatTempVal['Origin']);
					unset($seatTempVal['Destination']);
					unset($seatTempVal['AirlineCode']);
					unset($seatTempVal['FlightNumber']);

					$_Atemp['ssr'][] = $seatTempVal;
				}
			}
			if(isset($paxVal['SegmentAdditionalInfo']) && !empty($paxVal['SegmentAdditionalInfo'])){
				$_AmealSeatTempArr = array();
				foreach($paxVal['SegmentAdditionalInfo'] as $mealBaggageTempKey=>$mealBaggageTempVal){
					
					if(isset($mealBaggageTempVal['Baggage'])){
					
						$_AbaggageArr = explode("|",$mealBaggageTempVal['Baggage']);
						if($_AbaggageArr[0]!='0 Kg')
						{
							$_IapiPrice		= 0;
								
							// Default fares
							$_IdefaultPrice	= $_IapiPrice / $_IapiExchangeRate;
							$_IdefaultPrice	= Common::getRoundedFare($_IdefaultPrice,$_SdefaultCurrency);
							
							// User fares
							
							$_IuserPrice		= $_IdefaultPrice * $_IuserExchangeRate;
							$_IuserPrice		= Common::getRoundedFare($_IuserPrice,$_SuserCurrency);
							
							$_AmealSeatTempArr['amount']  		= $_IuserPrice;
							$_AmealSeatTempArr['apiAmount']		= $_IapiPrice;
							$_AmealSeatTempArr['defaultAmount']	= $_IdefaultPrice;
							$_AmealSeatTempArr['origin']		= $_Asegments[$mealBaggageTempKey]['Origin']['Airport']['AirportCode'];
							$_AmealSeatTempArr['destination']	= $_Asegments[$mealBaggageTempKey]['Destination']['Airport']['AirportCode'];
							$_AmealSeatTempArr['carrierCode']	= $_Asegments[$mealBaggageTempKey]['Airline']['AirlineCode'];
							$_AmealSeatTempArr['flightNumber']	= $_Asegments[$mealBaggageTempKey]['Airline']['FlightNumber'];
							$_AmealSeatTempArr['code']			= $_AbaggageArr[0];

							$_AmealSeatTempArr['description']	= $_AbaggageArr[0];

							$_AmealSeatTempArr['Text'] = $_AbaggageArr[0].' Free Baggage';
							$_AmealSeatTempArr['amount'] = $_IapiPrice;

							$_Atemp['ssr'][] = $_AmealSeatTempArr;
						}

					}
					
				}
			}
			/* if(isset($paxVal['SegmentAdditionalInfo']) && !empty($paxVal['SegmentAdditionalInfo'])){
				$_AmealSeatTempArr = array();
				foreach($paxVal['SegmentAdditionalInfo'] as $mealBaggageTempKey=>$mealBaggageTempVal){
					
					if(isset($mealBaggageTempVal['Meal'])){
					
						$_AbaggageArr = explode("|",$mealBaggageTempVal['Meal']);
						if($_AbaggageArr[0]!='0 Platter')
						{
							$_IapiPrice		= 0;
								
							// Default fares
							$_IdefaultPrice	= $_IapiPrice / $_IapiExchangeRate;
							$_IdefaultPrice	= Common::getRoundedFare($_IdefaultPrice,$_SdefaultCurrency);
							
							// User fares
							$_IuserPrice		= $_IdefaultPrice * $_IuserExchangeRate;
							$_IuserPrice		= Common::getRoundedFare($_IuserPrice,$_SuserCurrency);
							
							$_AmealSeatTempArr['amount']  		= $_IuserPrice;
							$_AmealSeatTempArr['apiAmount']		= $_IapiPrice;
							$_AmealSeatTempArr['defaultAmount']	= $_IdefaultPrice;
							$_AmealSeatTempArr['origin']		= $_Asegments[$mealBaggageTempKey]['Origin']['Airport']['AirportCode'];
							$_AmealSeatTempArr['destination']	= $_Asegments[$mealBaggageTempKey]['Destination']['Airport']['AirportCode'];
							$_AmealSeatTempArr['carrierCode']	= $_Asegments[$mealBaggageTempKey]['Airline']['AirlineCode'];
							$_AmealSeatTempArr['flightNumber']	= $_Asegments[$mealBaggageTempKey]['Airline']['FlightNumber'];
							$_AmealSeatTempArr['code']			= $_AbaggageArr[0];

							$_AmealSeatTempArr['description']	= $_AbaggageArr[0];

							$_AmealSeatTempArr['Text'] = $_AbaggageArr[0].' Meal';
							$_AmealSeatTempArr['amount'] = $_IapiPrice;

							$_Atemp['ssr'][] = $_AmealSeatTempArr;
						}

					}
					
				}
			} */
			
			// Passenger Fare settings
						
			$_ApaxFareInfo = $paxVal['Fare'];
			
			// Api fares
		
			$_IapiPaxBaseFare 			= $_ApaxFareInfo['BaseFare'];
			$_IapiPaxTax				= $_ApaxFareInfo['Tax'];
			$_IapiPaxTotalFare			= $_ApaxFareInfo['PublishedFare'];
			
			$_IapiPaxFee				= $_ApaxFareInfo['OtherCharges']+$_ApaxFareInfo['ServiceFee']+$_ApaxFareInfo['AdditionalTxnFeePub']+$_ApaxFareInfo['PGCharge'];
			$_IapiPaxOtherFee			= $_ApaxFareInfo['OtherCharges']+$_ApaxFareInfo['ServiceFee'];
			
			/*$_IapiFee					= $_ApaxFareInfo['OtherCharges']+$_ApaxFareInfo['AdditionalTxnFeePub']+$_ApaxFareInfo['PGCharge'];
			$_IapiOtherFee				= $_ApaxFareInfo['OtherCharges'];*/
			
			$_IapiPaxBaggageFare		= $_ApaxFareInfo['TotalBaggageCharges'];
			$_IapiPaxMealsFare			= $_ApaxFareInfo['TotalMealCharges'];
			$_IapiPaxSeatFare			= $_ApaxFareInfo['TotalSeatCharges'];
			
			// Default fares
			
			$_IdefaultPaxBaseFare  		= $_IapiPaxBaseFare / $_IapiExchangeRate;
			$_IdefaultPaxBaseFare  		= Common::getRoundedFare($_IdefaultPaxBaseFare,$_SdefaultCurrency);
			
			$_IdefaultPaxTax			= $_IapiPaxTax / $_IapiExchangeRate;
			$_IdefaultPaxTax  			= Common::getRoundedFare($_IdefaultPaxTax,$_SdefaultCurrency);
			
			$_IdefaultPaxFee			= $_IapiPaxFee / $_IapiExchangeRate;
			$_IdefaultPaxFee  			= Common::getRoundedFare($_IdefaultPaxFee,$_SdefaultCurrency);
			
			$_IdefaultPaxOtherFee		= $_IapiPaxOtherFee / $_IapiExchangeRate;
			$_IdefaultPaxOtherFee		= Common::getRoundedFare($_IdefaultPaxOtherFee,$_SdefaultCurrency);
			
			$_IdefaultPaxTotalFare		= $_IapiPaxTotalFare / $_IapiExchangeRate;
			$_IdefaultPaxTotalFare		= Common::getRoundedFare($_IdefaultPaxTotalFare,$_SdefaultCurrency);
			
			$_IdefaultPaxBaggageFare	= $_IapiPaxBaggageFare / $_IapiExchangeRate;
			$_IdefaultPaxBaggageFare	= Common::getRoundedFare($_IdefaultPaxBaggageFare,$_SdefaultCurrency);
			
			$_IdefaultPaxMealsFare		= $_IapiPaxMealsFare / $_IapiExchangeRate;
			$_IdefaultPaxMealsFare		= Common::getRoundedFare($_IdefaultPaxMealsFare,$_SdefaultCurrency);
			
			$_IdefaultPaxSeatFare		= $_IapiPaxSeatFare / $_IapiExchangeRate;
			$_IdefaultPaxSeatFare		= Common::getRoundedFare($_IdefaultPaxSeatFare,$_SdefaultCurrency);
			
			// User fares
			
			$_IuserPaxBaseFare  		= $_IdefaultPaxBaseFare * $_IuserExchangeRate;
			$_IuserPaxBaseFare			= Common::getRoundedFare($_IuserPaxBaseFare,$_SuserCurrency);
			
			$_IuserPaxTax				= $_IdefaultPaxTax * $_IuserExchangeRate;
			$_IuserPaxTax				= Common::getRoundedFare($_IuserPaxTax,$_SuserCurrency);
			
			$_IuserPaxFee				= $_IdefaultPaxFee * $_IuserExchangeRate;
			$_IuserPaxFee				= Common::getRoundedFare($_IuserPaxFee,$_SuserCurrency);
			
			$_IuserPaxOtherFee			= $_IdefaultPaxOtherFee * $_IuserExchangeRate;
			$_IuserPaxOtherFee			= Common::getRoundedFare($_IuserPaxOtherFee,$_SuserCurrency);
			
			$_IuserPaxTotalFare			= $_IdefaultPaxTotalFare * $_IuserExchangeRate;
			$_IuserPaxTotalFare			= Common::getRoundedFare($_IuserPaxTotalFare,$_SuserCurrency);
			
			$_IuserPaxBaggageFare		= $_IdefaultPaxBaggageFare * $_IuserExchangeRate;
			$_IuserPaxBaggageFare		= Common::getRoundedFare($_IuserPaxBaggageFare,$_SuserCurrency);
			
			$_IuserPaxMealsFare			= $_IdefaultPaxMealsFare * $_IuserExchangeRate;
			$_IuserPaxMealsFare			= Common::getRoundedFare($_IuserPaxMealsFare,$_SuserCurrency);
			
			$_IuserPaxSeatFare			= $_IdefaultPaxSeatFare * $_IuserExchangeRate;
			$_IuserPaxSeatFare			= Common::getRoundedFare($_IuserPaxSeatFare,$_SuserCurrency);
			
			if($_SapiCurrency == $_SuserCurrency){
				
				$_IuserPaxBaseFare  		= $_IapiPaxBaseFare;
				$_IuserPaxTax				= $_IapiPaxTax;
				$_IuserPaxFee				= $_IapiPaxFee;
				$_IuserPaxOtherFee			= $_IapiPaxOtherFee;
				$_IuserPaxTotalFare			= $_IapiPaxTotalFare;
				$_IuserPaxBaggageFare		= $_IapiPaxBaggageFare;
				$_IuserPaxMealsFare			= $_IapiPaxMealsFare;
				$_IuserPaxSeatFare			= $_IapiPaxSeatFare;
			}
			
			if($_IuserperPaxMarkup != 0){
			
				$_IuserPaxTotalFare = $_IuserPaxTotalFare + $_IuserperPaxMarkup;
				
				if($_SmarkupAddIn == "BF"){
					$_IuserPaxBaseFare = $_IuserPaxBaseFare + $_IuserperPaxMarkup;
				}
				else if($_SmarkupAddIn == "TAX"){
					$_IuserPaxTax = $_IuserPaxTax + $_IuserperPaxMarkup;
				}
			}
			
			$_Atemp['baseFare']		= $_IuserPaxBaseFare;
			$_Atemp['tax']			= $_IuserPaxTax;
			$_Atemp['fees']			= $_IuserPaxFee;
			$_Atemp['totalFare']	= $_IuserPaxTotalFare;
			$_Atemp['baggageFare']	= $_IuserPaxBaggageFare;
			$_Atemp['mealsFare']	= $_IuserPaxMealsFare;
			$_Atemp['seatFare']		= $_IuserPaxSeatFare;
			
			$_Atemp['api']['baseFare']		= $_IapiPaxBaseFare;
			$_Atemp['api']['tax']			= $_IapiPaxTax;
			$_Atemp['api']['fees']			= $_IapiPaxFee;
			$_Atemp['api']['totalFare']		= $_IapiPaxTotalFare;
			$_Atemp['api']['baggageFare']	= $_IapiPaxBaggageFare;
			$_Atemp['api']['mealsFare']		= $_IapiPaxMealsFare;
			$_Atemp['api']['seatFare']		= $_IapiPaxSeatFare;
			
			$_Atemp['default']['baseFare']		= $_IdefaultPaxBaseFare;
			$_Atemp['default']['tax']			= $_IdefaultPaxTax;
			$_Atemp['default']['fees']			= $_IdefaultPaxFee;
			$_Atemp['default']['totalFare']		= $_IdefaultPaxTotalFare;
			$_Atemp['default']['baggageFare']	= $_IdefaultPaxBaggageFare;
			$_Atemp['default']['mealsFare']		= $_IdefaultPaxMealsFare;
			$_Atemp['default']['seatFare']		= $_IdefaultPaxSeatFare;
			
			if(!in_array($_SpaxType,$_ApaxCheckTemp)){
				
				$_ApaxCheckTemp[] = $_SpaxType;
				
				$_AtempPaxFares 				= array();
				$_AtempPaxFares['paxType']		= $_SpaxType;
				$_AtempPaxFares['baseFare']		= $_Atemp['baseFare'];
				$_AtempPaxFares['tax']			= $_Atemp['tax'];
				$_AtempPaxFares['fees']			= $_Atemp['fees'];
				$_AtempPaxFares['totalFare']	= $_Atemp['baseFare'] + $_Atemp['tax'] + $_Atemp['fees'];
				
				$_ApaxFares[] = $_AtempPaxFares;
				
				$_AtempApiPaxFares 				= array();
				$_AtempApiPaxFares['paxType']	= $_SpaxType;
				$_AtempApiPaxFares['baseFare']	= $_Atemp['api']['baseFare'];
				$_AtempApiPaxFares['tax']		= $_Atemp['api']['tax'];
				$_AtempApiPaxFares['fees']		= $_Atemp['api']['fees'];
				$_AtempApiPaxFares['totalFare']	= $_Atemp['api']['baseFare'] + $_Atemp['api']['tax'] + $_Atemp['api']['fees'];
				
				$_AapiPaxFares[] = $_AtempApiPaxFares;
				
				$_AtempDefaultPaxFares 				= array();
				$_AtempDefaultPaxFares['paxType']	= $_SpaxType;
				$_AtempDefaultPaxFares['baseFare']	= $_Atemp['default']['baseFare'];
				$_AtempDefaultPaxFares['tax']		= $_Atemp['default']['tax'];
				$_AtempDefaultPaxFares['fees']		= $_Atemp['default']['fees'];
				$_AtempDefaultPaxFares['totalFare']	= $_Atemp['default']['baseFare'] + $_Atemp['default']['tax'] + $_Atemp['default']['fees'];
				
				$_AdefaultPaxFares[] = $_AtempDefaultPaxFares;
				
				$_ApaxTaxBreakup = $_ApaxFareInfo['TaxBreakup'];
				
				foreach($_ApaxTaxBreakup as $taxKey=>$taxVal){
					
					if($taxVal['key'] != "TotalTax"){
						
						$_IapiTaxValue		= $taxVal['value'];
						
						$_IdefaultTaxValue	= $_IapiTaxValue / $_IapiExchangeRate;
						$_IdefaultTaxValue	= Common::getRoundedFare($_IdefaultTaxValue,$_SdefaultCurrency);
						
						$_IuserTaxValue  	= $_IdefaultTaxValue * $_IuserExchangeRate;
						$_IuserTaxValue		= Common::getRoundedFare($_IuserTaxValue,$_SuserCurrency);
			
						$_AtempTax 						= array();
						$_AtempTax['taxCode'] 			= $taxVal['key'];
						$_AtempTax['amount'] 			= $_IuserTaxValue;
						$_AtempTax['taxDescription'] 	= $taxVal['key'];
						
						if($_SapiCurrency == $_SuserCurrency){
							$_AtempTax['amount'] = $_IapiTaxValue;
						}
						
						$_AtaxBreakUp[$_SpaxType][] = $_AtempTax;
						
						
						$_AtempTax['amount'] = $_IapiTaxValue;
						
						$_AapiTaxBreakUp[$_SpaxType][] = $_AtempTax;
						
						
						$_AtempTax['amount'] = $_IdefaultTaxValue;
						
						$_AdefaultTaxBreakUp[$_SpaxType][] = $_AtempTax;
					}
				}
				
				if($_IuserperPaxMarkup != 0 && in_array($_SpaxType,array('ADT','CHD'))){
								
					if($_SmarkupAddIn == "TAX"){
						$_AtempTax 						= array();
						$_AtempTax['taxCode'] 			= $_SmarkupTaxComponent;
						$_AtempTax['amount'] 			= $_IuserperPaxMarkup;
						$_AtempTax['taxDescription'] 	= 'Tax';
						$_AtaxBreakUp[$_SpaxType][] = $_AtempTax;
					}
				}
			}
			
			$_ApassengerDetails[] = $_Atemp;
		}
		
		$_AreturnData['paxDetails'] = $_ApassengerDetails;
		
		foreach($_AtripFlights as $tripKey=>&$tripVal){

			$tripVal['totalBaseFare']	= $_IuserBaseFare;
			$tripVal['totalTax']		= $_IuserTax;
			$tripVal['totalFees']		= $_IuserFee;
			$tripVal['totalFare']		= $_IuserTotalFare;
			$tripVal['totalBaggageFare']= $_IuserBaggageFare;
			$tripVal['totalMealsFare']  = $_IuserMealsFare;
			$tripVal['totalSeatFare']   = $_IuserSeatFare;
			$tripVal['paxFares']		= $_ApaxFares;
			$tripVal['taxBreakUP']		= $_AtaxBreakUp;
			
			$tripVal['markupTotal']		= $_IuserTotalMarkup;
			$tripVal['perPaxMarkup']	= $_IuserperPaxMarkup;
			
			$tripVal['api']['totalBaseFare']	= $_IapiBaseFare;
			$tripVal['api']['totalTax']			= $_IapiTax;
			$tripVal['api']['totalFees']		= $_IapiFee;
			$tripVal['api']['totalFare']		= $_IapiTotalFare;
			$tripVal['api']['totalBaggageFare']	= $_IapiBaggageFare;
			$tripVal['api']['totalMealsFare']  	= $_IapiMealsFare;
			$tripVal['api']['totalSeatFare']   	= $_IapiSeatFare;
			$tripVal['api']['paxFares']			= $_AapiPaxFares;
			$tripVal['api']['taxBreakUP']		= $_AapiTaxBreakUp;
			
			$tripVal['default']['totalBaseFare']	= $_IdefaultBaseFare;
			$tripVal['default']['totalTax']			= $_IdefaultTax;
			$tripVal['default']['totalFees']		= $_IdefaultFee;
			$tripVal['default']['totalFare']		= $_IdefaultTotalFare;
			$tripVal['default']['totalBaggageFare']	= $_IdefaultBaggageFare;
			$tripVal['default']['totalMealsFare']  	= $_IdefaultMealsFare;
			$tripVal['default']['totalSeatFare']   	= $_IdefaultSeatFare;
			$tripVal['default']['paxFares']			= $_AdefaultPaxFares;
			$tripVal['default']['taxBreakUP']		= $_AdefaultTaxBreakUp;
			
			if($tripKey == 0){
				
				$_AreturnData['totalBaseFare']	= $_IuserBaseFare;
				$_AreturnData['totalTax']		= $_IuserTax;
				$_AreturnData['totalFees']		= $_IuserFee;
				$_AreturnData['totalFare']		= $_IuserTotalFare;
				$_AreturnData['totalBaggageFare']= $_IuserBaggageFare;
				$_AreturnData['totalMealsFare']  = $_IuserMealsFare;
				$_AreturnData['totalSeatFare']   = $_IuserSeatFare;
				
				$_AreturnData['PublishedFare']   	= $_IuserPublishedFare;
				$_AreturnData['OfferedFare']   		= $_IuserOfferedFare;
				$_AreturnData['CommissionEarned']   = $_IuserCommissionEarned;
				$_AreturnData['ServiceFee']   		= $_IuserServiceFee;
				
				$_AreturnData['api']['totalBaseFare']	= $_IapiBaseFare;
				$_AreturnData['api']['totalTax']		= $_IapiTax;
				$_AreturnData['api']['totalFees']		= $_IapiFee;
				$_AreturnData['api']['totalFare']		= $_IapiTotalFare;
				$_AreturnData['api']['totalBaggageFare']= $_IapiBaggageFare;
				$_AreturnData['api']['totalMealsFare']  = $_IapiMealsFare;
				$_AreturnData['api']['totalSeatFare']   = $_IapiSeatFare;
				
				$_AreturnData['default']['totalBaseFare']	= $_IdefaultBaseFare;
				$_AreturnData['default']['totalTax']		= $_IdefaultTax;
				$_AreturnData['default']['totalFees']		= $_IdefaultFee;
				$_AreturnData['default']['totalFare']		= $_IdefaultTotalFare;
				$_AreturnData['default']['totalBaggageFare']= $_IdefaultBaggageFare;
				$_AreturnData['default']['totalMealsFare']  = $_IdefaultMealsFare;
				$_AreturnData['default']['totalSeatFare']   = $_IdefaultSeatFare;
			}
			
			if($tripKey > 0){
				
				$tripVal['totalBaseFare']	= 0;
				$tripVal['totalTax']		= 0;
				$tripVal['totalFees']		= 0;
				$tripVal['totalFare']		= 0;
				$tripVal['totalBaggageFare']= 0;
				$tripVal['totalMealsFare']	= 0;
				$tripVal['totalSeatFare']	= 0;
				
				$tripVal['markupTotal']		= 0;
				$tripVal['perPaxMarkup']	= 0;
				
				$tripVal['api']['totalBaseFare']	= 0;
				$tripVal['api']['totalTax']			= 0;
				$tripVal['api']['totalFees']		= 0;
				$tripVal['api']['totalFare']		= 0;
				$tripVal['api']['totalBaggageFare']	= 0;
				$tripVal['api']['totalMealsFare']  	= 0;
				$tripVal['api']['totalSeatFare']   	= 0;
				
				$tripVal['default']['totalBaseFare']	= 0;
				$tripVal['default']['totalTax']			= 0;
				$tripVal['default']['totalFees']		= 0;
				$tripVal['default']['totalFare']		= 0;
				$tripVal['default']['totalBaggageFare']	= 0;
				$tripVal['default']['totalMealsFare']  	= 0;
				$tripVal['default']['totalSeatFare']   	= 0;
				
				foreach($tripVal['paxFares'] as $tempPaxFareKey=>$tempPaxFareVal){
					$tripVal['paxFares'][$tempPaxFareKey]['baseFare'] 	= 0;
					$tripVal['paxFares'][$tempPaxFareKey]['tax'] 		= 0;
					$tripVal['paxFares'][$tempPaxFareKey]['fees'] 		= 0;
					$tripVal['paxFares'][$tempPaxFareKey]['totalFare'] 	= 0;
				}
				
				foreach($tripVal['api']['paxFares'] as $tempPaxFareKey=>$tempPaxFareVal){
					$tripVal['api']['paxFares'][$tempPaxFareKey]['baseFare'] 	= 0;
					$tripVal['api']['paxFares'][$tempPaxFareKey]['tax'] 		= 0;
					$tripVal['api']['paxFares'][$tempPaxFareKey]['fees'] 		= 0;
					$tripVal['api']['paxFares'][$tempPaxFareKey]['totalFare'] 	= 0;
				}
				
				foreach($tripVal['default']['paxFares'] as $tempPaxFareKey=>$tempPaxFareVal){
					$tripVal['default']['paxFares'][$tempPaxFareKey]['baseFare'] 	= 0;
					$tripVal['default']['paxFares'][$tempPaxFareKey]['tax'] 		= 0;
					$tripVal['default']['paxFares'][$tempPaxFareKey]['fees'] 		= 0;
					$tripVal['default']['paxFares'][$tempPaxFareKey]['totalFare'] 	= 0;
				}
				
				foreach($tripVal['taxBreakUP'] as $tempTaxKey=>$tempTaxVal){
					foreach($tempTaxVal as $tempTaxBreakupKey=>$tempTaxBreakupVal){
						$tripVal['taxBreakUP'][$tempTaxKey][$tempTaxBreakupKey]['amount'] = 0;
					}
				}
				
				foreach($tripVal['api']['taxBreakUP'] as $tempTaxKey=>$tempTaxVal){
					foreach($tempTaxVal as $tempTaxBreakupKey=>$tempTaxBreakupVal){
						$tripVal['api']['taxBreakUP'][$tempTaxKey][$tempTaxBreakupKey]['amount'] = 0;
					}
				}
				
				foreach($tripVal['default']['taxBreakUP'] as $tempTaxKey=>$tempTaxVal){
					foreach($tempTaxVal as $tempTaxBreakupKey=>$tempTaxBreakupVal){
						$tripVal['default']['taxBreakUP'][$tempTaxKey][$tempTaxBreakupKey]['amount'] = 0;
					}
				}
			}
		}
		
		if($_AreturnData['bookingStatus'] == 1 && $_AreturnData['ticketStatus'] == 0){
			$_AreturnData['bookingStatus']	 = 'CONFIRMED';
			$_AreturnData['bookingStatusNo'] = 2;
		}
		else if($_AreturnData['bookingStatus'] == 1 && $_AreturnData['ticketStatus'] == 1){
			$_AreturnData['bookingStatus']	 = 'CONFIRMED';
			$_AreturnData['bookingStatusNo'] = 1;
		}
		else if($_AreturnData['bookingStatus'] == 2){
			$_AreturnData['bookingStatus']   = 'HOLD';
			$_AreturnData['bookingStatusNo'] = 2;
		}
		else if($_AreturnData['bookingStatus'] == 3){
			$_AreturnData['bookingStatus']   = 'CANCELLED';
			$_AreturnData['bookingStatusNo'] = 3;
		}
		else if($_AreturnData['bookingStatus'] == 12){
			$_AreturnData['bookingStatus']   = 'CANCELLED';
			$_AreturnData['bookingStatusNo'] = 3;
		}
		else if($_AreturnData['bookingStatus'] == 5){
			$_AreturnData['bookingStatus']   = 'CONFIRMED';
			$_AreturnData['bookingStatusNo'] = 1;
		}
		
		if($_AreturnData['ticketStatus'] == 1){
			$_AreturnData['bookingStatus']	 = 'CONFIRMED';
			$_AreturnData['bookingStatusNo'] = 1;
		}
		
		if(isset($this->_Ainput['dbBookingStatus']) && $this->_Ainput['dbBookingStatus'] == 1 && $_AreturnData['bookingStatus'] != 'CONFIRMED'){
			$_AreturnData['bookingStatus']	 = 'CONFIRMED';
			$_AreturnData['bookingStatusNo'] = 1;
		}
		
		if(isset($this->_Ainput['dbBookingStatus']) && $this->_Ainput['dbBookingStatus'] == 2 && $_AreturnData['bookingStatus'] != 'HOLD'){
			$_AreturnData['bookingStatus']	 = 'HOLD';
			$_AreturnData['bookingStatusNo'] = 2;
		}
		
		if(isset($this->_Ainput['dbBookingStatus']) && $this->_Ainput['dbBookingStatus'] == 3 && $_AreturnData['bookingStatus'] !='CANCELLED'){
			$_AreturnData['bookingStatus']	 = 'CANCELLED';
			$_AreturnData['bookingStatusNo'] = 3;
		}
		
		$_AreturnData['flightDetails'] = $_AtripFlights;
		
		return $_AreturnData;
	}

	function fun1()
	{
		$json='';
		return $json; 
	}
}
?>