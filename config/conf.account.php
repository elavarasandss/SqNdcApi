<?php
/**
	@File Name 		:	conf.account.php
	@Author 		:	Ramanathan M <ramanathan@dss.com.sg>
	@Created Date	:	2015-12-21 10:55 AM
	@Description	:	Allowed account details.
*/

	$CONF['account']['users'] =	array
								(
									'TMTB2C' => array
									(
										'credentials' => array
										(
											'TEST'	=> array
											(
												'clientId'		=> 'ApiIntegrationNew',
												'userName'		=> 'Thanjai',
												'password'		=> 'Thanjai@1234',
											),
											'LIVE'	=> array
											(
												'clientId'		=> 'ApiIntegrationNew',
												'userName'		=> 'Thanjai',
												'password'		=> 'Thanjai@1234',
											),
										),
										'payments' => array
										(
											'TEST'	=> array
											(
												
											),
											'LIVE'	=> array
											(
											),
										),
										'promotionCode' => array
										(
											'TEST'	=> array
											(
												'all' 	=> '',
												'SG' 	=> '',
											),
											'LIVE'	=> array
											(
												'all' 	=> '',
												'SG' 	=> '',
											),
										),
									),
									'Maxima' => array
									(
										'credentials' => array
										(
											'TEST'	=> array
											(
												'clientId'		=> 'ApiIntegrationNew',
												'userName'		=> 'BOMM289',
												'password'		=> 'BOM12@1234',
											),
											'LIVE'	=> array
											(
												'clientId'		=> 'ApiIntegrationNew',
												'userName'		=> 'BOMM289',
												'password'		=> 'BOM12@1234',
											),
										),
										'payments' => array
										(
											'TEST'	=> array
											(
												
											),
											'LIVE'	=> array
											(
											),
										),
										'promotionCode' => array
										(
											'TEST'	=> array
											(
												'all' 	=> '',
												'SG' 	=> '',
											),
											'LIVE'	=> array
											(
												'all' 	=> '',
												'SG' 	=> '',
											),
										),
									),
								);
?>