<?php

require_once('sonic/sonic_functions.php');
require_once('sonic/db_dump.php');
require_once('cynite_apis.php');

error_reporting(E_ALL);

class cyanite_php_clone{
	
	function create_crate($name){
		try{
			$return_data=null;

			$cynite_api = new CyaniteAI();
			$crate_json = json_decode($cynite_api->Creating_crate($name));

			if($crate_json->data->crateCreate->__typename=="CrateCreateSuccess"){			
				
				$return_data = $crate_json->data->crateCreate->id;
				error_log("New generated crate id : ".$return_data);
			}
			else{
				
				$sonic_functions = new sonic_functions();
				error_log("page : [cyanite_php_clone] : function [create_crate] : error : ".$crate_json->data->crateCreate->message);				
				
				$sonic_functions->trigger_log_email("creating crate","cyanite_php_clone -- create_crate()",$crate_json->data->crateCreate->message);
				$return_data = 0;
			}

			return $return_data;
		}
		catch(Exception $e)
		{
			$sonic_functions = new sonic_functions();
			error_log("page : [cyanite_php_clone] : function [create_crate] : error : ".$e->getMessage());
			$sonic_functions->trigger_log_email("cyanite_php_clone","create_crate",$e->getMessage());
		}
	}

	function upload_mp3s_on_cyanite($sent_from,$asset_upload_link,$asset_id,$asset_name,$cyanite_crate_name,$cyanite_crate_id,$cyanite_key,$crate_not_requiredfor_uids)
	{
		$sonic_functions = new sonic_functions();
		try
		{
			$cd = date('Y/m/d h:i:s a', time());

			$cynite_api = new CyaniteAI();
		  	$db_dump = new db_dump();
			// Upload request
			
			error_log("**********Next Track**********");

			$RequestUpload_json = json_decode($cynite_api->RequestUpload($cyanite_key));
			if($RequestUpload_json != "0"){
				// echo "<br>upload start".json_decode($RequestUpload_json); // json_encode(array("status"=>500, "data"=>"Something went wrong while uploading request","Description"=>"File upload is pending and Library track creation is pending."));

				$var_RequestUpload_id = $RequestUpload_json->data->fileUploadRequest->id;
				$var_RequestUpload_url = $RequestUpload_json->data->fileUploadRequest->uploadUrl;
				
				// upload mp3

				echo "var_RequestUpload_url".$var_RequestUpload_url."<br>";
				// echo "path".$file_path."<br>";
				
				$fileupload_status = $cynite_api->FileUpload($var_RequestUpload_url , $asset_upload_link, $cyanite_key); //json_encode(array("status"=>500, "data"=>"Something went wrong while uploading file","Description"=>"File upload is pending and Library track creation is pending."));
				
				if($fileupload_status!="0"){
					//create ltrack id 
					$libraryTrack_json= json_decode($cynite_api->create_LibraryTrack($var_RequestUpload_id , $asset_name, $cyanite_key));

					if($libraryTrack_json != "0"){
						$libraryTrack_id = $libraryTrack_json->data->libraryTrackCreate->createdLibraryTrack->id;
						error_log("libraryTrack_id:".$libraryTrack_id);

						// dump track into crate
						if(in_array($sent_from,$crate_not_requiredfor_uids))
						{
							$response = $cynite_api -> add_libraryTrack_in_crate($libraryTrack_id, null, $cyanite_key);
						}
						else
						{
							$response = $cynite_api -> add_libraryTrack_in_crate($libraryTrack_id, $cyanite_crate_id, $cyanite_key);
						}

						if($response!="0"){
							// update status after upload
							$status = $db_dump->update_trackid_and_status($libraryTrack_id, $asset_id);
						}
					}
				}
			}
			error_log("sleep for 1 second");
			sleep(1);		
						
		}
		catch(Exception $e)
		{			
			error_log("page : [cyanite_php_clone] : function [upload_mp3s_on_cyanite] : error : ".$e->getMessage());
			$sonic_functions->trigger_log_email("cyanite_php_clone","upload_mp3s_on_cyanite",$e->getMessage());
		}
	}

