<?php
/**
	@File Name 		:	fareQuoteXml.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	fareQuoteXml input
*/

function fareQuoteXml($thisObj)
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
 
	//$_Ausernme  $_Apassword $_APseudoCityCode $_AWSAP $CountryCode $CountryCode $AgencyId $IataNumber $soapAction $url
// 	echo "<pre>";
// print_r($thisObj);
// exit;
	// $_Ainput 						= array();
		
	// $_Ainput['EndUserIp'] 			= $thisObj->_Oconf['site']['endUserIp'];
	// $_Ainput['TokenId'] 			= $thisObj->_Ssignature;
	// $_Ainput['TraceId'] 			= $thisObj->_Ainput['flightTraceId'];
	// $_Ainput['ResultIndex'] 		= $thisObj->_Ainput['flightResultIndex'];
	
	// return json_encode($_Ainput);
	$raw_pass 	= $_Apassword;


	$msg_id           = $thisObj->generate_uuid();
$timestamp        = $thisObj->timeStampHeader();
$nonce            = $thisObj->generate_nonce();
$encoded_nonce    = $thisObj->generate_encode_nonce($nonce);
$passwordDigest   = $thisObj->generate_pass($timestamp, $nonce, $raw_pass);
$_Ainput 						= $thisObj->_Ainput;
/* rubesh
$date_in=date('Y-m-d');
$requestTime= date('Y-m-d\TH:i:s'.substr((string)microtime(), 1, 4).'\Z',strtotime($date_in)); */



//$url 		= 'https://nodeA1.test.webservices.amadeus.com/1ASIWNDCSQ';
//$soapAction = 'http://webservices.amadeus.com/NDC_OfferPrice_18.1';

$totalpaxCnt = $_Ainput['ADT']+$_Ainput['CHD'];//+$_Ainput['INF'];
$_Ainput['Paxes'] = array();

//Total
for($i=1;$i<=$totalpaxCnt;$i++)
{
	$_Ainput['Paxes'][] = 'PAX'.$i;
}

for($i=1;$i<=$_Ainput['INF'];$i++)
{
	$_Ainput['Paxes'][] = 'PAX'.$i.'1';
}







$_Ainput['paxDetails']=array();

$adultArray = array();
$adultArray['paxType'] 	= 'ADT';
$adultArray['paxCount'] = $_Ainput['ADT'];
$_Ainput['paxDetails'][] = $adultArray;

$childArray = array();
$childArray['paxType'] 	= 'CHD';
$childArray['paxCount'] = $_Ainput['CHD'];
$_Ainput['paxDetails'][] = $childArray;

$infArray = array();
$infArray['paxType'] 	= 'INF';
$infArray['paxCount'] = $_Ainput['INF'];
$_Ainput['paxDetails'][] = $infArray;



//$_Ausernme  $_Apassword $_APseudoCityCode $_AWSAP $CountryCode $CountryCode $AgencyId $IataNumber $soapAction $url

$xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sec="http://xml.amadeus.com/2010/06/Security_v1" xmlns:link="http://wsdl.amadeus.com/2010/06/ws/Link_v1" xmlns:ses="http://xml.amadeus.com/2010/06/Session_v3" xmlns:ns="http://www.iata.org/IATA/2015/00/2018.1/AirShoppingRQ">
<soapenv:Header xmlns:wsa="http://www.w3.org/2005/08/addressing">

	 <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$msg_id.'</add:MessageID>
	 <wsa:Action>'.$soapAction.'</wsa:Action>
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
	<OfferPriceRQ xmlns="http://www.iata.org/IATA/2015/00/2018.1/OfferPriceRQ">
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

	$xml.='<Request>
			<DataLists>
				<PaxList>';
				$_paxes =$_Ainput['paxDetails'];
				
					foreach ($_paxes as $key => $value) {
						$paxcount=$value['paxCount'];
						 for($i=2;$i<=($paxcount+1);$i++){
						   $xml .='<Pax>';
						   if($value["paxType"]=='CHD')
							   $i=3;
						   $xml .='<PaxID>PAX'.$i.'</PaxID>';
						   $xml .='<PTC>'.$value["paxType"].'</PTC>';
						   $xml .='</Pax>';
						 }
					 }	
				$xml.='</PaxList>
			</DataLists>
		<PricedOffer>
				<SelectedOffer>
					<OfferRefID>'.$_Ainput["OfferID"].'</OfferRefID>
					<OwnerCode>'.$_Ainput["OwnerCode"].'</OwnerCode>
					<ShoppingResponseRefID>'.$_Ainput["ShoppingResponseID"].'</ShoppingResponseRefID>';

			$paxes=$_Ainput['Paxes'];
			$offeritemid=$_Ainput['OfferItemID'];
			
			if(!is_array($offeritemid)){
				
				$offeritemid = array($offeritemid);
			}
			/* foreach ($paxes as $_Pxkey => $_Pxvalue) {

				if(!isset($offeritemid[$_Pxkey])){
					$offeritemid[$_Pxkey] = $offeritemid[0];
				}


				$xml .='<SelectedOfferItem>
				<OfferItemRefID>'.$offeritemid[$_Pxkey].'</OfferItemRefID>
				<PaxRefID>'.$_Pxvalue.'</PaxRefID>';
				//$xml .='<Service><ServiceID>SRV1</ServiceID></Service>';

				
				if(isset($_Ainput["extraBaggageQuantityPaxWise"])){
					if(isset($_Ainput["extraBaggageQuantityPaxWise"][$_Pxkey])){
					// Case of any extra baggage booking		
					$xml .='<SelectedALaCarteOfferItem>
							<Qty>'.$_Ainput["extraBaggageQuantityPaxWise"][$_Pxkey].'</Qty>
						</SelectedALaCarteOfferItem>';
					
					}
				}
				$xml .='</SelectedOfferItem>';
			} */

			foreach ($_Ainput["extraBaggagePaxWise"] as $_PxBaggagekey => $_PxBaggagevalue) {

				
				$xml .='<SelectedOfferItem>
				<OfferItemRefID>'.$_Ainput["extraBaggageServiceWise"][$_PxBaggagekey].'</OfferItemRefID>
				<PaxRefID>'.$_Ainput["extraBaggagePaxWise"][$_PxBaggagekey].'</PaxRefID>';
								
				if(isset($_Ainput["extraBaggageQuantityPaxWise"])){
					if(isset($_Ainput["extraBaggageQuantityPaxWise"][$_PxBaggagekey])){
					// Case of any extra baggage booking		
					$xml .='<SelectedALaCarteOfferItem>
							<Qty>'.$_Ainput["extraBaggageQuantityPaxWise"][$_PxBaggagekey].'</Qty>
						</SelectedALaCarteOfferItem>';
					
					}
				}
				$xml .='</SelectedOfferItem>';
			}


			


				$xml .='</SelectedOffer>
			</PricedOffer>
		</Request>
	</OfferPriceRQ>
</soapenv:Body>
</soapenv:Envelope>';
// echo $xml;exit;
return $thisObj->formatXML($xml);
}
?>