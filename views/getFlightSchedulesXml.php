<?php
/**
	@File Name 		:	getFlightSchedulesXml.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	getFlightSchedulesXml input
*/

function getFlightSchedulesXml($thisObj)
{
	
	//echo "<pre>";print_r($thisObj->_Asettings['apiCredentials']['userName']);die;

   $_Ausernme        = $thisObj->_Asettings['apiCredentials']['userName'];
   $_Apassword       = $thisObj->_Asettings['apiCredentials']['password'];
   $_APseudoCityCode = $thisObj->_Asettings['apiCredentials']['PseudoCityCode'];
   $_AWSAP           = $thisObj->_Asettings['apiCredentials']['WSAP'];
   $CountryCode      = $thisObj->_Asettings['apiCredentials']['CountryCode'];
   $AgencyId         = $thisObj->_Asettings['apiCredentials']['AgencyId']; //AgencyId	 IataNumber
   $IataNumber       = $thisObj->_Asettings['apiCredentials']['IataNumber']; //AgencyId	 IataNumber
   $soapAction       = $thisObj->_Asettings['actionInfo']['soapAction'];
   $url              = $thisObj->_SrequestUrl;
   $raw_pass 	      = $_Apassword;

	/* $_IjourneyType = 0;
	
	if(count($thisObj->_Ainput['sectorInfo']) == 1){
		$_IjourneyType = 1;
	}
	else if(count($thisObj->_Ainput['sectorInfo']) == 2){
		$_IjourneyType = 2;
		
		if($thisObj->_Ainput['sectorInfo'][0]['origin'] != $thisObj->_Ainput['sectorInfo'][1]['destination']){
			$_IjourneyType = 3;
		}
		
		if(isset($thisObj->_Ainput['domesticReturn']) && $thisObj->_Ainput['domesticReturn'] == "Y"){
			$_IjourneyType = 2;
		}
		
		if(isset($thisObj->_Ainput['specialReturn']) && $thisObj->_Ainput['specialReturn'] == "Y"){
			$_IjourneyType = 5;
		}
		
	}
	else if(count($thisObj->_Ainput['sectorInfo']) > 2){
		$_IjourneyType = 3;
	}
	
	if(isset($thisObj->_Ainput['advancedSearch']) && $thisObj->_Ainput['advancedSearch'] == "Y"){
		$_IjourneyType = 4;
	}
	
	$_Ainput['EndUserIp'] 			= $thisObj->_Oconf['site']['endUserIp'];
	$_Ainput['TokenId'] 			= $thisObj->_Ssignature;
	$_Ainput['AdultCount'] 			= $thisObj->_Ainput['ADT'];
	$_Ainput['ChildCount'] 			= $thisObj->_Ainput['CHD'];
	$_Ainput['InfantCount'] 		= $thisObj->_Ainput['INF'];
	$_Ainput['DirectFlight'] 		= false;
	$_Ainput['OneStopFlight'] 		= false;
	$_Ainput['JourneyType'] 		= $_IjourneyType;
	$_Ainput['PreferredAirlines'] 	= isset($thisObj->_Ainput['preferredAirlines']) ? $thisObj->_Ainput['preferredAirlines'] : array();
	$_Ainput['Sources'] 			= $thisObj->_Oconf['site']['allowedSources'];
	$_Ainput['Segments'] 			= array();
	
	if(isset($thisObj->_Ainput['specialReturn']) && $thisObj->_Ainput['specialReturn'] == "Y"){
		$_Ainput['Sources'] = array('SG','6E');
		//$_Ainput['Sources'] = array('GDS','G8');
	}
	
	if($_IjourneyType == 3){
		$_Ainput['Sources'] = array('GDS');
	}
	
	if(isset($thisObj->_Ainput['calenderSearch']) && $thisObj->_Ainput['calenderSearch'] == "Y"){
		$_Ainput['Sources'] = array('GDS','SG','6E','G8');
	}
	
	foreach($thisObj->_Ainput['sectorInfo'] as $key=>$val){
		$_Ainput['Segments'][$key]['Origin'] 				= $val['origin'];
		$_Ainput['Segments'][$key]['Destination'] 			= $val['destination'];
		$_Ainput['Segments'][$key]['PreferredDepartureTime'] = $val['departureDate']."T00:00:00";
		$_Ainput['Segments'][$key]['PreferredArrivalTime'] 	= $val['departureDate']."T00:00:00";
		$_Ainput['Segments'][$key]['FlightCabinClass'] 		= 1;
	}
	
	#echo "<pre>";print_r($_Ainput);
	
	return json_encode($_Ainput); */
	
	
	
	
   
// $raw_pass 	= 'Ama19Web';




$_Ainput 						= $thisObj->_Ainput;

$cnts=0;
if($_Ainput['ADT']>0){

   $_Ainput['paxDetails'][$cnts]['paxType'] = 'ADT';
   $_Ainput['paxDetails'][$cnts]['paxCount'] = $_Ainput['ADT'];
   $cnts++;
}
if($_Ainput['CHD']>0){

   $_Ainput['paxDetails'][$cnts]['paxType'] = 'CHD';
   $_Ainput['paxDetails'][$cnts]['paxCount'] = $_Ainput['CHD'];
   $cnts++;
}
if($_Ainput['INF']>0){

   $_Ainput['paxDetails'][$cnts]['paxType'] = 'INF';
   $_Ainput['paxDetails'][$cnts]['paxCount'] = $_Ainput['INF'];
   $cnts++;
}


// print_r($_Ainput);
// exit;
$msg_id           = $thisObj->generate_uuid();
$timestamp        = $thisObj->timeStampHeader();
$nonce            = $thisObj->generate_nonce();
$encoded_nonce    = $thisObj->generate_encode_nonce($nonce);
$passwordDigest   = $thisObj->generate_pass($timestamp, $nonce, $raw_pass);
$date_in=date('Y-m-d');
$requestTime= date('Y-m-d\TH:i:s'.substr((string)microtime(), 1, 4).'\Z',strtotime($date_in));




//$soapAction = 'http://webservices.amadeus.com/NDC_AirShopping_18.1';
//$url 		= 'https://nodea1.test.webservices.amadeus.com/1ASIWNDCSQ';


$xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sec="http://xml.amadeus.com/2010/06/Security_v1" xmlns:link="http://wsdl.amadeus.com/2010/06/ws/Link_v1" xmlns:ses="http://xml.amadeus.com/2010/06/Session_v3" xmlns:ns="http://www.iata.org/IATA/2015/00/2018.1/AirShoppingRQ">
   <soapenv:Header xmlns:wsa="http://www.w3.org/2005/08/addressing">
   
		<add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$msg_id.'</add:MessageID>
		<wsa:Action>'.$soapAction .'</wsa:Action>
		<add:To xmlns:add="http://www.w3.org/2005/08/addressing">'.$url.'</add:To>
		
      <sec:AMA_SecurityHostedUser>
         <sec:UserID POS_Type="1" RequestorType="U" PseudoCityCode="'.$_APseudoCityCode.'" AgentDutyCode="SU">
            <typ:RequestorID xmlns:typ="http://xml.amadeus.com/2010/06/Types_v1" xmlns:iat="http://www.iata.org/IATA/2007/00/IATA2010.1">
               <iat:CompanyName>SQ</iat:CompanyName>
            </typ:RequestorID>
         </sec:UserID>
      </sec:AMA_SecurityHostedUser>
      <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
         <wsse:UsernameToken>
            <wsse:Username>'.$_Ausernme.'</wsse:Username>
            <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest">'.$passwordDigest.'</wsse:Password>
            <wsse:Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">'.$encoded_nonce.'</wsse:Nonce>
            <wsu:Created>'.$timestamp.'</wsu:Created>
         </wsse:UsernameToken>
      </wsse:Security>
   </soapenv:Header>';
   $xml .='<soapenv:Body>
      <AirShoppingRQ xmlns="http://www.iata.org/IATA/2015/00/2018.1/AirShoppingRQ">
         <PointOfSale>
            <Country>
               <CountryCode>'.$CountryCode.'</CountryCode>
            </Country>
         </PointOfSale>
         <Party>
            <Recipient>
               <ORA>
                  <AirlineDesigCode>SQ</AirlineDesigCode>
               </ORA>
            </Recipient>
           
            <Sender>
               <TravelAgency>
                  <AgencyID>'.$AgencyId.'</AgencyID> 	 
                  <IATA_Number>'.$IataNumber.'</IATA_Number>
               </TravelAgency>
            </Sender>
         </Party>';
         
         //Below recipant details no need participant details

         /* <Participant>
               <Aggregator>
                  <AggregatorID>GEN</AggregatorID>
               </Aggregator>
            </Participant> */
      
      $xml .='<Request>';
   $sectorInfo =$_Ainput['sectorInfo'];
      $xml .='<FlightRequest>';
               foreach($sectorInfo as $value){
                  $xml .='<OriginDestRequest>';
                  $xml .='<DestArrivalRequest>';
                  $xml .=' <IATA_LocationCode>'.$value["destination"].'</IATA_LocationCode>';
                  $xml .='</DestArrivalRequest>';
                  $xml .='<OriginDepRequest>';
                   $xml .=' <IATA_LocationCode>'.$value["origin"].'</IATA_LocationCode>';
                   $xml .='<Date>'.$value["departureDate"].'</Date>';
                   $xml .='</OriginDepRequest>';
                   $xml .=' </OriginDestRequest>';
              }
            $xml .='</FlightRequest>';

             

         $xml .='<Paxs>';
         $_paxes =$_Ainput['paxDetails'];
         foreach ($_paxes as $key => $value) {
            $paxcount=$value['paxCount'];
             for($i=1;$i<=$paxcount;$i++){
               $xml .='<Pax>';
               $xml .='<PaxID>'.$value["paxType"].'</PaxID>';
               $xml .='<PTC>'.$value["paxType"].'</PTC>';
               $xml .='</Pax>';
             }
         }
		 
         $xml .='</Paxs>';

         $cabinType = 'ECO';
         if($_Ainput["classType"]=='Economy'){
            
            $cabinType = 'ECO';
         }else{
             $cabinType = 'BUS';
         }
         $xml .='<ShoppingCriteria>
               <CabinTypeCriteria>
                  <CabinTypeName>'.$cabinType.'</CabinTypeName>
               </CabinTypeCriteria>
            </ShoppingCriteria>';
         $xml .='</Request>
      </AirShoppingRQ>
   </soapenv:Body>
</soapenv:Envelope>';
return $thisObj->formatXML($xml);


}
?>