<?php
/**
	@File Name 		:	updatePrice.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	updatePrice service
*/
class updatePrice extends Execute
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
		$this->_Ainput['flightTraceId']		= '';
		$this->_Ainput['flightResultIndex'] = '';
	}
	
    public function _doUpdatePrice()
	{
		$_Bstatus	= true;
		$_Smessage	= '';
		$_Adata		= array();
		$_Areturn	= array();
		$_IpaxCount	= $this->_Ainput['ADT'] + $this->_Ainput['CHD'];
		
		$this->_modifyData();
		
		$_BisSpecialReturn = false;
		
		foreach($this->_Ainput['flightDetails'] as $fKey=>$fVal){
			
			$_AjourneySellKey = explode("@@@",$fVal['journeySellKey']);
			
			if(isset($_AjourneySellKey[2]) && !empty($_AjourneySellKey[2])){
				$_AflightRefultIndex[] = $_AjourneySellKey[2];
			}
			
			if(isset($_AjourneySellKey[3]) && !empty($_AjourneySellKey[3])){
				$_SflightTraceId = $_AjourneySellKey[3];
			}
			
			if(isset($_AjourneySellKey[4]) && $_AjourneySellKey[4] == "Y"){
				$_BisSpecialReturn = true;
			}
		}
		
		$this->_Ainput['flightTraceId'] = $_SflightTraceId;
		
		$_AflightRefultIndex = array_unique($_AflightRefultIndex);
		
		if($_BisSpecialReturn && count($_AflightRefultIndex) > 0){
			$_AflightRefultIndex = array(implode(",",$_AflightRefultIndex));
		}
		
		$_IfareQuoteCount	 = count($_AflightRefultIndex);
		
		$_AflightResults = array();
		
		$_IdefaultTotalFare = 0;
		$_AmarkupData 		= array();
		
		for($i=0;$i<$_IfareQuoteCount;$i++){
			
			$this->_Ainput['flightResultIndex'] = $_AflightRefultIndex[$i];
			
			$_OfareQuote			= controllerGet::getObject('fareQuote',$this);				
			$_AfareQuoteResponse	= $_OfareQuote->_doFareQuote();
			
			if(isset($_AfareQuoteResponse['status']) && $_AfareQuoteResponse['status'] && isset($_AfareQuoteResponse['data']) && count($_AfareQuoteResponse['data']) > 0){
				
				foreach($_AfareQuoteResponse['data'] as $key=>$val){
					
					foreach($val as $subKey=>$subVal){
						
						if($subVal['defaultTotalFare'] != 0){
							$_IdefaultTotalFare += $subVal['defaultTotalFare'];
						}
						
						if(count($subVal['markupData']) > 0){
							
							if(count($_AmarkupData) <= 0){
								$_AmarkupData = $subVal['markupData'];
							}
							else{
								$_AmarkupData['markupValue']  += $subVal['markupData']['markupValue'];
								$_AmarkupData['perPaxMarkup'] += $subVal['markupData']['perPaxMarkup'];
								
								$_AmarkupData['userTotalMarkup']  += $subVal['markupData']['userTotalMarkup'];
								$_AmarkupData['perPaxMarkup'] += $subVal['markupData']['perPaxMarkup'];
							}
						}
						
						unset($subVal['defaultTotalFare']);
						unset($subVal['markupData']);
						
						$_AflightResults[] = $subVal;
					}
				}
				
				$this->updatePriceDetails($_IdefaultTotalFare,$_AmarkupData);
			}
			else{
				$_Bstatus	= false;
				$_Smessage .= $_AfareQuoteResponse['msg'];
				$_AflightResults = array();
				break;
			}
		}
		
		$_Areturn = array
					(
						'status' => $_Bstatus,
						'data'   => $_AflightResults,
						'msg'    => $_Smessage,
					);
					
		return $_Areturn;
	}
	
	function updatePriceDetails($_IdefaultTotalFare,$_AmarkupData)
	{
		$sqlInsertPrice = "INSERT INTO tbo_update_price
							(
								update_price_id,
								account_id,
								trace_id,
								api_mode,
								default_total,
								markup_data,
								created_date
							)
							VALUES
							(
								0,
								'".$GLOBALS['CONF']['userSettings']['accountId']."',
								'".$this->_Ainput['referenceId']."',
								'".$GLOBALS['CONF']['userSettings']['mode']."',
								'".$_IdefaultTotalFare."',
								'".json_encode($_AmarkupData)."',
								NOW()
							)";
							
		$this->_Odb->executeQuery($sqlInsertPrice);
	}
}
?>