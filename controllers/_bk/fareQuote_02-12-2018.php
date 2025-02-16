<?php
/**
	@File Name 		:	fareQuote.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	fareQuote service
*/
class fareQuote extends Execute
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
		$this->_SrequestUrl = $this->_Oconf['userSettings']['apiUrl']['fareQuote'];
	}
	
    public function _doFareQuote()
	{
		$_Bstatus  = true;
		$_Smessage = '';
		$_Adata	   = array();
		$_Areturn  = array();
		
		$this->_modifyData();
		$this->_setData();
		
		$_IpaxCount			= $this->_Ainput['ADT'] + $this->_Ainput['CHD'];
		
		$_AupdatePriceResult= $this->_executeService();
		
		$_AupdatePrice = array();
		
		if(isset($_AupdatePriceResult['Response']['ResponseStatus']) && $_AupdatePriceResult['Response']['ResponseStatus'] == 1 && isset($_AupdatePriceResult['Response']['Results']) && count($_AupdatePriceResult['Response']['Results']) > 0){
			
			$_StraceId			= $_AupdatePriceResult['Response']['TraceId'];
			$_StraceId			= $this->_Ainput['flightTraceId'];
			$_AflightResults	= $_AupdatePriceResult['Response']['Results'];
			
			if(!isset($_AflightResults[0])){
				$_AflightResults = array($_AflightResults);
			}
			
			if(count($_AflightResults) > 0){
				
				foreach($_AflightResults as $key=>$val){
					
					// Getting SSR Response 
					
					$_AssrResponse 	= array();
				
					$_OgetSsr			= controllerGet::getObject('getSsr',$this);				
					$_AgetSsrResponse	= $_OgetSsr->_doGetSsr();
					
					if(isset($_AgetSsrResponse['status']) && $_AgetSsrResponse['status'] && isset($_AgetSsrResponse['data']) && count($_AgetSsrResponse['data']) > 0){
						$_AssrResponse = $_AgetSsrResponse['data'];
					}
					
					// Refundable , lcc settings
					
					$_SisLcc 			= ($val['IsLCC'] == true) ? "Y" : "N";
					$_Srefundable 		= ($val['IsRefundable'] == true) ? "Y" : "N";
					$_SresultIndex 		= $val['ResultIndex'];
					$_SflightMergeKey	= $_SresultIndex;
					
					// Flight details setting
					
					$_Asegments			= $val['Segments'];
					$_AtripFlights		= array();
					$_AcarrierCodes		= array();
					
					foreach($_Asegments as $segmentKey=>$segmentVal){
						
						$_IflightScheduleIndex 	= $segmentKey;
						
						$_AflightDetails    	= array();
						$viaFlights				= array();
						$journeySellKey			= '';
						$sellKeyAppend			= '';
						$_BbagExist				= false;
						$_BmealExist			= false;
						$_BseatExist			= false;
						
						foreach($segmentVal as $segmentSubKey=>$segmentSubVal){
							
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
							
							$viaFlights[$segmentSubKey]['arrivalDate'] 			= $tmpArrivalDate;
							$viaFlights[$segmentSubKey]['arrivalTime'] 			= $tmpArrivalTime;
							
							$viaFlights[$segmentSubKey]['departureTerminal'] 	= $segmentSubVal['Origin']['Airport']['AirportName']." ".$segmentSubVal['Origin']['Airport']['Terminal'];
							$viaFlights[$segmentSubKey]['arrivalTerminal'] 		= $segmentSubVal['Destination']['Airport']['AirportName']." ".$segmentSubVal['Destination']['Airport']['Terminal'];
							
							$_StempSellKey = $viaFlights[$segmentSubKey]['carrierCode']."~".$viaFlights[$segmentSubKey]['flightNumber']."~".$viaFlights[$segmentSubKey]['departureStation']."~".$viaFlights[$segmentSubKey]['std']."~".$viaFlights[$segmentSubKey]['arrivalStation']."~".$viaFlights[$segmentSubKey]['sta']."~".$segmentSubVal['SegmentIndicator'];
							
							$journeySellKey .= $sellKeyAppend.$_StempSellKey;
							
							$sellKeyAppend = '~~~';
							
							$viaFlights[$segmentSubKey]['segmentSellKey'] = $_StempSellKey;
							
							if(count($_AbaggageAllowance) > 0){
								$viaFlights[$segmentSubKey]['baggageAllowance'] = $_AbaggageAllowance;
							}
							
							$viaFlights[$segmentSubKey]['ssr']		= array();
							$viaFlights[$segmentSubKey]['seatMap']	= array();
							
							$_ScheckKey  = $viaFlights[$segmentSubKey]['departureStation'].$viaFlights[$segmentSubKey]['arrivalStation'];
							$_ScheckKey1 = $viaFlights[$segmentSubKey]['carrierCode'].$viaFlights[$segmentSubKey]['flightNumber'].$viaFlights[$segmentSubKey]['departureStation'].$viaFlights[$segmentSubKey]['arrivalStation'];
							
							if(isset($_AssrResponse['Baggage'][$_ScheckKey]) && !empty($_AssrResponse['Baggage'][$_ScheckKey])){
								$_BbagExist = true;
								$viaFlights[$segmentSubKey]['ssr']['baggage'] = $_AssrResponse['Baggage'][$_ScheckKey];
							}
							
							if(isset($_AssrResponse['Meals'][$_ScheckKey]) && !empty($_AssrResponse['Meals'][$_ScheckKey])){
								$_BmealExist = true;
								$viaFlights[$segmentSubKey]['ssr']['meals'] = $_AssrResponse['Meals'][$_ScheckKey];
							}
							
							if(isset($_AssrResponse['Seat'][$_ScheckKey1]) && !empty($_AssrResponse['Seat'][$_ScheckKey1])){
								$_BseatExist = true;
								$viaFlights[$segmentSubKey]['seatMap'] = $_AssrResponse['Seat'][$_ScheckKey1];
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
						
						$journeySellKey .= "@@@".$_SisLcc."@@@".$_SresultIndex."@@@".$_StraceId;
						
						$_ScheckKey  = $journeyDepartureStation.$journeyArrivalStation;
						
						if(!$_BbagExist && isset($_AssrResponse['Baggage'][$_ScheckKey]) && !empty($_AssrResponse['Baggage'][$_ScheckKey])){
							$viaFlights[0]['ssr']['baggage'] = $_AssrResponse['Baggage'][$_ScheckKey];
						}
						
						if(!$_BmealExist && isset($_AssrResponse['Meals'][$_ScheckKey]) && !empty($_AssrResponse['Meals'][$_ScheckKey])){
							$viaFlights[0]['ssr']['meals'] = $_AssrResponse['Meals'][$_ScheckKey];
						}
						else if(!$_BmealExist && isset($_AssrResponse['Meals']['ALLALL']) && !empty($_AssrResponse['Meals']['ALLALL'])){
							$viaFlights[0]['ssr']['meals'] = $_AssrResponse['Meals']['ALLALL'];
						}
						
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
					
					// Fare settings
					
					$_AfareInfo			= $val['Fare'];
					
					$_SapiCurrency 		= $_AfareInfo['Currency'];
					$_IapiExchangeRate 	= isset($GLOBALS['CONF']['exchangeRate'][$_SapiCurrency]) ? $GLOBALS['CONF']['exchangeRate'][$_SapiCurrency] : 1;
					
					$_SuserCurrency 	= $this->_Ainput['currencyCode'];
					$_IuserExchangeRate	= isset($GLOBALS['CONF']['exchangeRate'][$_SuserCurrency]) ? $GLOBALS['CONF']['exchangeRate'][$_SuserCurrency] : 1;
					
					$_SdefaultCurrency 	= $GLOBALS['CONF']['site']['defaultCurrency'];
					
					/*$_IapiExchangeRate = 1;
					$_IuserExchangeRate= 1;*/
					
					// Api fares
					
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
						$_IapiTotalFare			= $_AfareInfo['OfferedFare'];
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
					
					if(isset($GLOBALS['CONF']['userSettings']['markupDetails']) && count($GLOBALS['CONF']['userSettings']['markupDetails']) > 0){
				
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
							
							$_AdefaultMarkupData['userTotalMarkup']  = $_IuserTotalMarkup;
							$_AdefaultMarkupData['userperPaxMarkup'] = $_IuserperPaxMarkup;
							
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
						
						$_IdefaultOfferFare  	= $_IapiOfferFare / $_IapiExchangeRate;
						$_IdefaultOfferFare  	= Common::getRoundedFare($_IdefaultOfferFare,$_SdefaultCurrency);
						
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
						
						$_IuserOfferFare	= $_IdefaultOfferFare * $_IuserExchangeRate;
						$_IuserOfferFare	= Common::getRoundedFare($_IuserOfferFare,$_SuserCurrency);
						
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

						if(!isset($_AupdatePrice[$tripKey])){
							$_AupdatePrice[$tripKey] = array();
						}
						
						$tripVal['totalBaseFare']	= $_IuserBaseFare;
						$tripVal['totalTax']		= $_IuserTax;
						$tripVal['totalFees']		= $_IuserFee;
						$tripVal['totalFare']		= $_IuserTotalFare;
						$tripVal['paxFares']		= $_ApaxFares;
						$tripVal['taxBreakUP']		= $_AtaxBreakup;
						$tripVal['markupData']		= $_AdefaultMarkupData;
						$tripVal['defaultTotalFare']= $_IdefaultTotalFare;
						$tripVal['offeredFare']		= $_IuserOfferFare;
						$_AcommissionEarned = 0;									//Calculate TDS amount
						$_AcommissionEarned = $_IuserTotalFare-$_IuserOfferFare;
						if($_AcommissionEarned>1){
							$_Atds = 0;//Common::getRoundedFare($_IuserAdjTax,$_SuserCurrency);
							$_Atds = Common::getRoundedFare(($_AcommissionEarned*5)/100,$_SuserCurrency);
							
							$tripVal['tdsAdded'] 	= $_Atds;
							$tripVal['totalTax']	+=$_Atds;							// TDS added in Total tax
							$tripVal['totalFare']	+=$_Atds;							// TDS added in Total Fare
							$tripVal['offeredFare']	+=$_Atds;							// TDS added in Offered Fare
							
						}
						
						if($tripKey > 0){
								
							$tripVal['totalBaseFare']	= 0;
							$tripVal['totalTax']		= 0;
							$tripVal['totalFees']		= 0;
							$tripVal['totalFare']		= 0;
							$tripVal['defaultTotalFare']= 0;
							$tripVal['markupData']		= array();
							
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
						
						$_AupdatePrice[$tripKey][] = $tripVal;
					}
				}
			}
			else{
				$_Bstatus  = false;
				$_Smessage = 'Update price results not found';
			}
		}
		else{
			$_Bstatus = false;
			
			if(isset($_AupdatePriceResult['Response']['Error']['ErrorMessage']) && !empty($_AupdatePriceResult['Response']['Error']['ErrorMessage'])){
				$_Smessage = $_AupdatePriceResult['Response']['Error']['ErrorMessage'];
			}
			else{
				$_Smessage = 'No flights found';
			}
		}
		
		$_Areturn = array
					(
						'status' => $_Bstatus,
						'data'   => $_AupdatePrice,
						'msg'    => $_Smessage,
					);
					
		return $_Areturn;
	}
}
?>