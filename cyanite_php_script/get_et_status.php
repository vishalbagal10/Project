<?php

$dbcon = include('config/config.php');
$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);


$token_json = file_get_contents('php://input');
//echo $token_json;

// Decode JSON data to PHP associative array
$token_json_items = json_decode("[".$token_json."]", true);
//print_r($token_json_items);

$token_json_items_count = count($token_json_items);
//echo 'count:-'.$games_transaction_obj_items_count;

foreach ($token_json_items as $data) 
{

	$et = $data["et"]; 
	$cdate = $data["cdate"];  
	$type = $data["atype"];
	//echo $et."-----".$cdate."-----".$type;
}
$get_future_date_query = "SELECT * FROM `tbl_config` WHERE `type`='future_date' and `is_active` = 0";
$result = $conn->query($get_future_date_query);
	
if($result->num_rows > 0)
{	
	$row = $result->fetch_array();	
	//echo $row["value"];
	$f_date = str_replace(":","",str_replace(" ","",str_replace("-","",$row["value"])));
	$encp1 = str_replace(":","",str_replace(" ","",str_replace("-","",$cdate)));
	$date_diff = $f_date-$encp1;
	$date_diff_insec = $date_diff*60;
	if($type == "python")
		$enc_token = md5($date_diff_insec.'pyMp3D0wnL0der');
	else
		$enc_token = md5($date_diff_insec.'cyMp3UpL0der');
}
else{
	$enc_token = "0";
}
//echo $enc_token;
if($et == $enc_token)
	echo 1;
else
	echo 0;
?>