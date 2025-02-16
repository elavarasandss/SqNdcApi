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
		
		$_AupdateSsrResult= $this->_executeService();
		
		$_AupdateSsrResult = $this->xmlstrToArray($_AupdateSsrResult);
		echo "<pre>";print_r($_AupdateSsrResult);exit;
		$_AupdateSsrResult = $_AupdateSsrResult['Body']['OfferPriceRS'];
		
		
		if(!empty($_AupdateSsrResult['Response']) || $_AupdateSsrResult['Response'] !=null){
			// echo "<pre>";print_r($_AupdateSsrResult);exit;
			$_BaggageAllowance = $_AupdateSsrResult['Response']['DataLists']['BaggageAllowanceList']['BaggageAllowance'];
			$_OriginDestList = $_AupdateSsrResult['Response']['DataLists']['OriginDestList']['OriginDest'];
			 $_Asectorcount=count($_OriginDestList);
			$_PaxJourneyList = $_AupdateSsrResult['Response']['DataLists']['PaxJourneyList']['PaxJourney'];
			$_PaxList = $_AupdateSsrResult['Response']['DataLists']['PaxList'];
			$_PaxSegmentList = $_AupdateSsrResult['Response']['DataLists']['PaxSegmentList']['PaxSegment'];
			$_PenaltyList = $_AupdateSsrResult['Response']['DataLists']['PenaltyList'];
			$_PriceClassList = $_AupdateSsrResult['Response']['DataLists']['PriceClassList']['PriceClass'];
			$_PricedOffer = $_AupdateSsrResult['Response']['PricedOffer']['Offer'];
			$_ShoppingResponseID = $_AupdateSsrResult['Response']['ShoppingResponse']['ShoppingResponseID'];
			$_BaggageAllowance_Keys = array_keys($_BaggageAllowance);
			if ($_BaggageAllowance_Keys !== range(0, count($_BaggageAllowance) - 1)) {
				
				$_BaggageAllowanceNew[]=$_BaggageAllowance;
			}else{
				$_BaggageAllowanceNew=$_BaggageAllowance;
			}
			// SERVICELIST WEBSERVICE
			// sleep(10);
			$this->_Ainput['action'] = "getServiceList";
			$_OgetserviceList	= controllerGet::getObject('getServiceList',$this);
			$_AServiceListResponce	= $_OgetserviceList->_dogetServiceList();
			// $_AserviceListResult= $this->getServiceListcallCurl();
			// echo "<pre>";print_r($_AServiceListResponce);exit;
			//   echo "hai". "<pre>";print_r($_AserviceListResult);exit;
			// $_AserviceListResult = Common::xmlstrToArray($_AServiceListResponce);
			// echo "<pre>";print_r($_AserviceListResult);exit;
			$_Aresult = $_AServiceListResponce['Body']['ServiceListRS'];
			
			$ServiceDefinitionList = $_Aresult['Response']['DataLists']['ServiceDefinitionList']['ServiceDefinition'];
			
			//echo "<pre>";print_r($ServiceDefinitionList);die;
			$ALaCarteOfferList = $_Aresult['Response']['ALaCarteOffer'];
			
			$ALaCarteOfferID = $ALaCarteOfferList['OfferID'];
			$ALaCarteOwnerCode = $ALaCarteOfferList['OwnerCode'];
			
			$_ASSRPriceArr = array();
			foreach($ALaCarteOfferList['ALaCarteOfferItem'] as $_ALaCarteKey => $_ALaCarteVal){
				
				$_AserviceID = $_ALaCarteVal['Service']['ServiceDefinitionRefID'];
				$_ASSRPriceArr[$_AserviceID] = $_ALaCarteVal;
			}
			// echo "<pre>";print_r($_ASSRPriceArr);exit;
			$_AmealDetails=array();
			$_AmealList =array();
			$_AseviceList =array();
			/* foreach ($ServiceDefinitionList as $key => $datavalue) {
				// if($datavalue['Description'][1]['DescText'] == 'MEAL'){
				//	$_AmealDetails[]=$datavalue;
				//}else{
				//	$_BaggageAllowanceNow[]=$datavalue;
				//} 
				
				//$_AmealDetails[]=$datavalue;
			}
			
			if(!empty($_AmealDetails)){
				$_FindUniquename = array_unique(array_column($_AmealDetails, 'ServiceCode'));
				$_AmealList = array_intersect_key($_AmealDetails, $_FindUniquename);
			}
			
		
			foreach ($_AmealList as  $mealvalue) {
				$_AseviceLisarrange[]=$mealvalue;
			}
			if(!empty($_AseviceLisarrange)){
				$_AseviceList = array_chunk($_AseviceLisarrange, 9);
				
				
				echo "<pre>";print_r($_AseviceList);die;
			} */
			/* $_AOriginDest_Keys = array_keys($_OriginDestList);
			if ($_AOriginDest_Keys !== range(0, count($_OriginDestList) - 1)) {
				
				$_OriginDest[]=$_OriginDestList;
			}else{
				$_OriginDest=$_OriginDestList;
			} */
			/* $_ApaxSegemtn_Keys = array_keys($_PaxSegmentList);
			if ($_ApaxSegemtn_Keys !== range(0, count($_PaxSegmentList) - 1)) {
				
				$_PaxSegment[]=$_PaxSegmentList;
			}else{
				$_PaxSegment=$_PaxSegmentList;
			}
			foreach ($_PaxSegment as $_Apaxkey => $_Apaxvalue) {
				$_Asector_info[$_Apaxkey]['Dep']['IATA_LocationCode']=$_Apaxvalue['Dep']['IATA_LocationCode'];
				$_Asector_info[$_Apaxkey]['Dep']['AircraftScheduledDateTime']=date("Y-m-d",strtotime($_Apaxvalue['Dep']['AircraftScheduledDateTime']));
				$_Asector_info[$_Apaxkey]['Arrival']['IATA_LocationCode']=$_Apaxvalue['Arrival']['IATA_LocationCode'];
				$_Asector_info[$_Apaxkey]['Arrival']['AircraftScheduledDateTime']=$_Apaxvalue['Arrival']['AircraftScheduledDateTime'];
				$_Asector_info[$_Apaxkey]['MarketingCarrierInfo']['CarrierName']=$_Apaxvalue['MarketingCarrierInfo']['CarrierName'];
				$_Asector_info[$_Apaxkey]['MarketingCarrierInfo']['MarketingCarrierFlightNumberText']=$_Apaxvalue['MarketingCarrierInfo']['MarketingCarrierFlightNumberText'];
			
			   } */
			 // echo "<pre>";print_r($_PaxSegment);
			// echo "<pre>";print_r($_OriginDestList);die;
			// file_put_contents('./_AseviceList'.date("j.n.Y").'.log', print_R($_AseviceList), FILE_APPEND);
			
			
			if(!isset($_OriginDestList[0])){
				
				$_OriginDestList = array($_OriginDestList);
			}
			foreach($_OriginDestList as $_Okey=>$_Ovalue){
				
				if(!isset($_PaxJourneyList[0])){
					$_PaxJourneyList = array($_PaxJourneyList);
				}
				
				if(!isset($_PaxJourneyList[$_Okey]['PaxSegmentRefID'][0])){
					$_PaxJourneyList[$_Okey]['PaxSegmentRefID'] = array($_PaxJourneyList[$_Okey]['PaxSegmentRefID']);
				}
				$_AonwardReturnSectors = $_PaxJourneyList[$_Okey]['PaxSegmentRefID'];     // array('SEG5','SEG6')
				
				//print_r($_AonwardReturnSectors);die;
				if(!is_array($_AonwardReturnSectors)){
					$_AonwardReturnSectors = array($_AonwardReturnSectors);
				}
				if(!isset($_AonwardReturnSectors[0])){
					$_AonwardReturnSectors = array($_AonwardReturnSectors);
				}
				/* if(is_array())
				if($_Okey==0){
					
					
				} */
				$_Data['flightDetails'][$_Okey]['journeySellKey']		= $_PricedOffer['OfferID'];
				$_Data['flightDetails'][$_Okey]['ShoppingResponseID']	= $_ShoppingResponseID;
				$_Data['flightDetails'][$_Okey]['OfferID']				= $_PricedOffer['OfferID'];
				$_Data['flightDetails'][$_Okey]['flightMergeKey']		= $_PricedOffer['OfferID'];
				$_Data['flightDetails'][$_Okey]['OfferItemID']		= $_PricedOffer['OfferItem']['OfferItemID'];;
					
				$_Data['flightDetails'][$_Okey]['departureStation'] 	= $_Ovalue['OriginCode'];
				$_Data['flightDetails'][$_Okey]['arrivalStation'] 		= $_Ovalue['DestCode'];
				$_Data['flightDetails'][$_Okey]['flighttype'] 			= $_Ovalue['PaxJourneyRefID'];
				$_Data['flightDetails'][$_Okey]['duration'] 			= $_PaxJourneyList[$_Okey]['Duration'];
				//$_Data['flightDetails'][$_Okey]['PaxSegmentRefID'] 		= $_PaxJourneyList[$_Okey]['PaxSegmentRefID'];
				$_Aviaid=0;
				$_AviaFlightsArr = array();
				if(!isset($_PaxSegmentList[0])){
					
					$_PaxSegmentList = array($_PaxSegmentList);
				}
				
				
				foreach ($_PaxSegmentList as $_Apaxkey => $_Apaxvalue) {
					
					$_ApaxsegmentID = $_Apaxvalue['PaxSegmentID'];
					//echo "<pre>";print_r($_AonwardReturnSectors);die;
					if(in_array($_ApaxsegmentID, $_AonwardReturnSectors)){
						
						
						$_Afarebasiscode = $_PricedOffer['OfferItem']['FareDetail']['FareComponent'][0]['FareBasis']['FareBasisCode']['Code'];
						$_AclassOfService = $_PricedOffer['OfferItem']['FareDetail']['FareComponent'][0]['FareBasis']['RBD'];
						
						
						//echo "<pre>";print_r($_PricedOffer['OfferItem']['FareDetail']['FareComponent']);die;
						$_AviaFlightsArr[$_Aviaid]['PaxSegmentID']		= $_Apaxvalue['PaxSegmentID'];
						$_AviaFlightsArr[$_Aviaid]['departureStation']	= $_Apaxvalue['Dep']['IATA_LocationCode'];
						$_AviaFlightsArr[$_Aviaid]['arrivalStation']	= $_Apaxvalue['Arrival']['IATA_LocationCode'];
						$_AviaFlightsArr[$_Aviaid]['std']				= $_Apaxvalue['Dep']['AircraftScheduledDateTime'];
						$_AviaFlightsArr[$_Aviaid]['sta']				= $_Apaxvalue['Arrival']['AircraftScheduledDateTime'];
						$_AviaFlightsArr[$_Aviaid]['carrierCode']		= $_PricedOffer['OwnerCode'];
						$_AviaFlightsArr[$_Aviaid]['flightNumber']		= $_Apaxvalue['MarketingCarrierInfo']['MarketingCarrierFlightNumberText'];
						$_AviaFlightsArr[$_Aviaid]['departureDate']		= $_Apaxvalue['Dep']['AircraftScheduledDateTime'];
						$_AviaFlightsArr[$_Aviaid]['departureTime']		= $_Apaxvalue['Dep']['AircraftScheduledDateTime'];
						$_AviaFlightsArr[$_Aviaid]['arrivalDate']		= $_Apaxvalue['Arrival']['AircraftScheduledDateTime'];
						$_AviaFlightsArr[$_Aviaid]['arrivalTime']		= $_Apaxvalue['Arrival']['AircraftScheduledDateTime'];
						$_AviaFlightsArr[$_Aviaid]['duration']			= $_Apaxvalue['Duration'];
						$_AviaFlightsArr[$_Aviaid]['leg']				= $_Apaxvalue['DatedOperatingLeg']['DatedOperatingLegID'];
						$_AviaFlightsArr[$_Aviaid]['AircraftType']		= $_Apaxvalue['DatedOperatingLeg']['IATA_AircraftType']['IATA_AircraftTypeCode'];
						$_AviaFlightsArr[$_Aviaid]['classOfService']	= $_PriceClassList['Name'];
						$_AviaFlightsArr[$_Aviaid]['fareBasisCode']		= $_Afarebasiscode;
						$_AviaFlightsArr[$_Aviaid]['classOfService']	= $_AclassOfService;
						$_AviaFlightsArr[$_Aviaid]['ruleNumber']		= '';
						$_AviaFlightsArr[$_Aviaid]['fareSellKey']		= '';
						$_AviaFlightsArr[$_Aviaid]['classType']			= $_PriceClassList['Name'];
						
						$_AssrdetailsArr = array();
						
						
						//echo "<pre>";print_r($ServiceDefinitionList);die;
						//foreach ($_AseviceList as $_Skey => $_Svalue) {
							
							foreach ($ServiceDefinitionList as $_SVkey => $_Svalue) {
							
							//echo "-->".$_Svalue['ServiceDefinitionID'];
							if(isset($_ASSRPriceArr[$_Svalue['ServiceDefinitionID']])){
										
										$_ApriceArr = $_ASSRPriceArr[$_Svalue['ServiceDefinitionID']];
								//compare service list segemnt like SEG2 = Already available segment like SEG2
								
								
								/* if(!is_array($_ApriceArr['Eligibility']['FlightAssociations']['PaxSegmentRefID'])){
									$_ApriceArr['Eligibility']['FlightAssociations']['PaxSegmentRefID'] = array($_ApriceArr['Eligibility']['FlightAssociations']['PaxSegmentRefID']);
									
								}
								if(in_array($_Apaxvalue['PaxSegmentID'], $_ApriceArr['Eligibility']['FlightAssociations']['PaxSegmentRefID']))
								{ */
									
									
								if($_ApriceArr['Eligibility']['FlightAssociations']['PaxSegmentRefID']==$_Apaxvalue['PaxSegmentID'])
								{
									
									if($_Svalue['Description'][1]['DescText'] == 'MEAL'){
										$_Assrtype = 'meals';
									}else{
										$_Assrtype = 'baggage';
									}
									
									$_AssrAmount = '';
									
									//echo "<pre>";print_r($_ApriceArr['UnitPrice']['TotalAmount']);die;
									
										
										if(isset($_ApriceArr['UnitPrice']['TotalAmount'])){
											
											if(isset($_ApriceArr['UnitPrice']['TotalAmount']['content'])){
												$_AssrAmount = $_ApriceArr['UnitPrice']['TotalAmount']['content'];
												
											}
											
										}
										$_AssrdetailsArr[$_Assrtype][$_SVkey]['OfferItemID']	= $_ApriceArr['OfferItemID'];
										$_AssrdetailsArr[$_Assrtype][$_SVkey]['PaxRefID']		= $_ApriceArr['Eligibility']['PaxRefID'];
										$_AssrdetailsArr[$_Assrtype][$_SVkey]['PaxSegmentRefID']=$_ApriceArr['Eligibility']['FlightAssociations']['PaxSegmentRefID'];
										$_AssrdetailsArr[$_Assrtype][$_SVkey]['amount']			=$_AssrAmount;
										
										
									
										$_AssrdetailsArr[$_Assrtype][$_SVkey]['codeType']		= $_Svalue['ServiceDefinitionID'];
										$_AssrdetailsArr[$_Assrtype][$_SVkey]['serviceId']		= $_Svalue['ServiceCode'];
										$_AssrdetailsArr[$_Assrtype][$_SVkey]['categoryId']		= $_Svalue['ServiceCode'];
										$_AssrdetailsArr[$_Assrtype][$_SVkey]['description']	= $_Svalue['Name'];
										$_AssrdetailsArr[$_Assrtype][$_SVkey]['code']			= $_Svalue['ServiceCode'];
										$_AssrdetailsArr[$_Assrtype][$_SVkey]['lfid']			= $_Svalue['ServiceCode'];
										
										$_AssrdetailsArr[$_Assrtype][$_SVkey]['departureDate']=0;
										$_AssrdetailsArr[$_Assrtype][$_SVkey]['std']=0;
										$_AssrdetailsArr[$_Assrtype][$_SVkey]['fareKey']		= $_Svalue['ServiceCode'];
										$_AssrdetailsArr[$_Assrtype][$_SVkey]['fareCodeType']	= $_Svalue['ServiceCode'];
										
										//echo "<pre>";print_r($_AssrdetailsArr);//die;
									}
									
								}
							
							}
						//}
						//echo "<pre>";print_r($_AssrdetailsArr);//die;
						$_AviaFlightsArr[$_Aviaid]['ssrDetails'] = $_AssrdetailsArr;
						
						
			
						$_Aviaid++;
					}
					
					
					
					$_Data['flightDetails'][$_Okey]['viaFlights'] = $_AviaFlightsArr;
					
					$_AtotalFare 	= $_PricedOffer['OfferItem']['Price']['TotalAmount']['content'];
					$_AbaseFare 	= $_PricedOffer['OfferItem']['Price']['BaseAmount']['content'];
					$_AtaxFare 		= $_PricedOffer['OfferItem']['Price']['TaxSummary']['TotalTaxAmount']['content'];
					
					
					$_Data['flightDetails'][$_Okey]['totalBaseFare'] 	= $_AbaseFare ;
					$_Data['flightDetails'][$_Okey]['totalTax'] 		= $_AtaxFare;
					$_Data['flightDetails'][$_Okey]['totalFare'] 		= $_AtotalFare;
					$_Data['flightDetails'][$_Okey]['totalFees'] 		= 0;
					
	
					$_Data['flightDetails'][$_Okey]['OfferItem'] = $_PricedOffer['OfferItem'];
					
					//Hide By Ela Start
					/* $_Data['flightDetails'][$_Okey]['viaFlights']['departureStation']	= $_Ovalue['Dep']['IATA_LocationCode'];
					$_Data['flightDetails'][$_Okey]['viaFlights']['arrivalStation']		= $_Ovalue['Arrival']['IATA_LocationCode'];
					$_Data['flightDetails'][$_Okey]['viaFlights']['std']				= $_Ovalue['Dep']['AircraftScheduledDateTime'];
					$_Data['flightDetails'][$_Okey]['viaFlights']['sta']				= $_Ovalue['Arrival']['AircraftScheduledDateTime'];
					$_Data['flightDetails'][$_Okey]['viaFlights']['carrierCode']		= $_PricedOffer['OwnerCode'];
					$_Data['flightDetails'][$_Okey]['viaFlights']['flightNumber']		= $_Ovalue['MarketingCarrierInfo']['MarketingCarrierFlightNumberText'];
					$_Data['flightDetails'][$_Okey]['viaFlights']['departureDate']		= $_Ovalue['Dep']['AircraftScheduledDateTime'];
					$_Data['flightDetails'][$_Okey]['viaFlights']['departureTime']		= $_Ovalue['Dep']['AircraftScheduledDateTime'];
					$_Data['flightDetails'][$_Okey]['viaFlights']['arrivalDate']		= $_Ovalue['Arrival']['AircraftScheduledDateTime'];
					$_Data['flightDetails'][$_Okey]['viaFlights']['arrivalTime']		= $_Ovalue['Arrival']['AircraftScheduledDateTime'];
					$_Data['flightDetails'][$_Okey]['viaFlights']['classOfService']		= $_PriceClassList['Name'];
					$_Data['flightDetails'][$_Okey]['viaFlights']['fareBasisCode']		= '';
					$_Data['flightDetails'][$_Okey]['viaFlights']['ruleNumber']			= '';
					$_Data['flightDetails'][$_Okey]['viaFlights']['fareSellKey']		= '';
					$_Data['flightDetails'][$_Okey]['viaFlights']['classType']			= $_PriceClassList['Name']; */
					//Hide By Ela end
					
					
					/* foreach ($_BaggageAllowanceNow as $_Bkey => $_Baggagevalue) {
						$_Data['flightDetails'][$_Okey]['viaFlights']['ssrDetails']['baggage'][$_Bkey]['codeType']=$_Baggagevalue[$_Bkey]['ServiceCode'];
						$_Data['flightDetails'][$_Okey]['viaFlights']['ssrDetails']['baggage'][$_Bkey]['serviceId']=$_Baggagevalue[$_Bkey]['ServiceDefinitionID'];
						$_Data['flightDetails'][$_Okey]['viaFlights']['ssrDetails']['baggage'][$_Bkey]['categoryId']=0;
						$_Data['flightDetails'][$_Okey]['viaFlights']['ssrDetails']['baggage'][$_Bkey]['description']=$_Baggagevalue[$_Bkey]['Name'];
						$_Data['flightDetails'][$_Okey]['viaFlights']['ssrDetails']['baggage'][$_Bkey]['code']=0;
						$_Data['flightDetails'][$_Okey]['viaFlights']['ssrDetails']['baggage'][$_Bkey]['lfid']=0;
						$_Data['flightDetails'][$_Okey]['viaFlights']['ssrDetails']['baggage'][$_Bkey]['amount']=0;
						$_Data['flightDetails'][$_Okey]['viaFlights']['ssrDetails']['baggage'][$_Bkey]['departureDate']=0;
						$_Data['flightDetails'][$_Okey]['viaFlights']['ssrDetails']['baggage'][$_Bkey]['std']=0;
						$_Data['flightDetails'][$_Okey]['viaFlights']['ssrDetails']['baggage'][$_Bkey]['fareKey']=0;
						$_Data['flightDetails'][$_Okey]['viaFlights']['ssrDetails']['baggage'][$_Bkey]['fareCodeType']=0;
					} */
					
					
					//echo "<pre>";print_r($_AseviceList);die;
					
				}
				
			}
			
			$_Areturn = array
					(
						'status' => $_Bstatus,
						'data'   => $_Data,
						'msg'    => "FareQuotes Data",
					);
					
		return $_Areturn;
			echo "<pre>";print_r($_Data);exit;
		}
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
					
					$_SGSTAllowed 		= ($val['GSTAllowed'] == true) ? "Y" : "N";
					$_SGSTMandatory 	= ($val['IsGSTMandatory'] == true) ? "Y" : "N";
					
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
						$_AflightDetails['GSTAllowed'] 		= $_SGSTAllowed;
						$_AflightDetails['GSTMandatory'] 	= $_SGSTMandatory;
						
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
					
					/* $_IapiBaseFare 			= $_AfareInfo['BaseFare'];
					$_IapiTax				= $_AfareInfo['Tax'];
					$_IapiTotalFare			= $_AfareInfo['PublishedFare']; */
					
					$_AapiCommissionAmt = 0;
					
					$_AapiCommissionAmt = $_AfareInfo['PublishedFare']-$_AfareInfo['OfferedFare'];
					
					if($_AapiCommissionAmt>0){
						$_AapiCommissionAmt = (($_AapiCommissionAmt*30)/100);
					}
					
					if($this->_Oconf['userSettings']['userName'] == "NUGDSIN" || $this->_Oconf['userSettings']['userName'] == "SelectMyFlight" || $this->_Oconf['userSettings']['userName'] == "FLYONSKY" || $this->_Oconf['userSettings']['userName'] == "GBAB2C"){
					
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
					
					$_IdefaultCommissionFare		= $_AapiCommissionAmt / $_IapiExchangeRate;
					$_IdefaultCommissionFare		= Common::getRoundedFare($_IdefaultCommissionFare,$_SdefaultCurrency);
					
					
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
						//$tripVal['defaultTotalFare']= $_IdefaultTotalFare;
						$tripVal['defaultTotalFare']= $_IdefaultTotalFare+$_IdefaultCommissionFare;
						//Ela 
						$_AexofferFare='';
						if($_IuserTotalFare<$_IuserOfferFare){
							
							$_AexofferFare  = $_IuserOfferFare;
						}
						else{
							$_AexofferFare  = $_IuserTotalFare;
						}
						$tripVal['expublishedFare']		= $_AexofferFare;
						$_AcommissionEarned = 0;		//Calculate TDS amount
						
						if($this->_Oconf['userSettings']['userName'] == "NUGDSIN" || $this->_Oconf['userSettings']['userName'] == "SelectMyFlight" || $this->_Oconf['userSettings']['userName'] == "FLYONSKY" || $this->_Oconf['userSettings']['userName'] == "GBAB2C"){ 
							$_AcommissionEarned = $_IuserOfferFare-$_IuserTotalFare;
						}else{
							$_AcommissionEarned = $_IuserTotalFare-$_IuserOfferFare;
						}
						
						if($_AcommissionEarned>1){
							//$_Atds = 0;//Common::getRoundedFare($_IuserAdjTax,$_SuserCurrency);
							$_Atds = Common::getRoundedFare(($_AcommissionEarned*30)/100,$_SuserCurrency);
							
							$tripVal['tdsAdded'] 	= $_Atds;
							//$tripVal['totalTax']	+=$_Atds;							// TDS added in Total tax
							$tripVal['totalFare']	+=$_Atds;							// TDS added in Total Fare
							//$tripVal['expublishedFare']	+=$_Atds;						// TDS added in Total Fare
							
							
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
	
	function fun1(){
		
		$_Axml =		'{"Response":{"Error":{"ErrorCode":0,"ErrorMessage":""},"IsPriceChanged":false,"ResponseStatus":1,"Results":{"ResultIndex":"OB1","Source":4,"IsLCC":false,"IsRefundable":false,"GSTAllowed":true,"IsGSTMandatory":false,"IsHoldAllowed":true,"AirlineRemark":"this is 9w.","Fare":{"Currency":"INR","BaseFare":225,"Tax":1009,"TaxBreakup":[{"key":"K3","value":0},{"key":"YQTax","value":0},{"key":"YR","value":0},{"key":"PSF","value":0},{"key":"UDF","value":0},{"key":"INTax","value":0},{"key":"TransactionFee","value":0},{"key":"OtherTaxes","value":0}],"YQTax":600,"AdditionalTxnFeeOfrd":0,"AdditionalTxnFeePub":0,"PGCharge":0,"OtherCharges":132.16,"ChargeBU":[{"key":"TBOMARKUP","value":0},{"key":"CONVENIENCECHARGE","value":0},{"key":"OTHERCHARGE","value":132.16}],"Discount":0,"PublishedFare":1366.16,"CommissionEarned":1.91,"PLBEarned":0.00,"IncentiveEarned":0.00,"OfferedFare":1364.25,"TdsOnCommission":0.57,"TdsOnPLB":0.00,"TdsOnIncentive":0.00,"ServiceFee":0,"TotalBaggageCharges":0,"TotalMealCharges":0,"TotalSeatCharges":0,"TotalSpecialServiceCharges":0},"FareBreakdown":[{"Currency":"INR","PassengerType":1,"PassengerCount":1,"BaseFare":225,"Tax":1009,"YQTax":600,"AdditionalTxnFeeOfrd":0,"AdditionalTxnFeePub":0,"PGCharge":0}],"Segments":[[{"Baggage":"1 PC(s)","CabinBaggage":null,"TripIndicator":1,"SegmentIndicator":1,"Airline":{"AirlineCode":"9W","AirlineName":"Jet Airways","FlightNumber":"780","FareClass":"W","OperatingCarrier":"9W"},"Origin":{"Airport":{"AirportCode":"MAA","AirportName":"Chennai","Terminal":"1","CityCode":"MAA","CityName":"Chennai","CountryCode":"IN","CountryName":"India"},"DepTime":"2019-03-14T06:50:00"},"Destination":{"Airport":{"AirportCode":"BLR","AirportName":"Hindustan","Terminal":"","CityCode":"BLR","CityName":"Bangalore","CountryCode":"IN","CountryName":"India"},"ArrTime":"2019-03-14T08:00:00"},"AccumulatedDuration":70,"Duration":70,"GroundTime":0,"Mile":0,"StopOver":false,"StopPoint":"","StopPointArrivalTime":"2019-03-14T08:00:00","StopPointDepartureTime":"2019-03-14T06:50:00","Craft":"AT7","Remark":null,"IsETicketEligible":true,"FlightStatus":"Confirmed","Status":""}]],"LastTicketDate":"12FEB19","TicketAdvisory":"TICKETS ARE NON-REFUNDABLE \u000aLAST TKT DTE 12FEB19  - SEE ADV PURCHASE \u000a","FareRules":[{"Origin":"MAA","Destination":"BLR","Airline":"9W","FareBasisCode":"W2IPO","FareRuleDetail":null,"FareRestriction":null}],"AirlineCode":"9W","ValidatingAirline":"9W"},"TraceId":"1feff92a-368b-40fc-acf1-5dde2bc6071b"}}';
		
		return $_Axml;
	}

	public function xmlstrToArray($xmlstr)
	{
		$doc = new DOMDocument();
		$doc->loadXML($xmlstr);
		return $this->domNodeToArray($doc->documentElement);
	}
	
	public function domNodeToArray($node) 
	{
		$output = array();
		switch ($node->nodeType) {
			case XML_CDATA_SECTION_NODE:
			case XML_TEXT_NODE:
				$output = trim($node->textContent);
				break;
			case XML_ELEMENT_NODE:
				for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) { 
					$child = $node->childNodes->item($i);
					$v = $this->domNodeToArray($child);
					if(isset($child->tagName)) {
						$t = $child->tagName;
						$t1 = explode(":",$t);
						if(isset($t1[1])){
							$t = $t1[1];
						}
						if(!isset($output[$t])) {
							$output[$t] = array();
						}
						$output[$t][] = $v;
					}
					elseif($v) {
						$output = (string) $v;
					}
				}
				if($node->attributes->length && !is_array($output)) {
                    $output = array('content'=>$output);
                }
				if(is_array($output)) {
					if($node->attributes->length) {
						$a = array();
						foreach($node->attributes as $attrName => $attrNode) {
							$a[$attrName] = (string) $attrNode->value;
						}
						$output['attributes'] = $a;
					}
					foreach ($output as $t => $v) {
						if(is_array($v) && count($v)==1 && $t!='attributes') {
							$output[$t] = $v[0];
						}
					}
				}
			break;
		}
		return $output;
	}
}
?>