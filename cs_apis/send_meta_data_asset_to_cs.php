<?php
// log started
$file = "logs/send_meta_data_asset_to_cs/send_meta_data_asset_to_cs_log_".date('Y-m-d').".log";
if(!is_file($file)){
    //Some simple example content.
    $contents = '';
    //Save our content to the file.
    file_put_contents("logs/send_meta_data_asset_to_cs/send_meta_data_asset_to_cs_log_".date('Y-m-d').".log", $contents);
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

$mydirectory = 'D:/SonicCV/python_script/cron_project/download/instagram_mp3/zip';

$dircontents = scandir($mydirectory);

foreach ($dircontents as $file) {
    $extension = pathinfo($file, PATHINFO_EXTENSION);
    if ($extension == 'zip') {
        echo $file;
        $folder_name = str_replace(".zip","",$file);
        
        if (file_exists("D:/SonicCV/python_script/cron_project/download/instagram_mp3/".$folder_name)) 
        {
            echo "The file $folder_name exists";
        }
        else 
        {
            echo "The file $folder_name does not exists";
            mkdir("D:/SonicCV/python_script/cron_project/download/instagram_mp3/".$folder_name);
        }
        $zip = new ZipArchive;
        $res = $zip->open("D:/SonicCV/python_script/cron_project/download/instagram_mp3/zip/".$file);
        if ($res === TRUE) {
          $zip->extractTo('D:/SonicCV/python_script/cron_project/download/instagram_mp3/'.$folder_name);
          $zip->close();
          unlink("D:/SonicCV/python_script/cron_project/download/instagram_mp3/zip/".$file);
          error_log("Content of ".$file." is extracted into ".$folder_name." successfully and ".$file." is deleted successfully");
        } else {
          error_log("Error occured while extarction of ".$file);
        }
    }
}

try{
    $cd = date('Y/m/d h:i:s a', time());

    $get_cv_ids_qry = "select * from tbl_social_media_sync_process_data where is_active = 0 AND (yt = 1 OR ig = 1 OR tt = 1 OR twt = 1) order by id asc";
    // $get_cv_ids_qry = "select tbl_social_media_sync_process_data.* from tbl_social_media_sync_process_data join tbl_social_spyder_graph_meta_data on tbl_social_spyder_graph_meta_data.cv_id = tbl_social_media_sync_process_data.cv_id where tbl_social_media_sync_process_data.is_active = 0 and tbl_social_spyder_graph_meta_data.is_active = 0 and tbl_social_spyder_graph_meta_data.cs_status = 0 GROUP by tbl_social_media_sync_process_data.cv_id order by tbl_social_media_sync_process_data.id desc";
    // $get_cv_ids_qry = "select * from tbl_social_media_sync_process_data where is_active = 0 AND (yt = 1 OR ig = 1 OR tt = 1 OR twt = 1) and cv_id= 1943";
    
    $get_cv_ids_qry_res = $conn->query($get_cv_ids_qry);
    $industry_wise_cv_id_array = array();
    
    while($get_cv_ids_qry_res_row = $get_cv_ids_qry_res->fetch_assoc()) {
        
        $process_type_array = ['youtube', 'instagram', 'tiktok', 'twitter'];
        foreach($process_type_array as $process_type)
        {
            switch($process_type)
            {
                case 'youtube':
                    $p_type = "yt";
                    break;
                case 'instagram':
                    $p_type = "ig";                             
                    break;
                case 'tiktok':
                    $p_type = "tt";
                    break;
                case 'twitter':
                    $p_type = "twt";
                    break;
            }

            // $get_process_sync_stats_qry = "select * from tbl_social_media_sync_process_data where cv_id = ".$get_cv_ids_qry_res_row['cv_id']." and is_active = 0 and ".$p_type." = 1";
            $get_process_sync_stats_qry = "select tbl_social_media_sync_process_data.* from tbl_social_media_sync_process_data join tbl_social_spyder_graph_meta_data on tbl_social_spyder_graph_meta_data.cv_id = tbl_social_media_sync_process_data.cv_id where tbl_social_media_sync_process_data.cv_id = ".$get_cv_ids_qry_res_row['cv_id']." and tbl_social_media_sync_process_data.is_active = 0 and tbl_social_spyder_graph_meta_data.is_active = 0 and tbl_social_spyder_graph_meta_data.cs_status = 0 and tbl_social_spyder_graph_meta_data.process_type = '".$process_type."'  GROUP by tbl_social_media_sync_process_data.cv_id";
            
            $get_process_sync_stats_qry_res = $conn->query($get_process_sync_stats_qry);

            if($get_process_sync_stats_qry_res->num_rows > 0)
            {
                $chnl_cnt_qry = "SELECT chn_id FROM `tbl_social_spyder_graph_request_data` WHERE `cv_id` = ".$get_cv_ids_qry_res_row['cv_id']." and `is_active` = 0 and `chn_notfound` = 0 and `process_type` = '".$process_type."'";

                $chnl_cnt_qry_res = $conn->query($chnl_cnt_qry);
                $channel_count = $chnl_cnt_qry_res->num_rows;
                echo "Total ".$process_type." channel count of CV ".$get_cv_ids_qry_res_row['cv_id']." is ".$channel_count."<br>";
                error_log("Total ".$process_type." channel count of CV ".$get_cv_ids_qry_res_row['cv_id']." is ".$channel_count);

                if($channel_count != 0)
                {
                    if($channel_count > 1)
                    {
                        if($process_type == 'youtube')
                        {
                            $upload_count = round($dbcon['youtube_video_upload_limit_count'] / $channel_count);
                        }
                        else
                        {
                            $upload_count = round($dbcon['video_upload_limit_count'] / $channel_count);
                        }
                        echo "Upload count for ".$process_type." channels ".$upload_count."<br>";
                        error_log("Upload count for ".$process_type." channels ".$upload_count);

                        $chnl_cntnt_upld_range_array = [];
                        $chnl_cntnt_upld_range = [];
                        $available_chnl_id_arr = [];
                        while ($chnl_cnt_qry_res_row = $chnl_cnt_qry_res->fetch_assoc())
                        {
                            $get_first_chnl_cntnt_id_qry = "SELECT * FROM ( SELECT * FROM tbl_social_spyder_graph_meta_data WHERE cv_id=".$get_cv_ids_qry_res_row['cv_id']." and is_active=0 and chn_id ='".$chnl_cnt_qry_res_row['chn_id']."' LIMIT ".$upload_count.") sub ORDER BY id ASC limit 1";
                            // echo $get_first_chnl_cntnt_id_qry."<br>";
                            $get_first_chnl_cntnt_id_qry_res = $conn->query($get_first_chnl_cntnt_id_qry);
                            $get_first_chnl_cntnt_id_qry_res_data = $get_first_chnl_cntnt_id_qry_res->fetch_assoc();
                            if($get_first_chnl_cntnt_id_qry_res->num_rows>0)
                            {
                                $first_id = $get_first_chnl_cntnt_id_qry_res_data['id'];
                                echo "first_id: ".$first_id."<br>"; 
                                error_log("first_id: ".$first_id); 
                            }
                                                  

                            $get_last_chnl_cntnt_id_qry = "SELECT * FROM ( SELECT * FROM tbl_social_spyder_graph_meta_data WHERE cv_id=".$get_cv_ids_qry_res_row['cv_id']." and is_active=0 and chn_id ='".$chnl_cnt_qry_res_row['chn_id']."' LIMIT ".$upload_count.") sub ORDER BY id DESC limit 1";
                            // echo $get_last_chnl_cntnt_id_qry."<br>";
                            $get_last_chnl_cntnt_id_qry_res = $conn->query($get_last_chnl_cntnt_id_qry);
                            $get_last_chnl_cntnt_id_qry_res_data = $get_last_chnl_cntnt_id_qry_res->fetch_assoc();
                            if($get_last_chnl_cntnt_id_qry_res->num_rows>0)
                            {
                                $last_id = $get_last_chnl_cntnt_id_qry_res_data['id'];
                                echo "last_id: ".$last_id."<br>";
                                error_log("last_id: ".$last_id);
                            }

                            $get_chnl_cntnt_total_count_qry = "SELECT * FROM tbl_social_spyder_graph_meta_data WHERE cv_id = ".$get_cv_ids_qry_res_row['cv_id']." and chn_id ='".$chnl_cnt_qry_res_row['chn_id']."' and is_active=0 and id BETWEEN ".$first_id." and ".$last_id;
                            // echo $get_chnl_cntnt_total_count_qry."<br>";
                            $get_chnl_cntnt_total_count_qry_res = $conn->query($get_chnl_cntnt_total_count_qry);
                            $chnl_cntnt_count = $get_chnl_cntnt_total_count_qry_res->num_rows;
                            echo "chnl_cntnt_count: ".$chnl_cntnt_count."<br>"; 
                            error_log("chnl_cntnt_count: ".$chnl_cntnt_count);              

                            if($first_id != '' && $first_id != null && $last_id != '' && $last_id != null)
                            {
                                $chnl_cntnt_upld_range["start_id"] = $first_id;
                                $chnl_cntnt_upld_range["end_id"] = $last_id;
                                $chnl_cntnt_upld_range["total_count"] = $chnl_cntnt_count;
                                $chnl_cntnt_upld_range_array[$chnl_cnt_qry_res_row['chn_id']] = $chnl_cntnt_upld_range;
                                array_push($available_chnl_id_arr,$chnl_cnt_qry_res_row['chn_id']);
                            }
                        }

                    }
                    else
                    {
                        if($process_type == 'youtube')
                        {
                            $upload_count = $dbcon['youtube_video_upload_limit_count'];
                        }
                        else
                        {
                            $upload_count = $dbcon['video_upload_limit_count'];
                        }                               
                        echo "Upload count for Singel ".$process_type." channel ".$upload_count."<br>";
                        error_log("Upload count for Singel ".$process_type." channel ".$upload_count);

                        $chnl_cntnt_upld_range_array = [];
                        $chnl_cntnt_upld_range = [];
                        $available_chnl_id_arr = [];

                        while ($chnl_cnt_qry_res_row = $chnl_cnt_qry_res->fetch_assoc())
                        {
                            $get_first_chnl_cntnt_id_qry = "SELECT * FROM ( SELECT * FROM tbl_social_spyder_graph_meta_data WHERE cv_id=".$get_cv_ids_qry_res_row['cv_id']." and is_active=0 and chn_id ='".$chnl_cnt_qry_res_row['chn_id']."' LIMIT ".$upload_count.") sub ORDER BY id ASC limit 1";
                            // echo $get_first_chnl_cntnt_id_qry."<br>";
                            $get_first_chnl_cntnt_id_qry_res = $conn->query($get_first_chnl_cntnt_id_qry);
                            $get_first_chnl_cntnt_id_qry_res_data = $get_first_chnl_cntnt_id_qry_res->fetch_assoc();
                            if($get_first_chnl_cntnt_id_qry_res->num_rows>0)
                            {
                                $first_id = $get_first_chnl_cntnt_id_qry_res_data['id'];
                                echo "first_id: ".$first_id."<br>";
                                error_log("first_id: ".$first_id);
                            }

                            $get_last_chnl_cntnt_id_qry = "SELECT * FROM ( SELECT * FROM tbl_social_spyder_graph_meta_data WHERE cv_id=".$get_cv_ids_qry_res_row['cv_id']." and is_active=0 and chn_id ='".$chnl_cnt_qry_res_row['chn_id']."' LIMIT ".$upload_count.") sub ORDER BY id DESC limit 1";
                            // echo $get_last_chnl_cntnt_id_qry."<br>";
                            $get_last_chnl_cntnt_id_qry_res = $conn->query($get_last_chnl_cntnt_id_qry);
                            $get_last_chnl_cntnt_id_qry_res_data = $get_last_chnl_cntnt_id_qry_res->fetch_assoc();
                            if($get_last_chnl_cntnt_id_qry_res->num_rows>0)
                            {
                                $last_id = $get_last_chnl_cntnt_id_qry_res_data['id'];
                                echo "last_id: ".$last_id."<br>";
                                error_log("last_id: ".$last_id);
                            }

                            $get_chnl_cntnt_total_count_qry = "SELECT * FROM tbl_social_spyder_graph_meta_data WHERE cv_id = ".$get_cv_ids_qry_res_row['cv_id']." and chn_id ='".$chnl_cnt_qry_res_row['chn_id']."' and is_active=0 and id BETWEEN ".$first_id." and ".$last_id;
                            // echo $get_chnl_cntnt_total_count_qry."<br>";
                            $get_chnl_cntnt_total_count_qry_res = $conn->query($get_chnl_cntnt_total_count_qry);
                            $chnl_cntnt_count = $get_chnl_cntnt_total_count_qry_res->num_rows;
                            echo "chnl_cntnt_count: ".$chnl_cntnt_count."<br>";                 
                            error_log("chnl_cntnt_count: ".$chnl_cntnt_count);  

                            if($first_id != '' && $first_id != null && $last_id != '' && $last_id != null)
                            {
                                $chnl_cntnt_upld_range["start_id"] = $first_id;
                                $chnl_cntnt_upld_range["end_id"] = $last_id;
                                $chnl_cntnt_upld_range["total_count"] = $chnl_cntnt_count;
                                $chnl_cntnt_upld_range_array[$chnl_cnt_qry_res_row['chn_id']] = $chnl_cntnt_upld_range;
                                array_push($available_chnl_id_arr,$chnl_cnt_qry_res_row['chn_id']);
                            }
                        }
                    }


                    echo "available_chnl_id_arr: <br>";
                    print_r($available_chnl_id_arr);
                    error_log("===========================================================================================");
                    error_log("available_chnl_id_arr: ".implode(",",$available_chnl_id_arr));
                    error_log("===========================================================================================");
                    echo "<br>===========================================================================================<br><br>";

                    if(!empty($available_chnl_id_arr) && count($available_chnl_id_arr)>0)
                    {
                        $transaction_token = check_and_get_access_token($conn);
                        foreach ($available_chnl_id_arr as $chnl_id)
                        {
                            echo $chnl_cntnt_upld_range_array[$chnl_id]['start_id']."<br>".$chnl_cntnt_upld_range_array[$chnl_id]['end_id']."<br>";
                            error_log("------------------------------------------------------------------------------------------------------");
                            error_log("Upload tracks of CV ".$get_cv_ids_qry_res_row['cv_id']." and channel ".$chnl_id." between ".$chnl_cntnt_upld_range_array[$chnl_id]['start_id']." and ".$chnl_cntnt_upld_range_array[$chnl_id]['end_id']);
                            error_log("------------------------------------------------------------------------------------------------------");

                            $sql = "select tbl_social_spyder_graph_meta_data.*,tbl_asset_types.asset_upload_at as 'asset_upload_at' from tbl_social_spyder_graph_meta_data LEFT JOIN tbl_asset_types on tbl_asset_types.asset_type_id  = tbl_social_spyder_graph_meta_data.asset_type_id where cv_id = ".$get_cv_ids_qry_res_row['cv_id']." and chn_id = '".$chnl_id."' and id between ".$chnl_cntnt_upld_range_array[$chnl_id]['start_id']." and ".$chnl_cntnt_upld_range_array[$chnl_id]['end_id']." and tbl_social_spyder_graph_meta_data.status=0 and tbl_social_spyder_graph_meta_data.cs_status=0 AND tbl_social_spyder_graph_meta_data.is_active = 0";          
                            //$sql = "select * from tbl_social_spyder_graph_meta_data where cv_id IN (989) and status=0 AND is_active = 0";
                             
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {  
                              while($row = $result->fetch_assoc()) {
                                //************************************
                                $asset_name = $row['otitle'];
                                $meta_data_id = $row['id'];
                                $meta_data_status = $row['status'];
                                $asset_upload_at = $row['asset_upload_at'];
                                $asset_type_id = $row['asset_type_id'];
                                $track_id = ($row['track_id'] != null && $row['track_id'] != 'null' && $row['track_id'] != '') ? $row['track_id'] : 0;
                                $path = $row['path'];
                                $call_from = "media_meta_data";

                                error_log("send_asset_content_to_central_system=>".$path);

                                if(file_exists($path) === true)
                                {
                                    send_asset_content_to_central_system($call_from,$conn,$asset_name,$meta_data_id,$transaction_token,$asset_type_id,$asset_upload_at,$meta_data_status,$track_id,$path);
                                }
                                else
                                {
                                    error_log("send_asset_content_to_central_system=> file not available at the moment to sent at central system ".$path);
                                }
                                //************************************
                              }

                              $updt_strt_and_end_id_qry = "UPDATE `tbl_social_spyder_graph_request_data` SET `uploaded_start_id` = '".$chnl_cntnt_upld_range_array[$chnl_id]['start_id']."' , `uploaded_end_id` = '".$chnl_cntnt_upld_range_array[$chnl_id]['end_id']."', `down_count` = '".$chnl_cntnt_upld_range_array[$chnl_id]['total_count']."' WHERE `cv_id` = ".$get_cv_ids_qry_res_row['cv_id']." and chn_id = '".$chnl_id."'";
                              $conn->query($updt_strt_and_end_id_qry);
                              
                            }
                            else
                            {
                                error_log("page : [cyanite_php_clone] : function [upload_mp3s_on_cyanite] : No content available for upload for cv ".$get_cv_ids_qry_res_row['cv_id']." and channel ".$chnl_id." between ".$chnl_cntnt_upld_range_array[$chnl_id]['start_id']." and ".$chnl_cntnt_upld_range_array[$chnl_id]['end_id']);
                            }
                        }
                    }
                }
            }
            
        }
    }   
}
catch(Exception $e)
{           
    error_log("page : [cyanite_php_clone] : function [upload_mp3s_on_cyanite] : error : ".$e->getMessage());
    $sonic_functions->trigger_log_email("cyanite_php_clone","upload_mp3s_on_cyanite",$e->getMessage());
}

/////////////////////////////////////////////////////////////////

error_log("****************************************************************************************************************************");

error_log("Ended at");
error_log(date('Y/m/d h:i:s', time()));
error_log("==========================================================================================================================================");
?>
