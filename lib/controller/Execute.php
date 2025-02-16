<?php
/**
	@File Name 		:	Execute.php
	@Author 		:	Ramanathan M <ramanathan@dss.com.sg>
	@Created Date	:	2015-12-21 10:55 AM
	@Description	:	Execute the webservive
*/
class Execute 
{
	var $_SwebserviceMethod;
	var $_Asettings;
	var $_SreqXml;
	var $_SheaderXml;
	var $_SrequestUrl;
	var $_IreferenceId;
	
	function __construct()
	{
		$this->_SwebserviceMethod = $GLOBALS['CONF']['site']['webserviceMethod'];
		$this->_Asettings		  = array();
		$this->_SreqXml			  = '';
		$this->_SheaderXml		  = '';
		$this->_SrequestUrl		  = '';
		$this->_IreferenceId	  = 0;
	}
	
	public static function &singleton()
    {
        static $instance;

        // If the instance is not there, create one
        if (!isset($instance)) {
            $class = __CLASS__;
            $instance = new $class();
        }
        return $instance;
    }
	
    public function _setData()
	{
		$this->_assignValues();		
	}

	public function _assignValues()
	{		
		$headerFunctionName = $this->_SheaderTemplateName;
		$headerFunctionName = str_replace(".tpl","",$headerFunctionName);
		fileInclude("views/{$headerFunctionName}.php");
		$this->_SheaderXml	= $headerFunctionName($this);
		
		$functionName = $this->_StemplateName;
		$functionName = str_replace(".tpl","",$functionName);
		fileInclude("views/{$functionName}.php");
		$this->_SreqXml	= $functionName($this);
	}
	
	public function getApiCurrency($origin,$destination)
	{		
		return $GLOBALS['CONF']['site']['defaultCurrency'];
	}
	
	function _checkUserBalance($_IbookingAmount)
	{		
		$sqlAccount = "SELECT
								(credit_limit + available_balance) as userBalance,
								currency
					   FROM
								account_details
					   WHERE
								account_id = '".$GLOBALS['CONF']['userSettings']['accountId']."'
								LIMIT 1";
								
		$_Aresult = $this->_Odb->getAll($sqlAccount);
		
		if(isset($_Aresult[0]['userBalance']) && $_Aresult[0]['userBalance'] > 0){
			
			$_IuserBalance	= $_Aresult[0]['userBalance'];
			$_IuserBalance	= Common::getRoundedFare($_Aresult[0]['userBalance'],$_Aresult[0]['currency']);
			
			if($_IuserBalance > $_IbookingAmount){
				return true;
			}
		}
		
		return false;
	}
	
	function calculateMarkupValue($_AmarkupInput)
	{
		$_ImarkupId				= 0;
		$_ImarkupValue			= 0;
		$_IperPaxMarkup			= 0;
		$_SmarkupType			= '';
		$_SmarkupFeeType		= '';
		$_SmarkupAddIn			= '';
		$_SmarkupTaxComponent	= '';
		$_AmarkupData			= array();
		
		$_AmarkupConf			= $_AmarkupInput['markupDetails'];
		$_SmarkupCarrier		= $_AmarkupInput['airlineCodes'];
		$_ItotalPaxCount		= $_AmarkupInput['paxCount'];
		$_ItripCount			= $_AmarkupInput['flightCount'];
		$_ItotalAmount			= $_AmarkupInput['totalAmount'];
		
		if(isset($_AmarkupConf['ALL']) && !empty($_AmarkupConf['ALL'])){
			$_AmarkupData = $_AmarkupConf['ALL'];
		}
		
		$_SmarkupCarrier = array_unique($_SmarkupCarrier);
		
		foreach($_SmarkupCarrier as $key=>$val){
			if(isset($_AmarkupConf[$val]) && !empty($_AmarkupConf[$val])){
				$_AmarkupData = $_AmarkupConf[$val];
				break;
			}
		}
		
		if(isset($_AmarkupData['markup_amount']) && !empty($_AmarkupData['markup_amount'])){
			
			if(isset($_AmarkupInput['checkExisting']) && $_AmarkupInput['checkExisting'] == "Y" && $_AmarkupData['markup_fee_type'] == "FIXED" && in_array($_AmarkupData['markup_type'],array("TP","PP"))){
				
				if($_AmarkupInput['journeyIndex'] > 0){
					return array();
				}
			}
			
			$_ImarkupId 			= $_AmarkupData['markup_id'];
			$_ImarkupValue 			= $_AmarkupData['markup_amount'];
			$_SmarkupAddIn			= $_AmarkupData['addMarkupIn'];
			$_SmarkupTaxComponent	= $_AmarkupData['taxComponentName'];
			$_SmarkupFeeType		= $_AmarkupData['markup_fee_type'];
			$_SmarkupType			= $_AmarkupData['markup_type'];
			
			if($_AmarkupData['markup_fee_type'] == "PERCENTAGE"){
				$_ImarkupValue = $_ItotalAmount * ($_ImarkupValue / 100);
			}
			
			if($_AmarkupData['markup_type'] == "PP"){
				$_ImarkupValue = $_ImarkupValue * $_ItotalPaxCount;
			}
			
			$_IperPaxMarkup = round(($_ImarkupValue / $_ItotalPaxCount),4);
		}
		
		$_AmakupResult = array();
		$_AmakupResult['markupId']				= $_ImarkupId;
		$_AmakupResult['markupValue']			= $_ImarkupValue;
		$_AmakupResult['markupType']			= $_SmarkupType;
		$_AmakupResult['markupFeeType']			= $_SmarkupFeeType;
		$_AmakupResult['markupAddIn']			= $_SmarkupAddIn;
		$_AmakupResult['markupTaxComponent']	= $_SmarkupTaxComponent;
		$_AmakupResult['perPaxMarkup']			= $_IperPaxMarkup;
		
		return $_AmakupResult;
	}
	
