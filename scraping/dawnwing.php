
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
include('simplehtmldom/simple_html_dom.php');

class Courier
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
                    $event = $stats[0]->plaintext ?? '';
                    $location = $stats[1]->plaintext ?? '';
                    $date = $stats[2]->plaintext ?? '';
                    $time = $stats[3]->plaintext ?? '';

                    if(!empty($date)){
                        $finaldate = $date.''.$time;
                        $scan[$i]['date']     = !empty($finaldate) ? date('Y-m-d', strtotime(str_replace('/', '-',$finaldate))) : '';
                        $scan[$i]['time']     = !empty($finaldate) ? date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $finaldate))) : '';
                        $scan[$i]['location'] = $location ?? '';
                        $scan[$i]['details']  = $event ?? '';
                        $pickupdate = $scan[$i]['time'] ?? '';
                        $destination_to = $scan[$i]['location'] ?? '';
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

       // echo '<pre>';print_r($return_array);die;
        return $return_array;  
    }

    private function clean($value)
    {
        return preg_replace('!\s+!', ' ', trim(strip_tags($value)));
    }

    function postCurl($url,$params){

        $curl = curl_init();
        if(is_array($params)){
          $parameters = implode('&',$params);
        }else{
          $parameters = $params;
        }


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
    
}



$object = New Courier();
$data = $object->scrapping("1321633574");
// echo '<pre>';print_r($data);die;
// include('view.php');
// print_r($data);



















