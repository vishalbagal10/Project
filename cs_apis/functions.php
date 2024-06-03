<?php
ini_set('display_errors', 0);
function log_transaction($conn,$txn_token,$txn_for,$txn_status,$txn_data,$txn_comment)
{
	//echo "INSERT INTO `tbl_transaction`(`txn_token`, `request_for`, `status`) VALUES ('".$txn_token."',$request_for,$status)";
	if($txn_for == 0)
	{
		
		if($txn_data != "")
		{
			// echo "txn_data".$txn_data."<br>";
			$ins_txn_log_qry = "INSERT INTO `tbl_transaction`(`txn_token`, `txn_for`, `txn_status`, `asset_id`, `txn_comment`) VALUES ('".$txn_token."',$txn_for,$txn_status,'".$txn_data."', '".$txn_comment."')";
		}
		else
		{
			$ins_txn_log_qry = "INSERT INTO `tbl_transaction`(`txn_token`, `txn_for`, `txn_status`, `txn_comment`) VALUES ('".$txn_token."',$txn_for,$txn_status, '".$txn_comment."')";
		}
	}
	else
	{
		$ins_txn_log_qry = "INSERT INTO `tbl_transaction`(`txn_token`, `txn_for`, `txn_status`, `master_tbl_name`, `txn_comment`) VALUES ('".$txn_token."',$txn_for,$txn_status,'".$txn_data."', '".$txn_comment."')";
	}
	// echo "ins_txn_log_qry".$ins_txn_log_qry."<br>";
	if($conn->query($ins_txn_log_qry))
	{
		error_log("data inserted successfully into transaction log table");
	}
	else
	{
		error_log("Something went wrong while insterting data into transaction log table");
	}
}

function check_and_get_access_token($conn)
{
    $chk_access_token_qry = "SELECT * FROM `tbl_cs_access_token` WHERE `is_active` = 0";

    $chk_access_token_qry_res = $conn->query($chk_access_token_qry);
    $chk_access_token_qry_res_row = $chk_access_token_qry_res->fetch_assoc();

    if($chk_access_token_qry_res->num_rows>0)
    {
        error_log("Active Access token exist");
        // echo "<br>Active Access token exist<br>";
        $txn_token = $chk_access_token_qry_res_row['token'];
        return $txn_token;
    }
    else
    {
        // $get_access_token_url = "http://192.168.1.112:7474/scs/get_access_token.php"; //LOCAL SERVER 1
        // $get_access_token_url = "http://10.100.0.60:7474/scs/get_access_token.php"; //LOCAL SERVER 2
        // $get_access_token_url = "https://taxonomy.logthis.in/apis/get_access_token.php"; //TEST SERVER
        $get_access_token_url = "https://taxonomy.sonic-hub.com/apis/get_access_token.php"; //LIVE SERVER

        $request_content_arr = ["identifier"=>"Sonic Radar", "pass_code"=>"ALD#(&%bcx)*(@89!SMA"];

        $content = json_encode($request_content_arr);

        $curl_response_data = json_decode(api_call($conn, $get_access_token_url, $content));

        // print_r($curl_response_data);

        // echo gettype($curl_response_data);

        if($curl_response_data->msg != "success")
        {
            $curl_response_data = json_decode(api_call($conn, $get_access_token_url, $content));
        }

        $txn_token = $curl_response_data->data->access_token;

        if($txn_token == '')
        {
            error_log("Empty Access token received");
            $curl_response_data = json_decode(api_call($conn, $get_access_token_url, $content));
        }
        
        $txn_token = $curl_response_data->data->access_token;

        if($txn_token != '')
        {
            error_log("Access token ".$txn_token." received successfully");

            $conn->query("UPDATE `tbl_cs_access_token` SET `is_active` = 1");

            $ins_access_token_qry = "INSERT INTO `tbl_cs_access_token`(`token`) VALUES ('".$txn_token."')";
            if($conn->query($ins_access_token_qry))
                error_log("Access token ".$txn_token." inserted successfully into access token table");
            else
                error_log("Error occured while insertring access token ".$txn_token." into access token table");
            
            return $txn_token;
        }
    }
    
}

function api_call($conn, $url, $content)
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($curl, CURLOPT_HTTPHEADER,
            array("Content-type: application/json"));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

    $curl_response = curl_exec($curl);

    $curl_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    /*if ( $curl_status != 201 ) {
        die("Error: call to URL $url failed with status $curl_status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
    }*/

    //$response = json_decode($curl_response, true);

    // print_r($curl_response);

    // print_r($json_response);

    if (curl_errno($curl))
    {
        // $error_msg = curl_error($curl.$response);
        // error_log("Error occurred after execution of curl request ".$error_msg);
        error_log("Error occurred after execution of curl request ");
        $curl_response = "0"; // error
    }

    return $curl_response;    
    curl_close($curl);
}

function extract_asset_result_data_from_received_response($conn, $response_content, $dbcon, $old_asset_cs_id, $present_at_splitter, $splitter_id, $cs_status_datetime)
{
	/*if($present_at_splitter == 1 && $splitter_id != 0)
	{
		$asset_cs_id = $asset_cs_id;
	}
	else
	{
		$asset_cs_id = $response_content->data->asset_id;
	}*/
	$asset_cs_id = $response_content->data->asset_id;
	error_log("asset_cs_id=>".$asset_cs_id."| asset_download_status=>".$response_content->data->asset_download_status);
	if($response_content->data->asset_download_status == 2)
	{
		$asset_download_status_date_time = $response_content->data->asset_download_status_date_time;
		error_log("Asset downloading failed at central system");
		error_log("UPDATE `tbl_assets` SET `cs_d_status` = 2, `cs_d_status_datetime`= '".$asset_download_status_date_time."', `cs_response_status` = 3, `cs_response_status_datetime`= '".$response_content->data->result->response_date_time."' WHERE `cs_asset_id` ='".$asset_cs_id."'");
		if($conn->query("UPDATE `tbl_assets` SET `cs_d_status` = 2, `cs_d_status_datetime`= '".$asset_download_status_date_time."', `cs_response_status` = 3, `cs_response_status_datetime`= '".$response_content->data->result->response_date_time."' WHERE `cs_asset_id` ='".$asset_cs_id."'"))
		{
			error_log("Downloading status updated successfully in tbl_assets table for asset id'".$asset_cs_id."'");
			return 0;
		}
		else
		{
			error_log("Error occured while updating status of Downloading in tbl_assets table for asset id'".$asset_cs_id."'");
			return 0;
		}
	}
	else
	{
		if($response_content->data->asset_download_status == 1)
		{
			if($conn->query("UPDATE `tbl_assets` SET `cs_d_status` = 1, `cs_d_status_datetime`= '".$response_content->data->asset_download_status_date_time."' WHERE `cs_asset_id` ='".$asset_cs_id."'"))
			{
				error_log("Downloading status updated successfully in tbl_assets table for asset id".$asset_cs_id);
				// return 0;
				if($response_content->data->result->status == 2)
				{
					// log_transaction($conn,$txn_token,$txn_for,$txn_status,$txn_data,$txn_comment);

					// $sonic_logo_asset_type_id_arr = [2,9];

					// if(!in_array($response_content->data->asset_type_id,$sonic_logo_asset_type_id_arr))
					if($response_content->data->asset_upload_at == 0)
					{
						$cyanite_id = $response_content->data->result->cyanite_data->cyanite_id;
						$veritonic_upload_id = $response_content->data->result->veritonic_data->upload_id;
						$segment_timestamps = json_encode($response_content->data->result->cyanite_data->segment_timestamps);
						$status = $response_content->data->result->status;

						$extract_and_store_cyanite_amp_data_status = extract_and_store_cyanite_amp_data($conn, $response_content, $old_asset_cs_id, $present_at_splitter, $splitter_id, $cs_status_datetime);
						if($extract_and_store_cyanite_amp_data_status == 1)
						{
							$extract_and_store_sonic_logo_data_status = extract_and_store_sonic_logo_data($conn, $response_content, $asset_cs_id);
							if($extract_and_store_sonic_logo_data_status == 1)
							{

								$chk_asset_processed_json_data_qry = "SELECT * FROM `tbl_asset_processed_json` WHERE `asset_id`= '".$asset_cs_id."'";
								$chk_asset_processed_json_data_qry_res = $conn->query($chk_asset_processed_json_data_qry);

								if($chk_asset_processed_json_data_qry_res->num_rows>0)
								{
									$chk_asset_processed_json_data_dlt_qry = "DELETE FROM `tbl_asset_processed_json` WHERE `asset_id` = '".$asset_cs_id."'";
									$conn->query($chk_asset_processed_json_data_dlt_qry);
								}
								
								if($conn->query("INSERT INTO `tbl_asset_processed_json`(`asset_id`, `asset_json`, `cyanite_id`, `veritonic_upload_id`) VALUES ('".$asset_cs_id."', '".json_encode($response_content)."', '".$cyanite_id."',  '".$veritonic_upload_id."')"))
								{
									error_log("Asset result json inserted successfully into asset json table for asset id'".$asset_cs_id."'");
									if($conn->query("UPDATE `tbl_assets` SET `cs_response_status` = ".$status.", `cs_response_status_datetime`= '".$response_content->data->result->response_date_time."', `segment_timestamps`= '".$segment_timestamps."'  WHERE `cs_asset_id` ='".$asset_cs_id."'"))
									{
										error_log("Response Status updated successfully in tbl_assets table for asset id '".$asset_cs_id."'");
										echo "Response Status updated successfully in tbl_assets table for asset id'".$asset_cs_id."'";
										return 1;
									}
									else
									{
										error_log("Error occured while updating Response status in tbl_assets table for asset id'".$asset_cs_id."'");
										return 0;
									}
									
								}
								else
								{
									error_log("Error occured while inserting asset result json into asset json table for asset id'".$asset_cs_id."'");
									return 0;
								}											
							}
						}
					}
					elseif($response_content->data->asset_upload_at == 1)
					{
						$cyanite_id = $response_content->data->result->cyanite_data->cyanite_id;
						$segment_timestamps = json_encode($response_content->data->result->cyanite_data->segment_timestamps);
						$status = $response_content->data->result->status;

						$extract_and_store_cyanite_amp_data_status = extract_and_store_cyanite_amp_data($conn, $response_content, $old_asset_cs_id, $present_at_splitter, $splitter_id, $cs_status_datetime);
						if($extract_and_store_cyanite_amp_data_status == 1)
						{
							$chk_asset_processed_json_data_qry = "SELECT * FROM `tbl_asset_processed_json` WHERE `asset_id`= '".$asset_cs_id."'";
							$chk_asset_processed_json_data_qry_res = $conn->query($chk_asset_processed_json_data_qry);

							if($chk_asset_processed_json_data_qry_res->num_rows>0)
							{
								$chk_asset_processed_json_data_dlt_qry = "DELETE FROM `tbl_asset_processed_json` WHERE `asset_id` = '".$asset_cs_id."'";
								$conn->query($chk_asset_processed_json_data_dlt_qry);
							}
							
							if($conn->query("INSERT INTO `tbl_asset_processed_json`(`asset_id`, `asset_json`, `cyanite_id`) VALUES ('".$asset_cs_id."', '".json_encode($response_content)."', '".$cyanite_id."')"))
							{
								error_log("Asset result json inserted successfully into asset json table for asset id'".$asset_cs_id."'");
								if($conn->query("UPDATE `tbl_assets` SET `cs_response_status` = ".$status.", `cs_response_status_datetime`= '".$response_content->data->result->response_date_time."', `segment_timestamps`= '".$segment_timestamps."' WHERE `cs_asset_id` ='".$asset_cs_id."'"))
								{
									error_log("Response Status updated successfully in tbl_assets table for asset id '".$asset_cs_id."'");
									echo "Response Status updated successfully in tbl_assets table for asset id'".$asset_cs_id."'";
								}
								else
								{
									error_log("Error occured while updating Response status in tbl_assets table for asset id'".$asset_cs_id."'");
								}
								return 1;
							}
							else
							{
								error_log("Error occured while inserting asset result json into asset json table for asset id'".$asset_cs_id."'");
								return 0;
							}
						}
					}
					else
					{
						$veritonic_upload_id = $response_content->data->result->veritonic_data->upload_id;
						$status = $response_content->data->result->status;
						
						$extract_and_store_sonic_logo_data_status = extract_and_store_sonic_logo_data($conn, $response_content, $asset_cs_id);
						if($extract_and_store_sonic_logo_data_status == 1)
						{
							$status = $response_content->data->result->status;
							$chk_asset_processed_json_data_qry = "SELECT * FROM `tbl_asset_processed_json` WHERE `asset_id`= '".$asset_cs_id."'";
							$chk_asset_processed_json_data_qry_res = $conn->query($chk_asset_processed_json_data_qry);

							if($chk_asset_processed_json_data_qry_res->num_rows>0)
							{
								$chk_asset_processed_json_data_dlt_qry = "DELETE FROM `tbl_asset_processed_json` WHERE `asset_id` = '".$asset_cs_id."'";
								$conn->query($chk_asset_processed_json_data_dlt_qry);
							}

							if($conn->query("INSERT INTO `tbl_asset_processed_json`(`asset_id`, `asset_json`, `veritonic_upload_id`) VALUES ('".$asset_cs_id."', '".json_encode($response_content)."',  '".$veritonic_upload_id."')"))
							{
								error_log("Asset result json inserted successfully into asset json table for asset id'".$asset_cs_id."'");
								if($conn->query("UPDATE `tbl_assets` SET `cs_response_status` = ".$status.", `cs_response_status_datetime`= '".$response_content->data->result->response_date_time."' WHERE `cs_asset_id` = '".$asset_cs_id."'"))
								{
									error_log("Response Status updated successfully in asset table for asset id '".$asset_cs_id."'");
									echo "Response Status updated successfully in asset table for asset id'".$asset_cs_id."'";
								}
								else
								{
									error_log("Error occured while updating Response status in asset table for asset id'".$asset_cs_id."'");
								}
								return 2;								
							}
							else
							{
								error_log("Error occured while inserting asset result json into asset json table for asset id'".$asset_cs_id."'");
								return 0;
							}
						}
					}
				}
				else
				{
					if($response_content->data->result->status == 3)
					{
						error_log("Asset ".$asset_cs_id." data processing failed at central system.");
						if($conn->query("UPDATE `tbl_assets` SET `cs_response_status` = ".$status.", `cs_response_status_datetime`= '".$response_content->data->result->response_date_time."' WHERE `cs_asset_id` ='".$asset_cs_id."'"))
						{
							error_log("Downloading status updated successfully in tbl_assets table for asset id'".$asset_cs_id."'");
						}
						else
						{
							error_log("Error occured while updating status of Downloading in tbl_assets table for asset id'".$asset_cs_id."'");
						}
						return 1;
					}
					else
					{
						if($response_content->data->result->status == 0)
							error_log("Asset ".$asset_cs_id." data processing not started yet at central system.");

						if($response_content->data->result->status == 1)
							error_log("Asset ".$asset_cs_id." data processing is in progress at central system.");

						return 0;
					}
				}
			}
			else
			{
				error_log("Error occured while updating status of Downloading in asset table for asset id".$asset_cs_id);
				return 0;
			}
			
		}
		else
		{
			error_log("Downloading of asset id".$asset_cs_id." is not started yet at central system.");
			return 0;
		}
	}  
}

