<?php
// log started
$file = "logs/get_asset_data/get_asset_data_log_".date('Y-m-d').".log";
if(!is_file($file)){
    //Some simple example content.
    $contents = '';
    //Save our content to the file.
    file_put_contents("logs/get_asset_data/get_asset_data_log_".date('Y-m-d').".log", $contents);
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

include('functions.php');

$dbcon = include('connection.php');
$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);
// include("connection.php");
// $get_asset_id_to_fetch_asset_data_qry  = "SELECT * FROM `tbl_assets` WHERE `cs_d_status` = 1 AND `cs_response_status` = 0 AND `is_active` = 0 AND `cs_d_status_datetime` < DATE_SUB(NOW(),INTERVAL 30 MINUTE) AND (`cs_asset_id` != '' || `cs_asset_id` IS NOT NULL)";
// $get_asset_id_to_fetch_asset_data_qry  = "SELECT * FROM `tbl_assets` WHERE `cs_response_status` = 0 AND `is_active` = 0 AND (`cs_asset_id` != '' || `cs_asset_id` IS NOT NULL) ORDER BY `create_date` DESC limit 100";

//YOUTUBE
$get_asset_id_to_fetch_asset_data_qry  = "SELECT tbl_assets.* FROM `tbl_assets` join tbl_social_spyder_graph_meta_data on tbl_social_spyder_graph_meta_data.asset_id = tbl_assets.id join tbl_social_media_sync_process_data on tbl_social_spyder_graph_meta_data.cv_id = tbl_social_media_sync_process_data.cv_id WHERE `tbl_social_media_sync_process_data`.yt=1 AND tbl_social_media_sync_process_data.is_active=0 and tbl_social_spyder_graph_meta_data.process_type='youtube' and tbl_social_spyder_graph_meta_data.track_id!= '' and tbl_social_spyder_graph_meta_data.status > 1 and tbl_assets.`is_active` = 0 AND (tbl_assets.`cs_asset_id` != '' || tbl_assets.`cs_asset_id` IS NOT NULL) ORDER BY tbl_assets.`create_date` DESC";

$get_asset_id_to_fetch_asset_data_qry_res  = $conn->query($get_asset_id_to_fetch_asset_data_qry);
if($get_asset_id_to_fetch_asset_data_qry_res->num_rows == 0)
{
    error_log("No asset pending of youtube to fetch asset result from central system.");
    //INSTAGRAM
    $get_asset_id_to_fetch_asset_data_qry  = "SELECT tbl_assets.* FROM `tbl_assets` join tbl_social_spyder_graph_meta_data on tbl_social_spyder_graph_meta_data.asset_id = tbl_assets.id join tbl_social_media_sync_process_data on tbl_social_spyder_graph_meta_data.cv_id = tbl_social_media_sync_process_data.cv_id WHERE `tbl_social_media_sync_process_data`.ig=1 AND tbl_social_media_sync_process_data.is_active=0 and tbl_social_spyder_graph_meta_data.process_type='instagram' and tbl_social_spyder_graph_meta_data.track_id!= '' and tbl_social_spyder_graph_meta_data.status > 1 and tbl_assets.`is_active` = 0 AND (tbl_assets.`cs_asset_id` != '' || tbl_assets.`cs_asset_id` IS NOT NULL) ORDER BY tbl_assets.`create_date` DESC";
    $get_asset_id_to_fetch_asset_data_qry_res  = $conn->query($get_asset_id_to_fetch_asset_data_qry);
}

if($get_asset_id_to_fetch_asset_data_qry_res->num_rows == 0)
{
    error_log("No asset pending of instagram to fetch asset result from central system.");
    //TIKTOK
    $get_asset_id_to_fetch_asset_data_qry  = "SELECT tbl_assets.* FROM `tbl_assets` join tbl_social_spyder_graph_meta_data on tbl_social_spyder_graph_meta_data.asset_id = tbl_assets.id join tbl_social_media_sync_process_data on tbl_social_spyder_graph_meta_data.cv_id = tbl_social_media_sync_process_data.cv_id WHERE `tbl_social_media_sync_process_data`.tt=1 AND tbl_social_media_sync_process_data.is_active=0 and tbl_social_spyder_graph_meta_data.process_type='tiktok' and tbl_social_spyder_graph_meta_data.track_id!= '' and tbl_social_spyder_graph_meta_data.status > 1 and tbl_assets.`is_active` = 0 AND (tbl_assets.`cs_asset_id` != '' || tbl_assets.`cs_asset_id` IS NOT NULL) ORDER BY tbl_assets.`create_date` DESC";
    $get_asset_id_to_fetch_asset_data_qry_res  = $conn->query($get_asset_id_to_fetch_asset_data_qry);
}

if($get_asset_id_to_fetch_asset_data_qry_res->num_rows == 0)
{
    error_log("No asset pending of tiktok to fetch asset result from central system.");
    //TIKTOK
    $get_asset_id_to_fetch_asset_data_qry  = "SELECT tbl_assets.* FROM `tbl_assets` join tbl_social_spyder_graph_meta_data on tbl_social_spyder_graph_meta_data.asset_id = tbl_assets.id join tbl_social_media_sync_process_data on tbl_social_spyder_graph_meta_data.cv_id = tbl_social_media_sync_process_data.cv_id WHERE `tbl_social_media_sync_process_data`.twt=1 AND tbl_social_media_sync_process_data.is_active=0 and tbl_social_spyder_graph_meta_data.process_type='twitter' and tbl_social_spyder_graph_meta_data.track_id!= '' and tbl_social_spyder_graph_meta_data.status > 1 and tbl_assets.`is_active` = 0 AND (tbl_assets.`cs_asset_id` != '' || tbl_assets.`cs_asset_id` IS NOT NULL) ORDER BY tbl_assets.`create_date` DESC";
    $get_asset_id_to_fetch_asset_data_qry_res  = $conn->query($get_asset_id_to_fetch_asset_data_qry);
}

