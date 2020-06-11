
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require 'simplehtmldom/simple_html_dom.php';
require 'Curl.php';
$url = "https://app.shippigo.com/api/trackFront";
$tracking_id = $_GET['tracking_id'];
$param = "awb=$tracking_id";
$curl = new Curl;
$response = $curl->postCurl($url,$param);

$data = json_decode($response,1);

$final_data = [];
$i = 0;
foreach($data['data'] as $key => $value){
    $date = $value['updatedOn'];
    $location = $value['place'];
    $date = date('d/m/Y',strtotime($date));
    $final_data['details'][$date][$i]['Event']= $location;
    $final_data['details'][$date][$i]['Location']= $value['details'];
    $final_data['details'][$date][$i]['Date'] = $value['updatedOn'];
    $last_element = count($data['data'])-1;
    if($key == 0){
      $origin =  $location;
      $final_status = $value['status'];
    }
    if($last_element == $key){
        $pickup_date = $date;
        $destination =  $location;
        $status_time = $date; 
    
    } 
    $i++;  
}
$final_data['Tracking ID'] = $tracking_id;
$final_data['Pickup Date'] = $pickup_date ?? '';   
$final_data['Origin'] = $origin ?? ''; 
$final_data['Destination'] = $destination ?? '';  
$final_data['Status Time'] = $status_time ?? '';  
$final_data['Final Status'] = $final_status ?? ''; 
//echo '<pre>';print_r($final_data);die;
?>