function extract_and_store_sonic_logo_data($conn, $response_content, $asset_cs_id)
{
	// echo "sonic_logo_tags_data-<br>";
	$sonic_logo_tags_data = $response_content->data->result->veritonic_data->sonic_logo_tags_data;
	// print_r($sonic_logo_tags_data);

	$ins_asset_processed_sonic_logo_tags_data_qry = "";
	$ins_asset_processed_sonic_logo_tags_data_qry = "INSERT INTO `tbl_asset_processed_sonic_logo_tag_data`(`asset_id`, `sonic_logo_tag_id`, `sonic_logo_tag_value`) VALUES ";

	foreach ($sonic_logo_tags_data as $sltd_key => $sltd_value)
	{
		// echo "AMP tag_id=>".$sltd_value->tag_id."<br>";
		$ins_asset_processed_sonic_logo_tags_data_qry .= "('".$asset_cs_id."', '".$sltd_value->tag_id."', '".$sltd_value->tag_value."'),";
	}

	$chk_asset_processed_sonic_logo_tags_data_qry = "SELECT * FROM `tbl_asset_processed_sonic_logo_tag_data` WHERE `asset_id`= '".$asset_cs_id."'";
	$chk_asset_processed_sonic_logo_tags_data_qry_res = $conn->query($chk_asset_processed_sonic_logo_tags_data_qry);

	if($chk_asset_processed_sonic_logo_tags_data_qry_res->num_rows>0)
	{
		$chk_asset_processed_sonic_logo_tags_data_dlt_qry = "DELETE FROM `tbl_asset_processed_sonic_logo_tag_data` WHERE `asset_id` = '".$asset_cs_id."'";
		$conn->query($chk_asset_processed_sonic_logo_tags_data_dlt_qry);
	}

	// echo "ins_asset_processed_sonic_logo_tags_data_qry=>".rtrim($ins_asset_processed_sonic_logo_tags_data_qry,",")."<br><br>";
	if($conn->multi_query(rtrim($ins_asset_processed_sonic_logo_tags_data_qry,",")) === TRUE)
	{
		error_log("Asset id '".$asset_cs_id."' processed sonic logo tag data is inserted successfully into asset processed sonic logo tag data table");
		while ($conn->next_result()) {;} // flush multi_queries

		// echo "sonic_logo_main_mood_tags_data-<br>";
		$sonic_logo_main_mood_tags_data = $response_content->data->result->veritonic_data->sonic_logo_main_mood_tags_data;
		// print_r($sonic_logo_main_mood_tags_data);

		$ins_asset_processed_sonic_logo_main_mood_tags_data_qry = "";
		$ins_asset_processed_sonic_logo_main_mood_tags_data_qry = "INSERT INTO `tbl_asset_processed_sonic_logo_main_mood_tag_data`(`asset_id`, `sonic_logo_main_mood_tag_id`, `sonic_logo_main_mood_tag_value`) VALUES ";

		foreach ($sonic_logo_main_mood_tags_data as $slmmtd_key => $slmmtd_value)
		{
			// echo "AMP tag_id=>".$slmmtd_value->tag_id."<br>";
			$ins_asset_processed_sonic_logo_main_mood_tags_data_qry .= "('".$asset_cs_id."', '".$slmmtd_value->tag_id."', '".$slmmtd_value->tag_value."'),";
		}

		$chk_asset_processed_sonic_logo_main_mood_tags_data_qry = "SELECT * FROM `tbl_asset_processed_sonic_logo_main_mood_tag_data` WHERE `asset_id`= '".$asset_cs_id."'";
		$chk_asset_processed_sonic_logo_main_mood_tags_data_qry_res = $conn->query($chk_asset_processed_sonic_logo_main_mood_tags_data_qry);

		if($chk_asset_processed_sonic_logo_main_mood_tags_data_qry_res->num_rows>0)
		{
			$chk_asset_processed_sonic_logo_main_mood_tags_data_dlt_qry = "DELETE FROM `tbl_asset_processed_sonic_logo_main_mood_tag_data` WHERE `asset_id` = '".$asset_cs_id."'";
			$conn->query($chk_asset_processed_sonic_logo_main_mood_tags_data_dlt_qry);
		}

		// echo "ins_asset_processed_sonic_logo_main_mood_tags_data_qry=>".rtrim($ins_asset_processed_sonic_logo_main_mood_tags_data_qry,",")."<br><br>";
		if($conn->multi_query(rtrim($ins_asset_processed_sonic_logo_main_mood_tags_data_qry,",")) === TRUE)
		{
			error_log("Asset id '".$asset_cs_id."' processed sonic logo tag data is inserted successfully into asset processed sonic logo tag data table");
			while ($conn->next_result()) {;} // flush multi_queries
			return 1;
		
		}
		else
		{
			error_log("Something went wrong while extracting and storing Asset '".$asset_cs_id."' sonic logo tag data into asset processed sonic logo tag data table.");
			return 0;
		}
	}
	else
	{
		error_log("Something went wrong while extracting and storing Asset '".$asset_cs_id."' sonic logo tag data into asset processed sonic logo tag data table.");
		return 0;
	}	
}

