<?php
/**
	@File Name 		:	logon.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	logon service
*/
class logon extends Execute
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
		$this->_SrequestUrl = $this->_Oconf['userSettings']['apiUrl']['logon'];
	}
	
    public function _doLogon()
	{
		$_Bstatus  = true;
		$_Smessage = '';
		$_Adata	   = array();
		$_Areturn  = array();
		$_StokenId = '';
		
		//echo "<pre>";
		
		//print_r($this->_Oconf['userSettings']['accountId']);die;
		$this->_modifyData();
		$this->_setData();
		
		$securityTokenDetails 						= model::getObject('securityTokenDetails','NEW');
		#$securityTokenDetails->_IreferenceId		= $this->_IreferenceId;
		$securityTokenDetails->_IreferenceId		= '';
		$securityTokenDetails->_Sstatus 			= 'A';
		#$securityTokenDetails->_DcreatedDateCheck 	= " created_date >= SUBTIME(NOW(),'0 0:15:0') ";
		$securityTokenDetails->_DcreatedDateCheck 	= " DATE_FORMAT(created_date, '%Y-%m-%d') = DATE_FORMAT(NOW(), '%Y-%m-%d') ";
		$securityTokenDetails->_SorderBy 			= " ORDER BY security_token_id DESC LIMIT 1 ";
		
		//print_r($securityTokenDetails);
		
		if($this->_Oconf['userSettings']['accountId']==22){
					
			$securityTokenDetails->_SaccountId			= $this->_Oconf['userSettings']['accountId'];
		}
		else{
			$securityTokenDetails->_SaccountId			= 0;
		}
				
		
		$_AsecurityTokenDetails 					= $securityTokenDetails->_select();
		
		if(count($_AsecurityTokenDetails) > 0 && !isset($_AsecurityTokenDetails[0])){
			$_AsecurityTokenDetails = array($_AsecurityTokenDetails);
		}
		
		if(isset($_AsecurityTokenDetails[0]['security_token']) && !empty($_AsecurityTokenDetails[0]['security_token'])){
			$_StokenId = $_AsecurityTokenDetails[0]['security_token'];
		}
		else{
			
			$logonResp = $this->_executeService();
			
			if(isset($logonResp['Status']) && $logonResp['Status'] == 1 && isset($logonResp['TokenId']) && !empty($logonResp['TokenId'])){
				
				$_StokenId = $logonResp['TokenId'];
				
				$securityTokenDetails 						= model::getObject('securityTokenDetails','NEW');
				$securityTokenDetails->_SsecurityToken		= $_StokenId;
				
				if($this->_Oconf['userSettings']['accountId']==22){
					
					$securityTokenDetails->_SaccountId			= $this->_Oconf['userSettings']['accountId'];
				}
				else{
					$securityTokenDetails->_SaccountId			= 0;
				}
				
				$securityTokenDetails->_IreferenceId		= $this->_IreferenceId;
				$securityTokenDetails->_Sstatus 			= 'A';
				$securityTokenDetails->_DcreatedDate 		= date('Y-m-d H:i:s');
				
				$securityTokenDetails->_insert();
			}
			else{
				$_Bstatus = false;
				if(isset($logonResp['Error']['ErrorMessage'])  && !empty($logonResp['Error']['ErrorMessage'])){
					$_Smessage = $logonResp['Error']['ErrorMessage'];
				}
				else{
					$_Smessage = 'Unknown Error';
				}
			}	
		}
		
		$_Areturn = array
					(
						'status' => $_Bstatus,
						'data'   => $_StokenId,
						'msg'    => $_Smessage,
					);
					
		return $_Areturn;
	}
}
?>