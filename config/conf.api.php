<?php
/**
	@File Name 		:	conf.api.php
	@Author 		:	Ramanathan M <ramanathan@dss.com.sg>
	@Created Date	:	2015-12-21 10:55 AM
	@Description	:	Declaring all web service api settings.
*/

	$CONF['api']['url'] = array
						(
							'TEST' => array
							(
								/* 'flightSearch'				=> 'https://nodea1.test.webservices.amadeus.com/1ASIWNDCSQ',
								'fareQuote'					=> 'https://nodeA1.test.webservices.amadeus.com/1ASIWNDCSQ',
								'seatAvailability'			=> 'https://nodeA1.test.webservices.amadeus.com/1ASIWNDCSQ',
								'serviceList'				=> 'https://nodeA1.test.webservices.amadeus.com/1ASIWNDCSQ',
								'cancelPnr'					=> 'https://nodeA1.test.webservices.amadeus.com/1ASIWNDCSQ', */
								
								'flightSearch'				=> 'https://nodea3.test.webservices.amadeus.com/1ASIWCLTSQ',
								'fareQuote'					=> 'https://nodea3.test.webservices.amadeus.com/1ASIWCLTSQ',
								'seatAvailability'			=> 'https://nodea3.test.webservices.amadeus.com/1ASIWCLTSQ',
								'serviceList'				=> 'https://nodea3.test.webservices.amadeus.com/1ASIWCLTSQ',
								'cancelPnr'					=> 'https://nodea3.test.webservices.amadeus.com/1ASIWCLTSQ',
								
							), 
							'LIVE' => array
							(
								
							),
						);
							
	$CONF['api']['credentials']	= array
								(
									'TEST'	=> array
										(

											/* //Test API
											'clientId'		=> 'ApiIntegrationNew',
											'userName'		=> 'WSSQNDC',				//LSS ID
											'password'		=> 'uJ4uAEmFt6q)xlzVqUmB0GtYA',
											'PseudoCityCode'=> 'NDCSQ08SQ',
											'WSAP'			=> '1ASIWNDCSQ', */
											
											//Preproduction API
											'clientId'		=> 'ApiIntegrationNew',
											'userName'		=> 'WSSQPVA',						//LSS ID
											'password'		=> 'xwPNSXS5w_SpGKM8uGeq72F98',
											'PseudoCityCode'=> 'NDCSQ08SQ',
											'WSAP'			=> '1ASIWCLTSQ',
											'CountryCode'	=> 'IN',
											'currencyCode'	=> 'INR',
											'AgencyId'		=> '14360732',
											'IataNumber'	=> '14360732',
											'AgentName'		=> 'PREVEEN AIR TRAVELS PRIVATE LIMITED',

											

										),
									'LIVE'	=> array
											(
												'clientId'		=> 'tboprod',
												'userName'		=> 'MAAA580',
												'password'		=> 'live-tbo@@580',
											),
								);
	$CONF['api']['GSTField']	= array
								(
									'TEST'	=> array
											(
												'GSTNumber'					=> '33AAFCN6097A1ZM',
												'GSTCompanyName'			=> 'ELA TOURS AND TRAVELS',
												'GSTCompanyAddress'			=> 'CHENNAI',
												'GSTCompanyEmail'			=> 'ELAVARASAN@DSS.COM.SG',
												'GSTCompanyContactNumber'	=> '9790657965',
											),
									'LIVE'	=> array
											(
												'GSTNumber'					=> '33AAFCN6097A1ZM',
												'GSTCompanyName'			=> 'ELA TOURS AND TRAVELS',
												'GSTCompanyAddress'			=> 'CHENNAI',
												'GSTCompanyEmail'			=> 'ELAVARASAN@DSS.COM.SG',
												'GSTCompanyContactNumber'	=> '9790657965',
											),
								);
									
	$CONF['api']['actions'] 	= array
								(
									'logon' => array
									(
										'className'  	=> 'logon',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'logonXml.tpl',
										'actionName'	=> '',
									),
									'logout' => array
									(
										'className'  	=> 'logout',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'logoutXml.tpl',
										'actionName'	=> '',
									),
									'getAgencyBalance' => array
									(
										'className'  	=> 'getAgencyBalance',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'getAgencyBalanceXml.tpl',
										'actionName'	=> '',
									),
									'getFlightSchedules' => array
									(
										'className'  	=> 'getFlightSchedules',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'getFlightSchedulesXml.tpl',
										'soapAction'	=> 'http://webservices.amadeus.com/NDC_AirShopping_18.1',
									),
									'updatePrice' => array
									(
										'className'  	=> 'updatePrice',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'updatePriceXml.tpl',
										'actionName'	=> '',
									),
									'fareQuote' => array
									(
										'className'  	=> 'fareQuote',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'fareQuoteXml.tpl',
										'soapAction'	=> 'http://webservices.amadeus.com/NDC_OfferPrice_18.1',
									),
									'getSeatAvailability' => array
									(
										'className'  	=> 'getSeatAvailability',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'getSeatAvailabilityXml.tpl',
										'soapAction'	=> 'http://webservices.amadeus.com/NDC_SeatAvailability_18.1',
									),
									'getServiceList' => array
									(
										'className'  	=> 'getServiceList',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'getServiceListXml.tpl',
										'soapAction'	=> 'http://webservices.amadeus.com/NDC_ServiceList_18.1',
									),
									'getCreateBooking' => array
									(
										'className'  	=> 'getCreateBooking',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'CreateBookingXml.tpl',
										'soapAction'	=> 'http://webservices.amadeus.com/NDC_OrderCreate_18.1',
									),
									'orderRetrive' => array
									(
										'className'  	=> 'orderRetrive',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'orderRetriveXml.tpl',
										'soapAction'	=> 'http://webservices.amadeus.com/NDC_OrderRetrieve_18.1',
									),
									'orderReshop' => array
									(
										'className'  	=> 'orderReshop',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'orderReshopXml.tpl',
										'soapAction'	=> 'http://webservices.amadeus.com/NDC_OrderReshop_18.1',
									),
									'orderChange' => array
									(
										'className'  	=> 'orderChange',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'orderChangeXml.tpl',
										'soapAction'	=> 'http://webservices.amadeus.com/NDC_OrderChange_18.1',
									),
									'cancelBooking' => array
									(
										'className'  	=> 'cancelBooking',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'cancelBookingXml.tpl',
										'soapAction'	=> 'http://webservices.amadeus.com/NDC_OrderCancel_18.1',
									),
									'getFareRules' => array
									(
										'className'  	=> 'getFareRules',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> '',
										'actionName'	=> '',
									),
									'fareRule' => array
									(
										'className'  	=> 'fareRule',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'fareRuleXml.tpl',
										'actionName'	=> '',
									),
									'getSsr' => array
									(
										'className'  	=> 'getSsr',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'getSsrXml.tpl',
										'actionName'	=> '',
									),
									'pnrBlocking' => array
									(
										'className'  	=> 'pnrBlocking',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'pnrBlockingXml.tpl',
										'actionName'	=> '',
									),
									'book' => array
									(
										'className'  	=> 'book',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'bookXml.tpl',
										'actionName'	=> '',
									),
									'ticket' => array
									(
										'className'  	=> 'ticket',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'ticketXml.tpl',
										'actionName'	=> '',
									),
									'getBooking' => array
									(
										'className'  	=> 'getBooking',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'getBookingXml.tpl',
										'actionName'	=> '',
									),
									'makeTicket' => array
									(
										'className'  	=> 'makeTicket',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'makeTicketXml.tpl',
										'actionName'	=> '',
									),
									'holdCancel' => array
									(
										'className'  	=> 'holdCancel',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'holdCancelXml.tpl',
										'actionName'	=> '',
									),
									'getTicketDetails' => array
									(
										'className'  	=> 'getTicketDetails',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'getTicketDetailsXml.tpl',
										'actionName'	=> '',
									),
									'getCalendarFare' => array
									(
										'className'  	=> 'getCalendarFare',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'getCalendarFareXml.tpl',
										'actionName'	=> '',
									),
									'updateCalendarFare' => array
									(
										'className'  	=> 'updateCalendarFare',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'updateCalendarFareXml.tpl',
										'actionName'	=> '',
									),
									'priceRbd' => array
									(
										'className'  	=> 'priceRbd',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'priceRbdXml.tpl',
										'actionName'	=> '',
									),
								);
								$CONF['api']['AllowUser']	= array('TRIPPERSZONE','TMTB2C');
								
								