function extract_and_store_cyanite_amp_data($conn, $response_content, $old_asset_cs_id, $present_at_splitter, $splitter_id, $cs_status_datetime)
{
	/*if($present_at_splitter == 1 && $splitter_id != 0)
	{
		$asset_cs_id = $asset_cs_id;
	}
	else
	{
		// echo "asset_id-".$response_content->data->asset_id."<br>";
		$asset_id = $response_content->data->asset_id;
	}*/
	// echo "asset_id-".$response_content->data->asset_id."<br>";
	$asset_cs_id = $response_content->data->asset_id;

	// echo "asset_download_status-".$response_content->data->asset_download_status."<br>";
	$asset_download_status = $response_content->data->asset_download_status;

	// echo "status-".$response_content->data->result->status."<br>";
	$status = $response_content->data->result->status;

	// echo "veritonic_upload_id-".$response_content->data->result->veritonic_data->upload_id."<br>";
	$veritonic_upload_id = $response_content->data->result->veritonic_data->upload_id;


	// Cyanite Data
	// echo "cyanite_id-".$response_content->data->result->cyanite_data->cyanite_id."<br>";
	$cyanite_id = $response_content->data->result->cyanite_data->cyanite_id;

	// echo "valence-".$response_content->data->result->cyanite_data->valence."<br>";
	$valence = $response_content->data->result->cyanite_data->valence;

	// echo "arousal-".$response_content->data->result->cyanite_data->arousal."<br>";
	$arousal = $response_content->data->result->cyanite_data->arousal;

	// echo "energylevel-".$response_content->data->result->cyanite_data->energylevel."<br>";
	$energylevel = $response_content->data->result->cyanite_data->energylevel;

	// echo "emotionalprofile-".$response_content->data->result->cyanite_data->emotionalprofile."<br>";
	$emotionalprofile = $response_content->data->result->cyanite_data->emotionalprofile;

	// echo "bpmprediction-<br>";
	$bpmprediction = json_encode($response_content->data->result->cyanite_data->bpmprediction);
	// print_r($bpmprediction);

	// echo "keyprediction-<br>";
	$keyprediction = json_encode($response_content->data->result->cyanite_data->keyprediction);
	// print_r($keyprediction);

	// echo "voice-<br>";
	$voice = json_encode($response_content->data->result->cyanite_data->voice);
	// print_r($voice);

	// echo "voiceover_degree-".$response_content->data->result->cyanite_data->voiceover_degree."<br>";
	$voiceover_degree = ($response_content->data->result->cyanite_data->voiceover_degree != '' && $response_content->data->result->cyanite_data->voiceover_degree != null) ? $response_content->data->result->cyanite_data->voiceover_degree : 0;

	// echo "voiceover_exists-".$response_content->data->result->cyanite_data->voiceover_exists."<br>";
	$voiceover_exists = $response_content->data->result->cyanite_data->voiceover_exists;

	// echo "genre-<br>";
	$genre = json_encode($response_content->data->result->cyanite_data->genre);
	// print_r($genre);

	// echo "moodtags-<br>";
	$moodtags = json_encode($response_content->data->result->cyanite_data->moodtags);
	// print_r($moodtags);

	// echo "genretags-<br>";
	$genretags = json_encode($response_content->data->result->cyanite_data->genretags);
	// print_r($genretags);

	// echo "segment_timestamps-<br>";
	$segment_timestamps = json_encode($response_content->data->result->cyanite_data->segment_timestamps);
	// print_r($segment_timestamps);

	$ins_asset_processed_cyanite_data_qry ="";
	if($present_at_splitter == 1 && $splitter_id != 0)
	{
		$ins_asset_processed_cyanite_data_qry = "INSERT INTO `tbl_asset_processed_cyanite_data`(`asset_id`, `cyanite_id`, `valence`, `arousal`, `energylevel`, `emotionalprofile`, `bpmprediction`, `keyprediction`, `voice`, `voiceover_degree`, `voiceover_exists`, `genre`, `moodtags`, `genretags`, `segment_timestamps`, `splitter_id`, `cs_status`, `cs_status_datetime`) VALUES ('".$asset_cs_id."', '".$cyanite_id."', '".$valence."', '".$arousal."', '".$energylevel."', '".$emotionalprofile."', '".$bpmprediction."', '".$keyprediction."', '".$voice."', '".$voiceover_degree."', '".$voiceover_exists."', '".$genre."', '".$moodtags."', '".$genretags."', '".$segment_timestamps."', ".$splitter_id.", 1, '".$cs_status_datetime."')";
	}
	else
	{
		$ins_asset_processed_cyanite_data_qry = "INSERT INTO `tbl_asset_processed_cyanite_data`(`asset_id`, `cyanite_id`, `valence`, `arousal`, `energylevel`, `emotionalprofile`, `bpmprediction`, `keyprediction`, `voice`, `voiceover_degree`, `voiceover_exists`, `genre`, `moodtags`, `genretags`, `segment_timestamps`) VALUES ('".$asset_cs_id."', '".$cyanite_id."', '".$valence."', '".$arousal."', '".$energylevel."', '".$emotionalprofile."', '".$bpmprediction."', '".$keyprediction."', '".$voice."', '".$voiceover_degree."', '".$voiceover_exists."', '".$genre."', '".$moodtags."', '".$genretags."', '".$segment_timestamps."')";
	}
	

	$chk_asset_processed_cyanite_data_qry = "SELECT * FROM `tbl_asset_processed_cyanite_data` WHERE `asset_id`= '".$asset_cs_id."'";
	$chk_asset_processed_cyanite_data_qry_res = $conn->query($chk_asset_processed_cyanite_data_qry);

	if($chk_asset_processed_cyanite_data_qry_res->num_rows>0)
	{
		$chk_asset_processed_cyanite_data_dlt_qry = "DELETE FROM `tbl_asset_processed_cyanite_data` WHERE `asset_id` = '".$asset_cs_id."'";
		$conn->query($chk_asset_processed_cyanite_data_dlt_qry);
	}
	if($old_asset_cs_id != 0)
	{
		$conn->query("DELETE FROM `tbl_asset_processed_cyanite_data` WHERE `asset_id` = '".$old_asset_cs_id."'");
		error_log("old_asset_cs_id ".$old_asset_cs_id." data deleted from asset_processed_cyanite_data tbl ");
	}
	error_log("ins_asset_processed_cyanite_data_qry ".$ins_asset_processed_cyanite_data_qry);
	// echo "ins_asset_processed_cyanite_data_qry=>".$ins_asset_processed_cyanite_data_qry."<br><br>";
	if($conn->query($ins_asset_processed_cyanite_data_qry))
	{
		error_log("Asset ".$asset_cs_id." cyanite data extracted and store in asset processed cyanite data table successfully.");

		// echo "amp_tags_data-<br>";
		$amp_tags_data = $response_content->data->result->cyanite_data->amp_tags_data;
		// print_r($amp_tags_data);
		$ins_asset_processed_amp_tags_data_qry ="";
		$ins_asset_processed_amp_tags_data_qry = "INSERT INTO `tbl_asset_processed_amp_tag_data`(`asset_id`, `amp_tag`, `amp_tag_value`, `amp_tag_scale`) VALUES ";

		foreach ($amp_tags_data as $atd_key => $atd_value)
		{
			// echo "AMP tag_id=>".$atd_value->tag_id."<br>";
			$ins_asset_processed_amp_tags_data_qry .= "('".$asset_cs_id."', '".$atd_value->tag_id."', '".$atd_value->tag_value."', '".$atd_value->tag_scale."'),";
		}

		$chk_asset_processed_amp_tags_data_qry = "SELECT * FROM `tbl_asset_processed_amp_tag_data` WHERE `asset_id`= '".$asset_cs_id."'";
		$chk_asset_processed_amp_tags_data_qry_res = $conn->query($chk_asset_processed_amp_tags_data_qry);

		if($chk_asset_processed_amp_tags_data_qry_res->num_rows>0)
		{
			$chk_asset_processed_amp_tags_data_dlt_qry = "DELETE FROM `tbl_asset_processed_amp_tag_data` WHERE `asset_id` = '".$asset_cs_id."'";
			$conn->query($chk_asset_processed_amp_tags_data_dlt_qry);
		}

		if($old_asset_cs_id != 0)
		{
			$conn->query("DELETE FROM `tbl_asset_processed_amp_tag_data` WHERE `asset_id` = '".$old_asset_cs_id."'");
			error_log("old_asset_cs_id ".$old_asset_cs_id." data deleted from asset_processed_amp_tag_data tbl ");
		}

		// echo "ins_asset_processed_amp_tags_data_qry=>".rtrim($ins_asset_processed_amp_tags_data_qry,",")."<br><br>";
		if($conn->multi_query(rtrim($ins_asset_processed_amp_tags_data_qry,",")) === TRUE)
		{
			error_log("Asset id '".$asset_cs_id."' processed amp tag data is inserted successfully into asset processed amp tag data table");
			while ($conn->next_result()) {;} // flush multi_queries

			// echo "genre_tags_data-<br>";
			$genre_tags_data = $response_content->data->result->cyanite_data->genre;
			// print_r($genre_tags_data);

			$ins_asset_genre_data_qry = "";
			$ins_asset_genre_data_qry = "INSERT INTO `tbl_asset_genre_data`(`asset_id`, `tag`, `tag_value`) VALUES ";

			foreach ($genre_tags_data as $gtd_key => $gtd_value)
			{
				// echo "GENRE tag=>".$gtd_value->tag."<br>";
				$ins_asset_genre_data_qry .= "('".$asset_cs_id."', '".$gtd_key."', '".$gtd_value."'),";
			}

			$chk_asset_genre_data_qry = "SELECT * FROM `tbl_asset_genre_data` WHERE `asset_id`= '".$asset_cs_id."'";
			$chk_asset_genre_data_qry_res = $conn->query($chk_asset_genre_data_qry);

			if($chk_asset_genre_data_qry_res->num_rows>0)
			{
				$chk_asset_genre_data_dlt_qry = "DELETE FROM `tbl_asset_genre_data` WHERE `asset_id` = '".$asset_cs_id."'";
				$conn->query($chk_asset_genre_data_dlt_qry);
			}

			if($old_asset_cs_id != 0)
			{
				$conn->query("DELETE FROM `tbl_asset_genre_data` WHERE `asset_id` = '".$old_asset_cs_id."'");
				error_log("old_asset_cs_id ".$old_asset_cs_id." data deleted from asset_genre_data tbl ");
			}

			// echo "ins_asset_genre_data_qry=>".rtrim($ins_asset_genre_data_qry,",")."<br><br>";
			if($conn->multi_query(rtrim($ins_asset_genre_data_qry,",")) === TRUE)
			{
				error_log("Asset id '".$asset_cs_id."' genre tag data is inserted successfully into asset genre data table");
					while ($conn->next_result()) {;} // flush multi_queries

				// echo "amp_main_mood_tags_data-<br>";
				$amp_main_mood_tags_data = $response_content->data->result->cyanite_data->amp_main_mood_tags_data;
				// print_r($amp_main_mood_tags_data);

				$ins_asset_processed_amp_main_mood_tags_data_qry = "";
				$ins_asset_processed_amp_main_mood_tags_data_qry = "INSERT INTO `tbl_asset_processed_amp_main_mood_tag_data`(`asset_id`, `amp_main_mood_tag`, `amp_main_mood_tag_value`) VALUES ";

				foreach ($amp_main_mood_tags_data as $ammtd_key => $ammtd_value)
				{
					// echo "AMP tag_id=>".$ammtd_value->tag_id."<br>";
					$ins_asset_processed_amp_main_mood_tags_data_qry .= "('".$asset_cs_id."', '".$ammtd_value->tag_id."', '".$ammtd_value->tag_value."'),";
				}

				$chk_asset_processed_amp_main_mood_tags_data_qry = "SELECT * FROM `tbl_asset_processed_amp_main_mood_tag_data` WHERE `asset_id`= '".$asset_cs_id."'";
				$chk_asset_processed_amp_main_mood_tags_data_qry_res = $conn->query($chk_asset_processed_amp_main_mood_tags_data_qry);

				if($chk_asset_processed_amp_main_mood_tags_data_qry_res->num_rows>0)
				{
					$chk_asset_processed_amp_main_mood_tags_data_dlt_qry = "DELETE FROM `tbl_asset_processed_amp_main_mood_tag_data` WHERE `asset_id` = '".$asset_cs_id."'";
					$conn->query($chk_asset_processed_amp_main_mood_tags_data_dlt_qry);
				}

				if($old_asset_cs_id != 0)
				{
					$conn->query("DELETE FROM `tbl_asset_processed_amp_main_mood_tag_data` WHERE `asset_id` = '".$old_asset_cs_id."'");
					error_log("old_asset_cs_id ".$old_asset_cs_id." data deleted from asset_processed_amp_main_mood_tag_data tbl ");
				}

				// echo "ins_asset_processed_amp_main_mood_tags_data_qry=>".rtrim($ins_asset_processed_amp_main_mood_tags_data_qry,",")."<br><br>";

				if($conn->multi_query(rtrim($ins_asset_processed_amp_main_mood_tags_data_qry,",")) === TRUE)
				{
					error_log("Asset id '".$asset_cs_id."' processed amp main mood tag data is inserted successfully into asset processed amp main mood tag data table");
					while ($conn->next_result()) {;} // flush multi_queries

					return 1;

					/*// echo "amp_main_mood_tags_segment_time_stamp_data-<br>";
					$amp_main_mood_tags_segment_time_stamp_data = $response_content->data->result->cyanite_data->amp_main_mood_tags_segment_time_stamp_data;
					// print_r($amp_main_mood_tags_segment_time_stamp_data);

					$ins_asset_processed_amp_main_mood_tags_segment_time_stamp_data_qry = "";
					$ins_asset_processed_amp_main_mood_tags_segment_time_stamp_data_qry = "INSERT INTO `tbl_asset_processed_amp_main_mood_tag_segment_time_stamps_data`(`asset_id`, `amp_main_mood_tag_id`, `segment_time_stamps_value`) VALUES ";

					foreach ($amp_main_mood_tags_segment_time_stamp_data as $ammtstsd_key => $ammtstsd_value)
					{
						// echo "AMP tag_id=>".$ammtstsd_value->tag_id."<br>";
						$ins_asset_processed_amp_main_mood_tags_segment_time_stamp_data_qry .= "('".$asset_cs_id."', '".$ammtstsd_value->tag_id."', '".json_encode($ammtstsd_value->time_stamp_data)."'),";
					}

					$chk_asset_processed_amp_main_mood_tags_segment_time_stamp_data_qry = "SELECT * FROM `tbl_asset_processed_amp_main_mood_tag_segment_time_stamps_data` WHERE `asset_id`= '".$asset_cs_id."'";
					$chk_asset_processed_amp_main_mood_tags_segment_time_stamp_data_qry_res = $conn->query($chk_asset_processed_amp_main_mood_tags_segment_time_stamp_data_qry);

					if($chk_asset_processed_amp_main_mood_tags_segment_time_stamp_data_qry_res->num_rows>0)
					{
						$chk_asset_processed_amp_main_mood_tags_segment_time_stamp_data_dlt_qry = "DELETE FROM `tbl_asset_processed_amp_main_mood_tag_segment_time_stamps_data` WHERE `asset_id` = '".$asset_cs_id."'";
						$conn->query($chk_asset_processed_amp_main_mood_tags_segment_time_stamp_data_dlt_qry);
					}

					// echo "ins_asset_processed_amp_main_mood_tags_segment_time_stamp_data_qry=>".rtrim($ins_asset_processed_amp_main_mood_tags_segment_time_stamp_data_qry,",")."<br><br>";

					if($conn->multi_query(rtrim($ins_asset_processed_amp_main_mood_tags_segment_time_stamp_data_qry,",")) === TRUE)
					{
						error_log("Asset id '".$asset_cs_id."' processed amp main mood tag segment time stamp data is inserted successfully into asset processed amp main mood tag segment time stamp data table");
						while ($conn->next_result()) {;} // flush multi_queries

						return 1;
						
					}
					else
					{
						error_log("Something went wrong while extracting and storing Asset '".$asset_cs_id."' processed amp main mood tag segment time stamp data into asset processed amp main mood tag segment time stamp data table");
						return 0;
					}*/
				}
				else
				{
					error_log("Something went wrong while extracting and storing Asset '".$asset_cs_id."' processed amp main mood tag data into asset processed amp main mood tag data table");
					return 0;
				}

				/*// echo "amp_tags_segment_time_stamp_data-<br>";
				$amp_tags_segment_time_stamp_data = $response_content->data->result->cyanite_data->amp_tags_segment_time_stamp_data;
				// print_r($amp_tags_segment_time_stamp_data);

				$ins_asset_processed_amp_tags_segment_time_stamp_data_qry = "";
				$ins_asset_processed_amp_tags_segment_time_stamp_data_qry = "INSERT INTO `tbl_asset_processed_amp_tag_segment_time_stamps_data`(`asset_id`, `amp_tag_id`, `segment_time_stamps_value`) VALUES ";

				foreach ($amp_tags_segment_time_stamp_data as $atstsd_key => $atstsd_value)
				{
					// echo "AMP tag_id=>".$atstsd_value->tag_id."<br>";
					$ins_asset_processed_amp_tags_segment_time_stamp_data_qry .= "('".$asset_cs_id."', '".$atstsd_value->tag_id."', '".json_encode($atstsd_value->time_stamp_data)."'),";
				}

				$chk_asset_processed_amp_tags_segment_time_stamp_data_qry = "SELECT * FROM `tbl_asset_processed_amp_tag_segment_time_stamps_data` WHERE `asset_id`= '".$asset_cs_id."'";
				$chk_asset_processed_amp_tags_segment_time_stamp_data_qry_res = $conn->query($chk_asset_processed_amp_tags_segment_time_stamp_data_qry);

				if($chk_asset_processed_amp_tags_segment_time_stamp_data_qry_res->num_rows>0)
				{
					$chk_asset_processed_amp_tags_segment_time_stamp_data_dlt_qry = "DELETE FROM `tbl_asset_processed_amp_tag_segment_time_stamps_data` WHERE `asset_id` = '".$asset_cs_id."'";
					$conn->query($chk_asset_processed_amp_tags_segment_time_stamp_data_dlt_qry);
				}

				// echo "ins_asset_processed_amp_tags_segment_time_stamp_data_qry=>".rtrim($ins_asset_processed_amp_tags_segment_time_stamp_data_qry,",")."<br><br>";
				if($conn->multi_query(rtrim($ins_asset_processed_amp_tags_segment_time_stamp_data_qry,",")) === TRUE)
				{
					error_log("Asset id '".$asset_cs_id."' processed amp tag segment time stamp data is inserted successfully into asset processed amp tag segment time stamp data table");
					while ($conn->next_result()) {;} // flush multi_queries

					
				}
				else
				{
					error_log("Something went wrong while extracting and storing Asset '".$asset_cs_id."' processed amp tag segment time stamp data into asset processed amp tag segment time stamp data table");
					return 0;
				}*/
			}
			else
			{
				error_log("Something went wrong while extracting and storing Asset '".$asset_cs_id."' genre tag data into asset genre data table");
				return 0;
			}

		}
		else
		{
			error_log("Something went wrong while extracting and storing Asset '".$asset_cs_id."' processed amp tag data into asset processed amp tag data table");
			return 0;
		}
	}
	else
	{
		error_log("Something went wrong while extracting and storing Asset '".$asset_cs_id."' cyanite data in asset processed cyanite data table.");
		return 0;
	}
}

