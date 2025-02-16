<?php
/**
	@File Name 		:	securityTokenDetails.php
	@Author 		:	<authorName> <authorEmail>
	@Created Date	:	2017-01-19 07:25:56
	@Description	:	CURD for security_token_details
*/
class securityTokenDetails
{
	var $_IsecurityTokenId;
	var $_IreferenceId;
	var $_SsecurityToken;
	var $_Sstatus;
	var $_DcreatedDate;
	
	function __construct()
	{
		$this->_IsecurityTokenId = 0;
		$this->_IreferenceId = 0;
		$this->_SsecurityToken = '';
		$this->_Sstatus = '';
		$this->_DcreatedDate = '0000-00-00 00:00:00';
		$this->_DcreatedDateCheck = '';
		$this->_SorderBy = '';
	}

	public static function &singleton()
	{
		static $instance;
		// If the instance is not there, create one
		if (!isset($instance)) {
			$instance = new securityTokenDetails();
		}
		return $instance;
	}

	function _insert()
	{
		$sql = "INSERT INTO {$GLOBALS['CONF']['table']['securityTokenDetails']}
		(
			security_token_id,
			account_id,
			reference_id,
			security_token,
			status,
			created_date
		)
		VALUES
		(
			".$this->_IsecurityTokenId.",
			".$this->_SaccountId.",
			'".mysql_real_escape_string($this->_IreferenceId)."',
			'".mysql_real_escape_string($this->_SsecurityToken)."',
			'".mysql_real_escape_string($this->_Sstatus)."',
			NOW()
		)";
		return $this->_Odb->executeQuery($sql);
	}

	function _select()
	{
		$sql ="SELECT
					security_token_id,
					security_token,
					status,
					created_date
			  FROM 
					{$GLOBALS['CONF']['table']['securityTokenDetails']}
			  WHERE 
					1 ";
		$condition = " AND account_id = ".$this->_SaccountId." AND";
		
		if(strlen($this->_IreferenceId) > 0){
			$sql = $sql.$condition." reference_id = '".$this->_IreferenceId."' ";
			$condition = " AND ";
		}
			
		if($this->_IsecurityTokenId != 0){
			$sql = $sql.$condition." security_token_id = ".$this->_IsecurityTokenId." ";
			$condition = " AND ";
		}
		
		
		
		if($this->_SsecurityToken != ''){
			$sql = $sql.$condition." security_token = '".mysql_real_escape_string($this->_SsecurityToken)."' ";
			$condition = " AND ";
		}
		
		if($this->_Sstatus != ''){
			$sql = $sql.$condition." status = '".mysql_real_escape_string($this->_Sstatus)."' ";
			$condition = " AND ";
		}
		
		if($this->_DcreatedDate != '' && $this->_DcreatedDate != '0000-00-00 00:00:00'){
			$sql = $sql.$condition." created_date = '".$this->_DcreatedDate."' ";
			$condition = " AND ";
		}
		
		if($this->_DcreatedDateCheck != ''){
			$sql = $sql.$condition." ".$this->_DcreatedDateCheck;
			$condition = " AND ";
		}
		
		if($this->_SorderBy != ''){
			$sql = $sql." ".$this->_SorderBy;
		}
		
		//echo $sql;die;
		#logWrite($sql,"TEST","w");
		//echo $sql;die;
		return $this->_Odb->getAll($sql);
	}

	function _update()
	{
		$sql = "UPDATE {$GLOBALS['CONF']['table']['securityTokenDetails']} SET ";
		$comma = "  ";
		if($this->_IsecurityTokenId != 0){
			$sql = $sql.$comma." security_token_id = ".$this->_IsecurityTokenId." ";
			$comma = " , ";
		}
		
		if($this->_SsecurityToken != ''){
			$sql = $sql.$comma." security_token = '".mysql_real_escape_string($this->_SsecurityToken)."' ";
			$comma = " , ";
		}
		
		if($this->_Sstatus != ''){
			$sql = $sql.$comma." status = '".mysql_real_escape_string($this->_Sstatus)."' ";
			$comma = " , ";
		}
		
		if($this->_DcreatedDate != '' && $this->_DcreatedDate != '0000-00-00 00:00:00'){
			$sql = $sql.$comma." created_date = '".$this->_DcreatedDate."' ";
			$comma = " , ";
		}
		
		$sql = $sql." WHERE  ";
		$condition = " ";
		if($this->_IsecurityTokenId != 0){
			$sql = $sql.$condition." security_token_id = ".$this->_IsecurityTokenId." ";
			$condition = " AND ";
		}
		
		if($this->_SsecurityToken != ''){
			$sql = $sql.$condition." security_token = '".mysql_real_escape_string($this->_SsecurityToken)."' ";
			$condition = " AND ";
		}
		
		/*if($this->_Sstatus != ''){
			$sql = $sql.$condition." status = '".mysql_real_escape_string($this->_Sstatus)."' ";
			$condition = " AND ";
		}
		
		if($this->_DcreatedDate != '' && $this->_DcreatedDate != '0000-00-00 00:00:00'){
			$sql = $sql.$condition." created_date = '".$this->_DcreatedDate."' ";
			$condition = " AND ";
		}*/
		
		return $this->_Odb->executeQuery($sql);
	}

	function _delete()
	{
		$sql ="DELETE
				FROM
					{$GLOBALS['CONF']['table']['securityTokenDetails']}
				WHERE  
					";
		$condition = "  ";
		if($this->_IsecurityTokenId != 0){
			$sql = $sql.$condition." security_token_id = ".$this->_IsecurityTokenId." ";
			$condition = " AND ";
		}
		
		if($this->_SsecurityToken != ''){
			$sql = $sql.$condition." security_token = '".mysql_real_escape_string($this->_SsecurityToken)."' ";
			$condition = " AND ";
		}
		
		if($this->_Sstatus != ''){
			$sql = $sql.$condition." status = '".mysql_real_escape_string($this->_Sstatus)."' ";
			$condition = " AND ";
		}
		
		if($this->_DcreatedDate != '' && $this->_DcreatedDate != '0000-00-00 00:00:00'){
			$sql = $sql.$condition." created_date = '".$this->_DcreatedDate."' ";
			$condition = " AND ";
		}
		
		return $this->_Odb->executeQuery($sql);
	}
}
?>