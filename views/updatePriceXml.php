<?php
/**
	@File Name 		:	headerXml.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	headerXml input
*/

function updatePriceXml()
{
	$msg_id           = $thisObj->generate_uuid();
$timestamp        = $thisObj->timeStampHeader();
$nonce            = $thisObj->generate_nonce();
$encoded_nonce    = $thisObj->generate_encode_nonce($nonce);
$passwordDigest   = $thisObj->generate_pass($timestamp, $nonce, $raw_pass);
/* rubesh
$date_in=date('Y-m-d');
$requestTime= date('Y-m-d\TH:i:s'.substr((string)microtime(), 1, 4).'\Z',strtotime($date_in)); */

$soapAction = 'http://webservices.amadeus.com/NDC_AirShopping_18.1';

	$xml ='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sec="http://xml.amadeus.com/2010/06/Security_v1" xmlns:link="http://wsdl.amadeus.com/2010/06/ws/Link_v1" xmlns:ses="http://xml.amadeus.com/2010/06/Session_v3" xmlns:ns="http://www.iata.org/IATA/2015/00/2018.1/AirShoppingRQ">
    <soapenv:Header xmlns:wsa="http://www.w3.org/2005/08/addressing">
        <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$msg_id.'</add:MessageID>
        <wsa:Action>http://webservices.amadeus.com/NDC_OfferPrice_18.1</wsa:Action>
        <add:To xmlns:add="http://www.w3.org/2005/08/addressing">https://nodea1.test.webservices.amadeus.com/1ASIWNDCSQ</add:To>
        <sec:AMA_SecurityHostedUser>
            <sec:UserID POS_Type="1" RequestorType="U" PseudoCityCode="SINSQ08NP" AgentDutyCode="SU">
                <typ:RequestorID xmlns:typ="http://xml.amadeus.com/2010/06/Types_v1" xmlns:iat="http://www.iata.org/IATA/2007/00/IATA2010.1">
                    <iat:CompanyName>SQ</iat:CompanyName>
                </typ:RequestorID>
            </sec:UserID>
        </sec:AMA_SecurityHostedUser>
        <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
            <wsse:UsernameToken>
                <wsse:Username>WSSQNDC</wsse:Username>
                <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest">'.$passwordDigest.'</wsse:Password>
                <wsse:Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">'.$encoded_nonce.'</wsse:Nonce>
                <wsu:Created>'.$timestamp.'</wsu:Created>
            </wsse:UsernameToken>
        </wsse:Security>
    </soapenv:Header>
    <soapenv:Body>
        <OfferPriceRQ xmlns="http://www.iata.org/IATA/2015/00/2018.1/OfferPriceRQ">
            <PayloadAttributes>
                <Version>18.1</Version>
            </PayloadAttributes>
            <Party>
                <Sender>
                    <TravelAgency>
                        <AgencyID>SINSQ08NP</AgencyID>
                        <IATA_Number>12345678</IATA_Number>
                    </TravelAgency>
                </Sender>
            </Party>
            <Request>
                <DataLists>
                    <PaxList>
                        <Pax>
                            <PaxID>PAX1</PaxID>
                            <PTC>ADT</PTC>
                        </Pax>
                    </PaxList>
                </DataLists>
                <PricedOffer>
                    <SelectedOffer>
                        <OfferRefID>SP2F-5826965488447062034-1</OfferRefID>
                        <OwnerCode>SQ</OwnerCode>
                        <ShoppingResponseRefID>SP2F-5826965488447062034</ShoppingResponseRefID>
                        <SelectedOfferItem>
                            <OfferItemRefID>SP2F-5826965488447062034-1-1</OfferItemRefID>
                            <PaxRefID>PAX1</PaxRefID>
                        </SelectedOfferItem>
                    </SelectedOffer>
                </PricedOffer>
            </Request>
        </OfferPriceRQ>
    </soapenv:Body>
</soapenv:Envelope>';
}
?>