//================== get access token function ==================

function get_access_token($conn){
	echo "<br>".$sql_query = "SELECT * FROM tbl_cs_access_token where is_active=0";
	 $run_sql_query = mysqli_query($conn,$sql_query);
	 $num_rows = mysqli_num_rows($run_sql_query);
	 error_log("Get Access_token Count == ".$num_rows);
	 echo "<br> Get Access_token Count ==".$num_rows;
	 if($num_rows > 0){
	 		echo "<br>*******token*******<br>".mysqli_fetch_assoc($run_sql_query)['token']."<br>*******<br>";
		 return $access_token = mysqli_fetch_assoc($run_sql_query)['token'];
	 }else{
		 // $url = "https://taxonomy.logthis.in/apis/get_access_token.php"; // TEST SERVER
 		 $url = "https://taxonomy.sonic-hub.com/apis/get_access_token.php"; // LIVE SERVER
		 $postParameter = array(
			 'identifier' => 'Sonic Radar',
			 'pass_code' => 'ALD#(&%bcx)*(@89!SMA'
		 );
 
		 $postParameter = json_encode($postParameter);
 
		 $curlResponse = curl_call_api($url,$postParameter);
		 error_log(" Access token curl response == ".$curlResponse);
		 echo "<br> Access token curl response".$curlResponse;
		 $response = json_decode($curlResponse,true);
		 if(count($response) > 0){
			 if($response['msg'] == "success"){
				 $access_token =  $response['data']['access_token'];
				 $sql_query = "INSERT INTO `tbl_cs_access_token` (`token`) VALUES ('$access_token')";
				 echo "<br>*-**-*-*-*-*-**-*access_token query<br>".$sql_query."<br>*-**-*-*-*-*-**-*<br>";
				 $run_sql_query = mysqli_query($conn,$sql_query);
				 if($run_sql_query){
					 echo "<br> New access token successfully inserted into tbl_cs_access_token";
					 error_log("New access token successfully inserted into tbl_cs_access_token");
				 }else{
					 echo "<br> Something went wrong while inserting new access token into tbl_cs_access_token";
					 error_log("Something went wrong while inserting new access token into tbl_cs_access_token");
				 }
				 echo "<br>*******token*******<br>".$access_token."<br>*******<br>";
				 return $access_token;
			 }else{
				 get_access_token($conn);
				 error_log("Get Access_token Failed");
				 echo "<br> Get Access_token Failed";
			 }
		 }else{
			 echo "<br> Something Went Wrong While Getting Access_token";
			 error_log("Something Went Wrong While Getting Access_token");
		 }
	 }
}
 
