
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


        $url       = "https://home.abxexpress.com.my/track_single.asp?search=True&itemHAWB={awb_no}";

        $url = str_replace('{awb_no}', $waybill_number, $url);
       
       
        // $curl = new Curl;
        $response = $this->getCurl($url);
        //echo $response;die;
        if (empty($response)) {
            $error['error'] = 'No information found.Please try again.';
            return $error;
        }
        
        $html = str_get_html($response);
        if (empty($html)) {
            $error['error'] = 'No information found.Please try again.';
            return $error;
        }    
        
        $li = $html->find('ul[id="first-list"] li');
       
        if(!empty($li)){
            $i = 0;
            foreach ($li as $key => $value) {
                $location = $value->find('div[class="title"]',0)->innertext ?? '';
                $details = $value->find('div[class="info"]',0)->innertext ?? '';
                 
                #invlaid tracking id
                if(!empty($location) && $Location == 'SORRY' && !empty($details)){
                  $error['error'] = $details;
                  return $error;
                }
                $date = $value->find('div[class="time"] span',0)->innertext ?? '';
                $time = $value->find('div[class="time"] span',1)->innertext ?? '';
                
                $scan[$i]['date']     = !empty($date) ? date('Y-m-d', strtotime(str_replace('/', '-',$date))) : '';
                $scan[$i]['time']     = !empty($date) ? date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $date))) : '';
                $scan[$i]['location'] = $location ?? '';
                $scan[$i]['details']  = $details ?? '';
                $pickupdate = $scan[$i]['time'];
                $destination_to = $scan[$i]['location'];
                $i++;
            }
            
        }
        $html->clear();
        unset($html);
        
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
            // parse_str($item, $cookie);
            // $cookies = array_merge($cookies, $cookie);
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
$data = $object->scrapping("83361027361");
echo '<pre>';print_r($data);die;
include('view.php');
// print_r($data);

