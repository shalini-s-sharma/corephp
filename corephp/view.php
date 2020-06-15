
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scraping</title>
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
</head>
<body>
<?php if(!empty($error) && count($error) > 0){
    echo $error['error'];
}  ?>
<?php if(!empty($data) && count($data) > 0){ ?>
<table class="shp_table"> 
<tbody><tr>
<td colspan="4" style="background-color: #2FCD97;text-align: center;color:#fff;font-size: 16px;border-color: #2FCD97;"><?= $data['status']??'';  ?></td>  
</tr>
<?php if(!empty($data['tracking_id'])){ ?>
<tr>
<td colspan="1" style="font-weight:bold;">
<b>Tracking ID</b></td><td colspan="2"><?= $data['tracking_id'] ?? ''; ?></td>
</tr>
<?php } ?>
<?php if(!empty($data['pickupdate'])){ ?>
<tr>
<td colspan="1" style="font-weight:bold;">Pickup Date</td><td colspan="2"><?= date('d/m/Y h:i',strtotime($data['pickupdate'])) ?? ''; ?></td>
</tr>
<?php } ?>
<?php if(!empty($data['destination_from'])){ ?>
<tr>
<td colspan="1" style="font-weight:bold;">Origin</td><td colspan="2"><?= $data['destination_from'] ?? ''; ?></td>
</tr>
<?php } ?>
<?php if(!empty($data['destination_to'])){ ?>
<tr>
<td colspan="1" style="font-weight:bold;">Destination</td><td colspan="2"><?= $data['destination_to'] ?? ''; ?></td>
</tr>
<?php } ?>
<?php if(!empty($data['status_time'])){ ?>
<tr>
<td colspan="1" style="font-weight:bold;">Status Time</td><td colspan="2"><?= $data['status_time'] ?? ''; ?></td>
</tr>
<?php } ?>
<?php if(!empty($data['recepient'])){ ?>
<tr>
<td colspan="1" style="font-weight:bold;">Recepient</td><td colspan="2"><?= $data['recepient']?? ''; ?></td>
</tr>
<?php } ?>
<?php foreach($data['details'] as $date => $value){ ?>
    <tr class="onj-tr"><td colspan="4"><?= $date ?? ''; ?></td></tr>
    <?php foreach($value as $val){ ?>
    <tr>
    <td><?= $val['Location'] ?? 'N/A';  ?></td>
    <td><?= $val['Event'] ?? '';  ?></td>
    <td><?= $val['Date'] ?? ''; ?></td>
    </tr>
    <?php } ?>
<?php } ?>
</tbody>
</table>
<?php } ?> 
</body>
</html>