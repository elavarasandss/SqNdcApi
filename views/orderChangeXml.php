<?php
function orderChangeXml($thisObj)
{

   $_Ausernme        = $thisObj->_Asettings['apiCredentials']['userName'];
   $_Apassword       = $thisObj->_Asettings['apiCredentials']['password'];
   $_APseudoCityCode = $thisObj->_Asettings['apiCredentials']['PseudoCityCode'];
   $_AWSAP           = $thisObj->_Asettings['apiCredentials']['WSAP'];
   $CountryCode      = $thisObj->_Asettings['apiCredentials']['CountryCode'];
   $AgencyId         = $thisObj->_Asettings['apiCredentials']['AgencyId'];          //AgencyId	 IataNumber
   $IataNumber       = $thisObj->_Asettings['apiCredentials']['IataNumber'];        //AgencyId	 IataNumber
   $soapAction       = $thisObj->_Asettings['actionInfo']['soapAction'];
   $url              = $thisObj->_SrequestUrl;
   $raw_pass        = $_Apassword;



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
    
    //$soapAction = 'http://webservices.amadeus.com/NDC_OrderChange_18.1';
    //$url 		= 'https://nodea1.test.webservices.amadeus.com/1ASIWCLCSQ';
    $_paxDetails = $_Ainput['paxDetails'];
    $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sec="http://xml.amadeus.com/2010/06/Security_v1" xmlns:link="http://wsdl.amadeus.com/2010/06/ws/Link_v1" xmlns:ses="http://xml.amadeus.com/2010/06/Session_v3" xmlns:ns="http://www.iata.org/IATA/2015/00/2018.1/OrderChangeRQ">
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
        <OrderChangeRQ xmlns="http://www.iata.org/IATA/2015/00/2018.1/OrderChangeRQ">
        <PayloadAttributes>
        <Version>18.1</Version>
    </PayloadAttributes>';
   
    $xml .='<PointOfSale>
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
            $_paxDetails = $_Ainput['paxDetails'];
            foreach ($_paxDetails as $key => $_PAxId) {
                $_PaxIds[]=$_PAxId["paxID"];
            }
            // <ActionContextCode>UPDATE_PRICE</ActionContextCode>
//     $xml .=' <Request>
   
//     <BookingRef>
//     <BookingID>'.$_Ainput["BookingID"].'</BookingID>
//     <BookingEntity>'.$_Ainput["BookingEntity"].'</BookingEntity>
//     </BookingRef>
//     <ChangeOrder>
//        <UpdateOrderItem>
//           <AcceptOffer>
//              <SelectedOffer>
//                 <OfferID>'.$_Ainput["OfferID"].'</OfferID>
//                 <OwnerCode>SQ</OwnerCode>
//                 <ShoppingResponseRefID>0</ShoppingResponseRefID>
//                 <SelectedOfferItem>
//                    <OfferItemRefID>'.$_Ainput["OfferItemID"].'</OfferItemRefID>
//                 </SelectedOfferItem>
//              </SelectedOffer>
//           </AcceptOffer>
//        </UpdateOrderItem>';
//       $xml.=' <DataLists>';
//       foreach ($_paxDetails as $key => $value) {
//          $dob        =$value["dob"];
//          $dobFormated=date("Y-m-d",strtotime($dob));
//         if($value['gender'] == "Male"){
//             $gender ="M";
//         }elseif($value['gender']=="gender"){
//             $gender ="F";
//         }
//         $xml    .='<Pax>
//             <PaxID>'.$value["paxID"].'</PaxID>
//             <PTC>'.$value["paxType"].'</PTC>
//             <Individual>
//                 <IndividualID>'.$value["paxID"].'</IndividualID>
//                 <Birthdate>'.$dobFormated.'</Birthdate>
//                 <GenderCode>'.$gender.'</GenderCode>
//                 <TitleName>'.$value["title"].'</TitleName>
//                 <GivenName>'.$value["firstName"].'</GivenName>
//                 <Surname>'.$value["lastName"].'</Surname>
//             </Individual>
//         </Pax>';
//     }
// $xml .=' </DataLists>';
// $xml .='    </ChangeOrder>
//     <Order>
//        <OrderID>'.$_Ainput["OrderID"].'</OrderID>
//        <OwnerCode>SQ</OwnerCode>
//     </Order>
//     <PaymentInfo>
//        <Amount CurCode="SGD">'.$_Ainput["totalAmount"].'</Amount>
//                                    <TypeCode>CA</TypeCode>
//                                    <PaymentMethod><Cash><CashInd>true</CashInd></Cash></PaymentMethod></PaymentInfo>
//       </Request>';

// Orderchange without price
// $xml .=' <Request>';
//     $xml.='<ChangeOrder>
//        <UpdateOrderItem>
//           <AcceptOffer>
//              <SelectedOffer>
//                 <OfferID>'.$_Ainput["OfferID"].'</OfferID>
//                 <OwnerCode/>
//                 <ShoppingResponseRefID>0</ShoppingResponseRefID>
//                 <SelectedOfferItem>
//                    <OfferItemRefID>'.$_Ainput["OfferItemID"].'</OfferItemRefID>
//                 </SelectedOfferItem>
//              </SelectedOffer>
//           </AcceptOffer>
//        </UpdateOrderItem>
//     </ChangeOrder>  <Order>
//            <OrderID>'.$_Ainput["OrderID"].'</OrderID>
//            <OwnerCode/>
//          </Order></Request>';
// <ActionContextCode>UPDATE_PRICE</ActionContextCode>    use for payment
if($_Ainput["service_name"]!='AddSeats'){
      

   $xml .=' <Request>
  
        <ChangeOrder>
          <UpdateOrderItem>
            <AcceptOffer>
              <SelectedOffer>
                <OfferID>'.$_Ainput["OfferID"].'</OfferID>
                <OwnerCode/>
                <ShoppingResponseRefID>'.$_Ainput["ShoppingResponseID"].'</ShoppingResponseRefID>
                <SelectedOfferItem>
                  <OfferItemRefID>'.$_Ainput["OfferItemID"].'</OfferItemRefID>
                </SelectedOfferItem>
              </SelectedOffer>
            </AcceptOffer>
          </UpdateOrderItem>
        </ChangeOrder>
        <Order>
          <OrderID>'.$_Ainput["OrderID"].'</OrderID>
          <OwnerCode/>
        </Order>';


        if($_Ainput["isTicketed"]){
      $xml.='<PaymentInfo>
      <Amount CurCode="INR">'.$_Ainput["totalAmount"].'</Amount>
      <TypeCode>CA</TypeCode>
      <PaymentMethod>
      <Cash>
      <CashInd>true</CashInd>
      </Cash>
      </PaymentMethod>
     </PaymentInfo>';
      }


    $xml .='</Request>';

   }

   //  $xml.='<ChangeOrder>
   //     <UpdateOrderItem>
   //        <AcceptOffer>';
   //     $xml .='<ServiceDefinitionList><ServiceDefinition><ServiceDefinitionID>SRV1</ServiceDefinitionID>';
   //   $xml .='<Name>ONE KILOGRAM BAGGAGE</Name>
   //   <Description>
   //   <DescID>1</DescID>
   //   <DescText>ONE KILOGRAM BAGGAGE</DescText>
   //   </Description>
   //   <BookingInstructions>
   //   <Text>TTL\s?%WVAL%KG</Text>
   //   </BookingInstructions>
   //   </ServiceDefinition>
   //   </ServiceDefinitionList>';       
   //  $xml   .='</AcceptOffer>
   //     </UpdateOrderItem>
   //  </ChangeOrder>';

   if($_Ainput["service_name"]=='AddSeats'){

    // Extra seat
      $xml .='<Request><ChangeOrder>
      <UpdateOrderItem>
         <AcceptOffer>
         <CreateOrderItem>
            <OrderItem>
               <OfferItemID Owner="SQ">'.$_Ainput["OfferItemRefID"].'</OfferItemID>
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
            </OrderItem>
         </CreateOrderItem>
         </AcceptOffer>
      </UpdateOrderItem>
   </ChangeOrder>';

   $xml.='<Order>
   <OrderID>'.$_Ainput["OrderID"].'</OrderID>
   <OwnerCode/>
 </Order>';

   if($_Ainput["isTicketed"]){
      $xml.='<PaymentInfo>
      <Amount CurCode="INR">'.$_Ainput["totalAmount"].'</Amount>
      <TypeCode>CA</TypeCode>
      <PaymentMethod>
      <Cash>
      <CashInd>true</CashInd>
      </Cash>
      </PaymentMethod>
     </PaymentInfo>';
      }

       $xml.='</Request>';
   }

// for extra baggage
// $xml .='    <Request>
//             <ChangeOrder>
//                <UpdateOrderItem>
//                   <AcceptOffer>
//                      <SelectedOffer>
//                         <OfferID>SP2P-6025756651551515426-1</OfferID>
//                         <OwnerCode/>
//                         <ShoppingResponseRefID>SP2P-6025756651551515426</ShoppingResponseRefID>
//                         <SelectedOfferItem>
//                            <OfferItemRefID>SP2P-6025756651551515426-1-1</OfferItemRefID>
//                            <PaxRefID>PAX2</PaxRefID>
//                            <SelectedBundleServices>
//                         		<SelectedServiceID>SRV0</SelectedServiceID>
//                      	  </SelectedBundleServices>
//                         </SelectedOfferItem>
//                         <SelectedOfferItem>
//                            <OfferItemRefID>SP2P-6025756651551515426-1-50</OfferItemRefID>
//                            <PaxRefID>PAX3</PaxRefID>
//                            <SelectedBundleServices>
//                         		<SelectedServiceID>SRV59</SelectedServiceID>
//                      	  </SelectedBundleServices>
//                         </SelectedOfferItem>
//                      </SelectedOffer>
//                   </AcceptOffer>
//                </UpdateOrderItem>
//             </ChangeOrder>';
   //  Order Details
   //  $xml.='<Order>
   //     <OrderID>'.$_Ainput["OrderID"].'</OrderID>
   //     <OwnerCode/>
   //  </Order>';
    
   //  Payment Infos
//     $xml .='<PaymentInfo>
//     <Amount CurCode="SGD">'.$_Ainput["totalAmount"].'</Amount>
//     <TypeCode>CA</TypeCode>
//     <PaymentMethod>
//     <Cash>
//     <CashInd>true</CashInd>
//     </Cash>
//     </PaymentMethod>
//    </PaymentInfo>';
//   $xml .='</Request>';


    //     $xml .='<Request>';
    //    $xml .='<ChangeOrder>
    //    <UpdateOrderItem>
    //    <AcceptOffer>
    //    <SelectedOffer>
    //    <OfferID>'.$_Ainput["OfferID"].'</OfferID>
    //    <OwnerCode>'.$_Ainput["carrierCode"].'</OwnerCode>
    //    <ShoppingResponseRefID>'.$_Ainput["ShoppingResponseID"].'</ShoppingResponseRefID>
    //    <SelectedOfferItem>
    //    <OfferItemRefID>'.$_Ainput["OfferItemID"].'</OfferItemRefID> 
    //    <PaxRefID>'.$_Ainput["PaxRefID"].'</PaxRefID>
    //    </SelectedOfferItem>
    //    </SelectedOffer>
    //     </AcceptOffer>
    //      </UpdateOrderItem>
    //      </ChangeOrder>';
    // $xml .='<Order>
    //     <OrderID>'.$_Ainput["OrderID"].'</OrderID>
    //     <OwnerCode>'.$_Ainput["carrierCode"].'</OwnerCode>
    //     </Order>
    //     </Request>       
    $xml .='</OrderChangeRQ>
    </soapenv:Body>
</soapenv:Envelope>';
// echo $xml;exit;
    return $thisObj->formatXML($xml);
}
?>