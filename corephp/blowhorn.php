
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
include('simplehtmldom/simple_html_dom.php');
class ModelCourierBlowHorn 
{
    private $error;

    function api_scrapping($waybill_number = '',$credential)
    {
        $status_date      = '';
        $status_time      = '';
        $pickupdate       = '';
        $destination_from = '';
        $destination_to   = '';
        $recipient        = '';
        $current_status   = '';
        $sender           = '';
        $extras           = array();
        $return_array     = array();
        $scan             = array();
       // print_r($credential['loginid']);die;
        if (!isset($credential['api_key'])) {
            $return_array['error'] = 'You are not authorised!';
            return $return_array;
        }
        $api_key = !empty($credential['api_key']) ? $credential['api_key'] : '';
        $ref_no = !empty($credential['ref_no']) ? $credential['ref_no'] : '';
        if(empty($api_key)){
            $return_array['error'] = 'You are not authorised!';
            return $return_array;
        }

        if(empty($ref_no)){
            $return_array['error'] = 'Reference number is required!';
            return $return_array;
        }

        $preUrl = 'https://blowhorn.com/api/orders/shipment/{order_id}';
        $preUrl = str_replace('{order_id}', $order_id, $preUrl);
        
        $response = $this->getCurl($preUrl,$api_key);

        $result = json_decode($response,1);
        if($result['status'] == 'FAIL'){
                $error['error'] = $result['message'];
                return $error;
        }

        if(!empty($result['message']['order_details']['awb_number'])){
            $waybill_number = $result['message']['order_details']['awb_number'];
        }

        if(empty($waybill_number)){
            $return_array['error'] = 'AWB Number is required!';
            return $return_array;
        }
        
        $url       = "https://blowhorn.com/api/orders/{awb_no}/status/history";

        $url = str_replace('{awb_no}', $waybill_number, $url);
       
        $response = $this->getCurl($url,$api_key);
        
       $result = json_decode($response,1);
       if($result['status'] == 'FAIL'){
            $error['error'] = $result['message'];
            return $error;
       }
       
        if(!empty($result['message'])){
           $i = 0;
           foreach ($result['message'] as $value) {
            $date = !empty($value['time']) ? $value['time'] : '';
            $location = !empty($value['location']) && is_array($value['location']) ? '' : '';
            $details = !empty($value['status']) ? $value['status'] : '';
            $status = !empty($value['status']) ? $value['status'] : '';
            if(!empty($date)){
                $scan[$i]['date']     = !empty($date) ? date('Y-m-d', strtotime(str_replace('/', '-',$date))) : '';
                $scan[$i]['time']     = !empty($date) ? date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $date))) : '';
                $scan[$i]['location'] = !empty($location) ? $location  : '';
                $scan[$i]['details']  = !empty($details) ? $details : '';
                $pickupdate = $scan[$i]['time'];
                $destination_to = $scan[$i]['location'];
                $current_status = $status;
                $i++;
            }
           }
        }
        krsort($scan);
        $status_date    = !empty($scan[0]['date']) ? $scan[0]['date'] : '';
        $status_time    = !empty($scan[0]['time']) ? $scan[0]['time'] : '';
        $return_array['scan'] = $scan;
        $return_array['destination_from'] = !empty($scan[0]['location']) ? $scan[0]['location'] : '';
        $return_array['destination_to']   = !empty($destination_to) ? $destination_to : '';
        $return_array['status']           = !empty($current_status) ? $current_status : "";
        $return_array['current_status']   = !empty($current_status) ? $current_status : "";
        $return_array['status_date'] = $status_date;
        $return_array['status_time'] = $status_time;
        $return_array['pickupdate']  = !empty($pickupdate) ? $pickupdate : '';
        $return_array['onj_status'] = $this->statusMapping($current_status);
        return $return_array;  
    }

    function getCurl($url,$api_key){
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
              "API_KEY: $api_key"
            ),
          ));
      
        $response = curl_exec($curl);
        $err = curl_error($curl);
    
        curl_close($curl);
    
        if ($err) {
          return "cURL Error #:" . $err;
        } else {
          return $response;
        }
    
      }
      private function clean($value)
      {
          return preg_replace('!\s+!', ' ', trim(strip_tags($value)));
      }
  
      public function statusMapping($status)
      {
          $status = strtoupper(str_replace('&nbsp;', '', trim(addslashes(strip_tags($status)))));
          
          $status_data = $this->registry->get('carrier_status', '221');
          
           
  
          if (isset($status_data[$status])) {
              return $status_data[$status];
          } else if (strstr($status, "YOUR SHIPMENT IS READY FOR CLAIMING")) {
              return 'OOD';
          }
          else if (strstr($status, "ARRIVED AT COD EXCHANGE")) {
              return 'INT';
          }
          else if (strstr($status, "ARRIVED AT")) {
              return 'INT';
          }
          else if (strstr($status, "READY FOR DELIVERY")) {
              return 'OOD';
          }else if (strstr($status, "FORWARDED TO")) {
              return 'INT';
          }
          else if (strstr($status, "ACCEPTED AT")) {
              return 'SCH';
          } 
          else if (strstr($status, "SHIPMENT DELIVERED")) {
              return 'DEL';
          }
          else if (strstr($status, "DELIVERED")) {
              return 'DEL';
          } 
          else if (strstr($status, "PACKAGE DELIVERED")) {
              return 'DEL';
          }
         else if (strstr($status, "RECEIVED BY")) {
             return 'DEL';
          }
          else if (strstr($status, "CONSIGNMENT DELIVERED")) {
              return 'DEL';
          }
          else if (strstr($status, "DELIVERED")) {
              return 'DEL';
          }
          else if (strstr($status, "YOUR SHIPMENT WAS NOT DELIVERED DUE TO AN UNFORESEEN EVENT. PLEASE GET IN TOUCH WITH US THROUGH OUR OFFICIAL CUSTOMER CARE CHANNELS")) {
              return 'UND';
          }
           else if (strstr($status, "READY FOR DELIVERY. PLEASE EXPECT DELIVERY WITHIN THE DAY")) {
              return 'OOD';
          } else if (strstr($status, "ARRIVED AT JOLO")) {
              return 'INT';
          }else if (strstr($status, "ARRIVED AT")) {
              return 'INT';
          } else if (strstr($status, "FORWARDED TO")) {
              return 'INT';
          } else if (strstr($status, "ARRIVED AT ZAMBOANGA DISTRIBUTION TEAM")) {
              return 'INT';
          } else if (strstr($status, "FORWARDED TO ZAMBOANGA DISTRIBUTION TEAM")) {
              return 'INT';
          } else if (strstr($status, "ARRIVED AT GENERAL SANTOS DISTRIBUTION TEAM")) {
              return 'INT';
          }else if (strstr($status, "ACCEPTED AT")) {
              return 'SCH';
          }else if (strstr($status, "WE TRIED TO DELIVER YOUR SHIPMENT BUT RECIPIENT IS UNKNOWN AT THE GIVEN ADDRESS. PLEASE GET IN TOUCH WITH US THROUGH OUR OFFICIAL CUSTOMER CARE CHANNELS")) {
              return 'UND';
          }else if (strstr($status, "WE TRIED TO DELIVER YOUR SHIPMENT BUT THERE WAS A REQUEST TO POSTPONE THE DELIVERY. PLEASE GET IN TOUCH WITH US THROUGH OUR OFFICIAL CUSTOMER CARE CHANNELS")) {
              return 'UND';
          }else if (strstr($status, "CLAIMED BY")) {
              return 'DEL';
          }else if (strstr($status, "CLAIMED AT")) {
              return 'DEL';
          }else if (strstr($status, "RECEIVED")) {
             return 'DEL';
          }else if (strstr($status, "BRANCH COLLECT TRACKING NUMBER HAS BEEN SENT VIA SMS. UPON PRESENTATION OF VALID IDS, THE AMOUNT CAN BE CLAIMED/WITHDRAWN AT ANY LBC BRANCH")) {
             return 'DEL';
          }else if (strstr($status, "RELEASED TO AUTHORIZED REPRESENTATIVE")) {
             return 'DEL';
          }else if (strstr($status, "REMITTANCE HAS BEEN CLAIMED")) {
             return 'DEL';
          }else if (strstr($status, "SHIPMENT IS WITH A CONCERN. PLEASE GET IN TOUCH WITH US THROUGH OUR OFFICIAL CUSTOMER CARE CHANNELS")) {
             return 'SMD';
          }else if (strstr($status, "WE TRIED TO DELIVER YOUR SHIPMENT BUT RECIPIENT\'S ADDRESS CANNOT BE LOCATED. PLEASE GET IN TOUCH WITH US THROUGH OUR OFFICIAL CUSTOMER CARE CHANNELS")) {
             return '22';
          }else if (strstr($status, "SHIPMENT WAS RETURNED TO THE SENDER")) {
             return 'RTD';
          }else if (strstr($status, "YOUR SHIPMENT WAS NOT DELIVERED DUE TO PAYMENT ISSUES DURING DELIVERY. PLEASE GET IN TOUCH WITH US THROUGH OUR OFFICIAL CUSTOMER CARE CHANNELS")) {
             return 'UND';
          }else if (strstr($status, "YOUR SHIPMENT WAS NOT DELIVERED")) {
             return 'UND';
          }
          return $status;
      }
}


//"SH-1JJWSHO"
$track = $_GET['track_id'] ?? '';
//2006622838
$ref_no = $_GET['ref_no'];
//MGCJqKlh36Epo4SX1D0rm2xLFWfsPU
$api_key['api_key'] = $_GET['api_key'];
$api_key['ref_no'] = $_GET['ref_no'];

$object = New ModelCourierBlowHorn();
$data = $object->api_scrapping($track,$api_key);
echo '<pre>';print_r($data);die;
// include('view.php');
// print_r($data);

