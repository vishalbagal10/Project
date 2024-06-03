<?php
// log started
$file = "logs/send_asset_splitter_data_to_cs/send_asset_splitter_data_to_cs_log_".date('Y-m-d').".log";
if(!is_file($file)){
    //Some simple example content.
    $contents = '';
    //Save our content to the file.
    file_put_contents("logs/send_asset_splitter_data_to_cs/send_asset_splitter_data_to_cs_log_".date('Y-m-d').".log", $contents);
}

// error message to be logged
$error_message = "This is an error message!";
  
// path of the log file where errors need to be logged
$log_file = $file;
  
// setting error logging to be active
ini_set("log_errors", TRUE); 
  
// setting the logging file in php.ini
ini_set('error_log', $log_file);

// log end
set_time_limit(0);
error_log("Started at");
error_log(date('Y/m/d h:i:s', time()));
error_log("==========================================================================================================================================");

include("functions.php");

$dbcon = include('connection.php');
$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);
if($conn){
    echo "connection ok";
}else{
   echo "connection not found";
}

//////////////////////////////////////////////////////////////////

/* $tbl_asset_splitter_query = "SELECT * from tbl_asset_splitter 
LEFT JOIN tbl_asset_processed_cyanite_data on tbl_asset_splitter.id = tbl_asset_processed_cyanite_data.splitter_id
LEFT JOIN tbl_assets ON tbl_asset_processed_cyanite_data.asset_id = tbl_assets.cs_asset_id
LEFT JOIN tbl_social_spyder_graph_meta_data on tbl_assets.id = tbl_social_spyder_graph_meta_data.asset_id
where tbl_asset_processed_cyanite_data.is_active = 0 and tbl_social_spyder_graph_meta_data.is_active = 0 and tbl_asset_splitter.cs_status = 1 and tbl_asset_splitter.is_active = 0";

 */
$tbl_asset_splitter_query = "SELECT * from tbl_asset_splitter where  tbl_asset_splitter.cs_status = 1 and tbl_asset_splitter.is_active = 0 limit 40";
$tbl_asset_splitter_query_result = $conn->query($tbl_asset_splitter_query);
$tbl_asset_splitter_query_result_num_row = $tbl_asset_splitter_query_result->num_rows;
echo "num rows count == ".$tbl_asset_splitter_query_result_num_row;
error_log("num rows count == ".$tbl_asset_splitter_query_result_num_row);
try{
    if($tbl_asset_splitter_query_result_num_row > 0){
        $transaction_token = check_and_get_access_token($conn);
        $counter = 1;
        while($row = $tbl_asset_splitter_query_result->fetch_assoc()){
            $path = $row['instrumental_path'];
            $cs_splitter_id = $row['cs_splitter_id'];
            $instrumental_path_array = explode('/',$path);
            $instrumental_path_array_count = count($instrumental_path_array);
            $asset_name = $instrumental_path_array[$instrumental_path_array_count-1];
            $call_from  = 'media_splitter_data';
            $asset_type_id = 1;
            $asset_upload_at = 1;
            $data_status = 0;
            $track_id = 0;
            error_log($counter."------------------------------------------------------------------------------------------------");
            send_asset_content_to_central_system($call_from,$conn,$asset_name,$cs_splitter_id,$transaction_token,$asset_type_id,$asset_upload_at,$data_status,$track_id,$path);
            error_log("------------------------------------------------------------------------------------------------".$counter);
            $counter++;
        }

    }
}
catch(Exception $e){
    error_log("page : [send_asset_splitter_data_to_cs] : : error : ".$e->getMessage());
}




/////////////////////////////////////////////////////////////////

error_log("****************************************************************************************************************************");

error_log("Ended at");
error_log(date('Y/m/d h:i:s', time()));
error_log("==========================================================================================================================================");
?>
