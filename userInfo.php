<?php

	function getUserInfo()
	{
		$_AuserInfo 				= array();
/* 	
		$_AuserInfo['userName']		= 'TMTB2C';
		$_AuserInfo['password']		= 'tmtb2c@2win!';
		$_AuserInfo['salt']			= 'tmtb2c@2win!!'; */
		
		// $_AuserInfo['userName']		= 'GBAB2C';
		// $_AuserInfo['password']		= 'b2c@2win!';
		// $_AuserInfo['salt']			= 'b2c@gba!2win';

		$_AuserInfo['userName']		= 'PREVEENTRAVELS'; 
		$_AuserInfo['password']		= 'preveentravelssq2win';
		$_AuserInfo['salt']			= 'preveentravelssq@salt';
		
		$_AuserInfo['hashSettings']	= array
										(
											'hashFormat'		=> 'userName|action|mode|ADT|CHD|INF|currencyCode|pnr|sectorInfo|salt',
											'hashSeparator'		=> '|',
											'encryptionType'	=> 'MD5',
										);
		
		return $_AuserInfo;
	}
	
	function getSecureHash($_AuserInfo,$_Ainput)
	{	
		$_ShashSeparator	= $_AuserInfo['hashSettings']['hashSeparator'];
		$_ShasFormat		= $_AuserInfo['hashSettings']['hashFormat'];
		$_SencryptionType	= $_AuserInfo['hashSettings']['encryptionType'];

		$_SinpStr		= '';
		$_Sappened		= '';
		$_AhasExplode	= explode($_ShashSeparator,$_ShasFormat);
		
		foreach($_AhasExplode as $_Ikey=>$_Sval){
			
			$_SinpVal = $_Sval.':';
			
			if($_Sval == 'salt'){
				$_SinpVal .= $_AuserInfo['salt'];
			}
			else if($_Sval == 'sectorInfo'){
				if(isset($_Ainput[$_Sval])){
					foreach($_Ainput[$_Sval] as $_IsecKey=>$_SsecVal){
						$_SinpVal .= $_SsecVal['origin'].$_SsecVal['destination'].$_SsecVal['departureDate'];
					}
				}
			}
			else{
				if(isset($_Ainput[$_Sval])){
					$_SinpVal .= $_Ainput[$_Sval];
				}
			}
			
			$_SinpStr	.= $_Sappened.$_SinpVal;
			$_Sappened   = $_ShashSeparator;
		}
		
		$_SencString = '';
				
		switch ($_SencryptionType){			
			case  'MD5':		
				$_SencString = md5($_SinpStr);	
				break;
			default:
				$_SencString = '';
		}
		
		return $_SencString;
	}

?>