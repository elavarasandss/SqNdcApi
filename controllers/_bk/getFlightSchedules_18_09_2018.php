<?php
/**
	@File Name 		:	getFlightSchedules.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	getFlightSchedules service
*/
class getFlightSchedules extends Execute
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
		$this->_SrequestUrl = $this->_Oconf['userSettings']['apiUrl']['flightSearch'];
		
		if(!isset($this->_AinputData['sectorInfo']) && isset($this->_AinputData['origin'])){
			$this->_AinputData['sectorInfo'][0]['origin'] 		 = $this->_AinputData['origin'];
			$this->_AinputData['sectorInfo'][0]['destination'] 	 = $this->_AinputData['destination'];
			$this->_AinputData['sectorInfo'][0]['departureDate'] = $this->_AinputData['departureDate'];
		}
	}
	
    public function _doGetFlightSchedules()
	{
		$_Bstatus  = true;
		$_Smessage = '';
		$_Adata	   = array();
		$_Areturn  = array();
		
		$this->_modifyData();
		$this->_setData();

		if(isset($this->_Ainput['calenderSearch']) && $this->_Ainput['calenderSearch'] == "Y"){
			$_OgetCalendarFare	= controllerGet::getObject('getCalendarFare',$this);				
			$_AgetCalendarFare	= $_OgetCalendarFare->_doGetCalendarFare();
		}
		
		$_IpaxCount			= $this->_Ainput['ADT'] + $this->_Ainput['CHD'];
		
		$_AflightResult		= $this->_executeService();
		
		if(isset($this->_Ainput['printApiResponse']) && $this->_Ainput['printApiResponse'] == "Y"){
			//echo "<pre>";print_r($_AflightResult);exit;
		}
		#echo "<pre>";print_r($_AflightResult);exit;
		
		$_AflightSchedules  = array();
		
		if(isset($_AflightResult['Response']['ResponseStatus']) && $_AflightResult['Response']['ResponseStatus'] == 1 && isset($_AflightResult['Response']['Results']) && count($_AflightResult['Response']['Results']) > 0){
			
			$_StraceId			= $_AflightResult['Response']['TraceId'];
			$_AflightResults	= $_AflightResult['Response']['Results'];
			
			#echo "<pre>";print_r($_AflightResult);exit;
			
			/*if(isset($_AflightResults[0]) && !empty($_AflightResults[0])){
				$_AflightResults = $_AflightResults[0];
			}*/
			
			if(isset($this->_Ainput['advancedSearch']) && $this->_Ainput['advancedSearch'] == "Y"){
				
				$_AnewFlights = array();
				$_BflightSet  = false;
				
				foreach($_AflightResults as $mainKey=>$mainVal){
					
					foreach($mainVal as $key=>$val){
						
						$_Asegments = $val['Segments'];
						
						foreach($_Asegments as $segmentKey=>$segmentVal){
							
							foreach($segmentVal as $segmentSubKey=>$segmentSubVal){
								
								if($segmentSubVal['Airline']['AirlineCode'] == "9W" && !$_BflightSet){
									
									$_AnewFlights[0][0] = $val;
									$_BflightSet = true;
								}
							}
							
						}
						
					}
				}
				$_AflightResults = $_AnewFlights;
			}
			
			if(count($_AflightResults) > 0){
				
				$_SfareType = 'S';
				
				if(count($_AflightResults) > 1){
					$_SfareType = 'M';
				}
				
				foreach($_AflightResults as $mainKey=>$mainVal){
					
					foreach($mainVal as $key=>$val){
						
						// Refundable , lcc settings
						
						$_SisLcc 			= ($val['IsLCC'] == true) ? "Y" : "N";
						$_Srefundable 		= ($val['IsRefundable'] == true) ? "Y" : "N";
						$_SresultIndex 		= $val['ResultIndex'];
						$_SflightMergeKey	= $_SresultIndex;
						
						if($_SfareType == 'M'){
							$_SflightMergeKey = 'ANY';
						}
							
						// Flight details setting
						
						$_Asegments			= $val['Segments'];
						
						if(isset($this->_Ainput['advancedSearch']) && $this->_Ainput['advancedSearch'] == "Y"){
							$_ApriceRbdVal		= $val;
							unset($_ApriceRbdVal['GSTAllowed']);
							unset($_ApriceRbdVal['Segments']);
							unset($_ApriceRbdVal['LastTicketDate']);
							unset($_ApriceRbdVal['TicketAdvisory']);
							unset($_ApriceRbdVal['AirlineCode']);
							unset($_ApriceRbdVal['ValidatingAirline']);
						}
						
						$_AtripFlights		= array();
						$_AcarrierCodes		= array();
						
						foreach($_Asegments as $segmentKey=>$segmentVal){
							
							$_IflightScheduleIndex 	= $segmentKey;
							
							if($_SfareType == 'M'){
								$_IflightScheduleIndex = $mainKey;
							}
							
							$_AflightDetails    	= array();
							$viaFlights				= array();
							$journeySellKey			= '';
							$sellKeyAppend			= '';
							
							foreach($segmentVal as $segmentSubKey=>$segmentSubVal){
								
								if(isset($this->_Ainput['advancedSearch']) && $this->_Ainput['advancedSearch'] == "Y"){
									
									$segmentSubVal['NoOfSeatAvailable'] = $segmentSubVal['Availability'][0]['Seats'];
									
									$_ApriceRbdVal['Segments'][$segmentKey][$segmentSubKey]['Airline']			= $segmentSubVal['Airline'];
									$_ApriceRbdVal['Segments'][$segmentKey][$segmentSubKey]['TripIndicator']	= $segmentSubVal['TripIndicator'];
									$_ApriceRbdVal['Segments'][$segmentKey][$segmentSubKey]['SegmentIndicator']	= $segmentSubVal['SegmentIndicator'];
									
									$_ApriceRbdVal['Segments'][$segmentKey][$segmentSubKey]['Airline']['FareClass'] = $segmentSubVal['Availability'][0]['Class'];									
								}
								
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
								
								$_AcarrierCodes[] = $viaFlights[$segmentSubKey]['carrierCode'];
							
								$viaFlights[$segmentSubKey]['classType'] 			= 'Economy';
								$viaFlights[$segmentSubKey]['fareBasisCode'] 		= $segmentSubVal['Airline']['FareClass'];
								$viaFlights[$segmentSubKey]['classOfService'] 		= $segmentSubVal['Airline']['FareClass'];
								$viaFlights[$segmentSubKey]['fareClassOfService']	= $segmentSubVal['Airline']['FareClass'];
								
								$viaFlights[$segmentSubKey]['refundable'] 			= $_Srefundable;
								$viaFlights[$segmentSubKey]['canHold'] 				= ($_SisLcc == "Y") ? "N" : "Y";
								$viaFlights[$segmentSubKey]['flightMergeKey'] 		= $_SflightMergeKey;
							
								$stdExplode 		= explode("T",$viaFlights[$segmentSubKey]['std']);
								$tmpDepartureDate 	= $stdExplode[0];
								$tmpDepartureTime 	= substr($stdExplode[1],0,5);
								
								$viaFlights[$segmentSubKey]['departureDate'] 	= $tmpDepartureDate;
								$viaFlights[$segmentSubKey]['departureTime'] 	= substr($stdExplode[1],0,5);
								
								$staExplode 		= explode("T",$viaFlights[$segmentSubKey]['sta']);
								$tmpArrivalDate 	= $staExplode[0];
								$tmpArrivalTime 	= substr($staExplode[1],0,5);
								//Fare
								$viaFlights[$segmentSubKey]['arrivalDate'] 			= $tmpArrivalDate;
								$viaFlights[$segmentSubKey]['arrivalTime'] 			= $tmpArrivalTime;
								
								$viaFlights[$segmentSubKey]['departureTerminal'] 	= $segmentSubVal['Origin']['Airport']['AirportName']." ".$segmentSubVal['Origin']['Airport']['Terminal'];
								$viaFlights[$segmentSubKey]['arrivalTerminal'] 		= $segmentSubVal['Destination']['Airport']['AirportName']." ".$segmentSubVal['Destination']['Airport']['Terminal'];
								
								$viaFlights[$segmentSubKey]['capacity'] 			= $segmentSubVal['NoOfSeatAvailable'];
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
							}
							
							$_SisSpecialReturn = (isset($this->_Ainput['specialReturn']) && $this->_Ainput['specialReturn'] == "Y") ? "Y" : "N";
							
							$journeySellKey .= "@@@".$_SisLcc."@@@".$_SresultIndex."@@@".$_StraceId."@@@".$_SisSpecialReturn;
							
							$_AflightDetails['departureStation'] = $journeyDepartureStation;
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
							$_AflightDetails['refundable'] 		= $_Srefundable;
							$_AflightDetails['canHold'] 		= ($_SisLcc == "Y") ? "N" : "Y";
							$_AflightDetails['flightMergeKey'] 	= $_SflightMergeKey;
							
							$_AtripFlights[$_IflightScheduleIndex] = $_AflightDetails;
							
						}
						
						if(isset($this->_Ainput['advancedSearch']) && $this->_Ainput['advancedSearch'] == "Y"){
							
							$_ApriceRbdInput = $_ApriceRbdVal;
							
							$this->_Ainput['flightTraceId']	= $_StraceId;
							$this->_Ainput['priceRbd']		= $_ApriceRbdInput;
							
							$_OpriceRbd			= controllerGet::getObject('priceRbd',$this);				
							$_ApriceRbdResponse	= $_OpriceRbd->_doPriceRbd();
						
							$val['Fare']			= $_ApriceRbdResponse['data']['Fare'];
							$val['FareBreakdown']	= $_ApriceRbdResponse['data']['FareBreakdown'];
							
						}
						
						// Fare settings
						
						$_AfareInfo			= $val['Fare'];
						
						$_SapiCurrency 		= $_AfareInfo['Currency'];
						$_IapiExchangeRate 	= isset($GLOBALS['CONF']['exchangeRate'][$_SapiCurrency]) ? $GLOBALS['CONF']['exchangeRate'][$_SapiCurrency] : 1;
						
						$_SuserCurrency 	= $this->_Ainput['currencyCode'];
						$_IuserExchangeRate	= isset($GLOBALS['CONF']['exchangeRate'][$_SuserCurrency]) ? $GLOBALS['CONF']['exchangeRate'][$_SuserCurrency] : 1;
						
						$_SdefaultCurrency 	= $GLOBALS['CONF']['site']['defaultCurrency'];
						
						if($this->_Oconf['userSettings']['userName'] == "NUGDSIN"){
							
							// Api fares
							if($_AfareInfo['OfferedFare'] < $_AfareInfo['PublishedFare']){
								
								$_IapiBaseFare 			=  $_AfareInfo['OfferedFare'] - $_AfareInfo['Tax'] - $_AfareInfo['AdditionalTxnFeePub'];    //$_AfareInfo['BaseFare'];
								$_IapiTotalFare			= $_AfareInfo['OfferedFare'];//$_AfareInfo['PublishedFare'];
								
							}
							else{
								
								$_IapiBaseFare 			=  $_AfareInfo['BaseFare'];    //$_AfareInfo['BaseFare'];
								
							}
							$_IapiTax				= $_AfareInfo['Tax'];
							$_IapiTotalFare			= $_AfareInfo['OfferedFare'];//$_AfareInfo['PublishedFare'];
							$_IapiOfferFare			= $_AfareInfo['PublishedFare'];
						}
						else{
							
							$_IapiBaseFare 			=  $_AfareInfo['BaseFare'];    //$_AfareInfo['BaseFare'];
							$_IapiTotalFare			= $_AfareInfo['PublishedFare'];//$_AfareInfo['OfferedFare'];//$_AfareInfo['PublishedFare'];
							$_IapiTax				= $_AfareInfo['Tax'];
							$_IapiOfferFare			= $_AfareInfo['OfferedFare'];
						}
						
						
						$_IapiFee				= $_AfareInfo['OtherCharges']+$_AfareInfo['ServiceFee']+$_AfareInfo['AdditionalTxnFeePub']+$_AfareInfo['PGCharge'];
						$_IapiOtherFee			= $_AfareInfo['OtherCharges']+$_AfareInfo['ServiceFee'];
						
						/*$_IapiFee				= $_AfareInfo['OtherCharges']+$_AfareInfo['AdditionalTxnFeePub']+$_AfareInfo['PGCharge'];
						$_IapiOtherFee			= $_AfareInfo['OtherCharges'];*/
						
						$_IapiOtherFeePerPax	= $_IapiOtherFee / $_IpaxCount;
						
						$_IapiServiceFee		= $_AfareInfo['ServiceFee'];
						$_IapiServiceFeePerPax	= $_IapiServiceFee / $_IpaxCount;
						
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
						
						$_IdefaultTotalFare		= $_IapiTotalFare / $_IapiExchangeRate;
						$_IdefaultTotalFare		= Common::getRoundedFare($_IdefaultTotalFare,$_SdefaultCurrency);
						
						$_IdefaultOfferFare  	= $_IapiOfferFare / $_IapiExchangeRate;
						$_IdefaultOfferFare  	= Common::getRoundedFare($_IdefaultOfferFare,$_SdefaultCurrency);
						
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
						$_IofferedFare;$_AfareInfo['OfferedFare'];
						
						$_IuserOfferFare		= $_IdefaultOfferFare * $_IuserExchangeRate;
						$_IuserOfferFare		= Common::getRoundedFare($_IuserOfferFare,$_SuserCurrency);
						
						
						
						if($_SapiCurrency == $_SuserCurrency){
							
							$_IuserBaseFare  		= $_IapiBaseFare;
							$_IuserTax				= $_IapiTax;
							$_IuserFee				= $_IapiFee;
							$_IuserOtherFee			= $_IapiOtherFee;
							$_IuserOtherFeePerPax	= $_IapiOtherFeePerPax;							
							$_IuserTotalFare		= $_IapiTotalFare;
						}
						
						// Markup setting
						
						$_AdefaultMarkupData = array();
						$_IuserTotalMarkup	 = 0;
						$_IuserperPaxMarkup	 = 0;
						$_SmarkupAddIn	 	 = '';
						$_SmarkupTaxComponent= '';
						
						if(isset($GLOBALS['CONF']['userSettings']['markupDetails']) && count($GLOBALS['CONF']['userSettings']['markupDetails']) > 0 && $mainKey == 0){
					
							$_AmarkupInput						= array();
							$_AmarkupInput['markupDetails']		= $GLOBALS['CONF']['userSettings']['markupDetails'];
							$_AmarkupInput['paxCount']			= $this->_Ainput['ADT'] + $this->_Ainput['CHD'];
							$_AmarkupInput['flightCount']		= count($_AtripFlights);
							$_AmarkupInput['airlineCodes']		= $_AcarrierCodes;
							$_AmarkupInput['totalAmount']		= $_IdefaultTotalFare;
							$_AmarkupInput['referenceId']		= $this->_Ainput['referenceId'];
							$_AmarkupInput['flightDetails']		= $_AtripFlights;
							
							$_AdefaultMarkupData = $this->calculateMarkupValue($_AmarkupInput);
							
							if(isset($_AdefaultMarkupData['markupValue']) && !empty($_AdefaultMarkupData['markupValue'])){
						
								$_IuserTotalMarkup	= $_AdefaultMarkupData['markupValue'] * $_IuserExchangeRate;
								$_IuserTotalMarkup	= Common::getRoundedFare($_IuserTotalMarkup,$_SuserCurrency);
								
								$_IuserperPaxMarkup	= $_AdefaultMarkupData['perPaxMarkup'] * $_IuserExchangeRate;
								$_IuserperPaxMarkup	= Common::getRoundedFare($_IuserperPaxMarkup,$_SuserCurrency);
								
								$_IuserTotalFare 	= $_IuserTotalFare + $_IuserTotalMarkup;
								
								$_SmarkupAddIn		= $_AdefaultMarkupData['markupAddIn'];
								
								if($_AdefaultMarkupData['markupAddIn'] == "BF"){
									$_IuserBaseFare = $_IuserBaseFare + $_IuserTotalMarkup;
								}
								else if($_AdefaultMarkupData['markupAddIn'] == "TAX"){
									$_IuserTax 				= $_IuserTax + $_IuserTotalMarkup;
									$_SmarkupTaxComponent 	= $_AdefaultMarkupData['markupTaxComponent'];
								}
							}
						}
						
						// Paxwise fares and tax details
						
						$_ItaxTransactionFee = 0;
						$_IairTransFee		 = 0;
						
						foreach($_AfareInfo['TaxBreakup'] as $fareTakKey=>$fareTaxVal){
							
							if($fareTaxVal['key'] == "TransactionFee"){
								$_ItaxTransactionFee = $fareTaxVal['value'];
							}
							
							if($fareTaxVal['key'] == "AirTransFee"){
								$_IairTransFee = $fareTaxVal['value'];
							}
						}
						
						if($_IairTransFee != 0 && isset($_AfareInfo['AirTransFee'])){
							$_IairTransFee = $_AfareInfo['AirTransFee'];
						}
						
						$_ApaxFares			= array();
						$_AtaxBreakup		= array();
						$_AfareBreakdown	= $val['FareBreakdown'];
						
						//echo "<pre>";print_r($_AfareBreakdown);
						
						foreach($_AfareBreakdown as $fareKey=>$fareVal){
							
							$_SpaxType = '';
							
							if($fareVal['PassengerType'] == 1){
								$_SpaxType = 'ADT';
							}
							else if($fareVal['PassengerType'] == 2){
								$_SpaxType = 'CHD';
							}
							else if($fareVal['PassengerType'] == 3){
								$_SpaxType = 'INF';
							}
							
							$_IfarePaxCount		= $fareVal['PassengerCount'];
							
							// Api fares
							
							
							$_IapiPaxBaseFare 		= ($fareVal['BaseFare']/$_IfarePaxCount);
							$_IapiPaxTax 			= ($fareVal['Tax']/$_IfarePaxCount);
							$_IapiPaxYqTax 			= ($fareVal['YQTax']/$_IfarePaxCount);
							$_IapiPaxTxnFeePub 		= ($fareVal['AdditionalTxnFeePub']/$_IfarePaxCount);
							$_IapiPaxTxnFeeOff 		= ($fareVal['AdditionalTxnFeeOfrd']/$_IfarePaxCount);
							$_IapiPaxPGCharge 		= ($fareVal['PGCharge']/$_IfarePaxCount);
							
							$_IapiPaxFeeTotal		= $_IapiPaxTxnFeePub + $_IapiPaxPGCharge + $_IapiOtherFeePerPax;
							
							if($_SpaxType == 'INF'){
								$_IapiPaxFeeTotal = $_IapiPaxFeeTotal - ($_IapiOtherFeePerPax+$_IapiServiceFeePerPax);
							}
							
							$_IapiPaxTotalFare		= $_IapiPaxBaseFare + $_IapiPaxTax + $_IapiPaxFeeTotal;
							
							if($_AfareInfo['OfferedFare'] < $_AfareInfo['PublishedFare']){
							
								$_IapiPaxBaseFare 			=  $_IapiPaxBaseFare - $_IapiPaxTxnFeePub;    //$_AfareInfo['BaseFare'];
							}
							
							
							// Default fares
							
							$_IdefaultPaxBaseFare 	= $_IapiPaxBaseFare / $_IapiExchangeRate;
							$_IdefaultPaxBaseFare	= Common::getRoundedFare($_IdefaultPaxBaseFare,$_SdefaultCurrency);
							
							$_IdefaultPaxTax 		= $_IapiPaxTax / $_IapiExchangeRate;
							$_IdefaultPaxTax		= Common::getRoundedFare($_IdefaultPaxTax,$_SdefaultCurrency);
							
							$_IdefaultPaxYqTax 		= $_IapiPaxYqTax / $_IapiExchangeRate;
							$_IdefaultPaxYqTax		= Common::getRoundedFare($_IdefaultPaxYqTax,$_SdefaultCurrency);
							
							$_IdefaultPaxTxnFeePub	= $_IapiPaxTxnFeePub / $_IapiExchangeRate;
							$_IdefaultPaxTxnFeePub	= Common::getRoundedFare($_IdefaultPaxTxnFeePub,$_SdefaultCurrency);
							
							$_IdefaultPaxTxnFeeOff 	= $_IapiPaxTxnFeeOff / $_IapiExchangeRate;
							$_IdefaultPaxTxnFeeOff	= Common::getRoundedFare($_IdefaultPaxTxnFeeOff,$_SdefaultCurrency);
							
							$_IdefaultPaxPGCharge 	= $_IapiPaxPGCharge / $_IapiExchangeRate;
							$_IdefaultPaxPGCharge	= Common::getRoundedFare($_IdefaultPaxPGCharge,$_SdefaultCurrency);
							
							$_IdefaultPaxFeeTotal 	= $_IapiPaxFeeTotal / $_IapiExchangeRate;
							$_IdefaultPaxFeeTotal	= Common::getRoundedFare($_IdefaultPaxFeeTotal,$_SdefaultCurrency);
							
							$_IdefaultPaxTotalFare 	= $_IapiPaxTotalFare / $_IapiExchangeRate;
							$_IdefaultPaxTotalFare	= Common::getRoundedFare($_IdefaultPaxTotalFare,$_SdefaultCurrency);
							
							// User fares
							
							$_IuserPaxBaseFare 	= $_IdefaultPaxBaseFare * $_IuserExchangeRate;
							$_IuserPaxBaseFare	= Common::getRoundedFare($_IuserPaxBaseFare,$_SuserCurrency);
							
							$_IuserPaxTax 		= $_IdefaultPaxTax * $_IuserExchangeRate;
							$_IuserPaxTax		= Common::getRoundedFare($_IuserPaxTax,$_SuserCurrency);
							
							$_IuserPaxYqTax 	= $_IdefaultPaxYqTax * $_IuserExchangeRate;
							$_IuserPaxYqTax		= Common::getRoundedFare($_IuserPaxYqTax,$_SuserCurrency);
							
							$_IuserPaxTxnFeePub	= $_IdefaultPaxTxnFeePub * $_IuserExchangeRate;
							$_IuserPaxTxnFeePub	= Common::getRoundedFare($_IuserPaxTxnFeePub,$_SuserCurrency);
							
							$_IuserPaxTxnFeeOff = $_IdefaultPaxTxnFeeOff * $_IuserExchangeRate;
							$_IuserPaxTxnFeeOff	= Common::getRoundedFare($_IuserPaxTxnFeeOff,$_SuserCurrency);
							
							$_IuserPaxPGCharge 	= $_IdefaultPaxPGCharge * $_IuserExchangeRate;
							$_IuserPaxPGCharge	= Common::getRoundedFare($_IuserPaxPGCharge,$_SuserCurrency);
							
							$_IuserPaxFeeTotal 	= $_IdefaultPaxFeeTotal * $_IuserExchangeRate;
							$_IuserPaxFeeTotal	= Common::getRoundedFare($_IuserPaxFeeTotal,$_SuserCurrency);
							
							$_IuserAdjTax		= 0;
							
							if($_IuserPaxTax != $_IuserPaxFeeTotal){
								$_IuserAdjTax = $_IuserPaxTax - ($_IuserPaxFeeTotal+$_IuserPaxYqTax);
							}
							
							$_IuserAdjTax		= Common::getRoundedFare($_IuserAdjTax,$_SuserCurrency);
							
							$_IuserPaxTotalFare = $_IdefaultPaxTotalFare * $_IuserExchangeRate;
							$_IuserPaxTotalFare	= Common::getRoundedFare($_IuserPaxTotalFare,$_SuserCurrency);
							
							if($_SapiCurrency == $_SuserCurrency){
								
								$_IuserPaxBaseFare 	= $_IapiPaxBaseFare;
								$_IuserPaxTax 		= $_IapiPaxTax;
								$_IuserPaxYqTax 	= $_IapiPaxYqTax;
								$_IuserPaxTxnFeePub	= $_IapiPaxTxnFeePub;
								$_IuserPaxTxnFeeOff = $_IapiPaxTxnFeeOff;
								$_IuserPaxPGCharge 	= $_IapiPaxPGCharge;
								$_IuserPaxFeeTotal 	= $_IapiPaxFeeTotal;
								
								$_IuserAdjTax		= 0;
								
								if($_IuserPaxTax != $_IuserPaxFeeTotal){
									$_IuserAdjTax = $_IuserPaxTax - ($_IuserPaxFeeTotal+$_IuserPaxYqTax);
								}
								
								$_IuserAdjTax		= Common::getRoundedFare($_IuserAdjTax,$_SuserCurrency);
								
								$_IuserPaxTotalFare = $_IapiPaxTotalFare;
							}
							
							// Markup setting
							
							if($_IuserperPaxMarkup != 0 && in_array($_SpaxType,array('ADT','CHD'))){
								
								$_IuserPaxTotalFare = $_IuserPaxTotalFare + $_IuserperPaxMarkup;
								
								if($_SmarkupAddIn == "BF"){
									$_IuserPaxBaseFare = $_IuserPaxBaseFare + $_IuserperPaxMarkup;
								}
								else if($_SmarkupAddIn == "TAX"){
									$_IuserPaxTax = $_IuserPaxTax + $_IuserperPaxMarkup;
								}
							}
							
							$fareVal['TransactionFee'] 	= $_ItaxTransactionFee;
							$fareVal['AirTransFee']		= $_IairTransFee;
						
							$_ApaxFares[$fareKey]['paxType']	= $_SpaxType;
							$_ApaxFares[$fareKey]['baseFare']	= $_IuserPaxBaseFare;
							$_ApaxFares[$fareKey]['tax']		= $_IuserPaxTax;
							$_ApaxFares[$fareKey]['fees']		= $_IuserPaxFeeTotal;
							$_ApaxFares[$fareKey]['totalFare']	= $_IuserPaxTotalFare;
							$_ApaxFares[$fareKey]['fareInfoRef']= base64_encode(json_encode($fareVal));
							
							if(!isset($_AtaxBreakup[$_SpaxType])){
								$_AtaxBreakup[$_SpaxType] = array();
							}
							
							if($_IuserPaxYqTax != 0){
								$_AtempTax 						= array();
								$_AtempTax['taxCode'] 			= 'YQ';
								$_AtempTax['amount'] 			= $_IuserPaxYqTax;
								$_AtempTax['taxDescription'] 	= 'Tax';
								$_AtaxBreakup[$_SpaxType][] = $_AtempTax;
							}
							
							if($_IuserPaxTxnFeePub != 0){
								$_AtempTax 						= array();
								$_AtempTax['taxCode'] 			= 'TFP';
								$_AtempTax['amount'] 			= $_IuserPaxTxnFeePub;
								$_AtempTax['taxDescription'] 	= 'Tax';
								$_AtaxBreakup[$_SpaxType][] = $_AtempTax;
							}
							
							if($_IuserPaxPGCharge != 0){
								$_AtempTax 						= array();
								$_AtempTax['taxCode'] 			= 'PGC';
								$_AtempTax['amount'] 			= $_IuserPaxPGCharge;
								$_AtempTax['taxDescription'] 	= 'Tax';
								$_AtaxBreakup[$_SpaxType][] = $_AtempTax;
							}
							
							if($_IuserAdjTax != 0){
								$_AtempTax 						= array();
								$_AtempTax['taxCode'] 			= 'ADJ';
								$_AtempTax['amount'] 			= $_IuserAdjTax;
								$_AtempTax['taxDescription'] 	= 'Tax';
								$_AtaxBreakup[$_SpaxType][] = $_AtempTax;
							}
							
							if($_IuserOtherFeePerPax != 0 && in_array($_SpaxType,array('ADT','CHD'))){
								$_AtempTax 						= array();
								$_AtempTax['taxCode'] 			= 'OFC';
								$_AtempTax['amount'] 			= $_IuserOtherFeePerPax;
								$_AtempTax['taxDescription'] 	= 'Tax';
								$_AtaxBreakup[$_SpaxType][] = $_AtempTax;
							}
							
							// Markup setting
							
							if($_IuserperPaxMarkup != 0 && in_array($_SpaxType,array('ADT','CHD'))){
								
								if($_SmarkupAddIn == "TAX"){
									$_AtempTax 						= array();
									$_AtempTax['taxCode'] 			= $_SmarkupTaxComponent;
									$_AtempTax['amount'] 			= $_IuserperPaxMarkup;
									$_AtempTax['taxDescription'] 	= 'Tax';
									$_AtaxBreakup[$_SpaxType][] = $_AtempTax;
								}
							}
						}
						
						foreach($_AtripFlights as $tripKey=>$tripVal){

							if(!isset($_AflightSchedules[$tripKey])){
								$_AflightSchedules[$tripKey] = array();
							}
							
							$tripVal['totalBaseFare']	= $_IuserBaseFare;
							$tripVal['totalTax']		= $_IuserTax;
							$tripVal['totalFees']		= $_IuserFee;
							$tripVal['totalFare']		= $_IuserTotalFare;
							$tripVal['offeredFare']		= $_IuserOfferFare;
							
							$tripVal['paxFares']		= $_ApaxFares;
							$tripVal['taxBreakUP']		= $_AtaxBreakup;
							
							if($_SfareType == 'S' && $tripKey > 0){
								
								$tripVal['totalBaseFare']	= 0;
								$tripVal['totalTax']		= 0;
								$tripVal['totalFees']		= 0;
								$tripVal['totalFare']		= 0;
								
								foreach($tripVal['paxFares'] as $tempPaxFareKey=>$tempPaxFareVal){
									$tripVal['paxFares'][$tempPaxFareKey]['baseFare'] 	= 0;
									$tripVal['paxFares'][$tempPaxFareKey]['tax'] 		= 0;
									$tripVal['paxFares'][$tempPaxFareKey]['fees'] 		= 0;
									$tripVal['paxFares'][$tempPaxFareKey]['totalFare'] 	= 0;
								}
								
								foreach($tripVal['taxBreakUP'] as $tempTaxKey=>$tempTaxVal){
									foreach($tempTaxVal as $tempTaxBreakupKey=>$tempTaxBreakupVal){
										$tripVal['taxBreakUP'][$tempTaxKey][$tempTaxBreakupKey]['amount'] = 0;
									}
								}
							}
							
							$_AflightSchedules[$tripKey][] = $tripVal;
						}
					}
				}
			}
			else{
				$_Bstatus  = false;
				$_Smessage = 'No flights found';
			}
		}
		else{
			$_Bstatus = false;
			
			if(isset($_AflightResult['Response']['Error']['ErrorMessage']) && !empty($_AflightResult['Response']['Error']['ErrorMessage'])){
				$_Smessage = $_AflightResult['Response']['Error']['ErrorMessage'];
			}
			else{
				$_Smessage = 'No flights found';
			}
		}
			
		/*getting from xml to restric data*/
		
		$_AairlinrData  = $this->_getairlineData();
		//print_r($_AairlinrData);exit;
		$_AairlineArr = array();
		foreach($_AairlinrData as $_AairlineKey => $_AairlineVal){
								
			$_AairlineArr[] = 	$_AairlineVal['airline_code'];
		}
		
		/* getting from xml to restric data end */

		/* restricting airline based on xml data*/
		
		
		$_ACountryData = $this->_getCountryData($this->_Ainput['sectorInfo'][0]['origin']);
		$_AairlineType = $this->_getAirlineType();
		
		$_Flightdata =  ($_AflightSchedules);
		$_AreturnArrData = array();
		
		foreach($_Flightdata as $_Atripkey => $_Atripvalue){
				
				$_i=0 ;
				
			foreach($_Atripvalue as $Flightdatakey => $Flightdatavalue){
				
						$_ResponceAirlineCode = ($Flightdatavalue['viaFlights'][0]['carrierCode']);
						
						
				if(!in_array($_ResponceAirlineCode,$_AairlineArr)){

					$_AreturnArrData[$_Atripkey][$_i] = $Flightdatavalue;
					$_i++;
				}
							

			}
	
		}
		$_AairlinesArr = array();
		foreach($_AairlineType as $airlinecode){
				
			foreach($airlinecode as $airlinevalue){
				$_AairlinesArr[] = 	$airlinevalue;
			
			
			}
					
				
		}
					
				

		$_Ausers = $GLOBALS['CONF']['api']['AllowUser'];
		if(in_array($this->_Ainput['userName'],$_Ausers)){
		
			if($_ACountryData == 'SG'){	
				$_AreturnArrDatas = array();
				foreach($_AreturnArrData as $_flightDataresKey => $_flightDataresVal ){
							$_ii=0 ;
						foreach($_flightDataresVal as $_flightRespkey => $_flightRespval){
							
							$_ResponceAirlineCodes = ($_flightRespval['viaFlights'][0]['carrierCode']);
							
							
							if(in_array($_ResponceAirlineCodes,$_AairlinesArr)){
							$_AreturnArrDatas[$_flightDataresKey][$_ii] = $_flightRespval;
							$_ii++;
						}
						}
					 
						
					}
					$_AreturnArrData = $_AreturnArrDatas;
			}
			
		}
					$_Areturn = array
					(
						'status' => $_Bstatus,
						'data'   => $_AreturnArrData,
						'msg'    => $_Smessage,
					);
					
					return $_Areturn;
	
		

		//exit;
		/* restricting airline based on xml data end *///_AflightSchedules
		
		
	}
		public function _getairlineData(){
		

			$_Sqlquery = 'select distinct account_id from account_credentials_details where user_name = "'.$this->_Ainput['userName'].'"';
					$data = $this->_Odb->getAll($_Sqlquery);
					
					$_Accid = $data[0]['account_id'];
					$_AapiId 		= $this->_Oconf['site']['apiId'];
		 	$_SQL	=	'
						SELECT
							m.id,m.api,m.status, m.created_by, m.created_date,
							mam.details_id, mam.account_id,
							mcm.details_id,
							mcm.airline_code
						FROM acc_api_flightselection AS m
						LEFT JOIN api_flight_account_mapping AS mam ON m.id = mam.details_id
						LEFT JOIN api_flightp_airline_mapping AS mcm ON m.id = mcm.details_id
							WHERE 
							m.api = '.$_AapiId.' AND m.status = "Y"
							AND
							mam.account_id in("'.$_Accid.'")';
								
			$datas = $this->_Odb->getAll($_SQL);
				
		return $datas ; 


	}
	
	public function _getCountryData($_Origin){
		
		 $_SQL = 'SELECT distinct countryCode FROM `uapi_citycode` where citycode = "'.$_Origin.'"';
		$data = $this->_Odb->getAll($_SQL);
		return $data[0]['countryCode'];
		
	}
	
	public function _getAirlineType(){
		 $_SQL = 'SELECT airline_code FROM airlines_list where airline_type ="LCC" and active_status ="Y"';
		$data = $this->_Odb->getAll($_SQL);
		return $data;
	}
	
}
?>