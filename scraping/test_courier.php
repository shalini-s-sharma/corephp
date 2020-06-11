<head>
  <meta charset="UTF-8">
</head>
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
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
        
        //include('simple_html_dom.php');
        $waybill_number = '104835';
        include('simple_html_dom.php');
        $url       = "http://www.khubaniairpack.com/KAPTrackDocPage.aspx?DOCREFNO={awb_no}";
        $url = str_replace('{awb_no}', $waybill_number, $url);
        
        
        $response = $this->getScan($url);
        
        
        if (empty($response)) {
            $error['error'] = 'No information found.Please try again.';
            return $error;
        }
        
        $html = str_get_html($response);
        if (empty($html)) {
            $error['error'] = 'No information found.Please try again.';
            return $error;
        }
        
        //echo $html;die();
        
        $tracking_div = $html->find("table[id='ContentPlaceHolder1_DataList1']", 0);
        if (empty($tracking_div)) {
            $error['error'] = 'No information found.Please try again.';
            return $error;
        }
        
        $tracing_table = $tracking_div->find("table[style='width: 567px; border-right: 1px solid; border-top: 1px solid; border-left: 1px solid; border-bottom: 1px solid;']");
       
        
        if (empty($tracing_table)) {
            
            $error['error'] = 'No information found.Please try again.';
            return $error;
        }
        
        $counter = 0;
        foreach ($tracing_table as $scan_row) {
            
            $dt = ($scan_row->find("span[id='ContentPlaceHolder1_DataList1_DOCDATE_".$counter."']", 0))?$this->clean($scan_row->find("span[id='ContentPlaceHolder1_DataList1_DOCDATE_".$counter."']", 0)->innertext):'';
            $dt = explode(' ', $dt);

           
            
            
            $scan[$counter]['date']     = !empty($dt[0]) ? date('Y-m-d', strtotime(str_replace('/', '-', $dt[0]))) : '';
            $scan[$counter]['time']     = !empty($dt[0]) ? date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $dt[0]))) : '';
            $scan[$counter]['location'] = ( $scan_row->find("span[id='ContentPlaceHolder1_DataList1_DOCRCUNAME_".$counter."']", 0)) ? $this->clean($scan_row->find("span[id='ContentPlaceHolder1_DataList1_DOCRCUNAME_".$counter."']", 0)->innertext):'';

            $scan[$counter]['details']  = ($scan_row->find("span[id='ContentPlaceHolder1_DataList1_DOCPODSTAT_".$counter."']", 0))?$this->clean($scan_row->find("span[id='ContentPlaceHolder1_DataList1_DOCPODSTAT_".$counter."']", 0)->innertext):'';
            
            
            
            $pickupdate = $scan[$counter]['time'];
            
            $counter++;
        }
        
       
        //echo '<pre>';print_r($scan);die();
        
      
        $current_status = ($scan[0]['details']) ? $scan[0]['details'] : '';
        $status_date    = $scan[0]['date'];
        $status_time    = $scan[0]['time'];
        $return_array['scan'] = $scan;
        $return_array['destination_from'] = $destination_from;
        $return_array['destination_to']   = $destination_to;
        $return_array['status']           = !empty($current_status) ? $current_status : "";
        $return_array['current_status']   = !empty($current_status) ? $current_status : "";
        
        $return_array['status_date'] = $status_date;
        $return_array['status_time'] = $status_time;
        $return_array['pickupdate']  = $pickupdate;

        //$return_array['onj_status'] = $this->statusMapping($current_status);
       echo '<pre>';print_r($return_array);die();
        return $return_array;
        
        
    }
    
    
    function getScan($url)
    {
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
    
   
    private function clean($value)
    {
        return preg_replace('!\s+!', ' ', trim(strip_tags($value)));
    }
    
}



$object = New Courier();
$object->scrapping("PR080673034YP");