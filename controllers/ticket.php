<?php
/**
	@File Name 		:	ticket.php
	@Author 		:	Ramanathan 
	@Created Date	:	2016-10-20 10:55 AM
	@Description	:	ticket service
*/
class ticket extends Execute
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
		$this->_SrequestUrl = $this->_Oconf['userSettings']['apiUrl']['ticket'];
	}
	
    public function _doTicket()
	{
		$_Bstatus  = true;
		$_Smessage = '';
		$_Adata	   = array();
		$_Areturn  = array();
		
		$this->_modifyData();
		$this->_setData();
		
		$_AticketResult = $this->_executeService();  
		
		//$_AticketResult = $this->fun1();     //satish						//-same code
		//$_AticketResult = 	json_decode($_AticketResult,true); 		//-same code
		
		//echo "<pre>";print_r($_AticketResult);die;
		
		$_AticketResponse = array();
		
		if(isset($_AticketResult['Response']['ResponseStatus']) && $_AticketResult['Response']['ResponseStatus'] == 1){
			
			if(isset($_AticketResult['Response']['Response']['PNR']) && !empty($_AticketResult['Response']['Response']['PNR']) && isset($_AticketResult['Response']['Response']['BookingId']) && !empty($_AticketResult['Response']['Response']['BookingId'])){
				
				if(isset($_AticketResult['Response']['Response']['TicketStatus']) && $_AticketResult['Response']['Response']['TicketStatus'] == 1){
					$_AticketResponse	= $_AticketResult['Response']['Response'];
					
					$_OgetBooking		= controllerGet::getObject('getBooking',$this);
					$_AticketResponse	= $_OgetBooking->parseBookingData($_AticketResponse);
				}
				else{
					$_Bstatus  = false;
					$_Smessage = 'Invalid ticket status';
					
					if(isset($_AticketResult['Response']['Response']['Message'])){
						$_Smessage .= $_AticketResult['Response']['Response']['Message'];
					}
				}
			}
			else{
				$_Bstatus  = false;
				$_Smessage = 'Booking PNR or BookingId Not found';
			}
		}
		else{
			$_Bstatus = false;
			
			if(isset($_AticketResult['Response']['Error']['ErrorMessage']) && !empty($_AticketResult['Response']['Error']['ErrorMessage'])){
				$_Smessage = $_AticketResult['Response']['Error']['ErrorMessage'];
			}
			else{
				$_Smessage = 'Unknown ticket error';
			}
		}
		
		if(!$_Bstatus){
			$_Smessage .= " ( API Trace Id :- ".$this->_Ainput['flightTraceId'].")";
		}
		
		$_Areturn = array
					(
						'status' => $_Bstatus,
						'data'   => $_AticketResponse, 
						'msg'    => $_Smessage,
					);
					
		return $_Areturn;
	}
	function fun1()
	{
		$json='{
  "Response": {
    "B2B2BStatus": false,
    "Error": {
      "ErrorCode": 0,
      "ErrorMessage": ""
    },
    "ResponseStatus": 1,
    "TraceId": "e1566c4c-4005-4021-9d95-47b076c86843",
    "Response": {
      "PNR": "SD7H5F",
      "BookingId": 1816849,
      "SSRDenied": false,
      "SSRMessage": null,
      "Status": 1,
      "IsPriceChanged": false,
      "IsTimeChanged": false,
      "FlightItinerary": {
        "CommentDetails": null,
        "IssuancePcc": "OTI011",
        "JourneyType": 1,
        "TripIndicator": 1,
        "BookingAllowedForRoamer": true,
        "BookingId": 1816849,
        "IsCouponAppilcable": true,
        "IsManual": false,
        "PNR": "SD7H5F",
        "IsDomestic": false,
        "ResultFareType": "RegularFare",
        "Source": 82,
        "Origin": "SIN",
        "Destination": "TRZ",
        "AirlineCode": "6E",
        "LastTicketDate": "2023-06-02T18:31:47",
        "ValidatingAirlineCode": "6E",
        "AirlineRemark": " 6E1",
        "IsLCC": true,
        "NonRefundable": false,
        "FareType": "PUB",
        "CreditNoteNo": null,
        "Fare": {
          "Currency": "INR",
          "BaseFare": 4005,
          "Tax": 3787,
          "TaxBreakup": [
            {
              "key": "K3",
              "value": 0
            },
            {
              "key": "TotalTax",
              "value": 3787
            },
            {
              "key": "OtherTaxes",
              "value": 3787
            }
          ],
          "YQTax": 0,
          "AdditionalTxnFeeOfrd": 0,
          "AdditionalTxnFeePub": 0,
          "PGCharge": 0,
          "OtherCharges": 0,
          "ChargeBU": [
            {
              "key": "TBOMARKUP",
              "value": 0
            },
            {
              "key": "GLOBALPROCUREMENTCHARGE",
              "value": 0
            },
            {
              "key": "OTHERCHARGE",
              "value": 0
            },
            {
              "key": "CONVENIENCECHARGE",
              "value": 0
            }
          ],
          "Discount": 0,
          "PublishedFare": 8617,
          "CommissionEarned": 0,
          "PLBEarned": 0,
          "IncentiveEarned": 0,
          "OfferedFare": 8617,
          "TdsOnCommission": 0,
          "TdsOnPLB": 0,
          "TdsOnIncentive": 0,
          "ServiceFee": 0,
          "TotalBaggageCharges": 0,
          "TotalMealCharges": 425,
          "TotalSeatCharges": 400,
          "TotalSpecialServiceCharges": 0
        },
        "CreditNoteCreatedOn": null,
        "Passenger": [
          {
            "BarcodeDetails": {
              "Id": 2982189,
              "Barcode": [
                {
                  "Index": 1,
                  "Format": "PDF417",
                  "Content": "M1VIVEGANANTHAN/LOGESH SD7H5F SINTRZ6E 1008 196Y006B00000000",
                  "BarCodeInBase64": null,
                  "JourneyWayType": 3
                }
              ]
            },
            "DocumentDetails": null,
            "GuardianDetails": null,
            "PaxId": 2982189,
            "Title": "Mr",
            "FirstName": "Logesh",
            "LastName": "Vivegananthan",
            "PaxType": 1,
            "DateOfBirth": "1995-06-10T21:00:00",
            "Gender": 1,
            "IsPANRequired": false,
            "IsPassportRequired": false,
            "PAN": "",
            "PassportNo": "4567776767",
            "PassportExpiry": "2030-01-17T21:00:00",
            "AddressLine1": "07-68 Vertex Tower A",
            "AddressLine2": "33 Ubi Avenue 3",
            "Fare": {
              "Currency": "INR",
              "BaseFare": 4005,
              "Tax": 3787,
              "TaxBreakup": [
                {
                  "key": "K3",
                  "value": 0
                },
                {
                  "key": "TotalTax",
                  "value": 3787
                },
                {
                  "key": "OtherTaxes",
                  "value": 3787
                }
              ],
              "YQTax": 0,
              "AdditionalTxnFeeOfrd": 0,
              "AdditionalTxnFeePub": 0,
              "PGCharge": 0,
              "OtherCharges": 0,
              "ChargeBU": [
                {
                  "key": "TBOMARKUP",
                  "value": 0
                },
                {
                  "key": "GLOBALPROCUREMENTCHARGE",
                  "value": 0
                },
                {
                  "key": "OTHERCHARGE",
                  "value": 0
                },
                {
                  "key": "CONVENIENCECHARGE",
                  "value": 0
                }
              ],
              "Discount": 0,
              "PublishedFare": 8617,
              "CommissionEarned": 0,
              "PLBEarned": 0,
              "IncentiveEarned": 0,
              "OfferedFare": 8617,
              "TdsOnCommission": 0,
              "TdsOnPLB": 0,
              "TdsOnIncentive": 0,
              "ServiceFee": 0,
              "TotalBaggageCharges": 0,
              "TotalMealCharges": 425,
              "TotalSeatCharges": 400,
              "TotalSpecialServiceCharges": 0
            },
            "City": "Singapore",
            "CountryCode": "SG",
            "Nationality": "SG",
            "ContactNo": "6563744459",
            "Email": "logesh@dss.com.sg",
            "IsLeadPax": true,
            "FFAirlineCode": null,
            "FFNumber": "",
            "MealDynamic": [
              {
                "AirlineCode": "6E",
                "FlightNumber": "1008",
                "WayType": 2,
                "Code": "CJSW",
                "Description": 2,
                "AirlineDescription": "Chicken Tikka Sandwich ombo",
                "Quantity": 1,
                "Currency": "INR",
                "Price": 425,
                "Origin": "SIN",
                "Destination": "TRZ"
              }
            ],
            "SeatDynamic": [
              {
                "AirlineCode": "6E",
                "FlightNumber": "1008",
                "CraftType": "A320-186",
                "Origin": "SIN",
                "Destination": "TRZ",
                "AvailablityType": 1,
                "Description": 2,
                "Code": "6B",
                "RowNo": "6",
                "SeatNo": "B",
                "SeatType": 3,
                "SeatWayType": 2,
                "Compartment": 1,
                "Deck": 1,
                "Currency": "INR",
                "Price": 400
              }
            ],
            "Ssr": [],
            "Ticket": {
              "TicketId": 2107437,
              "TicketNumber": "SD7H5F",
              "IssueDate": "2023-06-02T18:01:48",
              "ValidatingAirline": "708",
              "Remarks": "",
              "ServiceFeeDisplayType": "ShowInTax",
              "Status": "OK",
              "ConjunctionNumber": "",
              "TicketType": "N"
            },
            "SegmentAdditionalInfo": [
              {
                "FareBasis": "RZINT",
                "NVA": "",
                "NVB": "",
                "Baggage": "0 Kg",
                "Meal": "1 Platter",
                "Seat": "6B",
                "SpecialService": ""
              }
            ]
          }
        ],
        "CancellationCharges": null,
        "Segments": [
          {
            "Baggage": "0 Kg",
            "CabinBaggage": "7 KG",
            "CabinClass": 2,
            "TripIndicator": 1,
            "SegmentIndicator": 1,
            "Airline": {
              "AirlineCode": "6E",
              "AirlineName": "IndiGo",
              "FlightNumber": "1008",
              "FareClass": "HO",
              "OperatingCarrier": ""
            },
            "AirlinePNR": "",
            "Origin": {
              "Airport": {
                "AirportCode": "SIN",
                "AirportName": "Changi",
                "Terminal": "1",
                "CityCode": "SIN",
                "CityName": "Singapore",
                "CountryCode": "SG",
                "CountryName": "Singapore"
              },
              "DepTime": "2023-07-15T06:10:00"
            },
            "Destination": {
              "Airport": {
                "AirportCode": "TRZ",
                "AirportName": "Tiruchirapally Civil",
                "Terminal": "",
                "CityCode": "TRZ",
                "CityName": "Tiruchirapally",
                "CountryCode": "IN",
                "CountryName": "India"
              },
              "ArrTime": "2023-07-15T07:45:00"
            },
            "Duration": 245,
            "GroundTime": 0,
            "Mile": 0,
            "StopOver": false,
            "FlightInfoIndex": "",
            "StopPoint": "",
            "StopPointArrivalTime": "0001-01-01T00:00:00",
            "StopPointDepartureTime": "0001-01-01T00:00:00",
            "Craft": "320",
            "Remark": null,
            "IsETicketEligible": true,
            "FlightStatus": "Confirmed",
            "Status": "HK",
            "FareClassification": null
          }
        ],
        "FareRules": [
          {
            "Origin": "SIN",
            "Destination": "TRZ",
            "Airline": "6E",
            "FareBasisCode": "RZINT",
            "FareRuleDetail": "The FareBasisCode is: RZINT<br /><b> These are just hand bag fares there is no checked in baggage for this fare.</b><br />These are Fare Rules for International Flights.<ul><li>Meal: Chargable.</li><li>Seat: Chargable.</li><li> HandBag: 7 Kg.</li><li>Check-in Baggage: 0 Kg.</li></ul><table border=\"1\"><tr><td rowspan=\"4\">Change fee <br /><b>(Fare Difference is also applicable)</b></td></tr><tr><td>No. of Days Left For Departure</td><td>International</td></tr><tr><td>0-3 (DAYS)</td><td>UPTO INR 5000</td></tr><tr><td>4 DAYS AND ABOVE</td><td>UPTO INR 4500</td></tr><tr><td rowspan=\"4\">Cancellation Fee<br/ ><b>or Airfare charges (Whichever is lower)</b></td></tr><tr><td>No. of Days Left For Departure</td><td>International</td></tr><tr><td>0-3 (DAYS)</td><td>UPTO INR 6500</td></tr><tr><td>4 DAYS AND ABOVE</td><td>UPTO INR 6000</td></tr></table><br />Subject to change without prior notice. <b>Note</b> : We should receive the request at least four hours prior to Airline Fare Rules Policy.<br/> <br/>",
            "FareRestriction": null
          }
        ],
        "MiniFareRules": [
          {
            "JourneyPoints": "SIN-TRZ",
            "Type": "Reissue",
            "From": "0",
            "To": "3",
            "Unit": "Days",
            "Details": "INR 5000"
          },
          {
            "JourneyPoints": "SIN-TRZ",
            "Type": "Reissue",
            "From": "4",
            "To": "",
            "Unit": "Days",
            "Details": "INR 4500"
          },
          {
            "JourneyPoints": "SIN-TRZ",
            "Type": "Cancellation",
            "From": "0",
            "To": "3",
            "Unit": "Days",
            "Details": "INR 6500"
          },
          {
            "JourneyPoints": "SIN-TRZ",
            "Type": "Cancellation",
            "From": "4",
            "To": "",
            "Unit": "Days",
            "Details": "INR 6000"
          }
        ],
        "PenaltyCharges": {},
        "Status": 5,
        "Invoice": [
          {
            "CreditNoteGSTIN": null,
            "GSTIN": "07AAVCS7504D1Z",
            "InvoiceCreatedOn": "2023-06-02T18:01:48",
            "InvoiceId": 6220,
            "InvoiceNo": "IW/2324/6220",
            "InvoiceAmount": 8617,
            "Remarks": "",
            "InvoiceStatus": 3
          }
        ],
        "InvoiceAmount": 8617,
        "InvoiceNo": "IW/2324/6220",
        "InvoiceStatus": 3,
        "InvoiceCreatedOn": "2023-06-02T18:01:48",
        "Remarks": "",
        "IsWebCheckInAllowed": false
      },
      "TicketStatus": 1
    }
  }
}';
		return $json; 
	}
}
?>