<head>
  <meta charset="UTF-8">
</head>
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
include('simplehtmldom/simple_html_dom.php');
require 'Curl.php';


class Courier extends Curl
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


        $url       = "http://www.dawnwing.co.za/business-tools/online-parcel-tracking/";
        $param = "WaybillNo={awb_no}&parcel-search=go";
        $param = str_replace('{awb_no}', $waybill_number,$param);
        $response  = $this->postCurl($url,$param);
        // print_r(empty($response));die;
        if (empty($response)) {
            $error['error'] = 'No information found.Please try again.';
            return $error;
        }
        
        $html = str_get_html($response);
        if (empty($html)) {
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
                    $event = $stats[0]->plaintext;
                    $location = $stats[1]->plaintext;
                    $date = $stats[2]->plaintext;
                    $time = $stats[3]->plaintext;

                    if(!empty($date)){
                        $return_array['details'][$date][$i]['Event']= $event;
                        $return_array['details'][$date][$i]['Location']= $location;
                        $finaldate = $date.''.$time;
                        $return_array['details'][$date][$i]['Date'] = date('D , h:i',strtotime($finaldate));
                        $scan[$i]['date']     = !empty($finaldate) ? date('Y-m-d', strtotime(str_replace('/', '-',$finaldate))) : '';
                        $scan[$i]['time']     = !empty($finaldate) ? date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $finaldate))) : '';
                        $scan[$i]['location'] = $location ?? '';
                        $scan[$i]['details']  = $event ?? '';
                        $pickupdate = $scan[$i]['time'];
                        $destination_to = $scan[$i]['location'];
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
       
        $current_status = $scan[0]['details'] ?? '';
        $status_date    = $scan[0]['date'] ?? '';
        $status_time    = $scan[0]['time'] ?? '';
        // uksort($return_array['details'],function($dt1,$dt2){
        //     $tm1 = strtotime($dt1);
        //     $tm2 = strtotime($dt2);
        //     return ($tm1 < $tm2) ? -1 : (($tm1 > $tm2) ? 1 : 0);
        // });
        $return_array['scan'] = $scan;
        $return_array['tracking_id'] = $tracking_id ?? '';
        $return_array['destination_from'] = ($scan[0]['location']) ?? '';
        $return_array['destination_to']   = $destination_to ?? '';
        $return_array['status']           = !empty($current_status) ? $current_status : "";
        $return_array['current_status']   = !empty($current_status) ? $current_status : "";
        $return_array['status_date'] = $status_date;
        $return_array['status_time'] = $status_time;
        $return_array['pickupdate']  = $pickupdate ?? '';

       // echo '<pre>';print_r($return_array);die;
        return $return_array;  
    }

    private function clean($value)
    {
        return preg_replace('!\s+!', ' ', trim(strip_tags($value)));
    }
    
}



$object = New Courier();
$data = $object->scrapping("1321633574");
//echo '<pre>';print_r($data);die;
include('view.php');
// print_r($data);



















