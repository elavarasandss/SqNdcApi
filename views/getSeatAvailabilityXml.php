<?php
/**
	@File Name 		:	getFlightSchedulesXml.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	getFlightSchedulesXml input
*/

function getSeatAvailabilityXml($thisObj)
{

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



$_Ainput 						= $thisObj->_Ainput;
// print_r($_Ainput);
// exit;
$msg_id           = $thisObj->generate_uuid();
$timestamp        = $thisObj->timeStampHeader();
$nonce            = $thisObj->generate_nonce();
$encoded_nonce    = $thisObj->generate_encode_nonce($nonce);
$passwordDigest   = $thisObj->generate_pass($timestamp, $nonce, $raw_pass);
$date_in=date('Y-m-d');
$requestTime= date('Y-m-d\TH:i:s'.substr((string)microtime(), 1, 4).'\Z',strtotime($date_in));

//$soapAction = 'http://webservices.amadeus.com/NDC_SeatAvailability_18.1';
//$url 		= 'https://nodea1.test.webservices.amadeus.com/1ASIWCLCSQ';
             

$xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sec="http://xml.amadeus.com/2010/06/Security_v1" xmlns:link="http://wsdl.amadeus.com/2010/06/ws/Link_v1" xmlns:ses="http://xml.amadeus.com/2010/06/Session_v3" xmlns:ns="http://www.iata.org/IATA/2015/00/2018.1/SeatAvailabilityRQ">
<soapenv:Header xmlns:wsa="http://www.w3.org/2005/08/addressing">
   <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$msg_id.'</add:MessageID>
   <wsa:Action>http://webservices.amadeus.com/NDC_SeatAvailability_18.1</wsa:Action>
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
</soapenv:Header>
<soapenv:Body>
   <SeatAvailabilityRQ xmlns="http://www.iata.org/IATA/2015/00/2018.1/SeatAvailabilityRQ">
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
     </Party>
      <Request>
         <CoreRequest>';

         
      if($_Ainput["OfferItemID"]){

      
         $xml .='<Offer>
               <OfferItemID>'.$_Ainput["OfferItemID"].'</OfferItemID>
               <OwnerCode>'.$_Ainput["carrierCode"].'</OwnerCode>
               <PaxRefID>'.$_Ainput["PaxRefID"][0].'</PaxRefID>';
               if($_Ainput["PaxRefID"][1]){
                  $xml .='<PaxRefID>'.$_Ainput["PaxRefID"][1].'</PaxRefID>';
               }

               $xml .='<PaxSegmentRefID>'.$_Ainput["PaxSegmentRefID"].'</PaxSegmentRefID>
            </Offer>';

      } 
      if($_Ainput["OrderID"]){

      
         $xml .='<Order>
                     <OrderID>'.$_Ainput["OrderID"].'</OrderID>
                     <OwnerCode>'.$_Ainput["carrierCode"].'</OwnerCode>
                     <PaxSegmentRefID>'.$_Ainput["PaxSegmentRefID"].'</PaxSegmentRefID>
                  </Order>
                  ';

      }   

      $xml .='</CoreRequest>
         <ResponseParameters>
            <PricingParameter>
               <SimplePricingInd>true</SimplePricingInd>
            </PricingParameter>
         </ResponseParameters>
      </Request>
   </SeatAvailabilityRQ>
</soapenv:Body>
</soapenv:Envelope>';
// echo $xml;exit;
return $thisObj->formatXML($xml);


}
?>