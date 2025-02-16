<?php

	ini_set("display_errors",1);

	ini_set("output_buffering", "4096");
	ini_set("implicit_flush", "1");
	ini_set("memory_limit", "10000M");
	ini_set("max_execution_time", "0");
	date_default_timezone_set("Asia/Calcutta");
	set_time_limit(600);

	echo date('d-m-Y H:i:s');

	require_once('lib/common/include.php');	// Required include file, which have all the files which is necessary for portal
	require_once('userInfo.php');
	
	function getFlightSearchInput($_AuserInfo)
	{
		$_Ainput 						= array();
	
		$_Ainput['userName']			= $_AuserInfo['userName'];
		$_Ainput['action']				= 'getFlightSchedules';
		$_Ainput['mode']				= 'TEST';
		$_Ainput['tboFSC']				= '';
		$_Ainput['cabinType']			= 'Economy'; // Economy =>ECO  Business BUS classType 
		$_Ainput['classType']			= 'Economy'; // Economy =>ECO  Business BUS  
		//$_Ainput['specialReturn']		= 'Y';
		//$_Ainput['advancedSearch']	= 'Y';
		// INF
		//$_Ainput['paxDetails']			= array(array('paxType' => 'ADT','paxCount' => 2));

		$_Ainput['ADT']					= '1';
		$_Ainput['CHD']					= '1';
		$_Ainput['INF']					= '';
		$_Ainput['sectorInfo']			= array(
												0 => array
												(
													'origin'		=> 'SIN',
													'destination'	=> 'LAX',
													'departureDate'	=> '2025-03-01',
												), 
												1 => array
												(
													'origin'		=> 'LAX',
													'destination'	=> 'SIN',
													'departureDate'	=> '2025-03-18',
												),
												/*2 => array
												(
													'origin'		=> 'LHR',
													'destination'	=> 'MEL',
													'departureDate'	=> '2025-03-25',
												),
												3 => array
												(
													'origin'		=> 'DXB',
													'destination'	=> 'SIN',
													'departureDate'	=> '2024-07-04',
												),*/
											);
											
		$_Ainput['currencyCode']		= 'SGD';
		$_Ainput['CountryCode']			= 'SG';
		$_Ainput['AirlineDesigCode']	= 'SQ';
		$_Ainput['cabinClass']			= '';
		$_Ainput['referenceId']			= "1_AirShoppingRQ_".'';
		$_Ainput['hashKey']				= getSecureHash($_AuserInfo,$_Ainput);
					
		return $_Ainput;
	}
	
	function getUpdatePriceInput($_AuserInfo)
	{
		$_Ainput 						= array();
	
		$_Ainput['userName']			= $_AuserInfo['userName'];
		$_Ainput['action']				= 'fareQuote';
		$_Ainput['mode']				= 'TEST';
		$_Ainput['ADT']					= '1';
		$_Ainput['CHD']					= '1';
		$_Ainput['INF']					= '0';
		
		$_Ainput['flightDetails']		= array(
												0 => array
												(
													'journeySellKey' => 'AK~714~SIN~2019-05-15T16:10:00~KUL~2019-05-15T17:10:00~1~~~AK~13~KUL~2019-05-15T21:50:00~MAA~2019-05-15T23:05:00~2@@@Y@@@OB3@@@9c0e2ad0-dfd3-402d-b50b-4ccddc8f5005@@@N'
												),
												/*1 => array
												(
													'journeySellKey' => 'SG~103~MAA~2017-09-02T17:30:00~IXM~2017-09-02T18:35:00~1@@@Y@@@OB7@@@b4c5df45-2ac7-4b78-9d1d-0fe4428b6b20'
												)*/
											);
					
		$_Ainput['OwnerCode'] 			= "SQ";								
		$_Ainput['PaxRefID'] 			= "PAX1";								
		//$_Ainput['Paxes'] 			= array("PAX1","PAX2");							
		$_Ainput['OfferID'] 			= "SP1P-985187399063992104-1";	
		$_Ainput['OfferItemID'] 		= array("");
		$_Ainput['ShoppingResponseID'] 	= "SP1P-985187399063992104"; 
		$_Ainput['isOffered'] 			= true;	

		//This only called servicelist and then called offer price 
		
		$_Ainput['extraBaggagePaxWise'] 		= array("PAX2");			
		  $_Ainput['extraBaggageQuantityPaxWise'] = array("1");			
		  $_Ainput['extraBaggageServiceWise'] 	= array(
														"SP1P-985187399063992104-1-47");
		

		$_Ainput['currencyCode']		= 'SGD';
		$_Ainput['referenceId']			= '2_OfferPriceRQ'.'';
		$_Ainput['carrierCode'] 		= "SQ";
		$_Ainput['hashKey']				= getSecureHash($_AuserInfo,$_Ainput);
		//$_Ainput['paxDetails']			=array(array('paxType' => 'ADT','paxCount' => 1));			
		return $_Ainput;
	}
	
	function getUpdatePriceInput_AfterBooking_AddService($_AuserInfo)
	{
		$_Ainput 						= array();
	
		$_Ainput['userName']			= $_AuserInfo['userName'];
		$_Ainput['action']				= 'fareQuote';
		$_Ainput['service_name']		= 'AfterBooking_AddService';
		$_Ainput['mode']				= 'TEST';
		$_Ainput['ADT']					= '1';
		$_Ainput['CHD']					= '1';
		$_Ainput['INF']					= '0';
		
		$_Ainput['flightDetails']		= array(
												0 => array
												(
													'journeySellKey' => 'AK~714~SIN~2019-05-15T16:10:00~KUL~2019-05-15T17:10:00~1~~~AK~13~KUL~2019-05-15T21:50:00~MAA~2019-05-15T23:05:00~2@@@Y@@@OB3@@@9c0e2ad0-dfd3-402d-b50b-4ccddc8f5005@@@N'
												),
												/*1 => array
												(
													'journeySellKey' => 'SG~103~MAA~2017-09-02T17:30:00~IXM~2017-09-02T18:35:00~1@@@Y@@@OB7@@@b4c5df45-2ac7-4b78-9d1d-0fe4428b6b20'
												)*/
											);
					
		$_Ainput['OwnerCode'] 			= "SQ";								
		$_Ainput['PaxRefID'] 			= "PAX1";								
		//$_Ainput['Paxes'] 			= array("PAX1","PAX2");							
		$_Ainput['OfferID'] 			= "SP2P-15304254322598708886-1";	
		$_Ainput['OfferItemID'] 		= array("");
		$_Ainput['ShoppingResponseID'] 	= "SP2P-15304254322598708886"; 
		$_Ainput['isOffered'] 			= true;	

		//This only called servicelist and then called offer price 
		
		$_Ainput['extraBaggagePaxWise'] 		= array("PAX2");			
		  $_Ainput['extraBaggageQuantityPaxWise'] = array("1");			
		  $_Ainput['extraBaggageServiceWise'] 	= array(
														"SP2P-15304254322598708886-1-1");
		

		$_Ainput['currencyCode']		= 'SGD';
		$_Ainput['referenceId']			= '2_OfferPriceRQ'.'';
		$_Ainput['carrierCode'] 		= "SQ";
		$_Ainput['hashKey']				= getSecureHash($_AuserInfo,$_Ainput);
		//$_Ainput['paxDetails']			=array(array('paxType' => 'ADT','paxCount' => 1));			
		return $_Ainput;
	}
	function getSeatAvailability($_AuserInfo){
		$_Ainput 						= array();
	
		$_Ainput['userName']			= $_AuserInfo['userName'];
		$_Ainput['action']				= 'getSeatAvailability'; 
		$_Ainput['mode']				= 'TEST';
		$_Ainput['ADT']					= '1';
		$_Ainput['CHD']					= '1';
		$_Ainput['INF']					= '0';
	//	$_Ainput['OfferID'] 			="SP2F-17732624453503864675-1";		
	//	$_Ainput['OfferItemID'] 		="SP2F-17732624453503864675-1-1";	

		$_Ainput['OfferID'] 			= "1H1SQZ_3J6DBLF1QVIQXNHUQYLIAMHZYLAT";	
		$_Ainput['OfferItemID'] 		= "1H1SQZ_3J6DBLF1QVIQXNHUQYLIAMHZYLAT-1";

		//$_Ainput['OrderID']			='SQ_6J5NOK'; //Regarding Updating order

		$_Ainput['carrierCode'] 		="SQ";
		$_Ainput['PaxSegmentRefID'] 	="SEG19";
		$_Ainput['PaxRefID'] 			= array("PAX1","PAX2");
		$_Ainput['referenceId']			= '_SeatAvailabilityRQ'.'';
		return $_Ainput;
	}
	function getServiceList($_AuserInfo){
		$_Ainput 						= array();
	
		$_Ainput['userName']			= $_AuserInfo['userName'];
		$_Ainput['action']				= 'getServiceList'; 
		$_Ainput['mode']				= 'TEST';
		$_Ainput['ADT']					= '1';
		$_Ainput['CHD']					= '0';
		$_Ainput['INF']					= '1';
		//$_Ainput['OfferID'] 			= "1H1SQZ_4E6OG2XUUSU8L8JULZVJMKSUU0S4";		
		//$_Ainput['OfferItemID'] 		= "1H1SQZ_4E6OG2XUUSU8L8JULZVJMKSUU0S4-1";
		
		$_Ainput['OfferID'] 			= "1H0SQZ_C6NU62NCAQWDWRN3P7AJ7VGWP3L7";	
		$_Ainput['OfferItemID'] 		= "1H0SQZ_C6NU62NCAQWDWRN3P7AJ7VGWP3L7-1";
		//$_Ainput['ShoppingResponseID'] 	= "1H0SQZ_UHH2AEYYXYQXU5QHB0IR3SBJ7K8Z";

		$_Ainput['ServiceID'] 			= "1";										
		$_Ainput['isOffered'] 			= false;										
		$_Ainput['carrierCode'] 		= "SQ";
		// $_Ainput['PaxSegmentRefID'] 		="SEG4";
		$_Ainput['OrderID']				='SQ_548JJT'; //Regarding Updating order

		$_Ainput['referenceId']			= '2_ServiceListRQ'.'';

		return $_Ainput;
	}

	function createBooking($_AuserInfo){
		$_Ainput 						= array();
		$_Ainput['userName']			= $_AuserInfo['userName'];
		$_Ainput['action']				= 'getCreateBooking'; 
		$_Ainput['mode']				= 'TEST';
		$_Ainput['ADT']					= '1';
		$_Ainput['CHD']					= '1';
		$_Ainput['INF']					= '0';
		$_Ainput['paxCount']			='1';
		$_Ainput['agentPhone']			='9790657965';
		$_Ainput['agentMail']			='elavarasan@dss.com.sg';
		$_Ainput['currencyCode']		='INR';
		$_Ainput['totalAmount']			='169004';
		$_Ainput['isTicketed']			= true;
		
		$_ApaxdetailsJson			='[
											{
												"firstName": "ElavarasanADTOneSerS",
												"lastName": "PalaniSerS",
												"email": "elavarasan@gmail.com",
												"phone": "8344057968",
												"title": "Mr",
												"dob": "1996-02-21T21:00:00",
												"paxType": "ADT",
												"paxID": "PAX1",
												"age": "29",
												"gender": "Male",
												"nationality": "IN",
												"passportDetails": {
												"passportNumber": "J12393496eerS",
												"issuedCountry": "IN",
												"expiryDate": "2035-09-30T21:00:00",
												"issueDate": "2022-09-30T21:00:00",
												"Nationality": "IN",
												"firstName": "ElavarasanSer",
												"lastName": "PalaniSer",
												"title": "Mr",
												"dob": "1996-02-21T21:00:00",
												"gender": "Male",
												"NationalityISO": "IN"
												}
											},
											{
												"firstName": "SeeniAdulttwoSerS",
												"lastName": "vasaSerS",
												"email": "seeni@gmail.com",
												"phone": "6374707609",
												"title": "Mr",
												"dob": "2020-02-21T21:00:00",
												"paxType": "CHD",
												"paxID": "PAX2",
												"age": "1",
												"gender": "Male",
												"nationality": "IN",
												"passportDetails": {
													"passportNumber": "J12393466SSDFS",
													"issuedCountry": "IN",
													"expiryDate": "2035-09-30T21:00:00",
													"issueDate": "2020-02-21T21:00:00",
													"Nationality": "IN",
													"firstName": "SeeniSerS",
													"lastName": "vasaSerS",
													"title": "Mr",
													"dob": "2020-02-21T21:00:00",
													"gender": "Male",
													"NationalityISO": "IN"
												}
											} 
										]';
										
										/*  */
		$_Ainput['paxDetails']          = json_decode($_ApaxdetailsJson,true);

		//adult 
		// ,array("firstName"=>"Seeni","lastName"=>"vasa","email"=>"seeni@gmail.com","phone"=>"6374707609","title"=>"Mr","dob"=>"1996-02-21T21:00:00","paxType"=>"ADT","paxID"=>"PAX2","age"=>"27","gender"=>"Male","nationality"=>"IN","passportDetails"=>array("passportNumber"=>"J12393466","issuedCountry"=>"IN","expiryDate"=>"2035-09-30T21:00:00","issueDate"=>"1996-09-30T21:00:00","Nationality"=>"IN","firstName"=>"Rubesh","lastName"=>"Berry","title"=>"Mr","dob"=>"2020-02-21T21:00:00","gender"=>"Male","NationalityISO"=>"IN"))

		//child
		// array("firstName"=>"Seeni","lastName"=>"vasa","email"=>"seeni@gmail.com","phone"=>"6374707609","title"=>"Mr","dob"=>"2020-02-21T21:00:00","paxType"=>"CHD","paxID"=>"PAX2","age"=>"5","gender"=>"Male","nationality"=>"IN","passportDetails"=>array("passportNumber"=>"J12393466","issuedCountry"=>"IN","expiryDate"=>"2035-09-30T21:00:00","issueDate"=>"2022-09-30T21:00:00","Nationality"=>"IN","firstName"=>"Rubesh","lastName"=>"Berry","title"=>"Mr","dob"=>"2020-02-21T21:00:00","gender"=>"Male","NationalityISO"=>"IN"))

		//infant
		// array("firstName"=>"Child","lastName"=>"Baby","email"=>"child@gmail.com","phone"=>"637470765","title"=>"Mr","dob"=>"2023-02-21T21:00:00","paxType"=>"INF","paxID"=>"PAX11","age"=>"2","gender"=>"Male","nationality"=>"IN","passportDetails"=>array("passportNumber"=>"J12393423","issuedCountry"=>"IN","expiryDate"=>"2035-09-30T21:00:00","issueDate"=>"2022-09-30T21:00:00","Nationality"=>"IN","firstName"=>"Rubesh","lastName"=>"Berry","title"=>"Mr","dob"=>"2023-02-21T21:00:00","gender"=>"Male","NationalityISO"=>"IN"))
		
	/* 	$_Ainput['OfferID'] 			= "1H1SQZ_331NCTBJGIX0JIBZM1Q5H2PJK966";	
		$_Ainput['OfferItemID'] 		= array("1H1SQZ_331NCTBJGIX0JIBZM1Q5H2PJK966-1");
		$_Ainput['ShoppingResponseID'] 	= "1H1SQZ_VUD2D2YB474XH9R4N5M10113RFNG";
		$_Ainput['isOffered'] 			= true;	 */
		
		
		$_Ainput['OfferID'] 			= "1H1SQZ_3J6DBLF1QVIQXNHUQYLIAMHZYLAT";	 
		$_Ainput['OfferItemID'] 		= "1H1SQZ_3J6DBLF1QVIQXNHUQYLIAMHZYLAT-1";
		$_Ainput['ShoppingResponseID'] 	= ""; 
		$_Ainput['isOffered'] 			= true;	

		
		/* $_Ainput['OfferID'] 			= "1H0SQZ_IG926G9DF6YPPSURSP447Z6O4LWC";	
		$_Ainput['OfferItemID'] 		= "1H0SQZ_IG926G9DF6YPPSURSP447Z6O4LWC-1";
		$_Ainput['ShoppingResponseID'] 	= "1H0SQZ_UUGDIYIFEW22UG9RT810EM81AZ4Z"; */

		/* $_Ainput['OfferID'] 			= "1H0SQZ_K58A793YUXLIIOEOKKM10FRRNA8B";	
		$_Ainput['OfferItemID'] 		= "1H0SQZ_K58A793YUXLIIOEOKKM10FRRNA8B-1";
		$_Ainput['ShoppingResponseID'] 	= "1H0SQZ_HORY2AGJYU10MEY9TA4DAFMTQ35Y"; */

		//Only if the SSR are enable

		/* $_Ainput['extraBaggagePaxWise'] 		= array("PAX1",'PAX11');			
		$_Ainput['extraBaggageQuantityPaxWise'] = array("10",'10');			
		$_Ainput['extraBaggageServiceWise'] 	= array("1H1SQZ_331NCTBJGIX0JIBZM1Q5H2PJK966-2",'1H1SQZ_331NCTBJGIX0JIBZM1Q5H2PJK966-3'); */

