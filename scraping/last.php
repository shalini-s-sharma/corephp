
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


        $url       = "http://47.104.161.73:8043/track_query.aspx?track_number={awb_no}";

        $url = str_replace('{awb_no}', $waybill_number, $url);
       
       
        // $curl = new Curl;
        $response = $this->getCurl($url);
      //  echo $response;die;
        if (empty($response)) {
            $error['error'] = 'No information found.Please try again.';
            return $error;
        }
        
        $html = str_get_html($response);
        if (empty($html)) {
            $error['error'] = 'No information found.Please try again.';
            return $error;
        }    
        
        $li1 = $html->find('div[class="vote-info"] ul li',0);
        $destination= $li1->find('span span[class="msgcss"]',1)->innertext ?? '';
       // echo $destination;die;
        $li2 = $html->find('div[class="vote-info"] ul li',1);
        $current_status = $li2->find('span[class="msgcss"]',0)->innertext ?? '';

        $data =  $html->find('span[class="vertical-date"]');
        if(!empty($data)){
          foreach($data as $value){
            $ul = $value->find('ul li',0)->innertext ?? '';
            $ul = htmlentities($ul);
            echo $ul;
        
            $tag = 'i';
            preg_replace('/<(.+?)[\s]*\/?[\s]*>/si',',', $ul);
            echo $ul;die;
            if(!empty($ul)){
              $arr = explode('</i>',$ul);
              print_r($arr);die;
            }
            
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
        }
        
        if(!empty($cookies)){
            $cookies .= ';i18next_lng=en';
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
$data = $object->scrapping("TDQAB0003536942YQ");
echo '<pre>';print_r($data);die;
include('view.php');
// print_r($data);

