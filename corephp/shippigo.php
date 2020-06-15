
<?php
// error_reporting(E_ALL);
// ini_set("display_errors", 1);
// include('simplehtmldom/simple_html_dom.php');
class ModelCourierShippigo extends Model
{
    private $error;

    function scrapping($waybill_number)
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


        $url       = "https://app.shippigo.com/api/trackFront";
        $param = "awb={awb_no}";
        $param = str_replace('{awb_no}', $waybill_number,$param);
        $data = $this->postCurl($url,$param);
        $data = json_decode($data,1);

        if (empty($data['success'])) {
            $error['error'] = 'No information found.Please try again.';
            return $error;
        }
        
        if(!empty($data['status']) && $data['status'] == 400){
            $error['error'] = 'No information found.Please try again.';
            return $error;
        }

        $i = 0;
        if(!empty($data['data'])){
            foreach($data['data'] as $key => $value){
            $date = !empty($value['updatedOn']) ? $value['updatedOn'] : '';
            $location = !empty($value['place']) ? $value['place'] : '';

            if(!empty($date)){
                $date = date('d/m/Y',strtotime($date));
                $scan[$i]['date']     = !empty($date) ? date('Y-m-d', strtotime(str_replace('/', '-',$date))) : '';
                $scan[$i]['time']     = !empty($date) ? date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $date))) : '';
                $scan[$i]['location'] = !empty($location) ? $location : '';
                $scan[$i]['details']  = !empty($value['details']) ? $value['details'] : '';
                $pickupdate = !empty($scan[$i]['time']) ? $scan[$i]['time'] : '';
                $destination_from = !empty($scan[$i]['location']) ? $scan[$i]['location'] : '';
                $i++;
            }  
        }
        $current_status = !empty($scan[0]['details']) ? $scan[0]['details'] : '';
        $status_date    = !empty($scan[0]['date']) ? $scan[0]['date'] : '';
        $status_time    = !empty($scan[0]['time']) ? $scan[0]['time'] : '';
        $destination_to = !empty($scan[0]['location']) ? $scan[0]['location'] : '';
        $return_array['scan'] = $scan;
        $return_array['destination_from'] = !empty($destination_from) ? $destination_from : '';
        $return_array['destination_to']   = !empty($destination_to) ? $destination_to : '';
        $return_array['status']           = !empty($current_status) ? $current_status : "";
        $return_array['current_status']   = !empty($current_status) ? $current_status : "";
        $return_array['status_date'] = $status_date;
        $return_array['status_time'] = $status_time;
        $return_array['pickupdate']  = !empty($pickupdate) ? $pickupdate : '';
        $return_array['onj_status'] = $this->statusMapping($current_status);
        }

        return $return_array;  
    }


    function postCurl($url,$params){

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $params,
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "postman-token: 366befd3-c02f-8ad0-3756-f8e0264d9114"
          ),
        ));

        $response = curl_exec($curl);
          preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches);
          $cookies = '';
          foreach($matches[1] as $item) {
              $cookies = $item;
          }
          if(!empty($cookies)){
              curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                  "Connection: keep-alive",
                  "Cache-Control: max-age=0",
                  "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.132 Safari/537.36",
                  "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
                  "Cookie: $cookies"
                ),
              ));
            
            $response = curl_exec($curl);
          }
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



// $track = $_GET['track_id'];
// //"2844510164533"
// $object = New ModelCourierShippigo();
// $data = $object->scrapping($track);
//  echo '<pre>';print_r($data);die;
// include('view.php');
// print_r($data);

