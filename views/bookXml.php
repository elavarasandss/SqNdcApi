<?php
/**
	@File Name 		:	bookXml.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	bookXml input
*/

function bookXml($thisObj)
{
	$_Ainput 				= array();
		
	$_Ainput['EndUserIp'] 	= $thisObj->_Oconf['site']['endUserIp'];
	$_Ainput['TokenId'] 	= $thisObj->_Ssignature;
	$_Ainput['TraceId'] 	= $thisObj->_Ainput['flightTraceId'];
	$_Ainput['ResultIndex'] = $thisObj->_Ainput['flightResultIndex'];
	
	if(isset($thisObj->_Ainput['inputPassengers']) && count($thisObj->_Ainput['inputPassengers']) > 0){
		
		$_Ainput['Passengers'] 	= array();
		
		foreach($thisObj->_Ainput['inputPassengers'] as $paxKey=>$paxVal){
		
			$_IpaxType = 0;
			
			if($paxVal['paxType'] == 'ADT'){
				$_IpaxType = 1;
			}
			else if($paxVal['paxType'] == 'CHD'){
				$_IpaxType = 2;
			}
			else if($paxVal['paxType'] == 'INF'){
				$_IpaxType = 3;
			}
			
			$_Igender = 1;
			
			if($paxVal['gender'] == 'Male'){
				$_Igender = 1;
			}
			else if($paxVal['gender'] == 'Female'){
				$_Igender = 2;
			}
			
			$_IisLead = 0;
			
			if($_IisLead == 0 && $_IpaxType == 1){
				$_IisLead = 1;
			}
			
			if(isset($paxVal['passportDetails']['passportNumber']) && !empty($paxVal['passportDetails']['passportNumber'])){
				$paxVal['passportNo']		= $paxVal['passportDetails']['passportNumber'];
				$paxVal['passportExpiry']	= $paxVal['passportDetails']['expiryDate'];
			}
			
			if(!isset($paxVal['passportNo']) || empty($paxVal['passportNo'])){
				$paxVal['passportNo']		= "";
				$paxVal['passportExpiry']	= "";
			}
			
			$_Ainput['Passengers'][$paxKey]['Title'] 				= $paxVal['title'];
			$_Ainput['Passengers'][$paxKey]['FirstName'] 			= $paxVal['firstName'];
			$_Ainput['Passengers'][$paxKey]['LastName'] 			= $paxVal['lastName'];
			$_Ainput['Passengers'][$paxKey]['PaxType'] 				= $_IpaxType;
			$_Ainput['Passengers'][$paxKey]['DateOfBirth'] 			= $paxVal['dob'];
			$_Ainput['Passengers'][$paxKey]['Gender'] 				= $_Igender;
			$_Ainput['Passengers'][$paxKey]['PassportNo'] 			= $paxVal['passportNo'];
			$_Ainput['Passengers'][$paxKey]['PassportExpiry'] 		= $paxVal['passportExpiry'];
			$_Ainput['Passengers'][$paxKey]['AddressLine1'] 		= $thisObj->_Ainput['agentAddr1'];
			$_Ainput['Passengers'][$paxKey]['AddressLine2'] 		= $thisObj->_Ainput['agentAddr2'];
			$_Ainput['Passengers'][$paxKey]['City'] 				= $thisObj->_Ainput['agentCity'];
			$_Ainput['Passengers'][$paxKey]['CountryCode'] 			= $paxVal['nationality'];
			$_Ainput['Passengers'][$paxKey]['CountryName'] 			= $paxVal['nationalityName'];
			$_Ainput['Passengers'][$paxKey]['ContactNo'] 			= $thisObj->_Ainput['agentPhone'];
			$_Ainput['Passengers'][$paxKey]['Email'] 				= $thisObj->_Ainput['agentMail'];
			$_Ainput['Passengers'][$paxKey]['IsLeadPax'] 			= $_IisLead;
			$_Ainput['Passengers'][$paxKey]['FFAirline'] 			= "";
			$_Ainput['Passengers'][$paxKey]['FFNumber'] 			= "";
			
			if($thisObj->_Ainput['GSTMandatory']=='Y'){
				
				if($thisObj->_Ainput['gst_regnum']!=''){
					
					$_Ainput['Passengers'][$paxKey]['GSTNumber'] 				= $thisObj->_Ainput['gst_regnum'];
					$_Ainput['Passengers'][$paxKey]['GSTCompanyName'] 			= $thisObj->_Ainput['gst_company'];
					$_Ainput['Passengers'][$paxKey]['GSTCompanyAddress'] 		= $thisObj->_Ainput['gst_addr'];
					$_Ainput['Passengers'][$paxKey]['GSTCompanyEmail'] 			= $thisObj->_Ainput['gst_email'];
					$_Ainput['Passengers'][$paxKey]['GSTCompanyContactNumber'] 	= $thisObj->_Ainput['gst_mob'];
				}
				else{
					
					$_Ainput['Passengers'][$paxKey]['GSTNumber'] 				= $thisObj->_Asettings['GSTField']['GSTNumber'];
					$_Ainput['Passengers'][$paxKey]['GSTCompanyName'] 			= $thisObj->_Asettings['GSTField']['GSTCompanyName'];
					$_Ainput['Passengers'][$paxKey]['GSTCompanyAddress'] 		= $thisObj->_Asettings['GSTField']['GSTCompanyAddress'];
					$_Ainput['Passengers'][$paxKey]['GSTCompanyEmail'] 			= $thisObj->_Asettings['GSTField']['GSTCompanyEmail'];
					$_Ainput['Passengers'][$paxKey]['GSTCompanyContactNumber'] 	= $thisObj->_Asettings['GSTField']['GSTCompanyContactNumber'];
					
				}
			}
					
			$_ApaxFareInfoNew = array();
			
			if(isset($thisObj->_Ainput['paxFareDetails'][$thisObj->_Ainput['flightResultIndex']][$_IpaxType]) && !empty($thisObj->_Ainput['paxFareDetails'][$thisObj->_Ainput['flightResultIndex']][$_IpaxType])){
				
				$_ApaxFareInfo = $thisObj->_Ainput['paxFareDetails'][$thisObj->_Ainput['flightResultIndex']][$_IpaxType];
				
				$_ApaxFareInfoNew['BaseFare'] 				= round(($_ApaxFareInfo['BaseFare'] / $_ApaxFareInfo['PassengerCount']),2);
				$_ApaxFareInfoNew['Tax'] 					= round(($_ApaxFareInfo['Tax'] / $_ApaxFareInfo['PassengerCount']),2);
				$_ApaxFareInfoNew['YQTax'] 					= round(($_ApaxFareInfo['YQTax'] / $_ApaxFareInfo['PassengerCount']),2);
				$_ApaxFareInfoNew['AdditionalTxnFeeOfrd'] 	= round(($_ApaxFareInfo['AdditionalTxnFeeOfrd'] / $_ApaxFareInfo['PassengerCount']),2);
				$_ApaxFareInfoNew['AdditionalTxnFeePub'] 	= round(($_ApaxFareInfo['AdditionalTxnFeeOfrd'] / $_ApaxFareInfo['PassengerCount']),2);
				$_ApaxFareInfoNew['TransactionFee'] 		= round(($_ApaxFareInfo['TransactionFee'] / ($thisObj->_Ainput['ADT']+$thisObj->_Ainput['CHD'])),2);
				$_ApaxFareInfoNew['AirTransFee'] 			= round(($_ApaxFareInfo['AirTransFee'] / ($thisObj->_Ainput['ADT']+$thisObj->_Ainput['CHD'])),2);
			}
			
			if(count($_ApaxFareInfoNew) < 0){
				return '{}';
			}
			
			$_Ainput['Passengers'][$paxKey]['Fare'] = $_ApaxFareInfoNew;
			
			if($paxVal['paxType'] == 'INF' && $_Igender == 1){
				//$_Ainput['Passengers'][$paxKey]['Title'] = "Mr";
			}
			
			if(isset($paxVal['meals']) && !empty($paxVal['meals'])){
				
				$_AmealRequest = array();
				$_Spreference  = false;
				
				foreach($paxVal['meals'] as $paxMealKey=>$paxMealVal){
					
					$_Atemp = array();
					$_Atemp['WayType'] 				= $paxMealVal['wayType'];
					$_Atemp['Code'] 				= $paxMealVal['code'];
					$_Atemp['Description'] 			= $paxMealVal['orgDescription'];
					$_Atemp['AirlineDescription'] 	= $paxMealVal['description'];
					$_Atemp['Quantity'] 			= 1;
					$_Atemp['Price'] 				= 0;
					$_Atemp['Currency'] 			= 'INR';
					$_Atemp['Origin'] 				= $paxMealVal['origin'];
					$_Atemp['Destination'] 			= $paxMealVal['destination'];
					
					if($paxMealVal['preference'] && $paxMealVal['preference'] == "Y"){
						
						$_Spreference			= true;
						$_Atemp					= array();
						$_Atemp['Code']			= $paxMealVal['code'];
						$_Atemp['Description']	= $paxMealVal['orgDescription'];
						
						$_AmealRequest = $_Atemp;
					}
					else{
						$_AmealRequest[] = $_Atemp;
					}
					
				}
				
				if($_Spreference){
					$_Ainput['Passengers'][$paxKey]['Meal'] = $_AmealRequest;
				}
				else{
					$_Ainput['Passengers'][$paxKey]['MealDynamic'] = $_AmealRequest;
				}
			}
			
			if(isset($paxVal['seat']) && !empty($paxVal['seat'])){
				
				$_AseatRequest = array();
				
				foreach($paxVal['seat'] as $paxSeatKey=>$paxSeatVal){
					$paxSeatVal['Price'] = 0;
					$_AseatRequest[] = $paxSeatVal;
				}
				
				$_Ainput['Passengers'][$paxKey]['SeatDynamic'] = $_AseatRequest;
			}
			
			if(isset($paxVal['baggage']) && !empty($paxVal['baggage'])){
				
				$_AbagRequest = array();
				
				foreach($paxVal['baggage'] as $paxBagKey=>$paxBagVal){
					
					$_Atemp = array();
					$_Atemp['WayType'] 				= $paxBagVal['wayType'];
					$_Atemp['Code'] 				= $paxBagVal['code'];
					$_Atemp['Description'] 			= $paxBagVal['orgDescription'];
					$_Atemp['Quantity'] 			= 1;
					$_Atemp['Price'] 				= 0;
					$_Atemp['Currency'] 			= 'INR';
					$_Atemp['Origin'] 				= $paxBagVal['origin'];
					$_Atemp['Destination'] 			= $paxBagVal['destination'];
					$_Atemp['Weight'] 				= $paxBagVal['weight'];
					
					$_AbagRequest[] = $_Atemp;
				}
				
				$_Ainput['Passengers'][$paxKey]['Baggage'] = $_AbagRequest;
			}
		}
	}
	
	if(isset($thisObj->_Ainput['pnr']) && !empty($thisObj->_Ainput['pnr'])){
		//$_Ainput['PNR'] = $thisObj->_Ainput['pnr'];
	}
	
	if(isset($thisObj->_Ainput['bookingId']) && !empty($thisObj->_Ainput['bookingId'])){
		//$_Ainput['BookingId'] = $thisObj->_Ainput['bookingId'];
	}
	
	return json_encode($_Ainput);
}
?>