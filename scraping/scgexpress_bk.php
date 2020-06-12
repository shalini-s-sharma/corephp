
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require 'simplehtmldom/simple_html_dom.php';
require 'Curl.php';

$url = "https://www.scgexpress.co.th/tracking/detail/{awb_no}";
$tracking_id = $_GET['tracking_id'] ?? '';
if(empty($tracking_id)){
    $error = 'ADD tracking id In the url with ?tracking_id=';
    echo $error;
    die;
}

$tracking_id = base64_encode($tracking_id);
$url = str_replace('{awb_no}', $tracking_id, $url);
$curl = new Curl;
$response = $curl->getCurl($url);
if (empty($response)) {
    $error= 'No information found.Please try again.';
    return $error;
}

$html = str_get_html($response);
if(empty($html)){
    $error = 'No information found.Please try again.';
    echo $error;
    die; 
}

$return_array = [];
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
                $return_arrayfinal_data['Tracking ID'] = trim($a[1]);
            }
            if($val == 'Order'){
                $return_array['Order ID'] = trim($a[1]);
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
            $final_data['details'][$date][$i]['Date'] = date('D , h:i',strtotime($date));
            
    
            // span 1
            $detail = $sub_div->find('span[class="tracking-position"] h2 span',0)->innertext;
            $detail = trim(str_replace(array( '(', ')' ), '',$detail));
            $arr = explode(':',$detail);
            $final_data['details'][$date][$i]['Event']= $arr[0];
            $final_data['details'][$date][$i]['Location']= $arr[1];
               
            $final_data[$i]['date']     = !empty($datefilter) ? date('Y-m-d', strtotime(str_replace('/', '-',$datefilter))) : '';
            $final_data[$i]['time']     = !empty($datefilter) ? date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $datefilter))) : '';
            $final_data[$i]['location'] = $arr[1] ?? '';
            $final_data[$i]['details']  = $arr[0] ?? '';
            $pickupdate = $final_data[$i]['time'];
            $destination_to = $final_data[$i]['location'];
            $i++;
        }
    }
}

$current_status = $final_data[0]['details'] ?? '';
$status_date    = $final_data[0]['date'] ?? '';
$status_time    = $final_data[0]['time'] ?? '';
$return_array['scan'] = $final_data;
$return_array['destination_from'] = ($final_data[0]['location']) ?? '';
$return_array['destination_to']   = $destination_to ?? '';
$return_array['status']           = !empty($current_status) ? $current_status : "";
$return_array['current_status']   = !empty($current_status) ? $current_status : "";
$return_array['status_date'] = $status_date;
$return_array['status_time'] = $status_time;
$return_array['pickupdate']  = $pickupdate ?? '';

echo '<pre>';print_r($final_data);die;
$html->clear();
unset($html);
 
?>
