<?php
/**
	@File Name 		:	cancelBookingXml.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	cancelBookingXml input
*/

function cancelBookingXml($thisObj)
{
	$_Ainput 						= $thisObj->_Ainput;

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

	   
	$msg_id           = $thisObj->generate_uuid();
    $timestamp        = $thisObj->timeStampHeader();
    $nonce            = $thisObj->generate_nonce();
    $encoded_nonce    = $thisObj->generate_encode_nonce($nonce);
    $passwordDigest   = $thisObj->generate_pass($timestamp, $nonce, $raw_pass);
    $date_in=date('Y-m-d');
    $requestTime= date('Y-m-d\TH:i:s'.substr((string)microtime(), 1, 4).'\Z',strtotime($date_in));
    
    //$soapAction = 'http://webservices.amadeus.com/NDC_OrderCancel_18.1';
    //$url 		= 'https://nodea1.test.webservices.amadeus.com/1ASIWCLCSQ';
	
	$xml ='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sec="http://xml.amadeus.com/2010/06/Security_v1" xmlns:link="http://wsdl.amadeus.com/2010/06/ws/Link_v1" xmlns:ses="http://xml.amadeus.com/2010/06/Session_v3" xmlns:ns="http://www.iata.org/IATA/2015/00/2018.1/OrderCancelRQ">
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
        <OrderCancelRQ xmlns="http://www.iata.org/IATA/2015/00/2018.1/OrderCancelRQ">
            <PayloadAttributes>
                <Version>18.1</Version>
            </PayloadAttributes>
           
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
   //   <ExpectedRefundAmount><TotalAmount CurCode="SGD">'.$_Ainput["RefundAmount"].'</TotalAmount></ExpectedRefundAmount>
            $xml .='<Request>
                <Order>
                    <OrderID>'.$_Ainput["orderId"].'</OrderID>
                    <OwnerCode>'.$_Ainput["carrierCode"].'</OwnerCode>
                </Order>';
            if($_Ainput["RefundAmount"]>0){ 
               $xml .='<ExpectedRefundAmount>
              <TotalAmount CurCode="INR">'.$_Ainput["RefundAmount"].'</TotalAmount>
              </ExpectedRefundAmount>';
            }
              
         $xml .='</Request>
        </OrderCancelRQ>
    </soapenv:Body>
</soapenv:Envelope>';
return $thisObj->formatXML($xml);
}
?>