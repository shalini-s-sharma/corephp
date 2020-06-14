
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
include('simplehtmldom/simple_html_dom.php');

class Courier
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
            if(count($arr) > 0){
              foreach($arr as $value){
                $val = trim($value);
                $a = explode(':',$val);
                if(!empty($a[0])){
                    $val = trim($a[0]);
                    if($val == 'Tracking No.'){
                        $return_array['tracking_id'] = trim($a[1]);
                    }   
                }  
            }
          }
        }

        if(!empty($return_array['tracking_id']) &&  trim($return_array['tracking_id']) == '-'){
            $error['error'] = 'No information found.Please try again.';
            return $error;
        }
        
    
        
        $div2 = $html->find('div[class="cd-timeline-block"]');
        if(!empty($div2)){
            $i = 0;
            foreach ($div2 as $key => $value) {
                $sub_div = $value->find('div[class="cd-timeline-content"]',0);
                if(!empty($sub_div)){
                    //span 2
                    $date = $sub_div->find('span[class="cd-date"]',0)->innertext ?? '';
                    $datefilter = str_replace('เวลา',' ',$date) ?? '';
    
                    // span 1
                    $detail = $sub_div->find('span[class="tracking-position"] h2 span',0)->innertext;
                    $detail = trim(str_replace(array( '(', ')' ), '',$detail));
                    $arr = explode(':',$detail);
                    
                    $scan[$i]['date']     = !empty($datefilter) ? date('Y-m-d', strtotime(str_replace('/', '-',$datefilter))) : '';
                    $scan[$i]['time']     = !empty($datefilter) ? date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $datefilter))) : '';
                    $scan[$i]['location'] = $arr[1] ?? '';
                    $scan[$i]['details']  = $arr[0] ?? '';
                    $pickupdate = $scan[$i]['time'];
                    $destination_from = $scan[$i]['location'];
                    $i++;
                }
            }
        }
        $html->clear();
        unset($html);
        $current_status = $scan[0]['details'] ?? '';
        $status_date    = $scan[0]['date'] ?? '';
        $status_time    = $scan[0]['time'] ?? '';
        $destination_to = $scan[0]['location'] ?? '';
        $return_array['scan'] = $scan;
        $return_array['destination_from'] = $destination_from ?? '';
        $return_array['destination_to']   = $destination_to ?? '';
        $return_array['status']           = !empty($current_status) ? $current_status : "";
        $return_array['current_status']   = !empty($current_status) ? $current_status : "";
        $return_array['status_date'] = $status_date;
        $return_array['status_time'] = $status_time;
        $return_array['pickupdate']  = $pickupdate ?? '';
        return $return_array;  
    }

    function getCurl($url){

      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HEADER => 1,
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


$track = $_GET['track_id'];
//"121394342823"
$object = New Courier();
$data = $object->scrapping($track);
echo '<pre>';print_r($data);die;
include('view.php');
// print_r($data);