	public function getBookingDetails($pnr)
	{
		$sql = "SELECT  
						*
				FROM
						booking_details
				WHERE
						pnr = '".$pnr."'
						LIMIT 1";
		
		$_Adata = $this->_Odb->getAll($sql);
		
		return $_Adata;
	}
	
	public function _getUpdatePrice()
	{
		$sql = "SELECT
						*
				FROM
						tbo_update_price
				WHERE
						account_id = '".$GLOBALS['CONF']['userSettings']['accountId']."'
						AND api_mode = '".$GLOBALS['CONF']['userSettings']['mode']."'
						AND trace_id = '".$this->_Ainput['referenceId']."'
						ORDER BY update_price_id DESC LIMIT 1";
						
		$_Adata = $this->_Odb->getAll($sql);

		return $_Adata;
	}
	
	public function getPassengerDetails($bookingDetailsId)
	{
		$sql = "SELECT  
						*
				FROM
						booking_passenger_details
				WHERE
						booking_details_id = '".$bookingDetailsId."'";
		
		$_Adata = $this->_Odb->getAll($sql);
		
		return $_Adata;
	}
	
	public function _executeService()
	{	
		// echo $this->_SwebserviceMethod;exit;
		if($this->_SwebserviceMethod  == 'SOAP'){
			 return $this->_doSoapAction();
		}
		
		if($this->_SwebserviceMethod  == 'CURL'){
			 //return $this->_doCurlAction();
			 return $this->callCurl();
		}
	}
	
	public function _doCurlAction()
	{
		$header		= $this->_SheaderXml;
		$requestXml = $this->_SreqXml;
		
		$postData	= $requestXml;
		
		$url 		= $this->_SrequestUrl;
		
		$soapAction = $this->_Asettings['actionInfo']['actionName'];;

		$ch2  = curl_init();
	
		$ch2  = curl_init();
	
		curl_setopt($ch2, CURLOPT_URL, $url);
		
		curl_setopt($ch2, CURLOPT_TIMEOUT, 180);
		
		curl_setopt($ch2, CURLOPT_HEADER, 0);
		
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
		
		curl_setopt($ch2, CURLOPT_POST, 1);
		
		curl_setopt($ch2, CURLOPT_POSTFIELDS, $postData); 

		curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false); 
		
		curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, 2);
		
		//curl_setopt($ch2, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');

		$httpHeader2 = array("Content-Type: application/json;charset=UTF-8","Content-length: " . strlen($postData));
		
		curl_setopt($ch2, CURLOPT_HTTPHEADER, $httpHeader2);
		
		curl_setopt ($ch2, CURLOPT_ENCODING, "gzip,deflate");
		
		if(!empty($this->_IreferenceId)){
			logWrite("\n".$url." \n\nRequest :- \n---------- \n\n".$postData."\n","".$this->_IreferenceId,'a+','N');
		}
		else{
			logWrite("\n".$url." \n\nRequest :- \n---------- \n\n".$postData,"LogXML",'a+','Y');
		}
		
		//if(curl_exec($ch2)){ echo "Yes<br>"; } else { echo "No<br>"; }
		
		$response 	= '';
		$response 	= curl_exec($ch2);
		
		$_Aresponse = json_decode($response,true);
		//echo "<pre>";print_r($postData);print_r($response);
		curl_close($ch2);
		
		if(!empty($this->_IreferenceId)){
			logWrite("\n\nResponse :- \n----------- \n\n".$response."\n","".$this->_IreferenceId,'a+','N');
		}
		else{
			logWrite("\n\nResponse :- \n----------- \n\n".$response,"LogXML",'a+','Y');
		}
		
		return $_Aresponse;
	}
	public function getDefaultBookingFee($api,$acc,$airline)
    {
        $query = "SELECT COALESCE(booking_fee,0) FROM booking_fee WHERE ((booking_api = '".$api."' AND booking_account = '".$acc."' AND airlines LIKE '%".$airline."%')  OR (booking_api = '0' AND booking_account = '".$acc."') OR (booking_api = '".$api."' AND booking_account = '0' AND airlines = '0') OR (booking_api = '0' AND booking_account = '0' AND airlines = '0'))  AND status = 1 ORDER BY IF(booking_api = 0,booking_api,booking_account) DESC";
		
        $_IbookingFee = $this->_Odb->getOne($query);
		
		$_Ares = array();
        $_Ares['booking_fee'] = 0;
			
        if(!empty($_IbookingFee)){
            $_Ares['booking_fee'] = $_IbookingFee;
        }
		
        return $_Ares;
    }
	
	
	
	/* New Code*/
	
	public function generate_uuid() {

		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}

	public function timeStampHeader($ttlHours = 0) {
		
		date_default_timezone_set("UTC");

		$t 			= microtime(true);    
		$micro 		= sprintf("%03d", ($t - floor($t)) * 1000);
		$date 		= new DateTime(date('Y-m-d H:i:s.' . $micro));
		$timestamp 	= $date->format("Y-m-d\TH:i:s:") . $micro . 'Z';
		return $timestamp;
	}

	public function generate_nonce() {

		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$length=10;
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}

		return substr($randomString, 0, 8);
	}

	public function generate_encode_nonce($str) {
		$encodedNonce = base64_encode($str);
		return $encodedNonce;
	}

	public function generate_pass($timestamp, $nonce, $raw_pass) {
		//$encodedNonce = base64_encode($nonce);
		$passSHA = base64_encode(sha1($nonce . $timestamp . sha1($raw_pass, true), true));
		return $passSHA;
	}


	public function formatXML($inputXML)
	{
		libxml_use_internal_errors( true );
		
		$dom = new DOMDocument;
		
		$dom->preserveWhiteSpace = FALSE;
		
		if(trim($inputXML)=="")
		{
			return '';
		}
		
		$dom->loadXML($inputXML);
		
		libxml_use_internal_errors( true );
		$errors = libxml_get_errors();
		
		if(trim(empty( $errors ))!=1)
		{
			return "Given xml may not correct XML";
		}
		
		$inputXML = trim($inputXML);
		$dom->loadXML($inputXML);
		$dom->formatOutput = TRUE;
		$formattedXML =  $dom->saveXml();
		$formattedXML = trim($formattedXML);
		
		return $formattedXML;
	}		



	public function xmlstrToArray($xmlstr)
	{
		$doc = new \DOMDocument();
		$doc->loadXML($xmlstr);
		return domNodeToArray($doc->documentElement);
	}
	
	public function domNodeToArray($node) 
	{
		$output = array();
		switch ($node->nodeType) {
			case XML_CDATA_SECTION_NODE:
			case XML_TEXT_NODE:
				$output = trim($node->textContent);
				break;
			case XML_ELEMENT_NODE:
				for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) { 
					$child = $node->childNodes->item($i);
					$v = $this->domNodeToArray($child);
					if(isset($child->tagName)) {
						$t = $child->tagName;
						$t1 = explode(":",$t);
						if(isset($t1[1])){
							$t = $t1[1];
						}
						if(!isset($output[$t])) {
							$output[$t] = array();
						}
						$output[$t][] = $v;
					}
					elseif($v) {
						$output = (string) $v;
					}
				}
				if($node->attributes->length && !is_array($output)) {
					$output = array('content'=>$output);
				}
				if(is_array($output)) {
					if($node->attributes->length) {
						$a = array();
						foreach($node->attributes as $attrName => $attrNode) {
							$a[$attrName] = (string) $attrNode->value;
						}
						$output['attributes'] = $a;
					}
					foreach ($output as $t => $v) {
						if(is_array($v) && count($v)==1 && $t!='attributes') {
							$output[$t] = $v[0];
						}
					}
				}
			break;
		}
		return $output;
	}


	public function callCurl()
	{
		
		//$header		= $this->_SheaderXml;
		 $requestXml = $this->_SreqXml;
		$_Saction= $this->_Ainput['action'];
		 $postData	= $requestXml;
		
		 $url 		= $this->_SrequestUrl;
		//  $url 		= 'https://nodeA1.test.webservices.amadeus.com/1ASIWCLCSQ';
		   $soapAction = $GLOBALS['CONF']['api']['actions'][$_Saction]['soapAction'];
		// echo $url;exit;
		
		// logWrite("\n Request \n ----------- \n".$url.$postData,"LogXML",'Y','a+');
		$ch2  = curl_init();
	// echo	$url;exit;
		curl_setopt($ch2, CURLOPT_URL, $url);
		
		curl_setopt($ch2, CURLOPT_TIMEOUT, 180);
		
		//curl_setopt($ch2, CURLOPT_HEADER, 0);
		
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
		
		curl_setopt($ch2, CURLOPT_POST, 1);
		
		curl_setopt($ch2, CURLOPT_POSTFIELDS, $requestXml); 

		curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false); 
		
		curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, false);
		
		//curl_setopt($ch2, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
		
		//curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, FALSE);
		
		//curl_setopt($ch2, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');

		$httpHeader2 = array("SOAPAction: {$soapAction}","Content-Type: text/xml;charset=UTF-8","Accept-Encoding","gzip,deflate");
		
			
			// logWrite("\n Headers \n ----------- \n".print_r($httpHeader2,true),"LogXML",'Y','a+');
		
		curl_setopt($ch2, CURLOPT_HTTPHEADER, $httpHeader2);
		
		curl_setopt ($ch2, CURLOPT_ENCODING, "gzip,deflate");
		
		/* if(curl_exec($ch2))
		{ 
			echo "Yes<br>"; 
		} 
		else 
		{ 
			echo "No<br>";
			$errno = curl_errno($ch2);
			$error_message = curl_strerror($errno);
			echo "cURL error ({$errno}):\n {$error_message}<br>";
		} */
		// echo $url;exit;
	 $responseJson = curl_exec($ch2);
	//  logWrite("\n Request \n ----------- \n".$url.$responseJson,"LogXML",'Y','a+');
	
	
		
		
		curl_close($ch2);

		/*$iwsCurl = curl_init($url);
		
		$optArr = array(
				CURLOPT_POST => true,
				CURLOPT_CUSTOMREQUEST=> "POST",
				CURLOPT_POSTFIELDS => $xml,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_URL => $url,
				CURLOPT_VERBOSE => true,
				CURLOPT_FRESH_CONNECT => false,
				CURLOPT_HEADER => false,
				CURLOPT_TIMEOUT=>1000,
				CURLOPT_HTTPHEADER => array('Content-Type: text/xml','Accept:text/xml','SOAPAction : '.$seapAction),
			);

		curl_setopt_array($iwsCurl,$optArr);

		$responseJson = curl_exec($iwsCurl);*/

		if($parseXml == ''){
			$responseJson = $this->formatXML($responseJson);
		}
		else{
			if($responseJson != ''){
				$responseJson = $this->xmlstrToArray($responseJson);
			}
		}
		
		if(!empty($this->_IreferenceId)){
			logWrite("\n".$url." \n\nRequest :- \n---------- \n\n".$requestXml."\n","".$this->_IreferenceId,'a+','N');
		}
		else{
			logWrite("\n".$url." \n\nRequest :- \n---------- \n\n".$requestXml,"LogXML",'a+','Y');
		}
		
		
		if(!empty($this->_IreferenceId)){
			logWrite("\n\nResponse :- \n----------- \n\n".$responseJson."\n","".$this->_IreferenceId,'a+','N');
		}
		else{
			logWrite("\n\nResponse :- \n----------- \n\n".$responseJson,"LogXML",'a+','Y');
		}
		
		return $responseJson;
	}

	public function getServiceListcallCurl()
	{	
		require_once('/../../userinfo.php');
		$_AuserInfo	= getUserInfo();
		require_once('/../../direct.php');
		$_AserviceInput = getServiceList($_AuserInfo);
		// echo "<pre>";
		// print_r($_AserviceInput);exit;
		require_once('/../../views/getServiceListXml.php');
		$_AserviceXml = getServiceListXml($_AserviceInput);
		
		// echo $_AserviceXml;exit;
		//$header		= $this->_SheaderXml;
		 $requestXml = $_AserviceXml;
		$_Saction= $this->_Ainput['action'];
		 $postData	= $requestXml;
		
		 $url 		= $this->_SrequestUrl;
		//  $url 		= 'https://nodeA1.test.webservices.amadeus.com/1ASIWCLCSQ';
		   $soapAction ="http://webservices.amadeus.com/NDC_ServiceList_18.1";
		// echo $url;exit;
		
		// logWrite("\n Request \n ----------- \n".$url.$postData,"LogXML",'Y','a+');
		$ch2  = curl_init();
	// echo	$url;exit;
		curl_setopt($ch2, CURLOPT_URL, $url);
		
		curl_setopt($ch2, CURLOPT_TIMEOUT, 180);
		
		//curl_setopt($ch2, CURLOPT_HEADER, 0);
		
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
		
		curl_setopt($ch2, CURLOPT_POST, 1);
		
		curl_setopt($ch2, CURLOPT_POSTFIELDS, $requestXml); 

		curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false); 
		
		curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, false);
		
		$httpHeader2 = array("SOAPAction: {$soapAction}","Content-Type: text/xml;charset=UTF-8","Accept-Encoding","gzip,deflate");		
			// logWrite("\n Headers \n ----------- \n".print_r($httpHeader2,true),"LogXML",'Y','a+');
		
		curl_setopt($ch2, CURLOPT_HTTPHEADER, $httpHeader2);
		
		curl_setopt ($ch2, CURLOPT_ENCODING, "gzip,deflate");
		
	 $responseJson = curl_exec($ch2);
	//  logWrite("\n Request \n ----------- \n".$url.$responseJson,"LogXML",'Y','a+');
		curl_close($ch2);

		if($parseXml == ''){
			$responseJson = $this->formatXML($responseJson);
		}
		else{
			if($responseJson != ''){
				$responseJson = $this->xmlstrToArray($responseJson);
			}
		}
		
		if(!empty($this->_IreferenceId)){
			logWrite("\n".$url." \n\nRequest :- \n---------- \n\n".$requestXml."\n","".$this->_IreferenceId,'a+','N');
		}
		else{
			logWrite("\n".$url." \n\nRequest :- \n---------- \n\n".$requestXml,"LogXML",'a+','Y');
		}
		
		
		if(!empty($this->_IreferenceId)){
			logWrite("\n\nResponse :- \n----------- \n\n".$responseJson."\n","".$this->_IreferenceId,'a+','N');
		}
		else{
			logWrite("\n\nResponse :- \n----------- \n\n".$responseJson,"LogXML",'a+','Y');
		}
		// echo "<pre>";
		// print_r($responseJson);exit;
		return $responseJson;
	}

	function getServiceListcallCurlNew(){
		$raw_pass 	= 'Ama19Web';
		$msg_id           = $this->generate_uuid();
$timestamp        = $this->timeStampHeader();
$nonce            = $this->generate_nonce();
$encoded_nonce    = $this->generate_encode_nonce($nonce);
$passwordDigest   = $this->generate_pass($timestamp, $nonce, $raw_pass);
$date_in=date('Y-m-d');
$requestTime= date('Y-m-d\TH:i:s'.substr((string)microtime(), 1, 4).'\Z',strtotime($date_in));

$soapAction = 'http://webservices.amadeus.com/NDC_ServiceList_18.1';
$url 		= 'https://nodea1.test.webservices.amadeus.com/1ASIWCLCSQ';
$xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sec="http://xml.amadeus.com/2010/06/Security_v1" xmlns:link="http://wsdl.amadeus.com/2010/06/ws/Link_v1" xmlns:ses="http://xml.amadeus.com/2010/06/Session_v3" xmlns:ns="http://www.iata.org/IATA/2015/00/2018.1/ServiceListRQ">
<soapenv:Header xmlns:wsa="http://www.w3.org/2005/08/addressing">
   <add:MessageID xmlns:add="http://www.w3.org/2005/08/addressing">'.$msg_id.'</add:MessageID>
   <wsa:Action>'.$soapAction .'</wsa:Action>
   <add:To xmlns:add="http://www.w3.org/2005/08/addressing">https://nodea1.test.webservices.amadeus.com/1ASIWNDCSQ</add:To>
   <sec:AMA_SecurityHostedUser>
      <sec:UserID POS_Type="1" RequestorType="U" PseudoCityCode="NDCSQ08SQ" AgentDutyCode="SU">
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

   <ServiceListRQ xmlns="http://www.iata.org/IATA/2015/00/2018.1/ServiceListRQ">
         <PayloadAttributes>
            <Version>18.1</Version>
         </PayloadAttributes>
         <PointOfSale>
            <Country>
               <CountryCode>SG</CountryCode>
            </Country>
         </PointOfSale>
         <Party>
            <Recipient>
               <ORA>
                  <AirlineDesigCode>SQ</AirlineDesigCode>
               </ORA>
            </Recipient>
            <Participant>
               <Aggregator>
                  <AggregatorID>GEN</AggregatorID>
               </Aggregator>
            </Participant>
            <Sender>
               <TravelAgency>
                  <AgencyID>12345678</AgencyID>
                  <IATA_Number>12345678</IATA_Number>
               </TravelAgency>
            </Sender>
         </Party>';
   $xml .='<Request>
   <CoreRequest>
      <Offer>
      <OfferID>'.$this->_Ainput["OfferID"].'</OfferID>
      <OwnerCode>'.$this->_Ainput["carrierCode"].'</OwnerCode>
      <OfferItem>
      <OfferItemID>'.$this->_Ainput["OfferItemID"].'</OfferItemID>
      <Service> <ServiceID>1</ServiceID></Service>
      </OfferItem>
      </Offer>
      </CoreRequest>
      <ShoppingCriteria>
 <ServiceCriteria>
 <PrefLevel>
 <PrefContextText>CFS</PrefContextText>
 </PrefLevel>
 </ServiceCriteria>
 </ShoppingCriteria>

      </Request>
   </ServiceListRQ>
</soapenv:Body>
</soapenv:Envelope>';
// echo $xml;exit;
$postdata= $this->formatXML($xml);
$ch2  = curl_init();
// echo	$url;exit;
	curl_setopt($ch2, CURLOPT_URL, $url);
	
	curl_setopt($ch2, CURLOPT_TIMEOUT, 180);
	
	//curl_setopt($ch2, CURLOPT_HEADER, 0);
	
	curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
	
	curl_setopt($ch2, CURLOPT_POST, 1);
	
	curl_setopt($ch2, CURLOPT_POSTFIELDS, $requestXml); 

	curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false); 
	
	curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, false);
	
	$httpHeader2 = array("SOAPAction: {$soapAction}","Content-Type: text/xml;charset=UTF-8","Accept-Encoding","gzip,deflate");		
		// logWrite("\n Headers \n ----------- \n".print_r($httpHeader2,true),"LogXML",'Y','a+');
	
	curl_setopt($ch2, CURLOPT_HTTPHEADER, $httpHeader2);
	
	curl_setopt ($ch2, CURLOPT_ENCODING, "gzip,deflate");
	
 $responseJson = curl_exec($ch2);
//  logWrite("\n Request \n ----------- \n".$url.$responseJson,"LogXML",'Y','a+');
	curl_close($ch2);

	if($parseXml == ''){
		$responseJson = $this->formatXML($responseJson);
	}
	else{
		if($responseJson != ''){
			$responseJson = $this->xmlstrToArray($responseJson);
		}
	}
	
	if(!empty($this->_IreferenceId)){
		logWrite("\n".$url." \n\nRequest :- \n---------- \n\n".$requestXml."\n","".$this->_IreferenceId,'a+','N');
	}
	else{
		logWrite("\n".$url." \n\nRequest :- \n---------- \n\n".$requestXml,"LogXML",'a+','Y');
	}
	
	
	if(!empty($this->_IreferenceId)){
		logWrite("\n\nResponse :- \n----------- \n\n".$responseJson."\n","".$this->_IreferenceId,'a+','N');
	}
	else{
		logWrite("\n\nResponse :- \n----------- \n\n".$responseJson,"LogXML",'a+','Y');
	}
	// echo "<pre>";
	// print_r($responseJson);exit;
	return $responseJson;
	}
}
?>