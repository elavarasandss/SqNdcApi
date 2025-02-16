<?php
/**
	@File Name 		:	getFlightSchedulesXml.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	getFlightSchedulesXml input
*/

function CreateBookingXml($thisObj)
{


    $_Ausernme        = $thisObj->_Asettings['apiCredentials']['userName'];
    $_Apassword       = $thisObj->_Asettings['apiCredentials']['password'];
    $_APseudoCityCode = $thisObj->_Asettings['apiCredentials']['PseudoCityCode'];
    $_AWSAP           = $thisObj->_Asettings['apiCredentials']['WSAP'];
    $CountryCode      = $thisObj->_Asettings['apiCredentials']['CountryCode'];
    $AgencyId         = $thisObj->_Asettings['apiCredentials']['AgencyId']; //AgencyId	 IataNumber
    $IataNumber       = $thisObj->_Asettings['apiCredentials']['IataNumber']; //AgencyId	 IataNumber
    $AgentName        = $thisObj->_Asettings['apiCredentials']['AgentName']; //AgencyId	 IataNumber
    $currencyCode     = $thisObj->_Asettings['apiCredentials']['currencyCode']; //AgencyId	 IataNumber
    $soapAction       = $thisObj->_Asettings['actionInfo']['soapAction'];
    $url              = $thisObj->_SrequestUrl;

    $raw_pass 	=  $_Apassword;



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

//$soapAction = 'http://webservices.amadeus.com/NDC_OrderCreate_18.1';
//$url 		= 'https://nodea1.test.webservices.amadeus.com/1ASIWCLCSQ';
$_paxDetails = $_Ainput['paxDetails'];


$xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sec="http://xml.amadeus.com/2010/06/Security_v1" xmlns:link="http://wsdl.amadeus.com/2010/06/ws/Link_v1" xmlns:ses="http://xml.amadeus.com/2010/06/Session_v3" xmlns:ns="http://www.iata.org/IATA/2015/00/2018.1/OrderCreateRQ">
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
        <OrderCreateRQ xmlns="http://www.iata.org/IATA/2015/00/2018.1/OrderCreateRQ">
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
                <Name>'.$AgentName.'</Name>
           </TravelAgency>
        </Sender>
     </Party>';
            $_paxDetails = $_Ainput['paxDetails'];
            //$_paxDetailsCNT = $_Ainput['ADT']+$_Ainput['CHD']+$_Ainput['INF'];
			$_paxDetailsCNT = count($_paxDetails);
			//echo $_paxDetailsCNT;die;

			$_PaxIds=array();

           
                for($i=1; $i<=$_paxDetailsCNT;$i++) {
                    if( $_paxDetails[$i-1]['paxType']=='ADT' || $_paxDetails[$i-1]['paxType']=='CHD'){
                     $_PaxIds[]="PAX".$i;
                    }else if($_paxDetails[$i-1]['paxType']=='INF'){
                       // $_PaxIds[]="PAX".$i.$i;
                        $_PaxIds[]="PAX11";                     //Altered By Ela
                    }
                } 
           /*  if( $_paxDetails['paxType']=='INF'){
                for($i=1; $i<=$_paxDetailsCNT;$i++) {
                    $_PaxIds[]="PAX".$i.$i;
                }
            } */
            
            
    $xml    .='<Request><CreateOrder>';
            // $_PaxIds = array("PAX1","PAX2","PAX11");
            
    $xml .='<SelectedOffer>
                <OfferID>'.$_Ainput["OfferID"].'</OfferID>
                <OwnerCode>'.$_Ainput["carrierCode"].'</OwnerCode>
               <ShoppingResponseRefID>'.$_Ainput["ShoppingResponseID"].'</ShoppingResponseRefID>
                <SelectedOfferItem>
                <OfferItemID>'.$_Ainput["OfferItemID"].'</OfferItemID>';
                    foreach($_PaxIds as $_id_Value){
                        $xml .='<PaxRefID>'.$_id_Value.'</PaxRefID>';
                    }

    $xml .='</SelectedOfferItem>';
	
/* 	  $xml .='<SelectedOfferItem>
              <OfferItemID>SP2F-143391196670246802-1-3</OfferItemID>
              <PaxRefID>PAX1</PaxRefID>
              <SelectedBundleServices>
                <SelectedServiceID>SRV2</SelectedServiceID>
              </SelectedBundleServices>
            </SelectedOfferItem>'; */
			
	 $_AssrArray = '';
	foreach($_Ainput['flightDetails'] as $_AflightKey => $_AflightVal){
		
		foreach($_AflightVal['viaFlights'] as $_AviaKey => $_AviaVal){
			
			if(is_array($_AviaVal['ssrDetails'])){
				foreach($_AviaVal['ssrDetails'] as $_AssrKey => $_AssrVal){
					
				$xml .='<SelectedOfferItem>
					<OfferItemID>'.$_AssrVal["OfferItemID"].'</OfferItemID>';
					$xml .='<PaxRefID>'.$_AssrVal["PaxRefID"].'</PaxRefID>';
						
				// Services if any
				$xml .=' <SelectedBundleServices>
							<SelectedServiceID>'.$_AssrVal['codeType'].'</SelectedServiceID>
						  </SelectedBundleServices>';
				$xml .='</SelectedOfferItem>';
				
				
				  
				}
				
			}
			
		}
		
	}
	
	 $xml .='</SelectedOffer>';
	
				/* $xml .=' <SelectedBundleServices>
							<SelectedServiceID>SRV44</SelectedServiceID>
						  </SelectedBundleServices>'; */
        
        // SEAT BOOKING
    //     $xml .='<CreateOrderItem>
    //     <OfferItemID Owner="6X">PRICE1-SEG3</OfferItemID>
    //     <OfferItemType>
    //     <SeatItem>
    //     <Location>
    //     <Column>B</Column>
    //     <Row>
    //     <Number>41</Number>
    //     </Row>
    //    <Associations>
    //     <Passengers>
    //     <PassengerReferences>PAX1</PassengerReferences>
    //     </Passengers>
    //    <Flight>
    //     <SegmentReferences>SEG3</SegmentReferences>
    //     </Flight>
    //     </Associations>
    //     </Location>
    //     </SeatItem>
    //     </OfferItemType>
    //     </CreateOrderItem>
    //    '; 

if(isset($_Ainput["SeatColumn"])) { 

        $xml .='<CreateOrderItem>
        <OfferItemID Owner="SQ">'.$_Ainput["OfferItemRefID"].' </OfferItemID>
        <OfferItemType>
        <SeatItem>
            <Location>
                <Column>'.$_Ainput["SeatColumn"].'</Column>
                <Row>
                <Number>'.$_Ainput["SeatRow"].'</Number>
                </Row>
                <Associations>
                <Passengers>
                    <PassengerReferences>'.$_Ainput["PaxID"].'</PassengerReferences>
                </Passengers>
                <Flight>
                    <SegmentReferences>'.$_Ainput["PaxSegmentRefID"].'</SegmentReferences>
                </Flight>
                </Associations>
            </Location>
        </SeatItem>
        </OfferItemType>
    </CreateOrderItem>';
}

        $xml .='</CreateOrder>';
   

    $xml    .= '<DataLists>';

    // Contact Informations
    $xml    .= '<ContactInfoList>';
    foreach ($_paxDetails as $key => $_PaxValue) {

        if($_PaxValue['paxType']=='ADT' || $_PaxValue['paxType']=='CHD'){


        
            $xml .='<ContactInfo>';
            $xml.=' <IndividualRefID>'.$_PaxIds[$key].'</IndividualRefID>';
            $xml .=' <Phone> <LabelText>Mobile</LabelText>';
            $xml .='<CountryDialingCode>+91</CountryDialingCode>';
            $xml .='<PhoneNumber>'.$_Ainput["agentPhone"].'</PhoneNumber>';
            $xml .='</Phone>';
            $xml .='<EmailAddress><EmailAddressText>'.$_Ainput["agentMail"].'</EmailAddressText></EmailAddress>';
            $xml .='</ContactInfo>';
        }
    }
    $xml.='</ContactInfoList>';
    
    //PaxID

    $xml .='<PaxList>';
    foreach ($_paxDetails as $key => $value) {
             $dob        =$value["dob"];
             $dobFormated=date("Y-m-d",strtotime($dob));
            if($value['gender'] == "Male"){
                $gender ="M";
            }elseif($value['gender']=="gender"){
                $gender ="F";
            }
            $xml    .='<Pax>
                <PaxID>'.$value["paxID"].'</PaxID>
                <PTC>'.$value["paxType"].'</PTC>
                <Individual>
                    <IndividualID>'.$value["paxID"].'</IndividualID>
                    <Birthdate>'.$dobFormated.'</Birthdate>
                    <GenderCode>'.$gender.'</GenderCode>
                    <TitleName>'.$value["title"].'</TitleName>
                    <GivenName>'.$value["firstName"].'</GivenName>
                    <Surname>'.$value["lastName"].'</Surname>
                </Individual>
            </Pax>';
        }
     $xml .='</PaxList>';
   
    // Services if necessary
	
	$_AssrArray = '';
	
	$bag = 1;
	
	foreach($_Ainput['flightDetails'] as $_AflightKey => $_AflightVal){
		
		foreach($_AflightVal['viaFlights'] as $_AviaKey => $_AviaVal){
			
			if(is_array($_AviaVal['ssrDetails'])){
				foreach($_AviaVal['ssrDetails'] as $_AssrKey => $_AssrVal){
					
					$_AssrArray .='<ServiceDefinitionList><ServiceDefinition><ServiceDefinitionID>'.$_AssrVal['codeType'].'</ServiceDefinitionID>';
					$_AssrArray .='<Name>'.$_AssrVal['SSRCode'].'</Name>
					  <Description>
					  <DescID>'.$bag.'</DescID>
					  <DescText>'.$_AssrVal['description'].'</DescText>
					  </Description>
					  <BookingInstructions>
					  <Text>'.$_AssrVal['description'].'AAAAA'.$_AssrVal['PaxRefID'].'AAAAA'.$_AssrVal['PaxSegmentRefID'].'</Text>
					  </BookingInstructions>
					  </ServiceDefinition>
					  </ServiceDefinitionList>';
					  
					  $bag++;
				  
				}
				
			}
			
		}
		
	}
	
	$xml .=$_AssrArray;
	/* $_AssrArray='';
	if($_AssrArray!=''){
		
		 $xml .='<DataLists>';
		$xml .=$_AssrArray;
		 $xml .='</DataLists>';
	}else{
		
	} */
       
$xml .='</DataLists>';
    //  Payment Informations if necesary
    if($_Ainput["isTicketed"]){
        $xml .='<PaymentInfo>
        <Amount CurCode="'.$currencyCode.'">'.$_Ainput["totalAmount"].'</Amount>
        <TypeCode>CA</TypeCode>
        <PaymentMethod>
        <Cash>
        <CashInd>true</CashInd>
        </Cash>
        </PaymentMethod>
       </PaymentInfo>';
    }
  
    $xml .='</Request>
        </OrderCreateRQ>
    </soapenv:Body>
</soapenv:Envelope>';
// echo $xml;exit;
return $thisObj->formatXML($xml);


}
?>