/*  		$_Ainput['flightDetails'][0]['viaFlights'][0]['ssrDetails'][0] = array(
																		'OfferItemID'=>"1H0SQZ_C6NU62NCAQWDWRN3P7AJ7VGWP3L7-2", 
																		'PaxRefID'=>"PAX1", 
																		'codeType'=>"SRV1",
																		'SSRCode'=>"XBAG",
																		'description'=>"ADT"
																		);		
		$_Ainput['flightDetails'][0]['viaFlights'][0]['ssrDetails'][1] = array(
																		'OfferItemID'=>"1H0SQZ_C6NU62NCAQWDWRN3P7AJ7VGWP3L7-3", 
																		'PaxRefID'=>"PAX11", 
																		'codeType'=>"SRV2",
																		'SSRCode'=>"XBAG",
																		'description'=>"INF",
																		);  */

		$_Ainput['PaxID'] 				= "PAX1";	
		$_Ainput['PaxRefID'] 			= "PAX1";	

		$_Ainput['SeatRow'] 			= "46";
		$_Ainput['SeatColumn'] 			= "C"; 
		$_Ainput['OfferItemRefID'] 		= "PRICE3-SEG19";  // Seat Availability 
		$_Ainput['PaxSegmentRefID'] 	= "SEG19";

		$_Ainput['carrierCode'] 		="SQ";
		// $data['flightDetails']['flightDetails'] =getPnrBlockingInput($_AuserInfo);
		// echo "<pre>";
		// print_r($data['flightDetails']['flightDetails']['flightDetails']);exit;
		// $_Ainput['PaxSegmentRefID'] 		="SEG4";

		//print_r(json_encode($_Ainput['paxDetails']));die;
		$_Ainput['referenceId']			= '5_OrderCreateRQ'.'';

		return $_Ainput;
	}

	function orderRetrive($_AuserInfo){
		$_Ainput 						= array();
	
		$_Ainput['userName']			= $_AuserInfo['userName'];
		$_Ainput['action']				= 'orderRetrive';
		$_Ainput['mode']				= 'TEST';
		$_Ainput['orderId']				= 'SQ_548JJT'; 
		$_Ainput['carrierCode']			= 'SQ';
		$_Ainput['referenceId']			= '1_OrderRetrieveRQ'.'';
		return $_Ainput;
	}
	
	function orderReshop($_AuserInfo){
		$_Ainput 						= array();
	
		$_Ainput['userName']			= $_AuserInfo['userName'];
		$_Ainput['action']				= 'orderReshop';
		$_Ainput['service_name']		= 'Reshop';
		//$_Ainput['service_name']		= 'OrderWithoutFormOfPayment';
		$_Ainput['mode']				= 'TEST';
		$_Ainput['OrderID']				='SQ_6J5NOK';    
		$_Ainput['CabinTypeName']		='ECO';							//ECO  BUS
		$_Ainput['BookingRef']			='6J5NOK';
	 	$_Ainput['OrderItemRefID']		='SQ_6J5NOK_AIR-1-2-3'; 
	 	//$_Ainput['OrderWithoutFormOfPayment']		='true'; 

		$_Ainput['PAX2']			='PAX2';

	/*	$_Ainput['ServiceID']			='SEG1_PAX2';
		$_Ainput['PaxSegmentRefID']		='SEG1';
		
		$_Ainput['totalAmount']			='1514.30';

		$_Ainput['OfferID'] 			="SP1F-6263577381492385378-1"; //from order rehop
		$_Ainput['OfferItemID'] 		="SP1F-6263577381492385378-1-1"; //from order rehop
		$_Ainput['ShoppingResponseID'] 	="SP1F-6263577381492385378"; //from order rehop
		// $_Ainput['departuredate']		='2024-06-21'; */
		
		 $_Ainput['sectorInfo']			= array(
			0 => array
				(
					'origin'		=> 'SIN',
					'destination'	=> 'LAX',
					'departureDate'	=> '2025-03-11',
				),
			1 => array
			(
				'origin'		=> 'LAX', 
				'destination'	=> 'SIN',
				'departureDate'	=> '2025-03-20',
			)/*,
			2 => array
			(
				'origin'		=> 'SIN', 
				'destination'	=> 'DXB',
				'departureDate'	=> '2024-07-03',
			),
			3 => array
			(
				'origin'		=> 'DXB',
				'destination'	=> 'SIN',
				'departureDate'	=> '2024-07-04',
			),*/
		);
		$_Ainput['paxDetails']			=array(array("firstName"=>"Rubesh","lastName"=>"Berry","email"=>"rubesh@gmail.com","phone"=>"8344057968","title"=>"Mr","dob"=>"1996-02-21T21:00:00","paxType"=>"ADT","paxID"=>"PAX1","age"=>"29","gender"=>"Male","nationality"=>"IN","passportDetails"=>array("passportNumber"=>"J12393496","issuedCountry"=>"IN","expiryDate"=>"2035-09-30T21:00:00","issueDate"=>"2022-09-30T21:00:00","Nationality"=>"IN","firstName"=>"Rubesh","lastName"=>"Berry","title"=>"Mr","dob"=>"1996-02-21T21:00:00","gender"=>"Male","NationalityISO"=>"IN")),array("firstName"=>"Seeni","lastName"=>"vasa","email"=>"seeni@gmail.com","phone"=>"6374707609","title"=>"Mr","dob"=>"1996-02-21T21:00:00","paxType"=>"ADT","paxID"=>"PAX2","age"=>"27","gender"=>"Male","nationality"=>"IN","passportDetails"=>array("passportNumber"=>"J12393466","issuedCountry"=>"IN","expiryDate"=>"2035-09-30T21:00:00","issueDate"=>"1996-09-30T21:00:00","Nationality"=>"IN","firstName"=>"Rubesh","lastName"=>"Berry","title"=>"Mr","dob"=>"2020-02-21T21:00:00","gender"=>"Male","NationalityISO"=>"IN")));
		
		$_Ainput['carrierCode']			='SQ';

		$_Ainput['referenceId']			= 'OrderReshop'.'';

		return $_Ainput;
	}
	function orderReshopPrice($_AuserInfo){
		$_Ainput 						= array();

		
	
		$_Ainput['userName']			= $_AuserInfo['userName'];
		$_Ainput['action']				= 'orderReshop';
		$_Ainput['service_name']		= 'Reprice';
		$_Ainput['mode']				= 'TEST';    
		$_Ainput['OrderID']				='SQ_6YNY33';    
		$_Ainput['CabinTypeName']		= 'ECO';							//ECO  BUS
		$_Ainput['BookingRef']			= '6YNY33';
	 	$_Ainput['OrderItemRefID']		= 'SQ_6YNY33_AIR-1'; 

		$_Ainput['ServiceID']			='SEG1_PAX2';
		$_Ainput['PaxSegmentRefID']		='SEG1';
		$_Ainput['PaxRefID'][0]			='PAX2';						//Based on Pax count we should check this
		//$_Ainput['PaxRefID'][1]			='PAX3';
		//$_Ainput['totalAmount']			='11140';


/* 		$_Ainput['OfferID'] 			="SP2F-9685845778631309963-7"; //from order rehop
		$_Ainput['OfferItemID'] 		="SP2F-9685845778631309963-7-1"; //from order rehop
		$_Ainput['ShoppingResponseID'] 	="SP2F-9685845778631309963"; //from order rehop */


		$_Ainput['OfferID'] 			="SP2F-468265910941035439-7"; //from order rehop
		$_Ainput['OfferItemID'] 		="SP2F-468265910941035439-7-1"; //from order rehop
		$_Ainput['ShoppingResponseID'] 	="SP2F-468265910941035439"; //from order rehop   'SQ_5OM6IS_AIR-1-2';  8295

		// $_Ainput['departuredate']		='2024-06-21'; 
		
	/*	$_Ainput['sectorInfo']			= array(
			0 => array
			(
				'origin'		=> 'BKK',
				'destination'	=> 'SIN',
				'departureDate'	=> '2025-03-20',
			),
			1 => array
			(
				'origin'		=> 'SIN',
				'destination'	=> 'BOM',
				'departureDate'	=> '2024-08-25',
			),
			2 => array
			(
				'origin'		=> 'SIN',
				'destination'	=> 'DXB',
				'departureDate'	=> '2024-07-03',
			),
			3 => array
			(
				'origin'		=> 'DXB',
				'destination'	=> 'SIN',
				'departureDate'	=> '2024-07-04',
			),
		);*/
		$_Ainput['paxDetails']			=array(array("firstName"=>"Rubesh","lastName"=>"Berry","email"=>"rubesh@gmail.com","phone"=>"8344057968","title"=>"Mr","dob"=>"1996-02-21T21:00:00","paxType"=>"ADT","paxID"=>"PAX1","age"=>"29","gender"=>"Male","nationality"=>"IN","passportDetails"=>array("passportNumber"=>"J12393496","issuedCountry"=>"IN","expiryDate"=>"2035-09-30T21:00:00","issueDate"=>"2022-09-30T21:00:00","Nationality"=>"IN","firstName"=>"Rubesh","lastName"=>"Berry","title"=>"Mr","dob"=>"1996-02-21T21:00:00","gender"=>"Male","NationalityISO"=>"IN")),array("firstName"=>"Seeni","lastName"=>"vasa","email"=>"seeni@gmail.com","phone"=>"6374707609","title"=>"Mr","dob"=>"1996-02-21T21:00:00","paxType"=>"ADT","paxID"=>"PAX2","age"=>"27","gender"=>"Male","nationality"=>"IN","passportDetails"=>array("passportNumber"=>"J12393466","issuedCountry"=>"IN","expiryDate"=>"2035-09-30T21:00:00","issueDate"=>"1996-09-30T21:00:00","Nationality"=>"IN","firstName"=>"Rubesh","lastName"=>"Berry","title"=>"Mr","dob"=>"2020-02-21T21:00:00","gender"=>"Male","NationalityISO"=>"IN")));
		
		$_Ainput['carrierCode']			='SQ';

		$_Ainput['referenceId']			= 'OrderReprice'.'';

		return $_Ainput;
	}
	function orderReshopRefund($_AuserInfo){
		$_Ainput 						= array();
	
		$_Ainput['userName']			= $_AuserInfo['userName'];
		$_Ainput['action']				= 'orderReshop';
		$_Ainput['service_name']		= 'ReshopRefund';
		$_Ainput['mode']				= 'TEST';
		$_Ainput['OrderID']				='SQ_6YNY33';   
		$_Ainput['CabinTypeName']		='ECO';							//ECO  BUS
		$_Ainput['BookingRef']			='6YNY33';
	 	$_Ainput['OrderItemRefID']		='SQ_6YNY33_AIR-2'; 

	/* 	$_Ainput['ServiceID']			='SEG1_PAX2';
		$_Ainput['PaxSegmentRefID']		='SEG1';
		$_Ainput['PaxRefID'][0]			='PAX2';
		$_Ainput['PaxRefID'][1]			='PAX3';
		$_Ainput['totalAmount']			='11140';


		$_Ainput['OfferID'] 			="SP2F-9685845778631309963-7"; //from order rehop
		$_Ainput['OfferItemID'] 		="SP2F-9685845778631309963-7-1"; //from order rehop
		$_Ainput['ShoppingResponseID'] 	="SP2F-9685845778631309963"; //from order rehop */
		// $_Ainput['departuredate']		='2024-06-21'; 
		
		/* $_Ainput['sectorInfo']			= array(
			0 => array
			(
				'origin'		=> 'BKK',
				'destination'	=> 'SIN',
				'departureDate'	=> '2025-03-20',
			),
			1 => array
			(
				'origin'		=> 'SIN',
				'destination'	=> 'BOM',
				'departureDate'	=> '2024-08-25',
			),
			2 => array
			(
				'origin'		=> 'SIN',
				'destination'	=> 'DXB',
				'departureDate'	=> '2024-07-03',
			),
			3 => array
			(
				'origin'		=> 'DXB',
				'destination'	=> 'SIN',
				'departureDate'	=> '2024-07-04',
			),
		);*/
		//$_Ainput['paxDetails']			=array(array("firstName"=>"Rubesh","lastName"=>"Berry","email"=>"rubesh@gmail.com","phone"=>"8344057968","title"=>"Mr","dob"=>"1996-02-21T21:00:00","paxType"=>"ADT","paxID"=>"PAX1","age"=>"29","gender"=>"Male","nationality"=>"IN","passportDetails"=>array("passportNumber"=>"J12393496","issuedCountry"=>"IN","expiryDate"=>"2035-09-30T21:00:00","issueDate"=>"2022-09-30T21:00:00","Nationality"=>"IN","firstName"=>"Rubesh","lastName"=>"Berry","title"=>"Mr","dob"=>"1996-02-21T21:00:00","gender"=>"Male","NationalityISO"=>"IN")),array("firstName"=>"Seeni","lastName"=>"vasa","email"=>"seeni@gmail.com","phone"=>"6374707609","title"=>"Mr","dob"=>"1996-02-21T21:00:00","paxType"=>"ADT","paxID"=>"PAX2","age"=>"27","gender"=>"Male","nationality"=>"IN","passportDetails"=>array("passportNumber"=>"J12393466","issuedCountry"=>"IN","expiryDate"=>"2035-09-30T21:00:00","issueDate"=>"1996-09-30T21:00:00","Nationality"=>"IN","firstName"=>"Rubesh","lastName"=>"Berry","title"=>"Mr","dob"=>"2020-02-21T21:00:00","gender"=>"Male","NationalityISO"=>"IN")));
		
		$_Ainput['carrierCode']			='SQ';

		$_Ainput['referenceId']			= 'OrderReshopRQ_Refund'.'';

		return $_Ainput;
	}
	function orderChange($_AuserInfo){ 
		$_Ainput 						= array(); 
	
		$_Ainput['userName']			= $_AuserInfo['userName'];
		$_Ainput['action']				= 'orderChange';
		$_Ainput['isTicketed']			= true;

		//$_Ainput['service_name']		= 'AddSeats';
		
		$_Ainput['mode']				= 'TEST';
		$_Ainput['OrderID']				= 'SQ_6YNY33';
		$_Ainput['BookingID']			= '6YNY33';
		$_Ainput['BookingEntity']		= 'SQ';
		$_Ainput['totalAmount'] 		= "18480";				//Total differential + penalty from order reshop price

		$_Ainput['OfferID'] 			="SP2F-468265910941035439-7"; //from order rehop
		$_Ainput['OfferItemID'] 		="SP2F-468265910941035439-7-1"; //from order rehop
		$_Ainput['ShoppingResponseID'] 	="0"; //from order rehop   'SQ_5OM6IS_AIR-1-2';  8295
		
		/* $_Ainput['SelectedOfferItem']   =array(array("OfferItemID"=>"SP2P-5150870224196782662-1-1","PaxRefID"=>"PAX2",			"SelectedServiceID"=>"SRV1"),array("OfferItemID"=>"SP2P-5150870224196782662-1-1","PaxRefID"=>"PAX2",			"SelectedServiceID"=>"SRV1"));
		 */
		$_Ainput['PaxID'] 				="PAX2";	
		$_Ainput['PaxRefID'] 			="PAX2";	

		//$_Ainput['SeatRow'] 			="42";
		//$_Ainput['SeatColumn'] 			="C";
		//$_Ainput['OfferItemRefID'] 		="PRICE2-SEG1";  // Seat Availability 
		$_Ainput['PaxSegmentRefID'] 	="SEG1";
			
		$_Ainput['carrierCode']			='SQ';
		//$_Ainput['paxDetails']			=array(array("firstName"=>"Rubesh","lastName"=>"Berry","email"=>"rubesh@gmail.com","phone"=>"8344057968","title"=>"Mr","dob"=>"1996-02-21T21:00:00","paxType"=>"ADT","paxID"=>"PAX1","age"=>"29","gender"=>"Male","nationality"=>"IN","passportDetails"=>array("passportNumber"=>"J12393496","issuedCountry"=>"IN","expiryDate"=>"2035-09-30T21:00:00","issueDate"=>"2022-09-30T21:00:00","Nationality"=>"IN","firstName"=>"Rubesh","lastName"=>"Berry","title"=>"Mr","dob"=>"1996-02-21T21:00:00","gender"=>"Male","NationalityISO"=>"IN")),array("firstName"=>"Seeni","lastName"=>"vasa","email"=>"seeni@gmail.com","phone"=>"6374707609","title"=>"Mr","dob"=>"1996-02-21T21:00:00","paxType"=>"ADT","paxID"=>"PAX2","age"=>"27","gender"=>"Male","nationality"=>"IN","passportDetails"=>array("passportNumber"=>"J12393466","issuedCountry"=>"IN","expiryDate"=>"2035-09-30T21:00:00","issueDate"=>"1996-09-30T21:00:00","Nationality"=>"IN","firstName"=>"Rubesh","lastName"=>"Berry","title"=>"Mr","dob"=>"2020-02-21T21:00:00","gender"=>"Male","NationalityISO"=>"IN")));
		
		$_Ainput['referenceId']			= 'OrderChange'.''; 
		
		return $_Ainput;
	}

	function orderChangeSeat($_AuserInfo){ 
		$_Ainput 						= array(); 
	
		$_Ainput['userName']			= $_AuserInfo['userName'];
		$_Ainput['action']				= 'orderChange';
		$_Ainput['isTicketed']			= true;

		$_Ainput['service_name']		= 'AddSeats';
		
		$_Ainput['mode']				= 'TEST';
		$_Ainput['OrderID']				= 'SQ_6J5NOK';
		$_Ainput['BookingID']			= '6J5NOK';
		$_Ainput['BookingEntity']		= 'SQ';
		$_Ainput['totalAmount'] 		= "5197";				//Total differential + penalty from order reshop price

		$_Ainput['OfferID'] 			= "SP2F-468265910941035439-7"; //from order rehop
		$_Ainput['OfferItemID'] 		= "SP2F-468265910941035439-7-1"; //from order rehop
		$_Ainput['ShoppingResponseID'] 	= "0"; //from order rehop   'SQ_5OM6IS_AIR-1-2';  8295
		
		/* $_Ainput['SelectedOfferItem']   =array(array("OfferItemID"=>"SP2P-5150870224196782662-1-1","PaxRefID"=>"PAX2",			"SelectedServiceID"=>"SRV1"),array("OfferItemID"=>"SP2P-5150870224196782662-1-1","PaxRefID"=>"PAX2",			"SelectedServiceID"=>"SRV1"));
		 */
		$_Ainput['PaxID'] 				= "PAX2";	
		$_Ainput['PaxRefID'] 			= "PAX2";	

	$_Ainput['SeatRow'] 				= "46";
		$_Ainput['SeatColumn'] 			= "K"; 
		$_Ainput['OfferItemRefID'] 		= "PRICE2-SEG1";  // Seat Availability 
		$_Ainput['PaxSegmentRefID'] 	= "SEG1";
			
		$_Ainput['carrierCode']			= 'SQ';
		//$_Ainput['paxDetails']			=array(array("firstName"=>"Rubesh","lastName"=>"Berry","email"=>"rubesh@gmail.com","phone"=>"8344057968","title"=>"Mr","dob"=>"1996-02-21T21:00:00","paxType"=>"ADT","paxID"=>"PAX1","age"=>"29","gender"=>"Male","nationality"=>"IN","passportDetails"=>array("passportNumber"=>"J12393496","issuedCountry"=>"IN","expiryDate"=>"2035-09-30T21:00:00","issueDate"=>"2022-09-30T21:00:00","Nationality"=>"IN","firstName"=>"Rubesh","lastName"=>"Berry","title"=>"Mr","dob"=>"1996-02-21T21:00:00","gender"=>"Male","NationalityISO"=>"IN")),array("firstName"=>"Seeni","lastName"=>"vasa","email"=>"seeni@gmail.com","phone"=>"6374707609","title"=>"Mr","dob"=>"1996-02-21T21:00:00","paxType"=>"ADT","paxID"=>"PAX2","age"=>"27","gender"=>"Male","nationality"=>"IN","passportDetails"=>array("passportNumber"=>"J12393466","issuedCountry"=>"IN","expiryDate"=>"2035-09-30T21:00:00","issueDate"=>"1996-09-30T21:00:00","Nationality"=>"IN","firstName"=>"Rubesh","lastName"=>"Berry","title"=>"Mr","dob"=>"2020-02-21T21:00:00","gender"=>"Male","NationalityISO"=>"IN")));
		
		$_Ainput['referenceId']			= 'OrderChange'.''; 
		
		return $_Ainput;
	}

	function getCancelBookingInput($_AuserInfo)
	{
		$_Ainput 						= array();
	
		$_Ainput['userName']			= $_AuserInfo['userName'];
		$_Ainput['action']				= 'cancelBooking';
		$_Ainput['mode']				= 'TEST';
		$_Ainput['currencyCode']		= 'INR';
		$_Ainput['referenceId']			= 0;
		$_Ainput['pnr']					= 'SQ_6YNY33';
		$_Ainput['orderId']				= 'SQ_6YNY33';    
		$_Ainput['RefundAmount']		= '35689';					//Put 0 if it is void
		$_Ainput['carrierCode']			= 'SQ';
		$_Ainput['hashKey']				= getSecureHash($_AuserInfo,$_Ainput);
		$_Ainput['referenceId']			= 'OrderCancelRQ'.'';			
		return $_Ainput;
	}


	function getFareRulesInput($_AuserInfo)
	{
		$_Ainput 						= array();
	
		$_Ainput['userName']			= $_AuserInfo['userName'];
		$_Ainput['action']				= 'getFareRules';
		$_Ainput['mode']				= 'TEST';
		
		$_Ainput['flightDetails']		= array(
												0 => array
												(
													'journeySellKey' => 'SG~611~BOM~2017-09-02T07:20:00~MAA~2017-09-02T09:20:00~1~~~SG~3071~MAA~2017-09-03T06:10:00~IXM~2017-09-03T07:25:00~2@@@Y@@@OB6@@@833d2f54-0af4-4bc3-addd-f1f50f6fda51'
												),
												1 => array
												(
													'journeySellKey' => 'SG~3072~IXM~2017-09-06T07:45:00~MAA~2017-09-06T09:00:00~1~~~SG~612~MAA~2017-09-06T20:35:00~BOM~2017-09-06T22:25:00~2@@@Y@@@OB6@@@833d2f54-0af4-4bc3-addd-f1f50f6fda51'
												)
											);
											
		$_Ainput['currencyCode']		= 'SGD';
		$_Ainput['referenceId']			= 0;
		$_Ainput['hashKey']				= getSecureHash($_AuserInfo,$_Ainput);
					
		return $_Ainput;
	}
	
	function getPnrBlockingInput($_AuserInfo)
	{
		$inp = '{"ADT":"2","CHD":"0","INF":"0","paxCount":"2","currencyCode":"SGD","totalAmount":"700.78","paxDetails":[{"firstName":"Elamani","lastName":"Madhu","title":"Mr","dob":"2008-05-22T21:00:00","paxType":"ADT","age":"12","gender":"Male","nationality":"IN","passportDetails":{"passportNumber":"sdfsdfsdf","issuedCountry":"IN","expiryDate":"2020-12-24T21:00:00","issueDate":"0000-00-00T00:00:00","Nationality":"IN","firstName":"Elamani","lastName":"Madhu","title":"Mr","dob":"2008-05-22T21:00:00","gender":"Male","NationalityISO":"IN"}},{"firstName":"Chinna","lastName":"Mani","title":"Mr","dob":"2008-05-21T21:00:00","paxType":"ADT","age":"12","gender":"Male","nationality":"IN","passportDetails":{"passportNumber":"sdfdsfsdf","issuedCountry":"IN","expiryDate":"2020-12-30T21:00:00","issueDate":"0000-00-00T00:00:00","Nationality":"IN","firstName":"Chinna","lastName":"Mani","title":"Mr","dob":"2008-05-21T21:00:00","gender":"Male","NationalityISO":"IN"}}],"flightDetails":[{"journeySellKey":"AI~569~MAA~2020-06-19T10:25:00~BOM~2020-06-19T12:00:00~1~~~AI~144~BOM~2020-06-19T17:00:00~DEL~2020-06-19T19:10:00~2@@@N@@@OB39@@@53e7470c-488e-44ee-92c1-ae46819177da@@@N","viaFlights":[{"departureStation":"SIN","arrivalStation":"MAA","std":"2020-06-19T10:25:00","sta":"2020-06-19T12:00:00","carrierCode":"AI","flightNumber":"569","departureDate":"2020-06-19","departureTime":"10:25","arrivalDate":"2020-06-19","arrivalTime":"12:00","classOfService":"S","classType":"Economy","segmentSellKey":"AI~569~MAA~2020-06-19T10:25:00~BOM~2020-06-19T12:00:00~1"},{"departureStation":"BOM","arrivalStation":"DEL","std":"2020-06-19T17:00:00","sta":"2020-06-19T19:10:00","carrierCode":"AI","flightNumber":"144","departureDate":"2020-06-19","departureTime":"17:00","arrivalDate":"2020-06-19","arrivalTime":"19:10","classOfService":"S","classType":"Economy","segmentSellKey":"AI~144~BOM~2020-06-19T17:00:00~DEL~2020-06-19T19:10:00~2"}],"paxFaresKey":["eyJDdXJyZW5jeSI6IklOUiIsIlBhc3NlbmdlclR5cGUiOjEsIlBhc3NlbmdlckNvdW50IjoyLCJCYXNlRmFyZSI6NjcwMCwiVGF4IjoxNTY4LCJZUVRheCI6MCwiQWRkaXRpb25hbFR4bkZlZU9mcmQiOjAsIkFkZGl0aW9uYWxUeG5GZWVQdWIiOjAsIlBHQ2hhcmdlIjowLCJUcmFuc2FjdGlvbkZlZSI6MCwiQWlyVHJhbnNGZWUiOjB9"]},{"journeySellKey":"AI~191~DEL~2020-06-20T21:00:00~BOM~2020-06-20T23:10:00~1~~~AI~570~BOM~2020-06-21T06:30:00~MAA~2020-06-21T08:25:00~2@@@N@@@IB40@@@53e7470c-488e-44ee-92c1-ae46819177da","fareInfoRef":"eyJDdXJyZW5jeSI6IklOUiIsIlBhc3NlbmdlclR5cGUiOjEsIlBhc3NlbmdlckNvdW50IjoyLCJCYXNlRmFyZSI6NjcwMCwiVGF4IjoxNTg2LCJZUVRheCI6MCwiQWRkaXRpb25hbFR4bkZlZU9mcmQiOjAsIkFkZGl0aW9uYWxUeG5GZWVQdWIiOjAsIlBHQ2hhcmdlIjowLCJUcmFuc2FjdGlvbkZlZSI6MCwiQWlyVHJhbnNGZWUiOjB9"}],"apiName":"Tbo","apiUrl":"http:\/\/xmldev.nugds.com\/tbo1.1\/Service.php","mode":"TEST","apiFunctionName":"process","action":"pnrBlocking","referenceId":"Z29idWRnZXRhaXJURVNUMzQxNDk=","traceId":"Z29idWRnZXRhaXJURVNUMzQxNDk=","bookingType":"TICKET","hashKey":"a78a77aa1a371a2fab67bd2083cb508c"}';
		$_Ainput = json_decode($inp,true);
		
		return $_Ainput;
	}
	
	function getHoldCancelInput($_AuserInfo)
	{
		$_Ainput 						= array();
	
		$_Ainput['userName']			= $_AuserInfo['userName'];
		$_Ainput['action']				= 'holdCancel';
		$_Ainput['mode']				= 'TEST';
		$_Ainput['currencyCode']		= 'SGD';
		$_Ainput['referenceId']			= 0;
		$_Ainput['pnr']					= 'UAQFNZ';
		$_Ainput['hashKey']				= getSecureHash($_AuserInfo,$_Ainput);
					
		return $_Ainput;
	}
	
	function getGetBookingInput($_AuserInfo)
	{
		$_Ainput 						= array();
	
		$_Ainput['userName']			= $_AuserInfo['userName'];
		$_Ainput['action']				= 'getBooking';
		$_Ainput['mode']				= 'TEST';
		$_Ainput['currencyCode']		= 'INR';
		$_Ainput['referenceId']			= 'FM11GH';
		$_Ainput['pnr']					= 'FM11GH';
		$_Ainput['hashKey']				= getSecureHash($_AuserInfo,$_Ainput);
					//F8MC2P,L9848P
		return $_Ainput;
	}
	

	
	function getMakeTicketInput($_AuserInfo)
	{
		$_Ainput 						= array();
	
		$_Ainput['userName']			= $_AuserInfo['userName'];
		$_Ainput['action']				= 'makeTicket';
		$_Ainput['mode']				= 'TEST';
		$_Ainput['currencyCode']		= 'SGD';
		$_Ainput['referenceId']			= 'Q5YYQB';
		$_Ainput['pnr']					= 'TX2YKC';
		$_Ainput['hashKey']				= getSecureHash($_AuserInfo,$_Ainput);
					
		return $_Ainput;
	}
	
	function getGetCalendarFareInput($_AuserInfo)
	{
		$_Ainput 						= array();
	
		$_Ainput['userName']			= $_AuserInfo['userName'];
		$_Ainput['action']				= 'getCalendarFare';
		$_Ainput['mode']				= 'TEST';
		
		$_Ainput['sectorInfo']			= array(
												0 => array
												(
													'origin'		=> 'MAA',
													'destination'	=> 'BOM',
													'departureDate'	=> '2018-09-25',
												),
											);
											
		$_Ainput['currencyCode']		= 'INR';
		$_Ainput['cabinClass']			= '';
		$_Ainput['referenceId']			= 0;
		$_Ainput['hashKey']				= getSecureHash($_AuserInfo,$_Ainput);
					
		return $_Ainput;
	}
	function ticket($_AuserInfo)
	{
		$_Ainput 						= array();
	
		$_Ainput['userName']			= $_AuserInfo['userName'];
		$_Ainput['action']				= 'ticket';
		$_Ainput['mode']				= 'TEST';
		
		$_Ainput['sectorInfo']			= array(
												0 => array
												(
													'origin'		=> 'MAA',
													'destination'	=> 'BOM',
													'departureDate'	=> '2018-09-25',
												),
											);
											
		$_Ainput['currencyCode']		= 'INR';
		$_Ainput['cabinClass']			= '';
		$_Ainput['referenceId']			= 0;
		$_Ainput['hashKey']				= getSecureHash($_AuserInfo,$_Ainput);
					
		return $_Ainput;
	}
	
	function getPriceRbdInput($_AuserInfo)
	{
		$_Ainput 						= array();
	
		$_Ainput['userName']			= $_AuserInfo['userName'];
		$_Ainput['action']				= 'priceRbd';
		$_Ainput['mode']				= 'TEST';
		$_Ainput['ADT']					= '2';
		$_Ainput['CHD']					= '0';
		$_Ainput['INF']					= '0';
		
		$_Ainput['flightTraceId']		= '1718689b-967a-4081-b592-d149330c881b';
		$_Ainput['priceRbd']			= '{"ResultIndex":"OB9","Source":"5","IsLCC":"","IsRefundable":"1","GSTAllowed":"","AirlineRemark":"AI TEST","Segments":[[{"Baggage":"","CabinBaggage":"","TripIndicator":"1","SegmentIndicator":"1","Airline":{"AirlineCode":"AI","AirlineName":"Air India","FlightNumber":"563","FareClass":"S","OperatingCarrier":"AI"},"Origin":{"Airport":{"AirportCode":"MAA","AirportName":"Chennai","Terminal":"1","CityCode":"MAA","CityName":"Chennai","CountryCode":"IN","CountryName":"India"},"DepTime":"2018-09-04T14:20:00"},"Destination":{"Airport":{"AirportCode":"BLR","AirportName":"Hindustan","Terminal":"3","CityCode":"BLR","CityName":"Bangalore","CountryCode":"IN","CountryName":"India"},"ArrTime":"2018-09-04T15:20:00"},"Availability":[{"Class":"C","Seats":"4"},{"Class":"D","Seats":"4"},{"Class":"J","Seats":"4"},{"Class":"Z","Seats":"4"},{"Class":"Y","Seats":"9"},{"Class":"B","Seats":"9"},{"Class":"M","Seats":"9"},{"Class":"H","Seats":"9"},{"Class":"K","Seats":"9"},{"Class":"Q","Seats":"9"},{"Class":"V","Seats":"9"},{"Class":"W","Seats":"9"},{"Class":"G","Seats":"9"},{"Class":"L","Seats":"9"},{"Class":"U","Seats":"9"},{"Class":"T","Seats":"9"}],"Duration":"0","GroundTime":"0","Mile":"0","StopOver":"","StopPoint":"","StopPointArrivalTime":"2018-09-04T15:20:00","StopPointDepartureTime":"2018-09-04T14:20:00","Craft":"321","Remark":"","IsETicketEligible":"1","FlightStatus":"NotSet","Status":""}]],"LastTicketDate":"24Aug17","TicketAdvisory":"","AirlineCode":"AI","ValidatingAirline":"AI"}';
		
		$_Ainput['priceRbd']			= json_decode($_Ainput['priceRbd'],true);
		
		$_Ainput['priceRbd']['Segments'][0][0]['Airline']['FareClass'] = 'C';
		
		$_Ainput['currencyCode']		= 'INR';
		$_Ainput['cabinClass']			= '';
		$_Ainput['referenceId']			= 0;
		$_Ainput['hashKey']				= getSecureHash($_AuserInfo,$_Ainput);
					
		return $_Ainput;
	}
	
	function process()
	{
		$_AuserInfo	= getUserInfo();
		
		//$_Ainput	= getFlightSearchInput($_AuserInfo); 					//Working
		//$_Ainput	= getUpdatePriceInput($_AuserInfo);						//Working
		//$_Ainput	= createBooking($_AuserInfo);							//Working
		
		//$_Ainput	= orderRetrive($_AuserInfo);							//Working
		//$_Ainput	= orderReshop($_AuserInfo);								//Working
		//$_Ainput	= orderReshopPrice($_AuserInfo);						//Working
		//$_Ainput	= orderChange($_AuserInfo);								//Working		 
		

		//$_Ainput	= orderReshopRefund($_AuserInfo);	 					//Working
		//$_Ainput	= getCancelBookingInput($_AuserInfo);					// working 
		/* if it roundtrip, differential amount should be add seperately for Onward and return  */


		//$_Ainput	= getSeatAvailability($_AuserInfo);
		//$_Ainput	= orderChangeSeat($_AuserInfo);								//Working		

		//$_Ainput	= getServiceList($_AuserInfo);
		$_Ainput	= getUpdatePriceInput_AfterBooking_AddService($_AuserInfo);						//Working
		

		// $_Ainput	= getFareRulesInput($_AuserInfo);
		//$_Ainput	= getPnrBlockingInput($_AuserInfo);
		//$_Ainput	= getHoldCancelInput($_AuserInfo);
		// $_Ainput	= getGetBookingInput($_AuserInfo);
		// $_Ainput	= getCancelBookingInput($_AuserInfo);
		//$_Ainput	= getMakeTicketInput($_AuserInfo);
		//$_Ainput	= getGetCalendarFareInput($_AuserInfo);
		//$_Ainput	= getPriceRbdInput($_AuserInfo);
		//$_Ainput	= ticket($_AuserInfo);
		
		
		
		$_SERVER["PHP_AUTH_PW"] = md5($_AuserInfo['password']);
		

		echo "<h1>"; echo $_Ainput['action'].'-'.@$_Ainput['service_name']; echo "</h1>";

		echo "<pre><h1>Input</h1>";
		print_r($_Ainput);

		$_Ocontroller	= Controller::singleton();
		$_Aoutput		= $_Ocontroller->process($_Ainput);
		
		// echo "<pre>";print_r(json_encode($_Aoutput));
		echo "<pre><h1>Output</h1>";print_r($_Aoutput);die;
	}
	
	process();

?>