if($get_asset_id_to_fetch_asset_data_qry_res->num_rows > 0)
{
  $a_counter = 1;
  $txn_token = check_and_get_access_token($conn);
  // echo $txn_token."<br><br>";

  while($get_asset_id_to_fetch_asset_data_qry_res_row = $get_asset_id_to_fetch_asset_data_qry_res->fetch_assoc())
  {
    error_log($a_counter."***************************************************************************************************************");
    error_log("Getting asset result for Asset CS ID ".$get_asset_id_to_fetch_asset_data_qry_res_row['cs_asset_id']." started.");
    $asset_cs_id = $get_asset_id_to_fetch_asset_data_qry_res_row['cs_asset_id'];
    // echo $asset_cs_id."<br><br>";
    

    $asset_content_arr = ["transaction_token"=> $txn_token, "asset_id"=>$asset_cs_id];

    $content = json_encode($asset_content_arr);

    // $get_asset_result_content_url = "http://192.168.1.112:7474/scs/apis/send_requested_asset_result.php"; //LOCAL SERVER 1
    // $get_asset_result_content_url = "http://10.100.0.60:7474/scs/apis/send_requested_asset_result.php"; //LOCAL SERVER 2
    // $get_asset_result_content_url = "https://taxonomy.logthis.in/apis/send_requested_asset_result.php"; //TEST SERVER
    $get_asset_result_content_url = "https://taxonomy.sonic-hub.com/apis/send_requested_asset_result.php"; //LIVE SERVER

    $curl_response_data = api_call($conn, $get_asset_result_content_url, $content);
    // print_r($curl_response_data);
    // echo "<br>----------------------------------<br>";
    $response_content = json_decode($curl_response_data);
    // print_r($response_content);
    // echo "<br>----------------------------------<br>";

    $chk_in_splitter_tbl_qry = "SELECT tbl_asset_processed_cyanite_data.asset_id, tbl_asset_splitter.id, tbl_asset_splitter.cs_status_datetime FROM tbl_asset_splitter join tbl_asset_processed_cyanite_data on tbl_asset_splitter.id = tbl_asset_processed_cyanite_data.splitter_id WHERE tbl_asset_splitter.cs_asset_id = '".$asset_cs_id."' and tbl_asset_splitter.is_active = 0";
    $chk_in_splitter_tbl_qry_res = $conn->query($chk_in_splitter_tbl_qry);
    $present_at_splitter = 0;
    $splitter_id = 0;
    $cs_status_datetime = 0;
    $old_asset_cs_id = 0;
    error_log("ASSET ID1".$asset_cs_id);
    if($chk_in_splitter_tbl_qry_res->num_rows > 0)
    {
        $chk_in_splitter_tbl_qry_res_row = $chk_in_splitter_tbl_qry_res->fetch_assoc();
        $old_asset_cs_id = $chk_in_splitter_tbl_qry_res_row['asset_id'];
        $present_at_splitter = 1;
        $splitter_id = $chk_in_splitter_tbl_qry_res_row['id'];
        $cs_status_datetime = $chk_in_splitter_tbl_qry_res_row['cs_status_datetime'];
        // error_log("ASSET ID2".$asset_cs_id);
    }

    
    if($response_content->msg == 0)
    {
       $extract_asset_result_data_from_received_response_status = extract_asset_result_data_from_received_response($conn, $response_content, $dbcon, $old_asset_cs_id, $present_at_splitter, $splitter_id, $cs_status_datetime); 
      // $extract_asset_result_data_from_received_response_status = extract_asset_result_data_from_received_response($conn, $response_content, $dbcon);
      if($extract_asset_result_data_from_received_response_status == 1 || $extract_asset_result_data_from_received_response_status == 2)
      {
        // echo "UPDATE `tbl_assets` SET `cs_response_status` = ".$status.", `cs_response_dt`= '".$response_content->data->result->response_date_time."' WHERE `asset_cs_id` ='".$asset_cs_id."'";

        if($extract_asset_result_data_from_received_response_status == 1)
        {
          // echo $extract_asset_result_data_from_received_response_status."<br><br>";
          $get_curnt_asset_meta_data_qry = "SELECT tbl_social_spyder_graph_meta_data.id as meta_id, tbl_cvs.cv_id as cvid, tbl_cvs.cv_year, tbl_cvs.industry_id, tbl_cvs.sub_industry_id, tbl_social_spyder_graph_request_data.process_type, tbl_social_spyder_graph_request_data.down_count, tbl_social_spyder_graph_request_data.uploaded_start_id, tbl_social_spyder_graph_request_data.uploaded_end_id, tbl_social_media_sync_process_data.* FROM `tbl_social_spyder_graph_meta_data` left join tbl_assets on tbl_social_spyder_graph_meta_data.asset_id = tbl_assets.id left join tbl_cvs on tbl_social_spyder_graph_meta_data.cv_id = tbl_cvs.cv_id left join tbl_social_spyder_graph_request_data on tbl_social_spyder_graph_meta_data.chn_id = tbl_social_spyder_graph_request_data.chn_id left join tbl_social_media_sync_process_data on tbl_social_spyder_graph_request_data.cv_id = tbl_social_media_sync_process_data.cv_id WHERE tbl_assets.cs_asset_id = '".$asset_cs_id."'";

          // echo "**********get_curnt_asset_meta_data_qry".$get_curnt_asset_meta_data_qry."<br><br>";

          $get_curnt_asset_meta_data_qry_res = $conn->query($get_curnt_asset_meta_data_qry);
          $get_curnt_asset_meta_data_qry_res_row = $get_curnt_asset_meta_data_qry_res->fetch_assoc();

            switch($get_curnt_asset_meta_data_qry_res_row['process_type'])
            {
                case 'youtube':
                    $p_type = "yt";
                    $p_type_last_process_count = "yt_last_process_count";
                    $mood_graph_tbl_name = "tbl_social_media_yt_mood_graph_data";
                    $genre_graph_tbl_name = "tbl_social_media_yt_genre_graph_data";
                    break;
                case 'instagram':
                    $p_type = "ig";
                    $p_type_last_process_count = "ig_last_process_count";
                    $mood_graph_tbl_name = "tbl_social_media_ig_mood_graph_data";
                    $genre_graph_tbl_name = "tbl_social_media_ig_genre_graph_data";                            
                    break;
                case 'tiktok':
                    $p_type = "tt";
                    $p_type_last_process_count = "tt_last_process_count";
                    $mood_graph_tbl_name = "tbl_social_media_tt_mood_graph_data";
                    $genre_graph_tbl_name = "tbl_social_media_tt_genre_graph_data";
                    break;
                case 'twitter':
                    $p_type = "twt";
                    $p_type_last_process_count = "twt_last_process_count";
                    $mood_graph_tbl_name = "tbl_social_media_twt_mood_graph_data";
                    $genre_graph_tbl_name = "tbl_social_media_twt_genre_graph_data";
                    break;
            }

          $meta_tbl_id = $get_curnt_asset_meta_data_qry_res_row['meta_id'];
          $cv_id = $get_curnt_asset_meta_data_qry_res_row['cvid'];
          $cv_year = $get_curnt_asset_meta_data_qry_res_row['cv_year'];
          $industry_id = ($get_curnt_asset_meta_data_qry_res_row['industry_id'] != '' || $get_curnt_asset_meta_data_qry_res_row['industry_id'] != null ) ? $get_curnt_asset_meta_data_qry_res_row['industry_id'] : 0;
          $sub_industry_id = ($get_curnt_asset_meta_data_qry_res_row['sub_industry_id'] != '' || $get_curnt_asset_meta_data_qry_res_row['sub_industry_id'] != null ) ? $get_curnt_asset_meta_data_qry_res_row['sub_industry_id'] : 0;
          $process_type = $get_curnt_asset_meta_data_qry_res_row['process_type'];
          $down_count = $get_curnt_asset_meta_data_qry_res_row['down_count'];
          $uploaded_start_id = $get_curnt_asset_meta_data_qry_res_row['uploaded_start_id'];
          $uploaded_end_id = $get_curnt_asset_meta_data_qry_res_row['uploaded_end_id'];
          $last_processed_asset_count = ($get_curnt_asset_meta_data_qry_res_row[$p_type_last_process_count] != '' || $get_curnt_asset_meta_data_qry_res_row[$p_type_last_process_count] != null ) ? $get_curnt_asset_meta_data_qry_res_row[$p_type_last_process_count] : 0;

          if($conn->query("UPDATE `tbl_social_spyder_graph_meta_data` SET `status` = 2 WHERE `id` = ".$meta_tbl_id))
          {
            error_log("Status updated successfully in meta data table for meta data id".$meta_tbl_id);
            echo "Status updated successfully in meta data table for meta data id".$meta_tbl_id;
            $get_total_processed_asset_count_qry = "SELECT count(tbl_assets.id) as processed_asset_count FROM `tbl_social_spyder_graph_meta_data` join tbl_assets on tbl_social_spyder_graph_meta_data.asset_id = tbl_assets.id WHERE tbl_social_spyder_graph_meta_data.cv_id = ".$cv_id." and tbl_social_spyder_graph_meta_data.process_type = '".$process_type."' and tbl_social_spyder_graph_meta_data.is_active = 0 and tbl_assets.cs_response_status = 2";

            $get_total_processed_asset_count_qry_res = $conn->query($get_total_processed_asset_count_qry);
            $get_total_processed_asset_count_qry_res_row = $get_total_processed_asset_count_qry_res->fetch_assoc();

            $total_processed_asset_count = $get_total_processed_asset_count_qry_res_row['processed_asset_count'];


            $get_cv_social_media_curnt_data_qry = "SELECT tbl_social_media_sync_process_data.".$p_type_last_process_count." as last_processed_count, sum(tbl_social_spyder_graph_request_data.down_count) as down_count FROM `tbl_social_media_sync_process_data` join tbl_social_spyder_graph_request_data on tbl_social_media_sync_process_data.cv_id = tbl_social_spyder_graph_request_data.cv_id WHERE tbl_social_spyder_graph_request_data.cv_id = ".$cv_id." and tbl_social_spyder_graph_request_data.process_type = '".$process_type."'";

            $get_cv_social_media_curnt_data_qry_res = $conn->query($get_cv_social_media_curnt_data_qry);
            $get_cv_social_media_curnt_data_qry_res_row = $get_cv_social_media_curnt_data_qry_res->fetch_assoc();

            $down_count = $get_cv_social_media_curnt_data_qry_res_row['down_count'];
            $last_processed_asset_count = ($get_cv_social_media_curnt_data_qry_res_row['last_processed_count'] != '' || $get_cv_social_media_curnt_data_qry_res_row['last_processed_count'] != null ) ? $get_cv_social_media_curnt_data_qry_res_row['last_processed_count'] : 0;

            error_log("cv_id=>".$cv_id." | process_type=>".$process_type." || down_count=>".$down_count." ||| total_processed_asset_count=>".$total_processed_asset_count." |||| last_processed_asset_count=>".$last_processed_asset_count);

            echo "cv_id=>".$cv_id." | process_type=>".$process_type." || down_count=>".$down_count." ||| total_processed_asset_count=>".$total_processed_asset_count." |||| last_processed_asset_count=>".$last_processed_asset_count;

            $cv_multiplier = $dbcon['cv_multiplier'];

            if($total_processed_asset_count < $down_count)
            {
              if($total_processed_asset_count != $last_processed_asset_count)
              {
                if(($total_processed_asset_count-$last_processed_asset_count) == $cv_multiplier)
                {
                  echo "<br>IN 1<br>";
                  // generate graph code
                  $get_social_media_graph_avg_data_status = get_social_media_graph_avg_data($conn,$cv_id,$cv_year,$industry_id,$sub_industry_id,$process_type,$cv_multiplier,$down_count);
                  if($get_social_media_graph_avg_data_status == 1)
                  {
                    error_log("Individual Social Media ".$process_type." Graph for CV ".$cv_id." generated.");
                    $get_aggregate_graph_for_cv_status = get_aggregate_graph_for_cv($conn,$cv_id);
                    if($get_aggregate_graph_for_cv_status == 1)
                    {
                      error_log("Aggregate Graph for CV ".$cv_id." generated.");

                      $generate_top3_mood_genre_video_links_status = generate_top3_mood_genre_video_links($conn,$cv_id);

                      //////////////////////////////////

                      //get and store top 3 mood and genre video id and title of youtube in tbl_mood_genre_yt_videos start
                        /*$chk_mgytv_qry = "SELECT * FROM `tbl_mood_genre_yt_videos` WHERE cv_id=".$cv_id;
                        $chk_mgytv_qry_result = $conn->query($chk_mgytv_qry);
                        if ($chk_mgytv_qry_result->num_rows > 0)
                        {
                            while($chk_mgytv_qry_result_row = $chk_mgytv_qry_result->fetch_assoc())
                            {
                                $row_id = $chk_mgytv_qry_result_row['mgytv_id'];
                            }

                            $mgytv_ins_qry = "REPLACE INTO `tbl_mood_genre_yt_videos`(`mgytv_id`, `cv_id`, `mood_v1_id`, `mood_v1_title`, `mood_v2_id`, `mood_v2_title`, `mood_v3_id`, `mood_v3_title`, `genre_v1_id`, `genre_v1_title`, `genre_v2_id`, `genre_v2_title`, `genre_v3_id`, `genre_v3_title`) VALUES (".$row_id.",".$cv_id.",";
                        }
                        else
                        {
                            $mgytv_ins_qry = "INSERT INTO `tbl_mood_genre_yt_videos`(`cv_id`, `mood_v1_id`, `mood_v1_title`, `mood_v2_id`, `mood_v2_title`, `mood_v3_id`, `mood_v3_title`, `genre_v1_id`, `genre_v1_title`, `genre_v2_id`, `genre_v2_title`, `genre_v3_id`, `genre_v3_title`) VALUES (".$cv_id.",";
                        }

                        $cv_mood_aggr_graph_values_data_qry = "select * from `tbl_social_media_aggr_mood_graph_data` where `cv_id` =".$cv_id." and `is_active`=0";
                        $cv_mood_aggr_graph_values_data_qry_result = $conn->query($cv_mood_aggr_graph_values_data_qry);

                        if ($cv_mood_aggr_graph_values_data_qry_result->num_rows > 0)
                        {
                            $cv_mood_aggr_graph_values_data_array = [];
                            while($cv_mood_aggr_graph_values_data_qry_result_row = $cv_mood_aggr_graph_values_data_qry_result->fetch_assoc())
                            {
                                $cv_mood_aggr_graph_values_data_array['lbl_name'] = $cv_mood_aggr_graph_values_data_qry_result_row['lbl_value'];
                            }

                            //print_r($cv_mood_aggr_graph_values_data_array);

                            $cv_mood_aggr_graph_values_arr = $cv_mood_aggr_graph_values_data_array;
                            $cv_mood_aggr_graph_values_arr1 = $cv_mood_aggr_graph_values_data_array;
                            rsort($cv_mood_aggr_graph_values_arr);
                            $top3_mood = array_slice($cv_mood_aggr_graph_values_arr, 0, 3);   
                            foreach ($top3_mood as $mkey => $mval) {
                                //echo "key-".$mkey."----------- val-".$mval."<br>";
                                $mkey = array_search ($mval, $cv_mood_aggr_graph_values_arr1);
                                unset($cv_mood_aggr_graph_values_arr1[$mkey]);
                                //echo $mkey."<br>";
                                $top_3_mood_video_id_data = "SELECT video_id FROM `tbl_social_spyder_graph_meta_data` as a inner join (SELECT tbl_asset_processed_cyanite_data.cyanite_id FROM `tbl_asset_processed_cyanite_data` join tbl_assets on tbl_asset_processed_cyanite_data.asset_id = tbl_assets.cs_asset_id join tbl_social_spyder_graph_meta_data on tbl_social_spyder_graph_meta_data.asset_id = tbl_assets.id join tbl_cvs on tbl_cvs.cv_id = tbl_social_spyder_graph_meta_data.cv_id WHERE tbl_cvs.cv_id =".$cv_id." and tbl_asset_processed_cyanite_data.moodtags Like '%".$mkey."%' and tbl_cvs.is_active=0 and tbl_social_spyder_graph_meta_data.process_type='youtube' LIMIT 1) as a2 ON a.track_id=a2.cyanite_id";

                                $top_3_mood_video_id_data_result = $conn->query($top_3_mood_video_id_data);

                                if ($top_3_mood_video_id_data_result->num_rows > 0)
                                {
                                    $cv_mood_aggr_graph_values_data_array = [];
                                    while($top_3_mood_video_id_data_result_row = $top_3_mood_video_id_data_result->fetch_assoc())
                                    {
                                        //echo $top_3_mood_video_id_data_result_row['video_id']."<br>";
                                        $mapi_key = "AIzaSyCftMwHzQQ9Tt5KNtbWtAzf9k-2V8WIXU8";
                                        $mvideo_id = $top_3_mood_video_id_data_result_row['video_id'];
                                        $mtitle='';
                                        $murl = "https://www.googleapis.com/youtube/v3/videos?id=" . $mvideo_id . "&key=" . $mapi_key . "&part=snippet,contentDetails,statistics,status";
                                        $mjson = file_get_contents($murl);
                                        //$mgetData = json_decode( $mjson , true);
                                        $mgetData = json_decode( mb_convert_encoding($mjson, "HTML-ENTITIES", 'UTF-8') , true);
                                        foreach((array)$mgetData['items'] as $key => $gDat){
                                            $mtitle = $gDat['snippet']['title'];
                                        }
                                        // Output title
                                        //echo $mtitle."<br><br>";
                                        $mgytv_ins_qry .= "'".$mkey."$|$".$mvideo_id."','".str_replace("'","\'",$mtitle)."',";
                                    }
                                }
                                else
                                  {
                                      $mvideo_id = '';
                                      $mtitle='';
                                      $mgytv_ins_qry .= "'".$mvideo_id."','".str_replace("'","\'",$mtitle)."',";
                                  }
                            }
                        }

                        $cv_genre_aggr_graph_values_data_qry = "select * from `tbl_social_media_aggr_genre_graph_data` where `cv_id` =".$cv_id." and `is_active`=0";
                        $cv_genre_aggr_graph_values_data_result = $conn->query($cv_genre_aggr_graph_values_data_qry);

                        if ($cv_genre_aggr_graph_values_data_result->num_rows > 0)
                        {
                            $cv_genre_aggr_graph_values_data_array = [];
                            while($cv_genre_aggr_graph_values_data_result_row = $cv_genre_aggr_graph_values_data_result->fetch_assoc())
                            {
                                $cv_genre_aggr_graph_values_data_array['lbl_name'] = $cv_genre_aggr_graph_values_data_result_row['lbl_value'];

                            }

                            //print_r($cv_genre_aggr_graph_values_data_array);

                            $cv_genre_aggr_graph_values_arr = $cv_genre_aggr_graph_values_data_array;
                            $cv_genre_aggr_graph_values_arr1 = $cv_genre_aggr_graph_values_data_array;
                            rsort($cv_genre_aggr_graph_values_arr);
                            $top3_genre = array_slice($cv_genre_aggr_graph_values_arr, 0, 3);  
                            foreach ($top3_genre as $gkey => $gval) {
                                //echo "key-".$gkey."----------- val-".$gval."<br>";
                                $gkey = array_search ($gval, $cv_genre_aggr_graph_values_arr1);
                                unset($cv_genre_aggr_graph_values_arr1[$gkey]);
                                //echo $gkey."<br>";
                                $top_3_genre_video_id_data = "SELECT video_id FROM `tbl_social_spyder_graph_meta_data` as a inner join (SELECT tbl_asset_processed_cyanite_data.cyanite_id FROM `tbl_asset_processed_cyanite_data` join tbl_assets on tbl_asset_processed_cyanite_data.asset_id = tbl_assets.cs_asset_id join tbl_social_spyder_graph_meta_data on tbl_social_spyder_graph_meta_data.asset_id = tbl_assets.id join tbl_cvs on tbl_cvs.cv_id = tbl_social_spyder_graph_meta_data.cv_id WHERE tbl_cvs.cv_id =".$cv_id." and tbl_asset_processed_cyanite_data.genretags Like '%".$gkey."%' and tbl_cvs.is_active=0 and tbl_social_spyder_graph_meta_data.process_type='youtube' LIMIT 1) as a2 ON a.track_id=a2.cyanite_id";

                                $top_3_genre_video_id_data_result = $conn->query($top_3_genre_video_id_data);

                                if ($top_3_genre_video_id_data_result->num_rows > 0)
                                {
                                    $cv_genre_aggr_graph_values_data_array = [];
                                    while($top_3_genre_video_id_data_result_row = $top_3_genre_video_id_data_result->fetch_assoc())
                                    {
                                        //echo $top_3_genre_video_id_data_result_row['video_id']."<br>";
                                        $gapi_key = "AIzaSyCftMwHzQQ9Tt5KNtbWtAzf9k-2V8WIXU8";
                                        $gvideo_id = $top_3_genre_video_id_data_result_row['video_id'];
                                        $gtitle='';
                                        $gurl = "https://www.googleapis.com/youtube/v3/videos?id=" . $gvideo_id . "&key=" . $gapi_key . "&part=snippet,contentDetails,statistics,status";
                                        $gjson = file_get_contents($gurl);
                                        //$ggetData = json_decode( $gjson , true);
                                        $ggetData = json_decode( mb_convert_encoding($gjson, "HTML-ENTITIES", 'UTF-8') , true);
                                        foreach((array)$ggetData['items'] as $key => $gDat){
                                            $gtitle = $gDat['snippet']['title'];
                                        }
                                        // Output title
                                        //echo $gtitle."<br><br>";
                                        $mgytv_ins_qry .= "'".$gkey."$|$".$gvideo_id."','".str_replace("'","\'",$gtitle)."',";                
                                    }
                                }
                                else
                                  {
                                      $gtitle=''; 
                                      $gvideo_id='';
                                      $mgytv_ins_qry .= "'".$gvideo_id."','".str_replace("'","\'",$gtitle)."',";
                                  }
                            }
                        }
                        $final_mgytv_ins_qry = rtrim($mgytv_ins_qry,",").")";
                        //echo $final_mgytv_ins_qry;
                        if($conn->query($final_mgytv_ins_qry))
                        {
                          error_log("top 3 mood and genre video id and title of youtube inserted into tbl_mood_genre_yt_videos for cv_id - ".$cv_id);
                        }
                        else
                        {
                          error_log("someting went wrong while inserting top 3 mood and genre video id and title of youtube into tbl_mood_genre_yt_videos for cv_id - ".$cv_id);
                        }*/
                      //get and store top 3 mood and genre video id and title of youtube in tbl_mood_genre_yt_videos end

                      /////////////////////////////////
                    }
                  }
                }
                /*else
                {
                  error_log("Processed count not matched with muitplier to generate graph");
                  if($total_processed_asset_count == $down_count)
                  {
                    echo "<br>IN 2<br>";
                    // generate graph code
                    $get_social_media_graph_avg_data_status = get_social_media_graph_avg_data($conn,$cv_id,$cv_year,$industry_id,$sub_industry_id,$process_type,$cv_multiplier);
                    if($get_social_media_graph_avg_data_status == 1)
                    {
                      error_log("Individual Social Media ".$process_type." Graph for CV ".$cv_id." generated.");
                      $get_aggregate_graph_for_cv_status = get_aggregate_graph_for_cv($conn,$cv_id);
                      if($get_aggregate_graph_for_cv_status == 1)
                      {
                        error_log("Aggregate Graph for CV ".$cv_id." generated.");

                        $get_cvs_process_type_qry = "SELECT * FROM `tbl_social_spyder_graph_request_data` WHERE cv_id = ".$cv_id." and is_active = 0 and chn_notfound != 1";
                        $get_cvs_process_type_qry_res = $conn->query($get_cvs_process_type_qry);                    
                        $process_type_arr = [];
                        while($get_cvs_process_type_qry_res_row = $get_cvs_process_type_qry_res->fetch_assoc())
                        {
                          array_push($process_type_arr,$get_cvs_process_type_qry_res_row['process_type']);
                        }
                        if(count($process_type_arr)>0)
                        {
                          $cvs_social_media_data_processing_complete_arr = [];
                          foreach($process_type_arr as $process_type)
                          {
                            switch($process_type)
                            {
                                case 'youtube':
                                  $p_type_last_process_count = "yt_last_process_count";
                                  break;
                                case 'instagram':
                                  $p_type_last_process_count = "ig_last_process_count";                     
                                  break;
                                case 'tiktok':
                                  $p_type_last_process_count = "tt_last_process_count";
                                  break;
                                case 'twitter':
                                  $p_type_last_process_count = "twt_last_process_count";
                                  break;
                            }
                            $chk_cvs_social_media_data_processing_complete_qry = "SELECT tbl_social_media_sync_process_data.".$p_type_last_process_count." as last_processed_count, sum(tbl_social_spyder_graph_request_data.down_count) as down_count FROM `tbl_social_media_sync_process_data` join tbl_social_spyder_graph_request_data on tbl_social_media_sync_process_data.cv_id = tbl_social_spyder_graph_request_data.cv_id WHERE tbl_social_spyder_graph_request_data.cv_id = ".$cv_id." and tbl_social_spyder_graph_request_data.process_type = '".$process_type."'";
                            $chk_cvs_social_media_data_processing_complete_qry_res = $conn->query($chk_cvs_social_media_data_processing_complete_qry);
                            $chk_cvs_social_media_data_processing_complete_qry_res_row = $chk_cvs_social_media_data_processing_complete_qry_res->fetch_assoc();
                            if($chk_cvs_social_media_data_processing_complete_qry_res_row['last_processed_count'] == $chk_cvs_social_media_data_processing_complete_qry_res_row['down_count'])
                            {
                                array_push($cvs_social_media_data_processing_complete_arr,$process_type);
                            }
                          }

                          if(count($cvs_social_media_data_processing_complete_arr) == count($process_type_arr))
                          {
                            if($conn->query("UPDATE `tbl_social_media_sync_process_data` SET `status`= 1 WHERE ".$cv_id))
                            {
                              error_log("Status updated successfully in social media sync process table for cv".$cv_id);
                              if($industry_id != 0)
                              {
                                //get_industry_social_media_graph_avg_data($conn,$cv_id,$cv_year,$industry_id,$sub_industry_id);
                                $add_ind_to_process_queue_qry = "INSERT INTO `tbl_ind_social_media_process_data`(`industry_id`, `cv_id`, `year`, `status`) VALUES (".$industry_id.",".$cv_id.",'".$cv_year."', 0)";
                                if($conn->query($add_ind_to_process_queue_qry) === TRUE)
                                {
                                  error_log("Industry ".$industry_id." is successfully added to process queue.");
                                  if($sub_industry_id != 0)
                                  {
                                    //get_sub_industry_social_media_graph_avg_data($conn,$cv_id,$cv_year,$industry_id,$sub_industry_id);
                                    $add_sind_to_process_queue_qry = "INSERT INTO `tbl_sind_social_media_process_data`(`sub_industry_id`, `cv_id`, `year`, `status`) VALUES (".$sub_industry_id.",".$cv_id.",'".$cv_year."', 0)";
                                    if($conn->query($add_sind_to_process_queue_qry) === TRUE)
                                    {
                                      error_log("Sub Industry ".$sub_industry_id." is successfully added to process queue.");
                                      
                                    }
                                    else
                                    {
                                      error_log("Error occured while adding Sub Industry ".$sub_industry_id." to process queue.");
                                    }
                                  }
                                }
                                else
                                {
                                  error_log("Error occured while adding Industry ".$industry_id." to process queue.");
                                }
                              }
                            }
                            else
                            {
                              error_log("Error occured while updating status in social media sync process table for cv".$cv_id);
                            }
                          }
                        }
                      }
                    }
                  }
                }*/
              }
            }
            else
            {
              if($total_processed_asset_count >= $down_count)
              {
                echo "<br>IN 3<br>";
                // generate graph code
                $get_social_media_graph_avg_data_status = get_social_media_graph_avg_data($conn,$cv_id,$cv_year,$industry_id,$sub_industry_id,$process_type,$cv_multiplier,$down_count);
                if($get_social_media_graph_avg_data_status == 1)
                {
                  error_log("Individual Social Media ".$process_type." Graph for CV ".$cv_id." generated.");
                  $get_aggregate_graph_for_cv_status = get_aggregate_graph_for_cv($conn,$cv_id);
                  if($get_aggregate_graph_for_cv_status == 1)
                  {
                    error_log("Aggregate Graph for CV ".$cv_id." generated.");

                    $generate_top3_mood_genre_video_links_status = generate_top3_mood_genre_video_links($conn,$cv_id);

                    //////////////////////////////////

                    //get and store top 3 mood and genre video id and title of youtube in tbl_mood_genre_yt_videos start
                      /*$chk_mgytv_qry = "SELECT * FROM `tbl_mood_genre_yt_videos` WHERE cv_id=".$cv_id;
                      $chk_mgytv_qry_result = $conn->query($chk_mgytv_qry);
                      if ($chk_mgytv_qry_result->num_rows > 0)
                      {
                          while($chk_mgytv_qry_result_row = $chk_mgytv_qry_result->fetch_assoc())
                          {
                              $row_id = $chk_mgytv_qry_result_row['mgytv_id'];
                          }

                          $mgytv_ins_qry = "REPLACE INTO `tbl_mood_genre_yt_videos`(`mgytv_id`, `cv_id`, `mood_v1_id`, `mood_v1_title`, `mood_v2_id`, `mood_v2_title`, `mood_v3_id`, `mood_v3_title`, `genre_v1_id`, `genre_v1_title`, `genre_v2_id`, `genre_v2_title`, `genre_v3_id`, `genre_v3_title`) VALUES (".$row_id.",".$cv_id.",";
                      }
                      else
                      {
                          $mgytv_ins_qry = "INSERT INTO `tbl_mood_genre_yt_videos`(`cv_id`, `mood_v1_id`, `mood_v1_title`, `mood_v2_id`, `mood_v2_title`, `mood_v3_id`, `mood_v3_title`, `genre_v1_id`, `genre_v1_title`, `genre_v2_id`, `genre_v2_title`, `genre_v3_id`, `genre_v3_title`) VALUES (".$cv_id.",";
                      }

                      $cv_mood_aggr_graph_values_data_qry = "select * from `tbl_social_media_aggr_mood_graph_data` where `cv_id` =".$cv_id." and `is_active`=0";
                      $cv_mood_aggr_graph_values_data_qry_result = $conn->query($cv_mood_aggr_graph_values_data_qry);

                      if ($cv_mood_aggr_graph_values_data_qry_result->num_rows > 0)
                      {
                          $cv_mood_aggr_graph_values_data_array = [];
                          while($cv_mood_aggr_graph_values_data_qry_result_row = $cv_mood_aggr_graph_values_data_qry_result->fetch_assoc())
                          {
                              $cv_mood_aggr_graph_values_data_array['lbl_name'] = $cv_mood_aggr_graph_values_data_qry_result_row['lbl_value'];
                          }

                          //print_r($cv_mood_aggr_graph_values_data_array);

                          $cv_mood_aggr_graph_values_arr = $cv_mood_aggr_graph_values_data_array;
                          $cv_mood_aggr_graph_values_arr1 = $cv_mood_aggr_graph_values_data_array;
                          rsort($cv_mood_aggr_graph_values_arr);
                          $top3_mood = array_slice($cv_mood_aggr_graph_values_arr, 0, 3);   
                          foreach ($top3_mood as $mkey => $mval) {
                              //echo "key-".$mkey."----------- val-".$mval."<br>";
                              $mkey = array_search ($mval, $cv_mood_aggr_graph_values_arr1);
                              unset($cv_mood_aggr_graph_values_arr1[$mkey]);
                              //echo $mkey."<br>";
                              $top_3_mood_video_id_data = "SELECT video_id FROM `tbl_social_spyder_graph_meta_data` as a inner join (SELECT tbl_asset_processed_cyanite_data.cyanite_id FROM `tbl_asset_processed_cyanite_data` join tbl_assets on tbl_asset_processed_cyanite_data.asset_id = tbl_assets.cs_asset_id join tbl_social_spyder_graph_meta_data on tbl_social_spyder_graph_meta_data.asset_id = tbl_assets.id join tbl_cvs on tbl_cvs.cv_id = tbl_social_spyder_graph_meta_data.cv_id WHERE tbl_cvs.cv_id =".$cv_id." and tbl_asset_processed_cyanite_data.moodtags Like '%".$mkey."%' and tbl_cvs.is_active=0 and tbl_social_spyder_graph_meta_data.process_type='youtube' LIMIT 1) as a2 ON a.track_id=a2.cyanite_id";

                              $top_3_mood_video_id_data_result = $conn->query($top_3_mood_video_id_data);

                              if ($top_3_mood_video_id_data_result->num_rows > 0)
                              {
                                  $cv_mood_aggr_graph_values_data_array = [];
                                  while($top_3_mood_video_id_data_result_row = $top_3_mood_video_id_data_result->fetch_assoc())
                                  {
                                      //echo $top_3_mood_video_id_data_result_row['video_id']."<br>";
                                      $mapi_key = "AIzaSyCftMwHzQQ9Tt5KNtbWtAzf9k-2V8WIXU8";
                                      $mvideo_id = $top_3_mood_video_id_data_result_row['video_id'];
                                      $mtitle='';
                                      $murl = "https://www.googleapis.com/youtube/v3/videos?id=" . $mvideo_id . "&key=" . $mapi_key . "&part=snippet,contentDetails,statistics,status";
                                      $mjson = file_get_contents($murl);
                                      //$mgetData = json_decode( $mjson , true);
                                      $mgetData = json_decode( mb_convert_encoding($mjson, "HTML-ENTITIES", 'UTF-8') , true);
                                      foreach((array)$mgetData['items'] as $key => $gDat){
                                          $mtitle = $gDat['snippet']['title'];
                                      }
                                      // Output title
                                      //echo $mtitle."<br><br>";
                                      $mgytv_ins_qry .= "'".$mkey."$|$".$mvideo_id."','".str_replace("'","\'",$mtitle)."',";
                                  }
                              }
                              else
                                {
                                    $mvideo_id = '';
                                    $mtitle='';
                                    $mgytv_ins_qry .= "'".$mvideo_id."','".str_replace("'","\'",$mtitle)."',";
                                }
                          }
                      }

                      $cv_genre_aggr_graph_values_data_qry = "select * from `tbl_social_media_aggr_genre_graph_data` where `cv_id` =".$cv_id." and `is_active`=0";
                      $cv_genre_aggr_graph_values_data_result = $conn->query($cv_genre_aggr_graph_values_data_qry);

                      if ($cv_genre_aggr_graph_values_data_result->num_rows > 0)
                      {
                          $cv_genre_aggr_graph_values_data_array = [];
                          while($cv_genre_aggr_graph_values_data_result_row = $cv_genre_aggr_graph_values_data_result->fetch_assoc())
                          {
                              $cv_genre_aggr_graph_values_data_array['lbl_name'] = $cv_genre_aggr_graph_values_data_result_row['lbl_value'];

                          }

                          //print_r($cv_genre_aggr_graph_values_data_array);

                          $cv_genre_aggr_graph_values_arr = $cv_genre_aggr_graph_values_data_array;
                          $cv_genre_aggr_graph_values_arr1 = $cv_genre_aggr_graph_values_data_array;
                          rsort($cv_genre_aggr_graph_values_arr);
                          $top3_genre = array_slice($cv_genre_aggr_graph_values_arr, 0, 3);  
                          foreach ($top3_genre as $gkey => $gval) {
                              //echo "key-".$gkey."----------- val-".$gval."<br>";
                              $gkey = array_search ($gval, $cv_genre_aggr_graph_values_arr1);
                              unset($cv_genre_aggr_graph_values_arr1[$gkey]);
                              //echo $gkey."<br>";
                              $top_3_genre_video_id_data = "SELECT video_id FROM `tbl_social_spyder_graph_meta_data` as a inner join (SELECT tbl_asset_processed_cyanite_data.cyanite_id FROM `tbl_asset_processed_cyanite_data` join tbl_assets on tbl_asset_processed_cyanite_data.asset_id = tbl_assets.cs_asset_id join tbl_social_spyder_graph_meta_data on tbl_social_spyder_graph_meta_data.asset_id = tbl_assets.id join tbl_cvs on tbl_cvs.cv_id = tbl_social_spyder_graph_meta_data.cv_id WHERE tbl_cvs.cv_id =".$cv_id." and tbl_asset_processed_cyanite_data.genretags Like '%".$gkey."%' and tbl_cvs.is_active=0 and tbl_social_spyder_graph_meta_data.process_type='youtube' LIMIT 1) as a2 ON a.track_id=a2.cyanite_id";

                              $top_3_genre_video_id_data_result = $conn->query($top_3_genre_video_id_data);

                              if ($top_3_genre_video_id_data_result->num_rows > 0)
                              {
                                  $cv_genre_aggr_graph_values_data_array = [];
                                  while($top_3_genre_video_id_data_result_row = $top_3_genre_video_id_data_result->fetch_assoc())
                                  {
                                      //echo $top_3_genre_video_id_data_result_row['video_id']."<br>";
                                      $gapi_key = "AIzaSyCftMwHzQQ9Tt5KNtbWtAzf9k-2V8WIXU8";
                                      $gvideo_id = $top_3_genre_video_id_data_result_row['video_id'];
                                      $gtitle='';
                                      $gurl = "https://www.googleapis.com/youtube/v3/videos?id=" . $gvideo_id . "&key=" . $gapi_key . "&part=snippet,contentDetails,statistics,status";
                                      $gjson = file_get_contents($gurl);
                                      //$ggetData = json_decode( $gjson , true);
                                      $ggetData = json_decode( mb_convert_encoding($gjson, "HTML-ENTITIES", 'UTF-8') , true);
                                      foreach((array)$ggetData['items'] as $key => $gDat){
                                          $gtitle = $gDat['snippet']['title'];
                                      }
                                      // Output title
                                      //echo $gtitle."<br><br>";
                                      $mgytv_ins_qry .= "'".$gkey."$|$".$gvideo_id."','".str_replace("'","\'",$gtitle)."',";                
                                  }
                              }
                              else
                                {
                                    $gtitle=''; 
                                    $gvideo_id='';
                                    $mgytv_ins_qry .= "'".$gvideo_id."','".str_replace("'","\'",$gtitle)."',";
                                }
                          }
                      }
                      $final_mgytv_ins_qry = rtrim($mgytv_ins_qry,",").")";
                      //echo $final_mgytv_ins_qry;
                      if($conn->query($final_mgytv_ins_qry))
                      {
                        error_log("top 3 mood and genre video id and title of youtube inserted into tbl_mood_genre_yt_videos for cv_id - ".$cv_id);
                      }
                      else
                      {
                        error_log("someting went wrong while inserting top 3 mood and genre video id and title of youtube into tbl_mood_genre_yt_videos for cv_id - ".$cv_id);
                      }*/
                    //get and store top 3 mood and genre video id and title of youtube in tbl_mood_genre_yt_videos end

                    /////////////////////////////////

                    $get_cvs_process_type_qry = "SELECT * FROM `tbl_social_spyder_graph_request_data` WHERE cv_id = ".$cv_id." and is_active = 0 and chn_notfound != 1";
                      $get_cvs_process_type_qry_res = $conn->query($get_cvs_process_type_qry);                    
                      $process_type_arr = [];
                    while($get_cvs_process_type_qry_res_row = $get_cvs_process_type_qry_res->fetch_assoc())
                    {
                      array_push($process_type_arr,$get_cvs_process_type_qry_res_row['process_type']);
                    }
                    if(count($process_type_arr)>0)
                    {
                      $cvs_social_media_data_processing_complete_arr = [];
                      foreach($process_type_arr as $process_type)
                      {
                        switch($process_type)
                        {
                            case 'youtube':
                              $p_type_last_process_count = "yt_last_process_count";
                              break;
                            case 'instagram':
                              $p_type_last_process_count = "ig_last_process_count";                     
                              break;
                            case 'tiktok':
                              $p_type_last_process_count = "tt_last_process_count";
                              break;
                            case 'twitter':
                              $p_type_last_process_count = "twt_last_process_count";
                              break;
                        }
                        $chk_cvs_social_media_data_processing_complete_qry = "SELECT tbl_social_media_sync_process_data.".$p_type_last_process_count." as last_processed_count, sum(tbl_social_spyder_graph_request_data.down_count) as down_count FROM `tbl_social_media_sync_process_data` join tbl_social_spyder_graph_request_data on tbl_social_media_sync_process_data.cv_id = tbl_social_spyder_graph_request_data.cv_id WHERE tbl_social_spyder_graph_request_data.cv_id = ".$cv_id." and tbl_social_spyder_graph_request_data.process_type = '".$process_type."'";
                        $chk_cvs_social_media_data_processing_complete_qry_res = $conn->query($chk_cvs_social_media_data_processing_complete_qry);
                        $chk_cvs_social_media_data_processing_complete_qry_res_row = $chk_cvs_social_media_data_processing_complete_qry_res->fetch_assoc();
                        if($chk_cvs_social_media_data_processing_complete_qry_res_row['last_processed_count'] == $chk_cvs_social_media_data_processing_complete_qry_res_row['down_count'])
                        {
                            array_push($cvs_social_media_data_processing_complete_arr,$process_type);
                        }
                      }

                      if(count($cvs_social_media_data_processing_complete_arr) == count($process_type_arr))
                      {
                        if($conn->query("UPDATE `tbl_social_media_sync_process_data` SET `status`= 1 WHERE ".$cv_id))
                        {
                          error_log("Status updated successfully in social media sync process table for cv".$cv_id);
                          if($industry_id != 0)
                          {
                            //get_industry_social_media_graph_avg_data($conn,$cv_id,$cv_year,$industry_id,$sub_industry_id);
                            $add_ind_to_process_queue_qry = "INSERT INTO `tbl_ind_social_media_process_data`(`industry_id`, `cv_id`, `year`, `status`) VALUES (".$industry_id.",".$cv_id.",'".$cv_year."', 0)";
                            if($conn->query($add_ind_to_process_queue_qry) === TRUE)
                            {
                              error_log("Industry ".$industry_id." is successfully added to process queue.");
                              if($sub_industry_id != 0)
                              {
                                //get_sub_industry_social_media_graph_avg_data($conn,$cv_id,$cv_year,$industry_id,$sub_industry_id);
                                $add_sind_to_process_queue_qry = "INSERT INTO `tbl_sind_social_media_process_data`(`sub_industry_id`, `cv_id`, `year`, `status`) VALUES (".$sub_industry_id.",".$cv_id.",'".$cv_year."', 0)";
                                if($conn->query($add_sind_to_process_queue_qry) === TRUE)
                                {
                                  error_log("Sub Industry ".$sub_industry_id." is successfully added to process queue.");
                                  
                                }
                                else
                                {
                                  error_log("Error occured while adding Sub Industry ".$sub_industry_id." to process queue.");
                                }
                              }
                            }
                            else
                            {
                              error_log("Error occured while adding Industry ".$industry_id." to process queue.");
                            }
                          }
                        }
                        else
                        {
                          error_log("Error occured while updating status in social media sync process table for cv".$cv_id);
                        }
                      }
                    }
                  }
                }
              }
              else
              {
                error_log("Processed count id is greater than down count");
              }
            }
          }
          else
          {
            error_log("Error occured while updating status in meta data table for meta data id".$meta_tbl_id);
          }
        }
      }
      
    }
    else
    {
      if($response_content->msg != 1)
      {
        $conn->query("UPDATE `tbl_cs_access_token` SET `is_active` = 1");
        $txn_token = get_access_token($conn);

        $asset_content_arr = ["transaction_token"=> $txn_token, "asset_id"=>$asset_cs_id];

        $content = json_encode($asset_content_arr);

        //getting asset data again
        $curl_response_data = api_call($conn, $get_asset_result_content_url, $content);
        // print_r($curl_response_data);
        // echo "<br>----------------------------------<br>";
        $response_content = json_decode($curl_response_data);
        // print_r($response_content);
        // echo "<br>----------------------------------<br>";

        $extract_asset_result_data_from_received_response_status = extract_asset_result_data_from_received_response($conn, $response_content, $dbcon, $old_asset_cs_id, $present_at_splitter, $splitter_id, $cs_status_datetime);
        // $extract_asset_result_data_from_received_response_status = extract_asset_result_data_from_received_response($conn, $response_content, $dbcon);
        if($extract_asset_result_data_from_received_response_status == 1 || $extract_asset_result_data_from_received_response_status == 2)
        {
          // echo "UPDATE `tbl_assets` SET `cs_response_status` = ".$status.", `cs_response_dt`= '".$response_content->data->result->response_date_time."' WHERE `asset_cs_id` ='".$asset_cs_id."'";

          if($extract_asset_result_data_from_received_response_status == 1)
          {
            // echo $extract_asset_result_data_from_received_response_status."<br><br>";
            $get_curnt_asset_meta_data_qry = "SELECT tbl_social_spyder_graph_meta_data.id as meta_id, tbl_cvs.cv_id as cvid, tbl_cvs.cv_year, tbl_cvs.industry_id, tbl_cvs.sub_industry_id, tbl_social_spyder_graph_request_data.process_type, tbl_social_spyder_graph_request_data.down_count, tbl_social_spyder_graph_request_data.uploaded_start_id, tbl_social_spyder_graph_request_data.uploaded_end_id, tbl_social_media_sync_process_data.* FROM `tbl_social_spyder_graph_meta_data` left join tbl_assets on tbl_social_spyder_graph_meta_data.asset_id = tbl_assets.id left join tbl_cvs on tbl_social_spyder_graph_meta_data.cv_id = tbl_cvs.cv_id left join tbl_social_spyder_graph_request_data on tbl_social_spyder_graph_meta_data.chn_id = tbl_social_spyder_graph_request_data.chn_id left join tbl_social_media_sync_process_data on tbl_social_spyder_graph_request_data.cv_id = tbl_social_media_sync_process_data.cv_id WHERE tbl_assets.cs_asset_id = '".$asset_cs_id."'";

            // echo "**********get_curnt_asset_meta_data_qry".$get_curnt_asset_meta_data_qry."<br><br>";

            $get_curnt_asset_meta_data_qry_res = $conn->query($get_curnt_asset_meta_data_qry);
            $get_curnt_asset_meta_data_qry_res_row = $get_curnt_asset_meta_data_qry_res->fetch_assoc();

            switch($get_curnt_asset_meta_data_qry_res_row['process_type'])
            {
                case 'youtube':
                    $p_type = "yt";
                    $p_type_last_process_count = "yt_last_process_count";
                    $mood_graph_tbl_name = "tbl_social_media_yt_mood_graph_data";
                    $genre_graph_tbl_name = "tbl_social_media_yt_genre_graph_data";
                    break;
                case 'instagram':
                    $p_type = "ig";
                    $p_type_last_process_count = "ig_last_process_count";
                    $mood_graph_tbl_name = "tbl_social_media_ig_mood_graph_data";
                    $genre_graph_tbl_name = "tbl_social_media_ig_genre_graph_data";                            
                    break;
                case 'tiktok':
                    $p_type = "tt";
                    $p_type_last_process_count = "tt_last_process_count";
                    $mood_graph_tbl_name = "tbl_social_media_tt_mood_graph_data";
                    $genre_graph_tbl_name = "tbl_social_media_tt_genre_graph_data";
                    break;
                case 'twitter':
                    $p_type = "twt";
                    $p_type_last_process_count = "twt_last_process_count";
                    $mood_graph_tbl_name = "tbl_social_media_twt_mood_graph_data";
                    $genre_graph_tbl_name = "tbl_social_media_twt_genre_graph_data";
                    break;
            }

            $meta_tbl_id = $get_curnt_asset_meta_data_qry_res_row['meta_id'];
            $cv_id = $get_curnt_asset_meta_data_qry_res_row['cvid'];
            $cv_year = $get_curnt_asset_meta_data_qry_res_row['cv_year'];
            $industry_id = ($get_curnt_asset_meta_data_qry_res_row['industry_id'] != '' || $get_curnt_asset_meta_data_qry_res_row['industry_id'] != null ) ? $get_curnt_asset_meta_data_qry_res_row['industry_id'] : 0;
            $sub_industry_id = ($get_curnt_asset_meta_data_qry_res_row['sub_industry_id'] != '' || $get_curnt_asset_meta_data_qry_res_row['sub_industry_id'] != null ) ? $get_curnt_asset_meta_data_qry_res_row['sub_industry_id'] : 0;
            $process_type = $get_curnt_asset_meta_data_qry_res_row['process_type'];
            $down_count = $get_curnt_asset_meta_data_qry_res_row['down_count'];
            $uploaded_start_id = $get_curnt_asset_meta_data_qry_res_row['uploaded_start_id'];
            $uploaded_end_id = $get_curnt_asset_meta_data_qry_res_row['uploaded_end_id'];
            $last_processed_asset_count = ($get_curnt_asset_meta_data_qry_res_row[$p_type_last_process_count] != '' || $get_curnt_asset_meta_data_qry_res_row[$p_type_last_process_count] != null ) ? $get_curnt_asset_meta_data_qry_res_row[$p_type_last_process_count] : 0;

            if($conn->query("UPDATE `tbl_social_spyder_graph_meta_data` SET `status` = 2 WHERE `id` = ".$meta_tbl_id))
            {
              error_log("Status updated successfully in meta data table for meta data id".$meta_tbl_id);
              echo "Status updated successfully in meta data table for meta data id".$meta_tbl_id;
              $get_total_processed_asset_count_qry = "SELECT count(tbl_assets.id) as processed_asset_count FROM `tbl_social_spyder_graph_meta_data` join tbl_assets on tbl_social_spyder_graph_meta_data.asset_id = tbl_assets.id WHERE tbl_social_spyder_graph_meta_data.cv_id = ".$cv_id." and tbl_social_spyder_graph_meta_data.process_type = '".$process_type."' and tbl_social_spyder_graph_meta_data.is_active = 0 and tbl_assets.cs_response_status = 2";

              $get_total_processed_asset_count_qry_res = $conn->query($get_total_processed_asset_count_qry);
              $get_total_processed_asset_count_qry_res_row = $get_total_processed_asset_count_qry_res->fetch_assoc();

              $total_processed_asset_count = $get_total_processed_asset_count_qry_res_row['processed_asset_count'];

              $get_cv_social_media_curnt_data_qry = "SELECT tbl_social_media_sync_process_data.".$p_type_last_process_count." as last_processed_count, sum(tbl_social_spyder_graph_request_data.down_count) as down_count FROM `tbl_social_media_sync_process_data` join tbl_social_spyder_graph_request_data on tbl_social_media_sync_process_data.cv_id = tbl_social_spyder_graph_request_data.cv_id WHERE tbl_social_spyder_graph_request_data.cv_id = ".$cv_id." and tbl_social_spyder_graph_request_data.process_type = '".$process_type."'";

              $get_cv_social_media_curnt_data_qry_res = $conn->query($get_cv_social_media_curnt_data_qry);
              $get_cv_social_media_curnt_data_qry_res_row = $get_cv_social_media_curnt_data_qry_res->fetch_assoc();

              $down_count = $get_cv_social_media_curnt_data_qry_res_row['down_count'];
              $last_processed_asset_count = ($get_cv_social_media_curnt_data_qry_res_row['last_processed_count'] != '' || $get_cv_social_media_curnt_data_qry_res_row['last_processed_count'] != null ) ? $get_cv_social_media_curnt_data_qry_res_row['last_processed_count'] : 0;

              error_log("cv_id=>".$cv_id." | process_type=>".$process_type." || down_count=>".$down_count." ||| total_processed_asset_count=>".$total_processed_asset_count." |||| last_processed_asset_count=>".$last_processed_asset_count);

              echo "cv_id=>".$cv_id." | process_type=>".$process_type." || down_count=>".$down_count." ||| total_processed_asset_count=>".$total_processed_asset_count." |||| last_processed_asset_count=>".$last_processed_asset_count;

              $cv_multiplier = $dbcon['cv_multiplier'];

              if($total_processed_asset_count < $down_count)
              {
                if($total_processed_asset_count != $last_processed_asset_count)
                {
                  if(($total_processed_asset_count-$last_processed_asset_count) == $cv_multiplier)
                  {
                    echo "<br>IN 1<br>";
                    // generate graph code
                    $get_social_media_graph_avg_data_status = get_social_media_graph_avg_data($conn,$cv_id,$cv_year,$industry_id,$sub_industry_id,$process_type,$cv_multiplier,$down_count);
                    if($get_social_media_graph_avg_data_status == 1)
                    {
                      error_log("Individual Social Media ".$process_type." Graph for CV ".$cv_id." generated.");
                      $get_aggregate_graph_for_cv_status = get_aggregate_graph_for_cv($conn,$cv_id);
                      if($get_aggregate_graph_for_cv_status == 1)
                      {
                        error_log("Aggregate Graph for CV ".$cv_id." generated.");

                        $generate_top3_mood_genre_video_links_status = generate_top3_mood_genre_video_links($conn,$cv_id);

                        //////////////////////////////////

                        //get and store top 3 mood and genre video id and title of youtube in tbl_mood_genre_yt_videos start
                          /*$chk_mgytv_qry = "SELECT * FROM `tbl_mood_genre_yt_videos` WHERE cv_id=".$cv_id;
                          $chk_mgytv_qry_result = $conn->query($chk_mgytv_qry);
                          if ($chk_mgytv_qry_result->num_rows > 0)
                          {
                              while($chk_mgytv_qry_result_row = $chk_mgytv_qry_result->fetch_assoc())
                              {
                                  $row_id = $chk_mgytv_qry_result_row['mgytv_id'];
                              }

                              $mgytv_ins_qry = "REPLACE INTO `tbl_mood_genre_yt_videos`(`mgytv_id`, `cv_id`, `mood_v1_id`, `mood_v1_title`, `mood_v2_id`, `mood_v2_title`, `mood_v3_id`, `mood_v3_title`, `genre_v1_id`, `genre_v1_title`, `genre_v2_id`, `genre_v2_title`, `genre_v3_id`, `genre_v3_title`) VALUES (".$row_id.",".$cv_id.",";
                          }
                          else
                          {
                              $mgytv_ins_qry = "INSERT INTO `tbl_mood_genre_yt_videos`(`cv_id`, `mood_v1_id`, `mood_v1_title`, `mood_v2_id`, `mood_v2_title`, `mood_v3_id`, `mood_v3_title`, `genre_v1_id`, `genre_v1_title`, `genre_v2_id`, `genre_v2_title`, `genre_v3_id`, `genre_v3_title`) VALUES (".$cv_id.",";
                          }

                          $cv_mood_aggr_graph_values_data_qry = "select * from `tbl_social_media_aggr_mood_graph_data` where `cv_id` =".$cv_id." and `is_active`=0";
                          $cv_mood_aggr_graph_values_data_qry_result = $conn->query($cv_mood_aggr_graph_values_data_qry);

                          if ($cv_mood_aggr_graph_values_data_qry_result->num_rows > 0)
                          {
                              $cv_mood_aggr_graph_values_data_array = [];
                              while($cv_mood_aggr_graph_values_data_qry_result_row = $cv_mood_aggr_graph_values_data_qry_result->fetch_assoc())
                              {
                                  $cv_mood_aggr_graph_values_data_array['lbl_name'] = $cv_mood_aggr_graph_values_data_qry_result_row['lbl_value'];
                              }

                              //print_r($cv_mood_aggr_graph_values_data_array);

                              $cv_mood_aggr_graph_values_arr = $cv_mood_aggr_graph_values_data_array;
                              $cv_mood_aggr_graph_values_arr1 = $cv_mood_aggr_graph_values_data_array;
                              rsort($cv_mood_aggr_graph_values_arr);
                              $top3_mood = array_slice($cv_mood_aggr_graph_values_arr, 0, 3);   
                              foreach ($top3_mood as $mkey => $mval) {
                                  //echo "key-".$mkey."----------- val-".$mval."<br>";
                                  $mkey = array_search ($mval, $cv_mood_aggr_graph_values_arr1);
                                  unset($cv_mood_aggr_graph_values_arr1[$mkey]);
                                  //echo $mkey."<br>";
                                  $top_3_mood_video_id_data = "SELECT video_id FROM `tbl_social_spyder_graph_meta_data` as a inner join (SELECT tbl_asset_processed_cyanite_data.cyanite_id FROM `tbl_asset_processed_cyanite_data` join tbl_assets on tbl_asset_processed_cyanite_data.asset_id = tbl_assets.cs_asset_id join tbl_social_spyder_graph_meta_data on tbl_social_spyder_graph_meta_data.asset_id = tbl_assets.id join tbl_cvs on tbl_cvs.cv_id = tbl_social_spyder_graph_meta_data.cv_id WHERE tbl_cvs.cv_id =".$cv_id." and tbl_asset_processed_cyanite_data.moodtags Like '%".$mkey."%' and tbl_cvs.is_active=0 and tbl_social_spyder_graph_meta_data.process_type='youtube' LIMIT 1) as a2 ON a.track_id=a2.cyanite_id";

                                  $top_3_mood_video_id_data_result = $conn->query($top_3_mood_video_id_data);

                                  if ($top_3_mood_video_id_data_result->num_rows > 0)
                                  {
                                      $cv_mood_aggr_graph_values_data_array = [];
                                      while($top_3_mood_video_id_data_result_row = $top_3_mood_video_id_data_result->fetch_assoc())
                                      {
                                          //echo $top_3_mood_video_id_data_result_row['video_id']."<br>";
                                          $mapi_key = "AIzaSyCftMwHzQQ9Tt5KNtbWtAzf9k-2V8WIXU8";
                                          $mvideo_id = $top_3_mood_video_id_data_result_row['video_id'];
                                          $mtitle='';
                                          $murl = "https://www.googleapis.com/youtube/v3/videos?id=" . $mvideo_id . "&key=" . $mapi_key . "&part=snippet,contentDetails,statistics,status";
                                          $mjson = file_get_contents($murl);
                                          //$mgetData = json_decode( $mjson , true);
                                          $mgetData = json_decode( mb_convert_encoding($mjson, "HTML-ENTITIES", 'UTF-8') , true);
                                          foreach((array)$mgetData['items'] as $key => $gDat){
                                              $mtitle = $gDat['snippet']['title'];
                                          }
                                          // Output title
                                          //echo $mtitle."<br><br>";
                                          $mgytv_ins_qry .= "'".$mkey."$|$".$mvideo_id."','".str_replace("'","\'",$mtitle)."',";
                                      }
                                  }
                                  else
                                    {
                                        $mvideo_id = '';
                                        $mtitle='';
                                        $mgytv_ins_qry .= "'".$mvideo_id."','".str_replace("'","\'",$mtitle)."',";
                                    }
                              }
                          }

                          $cv_genre_aggr_graph_values_data_qry = "select * from `tbl_social_media_aggr_genre_graph_data` where `cv_id` =".$cv_id." and `is_active`=0";
                          $cv_genre_aggr_graph_values_data_result = $conn->query($cv_genre_aggr_graph_values_data_qry);

                          if ($cv_genre_aggr_graph_values_data_result->num_rows > 0)
                          {
                              $cv_genre_aggr_graph_values_data_array = [];
                              while($cv_genre_aggr_graph_values_data_result_row = $cv_genre_aggr_graph_values_data_result->fetch_assoc())
                              {
                                  $cv_genre_aggr_graph_values_data_array['lbl_name'] = $cv_genre_aggr_graph_values_data_result_row['lbl_value'];

                              }

                              //print_r($cv_genre_aggr_graph_values_data_array);

                              $cv_genre_aggr_graph_values_arr = $cv_genre_aggr_graph_values_data_array;
                              $cv_genre_aggr_graph_values_arr1 = $cv_genre_aggr_graph_values_data_array;
                              rsort($cv_genre_aggr_graph_values_arr);
                              $top3_genre = array_slice($cv_genre_aggr_graph_values_arr, 0, 3);  
                              foreach ($top3_genre as $gkey => $gval) {
                                  //echo "key-".$gkey."----------- val-".$gval."<br>";
                                  $gkey = array_search ($gval, $cv_genre_aggr_graph_values_arr1);
                                  unset($cv_genre_aggr_graph_values_arr1[$gkey]);
                                  //echo $gkey."<br>";
                                  $top_3_genre_video_id_data = "SELECT video_id FROM `tbl_social_spyder_graph_meta_data` as a inner join (SELECT tbl_asset_processed_cyanite_data.cyanite_id FROM `tbl_asset_processed_cyanite_data` join tbl_assets on tbl_asset_processed_cyanite_data.asset_id = tbl_assets.cs_asset_id join tbl_social_spyder_graph_meta_data on tbl_social_spyder_graph_meta_data.asset_id = tbl_assets.id join tbl_cvs on tbl_cvs.cv_id = tbl_social_spyder_graph_meta_data.cv_id WHERE tbl_cvs.cv_id =".$cv_id." and tbl_asset_processed_cyanite_data.genretags Like '%".$gkey."%' and tbl_cvs.is_active=0 and tbl_social_spyder_graph_meta_data.process_type='youtube' LIMIT 1) as a2 ON a.track_id=a2.cyanite_id";

                                  $top_3_genre_video_id_data_result = $conn->query($top_3_genre_video_id_data);

                                  if ($top_3_genre_video_id_data_result->num_rows > 0)
                                  {
                                      $cv_genre_aggr_graph_values_data_array = [];
                                      while($top_3_genre_video_id_data_result_row = $top_3_genre_video_id_data_result->fetch_assoc())
                                      {
                                          //echo $top_3_genre_video_id_data_result_row['video_id']."<br>";
                                          $gapi_key = "AIzaSyCftMwHzQQ9Tt5KNtbWtAzf9k-2V8WIXU8";
                                          $gvideo_id = $top_3_genre_video_id_data_result_row['video_id'];
                                          $gtitle='';
                                          $gurl = "https://www.googleapis.com/youtube/v3/videos?id=" . $gvideo_id . "&key=" . $gapi_key . "&part=snippet,contentDetails,statistics,status";
                                          $gjson = file_get_contents($gurl);
                                          //$ggetData = json_decode( $gjson , true);
                                          $ggetData = json_decode( mb_convert_encoding($gjson, "HTML-ENTITIES", 'UTF-8') , true);
                                          foreach((array)$ggetData['items'] as $key => $gDat){
                                              $gtitle = $gDat['snippet']['title'];
                                          }
                                          // Output title
                                          //echo $gtitle."<br><br>";
                                          $mgytv_ins_qry .= "'".$gkey."$|$".$gvideo_id."','".str_replace("'","\'",$gtitle)."',";                
                                      }
                                  }
                                  else
                                    {
                                        $gtitle=''; 
                                        $gvideo_id='';
                                        $mgytv_ins_qry .= "'".$gvideo_id."','".str_replace("'","\'",$gtitle)."',";
                                    }
                              }
                          }
                          $final_mgytv_ins_qry = rtrim($mgytv_ins_qry,",").")";
                          echo "<br>-*-*-*-*-<br>top 3 mood and genre video id and title of youtube<br>".$final_mgytv_ins_qry."<br>-*-*-*-*-<br>";
                          if($conn->query($final_mgytv_ins_qry))
                          {
                            error_log("top 3 mood and genre video id and title of youtube inserted into tbl_mood_genre_yt_videos for cv_id - ".$cv_id);
                          }
                          else
                          {
                            error_log("someting went wrong while inserting top 3 mood and genre video id and title of youtube into tbl_mood_genre_yt_videos for cv_id - ".$cv_id);
                          }*/
                        //get and store top 3 mood and genre video id and title of youtube in tbl_mood_genre_yt_videos end

                        /////////////////////////////////
                      }
                    }
                  }
                  /*else
                  {
                    error_log("Processed count not matched with muitplier to generate graph");
                    if($total_processed_asset_count == $down_count)
                    {
                      echo "<br>IN 2<br>";
                      // generate graph code
                      $get_social_media_graph_avg_data_status = get_social_media_graph_avg_data($conn,$cv_id,$cv_year,$industry_id,$sub_industry_id,$process_type,$cv_multiplier);
                      if($get_social_media_graph_avg_data_status == 1)
                      {
                        error_log("Individual Social Media ".$process_type." Graph for CV ".$cv_id." generated.");
                        $get_aggregate_graph_for_cv_status = get_aggregate_graph_for_cv($conn,$cv_id);
                        if($get_aggregate_graph_for_cv_status == 1)
                        {
                          error_log("Aggregate Graph for CV ".$cv_id." generated.");

                          $get_cvs_process_type_qry = "SELECT * FROM `tbl_social_spyder_graph_request_data` WHERE cv_id = ".$cv_id." and is_active = 0 and chn_notfound != 1";
                          $get_cvs_process_type_qry_res = $conn->query($get_cvs_process_type_qry);                    
                          $process_type_arr = [];
                          while($get_cvs_process_type_qry_res_row = $get_cvs_process_type_qry_res->fetch_assoc())
                          {
                            array_push($process_type_arr,$get_cvs_process_type_qry_res_row['process_type']);
                          }
                          if(count($process_type_arr)>0)
                          {
                            $cvs_social_media_data_processing_complete_arr = [];
                            foreach($process_type_arr as $process_type)
                            {
                              switch($process_type)
                              {
                                  case 'youtube':
                                    $p_type_last_process_count = "yt_last_process_count";
                                    break;
                                  case 'instagram':
                                    $p_type_last_process_count = "ig_last_process_count";                     
                                    break;
                                  case 'tiktok':
                                    $p_type_last_process_count = "tt_last_process_count";
                                    break;
                                  case 'twitter':
                                    $p_type_last_process_count = "twt_last_process_count";
                                    break;
                              }
                              $chk_cvs_social_media_data_processing_complete_qry = "SELECT tbl_social_media_sync_process_data.".$p_type_last_process_count." as last_processed_count, sum(tbl_social_spyder_graph_request_data.down_count) as down_count FROM `tbl_social_media_sync_process_data` join tbl_social_spyder_graph_request_data on tbl_social_media_sync_process_data.cv_id = tbl_social_spyder_graph_request_data.cv_id WHERE tbl_social_spyder_graph_request_data.cv_id = ".$cv_id." and tbl_social_spyder_graph_request_data.process_type = '".$process_type."'";
                              $chk_cvs_social_media_data_processing_complete_qry_res = $conn->query($chk_cvs_social_media_data_processing_complete_qry);
                              $chk_cvs_social_media_data_processing_complete_qry_res_row = $chk_cvs_social_media_data_processing_complete_qry_res->fetch_assoc();
                              if($chk_cvs_social_media_data_processing_complete_qry_res_row['last_processed_count'] == $chk_cvs_social_media_data_processing_complete_qry_res_row['down_count'])
                              {
                                  array_push($cvs_social_media_data_processing_complete_arr,$process_type);
                              }
                            }

                            if(count($cvs_social_media_data_processing_complete_arr) == count($process_type_arr))
                            {
                              if($conn->query("UPDATE `tbl_social_media_sync_process_data` SET `status`= 1 WHERE ".$cv_id))
                              {
                                error_log("Status updated successfully in social media sync process table for cv".$cv_id);
                                if($industry_id != 0)
                                {
                                  //get_industry_social_media_graph_avg_data($conn,$cv_id,$cv_year,$industry_id,$sub_industry_id);
                                  $add_ind_to_process_queue_qry = "INSERT INTO `tbl_ind_social_media_process_data`(`industry_id`, `cv_id`, `year`, `status`) VALUES (".$industry_id.",".$cv_id.",'".$cv_year."', 0)";
                                  if($conn->query($add_ind_to_process_queue_qry) === TRUE)
                                  {
                                    error_log("Industry ".$industry_id." is successfully added to process queue.");
                                    if($sub_industry_id != 0)
                                    {
                                      //get_sub_industry_social_media_graph_avg_data($conn,$cv_id,$cv_year,$industry_id,$sub_industry_id);
                                      $add_sind_to_process_queue_qry = "INSERT INTO `tbl_sind_social_media_process_data`(`sub_industry_id`, `cv_id`, `year`, `status`) VALUES (".$sub_industry_id.",".$cv_id.",'".$cv_year."', 0)";
                                      if($conn->query($add_sind_to_process_queue_qry) === TRUE)
                                      {
                                        error_log("Sub Industry ".$sub_industry_id." is successfully added to process queue.");
                                        
                                      }
                                      else
                                      {
                                        error_log("Error occured while adding Sub Industry ".$sub_industry_id." to process queue.");
                                      }
                                    }
                                  }
                                  else
                                  {
                                    error_log("Error occured while adding Industry ".$industry_id." to process queue.");
                                  }
                                }
                              }
                              else
                              {
                                error_log("Error occured while updating status in social media sync process table for cv".$cv_id);
                              }
                            }
                          }
                        }
                      }
                    }
                  }*/
                }
              }
              else
              {
                if($total_processed_asset_count >= $down_count)
                {
                  echo "<br>IN 3<br>";
                  // generate graph code
                  $get_social_media_graph_avg_data_status = get_social_media_graph_avg_data($conn,$cv_id,$cv_year,$industry_id,$sub_industry_id,$process_type,$cv_multiplier,$down_count);
                  if($get_social_media_graph_avg_data_status == 1)
                  {
                    error_log("Individual Social Media ".$process_type." Graph for CV ".$cv_id." generated.");
                    $get_aggregate_graph_for_cv_status = get_aggregate_graph_for_cv($conn,$cv_id);
                    if($get_aggregate_graph_for_cv_status == 1)
                    {
                      error_log("Aggregate Graph for CV ".$cv_id." generated.");

                      $generate_top3_mood_genre_video_links_status = generate_top3_mood_genre_video_links($conn,$cv_id);

                      //////////////////////////////////

                      //get and store top 3 mood and genre video id and title of youtube in tbl_mood_genre_yt_videos start
                        /*$chk_mgytv_qry = "SELECT * FROM `tbl_mood_genre_yt_videos` WHERE cv_id=".$cv_id;
                        $chk_mgytv_qry_result = $conn->query($chk_mgytv_qry);
                        if ($chk_mgytv_qry_result->num_rows > 0)
                        {
                            while($chk_mgytv_qry_result_row = $chk_mgytv_qry_result->fetch_assoc())
                            {
                                $row_id = $chk_mgytv_qry_result_row['mgytv_id'];
                            }

                            $mgytv_ins_qry = "REPLACE INTO `tbl_mood_genre_yt_videos`(`mgytv_id`, `cv_id`, `mood_v1_id`, `mood_v1_title`, `mood_v2_id`, `mood_v2_title`, `mood_v3_id`, `mood_v3_title`, `genre_v1_id`, `genre_v1_title`, `genre_v2_id`, `genre_v2_title`, `genre_v3_id`, `genre_v3_title`) VALUES (".$row_id.",".$cv_id.",";
                        }
                        else
                        {
                            $mgytv_ins_qry = "INSERT INTO `tbl_mood_genre_yt_videos`(`cv_id`, `mood_v1_id`, `mood_v1_title`, `mood_v2_id`, `mood_v2_title`, `mood_v3_id`, `mood_v3_title`, `genre_v1_id`, `genre_v1_title`, `genre_v2_id`, `genre_v2_title`, `genre_v3_id`, `genre_v3_title`) VALUES (".$cv_id.",";
                        }

                        $cv_mood_aggr_graph_values_data_qry = "select * from `tbl_social_media_aggr_mood_graph_data` where `cv_id` =".$cv_id." and `is_active`=0";
                        $cv_mood_aggr_graph_values_data_qry_result = $conn->query($cv_mood_aggr_graph_values_data_qry);

                        if ($cv_mood_aggr_graph_values_data_qry_result->num_rows > 0)
                        {
                            $cv_mood_aggr_graph_values_data_array = [];
                            while($cv_mood_aggr_graph_values_data_qry_result_row = $cv_mood_aggr_graph_values_data_qry_result->fetch_assoc())
                            {
                                $cv_mood_aggr_graph_values_data_array['lbl_name'] = $cv_mood_aggr_graph_values_data_qry_result_row['lbl_value'];
                            }

                            //print_r($cv_mood_aggr_graph_values_data_array);

                            $cv_mood_aggr_graph_values_arr = $cv_mood_aggr_graph_values_data_array;
                            $cv_mood_aggr_graph_values_arr1 = $cv_mood_aggr_graph_values_data_array;
                            rsort($cv_mood_aggr_graph_values_arr);
                            $top3_mood = array_slice($cv_mood_aggr_graph_values_arr, 0, 3);   
                            foreach ($top3_mood as $mkey => $mval) {
                                //echo "key-".$mkey."----------- val-".$mval."<br>";
                                $mkey = array_search ($mval, $cv_mood_aggr_graph_values_arr1);
                                unset($cv_mood_aggr_graph_values_arr1[$mkey]);
                                //echo $mkey."<br>";
                                $top_3_mood_video_id_data = "SELECT video_id FROM `tbl_social_spyder_graph_meta_data` as a inner join (SELECT tbl_asset_processed_cyanite_data.cyanite_id FROM `tbl_asset_processed_cyanite_data` join tbl_assets on tbl_asset_processed_cyanite_data.asset_id = tbl_assets.cs_asset_id join tbl_social_spyder_graph_meta_data on tbl_social_spyder_graph_meta_data.asset_id = tbl_assets.id join tbl_cvs on tbl_cvs.cv_id = tbl_social_spyder_graph_meta_data.cv_id WHERE tbl_cvs.cv_id =".$cv_id." and tbl_asset_processed_cyanite_data.moodtags Like '%".$mkey."%' and tbl_cvs.is_active=0 and tbl_social_spyder_graph_meta_data.process_type='youtube' LIMIT 1) as a2 ON a.track_id=a2.cyanite_id";

                                $top_3_mood_video_id_data_result = $conn->query($top_3_mood_video_id_data);

                                if ($top_3_mood_video_id_data_result->num_rows > 0)
                                {
                                    $cv_mood_aggr_graph_values_data_array = [];
                                    while($top_3_mood_video_id_data_result_row = $top_3_mood_video_id_data_result->fetch_assoc())
                                    {
                                        //echo $top_3_mood_video_id_data_result_row['video_id']."<br>";
                                        $mapi_key = "AIzaSyCftMwHzQQ9Tt5KNtbWtAzf9k-2V8WIXU8";
                                        $mvideo_id = $top_3_mood_video_id_data_result_row['video_id'];
                                        $mtitle='';
                                        $murl = "https://www.googleapis.com/youtube/v3/videos?id=" . $mvideo_id . "&key=" . $mapi_key . "&part=snippet,contentDetails,statistics,status";
                                        $mjson = file_get_contents($murl);
                                        //$mgetData = json_decode( $mjson , true);
                                        $mgetData = json_decode( mb_convert_encoding($mjson, "HTML-ENTITIES", 'UTF-8') , true);
                                        foreach((array)$mgetData['items'] as $key => $gDat){
                                            $mtitle = $gDat['snippet']['title'];
                                        }
                                        // Output title
                                        //echo $mtitle."<br><br>";
                                        $mgytv_ins_qry .= "'".$mkey."$|$".$mvideo_id."','".str_replace("'","\'",$mtitle)."',";
                                    }
                                }
                                else
                                  {
                                      $mvideo_id = '';
                                      $mtitle='';
                                      $mgytv_ins_qry .= "'".$mvideo_id."','".str_replace("'","\'",$mtitle)."',";
                                  }
                            }
                        }

                        $cv_genre_aggr_graph_values_data_qry = "select * from `tbl_social_media_aggr_genre_graph_data` where `cv_id` =".$cv_id." and `is_active`=0";
                        $cv_genre_aggr_graph_values_data_result = $conn->query($cv_genre_aggr_graph_values_data_qry);

                        if ($cv_genre_aggr_graph_values_data_result->num_rows > 0)
                        {
                            $cv_genre_aggr_graph_values_data_array = [];
                            while($cv_genre_aggr_graph_values_data_result_row = $cv_genre_aggr_graph_values_data_result->fetch_assoc())
                            {
                                $cv_genre_aggr_graph_values_data_array['lbl_name'] = $cv_genre_aggr_graph_values_data_result_row['lbl_value'];

                            }

                            //print_r($cv_genre_aggr_graph_values_data_array);

                            $cv_genre_aggr_graph_values_arr = $cv_genre_aggr_graph_values_data_array;
                            $cv_genre_aggr_graph_values_arr1 = $cv_genre_aggr_graph_values_data_array;
                            rsort($cv_genre_aggr_graph_values_arr);
                            $top3_genre = array_slice($cv_genre_aggr_graph_values_arr, 0, 3);  
                            foreach ($top3_genre as $gkey => $gval) {
                                //echo "key-".$gkey."----------- val-".$gval."<br>";
                                $gkey = array_search ($gval, $cv_genre_aggr_graph_values_arr1);
                                unset($cv_genre_aggr_graph_values_arr1[$gkey]);
                                //echo $gkey."<br>";
                                $top_3_genre_video_id_data = "SELECT video_id FROM `tbl_social_spyder_graph_meta_data` as a inner join (SELECT tbl_asset_processed_cyanite_data.cyanite_id FROM `tbl_asset_processed_cyanite_data` join tbl_assets on tbl_asset_processed_cyanite_data.asset_id = tbl_assets.cs_asset_id join tbl_social_spyder_graph_meta_data on tbl_social_spyder_graph_meta_data.asset_id = tbl_assets.id join tbl_cvs on tbl_cvs.cv_id = tbl_social_spyder_graph_meta_data.cv_id WHERE tbl_cvs.cv_id =".$cv_id." and tbl_asset_processed_cyanite_data.genretags Like '%".$gkey."%' and tbl_cvs.is_active=0 and tbl_social_spyder_graph_meta_data.process_type='youtube' LIMIT 1) as a2 ON a.track_id=a2.cyanite_id";

                                $top_3_genre_video_id_data_result = $conn->query($top_3_genre_video_id_data);

                                if ($top_3_genre_video_id_data_result->num_rows > 0)
                                {
                                    $cv_genre_aggr_graph_values_data_array = [];
                                    while($top_3_genre_video_id_data_result_row = $top_3_genre_video_id_data_result->fetch_assoc())
                                    {
                                        //echo $top_3_genre_video_id_data_result_row['video_id']."<br>";
                                        $gapi_key = "AIzaSyCftMwHzQQ9Tt5KNtbWtAzf9k-2V8WIXU8";
                                        $gvideo_id = $top_3_genre_video_id_data_result_row['video_id'];
                                        $gtitle='';
                                        $gurl = "https://www.googleapis.com/youtube/v3/videos?id=" . $gvideo_id . "&key=" . $gapi_key . "&part=snippet,contentDetails,statistics,status";
                                        $gjson = file_get_contents($gurl);
                                        //$ggetData = json_decode( $gjson , true);
                                        $ggetData = json_decode( mb_convert_encoding($gjson, "HTML-ENTITIES", 'UTF-8') , true);
                                        foreach((array)$ggetData['items'] as $key => $gDat){
                                            $gtitle = $gDat['snippet']['title'];
                                        }
                                        // Output title
                                        //echo $gtitle."<br><br>";
                                        $mgytv_ins_qry .= "'".$gkey."$|$".$gvideo_id."','".str_replace("'","\'",$gtitle)."',";                
                                    }
                                }
                                else
                                  {
                                      $gtitle=''; 
                                      $gvideo_id='';
                                      $mgytv_ins_qry .= "'".$gvideo_id."','".str_replace("'","\'",$gtitle)."',";
                                  }
                            }
                        }
                        $final_mgytv_ins_qry = rtrim($mgytv_ins_qry,",").")";
                        echo "<br>-*-*-*-*-<br>top 3 mood and genre video id and title of youtube<br>".$final_mgytv_ins_qry."<br>-*-*-*-*-<br>";
                        if($conn->query($final_mgytv_ins_qry))
                        {
                          error_log("top 3 mood and genre video id and title of youtube inserted into tbl_mood_genre_yt_videos for cv_id - ".$cv_id);
                        }
                        else
                        {
                          error_log("someting went wrong while inserting top 3 mood and genre video id and title of youtube into tbl_mood_genre_yt_videos for cv_id - ".$cv_id);
                        }*/
                      //get and store top 3 mood and genre video id and title of youtube in tbl_mood_genre_yt_videos end

                      /////////////////////////////////

                      $get_cvs_process_type_qry = "SELECT * FROM `tbl_social_spyder_graph_request_data` WHERE cv_id = ".$cv_id." and is_active = 0 and chn_notfound != 1";
                        $get_cvs_process_type_qry_res = $conn->query($get_cvs_process_type_qry);                    
                        $process_type_arr = [];
                      while($get_cvs_process_type_qry_res_row = $get_cvs_process_type_qry_res->fetch_assoc())
                      {
                        array_push($process_type_arr,$get_cvs_process_type_qry_res_row['process_type']);
                      }
                      if(count($process_type_arr)>0)
                      {
                        $cvs_social_media_data_processing_complete_arr = [];
                        foreach($process_type_arr as $process_type)
                        {
                          switch($process_type)
                          {
                              case 'youtube':
                                $p_type_last_process_count = "yt_last_process_count";
                                break;
                              case 'instagram':
                                $p_type_last_process_count = "ig_last_process_count";                     
                                break;
                              case 'tiktok':
                                $p_type_last_process_count = "tt_last_process_count";
                                break;
                              case 'twitter':
                                $p_type_last_process_count = "twt_last_process_count";
                                break;
                          }
                          $chk_cvs_social_media_data_processing_complete_qry = "SELECT tbl_social_media_sync_process_data.".$p_type_last_process_count." as last_processed_count, sum(tbl_social_spyder_graph_request_data.down_count) as down_count FROM `tbl_social_media_sync_process_data` join tbl_social_spyder_graph_request_data on tbl_social_media_sync_process_data.cv_id = tbl_social_spyder_graph_request_data.cv_id WHERE tbl_social_spyder_graph_request_data.cv_id = ".$cv_id." and tbl_social_spyder_graph_request_data.process_type = '".$process_type."'";
                          $chk_cvs_social_media_data_processing_complete_qry_res = $conn->query($chk_cvs_social_media_data_processing_complete_qry);
                          $chk_cvs_social_media_data_processing_complete_qry_res_row = $chk_cvs_social_media_data_processing_complete_qry_res->fetch_assoc();
                          if($chk_cvs_social_media_data_processing_complete_qry_res_row['last_processed_count'] == $chk_cvs_social_media_data_processing_complete_qry_res_row['down_count'])
                          {
                              array_push($cvs_social_media_data_processing_complete_arr,$process_type);
                          }
                        }

                        if(count($cvs_social_media_data_processing_complete_arr) == count($process_type_arr))
                        {
                          if($conn->query("UPDATE `tbl_social_media_sync_process_data` SET `status`= 1 WHERE ".$cv_id))
                          {
                            error_log("Status updated successfully in social media sync process table for cv".$cv_id);
                            if($industry_id != 0)
                            {
                              //get_industry_social_media_graph_avg_data($conn,$cv_id,$cv_year,$industry_id,$sub_industry_id);
                                $chk_ind_cv_qry = "SELECT * FROM `tbl_ind_social_media_process_data` WHERE `industry_id` = '".$industry_id."' and `cv_id` = '".$cv_id."' and year = '".$cv_year."'";
                                $chk_ind_cv_qry_res = $conn->query($chk_ind_cv_qry); 

                                if($chk_ind_cv_qry_res->num_rows>0)
                                {
                                    $add_ind_to_process_queue_qry = "UPDATE `tbl_ind_social_media_process_data` SET `status` = 0 WHERE `industry_id` = '".$industry_id."' and `cv_id` = '".$cv_id."' and year = '".$cv_year."'";
                                }
                                else
                                {
                                    $add_ind_to_process_queue_qry = "INSERT INTO `tbl_ind_social_media_process_data`(`industry_id`, `cv_id`, `year`, `status`) VALUES (".$industry_id.",".$cv_id.",'".$cv_year."', 0)";
                                }

                              if($conn->query($add_ind_to_process_queue_qry) === TRUE)
                              {
                                error_log("Industry ".$industry_id." is successfully added to process queue.");
                                if($sub_industry_id != 0)
                                {
                                  //get_sub_industry_social_media_graph_avg_data($conn,$cv_id,$cv_year,$industry_id,$sub_industry_id);
                                    $chk_sind_cv_qry = "SELECT * FROM `tbl_sind_social_media_process_data` WHERE `sub_industry_id` = '".$sub_industry_id."' and `cv_id` = '".$cv_id."' and year = '".$cv_year."'";
                                    $chk_sind_cv_qry_res = $conn->query($chk_sind_cv_qry); 

                                    if($chk_sind_cv_qry_res->num_rows>0)
                                    {
                                        $add_sind_to_process_queue_qry = "UPDATE `tbl_sind_social_media_process_data` SET `status` = 0 WHERE `sub_industry_id` = '".$sub_industry_id."' and `cv_id` = '".$cv_id."' and year = '".$cv_year."'";
                                    }
                                    else
                                    {
                                        $add_sind_to_process_queue_qry = "INSERT INTO `tbl_sind_social_media_process_data`(`sub_industry_id`, `cv_id`, `year`, `status`) VALUES (".$sub_industry_id.",".$cv_id.",'".$cv_year."', 0)";
                                    }
                                  if($conn->query($add_sind_to_process_queue_qry) === TRUE)
                                  {
                                    error_log("Sub Industry ".$sub_industry_id." is successfully added to process queue.");
                                    
                                  }
                                  else
                                  {
                                    error_log("Error occured while adding Sub Industry ".$sub_industry_id." to process queue.");
                                  }
                                }
                              }
                              else
                              {
                                error_log("Error occured while adding Industry ".$industry_id." to process queue.");
                              }
                            }
                          }
                          else
                          {
                            error_log("Error occured while updating status in social media sync process table for cv".$cv_id);
                          }
                        }
                      }
                    }
                  }
                }
                else
                {
                  error_log("Processed count id is greater than down count");
                }
              }
            }
            else
            {
              error_log("Error occured while updating status in meta data table for meta data id".$meta_tbl_id);
            }
          }
        }
      }
    }

    error_log("Getting asset result for Asset CS ID ".$get_asset_id_to_fetch_asset_data_qry_res_row['cs_asset_id']." ended.");
    error_log("***************************************************************************************************************".$a_counter);
    $a_counter++;
  }
}
else
{
  error_log("No asset pending of twitter to fetch asset result from central system.");
}


error_log("Ended at");
error_log(date('Y/m/d h:i:s', time()));
error_log("==========================================================================================================================================");
?>
