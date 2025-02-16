CREATE TABLE IF NOT EXISTS `tbo_security_token_details` (
  `security_token_id` int(11) NOT NULL AUTO_INCREMENT,
  `reference_id` varchar(300) DEFAULT NULL,
  `security_token` text,
  `status` enum('','A','B','C','D') DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  PRIMARY KEY (`security_token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS tbo_update_price
(
	update_price_id int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
	account_id int(11),
	trace_id varchar(300) DEFAULT NULL,
	api_mode varchar(10),
	default_total double,
	markup_data text,
	created_date datetime
);

INSERT INTO `api` (`api_name`, `api_type`, `status`, `updated_by`, `created_date`, `updated_on`)
SELECT 'SQNDC', 1, '1', '1', '2018-07-11 00:00:00', '2018-07-11 13:02:20'
FROM `api`
WHERE ((`api_id` = '33'));

INSERT INTO `account_credentials_details` (`account_id`, `api_id`, `api_mode`, `current_mode`, `user_name`, `password`, `salt`, `ip_patching`, `allowed_ips`, `hash_string`, `hash_separator`, `hash_encryption_type`, `log_folder`, `validate_secure_hash`, `created_by`, `created_date`, `last_updated_by`, `last_updated_date`)
SELECT '19', '34', 1, 1, 'PREVEENTRAVELS', 'preveentravelssq2win', 'preveentravelssq@salt', 'N', '', 'userName|action|mode|ADT|CHD|INF|currencyCode|pnr|sectorInfo|salt', '|', 'md5', '', 'Y', '1', '2017-12-18 07:20:07', NULL, NULL
FROM `account_credentials_details`
WHERE `account_id` = '19' AND ((`credentials_id` = '1933'));


truncate `asiatravel_facility`;

truncate `asiatravel_hotel`;

truncate `asiatravel_images`;

