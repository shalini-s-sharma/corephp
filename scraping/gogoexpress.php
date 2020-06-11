
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require 'simplehtmldom/simple_html_dom.php';
require 'Curl.php';

$url = "https://api.gogoxpress.com/v1/track/{awb_no}";
$tracking_id = $_GET['tracking_id'] ?? '';
if(empty($tracking_id)){
    $error = 'ADD tracking id In the url with ?tracking_id=';
    echo $error;
    die;
}

$track = explode('-',$tracking_id);
if(!empty($track) && count($track) < 3){
    $trackstring = str_split($tracking_id,4);
    $tracking_id = implode('-',$trackstring);
}


$url = str_replace('{awb_no}', $tracking_id, $url);
$curl = new Curl;
$response = $curl->getCurl($url);
$data = json_decode($response,1);
if(empty($data) || count($data) == 0){
    $error = 'Scraping error';
    echo $error;
    die; 
}
if(!empty($data['status']) && $data['status'] == 400){
    $error = $data['error']['detail'] ?? 'Scraping error';
    echo $error;
    die;
}
$final_data = [];
$i = 0;
$cdata = $data['data']['attributes'];
$final_data['Tracking ID'] = $cdata['id'] ?? '';
$final_data['Pickup Date'] = date('d/m/Y',strtotime($cdata['created_at'])) ?? '';
$final_data['Final Status'] = $cdata['status'] ?? '';
krsort($cdata['events']); 
foreach($cdata['events'] as $key => $val){ 
    $date = date('d/m/Y',strtotime($val['created_at']));
    $final_data['details'][$date][$key]['Event']= $val['remarks'];
    $final_data['details'][$date][$key]['Location']= '';
    $final_data['details'][$date][$key]['Date'] =  date('D , h:i',strtotime($val['created_at'])); 

    if((count($cdata['events'])-1) == $key){
        $arr = explode('Consignee',$val['remarks']);
        $final_data['Recepient'] = $arr[1] ?? ''; 
        $final_data['Status Time'] = date('d/m/Y',strtotime($val['created_at'])) ?? ''; 
    } 
}
 
?>
