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


        $url       = "https://www.scgexpress.co.th/tracking/detail/{awb_no}";

        $waybill_number = base64_encode($waybill_number);
        $url = str_replace('{awb_no}', $waybill_number, $url);
       
       
        // $curl = new Curl;
        $response = $this->getCurl($url);
        if (empty($response)) {
            $error['error'] = 'No information found.Please try again.';
            return $error;
        }
        
        $html = str_get_html($response);
        if (empty($html)) {
            $error['error'] = 'No information found.Please try again.';
            return $error;
        }

        $span1 = $html->find('div[class="track-detail-order"] span',0)->innertext;
        if(!empty($span1)){
            $text =  html_entity_decode($span1);
            $text = preg_replace('/<br[^>]*>/i',',', $text);
            $arr = explode(',',$text);
            foreach($arr as $value){
                $val = trim($value);
                $a = explode(':',$val);
                if(!empty($a[0])){
                    $val = trim($a[0]);
                    if($val == 'Tracking No.'){
                        $return_array['tracking_id'] = trim($a[1]);
                    }
                    if($val == 'Order'){
                        $return_array['order_id'] = trim($a[1]);
                    }   
                }  
            }
        }
        
    
        
        $div2 = $html->find('div[class="cd-timeline-block"]');
        if(!empty($div2)){
            $i = 0;
            foreach ($div2 as $key => $value) {
                $sub_div = $value->find('div[class="cd-timeline-content"]',0);
                if(!empty($sub_div)){
                    //span 2
                    $date = $sub_div->find('span[class="cd-date"]',0)->innertext;
                    $datefilter = str_replace('เวลา',' ',$date);
                    $date = date('d/m/Y',strtotime($datefilter));
                    $return_array['details'][$date][$i]['Date'] = date('D , h:i',strtotime($date));
                    
            
                    // span 1
                    $detail = $sub_div->find('span[class="tracking-position"] h2 span',0)->innertext;
                    $detail = trim(str_replace(array( '(', ')' ), '',$detail));
                    $arr = explode(':',$detail);
                    $return_array['details'][$date][$i]['Event']= $arr[0];
                    $return_array['details'][$date][$i]['Location']= $arr[1];
                    
                    $scan[$i]['date']     = !empty($datefilter) ? date('Y-m-d', strtotime(str_replace('/', '-',$datefilter))) : '';
                    $scan[$i]['time']     = !empty($datefilter) ? date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $datefilter))) : '';
                    $scan[$i]['location'] = $arr[1] ?? '';
                    $scan[$i]['details']  = $arr[0] ?? '';
                    $pickupdate = $scan[$i]['time'];
                    $destination_to = $scan[$i]['location'];
                    $i++;
                }
            }
        }
       
        $current_status = $scan[0]['details'] ?? '';
        $status_date    = $scan[0]['date'] ?? '';
        $status_time    = $scan[0]['time'] ?? '';
        $return_array['scan'] = $scan;
        $return_array['destination_from'] = ($scan[0]['location']) ?? '';
        $return_array['destination_to']   = $destination_to ?? '';
        $return_array['status']           = !empty($current_status) ? $current_status : "";
        $return_array['current_status']   = !empty($current_status) ? $current_status : "";
        $return_array['status_date'] = $status_date;
        $return_array['status_time'] = $status_time;
        $return_array['pickupdate']  = $pickupdate ?? '';

        return $return_array;  
    }

    private function clean($value)
    {
        return preg_replace('!\s+!', ' ', trim(strip_tags($value)));
    }
    
}



$object = New Courier();
$data = $object->scrapping("121394342823");
//echo '<pre>';print_r($data);die;
include('view.php');
// print_r($data);