//================== send asset content to central system  ==================
function send_asset_content_to_central_system($call_from,$conn,$asset_name,$table_id,$transaction_token,$asset_type_id,$asset_upload_at,$data_status,$track_id,$path){

	echo "<br>||||||||||||||||||||<br>transaction_token<br>".$transaction_token."<br>||||||||||||||||||||<br>";
	error_log("asset_upload_at == ".$asset_upload_at);
	if($asset_upload_at == 2 ){
		// $base_url = 'https://soniccv.witsinteractive.in/public/audios/cv_audios/'; // TEST SERVER
		$base_url = 'https://sonicradar.sonic-hub.com/public/audios/cv_audios/'; // LIVE SERVER
		error_log("if data_status for sonic logo == 0");
		 echo "<br> if data_status for sonic logo == 0";
		 // $url = "https://taxonomy.logthis.in/apis/send_asset_content.php"; // TEST SERVER
		 //$url = "http://localhost:7474/php_script/url.php"; // LOCAL SERVER
		 $url = "https://taxonomy.sonic-hub.com/apis/send_asset_content.php"; // LIVE SERVER

		 $data = array(
			 'transaction_token' => $transaction_token,
			 'name' => $asset_name,
			 'type_id'=> $asset_type_id,
			 'market_id' => 0,
			 'language_id' => 0,
			 "upload_at" => $asset_upload_at,
			 'track_id'=>$track_id,
			 'call_back_url' => 'https://sonicradar.sonic-hub.com/cs_apis/get_asset_data_callback.php',
			 'link' => $base_url.$path
		 );
		 error_log("transaction_token->".$transaction_token." name->".$asset_name." type_id->".$asset_type_id." upload_at->".$asset_upload_at." track_id->".$track_id." link->".$path);
		 error_log("postParameter == ".json_encode($data)); 
	}
	else{
			// $base_url = 'https://soniccv.witsinteractive.in/'; // TEST SERVER
			$base_url = 'https://sonicradar.sonic-hub.com/'; // LIVE SERVER
			if($data_status >= 2 ){
				error_log("if data_status >= 2");
				echo "<br> if data_status >= 2";  
				
				//$url = "http://localhost:7474/php_script/url.php"; // LOCAL SERVER
				// $url = "https://taxonomy.logthis.in/apis/send_asset_content.php"; // TEST SERVER
				$url = "https://taxonomy.sonic-hub.com/apis/send_asset_content.php"; // LIVE SERVER
				$data = array(
					'transaction_token' => $transaction_token,
					'name' => $asset_name,
					'type_id'=>$asset_type_id,
					'market_id'=>0,
					'language_id'=>0,
					"upload_at"=>$asset_upload_at,
					'track_id'=>$track_id,
					'call_back_url'=>'https://sonicradar.sonic-hub.com/cs_apis/get_asset_data_callback.php',
					'link'=>$path
				);
			}
			else{
				error_log("if data_status == 0");
				echo "<br> if data_status == 0";  
				//$url = "http://localhost:7474/php_script/url.php"; // LOCAL SERVER
				// $url = "https://taxonomy.logthis.in/apis/send_asset_content.php"; // TEST SERVER
				$url = "https://taxonomy.sonic-hub.com/apis/send_asset_content.php"; // LIVE SERVER
				$data = array(
					'transaction_token' => $transaction_token,
					'name' => $asset_name,
					'type_id'=>$asset_type_id,
					'market_id'=>0,
					'language_id'=>0,
					"upload_at"=>$asset_upload_at,
					'track_id'=>0,
					'call_back_url'=>'https://sonicradar.sonic-hub.com/cs_apis/get_asset_data_callback.php',
					'link'=>$path
				);
			}
			
	}
	
	 $postParameter = json_encode($data);
	 error_log("postParameter == ".$postParameter);
	 echo "<br> postParameter".$postParameter;
	 $curlResponse = curl_call_api($url,$postParameter);
	 // $curlResponse = api_call($conn,$url,json_encode($data));
	 error_log("send_asset_content_to_central_system curl response == ".$curlResponse);
	 echo  "<br> send_asset_content_to_central_system curl response == ".$curlResponse; 
	 $response = json_decode($curlResponse,true);
	 if(count($response) > 0){
		 $msg = $response['msg'];
		 echo "<br> response msg == ".$msg;
		 if($msg == 0 || $msg == '0'){            
			 $asset_id = $response['data']['asset_id'];
 
			 if($asset_id != ""){

				$success_msg = "Asset Id Succesfully Inserted Into tbl_assets";
				$error_msg = "Something went wrong whlie updating asset_id into tbl_assets";
				if($call_from == "media_splitter_data"){
					$query_insert = "update tbl_asset_splitter set  `cs_asset_id` = '".$asset_id."' where cs_splitter_id='".$table_id."'";
				}else{
				  $query_insert = "INSERT INTO  tbl_assets (`cs_asset_id`) VALUES ('$asset_id')";
				}
				
				$run_query_insert = mysqli_query($conn,$query_insert);
				$tbl_assets_id = mysqli_insert_id($conn);

				if($run_query_insert){
					error_log("***********".$success_msg."******** id==".$tbl_assets_id." and  asset id is ===  ".$asset_id);
					echo "\n ***********".$success_msg."******** id== ".$tbl_assets_id  ."  and  asset id is ===  ".$asset_id;


					$today = date('Y-m-d H:i:s');
					if($call_from == "media_meta_data"){
						$query = "update tbl_social_spyder_graph_meta_data set status=1,cs_status=1,cs_status_datetime='".$today."',asset_id='".$tbl_assets_id."' where id='".$table_id."'";
						echo "<br>".$query."<br>";error_log("|||||||||||||".$query."|||||||||||||");
						$success_msgs = "tbl_social_spyder_graph_meta_data Asset Sent to Cs Succesfully";
						$table_id_label = "meta data id";
						$error_msgs = "Something went wrong whlie updating cs_status into tbl_social_spyder_graph_meta_data for ";
					}elseif($call_from == "media_splitter_data"){
						$query = "update tbl_asset_splitter set cs_status=2, cs_status_datetime='".$today."' where cs_splitter_id='".$table_id."'";
						$success_msgs = "tbl_asset_splitter Asset Sent to Cs Succesfully";
						$table_id_label = "splitter data id";
						$error_msgs = "Something went wrong whlie updating cs_status into tbl_asset_splitter for ";
					}else{
						$query = "update tbl_cv_block_6_data set cs_status=1,cs_status_datetime='".$today."',assets_id='".$tbl_assets_id."' where b6_id='".$table_id."'";
						$success_msgs = "tbl_cv_block_6_data Asset Sent to Cs Succesfully";
						$table_id_label = "sonic logo id";
						$error_msgs = "Something went wrong whlie updating cs_status into tbl_cv_block_6_data ";
					}
					
					$run_query = mysqli_query($conn,$query);
					if($run_query){
						error_log("***********".$success_msgs."********".$table_id_label."==".$table_id);
						echo "\n ***********".$success_msgs."********".$table_id_label."==".$table_id;

						if($call_from == "media_splitter_data"){
							$select_old_asset_id_tbl_asset_splitter_query = "SELECT * FROM tbl_asset_splitter WHERE cs_splitter_id='".$table_id."'";
                            $run_select_old_asset_id_tbl_asset_splitter_query = mysqli_query($conn,$select_old_asset_id_tbl_asset_splitter_query);
                            $fetch_row = mysqli_fetch_assoc($run_select_old_asset_id_tbl_asset_splitter_query);
                            $old_asset_id = $fetch_row['old_asset_id'];
							$new_cs_asset_id_update_tbl_assets_query = "UPDATE tbl_assets SET cs_asset_id = '".$asset_id."', cs_d_status = 0, cs_d_status_datetime = null, cs_response_status = 0, cs_response_status_datetime = null, segment_timestamps = null WHERE id='".$old_asset_id."'";
							$run_new_cs_asset_id_update_tbl_assets_query = mysqli_query($conn,$new_cs_asset_id_update_tbl_assets_query);
							if($run_new_cs_asset_id_update_tbl_assets_query){
								error_log("new cs_asset_id successfully updated in tbl_assets and new cs_asset_id  is == '".$asset_id."' ");
								echo "\n new cs_asset_id successfully updated in tbl_assets and new cs_asset_id  is == '".$asset_id."'  ";
							}else{
								error_log("something went wrong while updating cs_asset_id in tbl_assets and cs_asset_id  is == '".$asset_id."' ");
								echo "\n something went wrong while updating cs_asset_id in tbl_assets and cs_asset_id  is == '".$asset_id."' ";
							}
						}

					}else{
						error_log("***********".$error_msgs."********".$table_id_label."==".$table_id);
						echo "\n ***********".$error_msgs."********".$table_id_label."==".$table_id;
					}

				}else{
					error_log("***********".$error_msg."********tbl_id == ".$table_id  ."   and  asset id is ===  ".$asset_id);
					echo "\n ***********".$error_msg."******** tbl_id ==".$table_id  ."   and  asset id is ===  ".$asset_id;
				}
				 
			 }else{
				 error_log("Something Went Wrong While Getting Asset_id");
				 echo "<br> Something Went Wrong While Getting Asset_id";
			 }
		 }else{
			 error_log("the transaction token is expired and you need to call get_access_token API again");
			 echo "<br> the transaction token is expired and you need to call get_access_token API again";
			 
			 $query_tbl_cs_access_token_update = "update tbl_cs_access_token set is_active=1";
			 error_log($query_tbl_cs_access_token_update);
			 $run_query = mysqli_query($conn,$query_tbl_cs_access_token_update);
			 $transaction_token = check_and_get_access_token($conn);
			 if($transaction_token != ""){
				 send_asset_content_to_central_system($call_from,$conn,$asset_name,$table_id,$transaction_token,$asset_type_id,$asset_upload_at,$data_status,$track_id,$path);
			 }else{
				 echo "<br> transaction_token failed while recalling when the transaction token is expired and you need to call get_access_token API again";
				 error_log("transaction_token failed while recalling when the transaction token is expired and you need to call get_access_token API again");
			 }
			 
		 }
	 }else{
		 error_log("Someting went wrong with send_asset_content_to_central_system curl response" );
		 echo "<br> Someting went wrong with send_asset_content_to_central_system curl response";  
	 }
}
 
 
//================== curl called here ==================
function curl_call_api($url,$postParameter){
	 $ch = curl_init();
	 // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	 // curl_setopt($ch, CURLOPT_POSTFIELDS, $postParameter);   
	 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
	 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
	 curl_setopt($ch, CURLOPT_HTTPHEADER,
            array("Content-type: application/json"));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postParameter);
	 curl_setopt($ch, CURLOPT_URL, $url);   
	 $res = curl_exec($ch);
	 return $res; 
}

