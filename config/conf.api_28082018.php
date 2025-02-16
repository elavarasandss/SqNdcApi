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
								'logon'				=> 'http://api.tektravels.com/SharedServices/SharedData.svc/rest/Authenticate',
								'logout'			=> 'http://api.tektravels.com/SharedServices/SharedData.svc/rest/Logout',
								'getAgencyBalance'	=> 'http://api.tektravels.com/SharedServices/SharedData.svc/rest/GetAgencyBalance',
								'flightSearch'		=> 'http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/Search/',
								'fareQuote'			=> 'http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/FareQuote/',
								'getSsr'			=> 'http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/SSR/',
								'fareRule'			=> 'http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/FareRule/',
								'book'				=> 'http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/Book/',
								'ticket'			=> 'http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/Ticket/',
								'getBookingDetails'	=> 'http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/GetBookingDetails/',
								'getBookingDetails'	=> 'http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/GetBookingDetails/',
								'releasePnr'		=> 'http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/ReleasePNRRequest/',
								'cancelPnr'			=> 'http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/SendChangeRequest/',
								'getCalendarFare'	=> 'http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/GetCalendarFare/',
								'updateCalendarFare'=> 'http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/UpdateCalendarFareOfDay/',
								'priceRbd'			=> 'http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/PriceRBD/',
							),
							'LIVE' => array
										(
											
										),
						);
							
	$CONF['api']['credentials']	= array
								(
									'TEST'	=> array
											(
												'clientId'		=> 'ApiIntegrationNew',
												'userName'		=> 'NUGDS',
												'password'		=> 'NUGDS@123',
											),
									'LIVE'	=> array
											(
												
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
										'actionName'	=> '',
									),
									'updatePrice' => array
									(
										'className'  	=> 'updatePrice',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> '',
										'actionName'	=> '',
									),
									'fareQuote' => array
									(
										'className'  	=> 'fareQuote',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'fareQuoteXml.tpl',
										'actionName'	=> '',
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
									'cancelBooking' => array
									(
										'className'  	=> 'cancelBooking',
										'headerTpl' 	=> 'headerXml.tpl',
										'templateName' 	=> 'cancelBookingXml.tpl',
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