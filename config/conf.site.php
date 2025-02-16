<?php
/**
	@File Name 		:	conf.site.php
	@Author 		:	Ramanathan M <ramanathan@dss.com.sg>
	@Created Date	:	2015-12-21 10:55 AM
	@Description	:	Declaring the site config variables .
*/

	$CONF['site']['hiddenFolder'] 				= 'N';
	$CONF['site']['executeFile']  				= $CONF['site']['hiddenFolder'] == 'Y' ? 'Service' : 'Service.php';
	
	$CONF['site']['webserviceMethod'] 			= 'CURL';  // SOAP // CURL
	
	$CONF['site']['infantCode'] 				= 'INF';
	
	$CONF['site']['paxTypes']   				= array
												(
													'ADT' => 'ADT',
													'CHD' => 'CHD',
													'INF' => 'INF',
												);
										
	$CONF['site']['paxAge']   					= array
												(
													'ADT' => 40,
													'CHD' => 10,
													'INF' => 1,
												);
	
	$CONF['site']['paxDob']   					= array
												(
													'ADT' => date("Y",strtotime('-'.$CONF['site']['paxAge']['ADT'].' years')).'-01-01',
													'CHD' => date("Y",strtotime('-'.$CONF['site']['paxAge']['CHD'].' years')).'-01-01',
													'INF' => date("Y",strtotime('-'.$CONF['site']['paxAge']['INF'].' years')).'-01-01',
												);
										
	$CONF['site']['logDateFormat'] 				= 'Y-m-d-G';
	
	$CONF['site']['defaultCurrency']			= 'SGD';
	$CONF['site']['defaultRoundOff']			= 3;
	$CONF['site']['currencyRoundOff']			= array
													(
														'SGD' => 2,
														'USD' => 2,
													);
	$CONF['site']['apiId']						= 34;
	$CONF['site']['addMarkupIn']				= 'BF';
	$CONF['site']['taxComponentName']			= 'WF';
	$CONF['site']['allowedSources']				= array
													(
														'GDS',
														'SG',
														'6E',
														'G8',
														'G9',
														'FZ',
														'IX',
														'AK',
														'LB',
													);
	
?>