function get_social_media_graph_avg_data($conn,$cv_id,$cv_year,$industry_id,$sub_industry_id,$process_type,$cv_multiplier,$down_count)
{
	$get_social_media_processed_asset_data_qry = "select `tbl_assets`.* from `tbl_cvs` inner join `tbl_social_spyder_graph_meta_data` on `tbl_social_spyder_graph_meta_data`.`cv_id` = `tbl_cvs`.`cv_id` inner join `tbl_assets` on `tbl_assets`.`id` = `tbl_social_spyder_graph_meta_data`.`asset_id` where `tbl_cvs`.`cv_id` = ".$cv_id." and `tbl_social_spyder_graph_meta_data`.`asset_id` is not null and `tbl_assets`.`is_active` = 0 and `tbl_assets`.`cs_response_status` = 2 and `tbl_social_spyder_graph_meta_data`.`process_type` = '".$process_type."'";

	$get_social_media_processed_asset_data_qry_res = $conn->query($get_social_media_processed_asset_data_qry);
	$social_media_asset_id_arr = [];
    while($get_total_processed_asset_count_qry_res_row = $get_social_media_processed_asset_data_qry_res->fetch_assoc())
    {
    	array_push($social_media_asset_id_arr, "'".$get_total_processed_asset_count_qry_res_row['cs_asset_id']."'");
    }

    switch($process_type)
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

    $get_social_media_mood_graph_data_qry = "select `tbl_asset_processed_amp_main_mood_tag_data`.`amp_main_mood_tag`, `tbl_amp_main_mood_tag_master`.`tag_name`, avg(tbl_asset_processed_amp_main_mood_tag_data.amp_main_mood_tag_value) as amp_main_mood_tag_value from `tbl_asset_processed_amp_main_mood_tag_data` inner join `tbl_amp_main_mood_tag_master` on `tbl_asset_processed_amp_main_mood_tag_data`.`amp_main_mood_tag` = `tbl_amp_main_mood_tag_master`.`tag_id` where tbl_asset_processed_amp_main_mood_tag_data.asset_id in (".implode(",",$social_media_asset_id_arr).") and `tbl_asset_processed_amp_main_mood_tag_data`.`is_active` = 0 group by `amp_main_mood_tag`";

    // echo "get_social_media_mood_graph_data_qry".$get_social_media_mood_graph_data_qry."<br><br>";

    $get_social_media_mood_graph_data_qry_res = $conn->query($get_social_media_mood_graph_data_qry);

    
	$ins_social_media_mood_graph_data_qry = "INSERT INTO `".$mood_graph_tbl_name."` (`cv_id`, `lbl_name`, `lbl_value`) VALUES ";
    while($get_social_media_mood_graph_data_qry_res_row = $get_social_media_mood_graph_data_qry_res->fetch_assoc())
    {
    	$ins_social_media_mood_graph_data_qry .= "(".$cv_id.", '".$get_social_media_mood_graph_data_qry_res_row['tag_name']."', ".$get_social_media_mood_graph_data_qry_res_row['amp_main_mood_tag_value']."),";
    }

    $chk_social_media_mood_graph_data_qry = "SELECT * FROM `".$mood_graph_tbl_name."` WHERE `cv_id`= ".$cv_id;
	$chk_social_media_mood_graph_data_qry_res = $conn->query($chk_social_media_mood_graph_data_qry);

	if($chk_social_media_mood_graph_data_qry_res->num_rows>0)
	{
		$chk_social_media_mood_graph_data_dlt_qry = "DELETE FROM `".$mood_graph_tbl_name."` WHERE `cv_id` = ".$cv_id;
		$conn->query($chk_social_media_mood_graph_data_dlt_qry);
	}

	// echo "ins_social_media_mood_graph_data_qry".$ins_social_media_mood_graph_data_qry."<br><br>";

	if($conn->multi_query(rtrim($ins_social_media_mood_graph_data_qry,",")) === TRUE)
	{
		error_log("Social Media '".$process_type."' Mood graph data for CV ".$cv_id." successfully insterted into social media mood graph table.");
		while ($conn->next_result()) {;} // flush multi_queries

		$get_social_media_genre_graph_data_qry = "select `tbl_asset_genre_data`.`tag`, avg(tbl_asset_genre_data.tag_value) as tag_value from `tbl_asset_genre_data` where tbl_asset_genre_data.asset_id in (".implode(",",$social_media_asset_id_arr).") and `tbl_asset_genre_data`.`is_active` = 0 group by `tag`";

	    $get_social_media_genre_graph_data_qry_res = $conn->query($get_social_media_genre_graph_data_qry);

	    
		$ins_social_media_genre_graph_data_qry = "INSERT INTO `".$genre_graph_tbl_name."` (`cv_id`, `lbl_name`, `lbl_value`) VALUES ";
	    while($get_social_media_genre_graph_data_qry_res_row = $get_social_media_genre_graph_data_qry_res->fetch_assoc())
	    {
	    	$ins_social_media_genre_graph_data_qry .= "(".$cv_id.", '".$get_social_media_genre_graph_data_qry_res_row['tag']."', ".$get_social_media_genre_graph_data_qry_res_row['tag_value']."),";
	    }

	    $chk_social_media_genre_graph_data_qry = "SELECT * FROM `".$genre_graph_tbl_name."` WHERE `cv_id`= ".$cv_id;
		$chk_social_media_genre_graph_data_qry_res = $conn->query($chk_social_media_genre_graph_data_qry);

		if($chk_social_media_genre_graph_data_qry_res->num_rows>0)
		{
			$chk_social_media_genre_graph_data_dlt_qry = "DELETE FROM `".$genre_graph_tbl_name."` WHERE `cv_id` = ".$cv_id;
			$conn->query($chk_social_media_genre_graph_data_dlt_qry);
		}

		// echo "ins_social_media_genre_graph_data_qry".$ins_social_media_genre_graph_data_qry."<br><br>";

		if($conn->multi_query(rtrim($ins_social_media_genre_graph_data_qry,",")) === TRUE)
		{
			error_log("Social Media '".$process_type."' Genre graph data for CV ".$cv_id." successfully insterted into social media genre graph table.");
			while ($conn->next_result()) {;} // flush multi_queries

			$get_last_processed_asset_count_qry = "SELECT * FROM `tbl_social_media_sync_process_data` WHERE cv_id = ".$cv_id;
			$get_last_processed_asset_count_qry_res = $conn->query($get_last_processed_asset_count_qry);
			$get_last_processed_asset_count_qry_res_row = $get_last_processed_asset_count_qry_res->fetch_assoc();
			$col_val = ($get_last_processed_asset_count_qry_res_row[$p_type_last_process_count] != '' && $get_last_processed_asset_count_qry_res_row[$p_type_last_process_count] != null) ? $get_last_processed_asset_count_qry_res_row[$p_type_last_process_count] + $cv_multiplier : 0 + $cv_multiplier;



			if($down_count == count($social_media_asset_id_arr))
			{
				$col_val = $down_count; 
			}

			echo "UPDATE `tbl_social_media_sync_process_data` SET `".$p_type."` = 2 , `".$p_type_last_process_count."` = ".$col_val."  WHERE `cv_id` = ".$cv_id;
			echo "<br><br>";

			if($conn->query("UPDATE `tbl_social_media_sync_process_data` SET `".$p_type."` = 2 , `".$p_type_last_process_count."` = ".$col_val." WHERE `cv_id` = ".$cv_id))
			{
				error_log("Social Media '".$process_type."' Sync Status (2) and last processed asset count updated for CV ".$cv_id." successfully 
					into social media sync process table.");
				echo "Social Media '".$process_type."' Sync Status (2) and last processed asset count updated for CV ".$cv_id." successfully 
					into social media sync process table.";	
					return 1;			
			}
			else
			{
				error_log("Error occured while updating Social Media '".$process_type."' Sync Status (2) and last processed asset count for CV ".$cv_id." into social media sync process table.");
				echo "Error occured while updating Social Media '".$process_type."' Sync Status (2) and last processed asset count for CV ".$cv_id." into social media sync process table.";
				return 0;
			}
		}
		else
		{
			error_log("Error occured while insterting Social Media '".$process_type."' Genre graph data for CV ".$cv_id." into social media genre graph table.");
			echo "Error occured while insterting Social Media '".$process_type."' Genre graph data for CV ".$cv_id." into social media genre graph table.";
			return 0;
		}
	}
	else
	{
		error_log("Error occured while insterting Social Media '".$process_type."' Mood graph data for CV ".$cv_id." into social media mood graph table.");
		echo "Error occured while insterting Social Media '".$process_type."' Mood graph data for CV ".$cv_id." into social media mood graph table.";
		return 0;
	}
}

function get_aggregate_graph_for_cv($conn,$cv_id)
{
	$get_processed_asset_data_qry = "SELECT tbl_social_spyder_graph_meta_data.process_type, tbl_assets.* FROM `tbl_social_spyder_graph_meta_data` join tbl_assets on tbl_social_spyder_graph_meta_data.asset_id = tbl_assets.id WHERE tbl_social_spyder_graph_meta_data.cv_id = ".$cv_id." and tbl_social_spyder_graph_meta_data.is_active = 0 and tbl_assets.cs_response_status = 2";

	$get_processed_asset_data_qry_res = $conn->query($get_processed_asset_data_qry);
	$processed_asset_id_arr = [];
	$process_type_arr = [];
	while($get_processed_asset_data_qry_res_row = $get_processed_asset_data_qry_res->fetch_assoc())
	{
		if(!in_array($get_processed_asset_data_qry_res_row['process_type'], $process_type_arr))
			array_push($process_type_arr, $get_processed_asset_data_qry_res_row['process_type']);

		array_push($processed_asset_id_arr, "'".$get_processed_asset_data_qry_res_row['cs_asset_id']."'");
	}

	if(count($process_type_arr)>1)
	{
		$get_social_media_aggr_mood_graph_data_qry = "select `tbl_asset_processed_amp_main_mood_tag_data`.`amp_main_mood_tag`, `tbl_amp_main_mood_tag_master`.`tag_name`, avg(tbl_asset_processed_amp_main_mood_tag_data.amp_main_mood_tag_value) as amp_main_mood_tag_value from `tbl_asset_processed_amp_main_mood_tag_data` inner join `tbl_amp_main_mood_tag_master` on `tbl_asset_processed_amp_main_mood_tag_data`.`amp_main_mood_tag` = `tbl_amp_main_mood_tag_master`.`tag_id` where tbl_asset_processed_amp_main_mood_tag_data.asset_id in (".implode(",",$processed_asset_id_arr).") and `tbl_asset_processed_amp_main_mood_tag_data`.`is_active` = 0 group by `amp_main_mood_tag`";

	    $get_social_media_aggr_mood_graph_data_qry_res = $conn->query($get_social_media_aggr_mood_graph_data_qry);

	    
		$ins_social_media_aggr_mood_graph_data_qry = "INSERT INTO `tbl_social_media_aggr_mood_graph_data` (`cv_id`, `lbl_name`, `lbl_value`) VALUES ";
	    while($get_social_media_aggr_mood_graph_data_qry_res_row = $get_social_media_aggr_mood_graph_data_qry_res->fetch_assoc())
	    {
	    	$ins_social_media_aggr_mood_graph_data_qry .= "(".$cv_id.", '".$get_social_media_aggr_mood_graph_data_qry_res_row['tag_name']."', ".$get_social_media_aggr_mood_graph_data_qry_res_row['amp_main_mood_tag_value']."),";
	    }

	    $chk_social_media_aggr_mood_graph_data_qry = "SELECT * FROM `tbl_social_media_aggr_mood_graph_data` WHERE `cv_id`= ".$cv_id;
		$chk_social_media_aggr_mood_graph_data_qry_res = $conn->query($chk_social_media_aggr_mood_graph_data_qry);

		if($chk_social_media_aggr_mood_graph_data_qry_res->num_rows>0)
		{
			$chk_social_media_aggr_mood_graph_data_dlt_qry = "DELETE FROM `tbl_social_media_aggr_mood_graph_data` WHERE `cv_id` = ".$cv_id;
			$conn->query($chk_social_media_aggr_mood_graph_data_dlt_qry);
		}

		if($conn->multi_query(rtrim($ins_social_media_aggr_mood_graph_data_qry,",")) === TRUE)
		{
			error_log("Social Media Aggregate Mood graph data for CV ".$cv_id." successfully insterted into social media aggregate mood graph table.");
			while ($conn->next_result()) {;} // flush multi_queries

			$get_social_media_aggr_genre_graph_data_qry = "select `tbl_asset_genre_data`.`tag`, avg(tbl_asset_genre_data.tag_value) as tag_value from `tbl_asset_genre_data` where tbl_asset_genre_data.asset_id in (".implode(",",$processed_asset_id_arr).") and `tbl_asset_genre_data`.`is_active` = 0 group by `tag`";

		    $get_social_media_aggr_genre_graph_data_qry_res = $conn->query($get_social_media_aggr_genre_graph_data_qry);

		    
			$ins_social_media_aggr_genre_graph_data_qry = "INSERT INTO `tbl_social_media_aggr_genre_graph_data` (`cv_id`, `lbl_name`, `lbl_value`) VALUES ";
		    while($get_social_media_aggr_genre_graph_data_qry_res_row = $get_social_media_aggr_genre_graph_data_qry_res->fetch_assoc())
		    {
		    	$ins_social_media_aggr_genre_graph_data_qry .= "(".$cv_id.", '".$get_social_media_aggr_genre_graph_data_qry_res_row['tag']."', ".$get_social_media_aggr_genre_graph_data_qry_res_row['tag_value']."),";
		    }

		    $chk_social_media_aggr_genre_graph_data_qry = "SELECT * FROM `tbl_social_media_aggr_genre_graph_data` WHERE `cv_id`= ".$cv_id;
			$chk_social_media_aggr_genre_graph_data_qry_res = $conn->query($chk_social_media_aggr_genre_graph_data_qry);

			if($chk_social_media_aggr_genre_graph_data_qry_res->num_rows>0)
			{
				$chk_social_media_aggr_genre_graph_data_dlt_qry = "DELETE FROM `tbl_social_media_aggr_genre_graph_data` WHERE `cv_id` = ".$cv_id;
				$conn->query($chk_social_media_aggr_genre_graph_data_dlt_qry);
			}

			if($conn->multi_query(rtrim($ins_social_media_aggr_genre_graph_data_qry,",")) === TRUE)
			{
				error_log("Social Media Aggregate Genre graph data for CV ".$cv_id." successfully insterted into social media aggregate genre graph table.");
				while ($conn->next_result()) {;} // flush multi_queries

				return 1;
			}
			else
			{
				error_log("Error occured while insterting Social Media Aggregate Genre graph data for CV ".$cv_id." into social media aggregate genre graph table.");
				echo "Error occured while insterting Social Media Aggregate Genre graph data for CV ".$cv_id." into social media aggregate genre graph table.";
				return 0;
			}
		}
		else
		{
			error_log("Error occured while insterting Social Media Aggregate Mood graph data for CV ".$cv_id." into social media aggregate  mood graph table.");
			echo "Error occured while insterting Social Media Aggregate Mood graph data for CV ".$cv_id." into social media aggregate  mood graph table.";
			return 0;
		}
	}
}

function generate_top3_mood_genre_video_links($conn,$cv_id)
{
	echo "|||||||||||||||||||||||||||||||||||||||||||||||||||<br>";
	echo "currnt CV ID ".$cv_id."<br>";
	$get_yt_down_count_qry = "SELECT count(*) as down_count FROM `tbl_social_spyder_graph_meta_data` WHERE cv_id= ".$cv_id." and process_type='youtube' and cs_status=1 and is_active=0";
	$get_yt_down_count_qry_res = $conn->query($get_yt_down_count_qry);
	$get_yt_down_count_qry_res_rows = $get_yt_down_count_qry_res->fetch_assoc();
	$down_count = $get_yt_down_count_qry_res_rows['down_count'];

	$get_processed_asset_count_qry = "SELECT count(DISTINCT tbl_asset_processed_amp_main_mood_tag_data.asset_id) as processed_count FROM `tbl_social_spyder_graph_meta_data` join tbl_assets on tbl_social_spyder_graph_meta_data.asset_id = tbl_assets.id JOIN tbl_asset_processed_amp_main_mood_tag_data on tbl_assets.cs_asset_id = tbl_asset_processed_amp_main_mood_tag_data.asset_id WHERE tbl_social_spyder_graph_meta_data.cv_id = ".$cv_id." and tbl_social_spyder_graph_meta_data.process_type = 'youtube' and tbl_social_spyder_graph_meta_data.is_active=0 and tbl_social_spyder_graph_meta_data.cs_status=1 and tbl_assets.is_active=0 and tbl_asset_processed_amp_main_mood_tag_data.is_active=0";

	$get_processed_asset_count_qry_res = $conn->query($get_processed_asset_count_qry);
	$get_processed_asset_count_qry_res_rows = $get_processed_asset_count_qry_res->fetch_assoc();
	$processed_count = $get_processed_asset_count_qry_res_rows['processed_count'];

	if($down_count == $processed_count)
	{
		//**************************top 3 mood videos**************************//

	    $mood_query = "SELECT tbl_social_media_aggr_mood_graph_data.*,tbl_amp_main_mood_tag_master.tag_id,tbl_amp_main_mood_tag_master.tag_name FROM `tbl_social_media_aggr_mood_graph_data`
	    LEFT JOIN tbl_amp_main_mood_tag_master on tbl_social_media_aggr_mood_graph_data.lbl_name=tbl_amp_main_mood_tag_master.tag_name
	    WHERE  cv_id= ".$cv_id."  ORDER BY lbl_value DESC LIMIT 3";
	    $run_mood_query = mysqli_query($conn,$mood_query);
	    $num_rows_mood_query  = mysqli_num_rows($run_mood_query);
	    $main_3_aggr_mood_tag_data = mysqli_fetch_all($run_mood_query,MYSQLI_ASSOC);


	    if($num_rows_mood_query > 0){
	        //**************************get asset_id from tbl_social_spyder_graph_meta_data**************************//
	        $meta_data_query = "SELECT cv_id,chn_id,name,crate_name,track_id,asset_id FROM `tbl_social_spyder_graph_meta_data` WHERE cv_id=".$cv_id." AND process_type='youtube' and is_active=0 AND cs_status=1";
	        $run_meta_data_query = mysqli_query($conn,$meta_data_query);
	        $num_rows_meta_data = mysqli_num_rows($run_meta_data_query);

	        if($num_rows_meta_data > 0){

	            $fetch_data = mysqli_fetch_all($run_meta_data_query,MYSQLI_ASSOC);

	            $assetids_array = [];
	            foreach($fetch_data as $key=>$data){
	                array_push($assetids_array,$data['asset_id']);
	            }
	            $assetids_string = implode(",",$assetids_array);

	            //**************************get cs_asset_id from tbl_assets**************************//
	            $tbl_assets_query = "SELECT cs_asset_id FROM `tbl_assets` WHERE id in ($assetids_string) and is_active=0 and cs_d_status=1 and cs_response_status=2";
	            $run_tbl_assets_query = mysqli_query($conn,$tbl_assets_query);
	            $num_rows_cs_asset_id = mysqli_num_rows($run_tbl_assets_query);

	            if($num_rows_cs_asset_id > 0){

	                $cs_asset_ids_fetch_data = mysqli_fetch_all($run_tbl_assets_query,MYSQLI_ASSOC);

	                $cs_asset_ids_array = [];
	                foreach($cs_asset_ids_fetch_data as $key=>$data){
	                    array_push($cs_asset_ids_array,"'".$data['cs_asset_id']."'");
	                }
	                $cs_asset_ids_string = implode(",",$cs_asset_ids_array);

	                $top_mood_cs_asset_ids_array = [];
	                foreach($main_3_aggr_mood_tag_data as $key=>$value){
	                    $tag_id = $value['tag_id'];
	                    $tag_name = $value['tag_name'];
	                    $tbl_asset_processed_amp_main_mood_tag_data_query = "SELECT * FROM `tbl_asset_processed_amp_main_mood_tag_data` WHERE asset_id in ($cs_asset_ids_string) and amp_main_mood_tag ='$tag_id' and is_active=0 ORDER by amp_main_mood_tag_value DESC LIMIT 1";
	                    $run_tbl_asset_processed_amp_main_mood_tag_data_query = mysqli_query($conn,$tbl_asset_processed_amp_main_mood_tag_data_query);
	                    $tbl_apammtd_fetch_data = mysqli_fetch_assoc($run_tbl_asset_processed_amp_main_mood_tag_data_query);
	                    $top_mood_cs_asset_ids_array[$tag_name]=$tbl_apammtd_fetch_data['asset_id'];
	                }
	               
	                //**************************get video_id from tbl_social_spyder_graph_meta_data using  tbl_assets id**************************//
	                $top_3_mood_videos_id = [];
	                foreach($top_mood_cs_asset_ids_array as $key=>$value){
	                    $meta_data_mood_videoid_query = "SELECT video_id FROM `tbl_social_spyder_graph_meta_data` LEFT JOIN  tbl_assets on tbl_social_spyder_graph_meta_data.asset_id=tbl_assets.id 
	                                                        WHERE cs_asset_id ='$value' and tbl_assets.is_active=0 and cs_d_status=1 and cs_response_status=2 and tbl_social_spyder_graph_meta_data.is_active=0
	                                                        and tbl_social_spyder_graph_meta_data.cs_status=1 and process_type='youtube' and cv_id=".$cv_id;
	                    $run_meta_data_mood_videoid_query = mysqli_query($conn,$meta_data_mood_videoid_query);
	                    if(mysqli_num_rows($run_tbl_assets_query) > 0){
	                        $meta_data_mood_videoids_fetch_data = mysqli_fetch_assoc($run_meta_data_mood_videoid_query);
	                        $top_3_mood_videos_id[$key]=$meta_data_mood_videoids_fetch_data['video_id'];
	                    }
	                }

	            }else{
	                error_log("no cs_asset_id data availabe in tbl_assets for cv_id == ".$cv_id." and asset_id==".$assetids_string);
	                echo "no cs_asset_id data availabe in tbl_assets for cv_id == ".$cv_id." and asset_id==".$assetids_string;
	                return 1;
	            }
	        }else{
	            error_log("no  data availabe in tbl_social_spyder_graph_meta_data for cv_id == ".$cv_id);
	            echo "no  data availabe in tbl_social_spyder_graph_meta_data for cv_id == ".$cv_id;
	            return 1;
	        }
	    }else{
	        error_log("no data availabe in tbl_social_media_aggr_mood_graph_data for cv_id == ".$cv_id);
	        echo "no data availabe in tbl_social_media_aggr_mood_graph_data for cv_id == ".$cv_id;
	        return 1;
	    }

	    //**************************top 3 genre videos**************************//


	    $genre_query = "SELECT * FROM `tbl_social_media_aggr_genre_graph_data` WHERE cv_id= ".$cv_id." and is_active=0 ORDER BY lbl_value DESC LIMIT 3";
	    $run_genre_query = mysqli_query($conn,$genre_query);
	    $num_rows_genre_query  = mysqli_num_rows($run_genre_query);
	    $main_3_aggr_genre_tag_data = mysqli_fetch_all($run_genre_query,MYSQLI_ASSOC);

	    if($num_rows_genre_query > 0){

	        //**************************get  asset_id from tbl_social_spyder_graph_meta_data**************************//
	        $meta_data_query = "SELECT asset_id FROM `tbl_social_spyder_graph_meta_data` WHERE cv_id=".$cv_id." AND process_type='youtube' and is_active=0 AND cs_status=1";
	        $run_meta_data_query = mysqli_query($conn,$meta_data_query);
	        $num_rows_meta_data = mysqli_num_rows($run_meta_data_query);
	        if($num_rows_meta_data > 0){

	            $fetch_data = mysqli_fetch_all($run_meta_data_query,MYSQLI_ASSOC);

	            $assetids_array = [];
	            foreach($fetch_data as $key=>$data){
	                array_push($assetids_array,$data['asset_id']);
	            }
	            $assetids_string = implode(",",$assetids_array);

	            //**************************get cs_asset_id from tbl_assets**************************//
	            $tbl_assets_query = "SELECT cs_asset_id FROM `tbl_assets` WHERE id in ($assetids_string) and is_active=0 and cs_d_status=1 and cs_response_status=2";
	            $run_tbl_assets_query = mysqli_query($conn,$tbl_assets_query);
	            $num_rows_cs_asset_id = mysqli_num_rows($run_tbl_assets_query);
	            if($num_rows_cs_asset_id > 0){

	                $cs_asset_ids_fetch_data = mysqli_fetch_all($run_tbl_assets_query,MYSQLI_ASSOC);

	                $cs_asset_ids_array = [];
	                foreach($cs_asset_ids_fetch_data as $key=>$data){
	                    array_push($cs_asset_ids_array,"'".$data['cs_asset_id']."'");
	                }
	                $cs_asset_ids_string = implode(",",$cs_asset_ids_array);

	                $top_genre_cs_asset_ids_array = [];
	                foreach($main_3_aggr_genre_tag_data as $key=>$value){
	                    $lbl_name = $value['lbl_name'];
	                    $tbl_asset_genre_data = "SELECT * FROM `tbl_asset_genre_data` WHERE asset_id in ($cs_asset_ids_string) and tag ='".$lbl_name."' and is_active=0 ORDER by tag_value DESC LIMIT 1";
	                    $run_tbl_asset_genre_data = mysqli_query($conn,$tbl_asset_genre_data);
	                    $tbl_agd_fetch_data = mysqli_fetch_assoc($run_tbl_asset_genre_data);
	                    $top_genre_cs_asset_ids_array[$lbl_name]=$tbl_agd_fetch_data['asset_id'];
	                }
	                
	                //**************************get  video_id from tbl_social_spyder_graph_meta_data using  tbl_assets id**************************//
	                
	                $top_3_genre_videos_id = [];
	                foreach($top_genre_cs_asset_ids_array as $key=>$value){
	                    $meta_data_genre_videoid_query = "SELECT video_id FROM `tbl_social_spyder_graph_meta_data` LEFT JOIN  tbl_assets on tbl_social_spyder_graph_meta_data.asset_id=tbl_assets.id 
	                                                    WHERE cs_asset_id='$value' and tbl_assets.is_active=0 and cs_d_status=1 and cs_response_status=2 and tbl_social_spyder_graph_meta_data.is_active=0
	                                                    and tbl_social_spyder_graph_meta_data.cs_status=1 and process_type='youtube' and cv_id=".$cv_id;
	                    $run_meta_data_genre_videoid_query = mysqli_query($conn,$meta_data_genre_videoid_query);
	                    if(mysqli_num_rows($run_meta_data_genre_videoid_query) > 0){
	                        $meta_data_genre_videoids_fetch_data = mysqli_fetch_assoc($run_meta_data_genre_videoid_query);
	                        $top_3_genre_videos_id[$key]=$meta_data_genre_videoids_fetch_data['video_id'];
	                    }
	                }

	            }else{
	                error_log("no cs_asset_id data availabe in tbl_assets for cv_id == ".$cv_id." and asset_id==".$assetids_string);
	                echo "no cs_asset_id data availabe in tbl_assets for cv_id == ".$cv_id." and asset_id==".$assetids_string;
	                return 1;
	            }

	        }else{
	            error_log("no  data availabe in tbl_social_spyder_graph_meta_data for cv_id == ".$cv_id);
	            echo "no  data availabe in tbl_social_spyder_graph_meta_data for cv_id == ".$cv_id;
	            return 1;
	        }

	    }else{
	        error_log("no data availabe in tbl_social_media_aggr_genre_graph_data for cv_id == ".$cv_id);
	        echo "no data availabe in tbl_social_media_aggr_genre_graph_data for cv_id == ".$cv_id;
	        return 1;
	    } 

	    $mgytv_ins_qry = '';

	    if($top_3_mood_videos_id > 0 || $top_3_genre_videos_id > 0){

	        $chk_mgytv_qry = "SELECT * FROM `tbl_mood_genre_yt_videos` WHERE cv_id=".$cv_id;
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


	        foreach($top_3_mood_videos_id as $mkey=>$value)
	        {
	            //echo $top_3_mood_video_id_data_result_row['video_id']."<br>";
	            $mapi_key = "AIzaSyCftMwHzQQ9Tt5KNtbWtAzf9k-2V8WIXU8";
	            $mvideo_id = $value;
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
	            $mgytv_ins_qry .= "'".$mkey."$|$".$mvideo_id."','".mysqli_real_escape_string($conn,str_replace("'","\'",$mtitle))."',";

	        }


	        foreach($top_3_genre_videos_id as $gkey=>$value){

	            //echo $top_3_genre_video_id_data_result_row['video_id']."<br>";
	            $gapi_key = "AIzaSyCftMwHzQQ9Tt5KNtbWtAzf9k-2V8WIXU8";
	            $gvideo_id = $value;
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
	            $mgytv_ins_qry .= "'".$gkey."$|$".$gvideo_id."','".mysqli_real_escape_string($conn,str_replace("'","\'",$gtitle))."',"; 

	        }
	    }else{
	        error_log("top 3 mood or genre video data not found for cv_id - ".$cv_id);
	        echo "top 3 mood or genre video data not found for cv_id - ".$cv_id;
	        return 1;
	    }

	    if($mgytv_ins_qry != ''){
	        $final_mgytv_ins_qry = rtrim($mgytv_ins_qry,",").")";
	        //echo $final_mgytv_ins_qry;
	        if($conn->query($final_mgytv_ins_qry))
	        {
	            error_log("top 3 mood and genre video id and title of youtube inserted into tbl_mood_genre_yt_videos for cv_id - ".$cv_id);
	            echo "top 3 mood and genre video id and title of youtube inserted into tbl_mood_genre_yt_videos for cv_id - ".$cv_id."<br><br>";
	            return 0;
	        }
	        else
	        {
	            error_log("someting went wrong while inserting top 3 mood and genre video id and title of youtube into tbl_mood_genre_yt_videos for cv_id - ".$cv_id);
	            echo "someting went wrong while inserting top 3 mood and genre video id and title of youtube into tbl_mood_genre_yt_videos for cv_id - ".$cv_id."<br><br>";
	            return 1;
	        }
	    }
	}
	else
	{
		error_log("asset count not matched");
		echo "asset count not matched<br><br>";
		return 0;
	}
	echo "<br>|||||||||||||||||||||||||||||||||||||||||||||||||||<br>";
}

function send_voiceover_degree_asset_to_cs($conn,$asset_processed_cyanite_data_id,$tbl_assets_id,$asset_name,$trim,$start_time,$end_time,$path,$transaction_token)
{      
       
    //$url = "http://localhost:7474/php_script/voiceover_degree/url.php"; // LOCAL SERVER
    // $url = "https://taxonomy.logthis.in/apis/send_asset_splitter_content.php"; // TEST SERVER
    $url = "https://taxonomy.sonic-hub.com/apis/send_asset_splitter_content.php"; // LIVE SERVER
    $data = array(
        'transaction_token' => $transaction_token,
        'name' => $asset_name,
        "type" => 1,
        "trim" => $trim,
        "start_time" => $start_time,
        "end_time" => $end_time,
        "call_back_url" => "https://sonicradar.sonic-hub.com/cs_apis/get_voiceover_degree_asset_data_callback.php",
        "link" => $path
    );
    $postParameter = json_encode($data);
    $curlResponse = curl_call_api($url,$postParameter);
    error_log("send_voiceover_degree_asset_to_cs curl response == ".$curlResponse);
    // echo  "<br> send_voiceover_degree_asset_to_cs curl response == ".$curlResponse; 
    $response = json_decode($curlResponse,true);  //{"msg":"0","data":{"splitter_id":"058e6338-5d35-11ee-924e-0cc47a8613ce"}}
    if(count($response) > 0){
        $msg = $response['msg'];
        if($msg == 0 || $msg == '0'){
            $splitter_id = $response['data']['splitter_id'];
            
            if($splitter_id != ""){
                $insert_query = "INSERT INTO  tbl_asset_splitter (`cs_splitter_id`,`old_asset_id`) VALUES ('$splitter_id','$tbl_assets_id')";
                // $insert_query = "INSERT INTO  tbl_asset_splitter (`cs_splitter_id`) VALUES ('$splitter_id')";
                $result = $conn->query($insert_query);
                $tbl_insert_id = mysqli_insert_id($conn);
                if($result === true){
                    echo "<br>cs_splitter_id successfully inserted into tbl_asset_splitter == '".$splitter_id."'";
                    error_log("\n cs_splitter_id successfully inserted into tbl_asset_splitter == '".$splitter_id."'");

                    $meta_data_updt_qry = "UPDATE `tbl_social_spyder_graph_meta_data` SET `skip_for_split` = 2 WHERE `id` = ".$tbl_assets_id;
                    error_log("\n meta_data_updt_qry => ".$meta_data_updt_qry);
                    $meta_data_updt_qry_res = $conn->query($meta_data_updt_qry);

                    if($meta_data_updt_qry_res == true)
                    {
                    	error_log("\n skip_for_split = 2 Successfully Updated Into tbl_social_spyder_graph_meta_data where id = ".$tbl_assets_id);
                    	echo "<br> skip_for_split = 2 Successfully Updated Into tbl_social_spyder_graph_meta_data where id = ".$tbl_assets_id;
                    }
                    else
                    {
                    	error_log("\n Something Went Wrong While Updating skip_for_split = 2 Into tbl_social_spyder_graph_meta_data where id = ".$tbl_assets_id);
                    	echo "<br> Something Went Wrong While Updating skip_for_split = 2 Into tbl_social_spyder_graph_meta_data where id = ".$tbl_assets_id;
                    }                 
                }
                else
                {
                	error_log("Something went wrong while inserting splitter id '".$splitter_id."' into  asset splitter table");
                }
            }else{
                error_log("\n splitter_id not found");
                echo("splitter_id not found");
            }
        }else{
            error_log("\n the transaction token is expired and you need to call check_and_get_access_token API again");
            echo "<br> the transaction token is expired and you need to call check_and_get_access_token API again";
            
            $query_tbl_cs_access_token_update = "UPDATE tbl_cs_access_token SET is_active=1";
            error_log($query_tbl_cs_access_token_update);
            $result = $conn->query($query_tbl_cs_access_token_update);
            if($result){
                $transaction_token = check_and_get_access_token($conn);
            }else{
                error_log("\n something went wrong whlie updating tbl_cs_access_token");
                echo("something went wrong whlie updating tbl_cs_access_token");
            }
            if($transaction_token != ""){
                send_voiceover_degree_asset_to_cs($conn,$asset_processed_cyanite_data_id,$tbl_assets_id,$asset_name,$trim,$start_time,$end_time,$path,$transaction_token);
            }else{
                echo "<br> transaction_token failed while recalling when the transaction token is expired and you need to call get_access_token API again";
                error_log("\n transaction_token failed while recalling when the transaction token is expired and you need to call get_access_token API again");
            }
        }

    }else{
        error_log("\n Someting went wrong with send_voiceover_degree_asset_to_cs curl response" );
	    echo "<br> Someting went wrong with send_voiceover_degree_asset_to_cs curl response";  
    }
}

function extract_voiceover_degree_asset_splitter_result($conn,$curlResponse){
    error_log("get_voiceover_degree_asset_splitter_result curl response == ".$curlResponse);
    echo  "<br> get_voiceover_degree_asset_splitter_result curl response == ".$curlResponse; 
    $response = json_decode($curlResponse,true);
    if(count($response) > 0){
        $msg = $response['msg'];
        $cs_splitter_id= $response['data']['splitter_id'];
        if($msg == 0 || $msg == '0'){
            $asset_download_status = $response['data']['asset_download_status'];
            $asset_download_status_date_time = $response['data']['asset_download_status_date_time'];
            if($asset_download_status == 1 || $asset_download_status == '1'){
                $status = $response['data']['result']['status'];
                if($status == 2){
                    $voice_path = $response['data']['result']['splitter_data']['voice_path'];
                    $music_path = $response['data']['result']['splitter_data']['music_path'];
                    $cs_status_datetime = date('Y-m-d H:i:s');
                    $tbl_asset_splitter_update_query = "UPDATE tbl_asset_splitter SET `vocal_path` = '".$voice_path."',`instrumental_path` = '".$music_path."',`cs_status` = '1',`cs_status_datetime`='".$cs_status_datetime."' WHERE cs_splitter_id='".$cs_splitter_id."'";
                    $tbl_asset_splitter_update_query_result = $conn->query($tbl_asset_splitter_update_query);

                    if($tbl_asset_splitter_update_query_result == true){
                        error_log("\n Voice Path And Instrumental Path Successfully Updated Into tbl_asset_splitter where cs_splitter_id = ".$cs_splitter_id);
                        echo "<br> Voice Path And Instrumental Path Successfully Updated Into tbl_asset_splitter where cs_splitter_id = ".$cs_splitter_id;

                        $get_meta_id_qry = "SELECT * FROM `tbl_asset_splitter` WHERE cs_splitter_id = '".$cs_splitter_id."'";
	                    $get_meta_id_qry_res = $conn->query($get_meta_id_qry);
	                    $get_meta_id_qry_res_row = $get_meta_id_qry_res->fetch_assoc();
	                    $meta_id = $get_meta_id_qry_res_row['old_asset_id'];

	                    $meta_data_updt_qry = "UPDATE `tbl_social_spyder_graph_meta_data` SET `path` = '".$music_path."', `is_active` = 0 WHERE `id` = ".$meta_id;
	                    $meta_data_updt_qry_res = $conn->query($meta_data_updt_qry);

	                    if($meta_data_updt_qry_res == true)
	                    {
	                    	error_log("\n Instrumental Path and is_active = 0 Successfully Updated Into tbl_social_spyder_graph_meta_data where id = ".$meta_id);
                        	echo "<br> Instrumental Path and is_active = 0 Successfully Updated Into tbl_social_spyder_graph_meta_data where id = ".$meta_id;
	                    }
	                    else
	                    {
	                    	error_log("\n Something Went Wrong While Updating Instrumental Path and is_active = 0 Into tbl_social_spyder_graph_meta_data where id = ".$meta_id);
                        	echo "<br> Something Went Wrong While Updating Instrumental Path and is_active = 0 Into tbl_social_spyder_graph_meta_data where id = ".$meta_id;
	                    }
                    }else{
                        error_log("\n Something Went Wrong While Updating  Voice Path And Instrumental Path  Into tbl_asset_splitter where cs_splitter_id = ".$cs_splitter_id);
                        echo "<br> Something Went Wrong While Updating  Voice Path And Instrumental Path  Into tbl_asset_splitter where cs_splitter_id = ".$cs_splitter_id;
                    }
                }else if($status == 3){
                    $tbl_asset_splitter_processing_failed_update_query = "UPDATE tbl_asset_splitter SET `cs_status` = '3' WHERE cs_splitter_id='".$cs_splitter_id."'";
                    $tbl_asset_splitter_processing_failed_update_query_result = $conn->query($tbl_asset_splitter_processing_failed_update_query);
                    if($tbl_asset_splitter_processing_failed_update_query_result === true){
                        error_log("\n asset splitter processing is failed update for tbl_asset_splitter cs_splitter_id ='".$cs_splitter_id."'");
                        echo "<br> asset splitter processing is failed update for tbl_asset_splitter cs_splitter_id '".$cs_splitter_id."'";
                    }else{
                        error_log("\n Something Went Wrong With processing is failed update for tbl_asset_splitter cs_splitter_id ='".$cs_splitter_id."'");
                        echo "<br> Something Went Wrong With processing is failed update for tbl_asset_splitter cs_splitter_id '".$cs_splitter_id."'";
                    }
                }else{
                    error_log("\n get Asset Splitter result status != 2 for tbl_asset_splitter cs_splitter_id = ".$cs_splitter_id);
                    echo "<br> get Asset Splitter result status != 2 for tbl_asset_splitter cs_splitter_id = ".$cs_splitter_id;
                }
            }else{
                error_log("\n get Asset Splitter result asset_download_status != 1 for tbl_asset_splitter cs_splitter_id = '".$cs_splitter_id."' ");
                echo "<br> get Asset Splitter result asset_download_status != 1 for tbl_asset_splitter cs_splitter_id = '".$cs_splitter_id."'";
            }
        }

    }else{
        error_log("\n Someting went wrong with get_voiceover_degree_asset_splitter_result curl response" );
        echo "<br> Someting went wrong with get_voiceover_degree_asset_splitter_result curl response";  
    }

}
?>
