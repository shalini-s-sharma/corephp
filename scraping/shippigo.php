
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
                $scan[$i]['date']     = date('Y-m-d', strtotime(str_replace('/', '-',$date))) ?? '';
                $scan[$i]['time']     = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $date))) ?? '';
                $scan[$i]['location'] = $location ?? '';
                $scan[$i]['details']  = $value['details'] ?? '';
                $pickupdate = $scan[$i]['time'] ?? '';
                $destination_from = $scan[$i]['location'] ?? '';
                $i++;
            }  
        }
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
        }

        return $return_array;  
    }


    function postCurl($url,$params){

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $params,
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
$data = $object->scrapping("2844510164533");
 echo '<pre>';print_r($data);die;
// include('view.php');
// print_r($data);

