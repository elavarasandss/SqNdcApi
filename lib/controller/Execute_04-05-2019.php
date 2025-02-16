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
		if($this->_SwebserviceMethod  == 'SOAP'){
			 return $this->_doSoapAction();
		}
		
		if($this->_SwebserviceMethod  == 'CURL'){
			 return $this->_doCurlAction();
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
			logWrite("\n".$url." \n\nRequest :- \n---------- \n\n".$postData."\n","LogXML-".$this->_IreferenceId,'a+','N');
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
			logWrite("\n\nResponse :- \n----------- \n\n".$response."\n","LogXML-".$this->_IreferenceId,'a+','N');
		}
		else{
			logWrite("\n\nResponse :- \n----------- \n\n".$response,"LogXML",'a+','Y');
		}
		
		return $_Aresponse;
	}
	public function getDefaultBookingFee($api,$acc,$airline)
    {
		/* $api = 11;
		$acc = 17;
		$airline ='6E'; */
        $query = "SELECT COALESCE(booking_fee,0) FROM booking_fee WHERE ((booking_api = '".$api."' AND booking_account = '".$acc."' AND airlines LIKE '%".$airline."%')  OR (booking_api = '0' AND booking_account = '".$acc."') OR (booking_api = '".$api."' AND booking_account = '0' AND airlines = '0') OR (booking_api = '0' AND booking_account = '0' AND airlines = '0'))  AND status = 1 ORDER BY IF(booking_api = 0,booking_api,booking_account) DESC";
		
        $_IbookingFee = $this->_Odb->getOne($query);
		
		$_Ares = array();
        $_Ares['booking_fee'] = 0;
			
        if(!empty($_IbookingFee)){
            $_Ares['booking_fee'] = $_IbookingFee;
        }
		
        return $_Ares;
    }
}
?>