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

    $get_cv_id_qrey = "SELECT DISTINCT(cv_id) FROM `tbl_social_spyder_graph_meta_data` WHERE is_active = 1 and skip_for_split = 1 and skip_percentage < 90";

    $get_cv_id_qrey_result = $conn->query($get_cv_id_qrey);
    $get_cv_id_qrey_result_num_row = $get_cv_id_qrey_result->num_rows;
    if($get_cv_id_qrey_result_num_row > 0)
    {
        while($get_cv_id_qrey_result_row = $get_cv_id_qrey_result->fetch_assoc()){

            $select_query = "SELECT * FROM `tbl_social_spyder_graph_meta_data` WHERE is_active = 1 and skip_for_split = 1 and skip_percentage < 90 and cv_id =".$get_cv_id_qrey_result_row['cv_id'];

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
                        $asset_processed_cyanite_data_id = 0;
                        $tbl_assets_id = $row['id'];
                        

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
                    error_log("no asset found which has voice and silence presence > 50 and < 90");
                }
            }
            catch(Exception $e){
                error_log("Error : ".$e->getMessage());
            }

        }
    }
    else
    {
        error_log("no cv found which assets has voice and silence presence > 50 and < 90");
    }

    

    
    error_log("==========================================================================================================================================");
    echo("<br>==========================================================================================================================================");

?>