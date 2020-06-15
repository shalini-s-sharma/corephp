
<?php
// error_reporting(E_ALL);
// ini_set("display_errors", 1);
// include('simplehtmldom/simple_html_dom.php');

class ModelCourierDawnWing extends Model
{
    private $error;

    function scrapping($waybill_number)
    {
        $tracking_id      = '';
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


        $url   = "http://www.dawnwing.co.za/business-tools/online-parcel-tracking/";
        $param = "WaybillNo={awb_no}&parcel-search=go";
        $param = str_replace('{awb_no}', $waybill_number,$param);
        
        $response  = $this->postCurl($url,$param);
        // print_r(($response));die;
        if (empty($response)) {
            $error['error'] = 'No information found.Please try again.';
            return $error;
        }
        
        $html = str_get_html($response);
        if (empty($html)) {
            $error['error'] = 'No information found.Please try again.';
            return $error;
        }

        $checkerror = $html->find('div[class="post-entry"] p',0)->innertext ?? '';
        if(!empty($checkerror)){
            $error['error'] = 'No information found.Please try again.';
            return $error;
        }
       
        $table = $html->find('table');
        $table_desc = $table[1] ?? '';
        $i = 0;
        if(!empty($table_desc)){
            foreach ($table_desc->find('tr') as $row){
                $stats = $row->find('td');  
                if (isset($stats[0]) && !empty($stats[0]->plaintext))
                {
                    $event = $stats[0]->plaintext ?? '';
                    $location = $stats[1]->plaintext ?? '';
                    $date = $stats[2]->plaintext ?? '';
                    $time = $stats[3]->plaintext ?? '';

                    if(!empty($date)){
                        $finaldate = $date.' '.$time;
                        $scan[$i]['date']     = !empty($finaldate) ? date('Y-m-d', strtotime(str_replace('/', '-',$finaldate))) : '';
                        $scan[$i]['time']     = !empty($finaldate) ? date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $finaldate))) : '';
                        $scan[$i]['location'] = $location ?? '';
                        $scan[$i]['details']  = $event ?? '';
                        $pickupdate = $scan[$i]['time'] ?? '';
                        $destination_to = $scan[$i]['location'] ?? '';
                        $current_status = $scan[$i]['details'] ?? '';
                        $status_date    = $scan[$i]['date'] ?? '';
                        $status_time    = $scan[$i]['time'] ?? '';
                        $i++;
                    }
                }
                
            }
        }

        $table_pod = $table[0] ?? ''; 
        if(!empty($table_pod)){
            foreach ($table_pod->find('tr') as $row){
                $stats = $row->find('td');
                $date_received = $stats[1]->plaintext ?? '';
                $time_received = $stats[2]->plaintext ?? '';
                $status_time = $date_received .''.$time_received;  
            }
        }

        $div = $html->find('div[class="box"]');
        if(!empty($div)){
                // box 1
            foreach($div[0]->find('h2') as $item) {
                    $item->innertext = '';
            }
            $text = str_replace('<h2></h2> ','',$div[0]->innertext);
            $text =  html_entity_decode($text);
            $text = preg_replace('/<br[^>]*>/i',',', $text);
            $arr = explode(',',$text);
            foreach($arr as $value){
                $val = trim($value);
                $a = explode(':',$val);
                if(!empty($a[0])){
                    $val = trim($a[0]);
                    if($val == 'Waybill Number'){
                        $tracking_id = trim($a[1]);
                    }
                    if($val == 'Waybill Status'){
                        $status = trim($a[1]);
                    } 
                    if($val == 'From'){
                        $destination_from = trim($a[1]);
                    }
                    
                }  
            }

            // box 2
            foreach($div[1]->find('h2') as $item) {
                $item->innertext = '';
            }                                                                                                                                                                                                                                                                                                                               
            $text = str_replace('<h2></h2> ','',$div[1]->innertext);
            $text =  html_entity_decode($text);
            $text = preg_replace('/<br[^>]*>/i',',', $text);
            $arr = explode(',',$text);
            foreach($arr as $value){
                $val = trim($value);
                $a = explode(':',$val);
                if(!empty($a[0])){
                    $val = trim($a[0]);
                    if($val == 'Waybill Date'){
                        $pickupdate = trim($a[1]);
                    }
                    if($val == 'To'){
                        $destination_to = trim($a[1]);
                    } 
                    if($val == 'Service'){
                        $return_array['Service'] = trim($a[1]);
                    }
                }
            }

        }
       
        $html->clear();
        unset($html);
        krsort($scan);
        $return_array['scan'] = $scan;
        $return_array['tracking_id'] = $tracking_id ?? '';
        $return_array['destination_from'] = ($scan[0]['location']) ?? '';
        $return_array['destination_to']   = $destination_to ?? '';
        $return_array['status']           = !empty($current_status) ? $current_status : "";
        $return_array['current_status']   = !empty($current_status) ? $current_status : "";
        $return_array['status_date'] = $status_date;
        $return_array['status_time'] = $status_time;
        $return_array['pickupdate']  = $pickupdate ?? '';

        $return_array['onj_status'] = $this->statusMapping($current_status);
    
        return $return_array;  
    }


    function postCurl($url,$params){
        $curl = curl_init();
        $parameters = $params;
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $parameters,
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "postman-token: 366befd3-c02f-8ad0-3756-f8e0264d9114"
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

// $track = $_GET['track_id'];
//"1321633574"

// $object = New ModelCourierDawnWing();
// $data = $object->scrapping($track);
// echo '<pre>';print_r($data);die;
// include('view.php');
// print_r($data);



















