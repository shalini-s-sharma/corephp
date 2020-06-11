<?php 
require 'gogoexpress.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
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

<?php if(!empty($final_data) && count($final_data) > 0){ ?>
<table class="shp_table"> 
<tbody><tr>
<td colspan="4" style="background-color: #2FCD97;text-align: center;color:#fff;font-size: 16px;border-color: #2FCD97;"><?= $final_data['Final Status']??'';  ?><!-- <a class="notify"><i class="fa fa-bell" onclick = "askquestion()" >    <small> Notify me</small></i></a> -->   <!--  --> </td>  
</tr>
<?php if(!empty($final_data['Tracking ID'])){ ?>
<tr>
<td colspan="1" style="font-weight:bold;">
<b>Tracking ID</b></td><td colspan="2"><?= $final_data['Tracking ID'] ?? ''; ?></td>
</tr>
<?php } ?>
<?php if(!empty($final_data['Pickup Date'])){ ?>
<tr>
<td colspan="1" style="font-weight:bold;">Pickup Date</td><td colspan="2"><?= $final_data['Pickup Date'] ?? ''; ?></td>
</tr>
<?php } ?>
<?php if(!empty($final_data['Origin'])){ ?>
<tr>
<td colspan="1" style="font-weight:bold;">Origin</td><td colspan="2"><?= $final_data['Origin'] ?? ''; ?></td>
</tr>
<?php } ?>
<?php if(!empty($final_data['Destination'])){ ?>
<tr>
<td colspan="1" style="font-weight:bold;">Destination</td><td colspan="2"><?= $final_data['Destination'] ?? ''; ?></td>
</tr>
<?php } ?>
<?php if(!empty($final_data['Status Time'])){ ?>
<tr>
<td colspan="1" style="font-weight:bold;">Status Time</td><td colspan="2"><?= $final_data['Status Time'] ?? ''; ?></td>
</tr>
<?php } ?>
<?php if(!empty($final_data['Recepient'])){ ?>
<tr>
<td colspan="1" style="font-weight:bold;">Recepient</td><td colspan="2"><?= $final_data['Recepient']?? ''; ?></td>
</tr>
<?php } ?>
<?php foreach($final_data['details'] as $date => $value){ ?>
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