<?php
function orderReshopXml($thisObj)
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



    $_Ainput          = $thisObj->_Ainput;
    // print_r($_Ainput);
    // exit;
    $msg_id           = $thisObj->generate_uuid();
    $timestamp        = $thisObj->timeStampHeader();
    $nonce            = $thisObj->generate_nonce();
    $encoded_nonce    = $thisObj->generate_encode_nonce($nonce);
    $passwordDigest   = $thisObj->generate_pass($timestamp, $nonce, $raw_pass);
    $date_in=date('Y-m-d');
    $requestTime= date('Y-m-d\TH:i:s'.substr((string)microtime(), 1, 4).'\Z',strtotime($date_in));
    
/*     $soapAction = 'http://webservices.amadeus.com/NDC_OrderReshop_18.1';
    $url        = 'https://nodea1.test.webservices.amadeus.com/1ASIWCLCSQ'; */
  
    $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sec="http://xml.amadeus.com/2010/06/Security_v1" xmlns:link="http://wsdl.amadeus.com/2010/06/ws/Link_v1" xmlns:ses="http://xml.amadeus.com/2010/06/Session_v3" xmlns:ns="http://www.iata.org/IATA/2015/00/2018.1/OrderReshopRQ">
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
        <OrderReshopRQ xmlns="http://www.iata.org/IATA/2015/00/2018.1/OrderReshopRQ">     
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

     if($_Ainput["service_name"]=='Reshop')
     {  
         //  Reshop the order  
         $sectorInfo =$_Ainput['sectorInfo'];
         $xml .='<Request>
                  <OrderItemRefID>'.$_Ainput["OrderID"].'</OrderItemRefID>
                  <UpdateOrder>
                     <ReshopOrder>
                        <ServiceOrder>
                           <AddOfferItems>
                              <ShoppingCriteria>
                                 <CabinTypeCriteria>
                                    <CabinTypeName>'.$_Ainput["CabinTypeName"].'</CabinTypeName>
                                 </CabinTypeCriteria>
                              </ShoppingCriteria>
                              <FlightRequest>';
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
                              $xml .='</FlightRequest>
                           </AddOfferItems>
                           <DeleteOrderItem>
                              <OrderItemRefID>'.$_Ainput["OrderItemRefID"].'</OrderItemRefID>
                           </DeleteOrderItem>
                        </ServiceOrder>
                     </ReshopOrder>
                  </UpdateOrder>
               </Request>';
      }

   //Reprice Order  offerid from

   if($_Ainput["service_name"]=='Reprice'){  

         $xml .='<Request>
         <OrderItemRefID>'.$_Ainput["OrderID"].'</OrderItemRefID>
         <UpdateOrder>
            <ReshopOrder>
               <ServiceOrder>
                  <AddOfferItems>
                     <ShoppingCriteria>
                        <ExistingOrderCriteria>
                           <OfferItem>
                              <OfferItemID>'.$_Ainput["OfferItemID"].'</OfferItemID>
                           </OfferItem>
                           <PaxRefID>'.$_Ainput["PaxRefID"][0].'</PaxRefID>';
                           if(isset($_Ainput["PaxRefID"][1])){
                               $xml .= '<PaxRefID>'.$_Ainput["PaxRefID"][1].'</PaxRefID>';
                           } 
                           if(isset($_Ainput["PaxRefID"][2])){
                               $xml .= '<PaxRefID>'.$_Ainput["PaxRefID"][2].'</PaxRefID>';
                           } 
                           if(isset($_Ainput["PaxRefID"][3])){
                               $xml .= '<PaxRefID>'.$_Ainput["PaxRefID"][3].'</PaxRefID>';
                           }
                          $xml .= '<Order>
                              <OrderID>'.$_Ainput["OrderID"].'</OrderID>
                           </Order>
                        </ExistingOrderCriteria>
                        
                     </ShoppingCriteria>
                     <FlightRequest>
                        <ShoppingResponse>
                           <ShoppingResponseID>'.$_Ainput["OfferID"].'</ShoppingResponseID>
                        </ShoppingResponse>
                     </FlightRequest>
                  </AddOfferItems>
                 
               </ServiceOrder>
            </ReshopOrder>
         </UpdateOrder>
      </Request>';
}

if($_Ainput["service_name"]=='ReshopRefund')
{  
      //  Reshop the order  
      $sectorInfo =$_Ainput['sectorInfo'];
      $xml .='<Request>
               <OrderItemRefID>'.$_Ainput["OrderID"].'</OrderItemRefID>
               <UpdateOrder>
                  <ReshopOrder>
                     <ServiceOrder>
                        <DeleteOrderItem>
                           <OrderItemRefID>'.$_Ainput["OrderItemRefID"].'</OrderItemRefID>
                        </DeleteOrderItem>
                     </ServiceOrder>
                  </ReshopOrder>
               </UpdateOrder>
            </Request>';
}
if($_Ainput["service_name"]=='OrderWithoutFormOfPayment')
{  
      // Reshop the order  
   $xml .=' <Request>
        <OrderItemRefID>'.$_Ainput["OrderID"].'</OrderItemRefID>
        <UpdateOrder>
          <RepriceOrder/>
        </UpdateOrder>
      </Request>';
}


//                         <CabinTypeCriteria>
//                            <CabinTypeName>'.$_Ainput["CabinTypeName"].'</CabinTypeName>
//                         </CabinTypeCriteria>
// <DeleteOrderItem>
// <OrderItemRefID>'.$_Ainput["OrderItemRefID"].'</OrderItemRefID>
// </DeleteOrderItem>

   //  // Void the order
/*     $xml .=' <Request><OrderItemRefID>'.$_Ainput["OrderID"].'</OrderItemRefID>
    <UpdateOrder>
    <ReshopOrder>
    <ServiceOrder>
    <DeleteOrderItem>
    <OrderItemRefID>'.$_Ainput["OrderItemRefID"].'</OrderItemRefID>
    </DeleteOrderItem>
    </ServiceOrder>
    </ReshopOrder>
    </UpdateOrder>
    </Request>';    */

   //Reprice the order
   // $xml .=' <Request>
   //      <OrderItemRefID>'.$_Ainput["OrderID"].'</OrderItemRefID>
   //      <UpdateOrder>
   //        <RepriceOrder/>
   //      </UpdateOrder>
   //    </Request>';
    $xml .='</OrderReshopRQ>
    </soapenv:Body>
</soapenv:Envelope>';
// echo $xml;exit;
    return $thisObj->formatXML($xml);
}
?>