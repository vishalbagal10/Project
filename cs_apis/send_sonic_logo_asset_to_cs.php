<?php 
$file = "logs/sonic_logo/solic_logo_log_".date('Y-m-d').".log";
if(!is_file($file)){
$contents = '';
file_put_contents("logs/sonic_logo/solic_logo_log_".date('Y-m-d').".log", $contents);
}
$error_message = "This is an error message!";
$log_file = $file;
ini_set("log_errors", TRUE); 
ini_set('error_log', $log_file);

include("connection.php");
include("functions.php");

if($conn){
 echo "connection ok";
}else{
    echo "connection not found";
}

error_log("\n\n date === ".date('Y-m-d H:i:s'));
echo "<br>". $sql_query = "SELECT tbl_cv_block_6_data.*,tbl_asset_types.asset_upload_at as 'asset_upload_at' FROM `tbl_cv_block_6_data` 
              LEFT JOIN tbl_cvs on tbl_cvs.cv_id=tbl_cv_block_6_data.cv_id
              LEFT JOIN tbl_asset_types on tbl_asset_types.asset_type_id=tbl_cv_block_6_data.asset_type_id  
              WHERE tbl_cv_block_6_data.is_active=0 
              and b6_name is not null 
              and tbl_cvs.is_active=0 
              and tbl_cvs.status=1
              and tbl_cv_block_6_data.cs_status=0 limit 2";

$run_sql_query = mysqli_query($conn,$sql_query);
$num_rows = mysqli_num_rows($run_sql_query);

if($num_rows > 0){
    $transaction_token = get_access_token($conn);
    
    if($transaction_token != ""){
        
        while($row = mysqli_fetch_assoc($run_sql_query)){
            $asset_name = $row['b6_name'];
            $b6_id = $row['b6_id'];
            $asset_upload_at = $row['asset_upload_at'];
            $asset_type_id = $row['asset_type_id'];
            echo "<br><br><br>*******************************************************************<br><br><br>";
            echo "<br>"."asset name == ".$asset_name.", b6_id == ".$b6_id.", asset_upload_at == ".$asset_upload_at.", asset_type_id == ".$asset_type_id;
            $data_status=0;
            $track_id=0;
            send_asset_content_to_central_system("sonic_logo",$conn,$asset_name,$b6_id,$transaction_token,$asset_type_id,$asset_upload_at,$data_status,$track_id);
        }
    }else{
        echo "<br> Transation Token  Failed \n\n";
        error_log("=========Transation Token  Failed =====");
    }
}else{
    echo "<br> Data Not Found";
    error_log("Data Not Found");
}

?>
