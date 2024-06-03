<?php

require_once('sonic/sonic_functions.php');
require_once('sonic/db_dump.php');
require_once('cynite_apis.php');

error_reporting(0);

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

	function upload_mp3s_on_cyanite(){

		$mydirectory = 'C:/SonicCV/python_script/cron_project/download/instagram_mp3/zip';

		$dircontents = scandir($mydirectory);
	
		foreach ($dircontents as $file) {
			$extension = pathinfo($file, PATHINFO_EXTENSION);
			if ($extension == 'zip') {
				echo $file;
				$folder_name = str_replace(".zip","",$file);
				
				if (file_exists("C:/SonicCV/python_script/cron_project/download/instagram_mp3/".$folder_name)) 
				{
				    echo "The file $folder_name exists";
				}
				else 
				{
				    echo "The file $folder_name does not exists";
				    mkdir("C:/SonicCV/python_script/cron_project/download/instagram_mp3/".$folder_name);
				}
				$zip = new ZipArchive;
				$res = $zip->open("C:/SonicCV/python_script/cron_project/download/instagram_mp3/zip/".$file);
				if ($res === TRUE) {
				  $zip->extractTo('C:/SonicCV/python_script/cron_project/download/instagram_mp3/'.$folder_name);
				  $zip->close();
				  unlink("C:/SonicCV/python_script/cron_project/download/instagram_mp3/zip/".$file);
				  error_log("Content of ".$file." is extracted into ".$folder_name." successfully and ".$file." is deleted successfully");
				} else {
				  error_log("Error occured while extarction of ".$file);
				}
			}
		}

		$sonic_functions = new sonic_functions();
		try{
			$cd = date('Y/m/d h:i:s a', time());

			$dbcon = include('config/config.php');
			$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);	
			
			//$get_cv_ids_qry = "select * from tbl_social_media_sync_process_data where is_active = 0 AND (yt = 1 OR ig = 1 OR tt = 1)";
			$get_cv_ids_qry = "select * from tbl_social_media_sync_process_data where is_active = 0 AND (yt = 1 OR ig = 1 OR tt = 1 OR twt = 1) order by id asc";
			// $get_cv_ids_qry = "select * from tbl_social_media_sync_process_data where is_active = 0 AND (yt = 1 OR ig = 1 OR tt = 1 OR twt = 1) and cv_id = 1460 ";
			//$get_cv_ids_qry = "select * from tbl_social_media_sync_process_data where is_active = 0 and ".$process_type." = 1 order by id asc";
			//$get_cv_ids_qry = "select * from tbl_social_media_sync_process_data where is_active = 0 AND (yt = 1 OR tt = 1 OR twt = 1) order by id asc";

			$get_cv_ids_qry_res = $conn->query($get_cv_ids_qry);
			$industry_wise_cv_id_array = array();
			
			while($get_cv_ids_qry_res_row = $get_cv_ids_qry_res->fetch_assoc()) {
				//array_push($industry_wise_cv_id_array, $get_cv_ids_qry_res_row['cv_id']);

				//$get_chnl_cnt_qry = "SELECT chn_id FROM `tbl_social_spyder_graph_request_data` WHERE `cv_id` = ".$get_cv_ids_qry_res_row['cv_id']." and `is_active` = 0 and `chn_notfound` = 0 and `v_count` != 0 and `down_count` != 0";

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

					$get_process_sync_stats_qry = "select * from tbl_social_media_sync_process_data where cv_id = ".$get_cv_ids_qry_res_row['cv_id']." and is_active = 0 and ".$p_type." = 1";
					$get_process_sync_stats_qry_res = $conn->query($get_process_sync_stats_qry);

					if($get_process_sync_stats_qry_res->num_rows > 0)
					{
						$chnl_cnt_qry = "SELECT chn_id FROM ( SELECT * FROM `tbl_social_spyder_graph_request_data` WHERE `cv_id` = ".$get_cv_ids_qry_res_row['cv_id']." and `is_active` = 0 and `chn_notfound` = 0 and v_count != 0 and down_count != 0 and process_type = '".$process_type."' UNION SELECT * FROM `tbl_social_spyder_graph_request_data` WHERE `cv_id` = ".$get_cv_ids_qry_res_row['cv_id']." and `is_active` = 0 and `chn_notfound` = 0 and v_count is null and down_count is null and process_type = '".$process_type."') ch";

						$chnl_cnt_qry_res = $conn->query($chnl_cnt_qry);
						$channel_count = $chnl_cnt_qry_res->num_rows;
						echo "Total ".$process_type." channel count of CV ".$get_cv_ids_qry_res_row['cv_id']." is ".$channel_count."<br>";
						error_log("Total ".$process_type." channel count of CV ".$get_cv_ids_qry_res_row['cv_id']." is ".$channel_count);

						$chnl_cntnt_upld_range_array = [];
						$chnl_cntnt_upld_range = [];
						$available_chnl_id_arr = [];
						error_log("Total channel count of CV ".$get_cv_ids_qry_res_row['cv_id']." is ".$channel_count);
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


								while ($chnl_cnt_qry_res_row = $chnl_cnt_qry_res->fetch_assoc())
								{
									$get_first_chnl_cntnt_id_qry = "SELECT * FROM ( SELECT * FROM tbl_social_spyder_graph_meta_data WHERE cv_id=".$get_cv_ids_qry_res_row['cv_id']." and is_active=0 and chn_id ='".$chnl_cnt_qry_res_row['chn_id']."' LIMIT ".$upload_count.") sub ORDER BY id ASC limit 1";
									// echo $get_first_chnl_cntnt_id_qry."<br>";
									$get_first_chnl_cntnt_id_qry_res = $conn->query($get_first_chnl_cntnt_id_qry);
									$get_first_chnl_cntnt_id_qry_res_data = $get_first_chnl_cntnt_id_qry_res->fetch_assoc();
									$first_id = $get_first_chnl_cntnt_id_qry_res_data['id'];
									// echo "first_id: ".$first_id."<br>";							

									$get_last_chnl_cntnt_id_qry = "SELECT * FROM ( SELECT * FROM tbl_social_spyder_graph_meta_data WHERE cv_id=".$get_cv_ids_qry_res_row['cv_id']." and is_active=0 and chn_id ='".$chnl_cnt_qry_res_row['chn_id']."' LIMIT ".$upload_count.") sub ORDER BY id DESC limit 1";
									// echo $get_last_chnl_cntnt_id_qry."<br>";
									$get_last_chnl_cntnt_id_qry_res = $conn->query($get_last_chnl_cntnt_id_qry);
									$get_last_chnl_cntnt_id_qry_res_data = $get_last_chnl_cntnt_id_qry_res->fetch_assoc();
									$last_id = $get_last_chnl_cntnt_id_qry_res_data['id'];
									// echo "last_id: ".$last_id."<br>";

									$get_chnl_cntnt_total_count_qry = "SELECT * FROM tbl_social_spyder_graph_meta_data WHERE cv_id = ".$get_cv_ids_qry_res_row['cv_id']." and chn_id ='".$chnl_cnt_qry_res_row['chn_id']."' and is_active=0";
									// echo $get_chnl_cntnt_total_count_qry."<br>";
									$get_chnl_cntnt_total_count_qry_res = $conn->query($get_chnl_cntnt_total_count_qry);
									$chnl_cntnt_count = $get_chnl_cntnt_total_count_qry_res->num_rows;
									// echo "chnl_cntnt_count: ".$chnl_cntnt_count."<br>";					

									if($first_id != '' && $first_id != null && $last_id != '' && $last_id != null)
									{
										$chnl_cntnt_upld_range["start_id"] = $first_id;
										$chnl_cntnt_upld_range["end_id"] = $last_id;
										$chnl_cntnt_upld_range["total_count"] = $chnl_cntnt_count;
										$chnl_cntnt_upld_range_array[$get_chnl_cnt_qry_res_row['chn_id']] = $chnl_cntnt_upld_range;
										array_push($available_chnl_id_arr,$get_chnl_cnt_qry_res_row['chn_id']);
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

								while ($chnl_cnt_qry_res_row = $chnl_cnt_qry_res->fetch_assoc())
								{
									$get_first_chnl_cntnt_id_qry = "SELECT * FROM ( SELECT * FROM tbl_social_spyder_graph_meta_data WHERE cv_id=".$get_cv_ids_qry_res_row['cv_id']." and is_active=0 and chn_id ='".$chnl_cnt_qry_res_row['chn_id']."' LIMIT ".$upload_count.") sub ORDER BY id ASC limit 1";
									// echo $get_first_chnl_cntnt_id_qry."<br>";
									$get_first_chnl_cntnt_id_qry_res = $conn->query($get_first_chnl_cntnt_id_qry);
									$get_first_chnl_cntnt_id_qry_res_data = $get_first_chnl_cntnt_id_qry_res->fetch_assoc();
									$first_id = $get_first_chnl_cntnt_id_qry_res_data['id'];
									// echo "first_id: ".$first_id."<br>";							

									$get_last_chnl_cntnt_id_qry = "SELECT * FROM ( SELECT * FROM tbl_social_spyder_graph_meta_data WHERE cv_id=".$get_cv_ids_qry_res_row['cv_id']." and is_active=0 and chn_id ='".$chnl_cnt_qry_res_row['chn_id']."' LIMIT ".$upload_count.") sub ORDER BY id DESC limit 1";
									// echo $get_last_chnl_cntnt_id_qry."<br>";
									$get_last_chnl_cntnt_id_qry_res = $conn->query($get_last_chnl_cntnt_id_qry);
									$get_last_chnl_cntnt_id_qry_res_data = $get_last_chnl_cntnt_id_qry_res->fetch_assoc();
									$last_id = $get_last_chnl_cntnt_id_qry_res_data['id'];
									// echo "last_id: ".$last_id."<br>";

									$get_chnl_cntnt_total_count_qry = "SELECT * FROM tbl_social_spyder_graph_meta_data WHERE cv_id = ".$get_cv_ids_qry_res_row['cv_id']." and chn_id ='".$chnl_cnt_qry_res_row['chn_id']."' and is_active=0";
									// echo $get_chnl_cntnt_total_count_qry."<br>";
									$get_chnl_cntnt_total_count_qry_res = $conn->query($get_chnl_cntnt_total_count_qry);
									$chnl_cntnt_count = $get_chnl_cntnt_total_count_qry_res->num_rows;
									// echo "chnl_cntnt_count: ".$chnl_cntnt_count."<br>";					

									if($first_id != '' && $first_id != null && $last_id != '' && $last_id != null)
									{
										$chnl_cntnt_upld_range["start_id"] = $first_id;
										$chnl_cntnt_upld_range["end_id"] = $last_id;
										$chnl_cntnt_upld_range["total_count"] = $chnl_cntnt_count;
										$chnl_cntnt_upld_range_array[$get_chnl_cnt_qry_res_row['chn_id']] = $chnl_cntnt_upld_range;
										array_push($available_chnl_id_arr,$get_chnl_cnt_qry_res_row['chn_id']);
									}
								}
							}

							if(!empty($available_chnl_id_arr))
							{

								foreach ($available_chnl_id_arr as $chnl_id)
								{
									echo $chnl_cntnt_upld_range_array[$chnl_id]['start_id']."<br>".$chnl_cntnt_upld_range_array[$chnl_id]['end_id']."<br>";
									error_log("------------------------------------------------------------------------------------------------------");
									error_log("Upload tracks of CV ".$get_cv_ids_qry_res_row['cv_id']." and channel ".$chnl_id." between ".$chnl_cntnt_upld_range_array[$chnl_id]['start_id']." and ".$chnl_cntnt_upld_range_array[$chnl_id]['end_id']);
									error_log("------------------------------------------------------------------------------------------------------");
									$sql = "select * from tbl_social_spyder_graph_meta_data where cv_id = ".$get_cv_ids_qry_res_row['cv_id']." and chn_id = '".$chnl_id."' and id between ".$chnl_cntnt_upld_range_array[$chnl_id]['start_id']." and ".$chnl_cntnt_upld_range_array[$chnl_id]['end_id']." and status=0 AND is_active = 0";			
									//$sql = "select * from tbl_social_spyder_graph_meta_data where cv_id IN (989) and status=0 AND is_active = 0";
									 
									$result = $conn->query($sql);

									if ($result->num_rows > 0) {  
									  while($row = $result->fetch_assoc()) {

									  	$cynite_api = new CyaniteAI();
									  	$db_dump = new db_dump();
										// Upload request
										
										error_log("**********Next Track**********");

										$RequestUpload_json = json_decode($cynite_api->RequestUpload());
										if($RequestUpload_json != "0"){
											// echo "<br>upload start".json_decode($RequestUpload_json); // json_encode(array("status"=>500, "data"=>"Something went wrong while uploading request","Description"=>"File upload is pending and Library track creation is pending."));

											$var_RequestUpload_id = $RequestUpload_json->data->fileUploadRequest->id;
											$var_RequestUpload_url = $RequestUpload_json->data->fileUploadRequest->uploadUrl;
											
											// upload mp3

											//echo "var_RequestUpload_url".$var_RequestUpload_url."<br>";
											//echo "path".$row["path"]."<br>";
											
											$fileupload_status = $cynite_api->FileUpload($var_RequestUpload_url , $row["path"]); //json_encode(array("status"=>500, "data"=>"Something went wrong while uploading file","Description"=>"File upload is pending and Library track creation is pending."));
											
											if($fileupload_status!="0"){
												//create ltrack id 
												$libraryTrack_json= json_decode($cynite_api->create_LibraryTrack($var_RequestUpload_id , $row["otitle"]));

												if($libraryTrack_json != "0"){
													$libraryTrack_id = $libraryTrack_json->data->libraryTrackCreate->createdLibraryTrack->id;
													error_log("libraryTrack_id:".$libraryTrack_id);

													// dump track into crate
													$response = $cynite_api -> add_libraryTrack_in_crate($libraryTrack_id, $row["crate_id"]);

													if($response!="0"){
														// update status after upload
														$status = $db_dump->update_trackid_and_status($libraryTrack_id, $row["id"]);
													}
												}
											}
										}
										error_log("sleep for 1 second");
										sleep(1);
									  }


									  $chk_meta_status_qry = "select * from tbl_social_spyder_graph_meta_data where cv_id = ".$get_cv_ids_qry_res_row['cv_id']." and chn_id = '".$chnl_id."' and id between ".$chnl_cntnt_upld_range_array[$chnl_id]['start_id']." and ".$chnl_cntnt_upld_range_array[$chnl_id]['end_id']." and status < 1 and is_active = 0";
									  $chk_meta_status_qry_res = $conn->query($chk_meta_status_qry);

									  if ($chk_meta_status_qry_res->num_rows == 0) {  
									  
									  	  $updt_strt_and_end_id_qey = "UPDATE `tbl_social_spyder_graph_request_data` SET `uploaded_start_id` = '".$chnl_cntnt_upld_range_array[$chnl_id]['start_id']."' , `uploaded_end_id` = '".$chnl_cntnt_upld_range_array[$chnl_id]['end_id']."' , `new_status` = 1 WHERE `cv_id` = ".$get_cv_ids_qry_res_row['cv_id']." and chn_id = '".$chnl_id."'";

									  	  if($conn->query($updt_strt_and_end_id_qey))
										  {
										  	error_log("page : [cyanite_php_clone] : function [upload_mp3s_on_cyanite] : start_id and end_id updated and status updated to 1 (upload completed) into tbl_social_spyder_graph_request_data for `cv_id` = ".$get_cv_ids_qry_res_row['cv_id']." and chn_id = ".$chnl_id);
										  }
										  else
										  {
										  	error_log("page : [cyanite_php_clone] : function [upload_mp3s_on_cyanite] : error occurred while updating start_id and end_id and status to 1 (upload completed) into tbl_social_spyder_graph_request_data for `cv_id` = ".$get_cv_ids_qry_res_row['cv_id']." and chn_id = ".$chnl_id);
										  }
									  }
									  else
										{
											error_log("page : [cyanite_php_clone] : function [fetch_analysis] : Few data pending for upload of cv ".$get_cv_ids_qry_res_row['cv_id']." channel ".$get_chnl_start_n_end_cntnt_id_qry_res_row['chn_id']." data between ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_start_id']." and ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_end_id']);
										}

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
	}

	function fetch_analysis(){
		error_log("fetch_analysis () called");
		$sonic_functions = new sonic_functions();

		try{
			$db_dump = new db_dump();
			$dbcon = include('config/config.php');
			$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);	

			// Add to Process Queue start
			//$get_cv_ids_qry = "select * from tbl_social_media_sync_process_data where is_active = 0 AND (yt = 1 OR ig = 1 OR tt = 1)";
			$get_cv_ids_qry = "select * from tbl_social_media_sync_process_data where is_active = 0 AND (yt = 1 OR ig = 1 OR tt = 1 OR twt = 1) order by id asc";
			
			$get_cv_ids_qry_res = $conn->query($get_cv_ids_qry);
			$industry_wise_cv_id_array = array();
			while($get_cv_ids_qry_res_row = $get_cv_ids_qry_res->fetch_assoc()) {
				//array_push($industry_wise_cv_id_array, $get_cv_ids_qry_res_row['cv_id']);

				$get_chnl_start_n_end_cntnt_id_qry = "SELECT * FROM `tbl_social_spyder_graph_request_data` WHERE `cv_id` = ".$get_cv_ids_qry_res_row['cv_id']." and is_active = 0 and `chn_notfound` = 0 and `new_status` = 1";
				$get_chnl_start_n_end_cntnt_id_qry_res = $conn->query($get_chnl_start_n_end_cntnt_id_qry);
				if ($get_chnl_start_n_end_cntnt_id_qry_res->num_rows > 0)
				{
					while($get_chnl_start_n_end_cntnt_id_qry_res_row = $get_chnl_start_n_end_cntnt_id_qry_res->fetch_assoc())
					{
						//if($get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_start_id'] > 0 && $get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_end_id'] > 0)
						//{
							$sql = "select * from tbl_social_spyder_graph_meta_data where cv_id = ".$get_cv_ids_qry_res_row['cv_id']." and chn_id = '".$get_chnl_start_n_end_cntnt_id_qry_res_row['chn_id']."' and id between ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_start_id']." and ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_end_id']." and status=1 and status!=0  AND is_active = 0";
							// Add to Process Queue end
						
							$result = $conn->query($sql);

							if ($result->num_rows > 0) {  
							  while($row = $result->fetch_assoc()) {

							  	$db_dump->old_cyanite_data_disable($row["cv_id"],$row["c_date"],$row["chn_id"],$row["process_type"]); // to disable cyanite entries as per start date and end date if fetch_and_dump_analised_record processed interrupts

							  	$db_dump->fetch_and_dump_analised_record($row["track_id"], $row["cv_id"], $row["start_date"], $row["end_date"],$row["id"],$row["c_date"],$row["chn_id"],$row["process_type"]);				
								error_log("sleep for 1 second");
								sleep(1);
							  }

							  $chk_meta_status_qry = "select * from tbl_social_spyder_graph_meta_data where cv_id = ".$get_cv_ids_qry_res_row['cv_id']." and chn_id = '".$get_chnl_start_n_end_cntnt_id_qry_res_row['chn_id']."' and id between ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_start_id']." and ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_end_id']." and status < 2 and is_active = 0";
							  $chk_meta_status_qry_res = $conn->query($chk_meta_status_qry);
							  if ($chk_meta_status_qry_res->num_rows == 0) {

								  	$updt_new_status_qry = "UPDATE `tbl_social_spyder_graph_request_data` SET `new_status` = 2 WHERE `cv_id` = ".$get_cv_ids_qry_res_row['cv_id']." and chn_id = '".$get_chnl_start_n_end_cntnt_id_qry_res_row['chn_id']."'";
								  	if($conn->query($updt_new_status_qry))
									  {
									  	error_log("page : [cyanite_php_clone] : function [fetch_analysis] : status updated to 2 (analysis completed) into tbl_social_spyder_graph_request_data for `cv_id` = ".$get_cv_ids_qry_res_row['cv_id']." and chn_id = ".$get_chnl_start_n_end_cntnt_id_qry_res_row['chn_id']);
									  }
									  else
									  {
									  	error_log("page : [cyanite_php_clone] : function [fetch_analysis] : error occurred updating status to 2 (analysis completed) into tbl_social_spyder_graph_request_data for `cv_id` = ".$get_cv_ids_qry_res_row['cv_id']." and chn_id = ".$get_chnl_start_n_end_cntnt_id_qry_res_row['chn_id']);
									  }
								}
								else
								{
									error_log("page : [cyanite_php_clone] : function [fetch_analysis] : Few data pending for analysis of cv ".$get_cv_ids_qry_res_row['cv_id']." of channel ".$get_chnl_start_n_end_cntnt_id_qry_res_row['chn_id']." data between ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_start_id']." and ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_end_id']);
								}
							}
							else{
								error_log("page : [cyanite_php_clone] : function [fetch_analysis] : error : No CV data pending for analysis");
							}
						//}
					}
				}
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
			$dbcon = include('config/config.php');
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
		$dbcon = include('config/config.php');
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
	
	function aggregate_of_aggregate_old(){
		error_log("aggregate_of_aggregate () called");
		//echo "process start";		

		$sonic_functions = new sonic_functions();

		$db_dump = new db_dump();
		$dbcon = include('config/config.php');
		$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);	

		$pending_cv_ids = "SELECT DISTINCT(cv_id), (select COUNT(cv_id) from tbl_social_spyder_graph_meta_data where cv_id=a.cv_id and STATUS!=4 and status!=5 and status!=6 and is_active=0) as pending FROM `tbl_social_spyder_graph_meta_data` a where status=4 and is_active=0";
		
		$result = $conn->query($pending_cv_ids);
		error_log("pending_cv_ids brand id".$pending_cv_ids);
		
		/*$temp_arr = array(486,530,2,10,11,12,13,14,332,493,494,496,497,498,512,522,528,529, 84,85,86,87,88,341,502,512,
 121, 122, 123, 124, 125, 126, 127, 128, 129, 130, 131, 132, 133, 134, 135, 136, 137, 138, 139, 140, 141, 142, 143, 144, 146, 147, 148, 149, 150, 151, 152, 153, 154, 155, 156, 157, 158, 159, 160, 161, 162, 163, 164, 165, 168, 169, 170, 171, 172, 173, 174, 175, 176, 177, 178, 179, 180, 181, 182, 183, 184, 185, 186, 187, 188, 189, 190, 191, 192, 193, 194, 195, 196, 197, 198, 199, 201, 202, 203, 204, 205, 206, 207, 208, 209, 210, 211, 212, 213, 214, 215, 216, 349, 351, 228, 229, 230, 231, 326, 329, 330, 368, 252, 253, 254, 255, 256, 257, 258, 259, 260, 339, 344, 488, 489, 499, 500,402);*/
 		//$temp_arr = array(402);
		//$temp_arr = array(532, 404);
		//$temp_arr = array(490);
		//$temp_arr = array(22,23,24,25,26,28,29,31,32,33,34,35,36,37,38,39,40,41,42,43,338,375,376,377,378,379,380,381,382,383,384,385,387,396,398,507);
		//$temp_arr = array(375,376,377,378,379,380,381,382,383,384,385,387,396,398,507);
		//$temp_arr = array(534, 535);
		//$temp_arr = array(541);
		//$temp_arr = array(628,629);
		//$temp_arr = array(629);
		//$temp_arr = array(580,509,541,465,463,464,486,530,17,18,19,20,21,372);  // Industry 11,16,19,20 and 23
		
		/*if ($result->num_rows > 0) {
		  while($row = $result->fetch_assoc()) {
				error_log("current brand id".$row["cv_id"]);
				if(in_array($row["cv_id"],$temp_arr))
				{
					if($row["pending"]==0){
						$db_dump->aggr_of_aggr($row["cv_id"]);
						error_log($row["cv_id"]." is in array");
					}
				}
				else
				{
					error_log($row["cv_id"]." is not in array");
				}
			}
		}*/
		if ($result->num_rows > 0) {
		  while($row = $result->fetch_assoc()) {
				error_log("current brand id".$row["cv_id"]);
				if($row["pending"]==0){
					$db_dump->aggr_of_aggr($row["cv_id"]);
				}
			}
		}
	}

	function generate_monthwise_data_graphs(){
		error_log("page : [cyanite_php_clone] : function [generate_monthwise_data_graphs] : generate_monthwise_data_graphs () called");
		$db_dump = new db_dump();
		$dbcon = include('config/config.php');
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
