<style>
.shp_table {
    width: ;
    border: thin solid #428BCA;
    padding: 0px;
    border-collapse: collapse;
    margin: 0 auto;
}
.shp_table{width: ;border: thin solid #428BCA;padding: 0px;border-collapse: collapse; margin: 0 auto;}
.shp_table th{border: 1px solid #428BCA;padding: 10px;text-align: center;}
.shp_table td{ padding: 5px 15px;border: 1px solid #428BCA;font-family:roboto;font-size:13px;text-align: left;}
.onj-tr{background-color: #428BCA;color: #fff;}

#track_result table{margin:0 auto;}
</style>
<?php
require 'simplehtmldom/simple_html_dom.php';
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "http://www.dawnwing.co.za/business-tools/online-parcel-tracking/",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => "WaybillNo=1321633574&parcel-search=go",
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

curl_close($curl);
$html   = str_get_html($response);
$table = $html->find('table');
$table_desc = $table[1];

$teams = array();
$i = 0;
foreach ($table_desc->find('tr') as $row)
    {
        $stats = $row->find('td');
        if (isset($stats[0]) && !empty($stats[0]->plaintext))
        {
            $event = $stats[0]->plaintext;
            $location = $stats[1]->plaintext;
            $date = $stats[2]->plaintext;
            $time = $stats[3]->plaintext;

            $teams['details'][$date][$i]['Event']= $event;
            $teams['details'][$date][$i]['Location']= $location;
            $teams['details'][$date][$i]['Date'] = $date;
            $teams['details'][$date][$i]['Time']= $time;
        }
        $i++;
    }
$table_pod = $table[0]; 
foreach ($table_pod->find('tr') as $row)
{
    $stats = $row->find('td');
    $date_received = $stats[1]->plaintext;
    $time_received = $stats[2]->plaintext;
    $teams['Date_receviecd'] = $date_received;
    $teams['time_received'] = $time_received;
    
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
        $teams[trim($a[0])] = trim($a[1]);
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
        $teams[trim($a[0])] = trim($a[1]);
    }
   
}
//echo '<pre>';print_r($teams);die;

$html->clear();
unset($html);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<table class="shp_table">	
<tbody><tr>
<td colspan="4" style="background-color: #2FCD97;text-align: center;color:#fff;font-size: 16px;border-color: #2FCD97;">InTransit <!-- <a class="notify"><i class="fa fa-bell" onclick = "askquestion()" >    <small> Notify me</small></i></a> -->   <!--  --> </td>  
</tr>

<tr>
<td colspan="1" style="font-weight:bold;">
<b>Tracking ID</b></td><td colspan="2"><?= $teams['Waybill Number'] ?? ''; ?></td>
</tr>
<tr>
<td colspan="1" style="font-weight:bold;">Pickup Date</td><td colspan="2"><?= date('d/m/Y',strtotime($teams['Waybill Date'])); ?></td>
</tr>
    <tr>
<td colspan="1" style="font-weight:bold;">Origin</td><td colspan="2"><?= $teams['From'] ?? ''; ?></td>
</tr>
    <tr>
<td colspan="1" style="font-weight:bold;">Destination</td><td colspan="2"><?= $teams['To'] ?? ''; ?></td>
</tr>
    <tr>
<td colspan="1" style="font-weight:bold;">Status Time</td><td colspan="2"><?= date('d/m/Y',strtotime($teams['Date_receviecd'])); ?></td>
</tr>
<?php foreach($teams['details'] as $date => $value){ ?>
    <tr class="onj-tr"><td colspan="4"><?= date('d/m/Y',strtotime($date)) ?></td></tr>
   
    <?php foreach($value as $val){ ?>
    <tr>
    <td><?= $val['Location'] ?? '';  ?></td>
    <td><?= $val['Event'] ?? '';  ?></td>
            <td><?= date('D',strtotime($val['Date'])) ?>,<?= $val['Time'] ?></td>
    </tr>
    <?php } ?>
<?php } ?>
</tbody></table> 
</body>
</html>