	function fetch_analysis($id){
		error_log("fetch_analysis () called");
		$sonic_functions = new sonic_functions();

		try{
			$db_dump = new db_dump();
			$dbcon = include('../config.php');
			$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);	

			if($id == 1)
			{
				$get_asset_data_qry = "SELECT tbl_assets.c_id, tbl_cyanite.track_id, tbl_tool_users.cyanite_key FROM `tbl_assets` join `tbl_cyanite` on tbl_assets.c_id = tbl_cyanite.c_id join `tbl_tool_users` on tbl_assets.sent_from = tbl_tool_users.uid WHERE tbl_assets.is_active = 0 AND tbl_assets.c_status = 1 AND tbl_cyanite.is_active = 0 and tbl_tool_users.uid = 1 order by tbl_assets.c_date ASC";
			}
			else
			{
				$get_asset_data_qry = "SELECT tbl_assets.c_id, tbl_cyanite.track_id, tbl_tool_users.cyanite_key FROM `tbl_assets` join `tbl_cyanite` on tbl_assets.c_id = tbl_cyanite.c_id join `tbl_tool_users` on tbl_assets.sent_from = tbl_tool_users.uid WHERE tbl_assets.is_active = 0 AND tbl_assets.c_status = 1 AND tbl_cyanite.is_active = 0 and tbl_tool_users.uid != 1 order by tbl_assets.c_date ASC";
			}

			$get_asset_data_qry_res = $conn->query($get_asset_data_qry);

			if($get_asset_data_qry_res->num_rows > 0)
			{
			  while($get_asset_data_qry_res_row = $get_asset_data_qry_res->fetch_assoc())
			  {
			  	if($get_asset_data_qry_res_row['cyanite_key'] == null || $get_asset_data_qry_res_row['cyanite_key'] == "")
				{
					$cyanite_key = 0;
				}
				else
				{
					$cyanite_key = $get_asset_data_qry_res_row['cyanite_key'];
					$db_dump->fetch_and_dump_analised_record($get_asset_data_qry_res_row["track_id"], $get_asset_data_qry_res_row["c_id"],$cyanite_key);		
				}

			  	error_log("sleep for 1 second");
				sleep(1);
			  }
			}
			else
			{
			  error_log("No asstes found to get analysied data");
			}			
		}
		catch(Exception $e)
		{			
			error_log("page : [cyanite_php_clone] : function [fetch_analysis] : error : ".$e->getMessage());
			$sonic_functions->trigger_log_email("cyanite_php_clone","fetch_analysis",$e->getMessage());
		}
	}

	function store_mood_genere(){
		error_log("store_mood_genere () called");
		$sonic_functions = new sonic_functions();

		try{
			$db_dump = new db_dump();
			$dbcon = include('../config.php');
			$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);	

			$PType= array("youtube", "instagram", "tiktok", "twitter");
			$Vcounts = array();
			$brand_Ids = array();
			$brand_IdsPType = array();

			for($i=0;$i<count($PType);$i++){

				$sql1 = "SELECT distinct cv_id FROM `tbl_social_spyder_graph_meta_data` WHERE `process_type`='".$PType[$i]."' and (status = 2) and `is_active`=0";
				$result = $conn->query($sql1);

				//echo "<br>sql 1 : ".$sql1."<br>";

				if ($result->num_rows > 0) {  
				  	while($row = $result->fetch_assoc()) {			  		

				  		$cv_id = $row["cv_id"];
				  		
				  		$sql2 = "select count(id) as count from tbl_social_spyder_graph_request_data where cv_id=".$cv_id." and (status !=2) and `process_type`='".$PType[$i]."' and `is_active`=0 and chn_notfound=0"; // need to test

				  		//echo "<br>sql 2 : ".$sql2."<br>";

				  		$result2 = $conn->query($sql2);
						
						if ($result2->num_rows > 0) {  
				  			while($row2 = $result2->fetch_assoc()) {
				  				//echo "<br>count : ".$row2["count"]."<br>";
								if($row2["count"]==0){			  					
									array_push($brand_Ids, $cv_id);
									array_push($brand_IdsPType,$PType[$i]);
								}
				  			}
				  		}
				  		else{  }
				 	}
				 }			 
			}

			//print_r($brand_Ids);

			for($i=0;$i<count($brand_Ids);$i++){

				//$sql = "select id,cv_id,(select count(id) from tbl_social_spyder_graph_meta_data where cv_id=a.cv_id and (status < 2)  AND is_active = 0) as pending from tbl_social_spyder_graph_meta_data a where is_active = 0 group by a.cv_id,id";

				// 2 means start analysis

				$get_chnl_start_n_end_cntnt_id_qry = "SELECT * FROM `tbl_social_spyder_graph_request_data` WHERE `cv_id` = ".$brand_Ids[$i]." and is_active = 0 and chn_notfound = 0 and `new_status` = 2 and `process_type` = '".$brand_IdsPType[$i]."'";
				$get_chnl_start_n_end_cntnt_id_qry_res = $conn->query($get_chnl_start_n_end_cntnt_id_qry);
				if ($get_chnl_start_n_end_cntnt_id_qry_res->num_rows > 0)
				{
					$chnl_ids_arr = [];
					$sql_pendings = "SELECT SUM(pending) as pending FROM (";
					$get_track_ids_qry = '';
					while($get_chnl_start_n_end_cntnt_id_qry_res_row = $get_chnl_start_n_end_cntnt_id_qry_res->fetch_assoc())
					{
						array_push($chnl_ids_arr, "'".$get_chnl_start_n_end_cntnt_id_qry_res_row['chn_id']."'");
						$sql_pendings .= "select count(id) as pending from tbl_social_spyder_graph_meta_data where cv_id=".$brand_Ids[$i]." and (status < 2) and is_active = 0 and id between ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_start_id']." and ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_end_id'];
						$sql_pendings .= " UNION ";

						$get_track_ids_qry .= "select track_id from tbl_social_spyder_graph_meta_data where cv_id=".$brand_Ids[$i]." and is_active = 0 and id between ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_start_id']." and ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_end_id'];
						$get_track_ids_qry .= " UNION ";
					}

					$chnl_ids_str = implode(",", $chnl_ids_arr);
					$final_sql_pendings = rtrim($sql_pendings," UNION ")." ) sp";
					$final_get_track_ids_qry = rtrim($get_track_ids_qry," UNION ");
				
					$pendings_result = $conn->query($final_sql_pendings);

					//echo "<br>final query : ".$sql_pendings;

					if ($pendings_result->num_rows > 0) {  
					  while($pendings_result_row = $pendings_result->fetch_assoc()) {
					  	error_log($pendings_result_row["pending"]." Pendings for upload of ".$brand_Ids[$i]." ".$brand_IdsPType[$i]);
					  	if($pendings_result_row["pending"]==0){
					  		
					  		$get_track_ids_qry_result = $conn->query($final_get_track_ids_qry);
					  		if ($get_track_ids_qry_result->num_rows > 0) {  
					  			$track_id_arr = [];
					  			while($get_track_ids_qry_result_row = $get_track_ids_qry_result->fetch_assoc()) {
					  				array_push($track_id_arr,$get_track_ids_qry_result_row['track_id']);
					  			}
					  			$track_id_str = implode(",", $track_id_arr);
					  			
					  			if(!empty($track_id_arr))
					  			{
					  				error_log("brand_Ids: ".$brand_Ids[$i]."|brand_IdsPType: ".$brand_IdsPType[$i]."|track_id_str: ".$track_id_str."|chnl_ids_str: ".$chnl_ids_str);
					  				$db_dump->extract_mood_and_genere($brand_Ids[$i],$brand_IdsPType[$i],$track_id_str,$chnl_ids_str);
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
			error_log("page : [cyanite_php_clone] : function [store_mood_genere] : error : ".$e->getMessage());
			$sonic_functions->trigger_log_email("cyanite_php_clone","store_mood_genere",$e->getMessage());
		}

		// if not pending then update status 3
	}

	function aggregate_of_aggregate(){
		error_log("aggregate_of_aggregate () called");
		$db_dump = new db_dump();
		$dbcon = include('../config.php');
		$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);	
		
		$get_distinct_cv_ids = "SELECT DISTINCT(cv_id) FROM `tbl_social_spyder_graph_meta_data` where status=4 and is_active=0";
		$get_distinct_cv_ids_result = $conn->query($get_distinct_cv_ids);
		error_log("get_distinct_cv_ids".$get_distinct_cv_ids);

		if ($get_distinct_cv_ids_result->num_rows > 0) {
		  while($get_distinct_cv_ids_result_row = $get_distinct_cv_ids_result->fetch_assoc()) {
				error_log("current brand id".$get_distinct_cv_ids_result_row["cv_id"]);
				$get_chnl_start_n_end_cntnt_id_qry = "SELECT * FROM `tbl_social_spyder_graph_request_data` WHERE `cv_id` = ".$get_distinct_cv_ids_result_row["cv_id"]." and is_active = 0 and chn_notfound = 0 and new_status >= 3";
				$get_chnl_start_n_end_cntnt_id_qry_res = $conn->query($get_chnl_start_n_end_cntnt_id_qry);
				if ($get_chnl_start_n_end_cntnt_id_qry_res->num_rows > 1)
				{
					$chnl_ids_arr = [];
					$get_pending_cv_count = "SELECT SUM(cv_count) as cv_count FROM (";
					$get_last_status_count = '';
					$get_track_ids_qry = '';
					$process_type_arr = [];
					while($get_chnl_start_n_end_cntnt_id_qry_res_row = $get_chnl_start_n_end_cntnt_id_qry_res->fetch_assoc())
					{
						if(!in_array("'".$get_chnl_start_n_end_cntnt_id_qry_res_row['process_type']."'", $process_type_arr))
						{
							array_push($process_type_arr, "'".$get_chnl_start_n_end_cntnt_id_qry_res_row['process_type']."'");	
						}
						array_push($chnl_ids_arr, "'".$get_chnl_start_n_end_cntnt_id_qry_res_row['chn_id']."'");
						$get_pending_cv_count .= "SELECT COUNT(cv_id) as cv_count from tbl_social_spyder_graph_meta_data where cv_id=".$get_distinct_cv_ids_result_row['cv_id']." and status<4 and is_active=0 and id between ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_start_id']." and ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_end_id'];
						$get_pending_cv_count .= " UNION ";

						$get_track_ids_qry .= "SELECT track_id from tbl_social_spyder_graph_meta_data where cv_id=".$get_distinct_cv_ids_result_row['cv_id']." and is_active=0 and id between ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_start_id']." and ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_end_id'];
						$get_track_ids_qry .= " UNION ";

						$get_last_status_count .= "SELECT track_id from tbl_social_spyder_graph_meta_data where cv_id=".$get_distinct_cv_ids_result_row['cv_id']." and status = 6 and is_active=0 and id between ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_start_id']." and ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_end_id'];
						$get_last_status_count .= " UNION ";
					}

					$chnl_ids_str = implode(",", $chnl_ids_arr);
					$final_get_pending_cv_count = rtrim($get_pending_cv_count," UNION ")." ) sp";
					$final_get_track_ids_qry = rtrim($get_track_ids_qry," UNION ");
					$final_get_last_status_count_qry = rtrim($get_last_status_count," UNION ");

					$get_pending_cv_count_result = $conn->query($final_get_pending_cv_count);
					error_log("get_pending_cv_count".$final_get_pending_cv_count);
					if ($get_pending_cv_count_result->num_rows > 0) {
						while($row = $get_pending_cv_count_result->fetch_assoc()) {
							
							if($row["cv_count"]==0){
								$get_track_ids_qry_result = $conn->query($final_get_track_ids_qry);
								$get_last_status_count_qry_result = $conn->query($final_get_last_status_count_qry);
								error_log("Total tracks count :".$get_track_ids_qry_result->num_rows." and Total tracks count who has status 6: ".$get_last_status_count_qry_result->num_rows." of CV: ".$get_distinct_cv_ids_result_row["cv_id"]);
						  		if (count($process_type_arr)>1 && ($get_track_ids_qry_result->num_rows != $get_last_status_count_qry_result->num_rows)) {
									error_log("aggr_of_aggr function is called to generate aggr graph for brand:".$get_distinct_cv_ids_result_row["cv_id"]);
									$track_id_arr = [];
						  			while($get_track_ids_qry_result_row = $get_track_ids_qry_result->fetch_assoc()) {
						  				array_push($track_id_arr,$get_track_ids_qry_result_row['track_id']);
						  			}
						  			$track_id_str = implode(",", $track_id_arr);

						  			if(!empty($track_id_arr))
						  			{
						  				$db_dump->aggr_of_aggr($get_distinct_cv_ids_result_row['cv_id'],$track_id_str,$chnl_ids_arr);
						  			}
						  		}
							}
							else
							{
								error_log("pending_cv_count for brand:".$get_distinct_cv_ids_result_row["cv_id"]." is ".$row["cv_count"]);
							}
						}
					}
				}
			}
		}
	}
	
	function generate_monthwise_data_graphs(){
		error_log("page : [cyanite_php_clone] : function [generate_monthwise_data_graphs] : generate_monthwise_data_graphs () called");
		$db_dump = new db_dump();
		$dbcon = include('../config.php');
		$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);	
		//$db_dump->ins_updt_monthly_graph_data(652);
		$get_cv_id_and_udt_qry = "SELECT cv_id, updated_at FROM `tbl_social_media_sync_process_data` WHERE is_active=0 and (yt>=2 || ig>=2 || tt>=2 || twt>=2)";
		$get_cv_id_and_udt_qry_res = $conn->query($get_cv_id_and_udt_qry);
		//error_log("get_cv_id_and_udt_qry: ".$get_cv_id_and_udt_qry);
		if ($get_cv_id_and_udt_qry_res->num_rows > 0) 
		{
		    $cv_id_array = [];
		    while($get_cv_id_and_udt_qry_res_row = $get_cv_id_and_udt_qry_res->fetch_assoc())
		    {
		        //error_log($get_cv_id_and_udt_qry_res_row['cv_id']."||".$get_cv_id_and_udt_qry_res_row['updated_at']);
		        //$verify_dates_qry = "SELECT * FROM `tbl_month_genre_graph_data` WHERE `cv_id`=".$get_cv_id_and_udt_qry_res_row['cv_id']." and (`created_at`<'".$get_cv_id_and_udt_qry_res_row['updated_at']."' && `updated_at`<'".$get_cv_id_and_udt_qry_res_row['updated_at']."' )";
		        $verify_dates_qry = "SELECT * FROM `tbl_month_genre_graph_data` WHERE `cv_id`=".$get_cv_id_and_udt_qry_res_row['cv_id']." and `updated_at`<'".$get_cv_id_and_udt_qry_res_row['updated_at']."'";
		        $verify_dates_qry_res = $conn->query($verify_dates_qry);
		        //error_log("verify_dates_qry: ".$verify_dates_qry);
		        if ($verify_dates_qry_res->num_rows > 0) 
		        {
		            //error_log("generate_monthwise_data_graphs () called in if");
		            //$db_dump->ins_updt_monthly_graph_data($get_cv_id_and_udt_qry_res_row['cv_id']);
		            array_push($cv_id_array, $get_cv_id_and_udt_qry_res_row['cv_id']);
		        }
		        else
		        {
		            //error_log("generate_monthwise_data_graphs () called in else");
		            $chk_exist_qry = "SELECT * FROM `tbl_month_genre_graph_data` WHERE `cv_id`=".$get_cv_id_and_udt_qry_res_row['cv_id'];
		            $chk_exist_qry_res = $conn->query($chk_exist_qry);
		            //error_log("chk_exist_qry: ".$chk_exist_qry);
		            if ($chk_exist_qry_res->num_rows == 0) 
		            {
		                //error_log("generate_monthwise_data_graphs () called in else if");
		                //$db_dump->ins_updt_monthly_graph_data($get_cv_id_and_udt_qry_res_row['cv_id']);
		                array_push($cv_id_array, $get_cv_id_and_udt_qry_res_row['cv_id']);
		            }
		        }
		    }
		    error_log("page : [cyanite_php_clone] : function [generate_monthwise_data_graphs] : CV IDs which need to process to get monthwise data:");
			error_log(implode(",",$cv_id_array));
			error_log("===================================================================================================================================");
			if(count($cv_id_array)>0)
			{
				$db_dump->ins_updt_monthly_graph_data($cv_id_array);
			}
		}
		else
		{
			error_log("page : [cyanite_php_clone] : function [generate_monthwise_data_graphs] : No CV IDs available for process to get monthwise data:");
			error_log("===================================================================================================================================");
		}
	}
}
?>
