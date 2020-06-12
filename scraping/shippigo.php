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
            $date = $value['updatedOn'] ?? '';
            $location = $value['place'] ?? '';

            if(!empty($date)){
                $date = date('d/m/Y',strtotime($date));
                $return_array['details'][$date][$i]['Event']= $location ?? '';
                $return_array['details'][$date][$i]['Location']= $value['details'] ?? '';
                $return_array['details'][$date][$i]['Date'] = date('D , h:i',strtotime($date));
                $scan[$i]['date']     = date('Y-m-d', strtotime(str_replace('/', '-',$date))) ?? '';
                $scan[$i]['time']     = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $date))) ?? '';
                $scan[$i]['location'] = $location ?? '';
                $scan[$i]['details']  = $value['details'] ?? '';
                $pickupdate = $scan[$i]['time'];
                $destination_to = $scan[$i]['location'];
                $i++;
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
        }

        return $return_array;  
    }

    private function clean($value)
    {
        return preg_replace('!\s+!', ' ', trim(strip_tags($value)));
    }
    
}



$object = New Courier();
$data = $object->scrapping("2844510164533");
//echo '<pre>';print_r($data);die;
include('view.php');
// print_r($data);

