<?php
    $file = "logs/send_voiceover_degree_asset_to_cs/send_voiceover_degree_asset_to_cs_log_".date('Y-m-d').".log";
    if(!is_file($file)){
    $contents = '';
    file_put_contents("logs/send_voiceover_degree_asset_to_cs/send_voiceover_degree_asset_to_cs_log_".date('Y-m-d').".log", $contents);
    }
    $error_message = "This is an error message!";
    $log_file = $file;
    ini_set("log_errors", TRUE); 
    ini_set('error_log', $log_file);
    include "functions.php";
    include "GetMp3Duration.php";
    $dbcon = include('connection.php');
    $conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);
    error_log("==========================================================================================================================================");
    echo("==========================================================================================================================================<br>");
    /*$select_query = "SELECT tbl_asset_processed_cyanite_data.apcd,tbl_assets.id,tbl_social_spyder_graph_meta_data.otitle,tbl_social_spyder_graph_meta_data.path
    FROM `tbl_asset_processed_cyanite_data`
    LEFT JOIN tbl_assets ON tbl_asset_processed_cyanite_data.asset_id = tbl_assets.cs_asset_id
    LEFT JOIN tbl_social_spyder_graph_meta_data on tbl_assets.id = tbl_social_spyder_graph_meta_data.asset_id
    WHERE tbl_asset_processed_cyanite_data.voiceover_degree > 0.4 and tbl_asset_processed_cyanite_data.is_active = 0 and tbl_assets.is_active = 0 and tbl_social_spyder_graph_meta_data.is_active = 0 and tbl_asset_processed_cyanite_data.cs_status = 0 limit 1";*/

    /*$select_query = "SELECT tbl_asset_processed_cyanite_data.apcd,tbl_assets.id as `tbl_assets_id`,tbl_social_spyder_graph_meta_data.otitle,tbl_social_spyder_graph_meta_data.path
    FROM tbl_asset_processed_cyanite_data
    LEFT JOIN tbl_assets ON tbl_asset_processed_cyanite_data.asset_id = tbl_assets.cs_asset_id
    LEFT JOIN tbl_social_spyder_graph_meta_data on tbl_assets.id = tbl_social_spyder_graph_meta_data.asset_id
    LEFT JOIN tbl_cvs on tbl_social_spyder_graph_meta_data.cv_id = tbl_cvs.cv_id
    WHERE tbl_asset_processed_cyanite_data.voiceover_degree > 0.4 and tbl_asset_processed_cyanite_data.is_active = 0 and tbl_assets.is_active = 0 and tbl_social_spyder_graph_meta_data.is_active = 0 and tbl_asset_processed_cyanite_data.cs_status = 0 
    and tbl_cvs.cv_id in (1609) and tbl_cvs.status = 1 and tbl_cvs.is_active = 0 and tbl_cvs.cv_year='2023' limit 1";*/

    $select_query = "SELECT tbl_asset_processed_cyanite_data.apcd,tbl_assets.id as `tbl_assets_id`,tbl_social_spyder_graph_meta_data.otitle,tbl_social_spyder_graph_meta_data.path
    FROM tbl_asset_processed_cyanite_data
    LEFT JOIN tbl_assets ON tbl_asset_processed_cyanite_data.asset_id = tbl_assets.cs_asset_id
    LEFT JOIN tbl_social_spyder_graph_meta_data on tbl_assets.id = tbl_social_spyder_graph_meta_data.asset_id
    LEFT JOIN tbl_cvs on tbl_social_spyder_graph_meta_data.cv_id = tbl_cvs.cv_id
    WHERE tbl_asset_processed_cyanite_data.voiceover_degree > 0.4 and tbl_asset_processed_cyanite_data.is_active = 0 and tbl_assets.is_active = 0 and tbl_social_spyder_graph_meta_data.is_active = 0 and tbl_asset_processed_cyanite_data.cs_status = 0 
    and tbl_cvs.cv_id in (1474,1470,1441,1861,1799,1858,1681,1800,1662,1742) and tbl_cvs.status = 1 and tbl_cvs.is_active = 0 limit 40";
    $result = $conn->query($select_query);
    $num_row = $result->num_rows;

    echo "num rows count == ".$num_row;
    error_log("num rows count == ".$num_row);
    try{
        if($num_row > 0){

            $transaction_token = check_and_get_access_token($conn);
            while($row = $result->fetch_assoc()){
                $path = $row['path'];
                $asset_name = $row['otitle'];
                $asset_processed_cyanite_data_id = $row['apcd'];
                $tbl_assets_id = $row['tbl_assets_id'];
                

                if(file_exists($path)){
                    $mp3file = new GetMp3Duration($path);
                    $duration = $mp3file->getDuration();
                    error_log("\n file duration = ".$duration);
                    echo "<br> file duration = ".$duration;

                    if($duration > 60){
                        $duration_half = $duration/2;
                        $start_time = $duration_half-30;
                        $end_time = $duration_half+30;
                        $trim = 1;
                    }else{
                        $trim = 0;
                        $start_time = 0;
                        $end_time = 0;
                    }
                    
                    send_voiceover_degree_asset_to_cs($conn,$asset_processed_cyanite_data_id,$tbl_assets_id,$asset_name,$trim,$start_time,$end_time,$path,$transaction_token);
                }else{
                    error_log("file not exist with file name ".$asset_name." at specified path ".$path);
                    echo "file not exist with file name ".$asset_name." at specified path ".$path;
                }
            }


        }else{
            error_log("no asset found as per the voiceover_degree > 0.4");
        }
    }
    catch(Exception $e){
        error_log("Error : ".$e->getMessage());
    }

    
    error_log("==========================================================================================================================================");
    echo("<br>==========================================================================================================================================");





?>