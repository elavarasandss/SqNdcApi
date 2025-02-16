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
		
		//$this->baggageAirlineArr = array("9W","AI","WY","UL");
		$this->baggageAirlineArr = array("9W","AI","UL","EK","TG","MH","PG","MI","SQ","TK","WY","EY");
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
		
		// $_AflightResult		= $this->fun1(); 
		
    // echo "<pre>";print_r($_AflightResult);die;
		$_AlowFareSearchResp = Common::xmlstrToArray($_AflightResult);
		$_AflightResult = $_AlowFareSearchResp['Body']['AirShoppingRS'];
		
    // echo "<pre>";print_r($_AlowFareSearchResp);die;
		
		
		
		if(isset($this->_Ainput['printApiResponse']) && $this->_Ainput['printApiResponse'] == "Y"){
			echo "<pre>";print_r($_AflightResult);exit;
		}
			if(isset($_AflightResult['Response']['Error']['ErrorMessage']) && !empty($_AflightResult['Response']['Error']['ErrorMessage'])){
				
				$_Smessage = $_AflightResult['Response']['Error']['ErrorMessage'];
				
				if($_Smessage=='InValid Session' || $_Smessage=='Invalid Token'){
					
					//if($this->_Ssignature=='b013b24f-dcf8-4ec0-a822-fac788753f9e')
					{
						
						/* $this->_killSession();
						
						$_Ologon		 	= controllerGet::getObject('logon',$this);				
						$_AlogonResponse 	= $_Ologon->_doLogon();
				
						if(isset($_AlogonResponse['status']) && $_AlogonResponse['status'] && isset($_AlogonResponse['data']) && !empty($_AlogonResponse['data'])){
													
							$this->_Ssignature	= $_AlogonResponse['data'];
							$this->_setData();
							$_AflightResult		= $this->_executeService();
						} */
						
						
					}
					
				}
			}
		
		$_AflightSchedules  = array();
		
		if(isset($_AflightResult['Response']) && $_AflightResult['Response'] && count($_AflightResult['Response']) > 0){
			
			$_AflightSeats 			 = $_AflightResult['Response']['AirShoppingProcessing']['MarketingMessages']['MarketMessage']['Associations']['OfferAssociations']['Flight'];
			$_AbaggageAllowanceList	 = $_AflightResult['Response']['DataLists']['BaggageAllowanceList']['BaggageAllowance'];
			$_AoriginDestList	 	 = $_AflightResult['Response']['DataLists']['OriginDestList']['OriginDest'];
			$_ApaxJourneyList	 	 = $_AflightResult['Response']['DataLists']['PaxJourneyList']['PaxJourney'];
			$_ApaxList	 	 		 = $_AflightResult['Response']['DataLists']['PaxList']['Pax'];
			$_ApaxSegmentList	 	 = $_AflightResult['Response']['DataLists']['PaxSegmentList']['PaxSegment'];
			$_ApenaltyList	 		 = $_AflightResult['Response']['DataLists']['PenaltyList']['Penalty'];
			$_ApriceClassList	 	 = $_AflightResult['Response']['DataLists']['PriceClassList']['PriceClass'];
			$_AoffersGroup	 	 	 = $_AflightResult['Response']['OffersGroup']['CarrierOffers']['Offer'];
			$_AShoppingResponse	 	 = $_AflightResult['Response']['ShoppingResponse'];
			
			
			// echo "<pre>";
			
			//print_r($_AbaggageAllowanceList);die;
			
			$_AflightResults = $_AoffersGroup;
			
			$_AreturnArrDatas=array();
		
			foreach($_AflightResults as $_Akey => $_Aval){
				
				$_Abaggageunit 	= '';
				$_Abaggagevalue = '';
				$_Abaggagetype = '';
				if($_Aval['BaggageAllowance']['BaggageAllowanceRefID']){
					
					$_AbagRefid =  $_Aval['BaggageAllowance']['BaggageAllowanceRefID'];
					
					foreach($_AbaggageAllowanceList as  $_AbagKey => $_AbagVal){
						
						if($_AbagVal['BaggageAllowanceID'] == $_AbagRefid){
							
							$_Abaggageunit 	= $_AbagVal['WeightAllowance']['MaximumWeightMeasure']['attributes']['UnitCode'];
							$_Abaggagevalue = $_AbagVal['WeightAllowance']['MaximumWeightMeasure']['content'];
							$_Abaggagetype 	= $_AbagVal['TypeCode'];
													
							
							
						}
					}
				}
				
				if($_Aval['JourneyOverview']){
					
					
					if(!isset($_Aval['OfferItem']['FareDetail'])){
														
						$_Aval['OfferItem']['FareDetail'] = array($_Aval['OfferItem']['FareDetail']);
					}
					
					foreach($_Aval['JourneyOverview']['JourneyPriceClass'] as $_ApriceClassKey => $_ApriceClassValue){				//Onward //Return  
						
						$_AflightJourneyId = $_ApriceClassValue['PaxJourneyRefID'];
						
						$_AselectJourneyid = '';
						
						foreach($_AoriginDestList as $_AoriginDestKey => $_AoriginDestVal){						
						
						//This contain, Onward and return sectors
							
							if(in_array($_AflightJourneyId, $_AoriginDestVal['PaxJourneyRefID'])){
								

								//This key indicate 0-Onward 1-Return
								
								$_AselectJourneyid = $_AoriginDestKey;										
								
								$_Aorigin 		= $_AoriginDestVal['OriginCode'];
								$_Adestination 	= $_AoriginDestVal['DestCode'];
								
								$_Aresponse['departureStation'] 	= $_Aorigin;
								$_Aresponse['arrivalStation'] 		= $_Adestination;
								$_Aresponse['flighttype'] 			= $_AflightJourneyId;
								
								foreach($_ApaxJourneyList as $_ApaxjourneyKey => $_ApaxjourneyVal ){
									
									if($_ApaxjourneyVal['PaxJourneyID']==$_AflightJourneyId){
										
										if(!is_array($_ApaxjourneyVal['PaxSegmentRefID'])){
											
											//echo "array";
											$_ApaxjourneyVal['PaxSegmentRefID']= array($_ApaxjourneyVal['PaxSegmentRefID']);
										}
										
										//print_r($_ApaxjourneyVal['PaxSegmentRefID']);die;
										$segmentArr = $_ApaxjourneyVal['PaxSegmentRefID'];
										$_Aresponse['viaFlights']=array();
										foreach($segmentArr as $_AsegKey => $_AsegVal){
											
											$_Asegmentid =  $_AsegVal;
											
											foreach($_ApaxSegmentList as $_AsementListKey => $_AsementListVal){
												
												if($_AsementListVal['PaxSegmentID']==$_Asegmentid)
												{
													
													$_Aviaflights = array();
													
													$departureStation 	= $_AsementListVal['Dep']['IATA_LocationCode'];
													$arrivalStation 	= $_AsementListVal['Arrival']['IATA_LocationCode'];
													
													$std 				= $_AsementListVal['Dep']['AircraftScheduledDateTime'];
													$sta 				= $_AsementListVal['Arrival']['AircraftScheduledDateTime'];
													
													$departureArr = explode("T",$std);
													$departureDate = $departureArr[0];
													$departureTime = $departureArr[1];
																						
													$arrivalArr = explode("T",$sta);
													$arrivalDate = $arrivalArr[0];
													$arrivalTime = $arrivalArr[1];
													
																									
													$carrierCode 		= $_AsementListVal['MarketingCarrierInfo']['CarrierDesigCode'];
													$flightNumber 		= $_AsementListVal['MarketingCarrierInfo']['MarketingCarrierFlightNumberText'];
													
													
													
													$departureTerminal='';
													if(!empty($_AsementListVal['Dep']['TerminalName'])){
														$departureTerminal 	= $_AsementListVal['Dep']['TerminalName'];
													}
													$arrivalTerminal='';
													if(!empty($_AsementListVal['Arrival']['TerminalName'])){
														$arrivalTerminal 	= $_AsementListVal['Arrival']['TerminalName'];
													}
													
													//$arrivalStation 	= $_AsementListVal['Arrival']['TerminalName'];
													if($_AsegKey==0){						//First Leg

														$_Aresponse['std'] 					= $std;
														$_Aresponse['departureDate'] 		= $departureDate;
														$_Aresponse['departureTime'] 		= $departureTime;
														$_Aresponse['departureTerminal'] 	= $departureTerminal;
													}	

													if(count($segmentArr)-1 == $_AsegKey){
														
														$_Aresponse['sta'] 				= $sta;
														$_Aresponse['arrivalDate'] 		= $arrivalDate;
														$_Aresponse['arrivalTime'] 		= $arrivalTime;
														$_Aresponse['arrivalTerminal'] 	= $arrivalTerminal;
													}		
													$_Aviaflights['departureStation'] 	= $departureStation;
													$_Aviaflights['arrivalStation'] 	= $arrivalStation;
													$_Aviaflights['std'] 				= $std;
													$_Aviaflights['sta'] 				= $sta;
													$_Aviaflights['state'] 				= 'State';
													$_Aviaflights['carrierCode'] 		= $carrierCode;
													$_Aviaflights['flightNumber'] 		= $flightNumber;
													
													$_Aviaflights['departureDate'] 		= $departureDate;
													$_Aviaflights['departureTime'] 		= $departureTime;
													$_Aviaflights['arrivalDate'] 		= $arrivalDate;
													$_Aviaflights['arrivalTime'] 		= $arrivalTime;
													$_Aviaflights['departureTerminal'] 	= $departureTerminal;
													$_Aviaflights['arrivalTerminal'] 	= $arrivalTerminal;
													
													$_Aviaflights['baggageAllowance']['Unit'] 			= $_Abaggageunit;
													$_Aviaflights['baggageAllowance']['Value'] 			= $_Abaggagevalue;
													$_Aviaflights['baggageAllowance']['AllowanceType'] 	= $_Abaggagetype;
													
													 
													
													
													foreach($_Aval['OfferItem']['FareDetail'][0]['FareComponent'] as $_AfareComKey =>  $_AfareComVal){
														
														if($_AfareComVal['SegmentRefs']==$_Asegmentid){
															
															$_Aviaflights['fareSequence'] 		= $_AfareComVal['FareBasis']['FareBasisCode']['Code'];
															$_Aviaflights['fareBasisCode'] 		= $_AfareComVal['FareBasis']['FareBasisCode']['Code'];
															$_Aviaflights['classOfService'] 	= $_AfareComVal['FareBasis']['RBD'];
															$_Aviaflights['fareClassOfService'] = $_AfareComVal['FareBasis']['RBD'];
															
															$_ApriceClassRef = $_AfareComVal['PriceClassRef'];
															
															foreach($_ApriceClassList as $_ApriceClassKey => $_ApriceClassVal){
																
																//print_r($_ApriceClassVal);die;
																
																if($_ApriceClassVal['PriceClassID']==$_ApriceClassRef){
																	
																	
																	if($_AfareComVal['FareBasis']['CabinType']['CabinTypeName']=='ECO'){
																
																		$_Aviaflights['CabinType'] 			= 'Economy';
																		$_Aviaflights['classInfoName'] 		= $_ApriceClassVal['Name'];
																		$_Aviaflights['productClass'] 		= $_ApriceClassVal['Name'];
																		$_Aviaflights['classDescription'] 	= $_ApriceClassVal['Desc'];
																	}
																	else if($_AfareComVal['FareBasis']['CabinType']['CabinTypeName']=='BUS'){
																		
																		$_Aviaflights['CabinType'] = 'BUSINESS';
																	}
																	else if($_AfareComVal['FareBasis']['CabinType']['CabinTypeName']=='ECO'){
																		
																		$_Aviaflights['CabinType'] = 'Economy';
																	}
																}
															}
															
															
															
															
														}
														
													}
													
													
													
													
													/* $_Aviaflights['capacity'] 		= 0
													$_Aviaflights['sold'] 				= 0
													$_Aviaflights['segmentSellKey'] 	= 506530
													
													$_Aviaflights['classType'] 			= Economy
													$_Aviaflights['ruleTariff'] 		= 1
													$_Aviaflights['ruleNumber'] 		= 1
													$_Aviaflights['fareSellKey'] 		= 1
													
													$_Aviaflights['classInfoName'] 		= Express VALUE (FBA 20kg)
													$_Aviaflights['productClass'] 		= Express VALUE (FBA 20kg)
													$_Aviaflights['fareApplicationType']= 184
													$_Aviaflights['seatsAvbl'] 			= 158
													$_Aviaflights['fareStatus'] 		= FareStatus
													$_Aviaflights['fareComRefKey'] 		= NTAw
													$_Aviaflights['status'] 			= Status
													$_Aviaflights['baggageAllowance'] 	= Array
														(
															$_Aviaflights['Unit] => Kilograms
															$_Aviaflights['Value] => 20
														) */
													
													$_Aresponse['viaFlights'][] = $_Aviaflights;
													//$_Aviaflights=array();
												}
												
											}
											
											
										}
										
										
										
									}
									
									
									
								}
								
								
								//This fare details getting price every adult/child/infant/
								
								$_ApaxfaresNew=array();
								$_AtaxBreakUP=array();
								foreach($_Aval['OfferItem']['FareDetail'] as $_AfareDetailKey => $_AfareDetailValue)
								{
									
									$_ApaxkeyArr = explode(" ",$_AfareDetailValue['PassengerRefs']);
									
									$_Apaxkey = $_ApaxkeyArr[0];
									
									foreach($_ApaxList as $_ApaxkeyNew => $_ApaxvalueNew){
										if($_ApaxvalueNew['PaxID']==$_Apaxkey){
											
											$_AoriginalPaxType = $_ApaxvalueNew['PTC'];
										}
										
									}
									
									
									$_Apaxfares = array();
									
									$_Apaxfares['paxType'] 		= $_AoriginalPaxType;
									$_Apaxfares['baseFare'] 	= $_AfareDetailValue['Price']['BaseAmount'];
									$_Apaxfares['fees'] 		= 0;
									$_Apaxfares['tax'] 			= $_AfareDetailValue['Price']['Taxes']['Total'];
									$_Apaxfares['totalFare'] 	= $_AfareDetailValue['Price']['TotalAmount']['DetailCurrencyPrice']['Total'];
									
									
									$_ApaxfaresNew[$_AfareDetailKey] = $_Apaxfares;
									
									$_AtaxcodeArr = array();
									
									$_AtaxcodeArr['taxCode'] 		= 'Tax';
									$_AtaxcodeArr['amount'] 		= $_Apaxfares['tax'];
									$_AtaxcodeArr['taxDescription'] = 'Tax';
									
									$_AtaxBreakUP[$_AoriginalPaxType][] = $_AtaxcodeArr;
									
								}
								$_Aresponse['paxfares'] 	= $_ApaxfaresNew;
								$_Aresponse['taxBreakUP'] 	= $_AtaxBreakUP;
								
								
								//$_Aresponse['paxType'] 		= $_Aval['OfferItem']['Service']['PaxRefID'];
								$_Aresponse['totalBaseFare'] 	= $_Aval['OfferItem']['Price']['BaseAmount'];
								$_Aresponse['totalFees'] 		= 0;
								$_Aresponse['totalTax'] 		= $_Aval['OfferItem']['Price']['TaxSummary']['TotalTaxAmount'];
								$_Aresponse['totalFare'] 		= $_Aval['OfferItem']['Price']['TotalAmount'];
								
								
								$_Aresponse['OfferID'] 			= $_Aval['OfferID'];
								$_Aresponse['flightMergeKey'] 	= $_Aval['OfferID'];				
								$_AreturnArrDatas[$_AselectJourneyid][]=$_Aresponse;
								
							}
							
						}
						
						
					}
					
					
					
					
				}
				
				
					
				
				
				
				
				
			}
			print_r($_AreturnArrDatas);die;
			
			
			
			$_StraceId			= $_AflightResult['Response']['TraceId'];
			$_AflightResults	= $_AflightResult['Response']['Results'];
			
			#echo "<pre>";print_r($_AflightResult);exit;
			
			/*if(isset($_AflightResults[0]) && !empty($_AflightResults[0])){
				$_AflightResults = $_AflightResults[0];
			}*/
			
			
			
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
						$_AairlineRemarks 		= ($val['AirlineRemark'] != '') ? $val['AirlineRemark'] : "";
						$_SresultIndex 		= $val['ResultIndex'];
						$_SflightMergeKey	= $_SresultIndex;
						
						$_SGSTAllowed 		= ($val['GSTAllowed'] == true) ? "Y" : "N";
						$_SGSTMandatory 	= ($val['IsGSTMandatory'] == true) ? "Y" : "N";
						
						
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
								
								$_Acabinbaggage = array();
								
								if(isset($segmentSubVal['CabinBaggage']) && !empty($segmentSubVal['CabinBaggage'])){
									$segmentSubVal['CabinBaggage'] = strtoupper($segmentSubVal['CabinBaggage']);
									$segmentSubVal['CabinBaggage'] = str_replace("KG","",$segmentSubVal['CabinBaggage']);
									$segmentSubVal['CabinBaggage'] = str_replace(" ","",$segmentSubVal['CabinBaggage']);
									
									if($segmentSubVal['CabinBaggage'] > 0){
										$_Acabinbaggage['Value'] = $segmentSubVal['CabinBaggage'];
										$_Acabinbaggage['Unit']  = 'Kilograms';
									}

								}
								
								if(isset($segmentSubVal['Baggage']) && !empty($segmentSubVal['Baggage'])){
									$segmentSubVal['Baggage'] = strtoupper($segmentSubVal['Baggage']);
									$segmentSubVal['Baggage'] = str_replace("KG","",$segmentSubVal['Baggage']);
									$segmentSubVal['Baggage'] = str_replace(" ","",$segmentSubVal['Baggage']);
									
									if($segmentSubVal['Baggage'] > 0){
										$_AbaggageAllowance['Value'] = $segmentSubVal['Baggage'];
										$_AbaggageAllowance['Unit']  = 'Kilograms';
									}
								}else{
									$_AoriginAirport 		= $segmentVal[0]['Origin']['Airport']['AirportCode'];
									$_AdestinationAirport 	= $segmentVal[count($segmentVal)-1]['Destination']['Airport']['AirportCode'];
									
									$_AoriginCountry 		= $segmentVal[0]['Origin']['Airport']['CountryCode'];
									$_AdestinationCountry 	= $segmentVal[count($segmentVal)-1]['Destination']['Airport']['CountryCode'];
									$_AairlineCode 			= $segmentSubVal['Airline']['AirlineCode'];
									
									
									if(in_array($_AairlineCode,$this->baggageAirlineArr)){
									//if($_AairlineCode=='9W' || $_AairlineCode=='AI' || $_AairlineCode=='WY' || $_AairlineCode=='UL'){
										
										$_Abaggage = $this->_getBaggageInformation($_AairlineCode,$_AoriginAirport,$_AdestinationAirport,$_AoriginCountry,$_AdestinationCountry);
									
										if(!empty($_Abaggage)){
											
											if($_Abaggage[0]['unit']=='KG'){
												if($this->_Ainput['classType']=='Business'){
													$_AbaggageAllowance['Value'] = $_Abaggage[0]['business_baggage'];
												}else{
													$_AbaggageAllowance['Value'] = $_Abaggage[0]['economy_baggage'];
												}
												$_AbaggageAllowance['Unit']  = $_Abaggage[0]['unit'].'- ';//'Kilograms';//$_Abaggage[0]['unit'];
											}
											else{
												
												if($_AbaggageAllowance['Value']!=0){
													if($this->_Ainput['classType']=='Business'){
														$_AbaggageAllowance['Value'] = $_Abaggage[0]['business_baggage'];
													}else{
														$_AbaggageAllowance['Value'] = $_Abaggage[0]['economy_baggage'];
													}
													
												}else{
													$_AbaggageAllowance['Value'] = '2';
												}
												
												$_AbaggageAllowance['Unit']  = $_Abaggage[0]['unit'].'- ';//'Kilograms';//$_Abaggage[0]['unit'];
											}
										
										}
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
								
								if($this->_Ainput['classType']=='Business'){
									$viaFlights[$segmentSubKey]['classType'] 			= 'Business';
								}else{
									$viaFlights[$segmentSubKey]['classType'] 			= 'Economy';
								}
								//$viaFlights[$segmentSubKey]['classType'] 			= 'Economy';
								
								
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
								
								if(count($_Acabinbaggage) > 0){
									$viaFlights[$segmentSubKey]['cabinbaggage'] = $_Acabinbaggage; 
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
							$_AflightDetails['airlineRemarks'] 	= $_AairlineRemarks;
							$_AflightDetails['GSTAllowed'] 		= $_SGSTAllowed;
							$_AflightDetails['GSTMandatory'] 	= $_SGSTMandatory;
							
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
						
						if($_SuserCurrency=='INR'){
							
							$_IuserExchangeRate=1;
						}
						$_SdefaultCurrency 	= $GLOBALS['CONF']['site']['defaultCurrency'];
						
						
						// Api fares
						
						if($this->_Oconf['userSettings']['userName'] == "NUGDSIN" || $this->_Oconf['userSettings']['userName'] == "FLYONSKY" || $this->_Oconf['userSettings']['userName'] == "GBAB2C" ){
							//||  $this->_Oconf['userSettings']['userName'] == "SelectMyFlight"
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
						
						/* $_IapiBaseFare 			= $_AfareInfo['BaseFare'];
						$_IapiTax				= $_AfareInfo['Tax'];
						$_IapiTotalFare			= $_AfareInfo['PublishedFare'];
						$_IapiOfferFare			= $_AfareInfo['OfferedFare']; */
						
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
								$_IuserOfferFare	= $_IuserOfferFare + $_IuserTotalMarkup;
								
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
							$_AcommissionEarned = 0;
							//|| $this->_Oconf['userSettings']['userName'] == "SelectMyFlight"
							if($this->_Oconf['userSettings']['userName'] == "NUGDSIN"  || $this->_Oconf['userSettings']['userName'] == "FLYONSKY" || $this->_Oconf['userSettings']['userName'] == "GBAB2C"){
							
								$_AcommissionEarned = $_IuserOfferFare-$_IuserTotalFare;
							}else{
								$_AcommissionEarned = $_IuserTotalFare-$_IuserOfferFare;
							}
							
															//Calculate TDS amount
							//$_AcommissionEarned = $_IuserTotalFare-$_IuserOfferFare;
							if($_AcommissionEarned>1){
								//$_Atds = 0;
								$_Atds = Common::getRoundedFare(($_AcommissionEarned*30)/100,$_SuserCurrency); // hidden tds flow
								
								$tripVal['tdsAdded'] 	= $_Atds;
								$tripVal['totalTax']	+=$_Atds;							// TDS added in Total tax
								$tripVal['totalFare']	+=$_Atds;							// TDS added in Total Fare
								$tripVal['offeredFare']	+=$_Atds;							// TDS added in Offered Fare
								
							}
							
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
		
		if($this->_Ainput['tboFSC']==1){ 
			
			$_AairlineArr = array();
			
			$_ACountryData = $this->_getCountryData($this->_Ainput['sectorInfo'][0]['origin']);
			$_AairlineType = $this->_getAirlineTypeAll();
			
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
		else{
			//This is old code
			/*getting from xml to restric data*/
		
			$_AairlinrData  = $this->_getairlineData();

			//logWrite("\n LowfareSearch \n ----------- \n".print_r($_AairlinrData,true)."\n","_Aairl",'N','a+');
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
				logWrite($datas,'_AairlinrData_');
		return $datas ; 


	}
	
	public function _getCountryData($_Origin){
		
		 $_SQL = 'SELECT distinct countryCode FROM `uapi_citycode` where citycode = "'.$_Origin.'"';
		$data = $this->_Odb->getAll($_SQL);
		return $data[0]['countryCode'];
		
	}
	
	public function _getAirlineType(){
		 $_SQL = 'SELECT airline_code FROM airlines_list where airline_type like "%LCC%" and active_status ="Y"';
		$data = $this->_Odb->getAll($_SQL);
		return $data;
	}
	public function _getAirlineTypeAll(){
		 $_SQL = 'SELECT airline_code FROM airlines_list where active_status ="Y"';
		$data = $this->_Odb->getAll($_SQL);
		return $data;
	}
	public function _getBaggageInformation($_AairlineCode,$_AoriginAirport,$_AdestinationAirport,$_AoriginCountry,$_AdestinationCountry){
		
		if(in_array($_AairlineCode,$this->baggageAirlineArr)){
			
		//if($_AairlineCode=='9W' || $_AairlineCode=='AI' || $_AairlineCode=='WY' || $_AairlineCode=='UL'){
			
			$_SQL = 'SELECT * FROM baggage_information where airline_code ="'.$_AairlineCode.'" and origin_country ="'.$_AoriginCountry.'" and destination_country ="'.$_AdestinationCountry.'" and status="Y" ';
		}
		else{
			
			$_SQL = 'SELECT * FROM baggage_information where airline_code ="'.$_AairlineCode.'" and origin ="'.$_AoriginAirport.'" and destination ="'.$_AoriginAirport.'" and status="Y" ';
		}	

		$data = $this->_Odb->getAll($_SQL);
		return $data;
	}
	
	function fun1()
	{
		
		$xml = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:awsse="http://xml.amadeus.com/2010/06/Session_v3" xmlns:wsa="http://www.w3.org/2005/08/addressing">
  <soap:Header>
    <wsa:To>http://www.w3.org/2005/08/addressing/anonymous</wsa:To>
    <wsa:From>
      <wsa:Address>https://nodea1.test.webservices.amadeus.com/1ASIWNDCSQ</wsa:Address>
    </wsa:From>
    <wsa:Action>http://webservices.amadeus.com/NDC_AirShopping_18.1</wsa:Action>
    <wsa:MessageID>urn:uuid:6dbbb912-0ea3-c614-7193-6d900548ac43</wsa:MessageID>
    <wsa:RelatesTo RelationshipType="http://www.w3.org/2005/08/addressing/reply">7caa06db-5813-48f6-bb65-46b83ad70ae0</wsa:RelatesTo>
    <awsse:Session TransactionStatusCode="End">
      <awsse:SessionId>00GDCHL6SS</awsse:SessionId>
      <awsse:SequenceNumber>1</awsse:SequenceNumber>
      <awsse:SecurityToken>11KW7HGRKP8AM2X03LOZF4XB5E</awsse:SecurityToken>
    </awsse:Session>
  </soap:Header>
  <soap:Body>
    <AirShoppingRS xmlns="http://www.iata.org/IATA/2015/00/2018.1/AirShoppingRS" xmlns:ns2="http://www.iata.org/IATA/2015/00/2018.1/AirShoppingRQ">
      <PayloadAttributes>
        <Version>18.1</Version>
        <CorrelationID>172KYUA58S4#0VGM#TQSLYG#91</CorrelationID>
      </PayloadAttributes>
      <Response>
        <AirShoppingProcessing>
          <MarketingMessages>
            <MarketMessage>
              <Associations>
                <OfferAssociations>
                  <Flight>
                    <FlightSegmentReference ref="SEG3">
                      <ClassOfService>
                        <Code SeatsLeft="9">E</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG3">
                      <ClassOfService>
                        <Code SeatsLeft="9">W</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG3">
                      <ClassOfService>
                        <Code SeatsLeft="9">Y</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG3">
                      <ClassOfService>
                        <Code SeatsLeft="9">K</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG3">
                      <ClassOfService>
                        <Code SeatsLeft="9">N</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG11">
                      <ClassOfService>
                        <Code SeatsLeft="9">Y</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG23">
                      <ClassOfService>
                        <Code SeatsLeft="9">N</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG14">
                      <ClassOfService>
                        <Code SeatsLeft="9">T</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG4">
                      <ClassOfService>
                        <Code SeatsLeft="9">E</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG4">
                      <ClassOfService>
                        <Code SeatsLeft="9">W</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG4">
                      <ClassOfService>
                        <Code SeatsLeft="9">Y</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG4">
                      <ClassOfService>
                        <Code SeatsLeft="9">K</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG4">
                      <ClassOfService>
                        <Code SeatsLeft="9">N</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG10">
                      <ClassOfService>
                        <Code SeatsLeft="9">N</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG5">
                      <ClassOfService>
                        <Code SeatsLeft="9">E</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG5">
                      <ClassOfService>
                        <Code SeatsLeft="9">W</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG5">
                      <ClassOfService>
                        <Code SeatsLeft="9">Y</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG5">
                      <ClassOfService>
                        <Code SeatsLeft="9">K</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG5">
                      <ClassOfService>
                        <Code SeatsLeft="9">N</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG26">
                      <ClassOfService>
                        <Code SeatsLeft="9">Y</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG15">
                      <ClassOfService>
                        <Code SeatsLeft="9">Y</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG19">
                      <ClassOfService>
                        <Code SeatsLeft="9">E</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG19">
                      <ClassOfService>
                        <Code SeatsLeft="9">W</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG19">
                      <ClassOfService>
                        <Code SeatsLeft="9">Y</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG19">
                      <ClassOfService>
                        <Code SeatsLeft="9">K</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG19">
                      <ClassOfService>
                        <Code SeatsLeft="9">N</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG27">
                      <ClassOfService>
                        <Code SeatsLeft="9">U</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG22">
                      <ClassOfService>
                        <Code SeatsLeft="9">Y</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG18">
                      <ClassOfService>
                        <Code SeatsLeft="9">E</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG18">
                      <ClassOfService>
                        <Code SeatsLeft="9">W</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG18">
                      <ClassOfService>
                        <Code SeatsLeft="9">Y</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG18">
                      <ClassOfService>
                        <Code SeatsLeft="9">K</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                    <FlightSegmentReference ref="SEG18">
                      <ClassOfService>
                        <Code SeatsLeft="9">N</Code>
                      </ClassOfService>
                    </FlightSegmentReference>
                  </Flight>
                </OfferAssociations>
              </Associations>
            </MarketMessage>
          </MarketingMessages>
        </AirShoppingProcessing>
        <DataLists>
          <BaggageAllowanceList>
            <BaggageAllowance>
              <BaggageAllowanceID>FBA1</BaggageAllowanceID>
              <TypeCode>CheckedBag</TypeCode>
              <WeightAllowance>
                <MaximumWeightMeasure UnitCode="KILOGRAM">25</MaximumWeightMeasure>
              </WeightAllowance>
            </BaggageAllowance>
            <BaggageAllowance>
              <BaggageAllowanceID>FBA2</BaggageAllowanceID>
              <TypeCode>CheckedBag</TypeCode>
              <WeightAllowance>
                <MaximumWeightMeasure UnitCode="KILOGRAM">30</MaximumWeightMeasure>
              </WeightAllowance>
            </BaggageAllowance>
          </BaggageAllowanceList>
          <OriginDestList>
            <OriginDest>
              <OriginDestID>MAASIN</OriginDestID>
              <OriginCode>MAA</OriginCode>
              <DestCode>SIN</DestCode>
              <PaxJourneyRefID>FLT8</PaxJourneyRefID>
              <PaxJourneyRefID>FLT1</PaxJourneyRefID>
              <PaxJourneyRefID>FLT9</PaxJourneyRefID>
              <PaxJourneyRefID>FLT5</PaxJourneyRefID>
              <PaxJourneyRefID>FLT3</PaxJourneyRefID>
            </OriginDest>
            <OriginDest>
              <OriginDestID>SINMAA</OriginDestID>
              <OriginCode>SIN</OriginCode>
              <DestCode>MAA</DestCode>
              <PaxJourneyRefID>FLT7</PaxJourneyRefID>
              <PaxJourneyRefID>FLT6</PaxJourneyRefID>
              <PaxJourneyRefID>FLT4</PaxJourneyRefID>
              <PaxJourneyRefID>FLT2</PaxJourneyRefID>
            </OriginDest>
          </OriginDestList>
          <PaxJourneyList>
            <PaxJourney>
              <PaxJourneyID>FLT7</PaxJourneyID>
              <Duration>PT6H20M</Duration>
              <PaxSegmentRefID>SEG22</PaxSegmentRefID>
              <PaxSegmentRefID>SEG23</PaxSegmentRefID>
            </PaxJourney>
            <PaxJourney>
              <PaxJourneyID>FLT8</PaxJourneyID>
              <Duration>PT7H50M</Duration>
              <PaxSegmentRefID>SEG10</PaxSegmentRefID>
              <PaxSegmentRefID>SEG11</PaxSegmentRefID>
            </PaxJourney>
            <PaxJourney>
              <PaxJourneyID>FLT6</PaxJourneyID>
              <Duration>PT7H45M</Duration>
              <PaxSegmentRefID>SEG26</PaxSegmentRefID>
              <PaxSegmentRefID>SEG27</PaxSegmentRefID>
            </PaxJourney>
            <PaxJourney>
              <PaxJourneyID>FLT1</PaxJourneyID>
              <Duration>PT4H10M</Duration>
              <PaxSegmentRefID>SEG5</PaxSegmentRefID>
            </PaxJourney>
            <PaxJourney>
              <PaxJourneyID>FLT9</PaxJourneyID>
              <Duration>PT9H5M</Duration>
              <PaxSegmentRefID>SEG14</PaxSegmentRefID>
              <PaxSegmentRefID>SEG15</PaxSegmentRefID>
            </PaxJourney>
            <PaxJourney>
              <PaxJourneyID>FLT5</PaxJourneyID>
              <Duration>PT5H0M</Duration>
              <PaxSegmentRefID>SEG3</PaxSegmentRefID>
            </PaxJourney>
            <PaxJourney>
              <PaxJourneyID>FLT4</PaxJourneyID>
              <Duration>PT4H10M</Duration>
              <PaxSegmentRefID>SEG18</PaxSegmentRefID>
            </PaxJourney>
            <PaxJourney>
              <PaxJourneyID>FLT2</PaxJourneyID>
              <Duration>PT4H0M</Duration>
              <PaxSegmentRefID>SEG19</PaxSegmentRefID>
            </PaxJourney>
            <PaxJourney>
              <PaxJourneyID>FLT3</PaxJourneyID>
              <Duration>PT4H15M</Duration>
              <PaxSegmentRefID>SEG4</PaxSegmentRefID>
            </PaxJourney>
          </PaxJourneyList>
          <PaxList>
            <Pax>
              <PaxID>PAX1</PaxID>
              <PTC>ADT</PTC>
            </Pax>
            <Pax>
              <PaxID>PAX3</PaxID>
              <PTC>CHD</PTC>
            </Pax>
            <Pax>
              <PaxID>PAX2</PaxID>
              <PTC>ADT</PTC>
            </Pax>
          </PaxList>
          <PaxSegmentList>
            <PaxSegment>
              <PaxSegmentID>SEG3</PaxSegmentID>
              <Dep>
                <IATA_LocationCode>MAA</IATA_LocationCode>
                <TerminalName>4</TerminalName>
                <AircraftScheduledDateTime>2024-01-25T01:10:00</AircraftScheduledDateTime>
              </Dep>
              <Arrival>
                <IATA_LocationCode>SIN</IATA_LocationCode>
                <TerminalName>0</TerminalName>
                <AircraftScheduledDateTime>2024-01-25T08:40:00</AircraftScheduledDateTime>
              </Arrival>
              <MarketingCarrierInfo>
                <CarrierDesigCode>SQ</CarrierDesigCode>
                <MarketingCarrierFlightNumberText>527</MarketingCarrierFlightNumberText>
              </MarketingCarrierInfo>
              <OperatingCarrierInfo>
                <CarrierDesigCode>SQ</CarrierDesigCode>
                <CarrierName>SINGAPORE AIRLINES</CarrierName>
              </OperatingCarrierInfo>
              <DatedOperatingLeg>
                <DatedOperatingLegID>LEG1</DatedOperatingLegID>
                <Dep>
                  <IATA_LocationCode>MAA</IATA_LocationCode>
                  <TerminalName>4</TerminalName>
                  <AircraftScheduledDateTime>2024-01-25T01:10:00</AircraftScheduledDateTime>
                </Dep>
                <Arrival>
                  <IATA_LocationCode>SIN</IATA_LocationCode>
                  <TerminalName>0</TerminalName>
                  <AircraftScheduledDateTime>2024-01-25T08:40:00</AircraftScheduledDateTime>
                </Arrival>
                <IATA_AircraftType>
                  <IATA_AircraftTypeCode>787</IATA_AircraftTypeCode>
                </IATA_AircraftType>
              </DatedOperatingLeg>
            </PaxSegment>
            <PaxSegment>
              <PaxSegmentID>SEG11</PaxSegmentID>
              <Dep>
                <IATA_LocationCode>CMB</IATA_LocationCode>
                <AircraftScheduledDateTime>2024-01-26T00:45:00</AircraftScheduledDateTime>
              </Dep>
              <Arrival>
                <IATA_LocationCode>SIN</IATA_LocationCode>
                <TerminalName>0</TerminalName>
                <AircraftScheduledDateTime>2024-01-26T07:15:00</AircraftScheduledDateTime>
              </Arrival>
              <MarketingCarrierInfo>
                <CarrierDesigCode>SQ</CarrierDesigCode>
                <MarketingCarrierFlightNumberText>469</MarketingCarrierFlightNumberText>
              </MarketingCarrierInfo>
              <OperatingCarrierInfo>
                <CarrierDesigCode>SQ</CarrierDesigCode>
                <CarrierName>SINGAPORE AIRLINES</CarrierName>
              </OperatingCarrierInfo>
              <DatedOperatingLeg>
                <DatedOperatingLegID>LEG2</DatedOperatingLegID>
                <Dep>
                  <IATA_LocationCode>CMB</IATA_LocationCode>
                  <AircraftScheduledDateTime>2024-01-26T00:45:00</AircraftScheduledDateTime>
                </Dep>
                <Arrival>
                  <IATA_LocationCode>SIN</IATA_LocationCode>
                  <TerminalName>0</TerminalName>
                  <AircraftScheduledDateTime>2024-01-26T07:15:00</AircraftScheduledDateTime>
                </Arrival>
                <IATA_AircraftType>
                  <IATA_AircraftTypeCode>787</IATA_AircraftTypeCode>
                </IATA_AircraftType>
              </DatedOperatingLeg>
            </PaxSegment>
            <PaxSegment>
              <PaxSegmentID>SEG23</PaxSegmentID>
              <Dep>
                <IATA_LocationCode>CMB</IATA_LocationCode>
                <AircraftScheduledDateTime>2024-02-01T00:40:00</AircraftScheduledDateTime>
              </Dep>
              <Arrival>
                <IATA_LocationCode>MAA</IATA_LocationCode>
                <TerminalName>3</TerminalName>
                <AircraftScheduledDateTime>2024-02-01T02:00:00</AircraftScheduledDateTime>
              </Arrival>
              <MarketingCarrierInfo>
                <CarrierDesigCode>UL</CarrierDesigCode>
                <MarketingCarrierFlightNumberText>125</MarketingCarrierFlightNumberText>
              </MarketingCarrierInfo>
              <OperatingCarrierInfo>
                <CarrierDesigCode>UL</CarrierDesigCode>
                <CarrierName>SRILANKAN AIRLINES</CarrierName>
              </OperatingCarrierInfo>
              <DatedOperatingLeg>
                <DatedOperatingLegID>LEG3</DatedOperatingLegID>
                <Dep>
                  <IATA_LocationCode>CMB</IATA_LocationCode>
                  <AircraftScheduledDateTime>2024-02-01T00:40:00</AircraftScheduledDateTime>
                </Dep>
                <Arrival>
                  <IATA_LocationCode>MAA</IATA_LocationCode>
                  <TerminalName>3</TerminalName>
                  <AircraftScheduledDateTime>2024-02-01T02:00:00</AircraftScheduledDateTime>
                </Arrival>
                <IATA_AircraftType>
                  <IATA_AircraftTypeCode>320</IATA_AircraftTypeCode>
                </IATA_AircraftType>
              </DatedOperatingLeg>
            </PaxSegment>
            <PaxSegment>
              <PaxSegmentID>SEG14</PaxSegmentID>
              <Dep>
                <IATA_LocationCode>MAA</IATA_LocationCode>
                <TerminalName>1</TerminalName>
                <AircraftScheduledDateTime>2024-01-25T18:30:00</AircraftScheduledDateTime>
              </Dep>
              <Arrival>
                <IATA_LocationCode>HYD</IATA_LocationCode>
                <AircraftScheduledDateTime>2024-01-25T19:40:00</AircraftScheduledDateTime>
              </Arrival>
              <MarketingCarrierInfo>
                <CarrierDesigCode>AI</CarrierDesigCode>
                <MarketingCarrierFlightNumberText>587</MarketingCarrierFlightNumberText>
              </MarketingCarrierInfo>
              <OperatingCarrierInfo>
                <CarrierDesigCode>AI</CarrierDesigCode>
                <CarrierName>AIR INDIA</CarrierName>
              </OperatingCarrierInfo>
              <DatedOperatingLeg>
                <DatedOperatingLegID>LEG4</DatedOperatingLegID>
                <Dep>
                  <IATA_LocationCode>MAA</IATA_LocationCode>
                  <TerminalName>1</TerminalName>
                  <AircraftScheduledDateTime>2024-01-25T18:30:00</AircraftScheduledDateTime>
                </Dep>
                <Arrival>
                  <IATA_LocationCode>HYD</IATA_LocationCode>
                  <AircraftScheduledDateTime>2024-01-25T19:40:00</AircraftScheduledDateTime>
                </Arrival>
                <IATA_AircraftType>
                  <IATA_AircraftTypeCode>359</IATA_AircraftTypeCode>
                </IATA_AircraftType>
              </DatedOperatingLeg>
            </PaxSegment>
            <PaxSegment>
              <PaxSegmentID>SEG4</PaxSegmentID>
              <Dep>
                <IATA_LocationCode>MAA</IATA_LocationCode>
                <TerminalName>4</TerminalName>
                <AircraftScheduledDateTime>2024-01-25T10:00:00</AircraftScheduledDateTime>
              </Dep>
              <Arrival>
                <IATA_LocationCode>SIN</IATA_LocationCode>
                <TerminalName>0</TerminalName>
                <AircraftScheduledDateTime>2024-01-25T16:45:00</AircraftScheduledDateTime>
              </Arrival>
              <MarketingCarrierInfo>
                <CarrierDesigCode>SQ</CarrierDesigCode>
                <MarketingCarrierFlightNumberText>525</MarketingCarrierFlightNumberText>
              </MarketingCarrierInfo>
              <OperatingCarrierInfo>
                <CarrierDesigCode>SQ</CarrierDesigCode>
                <CarrierName>SINGAPORE AIRLINES</CarrierName>
              </OperatingCarrierInfo>
              <DatedOperatingLeg>
                <DatedOperatingLegID>LEG5</DatedOperatingLegID>
                <Dep>
                  <IATA_LocationCode>MAA</IATA_LocationCode>
                  <TerminalName>4</TerminalName>
                  <AircraftScheduledDateTime>2024-01-25T10:00:00</AircraftScheduledDateTime>
                </Dep>
                <Arrival>
                  <IATA_LocationCode>SIN</IATA_LocationCode>
                  <TerminalName>0</TerminalName>
                  <AircraftScheduledDateTime>2024-01-25T16:45:00</AircraftScheduledDateTime>
                </Arrival>
                <IATA_AircraftType>
                  <IATA_AircraftTypeCode>7M8</IATA_AircraftTypeCode>
                </IATA_AircraftType>
              </DatedOperatingLeg>
            </PaxSegment>
            <PaxSegment>
              <PaxSegmentID>SEG10</PaxSegmentID>
              <Dep>
                <IATA_LocationCode>MAA</IATA_LocationCode>
                <TerminalName>4</TerminalName>
                <AircraftScheduledDateTime>2024-01-25T20:55:00</AircraftScheduledDateTime>
              </Dep>
              <Arrival>
                <IATA_LocationCode>CMB</IATA_LocationCode>
                <AircraftScheduledDateTime>2024-01-25T22:15:00</AircraftScheduledDateTime>
              </Arrival>
              <MarketingCarrierInfo>
                <CarrierDesigCode>UL</CarrierDesigCode>
                <MarketingCarrierFlightNumberText>124</MarketingCarrierFlightNumberText>
              </MarketingCarrierInfo>
              <OperatingCarrierInfo>
                <CarrierDesigCode>UL</CarrierDesigCode>
                <CarrierName>SRILANKAN AIRLINES</CarrierName>
              </OperatingCarrierInfo>
              <DatedOperatingLeg>
                <DatedOperatingLegID>LEG6</DatedOperatingLegID>
                <Dep>
                  <IATA_LocationCode>MAA</IATA_LocationCode>
                  <TerminalName>4</TerminalName>
                  <AircraftScheduledDateTime>2024-01-25T20:55:00</AircraftScheduledDateTime>
                </Dep>
                <Arrival>
                  <IATA_LocationCode>CMB</IATA_LocationCode>
                  <AircraftScheduledDateTime>2024-01-25T22:15:00</AircraftScheduledDateTime>
                </Arrival>
                <IATA_AircraftType>
                  <IATA_AircraftTypeCode>333</IATA_AircraftTypeCode>
                </IATA_AircraftType>
              </DatedOperatingLeg>
            </PaxSegment>
            <PaxSegment>
              <PaxSegmentID>SEG5</PaxSegmentID>
              <Dep>
                <IATA_LocationCode>MAA</IATA_LocationCode>
                <TerminalName>4</TerminalName>
                <AircraftScheduledDateTime>2024-01-25T23:15:00</AircraftScheduledDateTime>
              </Dep>
              <Arrival>
                <IATA_LocationCode>SIN</IATA_LocationCode>
                <TerminalName>0</TerminalName>
                <AircraftScheduledDateTime>2024-01-26T05:55:00</AircraftScheduledDateTime>
              </Arrival>
              <MarketingCarrierInfo>
                <CarrierDesigCode>SQ</CarrierDesigCode>
                <MarketingCarrierFlightNumberText>529</MarketingCarrierFlightNumberText>
              </MarketingCarrierInfo>
              <OperatingCarrierInfo>
                <CarrierDesigCode>SQ</CarrierDesigCode>
                <CarrierName>SINGAPORE AIRLINES</CarrierName>
              </OperatingCarrierInfo>
              <DatedOperatingLeg>
                <DatedOperatingLegID>LEG7</DatedOperatingLegID>
                <Dep>
                  <IATA_LocationCode>MAA</IATA_LocationCode>
                  <TerminalName>4</TerminalName>
                  <AircraftScheduledDateTime>2024-01-25T23:15:00</AircraftScheduledDateTime>
                </Dep>
                <Arrival>
                  <IATA_LocationCode>SIN</IATA_LocationCode>
                  <TerminalName>0</TerminalName>
                  <AircraftScheduledDateTime>2024-01-26T05:55:00</AircraftScheduledDateTime>
                </Arrival>
                <IATA_AircraftType>
                  <IATA_AircraftTypeCode>787</IATA_AircraftTypeCode>
                </IATA_AircraftType>
              </DatedOperatingLeg>
            </PaxSegment>
            <PaxSegment>
              <PaxSegmentID>SEG26</PaxSegmentID>
              <Dep>
                <IATA_LocationCode>SIN</IATA_LocationCode>
                <TerminalName>3</TerminalName>
                <AircraftScheduledDateTime>2024-01-31T09:05:00</AircraftScheduledDateTime>
              </Dep>
              <Arrival>
                <IATA_LocationCode>BLR</IATA_LocationCode>
                <TerminalName>1</TerminalName>
                <AircraftScheduledDateTime>2024-01-31T11:05:00</AircraftScheduledDateTime>
              </Arrival>
              <MarketingCarrierInfo>
                <CarrierDesigCode>SQ</CarrierDesigCode>
                <MarketingCarrierFlightNumberText>508</MarketingCarrierFlightNumberText>
              </MarketingCarrierInfo>
              <OperatingCarrierInfo>
                <CarrierDesigCode>SQ</CarrierDesigCode>
                <CarrierName>SINGAPORE AIRLINES</CarrierName>
              </OperatingCarrierInfo>
              <DatedOperatingLeg>
                <DatedOperatingLegID>LEG8</DatedOperatingLegID>
                <Dep>
                  <IATA_LocationCode>SIN</IATA_LocationCode>
                  <TerminalName>3</TerminalName>
                  <AircraftScheduledDateTime>2024-01-31T09:05:00</AircraftScheduledDateTime>
                </Dep>
                <Arrival>
                  <IATA_LocationCode>BLR</IATA_LocationCode>
                  <TerminalName>1</TerminalName>
                  <AircraftScheduledDateTime>2024-01-31T11:05:00</AircraftScheduledDateTime>
                </Arrival>
                <IATA_AircraftType>
                  <IATA_AircraftTypeCode>7M8</IATA_AircraftTypeCode>
                </IATA_AircraftType>
              </DatedOperatingLeg>
            </PaxSegment>
            <PaxSegment>
              <PaxSegmentID>SEG15</PaxSegmentID>
              <Dep>
                <IATA_LocationCode>HYD</IATA_LocationCode>
                <AircraftScheduledDateTime>2024-01-25T23:10:00</AircraftScheduledDateTime>
              </Dep>
              <Arrival>
                <IATA_LocationCode>SIN</IATA_LocationCode>
                <TerminalName>0</TerminalName>
                <AircraftScheduledDateTime>2024-01-26T06:05:00</AircraftScheduledDateTime>
              </Arrival>
              <MarketingCarrierInfo>
                <CarrierDesigCode>SQ</CarrierDesigCode>
                <MarketingCarrierFlightNumberText>523</MarketingCarrierFlightNumberText>
              </MarketingCarrierInfo>
              <OperatingCarrierInfo>
                <CarrierDesigCode>SQ</CarrierDesigCode>
                <CarrierName>SINGAPORE AIRLINES</CarrierName>
              </OperatingCarrierInfo>
              <DatedOperatingLeg>
                <DatedOperatingLegID>LEG9</DatedOperatingLegID>
                <Dep>
                  <IATA_LocationCode>HYD</IATA_LocationCode>
                  <AircraftScheduledDateTime>2024-01-25T23:10:00</AircraftScheduledDateTime>
                </Dep>
                <Arrival>
                  <IATA_LocationCode>SIN</IATA_LocationCode>
                  <TerminalName>0</TerminalName>
                  <AircraftScheduledDateTime>2024-01-26T06:05:00</AircraftScheduledDateTime>
                </Arrival>
                <IATA_AircraftType>
                  <IATA_AircraftTypeCode>359</IATA_AircraftTypeCode>
                </IATA_AircraftType>
              </DatedOperatingLeg>
            </PaxSegment>
            <PaxSegment>
              <PaxSegmentID>SEG19</PaxSegmentID>
              <Dep>
                <IATA_LocationCode>SIN</IATA_LocationCode>
                <TerminalName>3</TerminalName>
                <AircraftScheduledDateTime>2024-01-31T22:20:00</AircraftScheduledDateTime>
              </Dep>
              <Arrival>
                <IATA_LocationCode>MAA</IATA_LocationCode>
                <TerminalName>3</TerminalName>
                <AircraftScheduledDateTime>2024-01-31T23:50:00</AircraftScheduledDateTime>
              </Arrival>
              <MarketingCarrierInfo>
                <CarrierDesigCode>SQ</CarrierDesigCode>
                <MarketingCarrierFlightNumberText>526</MarketingCarrierFlightNumberText>
              </MarketingCarrierInfo>
              <OperatingCarrierInfo>
                <CarrierDesigCode>SQ</CarrierDesigCode>
                <CarrierName>SINGAPORE AIRLINES</CarrierName>
              </OperatingCarrierInfo>
              <DatedOperatingLeg>
                <DatedOperatingLegID>LEG10</DatedOperatingLegID>
                <Dep>
                  <IATA_LocationCode>SIN</IATA_LocationCode>
                  <TerminalName>3</TerminalName>
                  <AircraftScheduledDateTime>2024-01-31T22:20:00</AircraftScheduledDateTime>
                </Dep>
                <Arrival>
                  <IATA_LocationCode>MAA</IATA_LocationCode>
                  <TerminalName>3</TerminalName>
                  <AircraftScheduledDateTime>2024-01-31T23:50:00</AircraftScheduledDateTime>
                </Arrival>
                <IATA_AircraftType>
                  <IATA_AircraftTypeCode>787</IATA_AircraftTypeCode>
                </IATA_AircraftType>
              </DatedOperatingLeg>
            </PaxSegment>
            <PaxSegment>
              <PaxSegmentID>SEG27</PaxSegmentID>
              <Dep>
                <IATA_LocationCode>BLR</IATA_LocationCode>
                <TerminalName>1</TerminalName>
                <AircraftScheduledDateTime>2024-01-31T13:10:00</AircraftScheduledDateTime>
              </Dep>
              <Arrival>
                <IATA_LocationCode>MAA</IATA_LocationCode>
                <TerminalName>1</TerminalName>
                <AircraftScheduledDateTime>2024-01-31T14:20:00</AircraftScheduledDateTime>
              </Arrival>
              <MarketingCarrierInfo>
                <CarrierDesigCode>AI</CarrierDesigCode>
                <MarketingCarrierFlightNumberText>564</MarketingCarrierFlightNumberText>
              </MarketingCarrierInfo>
              <OperatingCarrierInfo>
                <CarrierDesigCode>AI</CarrierDesigCode>
                <CarrierName>AIR INDIA</CarrierName>
              </OperatingCarrierInfo>
              <DatedOperatingLeg>
                <DatedOperatingLegID>LEG11</DatedOperatingLegID>
                <Dep>
                  <IATA_LocationCode>BLR</IATA_LocationCode>
                  <TerminalName>1</TerminalName>
                  <AircraftScheduledDateTime>2024-01-31T13:10:00</AircraftScheduledDateTime>
                </Dep>
                <Arrival>
                  <IATA_LocationCode>MAA</IATA_LocationCode>
                  <TerminalName>1</TerminalName>
                  <AircraftScheduledDateTime>2024-01-31T14:20:00</AircraftScheduledDateTime>
                </Arrival>
                <IATA_AircraftType>
                  <IATA_AircraftTypeCode>321</IATA_AircraftTypeCode>
                </IATA_AircraftType>
              </DatedOperatingLeg>
            </PaxSegment>
            <PaxSegment>
              <PaxSegmentID>SEG22</PaxSegmentID>
              <Dep>
                <IATA_LocationCode>SIN</IATA_LocationCode>
                <TerminalName>2</TerminalName>
                <AircraftScheduledDateTime>2024-01-31T22:10:00</AircraftScheduledDateTime>
              </Dep>
              <Arrival>
                <IATA_LocationCode>CMB</IATA_LocationCode>
                <AircraftScheduledDateTime>2024-01-31T23:30:00</AircraftScheduledDateTime>
              </Arrival>
              <MarketingCarrierInfo>
                <CarrierDesigCode>SQ</CarrierDesigCode>
                <MarketingCarrierFlightNumberText>468</MarketingCarrierFlightNumberText>
              </MarketingCarrierInfo>
              <OperatingCarrierInfo>
                <CarrierDesigCode>SQ</CarrierDesigCode>
                <CarrierName>SINGAPORE AIRLINES</CarrierName>
              </OperatingCarrierInfo>
              <DatedOperatingLeg>
                <DatedOperatingLegID>LEG12</DatedOperatingLegID>
                <Dep>
                  <IATA_LocationCode>SIN</IATA_LocationCode>
                  <TerminalName>2</TerminalName>
                  <AircraftScheduledDateTime>2024-01-31T22:10:00</AircraftScheduledDateTime>
                </Dep>
                <Arrival>
                  <IATA_LocationCode>CMB</IATA_LocationCode>
                  <AircraftScheduledDateTime>2024-01-31T23:30:00</AircraftScheduledDateTime>
                </Arrival>
                <IATA_AircraftType>
                  <IATA_AircraftTypeCode>787</IATA_AircraftTypeCode>
                </IATA_AircraftType>
              </DatedOperatingLeg>
            </PaxSegment>
            <PaxSegment>
              <PaxSegmentID>SEG18</PaxSegmentID>
              <Dep>
                <IATA_LocationCode>SIN</IATA_LocationCode>
                <TerminalName>3</TerminalName>
                <AircraftScheduledDateTime>2024-01-31T20:20:00</AircraftScheduledDateTime>
              </Dep>
              <Arrival>
                <IATA_LocationCode>MAA</IATA_LocationCode>
                <TerminalName>3</TerminalName>
                <AircraftScheduledDateTime>2024-01-31T22:00:00</AircraftScheduledDateTime>
              </Arrival>
              <MarketingCarrierInfo>
                <CarrierDesigCode>SQ</CarrierDesigCode>
                <MarketingCarrierFlightNumberText>528</MarketingCarrierFlightNumberText>
              </MarketingCarrierInfo>
              <OperatingCarrierInfo>
                <CarrierDesigCode>SQ</CarrierDesigCode>
                <CarrierName>SINGAPORE AIRLINES</CarrierName>
              </OperatingCarrierInfo>
              <DatedOperatingLeg>
                <DatedOperatingLegID>LEG13</DatedOperatingLegID>
                <Dep>
                  <IATA_LocationCode>SIN</IATA_LocationCode>
                  <TerminalName>3</TerminalName>
                  <AircraftScheduledDateTime>2024-01-31T20:20:00</AircraftScheduledDateTime>
                </Dep>
                <Arrival>
                  <IATA_LocationCode>MAA</IATA_LocationCode>
                  <TerminalName>3</TerminalName>
                  <AircraftScheduledDateTime>2024-01-31T22:00:00</AircraftScheduledDateTime>
                </Arrival>
                <IATA_AircraftType>
                  <IATA_AircraftTypeCode>787</IATA_AircraftTypeCode>
                </IATA_AircraftType>
              </DatedOperatingLeg>
            </PaxSegment>
          </PaxSegmentList>
          <PenaltyList>
            <Penalty>
              <PenaltyID>PEN1</PenaltyID>
              <DescText>Change not permitted</DescText>
              <ChangeFeeInd>false</ChangeFeeInd>
            </Penalty>
            <Penalty>
              <PenaltyID>PEN2</PenaltyID>
              <DescText>Reissue not permitted</DescText>
              <ChangeFeeInd>false</ChangeFeeInd>
            </Penalty>
            <Penalty>
              <PenaltyID>PEN3</PenaltyID>
              <DescText>Cancel not permitted</DescText>
              <CancelFeeInd>false</CancelFeeInd>
            </Penalty>
            <Penalty>
              <PenaltyID>PEN4</PenaltyID>
              <PenaltyAmount>67.00</PenaltyAmount>
              <DescText>Change permitted</DescText>
              <AppCode>BDC</AppCode>
              <ChangeFeeInd>true</ChangeFeeInd>
            </Penalty>
            <Penalty>
              <PenaltyID>PEN5</PenaltyID>
              <PenaltyAmount>67.00</PenaltyAmount>
              <DescText>Reissue permitted</DescText>
              <AppCode>BDT</AppCode>
              <ChangeFeeInd>true</ChangeFeeInd>
            </Penalty>
            <Penalty>
              <PenaltyID>PEN6</PenaltyID>
              <PenaltyAmount>200.00</PenaltyAmount>
              <DescText>Cancel permitted</DescText>
              <AppCode>BDT</AppCode>
              <CancelFeeInd>true</CancelFeeInd>
            </Penalty>
            <Penalty>
              <PenaltyID>PEN7</PenaltyID>
              <PenaltyAmount>27.00</PenaltyAmount>
              <DescText>Change permitted</DescText>
              <AppCode>BDC</AppCode>
              <ChangeFeeInd>true</ChangeFeeInd>
            </Penalty>
            <Penalty>
              <PenaltyID>PEN8</PenaltyID>
              <PenaltyAmount>27.00</PenaltyAmount>
              <DescText>Reissue permitted</DescText>
              <AppCode>BDT</AppCode>
              <ChangeFeeInd>true</ChangeFeeInd>
            </Penalty>
            <Penalty>
              <PenaltyID>PEN9</PenaltyID>
              <PenaltyAmount>133.00</PenaltyAmount>
              <DescText>Cancel permitted</DescText>
              <AppCode>BDT</AppCode>
              <CancelFeeInd>true</CancelFeeInd>
            </Penalty>
            <Penalty>
              <PenaltyID>PEN10</PenaltyID>
              <PenaltyAmount>0.00</PenaltyAmount>
              <DescText>Change permitted</DescText>
              <AppCode>BDC</AppCode>
              <ChangeFeeInd>true</ChangeFeeInd>
            </Penalty>
            <Penalty>
              <PenaltyID>PEN11</PenaltyID>
              <PenaltyAmount>0.00</PenaltyAmount>
              <DescText>Reissue permitted</DescText>
              <AppCode>BDT</AppCode>
              <ChangeFeeInd>true</ChangeFeeInd>
            </Penalty>
            <Penalty>
              <PenaltyID>PEN12</PenaltyID>
              <PenaltyAmount>67.00</PenaltyAmount>
              <DescText>Cancel permitted</DescText>
              <AppCode>BDT</AppCode>
              <CancelFeeInd>true</CancelFeeInd>
            </Penalty>
            <Penalty>
              <PenaltyID>PEN13</PenaltyID>
              <PenaltyAmount>0.00</PenaltyAmount>
              <DescText>Change permitted</DescText>
              <AppCode>BDC</AppCode>
              <ChangeFeeInd>true</ChangeFeeInd>
            </Penalty>
            <Penalty>
              <PenaltyID>PEN14</PenaltyID>
              <PenaltyAmount>0.00</PenaltyAmount>
              <DescText>Reissue permitted</DescText>
              <AppCode>BDT</AppCode>
              <ChangeFeeInd>true</ChangeFeeInd>
            </Penalty>
            <Penalty>
              <PenaltyID>PEN15</PenaltyID>
              <PenaltyAmount>0.00</PenaltyAmount>
              <DescText>Cancel permitted</DescText>
              <AppCode>BDT</AppCode>
              <CancelFeeInd>true</CancelFeeInd>
            </Penalty>
          </PenaltyList>
          <PriceClassList>
            <PriceClass>
              <PriceClassID>FF51</PriceClassID>
              <Name>Economy Lite</Name>
              <Desc>
                <DescID>CANCEL_BEFOREDEPARTURE</DescID>
                <DescText>Not Allowed</DescText>
              </Desc>
              <Desc>
                <DescID>CANCEL_NOSHOWFIRST</DescID>
                <DescText>Not Allowed</DescText>
              </Desc>
              <Desc>
                <DescID>CHANGE_BEFOREDEPARTURE</DescID>
                <DescText>Not Allowed</DescText>
              </Desc>
              <Desc>
                <DescID>AWARD_UPGRADE</DescID>
                <DescText>Not Allowed</DescText>
              </Desc>
              <Desc>
                <DescID>AWARD_ACCRUAL</DescID>
                <DescText>50%</DescText>
              </Desc>
              <Desc>
                <DescID>BAGGAGEALLOWANCE_CARRYON</DescID>
                <DescText>1 piece Up to 7kg each. Sum of length, width and height of each piece should not exceed 115cm.</DescText>
              </Desc>
              <Desc>
                <DescID>SEATSELECTION</DescID>
                <DescText>Chargeable</DescText>
              </Desc>
            </PriceClass>
            <PriceClass>
              <PriceClassID>FF21</PriceClassID>
              <Name>Economy Flexi</Name>
              <Desc>
                <DescID>CANCEL_BEFOREDEPARTURE</DescID>
                <DescText>USD 50-100</DescText>
              </Desc>
              <Desc>
                <DescID>CANCEL_NOSHOWFIRST</DescID>
                <DescText>USD 100</DescText>
              </Desc>
              <Desc>
                <DescID>CHANGE_BEFOREDEPARTURE</DescID>
                <DescText>Complimentary</DescText>
              </Desc>
              <Desc>
                <DescID>AWARD_UPGRADE</DescID>
                <DescText>Allowed</DescText>
              </Desc>
              <Desc>
                <DescID>AWARD_ACCRUAL</DescID>
                <DescText>100%</DescText>
              </Desc>
              <Desc>
                <DescID>BAGGAGEALLOWANCE_CARRYON</DescID>
                <DescText>1 piece Up to 7kg each. Sum of length, width and height of each piece should not exceed 115cm.</DescText>
              </Desc>
              <Desc>
                <DescID>SEATSELECTION</DescID>
                <DescText>Complimentary (Forward Zone and Standard Seats)</DescText>
              </Desc>
            </PriceClass>
            <PriceClass>
              <PriceClassID>FF31</PriceClassID>
              <Name>Economy Standard</Name>
              <Desc>
                <DescID>CANCEL_BEFOREDEPARTURE</DescID>
                <DescText>USD 100-200</DescText>
              </Desc>
              <Desc>
                <DescID>CANCEL_NOSHOWFIRST</DescID>
                <DescText>USD 100</DescText>
              </Desc>
              <Desc>
                <DescID>CHANGE_BEFOREDEPARTURE</DescID>
                <DescText>USD 20-50</DescText>
              </Desc>
              <Desc>
                <DescID>AWARD_UPGRADE</DescID>
                <DescText>Allowed</DescText>
              </Desc>
              <Desc>
                <DescID>AWARD_ACCRUAL</DescID>
                <DescText>75%</DescText>
              </Desc>
              <Desc>
                <DescID>BAGGAGEALLOWANCE_CARRYON</DescID>
                <DescText>1 piece Up to 7kg each. Sum of length, width and height of each piece should not exceed 115cm.</DescText>
              </Desc>
              <Desc>
                <DescID>SEATSELECTION</DescID>
                <DescText>Complimentary (Standard Seats)</DescText>
              </Desc>
            </PriceClass>
            <PriceClass>
              <PriceClassID>FF41</PriceClassID>
              <Name>Economy Value</Name>
              <Desc>
                <DescID>CANCEL_BEFOREDEPARTURE</DescID>
                <DescText>USD 150-300</DescText>
              </Desc>
              <Desc>
                <DescID>CANCEL_NOSHOWFIRST</DescID>
                <DescText>USD 100-300</DescText>
              </Desc>
              <Desc>
                <DescID>CHANGE_BEFOREDEPARTURE</DescID>
                <DescText>USD 50-150</DescText>
              </Desc>
              <Desc>
                <DescID>AWARD_UPGRADE</DescID>
                <DescText>Not Allowed</DescText>
              </Desc>
              <Desc>
                <DescID>AWARD_ACCRUAL</DescID>
                <DescText>50%</DescText>
              </Desc>
              <Desc>
                <DescID>BAGGAGEALLOWANCE_CARRYON</DescID>
                <DescText>1 piece Up to 7kg each. Sum of length, width and height of each piece should not exceed 115cm.</DescText>
              </Desc>
              <Desc>
                <DescID>SEATSELECTION</DescID>
                <DescText>Chargeable</DescText>
              </Desc>
            </PriceClass>
          </PriceClassList>
        </DataLists>
        <OffersGroup>
          <CarrierOffers>
            <Offer>
              <OfferID>SP2F-14713846551743062930-1</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA1</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN1</PenaltyRefID>
              <PenaltyRefID>PEN2</PenaltyRefID>
              <PenaltyRefID>PEN3</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF51</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-1-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>494.90</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>386.00</BaseAmount>
                    <Taxes>
                      <Total>108.90</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>K16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>K</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF51</PriceClassRef>
                    <SegmentRefs>SEG5</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>K16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>K</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF51</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>394.10</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>290.00</BaseAmount>
                    <Taxes>
                      <Total>104.10</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>K16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>K</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF51</PriceClassRef>
                    <SegmentRefs>SEG5</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>K16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>K</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF51</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>1383.90</TotalAmount>
                  <BaseAmount>1062.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>321.90</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-2</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA1</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN1</PenaltyRefID>
              <PenaltyRefID>PEN2</PenaltyRefID>
              <PenaltyRefID>PEN3</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF51</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-2-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>494.90</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>386.00</BaseAmount>
                    <Taxes>
                      <Total>108.90</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>K16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>K</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF51</PriceClassRef>
                    <SegmentRefs>SEG4</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>K16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>K</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF51</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>394.10</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>290.00</BaseAmount>
                    <Taxes>
                      <Total>104.10</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>K16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>K</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF51</PriceClassRef>
                    <SegmentRefs>SEG4</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>K16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>K</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF51</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>1383.90</TotalAmount>
                  <BaseAmount>1062.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>321.90</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-3</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA1</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN1</PenaltyRefID>
              <PenaltyRefID>PEN2</PenaltyRefID>
              <PenaltyRefID>PEN3</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF51</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-3-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>494.90</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>386.00</BaseAmount>
                    <Taxes>
                      <Total>108.90</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>K16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>K</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF51</PriceClassRef>
                    <SegmentRefs>SEG5</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>K16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>K</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF51</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>394.10</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>290.00</BaseAmount>
                    <Taxes>
                      <Total>104.10</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>K16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>K</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF51</PriceClassRef>
                    <SegmentRefs>SEG5</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>K16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>K</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF51</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>1383.90</TotalAmount>
                  <BaseAmount>1062.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>321.90</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-4</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA1</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN1</PenaltyRefID>
              <PenaltyRefID>PEN2</PenaltyRefID>
              <PenaltyRefID>PEN3</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF51</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-4-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>494.90</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>386.00</BaseAmount>
                    <Taxes>
                      <Total>108.90</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>K16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>K</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF51</PriceClassRef>
                    <SegmentRefs>SEG4</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>K16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>K</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF51</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>394.10</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>290.00</BaseAmount>
                    <Taxes>
                      <Total>104.10</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>K16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>K</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF51</PriceClassRef>
                    <SegmentRefs>SEG4</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>K16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>K</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF51</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>1383.90</TotalAmount>
                  <BaseAmount>1062.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>321.90</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-5</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA1</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN1</PenaltyRefID>
              <PenaltyRefID>PEN2</PenaltyRefID>
              <PenaltyRefID>PEN3</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF51</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-5-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>494.90</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>386.00</BaseAmount>
                    <Taxes>
                      <Total>108.90</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>K16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>K</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF51</PriceClassRef>
                    <SegmentRefs>SEG3</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>K16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>K</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF51</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>394.10</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>290.00</BaseAmount>
                    <Taxes>
                      <Total>104.10</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>K16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>K</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF51</PriceClassRef>
                    <SegmentRefs>SEG3</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>K16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>K</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF51</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>1383.90</TotalAmount>
                  <BaseAmount>1062.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>321.90</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-6</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA1</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN1</PenaltyRefID>
              <PenaltyRefID>PEN2</PenaltyRefID>
              <PenaltyRefID>PEN3</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF51</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-6-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>494.90</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>386.00</BaseAmount>
                    <Taxes>
                      <Total>108.90</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>K16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>K</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF51</PriceClassRef>
                    <SegmentRefs>SEG3</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>K16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>K</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF51</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>394.10</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>290.00</BaseAmount>
                    <Taxes>
                      <Total>104.10</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>K16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>K</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF51</PriceClassRef>
                    <SegmentRefs>SEG3</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>K16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>K</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF51</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>1383.90</TotalAmount>
                  <BaseAmount>1062.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>321.90</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-7</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA1</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN4</PenaltyRefID>
              <PenaltyRefID>PEN5</PenaltyRefID>
              <PenaltyRefID>PEN6</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF41</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-7-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>550.60</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>439.00</BaseAmount>
                    <Taxes>
                      <Total>111.60</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>N16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF41</PriceClassRef>
                    <SegmentRefs>SEG5</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>N16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF41</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>436.10</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>330.00</BaseAmount>
                    <Taxes>
                      <Total>106.10</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>N16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF41</PriceClassRef>
                    <SegmentRefs>SEG5</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>N16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF41</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>1537.30</TotalAmount>
                  <BaseAmount>1208.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>329.30</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-8</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA1</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN4</PenaltyRefID>
              <PenaltyRefID>PEN5</PenaltyRefID>
              <PenaltyRefID>PEN6</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF41</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-8-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>550.60</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>439.00</BaseAmount>
                    <Taxes>
                      <Total>111.60</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>N16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF41</PriceClassRef>
                    <SegmentRefs>SEG4</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>N16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF41</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>436.10</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>330.00</BaseAmount>
                    <Taxes>
                      <Total>106.10</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>N16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF41</PriceClassRef>
                    <SegmentRefs>SEG4</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>N16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF41</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>1537.30</TotalAmount>
                  <BaseAmount>1208.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>329.30</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-9</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA1</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN4</PenaltyRefID>
              <PenaltyRefID>PEN5</PenaltyRefID>
              <PenaltyRefID>PEN6</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF41</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-9-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>550.60</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>439.00</BaseAmount>
                    <Taxes>
                      <Total>111.60</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>N16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF41</PriceClassRef>
                    <SegmentRefs>SEG5</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>N16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF41</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>436.10</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>330.00</BaseAmount>
                    <Taxes>
                      <Total>106.10</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>N16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF41</PriceClassRef>
                    <SegmentRefs>SEG5</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>N16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF41</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>1537.30</TotalAmount>
                  <BaseAmount>1208.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>329.30</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-10</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA1</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN4</PenaltyRefID>
              <PenaltyRefID>PEN5</PenaltyRefID>
              <PenaltyRefID>PEN6</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF41</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-10-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>550.60</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>439.00</BaseAmount>
                    <Taxes>
                      <Total>111.60</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>N16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF41</PriceClassRef>
                    <SegmentRefs>SEG4</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>N16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF41</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>436.10</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>330.00</BaseAmount>
                    <Taxes>
                      <Total>106.10</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>N16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF41</PriceClassRef>
                    <SegmentRefs>SEG4</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>N16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF41</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>1537.30</TotalAmount>
                  <BaseAmount>1208.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>329.30</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-11</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA1</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN4</PenaltyRefID>
              <PenaltyRefID>PEN5</PenaltyRefID>
              <PenaltyRefID>PEN6</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF41</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-11-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>550.60</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>439.00</BaseAmount>
                    <Taxes>
                      <Total>111.60</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>N16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF41</PriceClassRef>
                    <SegmentRefs>SEG3</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>N16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF41</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>436.10</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>330.00</BaseAmount>
                    <Taxes>
                      <Total>106.10</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>N16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF41</PriceClassRef>
                    <SegmentRefs>SEG3</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>N16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF41</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>1537.30</TotalAmount>
                  <BaseAmount>1208.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>329.30</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-12</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA1</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN4</PenaltyRefID>
              <PenaltyRefID>PEN5</PenaltyRefID>
              <PenaltyRefID>PEN6</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF41</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-12-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>550.60</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>439.00</BaseAmount>
                    <Taxes>
                      <Total>111.60</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>N16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF41</PriceClassRef>
                    <SegmentRefs>SEG3</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>N16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF41</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>436.10</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>330.00</BaseAmount>
                    <Taxes>
                      <Total>106.10</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>N16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF41</PriceClassRef>
                    <SegmentRefs>SEG3</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>N16IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF41</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>1537.30</TotalAmount>
                  <BaseAmount>1208.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>329.30</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-13</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN7</PenaltyRefID>
              <PenaltyRefID>PEN8</PenaltyRefID>
              <PenaltyRefID>PEN9</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF31</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-13-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>739.60</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>619.00</BaseAmount>
                    <Taxes>
                      <Total>120.60</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>W14IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>W</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF31</PriceClassRef>
                    <SegmentRefs>SEG5</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>W14IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>W</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF31</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>576.80</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>464.00</BaseAmount>
                    <Taxes>
                      <Total>112.80</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>W14IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>W</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF31</PriceClassRef>
                    <SegmentRefs>SEG5</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>W14IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>W</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF31</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>2056.00</TotalAmount>
                  <BaseAmount>1702.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>354.00</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-14</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN7</PenaltyRefID>
              <PenaltyRefID>PEN8</PenaltyRefID>
              <PenaltyRefID>PEN9</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF31</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-14-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>739.60</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>619.00</BaseAmount>
                    <Taxes>
                      <Total>120.60</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>W14IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>W</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF31</PriceClassRef>
                    <SegmentRefs>SEG4</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>W14IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>W</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF31</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>576.80</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>464.00</BaseAmount>
                    <Taxes>
                      <Total>112.80</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>W14IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>W</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF31</PriceClassRef>
                    <SegmentRefs>SEG4</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>W14IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>W</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF31</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>2056.00</TotalAmount>
                  <BaseAmount>1702.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>354.00</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-15</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN7</PenaltyRefID>
              <PenaltyRefID>PEN8</PenaltyRefID>
              <PenaltyRefID>PEN9</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF31</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-15-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>739.60</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>619.00</BaseAmount>
                    <Taxes>
                      <Total>120.60</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>W14IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>W</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF31</PriceClassRef>
                    <SegmentRefs>SEG5</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>W14IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>W</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF31</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>576.80</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>464.00</BaseAmount>
                    <Taxes>
                      <Total>112.80</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>W14IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>W</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF31</PriceClassRef>
                    <SegmentRefs>SEG5</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>W14IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>W</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF31</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>2056.00</TotalAmount>
                  <BaseAmount>1702.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>354.00</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-16</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN7</PenaltyRefID>
              <PenaltyRefID>PEN8</PenaltyRefID>
              <PenaltyRefID>PEN9</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF31</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-16-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>739.60</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>619.00</BaseAmount>
                    <Taxes>
                      <Total>120.60</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>W14IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>W</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF31</PriceClassRef>
                    <SegmentRefs>SEG4</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>W14IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>W</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF31</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>576.80</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>464.00</BaseAmount>
                    <Taxes>
                      <Total>112.80</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>W14IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>W</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF31</PriceClassRef>
                    <SegmentRefs>SEG4</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>W14IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>W</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF31</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>2056.00</TotalAmount>
                  <BaseAmount>1702.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>354.00</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-17</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN7</PenaltyRefID>
              <PenaltyRefID>PEN8</PenaltyRefID>
              <PenaltyRefID>PEN9</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF31</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-17-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>739.60</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>619.00</BaseAmount>
                    <Taxes>
                      <Total>120.60</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>W14IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>W</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF31</PriceClassRef>
                    <SegmentRefs>SEG3</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>W14IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>W</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF31</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>576.80</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>464.00</BaseAmount>
                    <Taxes>
                      <Total>112.80</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>W14IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>W</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF31</PriceClassRef>
                    <SegmentRefs>SEG3</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>W14IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>W</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF31</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>2056.00</TotalAmount>
                  <BaseAmount>1702.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>354.00</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-18</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN7</PenaltyRefID>
              <PenaltyRefID>PEN8</PenaltyRefID>
              <PenaltyRefID>PEN9</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF31</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-18-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>739.60</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>619.00</BaseAmount>
                    <Taxes>
                      <Total>120.60</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>W14IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>W</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF31</PriceClassRef>
                    <SegmentRefs>SEG3</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>W14IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>W</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF31</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>576.80</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>464.00</BaseAmount>
                    <Taxes>
                      <Total>112.80</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>W14IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>W</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF31</PriceClassRef>
                    <SegmentRefs>SEG3</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>W14IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>W</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF31</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>2056.00</TotalAmount>
                  <BaseAmount>1702.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>354.00</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-19</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN10</PenaltyRefID>
              <PenaltyRefID>PEN11</PenaltyRefID>
              <PenaltyRefID>PEN12</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF21</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-19-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>1238.30</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>1094.00</BaseAmount>
                    <Taxes>
                      <Total>144.30</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>E12IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>E</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG5</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>E12IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>E</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>950.60</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>820.00</BaseAmount>
                    <Taxes>
                      <Total>130.60</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>E12IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>E</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG5</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>E12IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>E</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>3427.20</TotalAmount>
                  <BaseAmount>3008.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>419.20</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-20</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN10</PenaltyRefID>
              <PenaltyRefID>PEN11</PenaltyRefID>
              <PenaltyRefID>PEN12</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF21</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-20-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>1238.30</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>1094.00</BaseAmount>
                    <Taxes>
                      <Total>144.30</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>E12IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>E</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG4</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>E12IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>E</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>950.60</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>820.00</BaseAmount>
                    <Taxes>
                      <Total>130.60</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>E12IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>E</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG4</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>E12IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>E</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>3427.20</TotalAmount>
                  <BaseAmount>3008.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>419.20</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-21</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN10</PenaltyRefID>
              <PenaltyRefID>PEN11</PenaltyRefID>
              <PenaltyRefID>PEN12</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF21</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-21-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>1238.30</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>1094.00</BaseAmount>
                    <Taxes>
                      <Total>144.30</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>E12IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>E</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG5</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>E12IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>E</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>950.60</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>820.00</BaseAmount>
                    <Taxes>
                      <Total>130.60</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>E12IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>E</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG5</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>E12IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>E</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>3427.20</TotalAmount>
                  <BaseAmount>3008.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>419.20</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-22</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN10</PenaltyRefID>
              <PenaltyRefID>PEN11</PenaltyRefID>
              <PenaltyRefID>PEN12</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF21</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-22-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>1238.30</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>1094.00</BaseAmount>
                    <Taxes>
                      <Total>144.30</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>E12IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>E</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG4</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>E12IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>E</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>950.60</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>820.00</BaseAmount>
                    <Taxes>
                      <Total>130.60</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>E12IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>E</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG4</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>E12IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>E</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>3427.20</TotalAmount>
                  <BaseAmount>3008.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>419.20</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-23</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN10</PenaltyRefID>
              <PenaltyRefID>PEN11</PenaltyRefID>
              <PenaltyRefID>PEN12</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF21</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-23-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>1238.30</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>1094.00</BaseAmount>
                    <Taxes>
                      <Total>144.30</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>E12IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>E</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG3</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>E12IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>E</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>950.60</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>820.00</BaseAmount>
                    <Taxes>
                      <Total>130.60</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>E12IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>E</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG3</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>E12IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>E</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>3427.20</TotalAmount>
                  <BaseAmount>3008.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>419.20</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-24</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN10</PenaltyRefID>
              <PenaltyRefID>PEN11</PenaltyRefID>
              <PenaltyRefID>PEN12</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF21</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-24-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>1238.30</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>1094.00</BaseAmount>
                    <Taxes>
                      <Total>144.30</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>E12IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>E</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG3</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>E12IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>E</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>950.60</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>820.00</BaseAmount>
                    <Taxes>
                      <Total>130.60</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>E12IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>E</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG3</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>E12IIRB1</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>NEGOTIATED FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>E</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>3427.20</TotalAmount>
                  <BaseAmount>3008.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>419.20</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-25</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                <PaxJourneyRefID>FLT6</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN13</PenaltyRefID>
              <PenaltyRefID>PEN14</PenaltyRefID>
              <PenaltyRefID>PEN15</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF21</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT6</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-25-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>2848.00</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>2571.00</BaseAmount>
                    <Taxes>
                      <Total>277.00</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG5</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG26</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>U</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG27</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>2173.90</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>1929.00</BaseAmount>
                    <Taxes>
                      <Total>244.90</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG5</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG26</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>U</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG27</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT6</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>7869.90</TotalAmount>
                  <BaseAmount>7071.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>798.90</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-26</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                <PaxJourneyRefID>FLT6</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN13</PenaltyRefID>
              <PenaltyRefID>PEN14</PenaltyRefID>
              <PenaltyRefID>PEN15</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF21</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT6</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-26-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>2848.00</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>2571.00</BaseAmount>
                    <Taxes>
                      <Total>277.00</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG4</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG26</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>U</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG27</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>2173.90</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>1929.00</BaseAmount>
                    <Taxes>
                      <Total>244.90</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG4</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG26</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>U</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG27</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT6</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>7869.90</TotalAmount>
                  <BaseAmount>7071.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>798.90</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-27</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                <PaxJourneyRefID>FLT6</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN13</PenaltyRefID>
              <PenaltyRefID>PEN14</PenaltyRefID>
              <PenaltyRefID>PEN15</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF21</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT6</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-27-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>2848.00</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>2571.00</BaseAmount>
                    <Taxes>
                      <Total>277.00</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG3</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG26</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>U</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG27</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>2173.90</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>1929.00</BaseAmount>
                    <Taxes>
                      <Total>244.90</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG3</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG26</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>U</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG27</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT6</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>7869.90</TotalAmount>
                  <BaseAmount>7071.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>798.90</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-28</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                <PaxJourneyRefID>FLT7</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN13</PenaltyRefID>
              <PenaltyRefID>PEN14</PenaltyRefID>
              <PenaltyRefID>PEN15</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF21</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT7</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-28-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>2935.80</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>2571.00</BaseAmount>
                    <Taxes>
                      <Total>364.80</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG5</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG22</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG23</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>2261.70</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>1929.00</BaseAmount>
                    <Taxes>
                      <Total>332.70</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG5</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG22</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG23</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT1</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT7</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>8133.30</TotalAmount>
                  <BaseAmount>7071.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>1062.30</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-29</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                <PaxJourneyRefID>FLT7</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN13</PenaltyRefID>
              <PenaltyRefID>PEN14</PenaltyRefID>
              <PenaltyRefID>PEN15</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF21</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT7</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-29-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>2935.80</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>2571.00</BaseAmount>
                    <Taxes>
                      <Total>364.80</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG4</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG22</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG23</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>2261.70</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>1929.00</BaseAmount>
                    <Taxes>
                      <Total>332.70</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG4</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG22</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG23</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT3</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT7</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>8133.30</TotalAmount>
                  <BaseAmount>7071.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>1062.30</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-30</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                <PaxJourneyRefID>FLT7</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN13</PenaltyRefID>
              <PenaltyRefID>PEN14</PenaltyRefID>
              <PenaltyRefID>PEN15</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF21</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT7</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-30-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>2935.80</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>2571.00</BaseAmount>
                    <Taxes>
                      <Total>364.80</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG3</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG22</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG23</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>2261.70</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>1929.00</BaseAmount>
                    <Taxes>
                      <Total>332.70</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG3</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG22</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG23</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT5</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT7</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>8133.30</TotalAmount>
                  <BaseAmount>7071.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>1062.30</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-31</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT8</PaxJourneyRefID>
                <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN13</PenaltyRefID>
              <PenaltyRefID>PEN14</PenaltyRefID>
              <PenaltyRefID>PEN15</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF21</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT8</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-31-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>2935.80</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>2571.00</BaseAmount>
                    <Taxes>
                      <Total>364.80</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG10</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG11</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>2261.70</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>1929.00</BaseAmount>
                    <Taxes>
                      <Total>332.70</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG10</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG11</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT8</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>8133.30</TotalAmount>
                  <BaseAmount>7071.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>1062.30</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-32</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT8</PaxJourneyRefID>
                <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN13</PenaltyRefID>
              <PenaltyRefID>PEN14</PenaltyRefID>
              <PenaltyRefID>PEN15</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF21</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT8</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-32-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>2935.80</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>2571.00</BaseAmount>
                    <Taxes>
                      <Total>364.80</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG10</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG11</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>2261.70</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>1929.00</BaseAmount>
                    <Taxes>
                      <Total>332.70</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG10</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG11</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT8</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>8133.30</TotalAmount>
                  <BaseAmount>7071.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>1062.30</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-33</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT9</PaxJourneyRefID>
                <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN13</PenaltyRefID>
              <PenaltyRefID>PEN14</PenaltyRefID>
              <PenaltyRefID>PEN15</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF21</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT9</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-33-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>2983.40</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>2700.00</BaseAmount>
                    <Taxes>
                      <Total>283.40</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>T</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG14</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG15</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>2274.70</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>2025.00</BaseAmount>
                    <Taxes>
                      <Total>249.70</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>T</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG14</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG15</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG19</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT9</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT2</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>8241.50</TotalAmount>
                  <BaseAmount>7425.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>816.50</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-34</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT9</PaxJourneyRefID>
                <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN13</PenaltyRefID>
              <PenaltyRefID>PEN14</PenaltyRefID>
              <PenaltyRefID>PEN15</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF21</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT9</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-34-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>2983.40</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>2700.00</BaseAmount>
                    <Taxes>
                      <Total>283.40</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>T</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG14</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG15</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>2274.70</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>2025.00</BaseAmount>
                    <Taxes>
                      <Total>249.70</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>T</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG14</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG15</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG18</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT9</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT4</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>8241.50</TotalAmount>
                  <BaseAmount>7425.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>816.50</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-35</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT8</PaxJourneyRefID>
                <PaxJourneyRefID>FLT6</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN13</PenaltyRefID>
              <PenaltyRefID>PEN14</PenaltyRefID>
              <PenaltyRefID>PEN15</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF21</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT8</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT6</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-35-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>2994.60</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>2571.00</BaseAmount>
                    <Taxes>
                      <Total>423.60</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG10</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG11</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG26</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>U</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG27</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>2320.50</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>1929.00</BaseAmount>
                    <Taxes>
                      <Total>391.50</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG10</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG11</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG26</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>U</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG27</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT8</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT6</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>8309.70</TotalAmount>
                  <BaseAmount>7071.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>1238.70</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-36</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT9</PaxJourneyRefID>
                <PaxJourneyRefID>FLT6</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN13</PenaltyRefID>
              <PenaltyRefID>PEN14</PenaltyRefID>
              <PenaltyRefID>PEN15</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF21</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT9</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT6</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-36-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>3042.20</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>2700.00</BaseAmount>
                    <Taxes>
                      <Total>342.20</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>T</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG14</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG15</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG26</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>U</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG27</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>2333.50</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>2025.00</BaseAmount>
                    <Taxes>
                      <Total>308.50</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>T</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG14</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG15</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG26</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>U</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG27</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT9</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT6</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>8417.90</TotalAmount>
                  <BaseAmount>7425.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>992.90</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-37</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT8</PaxJourneyRefID>
                <PaxJourneyRefID>FLT7</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN13</PenaltyRefID>
              <PenaltyRefID>PEN14</PenaltyRefID>
              <PenaltyRefID>PEN15</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF21</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT8</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT7</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-37-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>3082.40</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>2571.00</BaseAmount>
                    <Taxes>
                      <Total>511.40</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG10</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG11</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG22</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG23</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>2408.30</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>1929.00</BaseAmount>
                    <Taxes>
                      <Total>479.30</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG10</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG11</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG22</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG23</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT8</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT7</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>8573.10</TotalAmount>
                  <BaseAmount>7071.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>1502.10</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <Offer>
              <OfferID>SP2F-14713846551743062930-38</OfferID>
              <OwnerCode>SQ</OwnerCode>
              <OfferExpirationDateTime>2024-01-15T15:47:40Z</OfferExpirationDateTime>
              <BaggageAllowance>
                <PaxJourneyRefID>FLT9</PaxJourneyRefID>
                <PaxJourneyRefID>FLT7</PaxJourneyRefID>
                <PaxRefID>PAX1</PaxRefID>
                <PaxRefID>PAX3</PaxRefID>
                <PaxRefID>PAX2</PaxRefID>
                <BaggageAllowanceRefID>FBA2</BaggageAllowanceRefID>
              </BaggageAllowance>
              <PenaltyRefID>PEN13</PenaltyRefID>
              <PenaltyRefID>PEN14</PenaltyRefID>
              <PenaltyRefID>PEN15</PenaltyRefID>
              <JourneyOverview>
                <PriceClassRefID>FF21</PriceClassRefID>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT9</PaxJourneyRefID>
                </JourneyPriceClass>
                <JourneyPriceClass>
                  <PaxJourneyRefID>FLT7</PaxJourneyRefID>
                </JourneyPriceClass>
              </JourneyOverview>
              <OfferItem>
                <OfferItemID>SP2F-14713846551743062930-38-1</OfferItemID>
                <MandatoryInd>true</MandatoryInd>
                <FareDetail>
                  <PassengerRefs>PAX1 PAX2</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>3130.00</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>2700.00</BaseAmount>
                    <Taxes>
                      <Total>430.00</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>T</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG14</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG15</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG22</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG23</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <FareDetail>
                  <PassengerRefs>PAX3</PassengerRefs>
                  <Price>
                    <TotalAmount>
                      <DetailCurrencyPrice>
                        <Total>2421.30</Total>
                      </DetailCurrencyPrice>
                    </TotalAmount>
                    <BaseAmount>2025.00</BaseAmount>
                    <Taxes>
                      <Total>396.30</Total>
                    </Taxes>
                  </Price>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>T</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG14</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG15</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>Y</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG22</SegmentRefs>
                  </FareComponent>
                  <FareComponent>
                    <FareBasis>
                      <FareBasisCode>
                        <Code>YIFSQ</Code>
                      </FareBasisCode>
                      <FareRulesRemarks>
                        <FareRulesRemark>
                          <Category>FT</Category>
                          <Text>PUBLIC FARE</Text>
                        </FareRulesRemark>
                      </FareRulesRemarks>
                      <RBD>N</RBD>
                      <CabinType>
                        <CabinTypeCode>M</CabinTypeCode>
                        <CabinTypeName>ECO</CabinTypeName>
                      </CabinType>
                    </FareBasis>
                    <PriceClassRef>FF21</PriceClassRef>
                    <SegmentRefs>SEG23</SegmentRefs>
                  </FareComponent>
                </FareDetail>
                <Service>
                  <ServiceID>1</ServiceID>
                  <PaxRefID>PAX1</PaxRefID>
                  <PaxRefID>PAX3</PaxRefID>
                  <PaxRefID>PAX2</PaxRefID>
                  <ServiceAssociations>
                    <PaxJourneyRefID>FLT9</PaxJourneyRefID>
                    <PaxJourneyRefID>FLT7</PaxJourneyRefID>
                  </ServiceAssociations>
                </Service>
                <Price>
                  <TotalAmount>8681.30</TotalAmount>
                  <BaseAmount>7425.00</BaseAmount>
                  <TaxSummary>
                    <TotalTaxAmount>1256.30</TotalTaxAmount>
                  </TaxSummary>
                </Price>
              </OfferItem>
            </Offer>
            <CarrierOffersSummary>
              <MatchedOfferQty>38</MatchedOfferQty>
            </CarrierOffersSummary>
          </CarrierOffers>
        </OffersGroup>
        <Metadata>
          <Other>
            <OtherMetadata>
              <CurrencyMetadatas>
                <CurrencyMetadata MetadataKey="CURSGD">
                  <Name>SGD</Name>
                </CurrencyMetadata>
              </CurrencyMetadatas>
            </OtherMetadata>
            <OtherMetadata>
              <DescriptionMetadatas>
                <DescriptionMetadata MetadataKey="DISCLAIMER">
                  <Topic>More details on fare conditions will be provided upon selecting your desired flight(s). Fees are subjected to currency conversion and your banks exchange rate.</Topic>
                  <Sequence>1</Sequence>
                </DescriptionMetadata>
              </DescriptionMetadatas>
            </OtherMetadata>
          </Other>
        </Metadata>
        <ShoppingResponse>
          <ShoppingResponseID>SP2F-14713846551743062930</ShoppingResponseID>
        </ShoppingResponse>
      </Response>
    </AirShoppingRS>
  </soap:Body>
</soap:Envelope> ';


	return $xml;
	
	}
	
	function _killSession()
	{
		
		//$this->_Ssignature111='b013b24f-dcf8-4ec0-a822-fac788753f9e';
		$_SQL="update tbo_security_token_details set status='D' where security_token= '".$this->_Ssignature."' ";
		
		//echo $_SQL;
		$data = $this->_Odb->executeQuery($_SQL);

	}
	
}
?>