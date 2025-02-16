<?php
/**
	@File Name 		:	Common.php
	@Author 		:	Ramanathan M <ramanathan@dss.com.sg>
	@Created Date	:	2015-12-21 10:55 AM
	@Description	:	All commonly using function will be here and it should be accessable through out the application
**/
class Common
{
	public static function getSecureHash($_AuserInfo,$_Ainput)
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
	
	public function xmlstrToArray($xmlstr)
	{
		$doc = new DOMDocument();
		$doc->loadXML($xmlstr);
		return $this->domNodeToArray($doc->documentElement);
	}
	
	function domNodeToArray($node) 
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
	
	public function getRoundedFare($fare=0,$currencyCode = null)
	{
		if(!$fare)
			return $fare;
		
		$roundTo = $GLOBALS['CONF']['site']['defaultRoundOff'];
		
		if(!is_null($currencyCode) && isset($GLOBALS['CONF']['site']['currencyRoundOff'][$currencyCode])){
			$roundTo = $GLOBALS['CONF']['site']['currencyRoundOff'][$currencyCode];
		}
		
		return round($fare,$roundTo);
	}
}
?>