<?php
/**
	@File Name 		:	getSsr.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	getSsr service
*/
class getSsr extends Execute
{
	
	function __construct()
	{
		parent::__construct();
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
	
	public function _modifyData()
	{
		$this->_SrequestUrl = $this->_Oconf['userSettings']['apiUrl']['getSsr'];
	}
	
    public function _doGetSsr()
	{
		$_Bstatus  = true;
		$_Smessage = '';
		$_Adata	   = array();
		$_Areturn  = array();
		
		$this->_modifyData();
		$this->_setData();
		
		$_AssrResult = $this->_executeService();
		
		$_AssrResponse = array();
		
		if(isset($_AssrResult['Response']['ResponseStatus']) && $_AssrResult['Response']['ResponseStatus'] == 1){
			
			$_AssrResult = $_AssrResult['Response'];
			
			if(isset($_AssrResult['Baggage']) && !empty($_AssrResult['Baggage'])){
				
				foreach($_AssrResult['Baggage'] as $bagMainKey=>$bagMainVal){
					
					foreach($bagMainVal as $bagSubKey=>$bagSubVal){
						
						if(!isset($_AssrResponse['Baggage'])){
							$_AssrResponse['Baggage'] = array();
						}
						
						$_SapiCurrency		= $bagSubVal['Currency'];
						$_IapiExchangeRate 	= isset($GLOBALS['CONF']['exchangeRate'][$_SapiCurrency]) ? $GLOBALS['CONF']['exchangeRate'][$_SapiCurrency] : 1;
						
						$_SuserCurrency 	= $this->_Ainput['currencyCode'];
						$_IuserExchangeRate	= isset($GLOBALS['CONF']['exchangeRate'][$_SuserCurrency]) ? $GLOBALS['CONF']['exchangeRate'][$_SuserCurrency] : 1;
						
						$_SdefaultCurrency 	= $GLOBALS['CONF']['site']['defaultCurrency'];
						
						$_IapiAmount = $bagSubVal['Price'];
						
						$_SbaggageCodeCheck = $bagSubVal['Code'];
						$_SbaggageCodeCheck = strtoupper(str_replace(" ","",$_SbaggageCodeCheck));
						
						if($_SbaggageCodeCheck != "" && $_SbaggageCodeCheck != "NOBAGGAGE"){
						//if($_IapiAmount > 0 && $_SbaggageCodeCheck != "" && $_SbaggageCodeCheck != "NOBAGGAGE"){
							
							// Default fares
							
							$_IdefaultAmount	= $_IapiAmount / $_IapiExchangeRate;
							$_IdefaultAmount	= Common::getRoundedFare($_IdefaultAmount,$_SdefaultCurrency);
							
							// User fares
							
							$_IuserAmount		= $_IdefaultAmount * $_IuserExchangeRate;
							$_IuserAmount		= Common::getRoundedFare($_IuserAmount,$_SuserCurrency);
							
							$_AtempData = array();
							
							$_AtempData['origin']			= $bagSubVal['Origin'];
							$_AtempData['destination']		= $bagSubVal['Destination'];
							$_AtempData['code']				= $bagSubVal['Code'];
							$_AtempData['wayType']			= $bagSubVal['WayType'];
							$_AtempData['description']		= "Baggage ".$bagSubVal['Weight']." Kg";
							$_AtempData['amount']			= $_IuserAmount;
							$_AtempData['orgDescription']	= $bagSubVal['Description'];
							$_AtempData['weight']			= $bagSubVal['Weight'];
							$_AtempData['farRefKey']		= base64_encode($_IdefaultAmount);
							
							if($_SapiCurrency == $_SuserCurrency){
								$_AtempData['amount'] = $_IapiAmount;
							}
							
							$_SsectorKey = $_AtempData['origin'].$_AtempData['destination'];
							
							if(!isset($_AssrResponse['Baggage'][$_SsectorKey])){
								$_AssrResponse['Baggage'][$_SsectorKey] = array();
							}
							
							$_AssrResponse['Baggage'][$_SsectorKey][] = $_AtempData;
						}
					}					
				}
			}
			
			if((isset($_AssrResult['MealDynamic']) && !empty($_AssrResult['MealDynamic'])) || (isset($_AssrResult['Meal']) && !empty($_AssrResult['Meal']))){
			//if(isset($_AssrResult['MealDynamic']) && !empty($_AssrResult['MealDynamic'])){
				
				$_AmealData = array();
				$_SnonLcc   = "N";
				
				if(isset($_AssrResult['MealDynamic']) && !empty($_AssrResult['MealDynamic'])){
					$_AmealData = $_AssrResult['MealDynamic'];
				}
				if(isset($_AssrResult['Meal']) && !empty($_AssrResult['Meal'])){
					$_AmealData = $_AssrResult['Meal'];
					$_AmealData = array($_AmealData);
					$_SnonLcc   = "Y";
				}
				
				foreach($_AmealData as $mealMainKey=>$mealMainVal){
					
					foreach($mealMainVal as $mealSubKey=>$mealSubVal){
						
						if(!isset($_AssrResponse['Meals'])){
							$_AssrResponse['Meals'] = array();
						}
						
						if($_SnonLcc == "Y"){
							$mealSubVal['AirlineDescription'] = $mealSubVal['Description'];
						}
						
						$_SapiCurrency		= $mealSubVal['Currency'];
						$_IapiExchangeRate 	= isset($GLOBALS['CONF']['exchangeRate'][$_SapiCurrency]) ? $GLOBALS['CONF']['exchangeRate'][$_SapiCurrency] : 1;
						
						$_SuserCurrency 	= $this->_Ainput['currencyCode'];
						$_IuserExchangeRate	= isset($GLOBALS['CONF']['exchangeRate'][$_SuserCurrency]) ? $GLOBALS['CONF']['exchangeRate'][$_SuserCurrency] : 1;
						
						$_SdefaultCurrency 	= $GLOBALS['CONF']['site']['defaultCurrency'];
						
						$_IapiAmount = isset($mealSubVal['Price']) ? $mealSubVal['Price'] : 0;
						
						$_SmealCodeCheck = $mealSubVal['Code'];
						$_SmealCodeCheck = strtoupper(str_replace(" ","",$_SmealCodeCheck));
						
						if($_SmealCodeCheck != "" && $_SmealCodeCheck != "NOMEAL"){
						//if($_IapiAmount > 0 && $_SmealCodeCheck != "" && $_SmealCodeCheck != "NOMEAL"){
							
							// Default fares
							
							$_IdefaultAmount	= $_IapiAmount / $_IapiExchangeRate;
							$_IdefaultAmount	= Common::getRoundedFare($_IdefaultAmount,$_SdefaultCurrency);
							
							// User fares
							
							$_IuserAmount		= $_IdefaultAmount * $_IuserExchangeRate;
							$_IuserAmount		= Common::getRoundedFare($_IuserAmount,$_SuserCurrency);
							
							$_AtempData = array();
							
							$_AtempData['origin']			= isset($mealSubVal['Origin']) ? $mealSubVal['Origin'] : 'ALL';
							$_AtempData['destination']		= isset($mealSubVal['Destination']) ? $mealSubVal['Destination'] : 'ALL';
							$_AtempData['code']				= $mealSubVal['Code'];
							$_AtempData['wayType']			= isset($mealSubVal['WayType']) ? $mealSubVal['WayType'] : '';
							$_AtempData['description']		= isset($mealSubVal['AirlineDescription']) ? $mealSubVal['AirlineDescription'] : $mealSubVal['Code'];
							$_AtempData['amount']			= $_IuserAmount;
							$_AtempData['orgDescription']	= isset($mealSubVal['Description']) ? $mealSubVal['Description'] : $mealSubVal['Code'];
							$_AtempData['farRefKey']		= base64_encode($_IdefaultAmount);
							$_AtempData['preference']		= $_SnonLcc;
							
							if($_SapiCurrency == $_SuserCurrency){
								$_AtempData['amount'] = $_IapiAmount;
							}
							
							$_SsectorKey = $_AtempData['origin'].$_AtempData['destination'];
							
							if(!isset($_AssrResponse['Meals'][$_SsectorKey])){
								$_AssrResponse['Meals'][$_SsectorKey] = array();
							}
							
							$_AssrResponse['Meals'][$_SsectorKey][] = $_AtempData;
						}
					}					
				}
			}
			
			if(isset($_AssrResult['SeatDynamic']) && !empty($_AssrResult['SeatDynamic'])){
				//$_AssrResponse['Seat'] = $_AssrResult['SeatDynamic'];
				
				foreach($_AssrResult['SeatDynamic'] as $seatMainKey=>$seatMainVal){
					
					foreach($seatMainVal['SegmentSeat'] as $setSegKey=>$seatSegVal){
						
						$_AseatMapData = $seatSegVal['RowSeats'];
						
						$_SflightIndex = $_AseatMapData[0]['Seats'][0]['AirlineCode'].$_AseatMapData[0]['Seats'][0]['FlightNumber'].$_AseatMapData[0]['Seats'][0]['Origin'].$_AseatMapData[0]['Seats'][0]['Destination'];
						
						foreach($_AseatMapData as $seatMapKey=>&$seatMapVal){
							
							foreach($seatMapVal['Seats'] as $setsKey=>&$seatsVal){
								
								$_SapiCurrency		= $seatsVal['Currency'];
								$_IapiExchangeRate 	= isset($GLOBALS['CONF']['exchangeRate'][$_SapiCurrency]) ? $GLOBALS['CONF']['exchangeRate'][$_SapiCurrency] : 1;
								
								$_SuserCurrency 	= $this->_Ainput['currencyCode'];
								$_IuserExchangeRate	= isset($GLOBALS['CONF']['exchangeRate'][$_SuserCurrency]) ? $GLOBALS['CONF']['exchangeRate'][$_SuserCurrency] : 1;
								
								$_SdefaultCurrency 	= $GLOBALS['CONF']['site']['defaultCurrency'];
								
								$_IapiPrice		= $seatsVal['Price'];
								
								// Default fares
							
								$_IdefaultPrice	= $_IapiPrice / $_IapiExchangeRate;
								$_IdefaultPrice	= Common::getRoundedFare($_IdefaultPrice,$_SdefaultCurrency);
								
								// User fares
								
								$_IuserPrice		= $_IdefaultPrice * $_IuserExchangeRate;
								$_IuserPrice		= Common::getRoundedFare($_IuserPrice,$_SuserCurrency);
								
								$seatsVal['Price']  	= $_IuserPrice;
								$seatsVal['farRefKey']	= base64_encode($_IdefaultPrice);
								
								if($_SapiCurrency == $_SuserCurrency){
									$seatsVal['Price'] = $_IapiPrice;
								}
							
								if(!isset($seatsVal['Text'])){
									$seatsVal['Text'] = '';
								}
								
								unset($seatsVal['Currency']);
							}							
						}
						
						if(!isset($_AssrResponse['Seat'])){
							$_AssrResponse['Seat'] = array();
						}
						
						if(!isset($_AssrResponse['Seat'][$_SflightIndex])){
							$_AssrResponse['Seat'][$_SflightIndex] = array();
						}
						
						$_AssrResponse['Seat'][$_SflightIndex] = $_AseatMapData;
					}
					
				}
			}
		}
		else{
			$_Bstatus = false;
			
			if(isset($_AssrResult['Response']['Error']['ErrorMessage']) && !empty($_AssrResult['Response']['Error']['ErrorMessage'])){
				$_Smessage = $_AssrResult['Response']['Error']['ErrorMessage'];
			}
			else{
				$_Smessage = 'No SSR found';
			}
		}
		
		$_Areturn = array
					(
						'status' => $_Bstatus,
						'data'   => $_AssrResponse,
						'msg'    => $_Smessage,
					);
					
		return $_Areturn;
	}
}
?>