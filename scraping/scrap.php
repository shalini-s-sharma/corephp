<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require 'simplehtmldom/simple_html_dom.php';
require 'Curl.php';
$url = "http://www.dawnwing.co.za/business-tools/online-parcel-tracking/";
$param = "WaybillNo=1321633552&parcel-search=go";
$curl = new Curl;
$response = $curl->postCurl($url,$param);
$html   = str_get_html($response);

$final_data = array();
$i = 0;
$table = $html->find('table');
$table_desc = $table[1];
foreach ($table_desc->find('tr') as $row){
        $stats = $row->find('td');
        if (isset($stats[0]) && !empty($stats[0]->plaintext))
        {
            $event = $stats[0]->plaintext;
            $location = $stats[1]->plaintext;
            $date = $stats[2]->plaintext;
            $time = $stats[3]->plaintext;

            $final_data['details'][$date][$i]['Event']= $event;
            $final_data['details'][$date][$i]['Location']= $location;
            $final_data['details'][$date][$i]['Date'] = $date.''.$time;
        }
        $i++;
    }
$table_pod = $table[0]; 
foreach ($table_pod->find('tr') as $row){
    $stats = $row->find('td');
    $date_received = $stats[1]->plaintext ?? '';
    $time_received = $stats[2]->plaintext ?? '';
    $final_data['Status Time'] = $date_received .''.$time_received;  
}

$div = $html->find('div[class="box"]');
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
            $final_data['Tracking ID'] = trim($a[1]);
        }
        if($val == 'Waybill Status'){
            $final_data['Final Status'] = trim($a[1]);
        } 
        if($val == 'From'){
            $final_data['Origin'] = trim($a[1]);
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
            $final_data['Pickup Date'] = trim($a[1]);
        }
        if($val == 'To'){
            $final_data['Destination'] = trim($a[1]);
        } 
        if($val == 'Service'){
            $final_data['Service'] = trim($a[1]);
        }
    }
}
//echo '<pre>';print_r($final_data);die;

$html->clear();
unset($html);
?>
