<?php

// log started
$file = "logs/get_asset_data_callback_log_".date('Y-m-d').".log";
if(!is_file($file)){
    //Some simple example content.
    $contents = date('Y-m-d h:i:s');
    //Save our content to the file.
    file_put_contents("logs/get_asset_data_callback_log_".date('Y-m-d').".log", $contents);
}

  
// path of the log file where errors need to be logged
$log_file = $file;
  
// setting error logging to be active
ini_set("log_errors", TRUE); 
  
// setting the logging file in php.ini
ini_set('error_log', $log_file);

// log end
error_log("Started at");
error_log(date('Y/m/d h:i:s', time()));
error_log("==========================================================================================================================================");
include('functions.php');

// $dbcon = include('config.php');
// $conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);
include("connection.php");

$response = file_get_contents('php://input');
  
// error_log("/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/");
// error_log($response);
// // error_log($_SERVER['HTTP_REFERER']);
// error_log("/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/");

//echo $response;
// error_log($response);
$response_content = json_decode($response);
//print_r($response_content);
// error_log($response_content);

error_log("****************************************************************************************************************************");
$extract_asset_result_data_from_received_response_status = extract_asset_result_data_from_received_response($conn, $response_content);
if($extract_asset_result_data_from_received_response_status == 1)
{
    // echo "UPDATE `tbl_assets` SET `cs_response_status` = ".$status.", `cs_response_dt`= '".$response_content->data->result->response_date_time."' WHERE `asset_cs_id` ='".$asset_cs_id."'";
    if($conn->query("UPDATE `tbl_assets` SET `cs_response_status` = ".$response_content->data->result->status.", `cs_response_status_datetime`= '".$response_content->data->result->response_date_time."' WHERE `cs_asset_id` ='".$asset_cs_id."'"))
    {
        error_log("Response Status updated successfully in tbl_assets table for asset id".$asset_cs_id);
        echo "Response Status updated successfully in tbl_assets table for asset id".$asset_cs_id;
    }
    else
    {
        error_log("Error occured while updating Response status in tbl_assets table for asset id".$asset_cs_id);
    }
}
error_log("****************************************************************************************************************************");

error_log("Ended at");
error_log(date('Y/m/d h:i:s', time()));
error_log("==========================================================================================================================================");