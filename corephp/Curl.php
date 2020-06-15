<?php 

class Curl{

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

  function dd($data){
    if(!is_array($data)){
      echo '<pre>';print_r($data);die;
    }else{
      echo '<pre>';echo $data;die;
    }
  }


}



?>