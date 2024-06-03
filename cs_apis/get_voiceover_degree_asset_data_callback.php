<?php

$file = "logs/get_voiceover_degree_asset_data_callback/get_voiceover_degree_asset_data_callback_log_".date('Y-m-d').".log";
if(!is_file($file)){
$contents = '';
file_put_contents("logs/get_voiceover_degree_asset_data_callback/get_voiceover_degree_asset_data_callback_log_".date('Y-m-d').".log", $contents);
}
$error_message = "This is an error message!";
$log_file = $file;
ini_set("log_errors", TRUE); 
ini_set('error_log', $log_file);

error_log("Started at");
error_log(date('Y/m/d h:i:s', time()));
error_log("==========================================================================================================================================");
include "functions.php";
$dbcon = include('connection.php');
$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);

$post_data_expected = file_get_contents("php://input");
// $response = json_decode($post_data_expected);
extract_voiceover_degree_asset_splitter_result($conn,$post_data_expected);

/*if($response != ''){
    $msg = $response->msg;
    if($msg == 0 || $msg == '0'){
            $cs_splitter_id = $response->data->splitter_id;
            $status = $response->data->result->status;
            if($status == 2){
                $voice_path = $response->data->result->splitter_data->voice_path;
                $music_path = $response->data->result->splitter_data->music_path;
                $cs_status_datetime = date('Y-m-d H:i:s');
                $tbl_asset_splitter_update_query = "UPDATE tbl_asset_splitter SET `vocal_path` = '".$voice_path."',`instrumental_path` = '".$music_path."',`cs_status` = 1,`cs_status_datetime`='".$cs_status_datetime."' WHERE cs_splitter_id='".$cs_splitter_id."'";
                $tbl_asset_splitter_update_query_result = $conn->query($tbl_asset_splitter_update_query);
                if($tbl_asset_splitter_update_query_result === true){
                    error_log("\n Voice Path And Instrumental Path Successfully Updated Into tbl_asset_splitter where cs_splitter_id = .'".$cs_splitter_id."'");
                    echo "<br> Voice Path And Instrumental Path Successfully Updated Into tbl_asset_splitter where cs_splitter_id = '".$cs_splitter_id."'";
                }else{
                    error_log("\n Something Went Wrong While Updating  Voice Path And Instrumental Path  Into tbl_asset_splitter where cs_splitter_id = '".$cs_splitter_id."'");
                    echo "<br> Something Went Wrong While Updating  Voice Path And Instrumental Path  Into tbl_asset_splitter where cs_splitter_id = ".$cs_splitter_id;
                }
            }else if($status == 3){
                $tbl_asset_splitter_processing_failed_update_query = "UPDATE tbl_asset_splitter SET `cs_status` = 3 WHERE cs_splitter_id='".$cs_splitter_id."'";
                $tbl_asset_splitter_processing_failed_update_query_result = $conn->query($tbl_asset_splitter_processing_failed_update_query);
                if($tbl_asset_splitter_processing_failed_update_query_result === true){
                    error_log("\n asset splitter processing is failed update for tbl_asset_splitter cs_splitter_id ='".$cs_splitter_id."'");
                    echo "<br> asset splitter processing is failed update for tbl_asset_splitter cs_splitter_id '".$cs_splitter_id."'";
                }else{
                    error_log("\n Something Went Wrong With processing is failed update for tbl_asset_splitter cs_splitter_id ='".$cs_splitter_id."'");
                    echo "<br> Something Went Wrong With processing is failed update for tbl_asset_splitter cs_splitter_id '".$cs_splitter_id."'";
                }
            }else{
                error_log("\n get Asset Splitter result status != 2 for tbl_asset_splitter cs_splitter_id = '".$cs_splitter_id."'");
                echo "<br> get Asset Splitter result status != 2 for tbl_asset_splitter cs_splitter_id = '".$cs_splitter_id."'";
            }
       
    }else{
        error_log("\n Something Went Wrong With  get_voiceover_degree_asset_data_callback response->msg");
        echo("Something Went Wrong With  get_voiceover_degree_asset_data_callback response->msg");
    }
}else{
    error_log("\n Something Went Wrong With  get_voiceover_degree_asset_data_callback response");
    echo("Something Went Wrong With  get_voiceover_degree_asset_data_callback response");
}*/



error_log("Ended at");
error_log(date('Y/m/d h:i:s', time()));
error_log("==========================================================================================================================================");


?>