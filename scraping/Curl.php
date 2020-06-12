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
        CURLOPT_HTTPHEADER => array(
          "cache-control: no-cache",
          "postman-token: 366befd3-c02f-8ad0-3756-f8e0264d9114"
        ),
      ));

      $response = curl_exec($curl);
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
        "Connection: keep-alive",
        "Cache-Control: max-age=0",
        "Origin: http://www.dawnwing.co.za",
        "Upgrade-Insecure-Requests: 1",
        "Content-Type: application/x-www-form-urlencoded",
        "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.132 Safari/537.36",
        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
        "Referer: http://www.dawnwing.co.za/business-tools/online-parcel-tracking/",
        "Accept-Language: en-GB,en-US;q=0.9,en;q=0.8",
        "Cookie: PHPSESSID=c9a3f3vfmv65vrhida5vttd1g0; ga=GA1.3.286168519.1591701510; gid=GA1.3.236381466.1591701510; wcsid=IMl4vG5MJszMZW5R9I5pD0JdD6bP2rao; hblid=Uefclsqz5ujQ9H5a9I5pD0JBd0oPO6rD; fbp=fb.2.1591701510659.1674385913; okdetect=%7B%22token%22%3A%2215917015110590%22%2C%22proto%22%3A%22http%3A%22%2C%22host%22%3A%22www.dawnwing.co.za%22%7D; olfsk=olfsk3812280214432364; okbk=cd4%3Dtrue%2Cvi5%3D0%2Cvi4%3D1591701511887%2Cvi3%3Dactive%2Cvi2%3Dfalse%2Cvi1%3Dfalse%2Ccd8%3Dchat%2Ccd6%3D0%2Ccd5%3Daway%2Ccd3%3Dfalse%2Ccd2%3D0%2Ccd1%3D0%2C; ok=4796-135-10-5809; gat=1; oklv=1591703506287%2CIMl4vG5MJszMZW5R9I5pD0JdD6bP2rao"
      ),
    ));

      $response = curl_exec($curl);
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