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


        $url       = "https://api.gogoxpress.com/v1/track/{awb_no}";

        $track = explode('-',$waybill_number);
        if(!empty($track) && count($track) < 3){
            $trackstring = str_split($waybill_number,4);
            $$waybill_number = implode('-',$trackstring);
        }
        $url = str_replace('{awb_no}', $waybill_number, $url);
       
       
        // $curl = new Curl;
        $data = $this->getCurl($url);
        $data = json_decode($data,1);
        if(empty($data) || count($data) == 0){
            $error['error'] = 'No information found.Please try again.';
            return $error;
        }
        
        if(!empty($data['status']) && $data['status'] == 400){
            $error['error'] = 'No information found.Please try again.';
            return $error;
        }

        $cdata = $data['data']['attributes'];
        $final_data['Tracking ID'] = $cdata['id'] ?? '';
        $final_data['Pickup Date'] = date('d/m/Y',strtotime($cdata['created_at'])) ?? '';
        $final_data['Final Status'] = $cdata['status'] ?? '';
        krsort($cdata['events']);
        $i = 0; 
        foreach($cdata['events'] as $key => $val){ 
            $date = date('d/m/Y',strtotime($val['created_at']));

            if(!empty($date)){
                $return_array['details'][$date][$i]['Event']= $val['remarks'] ?? '';
                $return_array['details'][$date][$i]['Location']= '';
                $return_array['details'][$date][$i]['Date'] =  date('D , h:i',strtotime($val['created_at']));
                
                $scan[$i]['date']     = !empty($date) ? date('Y-m-d', strtotime(str_replace('/', '-',$date))) : '';
                $scan[$i]['time']     = !empty($date) ? date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $date))) : '';
                $scan[$i]['location'] = '';
                $scan[$i]['details']  = $val['remarks'] ?? '';
                $pickupdate = $scan[$i]['time'];
                if(!empty($scan[$i]['details'])){
                    $arr = explode('Consignee',$scan[$i]['details']);
                    $recipient = $arr[1] ?? '';
                }
                $i++;
            }
            
        }
       
        $current_status = $scan[0]['details'] ?? '';
        $status_date    = $scan[0]['date'] ?? '';
        $status_time    = $scan[0]['time'] ?? '';
       
        $return_array['scan'] = $scan;
        $return_array['destination_from'] =  '';
        $return_array['destination_to']   = $destination_to ?? '';
        $return_array['status']           = !empty($current_status) ? $current_status : "";
        $return_array['current_status']   = !empty($current_status) ? $current_status : "";
        $return_array['status_date'] = $status_date;
        $return_array['status_time'] = $status_time;
        $return_array['pickupdate']  = $pickupdate ?? '';
        $return_array['recipient']  = $recipient ?? '';
       
        return $return_array;  
    }

    private function clean($value)
    {
        return preg_replace('!\s+!', ' ', trim(strip_tags($value)));
    }
    
}



$object = New Courier();
$data = $object->scrapping("4288-3661-VJUC");
//echo '<pre>';print_r($data);die;
include('view.php');
// print_r($data);

