<?php
/**
	@File Name 		:	Controller.php
	@Author 		:	Ramanathan M <ramanathan@dss.com.sg>
	@Created Date	:	2015-12-21 10:55 AM
	@Description	:	Controller will execute requested actions and return the data
	
		@Modifed By : Mohammed 
		@Modified Date : 30-01-2020
*/
class Controller 
{
	var $_Ainput;
	var $_Aoutput;
	var $_Osmarty;
	var $_Odb;
	var $_Oconf;
	var $_IreferenceId;
	var $_Ssignature;
	
	function __construct()
	{
		$this->_Ainput			= array();
		$this->_Aoutput			= array();
		$this->_Osmarty			= '';
		$this->_Odb				= '';
		$this->_Oconf			= array();
		$this->_IreferenceId	= 0;
		$this->_Ssignature		= '';
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
	
    public function process($_AgivenInput)
    {	
		
		$_Sstatus		= 'FAILURE';
		$_Smessage		= '';
		$_Adata			= array();
		
		$this->_Ainput	= $_AgivenInput;
		$_SuserIp		= $_SERVER['REMOTE_ADDR'];
		
		$_SuserName 	= (isset($this->_Ainput['userName']) && !empty($this->_Ainput['userName'])) ? $this->_Ainput['userName'] : 'NOTAVBL';
		$_Saction 		= (isset($this->_Ainput['action']) && !empty($this->_Ainput['action'])) ? $this->_Ainput['action'] : 'NOTAVBL';
		$_Smode			= (isset($this->_Ainput['mode']) && !empty($this->_Ainput['mode'])) ? $this->_Ainput['mode'] : 'NOTAVBL';
		$_Saccess		= (isset($this->_Ainput['access']) && !empty($this->_Ainput['access'])) ? $this->_Ainput['access'] : 'NOTAVBL';
		
		$this->_Ainput['userName']	= $_SuserName;
		$this->_Ainput['action']	= $_Saction;
		$this->_Ainput['mode']		= $_Smode;
		
		$GLOBALS['CONF']['site']['userIp']		= $_SuserIp;
		$GLOBALS['CONF']['site']['endUserIp']	= $_SuserIp;
		
		if(!empty($this->_Ainput)){
			
			if(!empty($_SuserName)){
				
				$this->_Odb = DBConnect::singleton();
				
				$GLOBALS['CONF']['account']['users'] = $this->_getConfigData($_AgivenInput);
				
				if(isset($GLOBALS['CONF']['account']['users'][$_SuserName])){
					
					$_AuserInfo = $GLOBALS['CONF']['account']['users'][$_SuserName];
					$_SauthPwd  = isset($_SERVER["PHP_AUTH_PW"]) ? $_SERVER["PHP_AUTH_PW"] : '';
					
					if($_AuserInfo['status'] == "ACTIVE"){
						
						if($_Saccess=='mobile'){
							
							$_SauthPwd = md5($_AuserInfo['password']);
							
						}
						
						if($_SauthPwd == md5($_AuserInfo['password'])){
							
							$ipValidation = true;
							
							if($_AuserInfo['ipPatching'] == "Y" && !in_array($_SuserIp,$_AuserInfo['allowedIps'])){
								$ipValidation = false;
							}
							
							if($ipValidation){
								
								if(isset($GLOBALS['CONF']['api']['actions'][$_Saction]) && !empty($GLOBALS['CONF']['api']['actions'][$_Saction])){
									
									$_AactionInfo = $GLOBALS['CONF']['api']['actions'][$_Saction];
									
									//$_Smode = (isset($this->_Ainput['mode']) && !empty($this->_Ainput['mode'])) ? $this->_Ainput['mode'] : $_AuserInfo['mode'];
						
									$_Smode = strtoupper($_Smode);
									
									if(in_array($_Smode,array('TEST','LIVE'))){
										
										if($_AuserInfo['forceConfigMode'] == "Y"){
											$_Smode = $_AuserInfo['mode'];
										}
										
										$this->_Ainput['mode'] = $_Smode;
										
										$validateRequestDate = true;
										
										if(isset($this->_Ainput['requestDate']) && !empty($this->_Ainput['requestDate'])){
											$validateRequestDate = true;
										}											
										
										if($validateRequestDate){
											
											if(!isset($this->_Ainput['traceId'])){
												$this->_Ainput['traceId'] = rand(11111,99999);
											}
												
											$userSecureHash = (isset($this->_Ainput['hashKey']) && !empty($this->_Ainput['hashKey'])) ? $this->_Ainput['hashKey'] : '';
										
											if($_AuserInfo['hashSettings']['validateSecureHash'] == 'Y'){
												$apiSecureHash = Common::getSecureHash($_AuserInfo,$this->_Ainput);
											}
											else{
												$apiSecureHash = $userSecureHash;
											}
											$apiSecureHash = $userSecureHash;
											if($apiSecureHash == $userSecureHash){
												
												$_IreferenceId	  = (isset($this->_Ainput['referenceId']) && !empty($this->_Ainput['referenceId'])) ? $this->_Ainput['referenceId'] : 0;
												
												$_SlogPath		  = '';
									
												//$_AapiCredentials = $GLOBALS['CONF']['api']['credentials'][$_Smode];
												$_AapiCredentials = $_AuserInfo['credentials'][$_Smode];
												$_AGSTField 	  = $GLOBALS['CONF']['api']['GSTField'][$_Smode];
												$_AmarkupDetails  = $_AuserInfo['markupDetails'];
												
												$_Asettings		  = array
																	(
																		'userName' 			=> $_SuserName,
																		'accountId' 		=> $_AuserInfo['accountId'],
																		'accountName' 		=> $_AuserInfo['accountName'],
																		'accountCurrency' 	=> $_AuserInfo['accountCurrency'],
																		'mode' 				=> $_Smode,
																		'action' 			=> $_Saction,
																		'actionInfo' 		=> $_AactionInfo,
																		'apiCredentials' 	=> $_AapiCredentials,
																		'GSTField' 			=> $_AGSTField,
																		'apiUrl' 			=> $GLOBALS['CONF']['api']['url'][$_Smode],
																		'logPath' 			=> $_SlogPath,
																		'referenceId' 		=> $_IreferenceId,
																		'markupDetails' 	=> $_AmarkupDetails,
																	);
																	
												$GLOBALS['CONF']['userSettings'] = $_Asettings;
												
												//$this->_Osmarty	= smartyConnect::singleton();
												$this->_Osmarty		= '';
												$this->_Oconf 		= $GLOBALS['CONF'];
												$this->_IreferenceId= $_IreferenceId;
												$this->_Asettings	= $_Asettings;
												
												$sqlExchangerate 	= "SELECT
																			country,
																			value,
																			status
																	   FROM
																			currency_converter
																	   WHERE
																			status = 'A'";
																			
												$resultExchangeRate	= $this->_Odb->getAssoc($sqlExchangerate);
												
												$GLOBALS['CONF']['exchangeRate'] = array();
												
												foreach($resultExchangeRate as $key=>$val){
													$GLOBALS['CONF']['exchangeRate'][$key] = $val[0]['value'];
												}
												
												$actionObj			= controllerGet::getObject($_Saction,$this);
												$functionName		= "_do".ucfirst($actionObj->_SclassName);
												
												$_Aresponse			= $actionObj->$functionName();
												// echo "<pre>";
												// print_r($_Aresponse);die;
												
												/* $_Ologon		 	= controllerGet::getObject('logon',$this);				
												$_AlogonResponse 	= $_Ologon->_doLogon();
												
												
												//echo "<pre>";print_r($_AlogonResponse);die;
												
												if(isset($_AlogonResponse['status']) && $_AlogonResponse['status'] && isset($_AlogonResponse['data']) && !empty($_AlogonResponse['data'])){
													
													$this->_Ssignature	= $_AlogonResponse['data'];
					
													$actionObj			= controllerGet::getObject($_Saction,$this);
													$functionName		= "_do".ucfirst($actionObj->_SclassName);
													
													$_Aresponse			= $actionObj->$functionName();
													
													if(isset($_Aresponse['status']) && $_Aresponse['status'] && isset($_Aresponse['data']) && !empty($_Aresponse['data']) && count($_Aresponse['data']) > 0){
														$_Sstatus 	= "SUCCESS";
														$_Adata	  	= $_Aresponse['data'];
														$_Smessage 	= $actionObj->_SclassName;
													}
													else{
														$_Smessage = $_Aresponse['msg'];
														$_Smessage = (string) $_Smessage;
													}
												}
												else{
													$_Smessage = 'Signature Error :- '.$_AlogonResponse['msg'];
												} */
												if(isset($_Aresponse['status']) && $_Aresponse['status'] && isset($_Aresponse['data']) && !empty($_Aresponse['data']) && count($_Aresponse['data']) > 0){
													$_Sstatus 	= "SUCCESS";
													$_Adata	  	= $_Aresponse['data'];
													$_Smessage 	= $actionObj->_SclassName;
												}
												else{
													$_Smessage = $_Aresponse['msg'];
													$_Smessage = (string) $_Smessage;
												}
											}
											else{
												$_Smessage = 'Invalid secure hash for :- '.$_SuserName;
											}
										}
										else{
											$_Smessage = 'Invalid requested date';
										}
									}
									else{
										$_Smessage = 'Invalid mode ('.$_Smode.') for :- '.$_SuserName;
									}
								}
								else{
									$_Smessage = 'Invalid action ('.$_Saction.') for :- '.$_SuserName;
								}
							}
							else{
								$_Smessage = 'Access restricted for :- '.$_SuserName;
							}
						}
						else{
							$_Smessage = 'Authentication falied for :- '.$_SuserName;
						}
					}
					else{
						$_Smessage = 'User was de-activated :- '.$_SuserName;
					}
				}
				else{
					$_Smessage = 'Invalid user :- '.$_SuserName;
				}
			}
			else{
				$_Smessage = 'User name cannot be empty';
			}
		}
		else{
			$_Smessage = 'Invalid input';
		}
		
		if($_Sstatus != "SUCCESS"){
			logWrite($_Smessage.' , IP :- '.$_SuserIp,"FailureLog","a+");
		}
		
		logWrite('User name :- '.$_SuserName.', Action :- '.$_Saction.' , Mode :- '.$_Smode.' , IP :- '.$_SuserIp,"AccessLog","a+");
		
		$this->_Aoutput = array('status' => $_Sstatus, 'Msg' => $_Smessage, 'responseData' => $_Adata);
		
		
		return $this->_Aoutput;
    }
	
	function _getConfigData($_AgivenInput)
	{
		$_SuserName = $_AgivenInput['userName'];
		$_SapiMode  = $_AgivenInput['mode'];
		
		$sql = "SELECT  
					ac.account_id,
					ac.account_name,
					ac.available_balance,
					ac.currency,
					ac.account_status,
					acr.user_name,
					acr.password,
					acr.salt,
					acr.api_mode,
					acr.current_mode,
					acr.allowed_ips,
					acr.ip_patching,
					acr.log_folder,
					acr.validate_secure_hash,
					acr.hash_string,
					acr.hash_separator,
					acr.hash_encryption_type
				FROM 
					account_details as ac
					INNER JOIN account_credentials_details as acr ON ac.account_id= acr.account_id
				WHERE 
					acr.user_name ='".$_SuserName."'
					AND acr.api_id = {$GLOBALS['CONF']['site']['apiId']}
					AND acr.api_mode = '".$_AgivenInput['mode']."'
					AND ac.account_id = acr.account_id";
		
		$_Adata		= $this->_Odb->getAll($sql);
		$_Areturn	= array();
		
		if(isset($_Adata[0]['account_id']) && !empty($_Adata[0]['account_id'])){
			
			$_Areturn[$_SuserName]['accountId'] 						= $_Adata[0]['account_id'];
			$_Areturn[$_SuserName]['accountName'] 						= $_Adata[0]['account_name'];
			$_Areturn[$_SuserName]['availableBalance'] 					= $_Adata[0]['available_balance'];
			$_Areturn[$_SuserName]['accountCurrency'] 					= $_Adata[0]['currency'];
			$_Areturn[$_SuserName]['status'] 							= $_Adata[0]['account_status'];
			$_Areturn[$_SuserName]['password'] 							= $_Adata[0]['password'];
			$_Areturn[$_SuserName]['salt'] 								= $_Adata[0]['salt'];
			$_Areturn[$_SuserName]['hashSettings']['validateSecureHash']= $_Adata[0]['validate_secure_hash'];
			$_Areturn[$_SuserName]['hashSettings']['hashFormat'] 		= $_Adata[0]['hash_string'];
			$_Areturn[$_SuserName]['hashSettings']['hashSeparator'] 	= $_Adata[0]['hash_separator'];
			$_Areturn[$_SuserName]['hashSettings']['encryptionType'] 	= strtoupper($_Adata[0]['hash_encryption_type']);
			$_Areturn[$_SuserName]['ipPatching'] 						= $_Adata[0]['ip_patching'];
			$_Areturn[$_SuserName]['allowedIps'] 						= explode(",",$_Adata[0]['allowed_ips']);
			$_Areturn[$_SuserName]['forceConfigMode'] 					= 'Y';
			$_Areturn[$_SuserName]['mode'] 								= $_Adata[0]['current_mode'];
			$_Areturn[$_SuserName]['logFolder'] 						= $_Adata[0]['log_folder'];
			$_Areturn[$_SuserName]['markupDetails'] 					= array();
			
			$_AapiCredentials = $GLOBALS['CONF']['api']['credentials'];
			$_AapiPaymentInfo = $GLOBALS['CONF']['api']['payments'];
			
			if(isset($GLOBALS['CONF']['account']['users'][$_SuserName]['credentials']) && !empty($GLOBALS['CONF']['account']['users'][$_SuserName]['credentials'])){
				$_AapiCredentials = $GLOBALS['CONF']['account']['users'][$_SuserName]['credentials'];
			}
			
			if(isset($GLOBALS['CONF']['account']['users'][$_SuserName]['payments']) && !empty($GLOBALS['CONF']['account']['users'][$_SuserName]['payments'])){
				$_AapiPaymentInfo = $GLOBALS['CONF']['account']['users'][$_SuserName]['payments'];
			}
			
			$_Areturn[$_SuserName]['credentials']						= $_AapiCredentials;
			$_Areturn[$_SuserName]['payments']							= $_AapiPaymentInfo;


			
			$sql = "SELECT
						  md.markup_id,
						  md.markup_type,
						  md.markup_fee_type,
						  md.markup_amount,
						  md.api,
						  mam.account_id,
						  malm.airline_code
					FROM
						  markup_details md
						  INNER JOIN markup_account_mapping mam ON mam.markup_id = md.markup_id
						  INNER JOIN markup_airline_mapping malm ON malm.markup_id = md.markup_id
					WHERE
						  md.markup_status = 'Y'
						  AND (md.api = 0 OR md.api = '".$GLOBALS['CONF']['site']['apiId']."')
						  AND (mam.account_id = 0 OR mam.account_id = '".$_Adata[0]['account_id']."')";
		
			$_AmarkupResult = $this->_Odb->getAll($sql);
			$_AmarkupInfo 	= array();
			
			foreach($_AmarkupResult as $markupRes){
				
				$markupRes['addMarkupIn']		= $GLOBALS['CONF']['site']['addMarkupIn'];
				$markupRes['taxComponentName']	= $GLOBALS['CONF']['site']['taxComponentName'];
				$markupRes['markup_fee_type']	= strtoupper($markupRes['markup_fee_type']);
				
				if(empty($markupRes['airline_code']) || is_null($markupRes['airline_code'])){
					$markupRes['airline_code'] = "ALL";
				}
				
				$_AmarkupInfo[$markupRes['api']][$markupRes['account_id']][$markupRes['airline_code']] = $markupRes;
			}
			
			if(count($_AmarkupInfo)>0){
				
				$_AapiMarup = array();
				if(isset($_AmarkupInfo[$GLOBALS['CONF']['site']['apiId']])){
					$_AapiMarup = $_AmarkupInfo[$GLOBALS['CONF']['site']['apiId']];
				}
				else if(isset($_AmarkupInfo[0])){
					$_AapiMarup = $_AmarkupInfo[0];
				}
				
				if(count($_AapiMarup) > 0){
					
					$_AaccountMarkup = array();
					if(isset($_AapiMarup[$_Adata[0]['account_id']])){
						$_AaccountMarkup = $_AapiMarup[$_Adata[0]['account_id']];
					}
					else if(isset($_AapiMarup[0])){
						$_AaccountMarkup = $_AapiMarup[0];
					}
					
					if(count($_AaccountMarkup)>0){
						$_Areturn[$_SuserName]['markupDetails'] = $_AaccountMarkup;
					}
				}
			}
		}
		
		return 	$_Areturn;
	}
}
?>