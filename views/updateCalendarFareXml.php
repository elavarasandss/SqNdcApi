<?php
/**
	@File Name 		:	updateCalendarFareXml.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	updateCalendarFareXml input
*/

function updateCalendarFareXml($thisObj)
{
	$_Ainput 						= array();
	
	$_IjourneyType = 0;
	
	if(count($thisObj->_Ainput['sectorInfo']) == 1){
		$_IjourneyType = 1;
	}
	else if(count($thisObj->_Ainput['sectorInfo']) == 2){
		$_IjourneyType = 2;
		
		if($thisObj->_Ainput['sectorInfo'][0]['origin'] != $thisObj->_Ainput['sectorInfo'][1]['destination']){
			//$_IjourneyType = 3;
		}
		
		if(isset($thisObj->_Ainput['domesticReturn']) && $thisObj->_Ainput['domesticReturn'] == "Y"){
			$_IjourneyType = 3;
		}
		
		if(isset($thisObj->_Ainput['specialReturn']) && $thisObj->_Ainput['specialReturn'] == "Y"){
			$_IjourneyType = 5;
		}
		
	}
	else if(count($thisObj->_Ainput['sectorInfo']) > 2){
		$_IjourneyType = 3;
	}
	
	$_Ainput['EndUserIp'] 			= $thisObj->_Oconf['site']['endUserIp'];
	$_Ainput['TokenId'] 			= $thisObj->_Ssignature;
	$_Ainput['JourneyType'] 		= $_IjourneyType;
	$_Ainput['PreferredAirlines'] 	= null;
	$_Ainput['Sources'] 			= $thisObj->_Oconf['site']['allowedSources'];
	$_Ainput['Segments'] 			= array();
	
	foreach($thisObj->_Ainput['sectorInfo'] as $key=>$val){
		$_Ainput['Segments'][$key]['Origin'] 				= $val['origin'];
		$_Ainput['Segments'][$key]['Destination'] 			= $val['destination'];
		$_Ainput['Segments'][$key]['PreferredDepartureTime'] = $val['departureDate']."T00:00:00";
		$_Ainput['Segments'][$key]['FlightCabinClass'] 		= 1;
	}
	
	if(isset($thisObj->_Ainput['calenderSearch']) && $thisObj->_Ainput['calenderSearch'] == "Y"){
		$_Ainput['Sources'] = array('GDS','SG','6E','G8');
	}
	
	return json_encode($_Ainput);
}
?>