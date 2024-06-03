<?php
 $file = "logs/get_voiceover_degree_asset_data/get_voiceover_degree_asset_data_log_".date('Y-m-d').".log";
 if(!is_file($file)){
 $contents = '';
 file_put_contents("logs/get_voiceover_degree_asset_data/get_voiceover_degree_asset_data_log_".date('Y-m-d').".log", $contents);
 }
 $error_message = "This is an error message!";
 $log_file = $file;
 ini_set("log_errors", TRUE); 
 ini_set('error_log', $log_file);

include "functions.php";
$dbcon = include('connection.php');
$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);

$tbl_asset_splitter_query = "SELECT * FROM `tbl_asset_splitter` WHERE cs_status = 0 and is_active = 0";
$tbl_asset_splitter_query_result = $conn->query($tbl_asset_splitter_query);
$tbl_asset_splitter_query_result_num_rows = $tbl_asset_splitter_query_result->num_rows;

if($tbl_asset_splitter_query_result_num_rows > 0){
    $transaction_token = check_and_get_access_token($conn);
    while($row = $tbl_asset_splitter_query_result->fetch_assoc()){
        $cs_splitter_id = $row['cs_splitter_id'];

        //$base_url = 'http://localhost:7474/'; // LOCAL SERVER
        // $base_url = 'https://soniccv.witsinteractive.in/public/audios/cv_audios/'; // TEST SERVER
        //$base_url = 'https://sonicradar.sonic-hub.com/public/audios/cv_audios/'; // LIVE SERVER
        
        //$url = "http://localhost:7474/php_script/voiceover_degree/cs_splitter_data.php"; // LOCAL SERVER
        // $url = "https://taxonomy.logthis.in/apis/send_requested_asset_result.php"; // TEST SERVER
        $url = "https://taxonomy.sonic-hub.com/apis/send_requested_asset_splitter_result.php"; // LIVE SERVER
        
        $data = array(
            'transaction_token' => $transaction_token,
            'splitter_id' => $cs_splitter_id,
        );
        $postParameter = json_encode($data);
        $curlResponse = curl_call_api($url,$postParameter);
        extract_voiceover_degree_asset_splitter_result($conn,$curlResponse);
    }
}else{
    error_log("no data found in tbl_asset_splitter");
    echo ("no data found in tbl_asset_splitter");
}

?>