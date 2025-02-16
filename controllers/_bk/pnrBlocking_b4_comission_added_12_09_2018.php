<?php
/**
	@File Name 		:	pnrBlocking.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	pnrBlocking service
*/
class pnrBlocking extends Execute
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
	
    public function _doPnrBlocking()
	{
		$_Bstatus	= true;
		$_Smessage	= '';
		$_Adata		= array();
		$_Areturn	= array();
		$_IpaxCount	= $this->_Ainput['ADT'] + $this->_Ainput['CHD'];
		
		$this->_modifyData();
		
		$_BisSpecialReturn = false;
		
		$_ApaxFareDetails = array();
		
		if(isset($this->_Ainput['paxFaresKey']) && count($this->_Ainput['paxFaresKey']) > 0){
			
			foreach($this->_Ainput['paxFaresKey'] as $tempPaxFareKey=>$tempPaxFareVal){
			
				$_AjourneySellKey	= explode("@@@",$tempPaxFareVal['journeySellKey']);
				$_ApaxFareInfo		= json_decode(base64_decode($tempPaxFareVal['fareInfoRef']),true);
				$_SresultIndex		= $_AjourneySellKey[2];
				
				if(!isset($_ApaxFareDetails[$_SresultIndex])){
					$_ApaxFareDetails[$_SresultIndex] = array();
				}
				
				$_ApaxFareDetails[$_SresultIndex][$_ApaxFareInfo['PassengerType']] = $_ApaxFareInfo;
			}
		}
		else{
			$_Areturn = array
					(
						'status' => false,
						'data'   => array(),
						'msg'    => 'Invalid PaxFaresKey',
					);
					
			return $_Areturn;
		}
		
		foreach($this->_Ainput['flightDetails'] as $fKey=>$fVal){
			
			$_AjourneySellKey = explode("@@@",$fVal['journeySellKey']);
			
			if(isset($_AjourneySellKey[2]) && !empty($_AjourneySellKey[2])){
				
				$_AflightRefultIndex[$_AjourneySellKey[2]] = $_AjourneySellKey[1];
				
				foreach($fVal['paxFaresKey'] as $tempPaxFareKey=>$tempPaxFareVal){
					
					$_ApaxFareInfo		= json_decode(base64_decode($tempPaxFareVal),true);
					$_SresultIndex		= $_AjourneySellKey[2];
					
					if(isset($_ApaxFareDetails[$_SresultIndex][$_ApaxFareInfo['PassengerType']])){
						$_ApaxFareDetails[$_SresultIndex][$_ApaxFareInfo['PassengerType']]['AirTransFee'] = $_ApaxFareInfo['AirTransFee'];
					}
				}
			}
			
			if(isset($_AjourneySellKey[3]) && !empty($_AjourneySellKey[3])){
				$_SflightTraceId = $_AjourneySellKey[3];
			}
			
			if(isset($_AjourneySellKey[4]) && $_AjourneySellKey[4] == "Y"){
				$_BisSpecialReturn = true;
			}
		}
		
		$this->_Ainput['paxFareDetails'] = $_ApaxFareDetails;
		
		if($_BisSpecialReturn && count($_AflightRefultIndex) > 0){
			
			$_AindexeValues	= array_values($_AflightRefultIndex);
			$_AindexeKeys	= array_keys($_AflightRefultIndex);
			$_SindexeKeys	= implode(",",$_AindexeKeys);
			
			$_AflightRefultIndex = array();
			
			//$_AflightRefultIndex[$_SindexeKeys] = $_AindexeValues[0];
			$_AflightRefultIndex[$_AindexeKeys[0]] = $_AindexeValues[0];
		}
		
		$_IssrFare		= 0;
		$_SfareRefFound = true;
		
		foreach($this->_Ainput['paxDetails'] as $paxKey=>$paxVal){
			
			if(isset($paxVal['meals']) && !empty($paxVal['meals'])){
				
				foreach($paxVal['meals'] as $paxMealKey=>$paxMealVal){
					
					if(isset($paxMealVal['farRefKey'])){
						$_IssrFare += base64_decode($paxMealVal['farRefKey']);
					}
					else{
						$_SfareRefFound = false;
					}
				}
			}
			
			if(isset($paxVal['seat']) && !empty($paxVal['seat'])){
				
				foreach($paxVal['seat'] as $paxSeatKey=>$paxSeatVal){
					
					if(isset($paxSeatVal['farRefKey'])){
						$_IssrFare += base64_decode($paxSeatVal['farRefKey']);
					}
					else{
						$_SfareRefFound = false;
					}
				}
			}
			
			if(isset($paxVal['baggage']) && !empty($paxVal['baggage'])){
				
				foreach($paxVal['baggage'] as $paxBagKey=>$paxBagVal){
					
					if(isset($paxBagVal['farRefKey'])){
						$_IssrFare += base64_decode($paxBagVal['farRefKey']);
					}
					else{
						$_SfareRefFound = false;
					}
				}
			}
		}
		
		if(!$_SfareRefFound){
			$_Areturn = array
					(
						'status' => false,
						'data'   => array(),
						'msg'    => 'Invalid SSR fare reference',
					);
					
			return $_Areturn;
		}
		
		$_ApnrBlockReturn = array();
		
		$this->_Ainput['flightTraceId'] = $_SflightTraceId;
		
		$_ApaxDetails = $this->_Ainput['paxDetails'];
		
		$_AupdatePriceInfo = $this->_getUpdatePrice();
		
		if(isset($_AupdatePriceInfo[0]['update_price_id']) && !empty($_AupdatePriceInfo[0]['update_price_id'])){
			
			$this->_Ainput['flightTraceId'] = $_SflightTraceId;
			
			$_IdefaultBookingTotalAmount	= $_AupdatePriceInfo[0]['default_total'];
			$_IdefaultBookingTotalAmount   += $_IssrFare;
			
			$this->_Ainput['markupData'] 	= json_decode($_AupdatePriceInfo[0]['markup_data'],true);
			
			$_SaccountCurrency				= $GLOBALS['CONF']['userSettings']['accountCurrency'];
			$_IaccountExchangeRate			= isset($GLOBALS['CONF']['exchangeRate'][$_SaccountCurrency]) ? $GLOBALS['CONF']['exchangeRate'][$_SaccountCurrency] : 1;
			
			$this->_Ainput['accountCurrency']		= $_SaccountCurrency;
			$this->_Ainput['accountExchangeRate']	= $_IaccountExchangeRate;
								
			$_IaccountBookingTotalAmount	= $_IdefaultBookingTotalAmount * $_IaccountExchangeRate;
			$_IaccountBookingTotalAmount 	= Common::getRoundedFare($_IaccountBookingTotalAmount,$_SaccountCurrency);
		
			$_BbalaceCheck = $this->_checkUserBalance($_IaccountBookingTotalAmount);
			
			if($_BbalaceCheck){
				
				foreach($_AflightRefultIndex as $key=>$val){
					
					$this->_Ainput['pnr']				= '';
					$this->_Ainput['bookingId']			= '';
					$this->_Ainput['flightResultIndex'] = $key;
					$this->_Ainput['inputPassengers']   = array();
					
					$_AtempPaxDetails = $_ApaxDetails;
					
					if(!$_BisSpecialReturn && count($_AflightRefultIndex) > 1){
						
						foreach($_AtempPaxDetails as $tempPaxKey=>$tempPaxVal){
						
							if(isset($tempPaxVal['baggage']) && count($tempPaxVal['baggage']) > 0){
								
								foreach($tempPaxVal['baggage'] as $tempBaggageKey=>$tempBaggageVal){
									
									$_AjourneySellKeyExplode = explode("@@@",$tempBaggageVal['journeySellKey']);
									
									if($key != $_AjourneySellKeyExplode[2]){
										
										unset($tempPaxVal['baggage'][$tempBaggageKey]);
									}
								}
								
								$tempPaxVal['baggage'] = array_values($tempPaxVal['baggage']);
								
								$_AtempPaxDetails[$tempPaxKey]['baggage'] = $tempPaxVal['baggage'];
							}
							
							if(isset($tempPaxVal['meals']) && count($tempPaxVal['meals']) > 0){
								
								foreach($tempPaxVal['meals'] as $tempMealKey=>$tempMealVal){
									
									$_AjourneySellKeyExplode = explode("@@@",$tempMealVal['journeySellKey']);
									
									if($key != $_AjourneySellKeyExplode[2]){
										
										unset($tempPaxVal['meals'][$tempMealKey]);
									}
								}
								
								$tempPaxVal['meals'] = array_values($tempPaxVal['meals']);
								
								$_AtempPaxDetails[$tempPaxKey]['meals'] = $tempPaxVal['meals'];
							}
						}
					}
					
					
					if($val == "Y"){
						// LCC Ticket flow
						$this->_Ainput['inputPassengers'] = $_AtempPaxDetails;
						
						$_Oticket			= controllerGet::getObject('ticket',$this);				
						$_AticketResponse	= $_Oticket->_doTicket();
						
						if(isset($_AticketResponse['status']) && !empty($_AticketResponse['status'])){
							$_ApnrBlockReturn[] = $_AticketResponse['data'];
						}
						else{
							$_Bstatus  = false;
							$_Smessage = $_AticketResponse['msg'];
						}
					}
					else if($val == "N"){
						
						// Non LCC Book and Ticket
						
						// LCC Ticket flow
						$this->_Ainput['inputPassengers'] = $_AtempPaxDetails;
						
						$_Obook				= controllerGet::getObject('book',$this);				
						$_AbookResponse		= $_Obook->_doBook();
						
						if(isset($_AbookResponse['status']) && !empty($_AbookResponse['status'])){
							
							if($this->_Ainput['bookingType'] == "TICKET"){
								
								$this->_Ainput['inputPassengers'] 	= array();
								$this->_Ainput['pnr']				= $_AbookResponse['data']['pnr'];
								$this->_Ainput['bookingId']			= $_AbookResponse['data']['bookingId'];
								
								$_Oticket			= controllerGet::getObject('ticket',$this);				
								$_AticketResponse	= $_Oticket->_doTicket();
								
								if(isset($_AticketResponse['status']) && !empty($_AticketResponse['status'])){
									$_ApnrBlockReturn[] = $_AticketResponse['data'];
								}
								else{
									$_Smessage			= $_AticketResponse['msg'];
									$_ApnrBlockReturn[]	= $_AbookResponse['data'];
								}
							}
							else{
								$_ApnrBlockReturn[] = $_AbookResponse['data'];
							}
						}
						else{
							$_Bstatus  = false;
							$_Smessage = $_AbookResponse['msg'];
						}
					}
				}
			}
			else{
				$_Bstatus  = false;
				$_Smessage = 'Insuficient balance in your account';
			}
		}
		else{
			$_Bstatus  = false;
			$_Smessage = 'Update price response not found';
		}
		
		$_IdebitKey		= 0;
		$_AsuccessResp	= array();
		
		foreach($_ApnrBlockReturn as $pnrBlockReturnKey=>$pnrBlockReturnVal){
			
			if(isset($pnrBlockReturnVal['pnr']) && !empty($pnrBlockReturnVal['pnr'])){
				
				$_AsuccessResp[] = 'Y';
				
				$_Bstatus = true;
				$_Bdebit = false;
			
				if($_IdebitKey == 0){
					$_Bdebit = true;
					$_IdebitKey++;
				}
				
				$this->_storeBookingInfo($pnrBlockReturnVal,$_IaccountBookingTotalAmount,$_Bdebit);
			}
		}
		
		if(count($_ApnrBlockReturn) <= 0 || !in_array('Y',$_AsuccessResp)){
			$_Bstatus  = false;
		}
		
		if($_Bstatus && count($_ApnrBlockReturn) > 0 && count($_AflightRefultIndex) != count($_ApnrBlockReturn)){
			$_Bstatus  = true;
			$_Smessage = 'PNR created partialy';
		}
		
		$_Areturn = array
					(
						'status' => $_Bstatus,
						'data'   => $_ApnrBlockReturn,
						'msg'    => $_Smessage,
					);
					
		return $_Areturn;
	}
	
	public function _storeBookingInfo($_AbookingData,$_IdebitAmount,$_Bdebit)
	{
		$_IflightCount		= count($_AbookingData['flightDetails']);
		$_Sorigin			= $_AbookingData['flightDetails'][0]['departureStation'];
		$_Sdestination		= $_AbookingData['flightDetails'][0]['arrivalStation'];
		$_SdepartureDate	= $_AbookingData['flightDetails'][0]['departureDate'];
		$_Spnr				= $_AbookingData['pnr'];
		$_SuniversalPnr		= $_AbookingData['bookingId'];
		$_Imarkup			= 0;
		$_IdefaultMarkup	= 0;
		$_SmarkupInfo		= 0;
		$_Ssource			= $_AbookingData['source'];
		
		$_IbookingStatus	= $_AbookingData['bookingStatusNo'];
		$_IticketStatus		= $_AbookingData['ticketStatus'];
		
		if($this->_Ainput['bookingType'] == "TICKET"){
			$_IbookingStatus = 1;
		}
		
		if($this->_Ainput['bookingType'] == "HOLD"){
			$_IbookingStatus = 2;
		}
		
		$_DholdDeadLineDate	= isset($_AbookingData['holdDeadLineDate']) ? $_AbookingData['holdDeadLineDate'] : "0000-00-00 00:00:00";
		
		if(isset($this->_Ainput['markupData']) && count($this->_Ainput['markupData']) > 0){
			$_Imarkup			= $this->_Ainput['markupData']['userTotalMarkup'];
			$_IdefaultMarkup	= $this->_Ainput['markupData']['markupValue'];
			$_SmarkupInfo		= serialize($this->_Ainput['markupData']);
		}
		
		if($_IflightCount == 1){
			$_ItripType = 1;
		}
		if($_IflightCount == 2){
			$_ItripType = 2;
		}
		else if($_IflightCount > 2){
			$_ItripType		= 3;
			$_Sdestination	= $_AbookingData['flightDetails'][$_IflightCount-1]['arrivalStation'];
		}
		
		$sqlInsertBookingDetails = "INSERT INTO booking_details
									 (
										booking_details_id,
										trace_id,
										account_id,
										api_id,
										api_mode,
										api_provider,
										trip_type,
										origin,
										destination,
										departure_date,
										adult,
										child,
										infant,
										universal_pnr,
										airline_pnr,
										pnr,
										booking_status,
										booking_response,
										booking_response_message,
										ticket_status,
										ticket_respnse,
										ticket_respnse_message,
										booking_deadline_date,
										currency,
										exchange_rate,
										markup,
										markup_info,
										discount,
										base_fare,
										tax,
										fees,
										baggage_fare,
										meals_fare,
										seat_fare,
										connect_fare,
										other_charges,
										total_fare,
										default_currency,
										default_exchange_rate,
										default_markup,
										default_discount,
										default_base_fare,
										default_tax,
										default_fees,
										default_baggage_fare,
										default_meals_fare,
										default_seat_fare,
										default_connect_fare,
										default_other_charges,
										default_total_fare,
										api_currency,
										api_exchange_rate,
										api_base_fare,
										api_tax,
										api_fees,
										api_baggage_fare,
										api_meals_fare,
										api_seat_fare,
										api_connect_fare,
										api_other_charges,
										api_total_fare,
										created_date
									 )
									 VALUES
									 (
										0,
										'".$this->_Ainput['referenceId']."',
										'".$GLOBALS['CONF']['userSettings']['accountId']."',
										'".$GLOBALS['CONF']['site']['apiId']."',
										'".$GLOBALS['CONF']['userSettings']['mode']."',
										'".$_Ssource."',
										'".$_ItripType."',
										'".$_Sorigin."',
										'".$_Sdestination."',
										'".$_SdepartureDate."',
										'".$this->_Ainput['ADT']."',
										'".$this->_Ainput['CHD']."',
										'".$this->_Ainput['INF']."',
										'".$_SuniversalPnr."',
										'".$_Spnr."',
										'".$_Spnr."',
										'".$_IbookingStatus."',
										'',
										'',
										'".$_IticketStatus."',
										'',
										'',
										'".$_DholdDeadLineDate."',
										'".$_AbookingData['userCurrency']."',
										'".$_AbookingData['userExchangeRate']."',
										'".$_Imarkup."',
										'".$_SmarkupInfo."',
										'0',
										'".$_AbookingData['totalBaseFare']."',
										'".$_AbookingData['totalTax']."',
										'".$_AbookingData['totalFees']."',
										'".$_AbookingData['totalBaggageFare']."',
										'".$_AbookingData['totalMealsFare']."',
										'".$_AbookingData['totalSeatFare']."',
										'0',
										'0',
										'".$_AbookingData['totalFare']."',
										'".$_AbookingData['defaultCurrency']."',
										'1',
										'".$_IdefaultMarkup."',
										'0',
										'".$_AbookingData['default']['totalBaseFare']."',
										'".$_AbookingData['default']['totalTax']."',
										'".$_AbookingData['default']['totalFees']."',
										'".$_AbookingData['default']['totalBaggageFare']."',
										'".$_AbookingData['default']['totalMealsFare']."',
										'".$_AbookingData['default']['totalSeatFare']."',
										'0',
										'0',
										'".$_AbookingData['default']['totalFare']."',
										'".$_AbookingData['apiCurrency']."',
										'".$_AbookingData['apiExchangeRate']."',
										'".$_AbookingData['api']['totalBaseFare']."',
										'".$_AbookingData['api']['totalTax']."',
										'".$_AbookingData['api']['totalFees']."',
										'".$_AbookingData['api']['totalBaggageFare']."',
										'".$_AbookingData['api']['totalMealsFare']."',
										'".$_AbookingData['api']['totalSeatFare']."',
										'0',
										'0',
										'".$_AbookingData['api']['totalFare']."',
										NOW()
									 )";
									 
		$_IbookingDetailsId = $this->_Odb->executeQuery($sqlInsertBookingDetails);
		
		if($_Bdebit == true){
			
			$sqlCreditDebit = "INSERT INTO account_credit_debit 
							(
								account_id, 
								booking_id,
								trancation_amount, 
								transaction_type,
								mode,
								transaction_ref_no,
								transaction_currency, 
								exchange_rate, 
								last_update_date
							)
							VALUES 
							(
								'".$GLOBALS['CONF']['userSettings']['accountId']."', 
								'".$_IbookingDetailsId."',
								'".$_IdebitAmount."', 
								'debit',
								'".$GLOBALS['CONF']['userSettings']['mode']."',
								'".$_Spnr."',
								'".$this->_Ainput['accountCurrency']."', 
								'".$this->_Ainput['accountExchangeRate']."', 
								NOW()
							)";
						
			$_IcreditDebitId = $this->_Odb->executeQuery($sqlCreditDebit);
			
			$sqlUpdateAvblBalanace = "UPDATE account_details SET available_balance = available_balance-".$_IdebitAmount.", last_updated_date=NOW() WHERE account_id = '".$GLOBALS['CONF']['userSettings']['accountId']."'";
			
			$this->_Odb->executeQuery($sqlUpdateAvblBalanace);
		}
			
		$_AflightIds = array();
		
		foreach($_AbookingData['flightDetails'] as $flightKey=>$flightVal){
			
			$sqlInsertFlightDetails = "INSERT INTO booking_flight_details
										(
											booking_flight_id,
											booking_details_id,
											origin,
											destination,
											departure_date,
											departure_time,
											arrival_date,
											arrival_time,
											stops,
											journey_sell_key
										)
										VALUES
										(
											0,
											'".$_IbookingDetailsId."',
											'".$flightVal['departureStation']."',
											'".$flightVal['arrivalStation']."',
											'".$flightVal['departureDate']."',
											'".$flightVal['departureTime']."',
											'".$flightVal['arrivalDate']."',
											'".$flightVal['arrivalTime']."',
											'".$flightVal['stops']."',
											'".$flightVal['journeySellKey']."'
										)";
										
			$_IbookingFlightId = $this->_Odb->executeQuery($sqlInsertFlightDetails);
			
			foreach($flightVal['viaFlights'] as $viaFlightKey=>$viaFlightVal){
				
				$viaRef = $viaFlightVal['carrierCode'].$viaFlightVal['flightNumber'].$viaFlightVal['departureStation'].$viaFlightVal['arrivalStation'];
				$viaRef = str_replace("-","",$viaRef);
				$viaRef = str_replace(" ","",$viaRef);
				
				$sqlInsertViaFlightDetails = "INSERT INTO booking_via_flight_details
											(
												booking_via_flight_id,
												booking_flight_id,
												origin,
												destination,
												carrier_code,
												flight_number,
												departure_date,
												departure_time,
												arrival_date,
												arrival_time,
												class_of_service,
												class_type,
												fare_basis_code,
												product_class,
												segment_sell_key
											)
											VALUES
											(
												0,
												'".$_IbookingFlightId."',
												'".$viaFlightVal['departureStation']."',
												'".$viaFlightVal['arrivalStation']."',
												'".$viaFlightVal['carrierCode']."',
												'".$viaFlightVal['flightNumber']."',
												'".$viaFlightVal['departureDate']."',
												'".$viaFlightVal['departureTime']."',
												'".$viaFlightVal['arrivalDate']."',
												'".$viaFlightVal['arrivalTime']."',
												'".$viaFlightVal['classOfService']."',
												'".$viaFlightVal['fareClassOfService']."',
												'".$viaFlightVal['fareBasisCode']."',
												'".$viaFlightVal['classType']."',
												'".$viaFlightVal['segmentSellKey']."'
											)";
											
				$_IbookingViaFlightId = $this->_Odb->executeQuery($sqlInsertViaFlightDetails);
				
				$_AflightIds[$viaRef] = $_IbookingFlightId."_".$_IbookingViaFlightId;
			}
		}
		
		foreach($_AbookingData['paxDetails'] as $paxKey=>$paxVal){
			
			$_SpaxType		= 1;
			$_SticketNumber	= $paxVal['ticketNumber'];
			
			if($paxVal['paxType'] == "ADT"){
				$_SpaxType = 1;
			}
			else if($paxVal['paxType'] == "CHD"){
				$_SpaxType = 2;
			}
			else if($paxVal['paxType'] == "INF"){
				$_SpaxType = 3;
			}
			
			$_Igender = 1;
			
			if($paxVal['gender'] == "Male"){
				$_Igender = 1;
			}
			else if($paxVal['gender'] == "Female"){
				$_Igender = 2;
			}
			
			$_SpasportNumber			= $paxVal['passportNo'];
			$_SpassportIssuedCountry	= '';
			$_SpassportExpiryDate		= $paxVal['passportExpiry'];
			$_SpassportIssueDate		= '0000-00-00';
			$_SpassportPlaceOfIssue		= '';
			
			$sqlInsertPax = "INSERT INTO booking_passenger_details
							(
								passenger_details_id,
								booking_details_id,
								first_name,
								last_name,
								title,
								dob,
								pax_type,
								passport_number,
								passport_issued_country,
								passport_expiry_date,
								gender,
								passport_issue_date,
								passport_place_of_issue,
								ticket_number,
								status
							)
							VALUES
							(
								0,
								'".$_IbookingDetailsId."',
								'".$paxVal['firstName']."',
								'".$paxVal['lastName']."',
								'".$paxVal['title']."',
								'".$paxVal['dob']."',
								'".$_SpaxType."',
								'".$_SpasportNumber."',
								'".$_SpassportIssuedCountry."',
								'".$_SpassportExpiryDate."',
								'".$_Igender."',
								'".$_SpassportIssueDate."',
								'".$_SpassportPlaceOfIssue."',
								'".$_SticketNumber."',
								1
							)";
							
			$_IpassengerDetailsId = $this->_Odb->executeQuery($sqlInsertPax);
			
			if(isset($paxVal['ssr']) && count($paxVal['ssr']) > 0){
				
				foreach($paxVal['ssr'] as $paxSsrKey=>$paxSsrVal){
					
					$_SflightReferenceTemp = $paxSsrVal['carrierCode'].$paxSsrVal['flightNumber'].$paxSsrVal['origin'].$paxSsrVal['destination'];
					
					$_SflightId = isset($_AflightIds[$_SflightReferenceTemp]) ? $_AflightIds[$_SflightReferenceTemp] : "0_0";
					$_AflightId = explode("_",$_SflightId);
					
					$sqlInsertBookingSsr = "INSERT INTO booking_ssr_details
											(
												ssr_details_id,
												passenger_details_id,
												booking_flight_id,
												booking_via_flight_id,
												ssr_code,
												ssr_name,
												amount,
												default_amount,
												api_amount,
												flight_reference
											)
											VALUES
											(
												0,
												'".$_IpassengerDetailsId."',
												'".$_AflightId[0]."',
												'".$_AflightId[1]."',
												'".$paxSsrVal['code']."',
												'".$paxSsrVal['description']."',
												'".$paxSsrVal['amount']."',
												'".$paxSsrVal['defaultAmount']."',
												'".$paxSsrVal['apiAmount']."',
												'".$_SflightReferenceTemp."'
											)";
											
					$this->_Odb->executeQuery($sqlInsertBookingSsr);
				}
			}
		}
	}
}
?>