<?php

require_once('sonic/sonic_functions.php');

class db_dump
{
	function fetch_and_dump_analised_record($track_id, $brand_id, $start_date, $end_date, $matadata_id, $c_date,$chnl_id,$process_type){		
		$sonic_functions = new sonic_functions();
		error_log("page : [db_dump] : function [fetch_and_dump_analised_record] for  cv id:".$brand_id." and channel id:".$chnl_id." and process type:".$process_type." and track id:".$track_id);
		try{
			include '../cynite_apis.php';

			$cynite_api = new CyaniteAI();

			$json_str = json_decode($cynite_api->Fetch_AnyalisedData($track_id));
			echo "response-------------------";
			print_r($json_str);

			$analysis_status = $json_str ->data ->libraryTrack ->audioAnalysisV6 ->__typename;

			if ($analysis_status == "AudioAnalysisV6Finished") {

				$brand_id 			= $brand_id;
				$LTrack_id 			= $json_str ->data ->libraryTrack ->id;
				$mood 				= json_encode( $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->mood );
				$moodtags 			= json_encode( $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->moodTags );
				$genre 				= json_encode( $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->genre );
				$genretags 			= json_encode( $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->genreTags );
				$voice 				= json_encode( $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->voice );
				$valence 			= $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->valence;
				$arousal 			= $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->arousal;
				$bpm 				= $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->bpm;
				$key 				= $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->key;
				$segmentdetails 	= null;
				$cyanitejson 		= json_encode($json_str);
				$status 			= "synced";
				$version 			= "6";
				$start_date 		= $start_date;
				$end_date			= $end_date;

				$dbcon = include('config/config.php');
				$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);

				$sql = "INSERT INTO cyanite (`brand_id`, `LTrack_id`, `mood`, `moodtags`, `genre`, `genretags`, `voice`, `valence`, `arousal`, `bpm`, `key`, `segmentdetails`, `cyanitejson`, `status`, `version`, `start_date`, `end_date`,`c_date`,`chn_id`,`process_type`)
				VALUES (".$brand_id.",".$LTrack_id.",'".$mood."','".$moodtags."','".$genre."','".$genretags."','".$voice."','".$valence."','".$arousal."','".$bpm."','".$key."','".$segmentdetails."','".$cyanitejson."','".$status."',".$version.",'".$start_date."','".$end_date."','".$c_date."','".$chnl_id."','".$process_type."')";
				
				
				if ($conn->query($sql) === TRUE) {

					$return_data = "go";
					$obj = new db_dump();
					$obj->update_analysis_status($matadata_id);
					error_log("page : [db_dump] : function [fetch_and_dump_analised_record] : Message : Data inserted into cyanite table for cv id:".$brand_id." and channel id:".$chnl_id." and process type:".$process_type." and track id:".$LTrack_id);

				} else {
					$return_data = "stop";
					error_log("page : [db_dump] : function [fetch_and_dump_analised_record] : error : cyanite table insertion error for cv id:".$brand_id." and channel id:".$chnl_id." and process type:".$process_type." and track id:".$LTrack_id);
				}
			}
			
			$conn->close();

			return $return_data;
		}
		catch(Exception $e)
		{
			error_log("page : [db_dump] : function [fetch_and_dump_analised_record] : error : ".$e->getMessage());
			error_log("page : [db_dump] : function [fetch_and_dump_analised_record] : analysis_status from cyanite : ".$analysis_status);
			$sonic_functions->trigger_log_email("db_dump","fetch_and_dump_analised_record",$e->getMessage());
		}
		
	}	

	

	function extract_mood_and_genere($brand_id, $brand_process_type){
		$sonic_functions = new sonic_functions();
		error_log("page : [db_dump] : function [extract_mood_and_genere] for cv id:".$brand_id." and process_type:".$brand_process_type);
		try{

			switch($brand_process_type){
				case 'youtube':
					$table_name_id = "16";
					$col_name = "yt";
					break;
				case 'instagram':
					$table_name_id = "17";
					$col_name = "ig";
					break;
				case 'tiktok':
					$table_name_id = "18";
					$col_name = "tt";
					break;
				case 'twitter':
					$table_name_id = "19";
					$col_name = "twt";
					break;
			}

			$dbcon = include('config/config.php');
			$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);	
			
			$sql_paras = "SELECT 
			cyanite.LTrack_id,
			cyanite.brand_id,
			json_extract(cyanite.mood, '$.aggressive') AS aggressive,
			json_extract(cyanite.mood, '$.calm') AS calm,
			json_extract(cyanite.mood, '$.chilled') AS chilled,
			json_extract(cyanite.mood, '$.dark') AS dark,
			json_extract(cyanite.mood, '$.energetic') AS energetic,
			json_extract(cyanite.mood, '$.epic') AS epic,
			json_extract(cyanite.mood, '$.happy') AS happy,
			json_extract(cyanite.mood, '$.romantic') AS romantic,
			json_extract(cyanite.mood, '$.sad') AS sad,
			json_extract(cyanite.mood, '$.scary') AS scary,
			json_extract(cyanite.mood, '$.sexy') AS sexy,
			json_extract(cyanite.mood, '$.ethereal') AS ethereal,
			json_extract(cyanite.mood, '$.uplifting') AS uplifting,
			json_extract(cyanite.genre, '$.ambient') AS ambient,
			json_extract(cyanite.genre, '$.blues') AS blues,
			json_extract(cyanite.genre, '$.classical') AS classical,
			json_extract(cyanite.genre, '$.country') AS country,
			json_extract(cyanite.genre, '$.electronicDance') AS electronicDance,
			json_extract(cyanite.genre, '$.folk') AS folk,
			json_extract(cyanite.genre, '$.indieAlternative') AS indieAlternative,
			json_extract(cyanite.genre, '$.jazz') AS jazz,
			json_extract(cyanite.genre, '$.latin') AS latin,
			json_extract(cyanite.genre, '$.metal') AS metal,
			json_extract(cyanite.genre, '$.pop') AS pop,
			json_extract(cyanite.genre, '$.punk') AS punk,
			json_extract(cyanite.genre, '$.rapHipHop') AS rapHipHop,
			json_extract(cyanite.genre, '$.reggae') AS reggae,
			json_extract(cyanite.genre, '$.rnb') AS rnb,
			json_extract(cyanite.genre, '$.rock') AS rock,
			json_extract(cyanite.genre, '$.singerSongwriter') AS singerSongwriter FROM cyanite where cyanite.brand_id=".$brand_id." and cyanite.process_type='".$brand_process_type."' and cyanite.is_active=0";
			//error_log("page : [db_dump] : function [extract_mood_and_genere] sql_paras: ".$sql_paras);
			$result = $conn->query($sql_paras);
			$track_id_array = array();
			if ($result->num_rows > 0) {
				error_log("----------------------------------------------------------------------------------------------------------------------------------------------------");
				$ins_mood_temp_query = "insert into tbl_cv_block_".$table_name_id."_mood_graph_temp_data (cv_id,track_id,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values ";
				$ins_genre_temp_query = "insert into tbl_cv_block_".$table_name_id."_genre_graph_temp_data (cv_id,track_id,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values ";
				while($row = $result->fetch_assoc()) {
					$LTrack_id = $row["LTrack_id"];
					error_log("page : [db_dump] : function [extract_mood_and_genere] track id extracting : ".$LTrack_id);
					//error_log("page : [db_dump] : function [extract_mood_and_genere] generating mood insert query for : ".$LTrack_id);
					
					$ins_mood_temp_query .= "(".$brand_id.",".$row['LTrack_id'].",".$row['aggressive'].",".$row['calm'].",".$row['chilled'].",".$row['dark'].",".$row['energetic'].",".$row['epic'].",".$row['happy'].",".$row['romantic'].",".$row['sad'].",".$row['scary'].",".$row['sexy'].",".$row['ethereal'].",".$row['uplifting']."),";
					
					//error_log("page : [db_dump] : function [extract_mood_and_genere] generating genre insert query for : ".$LTrack_id);
					
					$ins_genre_temp_query .= "(".$brand_id.",".$row['LTrack_id'].",".$row['ambient'].",".$row['blues'].",".$row['classical'].",".$row['country'].",".$row['electronicDance'].",".$row['folk'].",".$row['indieAlternative'].",".$row['jazz'].",".$row['latin'].",".$row['metal'].",".$row['pop'].",".$row['punk'].",".$row['rapHipHop'].",".$row['reggae'].",".$row['rnb'].",".$row['rock'].",".$row['singerSongwriter']."),";
					
					array_push($track_id_array,$LTrack_id);
						
				}
				$multi_ins_mood_temp_query = rtrim($ins_mood_temp_query,",");
				//error_log("page : [db_dump] : function [extract_mood_and_genere] generated multi mood insert query : ".$multi_ins_mood_temp_query." for : ".$brand_id);
				error_log("page : [db_dump] : function [extract_mood_and_genere] multi mood insert query  for cv: ".$brand_id." is generated");
				
				$multi_ins_genre_temp_query = rtrim($ins_genre_temp_query,",");
				//error_log("page : [db_dump] : function [extract_mood_and_genere] generated multi genre insert query : ".$multi_ins_genre_temp_query." for : ".$brand_id);
				error_log("page : [db_dump] : function [extract_mood_and_genere] multi genre insert query  for cv: ".$brand_id." is generated");
				
				if($conn->query($multi_ins_mood_temp_query)){
					// update status 3
					$sql1 = "update tbl_social_spyder_graph_meta_data set status=3 where track_id IN (".implode(",",$track_id_array).")";
					//error_log("page : [db_dump] : function [extract_mood_and_genere] update status to 3 at tbl_social_spyder_graph_meta_data of trackID : ".$LTrack_id);
					error_log("page : [db_dump] : function [extract_mood_and_genere] status updated to 3 in tbl_social_spyder_graph_meta_data for tracks :".implode(",",$track_id_array));
					$conn->query($sql1);						
				}
				
				if($conn->query($multi_ins_genre_temp_query)){
					// update status 3
					$sql2 = "update tbl_social_spyder_graph_meta_data set status=4 where track_id IN (".implode(",",$track_id_array).")";
					//error_log("page : [db_dump] : function [extract_mood_and_genere] update status to 3 at tbl_social_spyder_graph_meta_data of trackID : ".$LTrack_id);
					error_log("page : [db_dump] : function [extract_mood_and_genere] status updated to 4 in tbl_social_spyder_graph_meta_data for tracks :".implode(",",$track_id_array));
					if($conn->query($sql2))
					{
						$chk_query = "select * from tbl_cv_block_".$table_name_id."_mood_graph_data where cv_id =".$brand_id;
						$chk_query_res = $conn->query($chk_query);
						if($chk_query_res->num_rows > 0)
						{
							$conn->query("DELETE FROM tbl_cv_block_".$table_name_id."_mood_graph_data WHERE cv_id=".$brand_id);
							error_log("page : [db_dump] : function [extract_mood_and_genere] exisiting cv_id=".$brand_id." data is deleted from tbl_cv_block_".$table_name_id."_mood_graph_data");
						}
						
						$sql_mood_avg_query = "SELECT round(AVG(`aggressive`),2) as aggressive, round(AVG(`calm`),2) as calm, round(AVG(`chilled`),2) as chilled, round(AVG(`dark`),2) as dark, round(AVG(`energetic`),2) as energetic, round(AVG(`epic`),2) as epic, round(AVG(`happy`),2) as happy, round(AVG(`romantic`),2) as romantic, round(AVG(`sad`),2) as sad, round(AVG(`scary`),2) as scary, round(AVG(`sexy`),2) as sexy, round(AVG(`ethereal`),2) as ethereal, round(AVG(`uplifting`),2) as uplifting FROM `tbl_cv_block_".$table_name_id."_mood_graph_temp_data` WHERE `cv_id` =".$brand_id;
						error_log("sql_mood_avg_query:".$sql_mood_avg_query);
						$result = $conn->query($sql_mood_avg_query);
						if ($result->num_rows > 0) {
				  			while($row = $result->fetch_assoc()) {
								$s1 = "insert into tbl_cv_block_".$table_name_id."_mood_graph_data(cv_id,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values(".$brand_id.",".$row['aggressive'].",".$row['calm'].",".$row['chilled'].",".$row['dark'].",".$row['energetic'].",".$row['epic'].",".$row['happy'].",".$row['romantic'].",".$row['sad'].",".$row['scary'].",".$row['sexy'].",".$row['ethereal'].",".$row['uplifting'].")";							
				
								error_log("tbl_cv_block_".$table_name_id."_mood_graph_data:".$s1);
								if($conn->query($s1))
								{
									$conn->query("DELETE FROM tbl_cv_block_".$table_name_id."_mood_graph_temp_data WHERE cv_id=".$brand_id);
									error_log("page : [db_dump] : function [extract_mood_and_genere] cv_id=".$brand_id." data is deleted from tbl_cv_block_".$table_name_id."_mood_graph_temp_data");
								}
							}
						}
						
						$chk_query = "select * from tbl_cv_block_".$table_name_id."_genre_graph_data where cv_id =".$brand_id;
						$chk_query_res = $conn->query($chk_query);
						if($chk_query_res->num_rows > 0)
						{
							$conn->query("DELETE FROM tbl_cv_block_".$table_name_id."_genre_graph_data WHERE cv_id=".$brand_id);
							error_log("page : [db_dump] : function [extract_mood_and_genere] exisiting cv_id=".$brand_id." data is deleted from tbl_cv_block_".$table_name_id."_genre_graph_data");
						}
						
						$sql_genre_avg_query = "SELECT round(AVG(`ambient`),2) as ambient, round(AVG(`blues`),2) as blues, round(AVG(`classical`),2) as classical, round(AVG(`country`),2) as country, round(AVG(`electronicDance`),2) as electronicDance, round(AVG(`folk`),2) as folk, round(AVG(`indieAlternative`),2) as indieAlternative, round(AVG(`jazz`),2) as jazz, round(AVG(`latin`),2) as latin, round(AVG(`metal`),2) as metal, round(AVG(`pop`),2) as pop, round(AVG(`punk`),2) as punk, round(AVG(`rapHipHop`),2) as rapHipHop, round(AVG(`reggae`),2) as reggae, round(AVG(`rnb`),2) as rnb, round(AVG(`rock`),2) as rock, round(AVG(`singerSongwriter`),2) as singerSongwriter FROM `tbl_cv_block_".$table_name_id."_genre_graph_temp_data` WHERE `cv_id` =".$brand_id;
						error_log("sql_genre_avg_query:".$sql_genre_avg_query);
						$result = $conn->query($sql_genre_avg_query);
						if ($result->num_rows > 0) {
				  			while($row = $result->fetch_assoc()) {
								$s2 = "insert into tbl_cv_block_".$table_name_id."_genre_graph_data(cv_id,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values(".$brand_id.",".$row['ambient'].",".$row['blues'].",".$row['classical'].",".$row['country'].",".$row['electronicDance'].",".$row['folk'].",".$row['indieAlternative'].",".$row['jazz'].",".$row['latin'].",".$row['metal'].",".$row['pop'].",".$row['punk'].",".$row['rapHipHop'].",".$row['reggae'].",".$row['rnb'].",".$row['rock'].",".$row['singerSongwriter'].")";							
				
								error_log("tbl_cv_block_".$table_name_id."_genre_graph_data:".$s2);
								if($conn->query($s2))
								{
									$conn->query("DELETE FROM tbl_cv_block_".$table_name_id."_genre_graph_temp_data WHERE cv_id=".$brand_id);
									$conn->query("UPDATE `tbl_social_media_sync_process_data` SET `".$col_name."` = 2  WHERE `cv_id` = ".$brand_id);
									error_log("page : [db_dump] : function [extract_mood_and_genere] cv_id=".$brand_id." data is deleted from tbl_cv_block_".$table_name_id."_genre_graph_temp_data");
								}
							}
						}
						
					}						
				}
				error_log("page : [db_dump] : function [extract_mood_and_genere] : Message : Both Graphs Average Data inserted for cv id:".$brand_id);
				error_log("====================================================================================================================================================");
			}
		}
		catch(Exception $e)
		{
			error_log("page : [db_dump] : function [extract_mood_and_genere] : error : ".$e->getMessage());
			$sonic_functions->trigger_log_email("db_dump","extract_mood_and_genere",$e->getMessage());
		}
	}

	function update_crate_id_matadata_table($crate_id, $brand_id){
		$sonic_functions = new sonic_functions();
		try{
			$return_data = null;

			$dbcon = include('config/config.php');
			$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);	

			$sql = "update tbl_social_spyder_graph_meta_data set crate_id = ".$crate_id. " where cv_id = ".$brand_id;
			
			if ($conn->query($sql) === TRUE) {

				$return_data = $crate_id;
			} else {

				$return_data = 0;
			}

			$conn->close();

			return $return_data;
		}
		catch(Exception $e)
		{
			error_log("page : [db_dump] : function [update_crate_id_matadata_table] : error : ".$e->getMessage());
			$sonic_functions->trigger_log_email("db_dump","update_crate_id_matadata_table",$e->getMessage());
		}
		
	}

	function update_trackid_and_status($track_id, $id){
		$sonic_functions = new sonic_functions();
		try{		
			$return_data = null;

			$dbcon = include('config/config.php');
			$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);	

			$sql = "update tbl_social_spyder_graph_meta_data set track_id = ".$track_id.", status=1 where id=".$id;
			
			if ($conn->query($sql) === TRUE) {

				$return_data = 1; // success
				error_log("page : [db_dump] : function [update_trackid_and_status] : LTid : ".$track_id." finished !!");
			} else {

				$return_data = 0;
				error_log("page : [db_dump] : function [update_trackid_and_status] : error occured : LTid : ".$track_id);
			}

			$conn->close();

			return $return_data;
		}
		catch(Exception $e)
		{
			error_log("page : [db_dump] : function [update_trackid_and_status] : error : ".$e->getMessage());
			$sonic_functions->trigger_log_email("db_dump","update_trackid_and_status",$e->getMessage());
		}
	}

	function update_analysis_status($id){
		$sonic_functions = new sonic_functions();
		try{
			$return_data = null;

			$dbcon = include('config/config.php');
			$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);	

			$sql = "update tbl_social_spyder_graph_meta_data set status=2 where id=".$id;
			
			if ($conn->query($sql) === TRUE) {

				$return_data = 1; // success
			} else {

				$return_data = 0;
			}

			$conn->close();

			return $return_data;
		}
		catch(Exception $e)
		{
			error_log("page : [db_dump] : function [update_analysis_status] : error : ".$e->getMessage());
			$sonic_functions->trigger_log_email("db_dump","update_analysis_status",$e->getMessage());
		}
	}

	function old_cyanite_data_disable($cv_id,$c_date,$chnl_id,$process_type){
		$sonic_functions = new sonic_functions();
		try{
			$dbcon = include('config/config.php');
			$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);	
			
			// cdate is in filter to diable old records
			// so if process inteurrpt so next time we will disable old records only.
			// all channels of each brand will have same cdate
			$sql =  "update cyanite set is_active=1 where cv_id=".$cv_id." and c_date !='".$c_date."' and process_type ='".$process_type."'";
			$conn->query($sql);
		}
		catch(Exception $e)
		{
			error_log("page : [db_dump] : function [old_cyanite_data_disable] : error : ".$e->getMessage());
			$sonic_functions->trigger_log_email("db_dump","old_cyanite_data_disable",$e->getMessage());
		}
	}

	function aggr_of_aggr($cv_id){
		//echo ">> ".$cv_id;
		error_log("page : [db_dump] : function [aggr_of_aggr] for cv id:".$cv_id);
		$sonic_functions = new sonic_functions();
		
		try{
			
			$dbcon = include('config/config.php');
			$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);	
			
			
			$sql_paras = "SELECT 
			cyanite.LTrack_id,
			cyanite.brand_id,
			json_extract(cyanite.mood, '$.aggressive') AS aggressive,
			json_extract(cyanite.mood, '$.calm') AS calm,
			json_extract(cyanite.mood, '$.chilled') AS chilled,
			json_extract(cyanite.mood, '$.dark') AS dark,
			json_extract(cyanite.mood, '$.energetic') AS energetic,
			json_extract(cyanite.mood, '$.epic') AS epic,
			json_extract(cyanite.mood, '$.happy') AS happy,
			json_extract(cyanite.mood, '$.romantic') AS romantic,
			json_extract(cyanite.mood, '$.sad') AS sad,
			json_extract(cyanite.mood, '$.scary') AS scary,
			json_extract(cyanite.mood, '$.sexy') AS sexy,
			json_extract(cyanite.mood, '$.ethereal') AS ethereal,
			json_extract(cyanite.mood, '$.uplifting') AS uplifting,
			json_extract(cyanite.genre, '$.ambient') AS ambient,
			json_extract(cyanite.genre, '$.blues') AS blues,
			json_extract(cyanite.genre, '$.classical') AS classical,
			json_extract(cyanite.genre, '$.country') AS country,
			json_extract(cyanite.genre, '$.electronicDance') AS electronicDance,
			json_extract(cyanite.genre, '$.folk') AS folk,
			json_extract(cyanite.genre, '$.indieAlternative') AS indieAlternative,
			json_extract(cyanite.genre, '$.jazz') AS jazz,
			json_extract(cyanite.genre, '$.latin') AS latin,
			json_extract(cyanite.genre, '$.metal') AS metal,
			json_extract(cyanite.genre, '$.pop') AS pop,
			json_extract(cyanite.genre, '$.punk') AS punk,
			json_extract(cyanite.genre, '$.rapHipHop') AS rapHipHop,
			json_extract(cyanite.genre, '$.reggae') AS reggae,
			json_extract(cyanite.genre, '$.rnb') AS rnb,
			json_extract(cyanite.genre, '$.rock') AS rock,
			json_extract(cyanite.genre, '$.singerSongwriter') AS singerSongwriter FROM cyanite where cyanite.brand_id=".$cv_id." and cyanite.is_active=0";
	
			$result = $conn->query($sql_paras);
			$track_id_array = array();
			if ($result->num_rows > 0) {
				error_log("----------------------------------------------------------------------------------------------------------------------------------------------------");
				$ins_mood_temp_query = "insert into tbl_mood_aggr_graph_temp_data (cv_id,track_id,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values ";
				$ins_genre_temp_query = "insert into tbl_genre_aggr_graph_temp_data (cv_id,track_id,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values ";
				while($row = $result->fetch_assoc()) {
					$LTrack_id = $row["LTrack_id"];
					
					//error_log("page : [db_dump] : function [aggr_of_aggr] track id extracting : ".$LTrack_id);
					//error_log("page : [db_dump] : function [aggr_of_aggr] generating mood insert query for : ".$LTrack_id);
					
					$ins_mood_temp_query .= "(".$cv_id.",".$row['LTrack_id'].",".$row['aggressive'].",".$row['calm'].",".$row['chilled'].",".$row['dark'].",".$row['energetic'].",".$row['epic'].",".$row['happy'].",".$row['romantic'].",".$row['sad'].",".$row['scary'].",".$row['sexy'].",".$row['ethereal'].",".$row['uplifting']."),";
					
					//error_log("page : [db_dump] : function [aggr_of_aggr] generating genre insert query for : ".$LTrack_id);
					
					$ins_genre_temp_query .= "(".$cv_id.",".$row['LTrack_id'].",".$row['ambient'].",".$row['blues'].",".$row['classical'].",".$row['country'].",".$row['electronicDance'].",".$row['folk'].",".$row['indieAlternative'].",".$row['jazz'].",".$row['latin'].",".$row['metal'].",".$row['pop'].",".$row['punk'].",".$row['rapHipHop'].",".$row['reggae'].",".$row['rnb'].",".$row['rock'].",".$row['singerSongwriter']."),";
					
					array_push($track_id_array,$LTrack_id);
						
				}
				
				$multi_ins_mood_temp_query = rtrim($ins_mood_temp_query,",");
				//error_log("page : [db_dump] : function [aggr_of_aggr] generated multi mood insert query : ".$multi_ins_mood_temp_query." for : ".$cv_id);
				error_log("page : [db_dump] : function [aggr_of_aggr] multi mood insert query  for cv: ".$cv_id." is generated");
				
				$multi_ins_genre_temp_query = rtrim($ins_genre_temp_query,",");
				//error_log("page : [db_dump] : function [aggr_of_aggr] generated multi genre insert query : ".$multi_ins_genre_temp_query." for : ".$cv_id);
				error_log("page : [db_dump] : function [aggr_of_aggr] multi genre insert query  for cv: ".$cv_id." is generated");
				
				if($conn->query($multi_ins_mood_temp_query)){
					// update status 5
					$sql1 = "update tbl_social_spyder_graph_meta_data set status=5 where track_id IN (".implode(",",$track_id_array).")";
					//error_log("page : [db_dump] : function [aggr_of_aggr] update status to 5 at tbl_social_spyder_graph_meta_data of trackID : ".$LTrack_id);
					error_log("page : [db_dump] : function [aggr_of_aggr] status updated to 5 in tbl_social_spyder_graph_meta_data for tracks :".implode(",",$track_id_array));
					$conn->query($sql1);						
				}
				
				if($conn->query($multi_ins_genre_temp_query)){
					// update status 6
					$sql2 = "update tbl_social_spyder_graph_meta_data set status=6 where track_id IN (".implode(",",$track_id_array).")";
					//error_log("page : [db_dump] : function [extract_mood_and_genere] update status to 6 at tbl_social_spyder_graph_meta_data of trackID : ".$LTrack_id);
					error_log("page : [db_dump] : function [aggr_of_aggr] status updated to 6 in tbl_social_spyder_graph_meta_data for tracks :".implode(",",$track_id_array));
					if($conn->query($sql2))
					{
						$chk_query = "select * from tbl_mood_aggr_graph_data where cv_id =".$cv_id;
						$chk_query_res = $conn->query($chk_query);
						if($chk_query_res->num_rows > 0)
						{
							$conn->query("DELETE FROM tbl_mood_aggr_graph_data WHERE cv_id=".$cv_id);
							error_log("page : [db_dump] : function [aggr_of_aggr] exisiting cv_id=".$cv_id." data is deleted from tbl_mood_aggr_graph_data");
						}
						
						$sql_mood_avg_query = "SELECT round(AVG(`aggressive`),2) as aggressive, round(AVG(`calm`),2) as calm, round(AVG(`chilled`),2) as chilled, round(AVG(`dark`),2) as dark, round(AVG(`energetic`),2) as energetic, round(AVG(`epic`),2) as epic, round(AVG(`happy`),2) as happy, round(AVG(`romantic`),2) as romantic, round(AVG(`sad`),2) as sad, round(AVG(`scary`),2) as scary, round(AVG(`sexy`),2) as sexy, round(AVG(`ethereal`),2) as ethereal, round(AVG(`uplifting`),2) as uplifting FROM `tbl_mood_aggr_graph_temp_data` WHERE `cv_id` =".$cv_id;
						error_log("[db_dump] : function [aggr_of_aggr] sql_mood_avg_query:".$sql_mood_avg_query);
						$result = $conn->query($sql_mood_avg_query);
						if ($result->num_rows > 0) {
				  			while($row = $result->fetch_assoc()) {
								$s1 = "insert into tbl_mood_aggr_graph_data(cv_id,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values(".$cv_id.",".$row['aggressive'].",".$row['calm'].",".$row['chilled'].",".$row['dark'].",".$row['energetic'].",".$row['epic'].",".$row['happy'].",".$row['romantic'].",".$row['sad'].",".$row['scary'].",".$row['sexy'].",".$row['ethereal'].",".$row['uplifting'].")";							
				
								error_log("[db_dump] : function [aggr_of_aggr] tbl_mood_aggr_graph_data:".$s1);
								if($conn->query($s1))
								{
									$conn->query("DELETE FROM tbl_mood_aggr_graph_temp_data WHERE cv_id=".$cv_id);
									error_log("page : [db_dump] : function [extract_mood_and_genere] cv_id=".$cv_id." data is deleted from tbl_mood_aggr_graph_temp_data");
								}
							}
						}
						
						$chk_query = "select * from tbl_genre_aggr_graph_data where cv_id =".$cv_id;
						$chk_query_res = $conn->query($chk_query);
						if($chk_query_res->num_rows > 0)
						{
							$conn->query("DELETE FROM tbl_genre_aggr_graph_data WHERE cv_id=".$cv_id);
							error_log("page : [db_dump] : function [aggr_of_aggr] exisiting cv_id=".$cv_id." data is deleted from tbl_genre_aggr_graph_data");
						}
						
						$sql_genre_avg_query = "SELECT round(AVG(`ambient`),2) as ambient, round(AVG(`blues`),2) as blues, round(AVG(`classical`),2) as classical, round(AVG(`country`),2) as country, round(AVG(`electronicDance`),2) as electronicDance, round(AVG(`folk`),2) as folk, round(AVG(`indieAlternative`),2) as indieAlternative, round(AVG(`jazz`),2) as jazz, round(AVG(`latin`),2) as latin, round(AVG(`metal`),2) as metal, round(AVG(`pop`),2) as pop, round(AVG(`punk`),2) as punk, round(AVG(`rapHipHop`),2) as rapHipHop, round(AVG(`reggae`),2) as reggae, round(AVG(`rnb`),2) as rnb, round(AVG(`rock`),2) as rock, round(AVG(`singerSongwriter`),2) as singerSongwriter FROM `tbl_genre_aggr_graph_temp_data` WHERE `cv_id` =".$cv_id;
						error_log("[db_dump] : function [aggr_of_aggr] sql_genre_avg_query:".$sql_genre_avg_query);
						$result = $conn->query($sql_genre_avg_query);
						if ($result->num_rows > 0) {
				  			while($row = $result->fetch_assoc()) {
								$s2 = "insert into tbl_genre_aggr_graph_data(cv_id,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values(".$cv_id.",".$row['ambient'].",".$row['blues'].",".$row['classical'].",".$row['country'].",".$row['electronicDance'].",".$row['folk'].",".$row['indieAlternative'].",".$row['jazz'].",".$row['latin'].",".$row['metal'].",".$row['pop'].",".$row['punk'].",".$row['rapHipHop'].",".$row['reggae'].",".$row['rnb'].",".$row['rock'].",".$row['singerSongwriter'].")";							
				
								error_log("[db_dump] : function [aggr_of_aggr] tbl_genre_aggr_graph_data:".$s2);
								if($conn->query($s2))
								{
									$conn->query("DELETE FROM tbl_genre_aggr_graph_temp_data WHERE cv_id=".$cv_id);
									$conn->query("UPDATE `tbl_social_media_sync_process_data` SET `aggr` = 2  WHERE `cv_id` = ".$cv_id);

									//get and store top 3 mood and genre video id and title of youtube in tbl_mood_genre_yt_videos
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

									$cv_mood_aggr_graph_values_data_qry = "select `aggressive`,`calm`,`chilled`,`energetic`,`happy`,`romantic`,`sad`,`scary`,`sexy`,`ethereal`,`uplifting` from `tbl_mood_aggr_graph_data` where `cv_id` =".$cv_id." and `is_active`=0";
									$cv_mood_aggr_graph_values_data_qry_result = $conn->query($cv_mood_aggr_graph_values_data_qry);

									if ($cv_mood_aggr_graph_values_data_qry_result->num_rows > 0)
									{
									    $cv_mood_aggr_graph_values_data_array = [];
									    while($cv_mood_aggr_graph_values_data_qry_result_row = $cv_mood_aggr_graph_values_data_qry_result->fetch_assoc())
									    {
									        $cv_mood_aggr_graph_values_data_array['aggressive'] = $cv_mood_aggr_graph_values_data_qry_result_row['aggressive'];
									        $cv_mood_aggr_graph_values_data_array['calm'] = $cv_mood_aggr_graph_values_data_qry_result_row['calm'];
									        $cv_mood_aggr_graph_values_data_array['chilled'] = $cv_mood_aggr_graph_values_data_qry_result_row['chilled'];
									        $cv_mood_aggr_graph_values_data_array['energetic'] = $cv_mood_aggr_graph_values_data_qry_result_row['energetic'];
									        $cv_mood_aggr_graph_values_data_array['happy'] = $cv_mood_aggr_graph_values_data_qry_result_row['happy'];
									        $cv_mood_aggr_graph_values_data_array['romantic'] = $cv_mood_aggr_graph_values_data_qry_result_row['romantic'];
									        $cv_mood_aggr_graph_values_data_array['sad'] = $cv_mood_aggr_graph_values_data_qry_result_row['sad'];
									        $cv_mood_aggr_graph_values_data_array['scary'] = $cv_mood_aggr_graph_values_data_qry_result_row['scary'];
									        $cv_mood_aggr_graph_values_data_array['sexy'] = $cv_mood_aggr_graph_values_data_qry_result_row['sexy'];
									        $cv_mood_aggr_graph_values_data_array['ethereal'] = $cv_mood_aggr_graph_values_data_qry_result_row['ethereal'];
									        $cv_mood_aggr_graph_values_data_array['uplifting'] = $cv_mood_aggr_graph_values_data_qry_result_row['uplifting'];
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
									        $top_3_mood_video_id_data = "SELECT video_id FROM `tbl_social_spyder_graph_meta_data` as a inner join (SELECT LTrack_id FROM `cyanite` WHERE `brand_id`=".$cv_id."  and is_active=0 and moodtags Like '%".$mkey."%' and process_type='youtube' LIMIT 1) as a2 ON a.track_id=a2.LTrack_id";

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

									$cv_genre_aggr_graph_values_data_qry = "select `ambient`,`blues`,`classical`,`country`,`electronicDance`,`folk`,`indieAlternative`,`jazz`,`latin`,`metal`,`pop`,`punk`,`rapHipHop`,`reggae`,`rnb`,`rock`,`singerSongwriter` from `tbl_genre_aggr_graph_data` where `cv_id` =".$cv_id." and `is_active`=0";
									$cv_genre_aggr_graph_values_data_result = $conn->query($cv_genre_aggr_graph_values_data_qry);

									if ($cv_genre_aggr_graph_values_data_result->num_rows > 0)
									{
									    $cv_genre_aggr_graph_values_data_array = [];
									    while($cv_genre_aggr_graph_values_data_result_row = $cv_genre_aggr_graph_values_data_result->fetch_assoc())
									    {
									        $cv_genre_aggr_graph_values_data_array['ambient'] = $cv_genre_aggr_graph_values_data_result_row['ambient'];
									        $cv_genre_aggr_graph_values_data_array['blues'] = $cv_genre_aggr_graph_values_data_result_row['blues'];
									        $cv_genre_aggr_graph_values_data_array['classical'] = $cv_genre_aggr_graph_values_data_result_row['classical'];
									        $cv_genre_aggr_graph_values_data_array['country'] = $cv_genre_aggr_graph_values_data_result_row['country'];
									        $cv_genre_aggr_graph_values_data_array['electronicDance'] = $cv_genre_aggr_graph_values_data_result_row['electronicDance'];
									        $cv_genre_aggr_graph_values_data_array['folk'] = $cv_genre_aggr_graph_values_data_result_row['folk'];
									        $cv_genre_aggr_graph_values_data_array['indieAlternative'] = $cv_genre_aggr_graph_values_data_result_row['indieAlternative'];
									        $cv_genre_aggr_graph_values_data_array['jazz'] = $cv_genre_aggr_graph_values_data_result_row['jazz'];
									        $cv_genre_aggr_graph_values_data_array['latin'] = $cv_genre_aggr_graph_values_data_result_row['latin'];
									        $cv_genre_aggr_graph_values_data_array['metal'] = $cv_genre_aggr_graph_values_data_result_row['metal'];
									        $cv_genre_aggr_graph_values_data_array['pop'] = $cv_genre_aggr_graph_values_data_result_row['pop'];
									        $cv_genre_aggr_graph_values_data_array['punk'] = $cv_genre_aggr_graph_values_data_result_row['punk'];
									        $cv_genre_aggr_graph_values_data_array['rapHipHop'] = $cv_genre_aggr_graph_values_data_result_row['rapHipHop'];
									        $cv_genre_aggr_graph_values_data_array['reggae'] = $cv_genre_aggr_graph_values_data_result_row['reggae'];
									        $cv_genre_aggr_graph_values_data_array['rnb'] = $cv_genre_aggr_graph_values_data_result_row['rnb'];
									        $cv_genre_aggr_graph_values_data_array['rock'] = $cv_genre_aggr_graph_values_data_result_row['rock'];
									        $cv_genre_aggr_graph_values_data_array['singerSongwriter'] = $cv_genre_aggr_graph_values_data_result_row['singerSongwriter'];

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
									        $top_3_genre_video_id_data = "SELECT video_id FROM `tbl_social_spyder_graph_meta_data` as a inner join (SELECT LTrack_id FROM `cyanite` WHERE `brand_id`=".$cv_id."  and is_active=0 and genretags Like '%".$gkey."%' and process_type='youtube' LIMIT 1) as a2 ON a.track_id=a2.LTrack_id";

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
									}

									$get_cv_ind_and_sub_ind_qry = "SELECT * FROM `tbl_cvs` where cv_id = ".$cv_id;
									$get_cv_ind_and_sub_ind_qry_res = $conn->query($get_cv_ind_and_sub_ind_qry);
									$industry_id = '';
									$sub_ind_id = '';
									if ($get_cv_ind_and_sub_ind_qry_res->num_rows > 0) {
				  						while($get_cv_ind_and_sub_ind_qry_res_row = $get_cv_ind_and_sub_ind_qry_res->fetch_assoc()) {
											$industry_id = $get_cv_ind_and_sub_ind_qry_res_row['industry_id'];
											$sub_ind_id = $get_cv_ind_and_sub_ind_qry_res_row['sub_industry_id'];
											$cv_year = $get_cv_ind_and_sub_ind_qry_res_row['cv_year']; 
										}
									}
									
									if($industry_id != '')
									{
										error_log("Industry graph generation started for Industry - ".$industry_id);
										$ind_cv_ids_arr = array();
										$ind_cv_ids_qry = "SELECT * FROM `tbl_cvs` WHERE industry_id=".$industry_id." and is_active=0 and status=1 and cv_year=".$cv_year;
										$ind_cv_ids_qry_res = $conn->query($ind_cv_ids_qry);
										if($ind_cv_ids_qry_res->num_rows > 0)
										{
											while($ind_cv_ids_qry_res_row = $ind_cv_ids_qry_res->fetch_assoc())
											{
												array_push($ind_cv_ids_arr, $ind_cv_ids_qry_res_row['cv_id']);
											}

											$ind_cv_ids_arr_str = implode(",",$ind_cv_ids_arr);
											error_log($ind_cv_ids_arr_str);
											$chk_pending_request_qry = "SELECT * FROM `tbl_social_spyder_graph_request_data` WHERE `cv_id` in (".$ind_cv_ids_arr_str.") and is_active=0 and status<2";
											$chk_pending_request_qry_res = $conn->query($chk_pending_request_qry);
											error_log($chk_pending_request_qry_res->num_rows);
											if($chk_pending_request_qry_res->num_rows == 0)
											{
												$chk_process_priority_qry = "SELECT * FROM `tbl_social_media_sync_process_data` where cv_id IN (".$ind_cv_ids_arr_str.") and is_active=0 and (yt=0 or ig=0 || tt=0 || twt=0)";
												$chk_process_priority_qry_res = $conn->query($chk_process_priority_qry);
												error_log($chk_process_priority_qry_res->num_rows);
												if($chk_process_priority_qry_res->num_rows == 0)
												{
													$chk_meta_qry = "SELECT * FROM `tbl_social_spyder_graph_meta_data` where cv_id In (".$ind_cv_ids_arr_str.") and is_active=0 and status<4";
													$chk_meta_qry_res = $conn->query($chk_meta_qry);
													error_log($chk_meta_qry_res->num_rows);
													$cv_record_counter = 0;
													$data_inserted_type_arr = array();
													if($chk_meta_qry_res->num_rows == 0)
													{
														$process_type_arr = array('youtube','instagram','tiktok','twitter');
														for($i=0; $i<count($process_type_arr); $i++)
														{
															for($j=0; $j<count($ind_cv_ids_arr); $j++)
															{
																$brand_id = $ind_cv_ids_arr[$j];
																$brand_process_type = $process_type_arr[$i];
																switch($brand_process_type){
																	case 'youtube':
																		$table_name = "youtube";
																		break;
																	case 'instagram':
																		$table_name = "instagram";								
																		break;
																	case 'tiktok':
																		$table_name = "tiktok";
																		break;
																	case 'twitter':
																		$table_name = "twitter";
																		break;
																}

																$sql_paras = "SELECT 
																			cyanite.LTrack_id,
																			cyanite.brand_id,
																			json_extract(cyanite.mood, '$.aggressive') AS aggressive,
																			json_extract(cyanite.mood, '$.calm') AS calm,
																			json_extract(cyanite.mood, '$.chilled') AS chilled,
																			json_extract(cyanite.mood, '$.dark') AS dark,
																			json_extract(cyanite.mood, '$.energetic') AS energetic,
																			json_extract(cyanite.mood, '$.epic') AS epic,
																			json_extract(cyanite.mood, '$.happy') AS happy,
																			json_extract(cyanite.mood, '$.romantic') AS romantic,
																			json_extract(cyanite.mood, '$.sad') AS sad,
																			json_extract(cyanite.mood, '$.scary') AS scary,
																			json_extract(cyanite.mood, '$.sexy') AS sexy,
																			json_extract(cyanite.mood, '$.ethereal') AS ethereal,
																			json_extract(cyanite.mood, '$.uplifting') AS uplifting,
																			json_extract(cyanite.genre, '$.ambient') AS ambient,
																			json_extract(cyanite.genre, '$.blues') AS blues,
																			json_extract(cyanite.genre, '$.classical') AS classical,
																			json_extract(cyanite.genre, '$.country') AS country,
																			json_extract(cyanite.genre, '$.electronicDance') AS electronicDance,
																			json_extract(cyanite.genre, '$.folk') AS folk,
																			json_extract(cyanite.genre, '$.indieAlternative') AS indieAlternative,
																			json_extract(cyanite.genre, '$.jazz') AS jazz,
																			json_extract(cyanite.genre, '$.latin') AS latin,
																			json_extract(cyanite.genre, '$.metal') AS metal,
																			json_extract(cyanite.genre, '$.pop') AS pop,
																			json_extract(cyanite.genre, '$.punk') AS punk,
																			json_extract(cyanite.genre, '$.rapHipHop') AS rapHipHop,
																			json_extract(cyanite.genre, '$.reggae') AS reggae,
																			json_extract(cyanite.genre, '$.rnb') AS rnb,
																			json_extract(cyanite.genre, '$.rock') AS rock,
																			json_extract(cyanite.genre, '$.singerSongwriter') AS singerSongwriter FROM cyanite where cyanite.brand_id=".$brand_id." and cyanite.process_type='".$brand_process_type."' and cyanite.is_active=0";
																			
																$result = $conn->query($sql_paras);
																$track_id_array = array();
																$individual_cv_record_counter = 0;						
																if ($result->num_rows > 0) {
																	$ins_ind_mood_temp_query = '';
																	$ins_ind_mood_temp_query = "insert into tbl_industry_".$table_name."_mood_graph_temp_data (ind_id,ind_year,track_id,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values ";
																	$ins_ind_aggr_mood_temp_query = '';
																	$ins_ind_aggr_mood_temp_query = "insert into tbl_industry_mood_aggr_graph_temp_data (ind_id,ind_year,track_id,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values ";
																	$ins_ind_genre_temp_query = '';
																	$ins_ind_genre_temp_query = "insert into tbl_industry_".$table_name."_genre_graph_temp_data (ind_id,ind_year,track_id,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values ";
																	$ins_ind_aggr_genre_temp_query = '';
																	$ins_ind_aggr_genre_temp_query = "insert into tbl_industry_genre_aggr_graph_temp_data (ind_id,ind_year,track_id,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values ";
																	while($row = $result->fetch_assoc()) {
																		$LTrack_id = $row["LTrack_id"];
																		//error_log("----------------------------------------------------------------------------------------------------------------------------------------------------");
																		//echo "track id extracting : ".$LTrack_id;
																		
																		$ins_ind_mood_temp_query .= "(".$industry_id.",".$cv_year.",".$row['LTrack_id'].",".$row['aggressive'].",".$row['calm'].",".$row['chilled'].",".$row['dark'].",".$row['energetic'].",".$row['epic'].",".$row['happy'].",".$row['romantic'].",".$row['sad'].",".$row['scary'].",".$row['sexy'].",".$row['ethereal'].",".$row['uplifting']."),";
																		
																		$ins_ind_aggr_mood_temp_query .= "(".$industry_id.",".$cv_year.",".$row['LTrack_id'].",".$row['aggressive'].",".$row['calm'].",".$row['chilled'].",".$row['dark'].",".$row['energetic'].",".$row['epic'].",".$row['happy'].",".$row['romantic'].",".$row['sad'].",".$row['scary'].",".$row['sexy'].",".$row['ethereal'].",".$row['uplifting']."),";
																		
																		
																		$ins_ind_genre_temp_query .= "(".$industry_id.",".$cv_year.",".$row['LTrack_id'].",".$row['ambient'].",".$row['blues'].",".$row['classical'].",".$row['country'].",".$row['electronicDance'].",".$row['folk'].",".$row['indieAlternative'].",".$row['jazz'].",".$row['latin'].",".$row['metal'].",".$row['pop'].",".$row['punk'].",".$row['rapHipHop'].",".$row['reggae'].",".$row['rnb'].",".$row['rock'].",".$row['singerSongwriter']."),";
																		
																		$ins_ind_aggr_genre_temp_query .= "(".$industry_id.",".$cv_year.",".$row['LTrack_id'].",".$row['ambient'].",".$row['blues'].",".$row['classical'].",".$row['country'].",".$row['electronicDance'].",".$row['folk'].",".$row['indieAlternative'].",".$row['jazz'].",".$row['latin'].",".$row['metal'].",".$row['pop'].",".$row['punk'].",".$row['rapHipHop'].",".$row['reggae'].",".$row['rnb'].",".$row['rock'].",".$row['singerSongwriter']."),";
																		
																		//array_push($track_id_array,$LTrack_id);
																		$individual_cv_record_counter = $individual_cv_record_counter+1;
																			
																	}
																	if($individual_cv_record_counter >0)
																	{
																		$multi_ins_ind_mood_temp_query = rtrim($ins_ind_mood_temp_query,",");
																		error_log("multi_ins_ind_mood_temp_query is generated for".$table_name);
																		
																		$conn->query($multi_ins_ind_mood_temp_query);
																		
																		$multi_ins_ind_aggr_mood_temp_query = rtrim($ins_ind_aggr_mood_temp_query,",");
																		error_log("multi_ins_ind_aggr_mood_temp_query is generated for".$table_name);
																		$conn->query($multi_ins_ind_aggr_mood_temp_query);
																		
																		$multi_ins_ind_genre_temp_query = rtrim($ins_ind_genre_temp_query,",");
																		error_log("multi_ins_ind_genre_temp_query is generated for".$table_name);
																		$conn->query($multi_ins_ind_genre_temp_query);
																		
																		$multi_ins_ind_aggr_genre_temp_query = rtrim($ins_ind_aggr_genre_temp_query,",");
																		error_log("multi_ins_ind_aggr_genre_temp_query is generated for".$table_name);
																		$conn->query($multi_ins_ind_aggr_genre_temp_query);

																		if(!in_array($table_name, $data_inserted_type_arr))
																		{
																			array_push($data_inserted_type_arr,$table_name);
																		}
																	}
																	else
																	{

																	}							
																	error_log("====================================================================================================================================================");
																}
															}
														}
														if(!empty($data_inserted_type_arr))
														{
															for($i=0; $i<count($data_inserted_type_arr); $i++)
															{
																$sql_ind_mood_avg_query = '';
																$sql_ind_mood_avg_query = "SELECT round(AVG(`aggressive`),2) as aggressive, round(AVG(`calm`),2) as calm, round(AVG(`chilled`),2) as chilled, round(AVG(`dark`),2) as dark, round(AVG(`energetic`),2) as energetic, round(AVG(`epic`),2) as epic, round(AVG(`happy`),2) as happy, round(AVG(`romantic`),2) as romantic, round(AVG(`sad`),2) as sad, round(AVG(`scary`),2) as scary, round(AVG(`sexy`),2) as sexy, round(AVG(`ethereal`),2) as ethereal, round(AVG(`uplifting`),2) as uplifting FROM `tbl_industry_".$data_inserted_type_arr[$i]."_mood_graph_temp_data` WHERE `ind_id` =".$industry_id." AND `ind_year`= ".$cv_year;
																error_log("sql_ind_".$data_inserted_type_arr[$i]."_mood_avg_query:".$sql_ind_mood_avg_query);
																$result = $conn->query($sql_ind_mood_avg_query);
																if ($result->num_rows > 0) {
																	while($row = $result->fetch_assoc()) {
																		$chk_mood_aggr_qry = "SELECT * FROM tbl_industry_".$data_inserted_type_arr[$i]."_mood_graph_data WHERE `ind_id`=".$industry_id." and `ind_year`=".$cv_year;
																		$chk_mood_aggr_qry_result = $conn->query($chk_mood_aggr_qry);
																		if($chk_mood_aggr_qry_result->num_rows > 0)
																		{
																			$s1 = "UPDATE tbl_industry_".$data_inserted_type_arr[$i]."_mood_graph_data SET `aggressive`=".$row['aggressive'].",`calm`=".$row['calm'].",`chilled`=".$row['chilled'].",`dark`=".$row['dark'].",`energetic`=".$row['energetic'].",`epic`=".$row['epic'].",`happy`=".$row['happy'].",`romantic`=".$row['romantic'].",`sad`=".$row['sad'].",`scary`=".$row['scary'].",`sexy`=".$row['sexy'].",`ethereal`=".$row['ethereal'].",`uplifting`=".$row['uplifting']." WHERE `ind_id`=".$industry_id." and `ind_year`=".$cv_year;
																		}
																		else
																		{
																			$s1 = "insert into tbl_industry_".$data_inserted_type_arr[$i]."_mood_graph_data (ind_id,ind_year,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values(".$industry_id.",".$cv_year.",".$row['aggressive'].",".$row['calm'].",".$row['chilled'].",".$row['dark'].",".$row['energetic'].",".$row['epic'].",".$row['happy'].",".$row['romantic'].",".$row['sad'].",".$row['scary'].",".$row['sexy'].",".$row['ethereal'].",".$row['uplifting'].")";
																		}													

																		error_log("tbl_industry_".$data_inserted_type_arr[$i]."_mood_graph_data:".$s1);
																		$conn->query($s1);
																		$conn->query("DELETE FROM tbl_industry_".$data_inserted_type_arr[$i]."_mood_graph_temp_data WHERE ind_id=".$industry_id." and ind_year=".$cv_year);
																	}
																}
																$sql_ind_genre_avg_query = '';
																$sql_ind_genre_avg_query = "SELECT round(AVG(`ambient`),2) as ambient, round(AVG(`blues`),2) as blues, round(AVG(`classical`),2) as classical, round(AVG(`country`),2) as country, round(AVG(`electronicDance`),2) as electronicDance, round(AVG(`folk`),2) as folk, round(AVG(`indieAlternative`),2) as indieAlternative, round(AVG(`jazz`),2) as jazz, round(AVG(`latin`),2) as latin, round(AVG(`metal`),2) as metal, round(AVG(`pop`),2) as pop, round(AVG(`punk`),2) as punk, round(AVG(`rapHipHop`),2) as rapHipHop, round(AVG(`reggae`),2) as reggae, round(AVG(`rnb`),2) as rnb, round(AVG(`rock`),2) as rock, round(AVG(`singerSongwriter`),2) as singerSongwriter FROM `tbl_industry_".$data_inserted_type_arr[$i]."_genre_graph_temp_data` WHERE `ind_id` =".$industry_id." AND `ind_year`= ".$cv_year;
																error_log("sql_ind_".$data_inserted_type_arr[$i]."_genre_avg_query:".$sql_ind_genre_avg_query);
																$result1 = $conn->query($sql_ind_genre_avg_query);
																if ($result1->num_rows > 0) {
																	while($row1 = $result1->fetch_assoc()) {
																		$chk_ind_genre_aggr_qry = "SELECT * FROM tbl_industry_".$data_inserted_type_arr[$i]."_genre_graph_data WHERE `ind_id`=".$industry_id." and `ind_year`=".$cv_year;
																		$chk_ind_genre_aggr_qry_result = $conn->query($chk_ind_genre_aggr_qry);
																		if($chk_ind_genre_aggr_qry_result->num_rows > 0)
																		{
																			$s11 = "UPDATE tbl_industry_".$data_inserted_type_arr[$i]."_genre_graph_data SET `ambient`=".$row1['ambient'].",`blues`=".$row1['blues'].",`classical`=".$row1['classical'].",`country`=".$row1['country'].",`electronicDance`=".$row1['electronicDance'].",`folk`=".$row1['folk'].",`indieAlternative`=".$row1['indieAlternative'].",`jazz`=".$row1['jazz'].",`latin`=".$row1['latin'].",`metal`=".$row1['metal'].",`pop`=".$row1['pop'].",`punk`=".$row1['punk'].",`rapHipHop`=".$row1['rapHipHop'].",`reggae`=".$row1['reggae'].",`rnb`=".$row1['rnb'].",`rock`=".$row1['rock'].",`singerSongwriter`=".$row1['singerSongwriter']." WHERE `ind_id`=".$industry_id." and `ind_year`=".$cv_year;
																		}
																		else
																		{
																			$s11 = "insert into tbl_industry_".$data_inserted_type_arr[$i]."_genre_graph_data (ind_id,ind_year,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values(".$industry_id.",".$cv_year.",".$row1['ambient'].",".$row1['blues'].",".$row1['classical'].",".$row1['country'].",".$row1['electronicDance'].",".$row1['folk'].",".$row1['indieAlternative'].",".$row1['jazz'].",".$row1['latin'].",".$row1['metal'].",".$row1['pop'].",".$row1['punk'].",".$row1['rapHipHop'].",".$row1['reggae'].",".$row1['rnb'].",".$row1['rock'].",".$row1['singerSongwriter'].")";
																		}

																		error_log("tbl_industry_".$data_inserted_type_arr[$i]."_genre_graph_data:".$s11);
																		$conn->query($s11);
																		$conn->query("DELETE FROM tbl_industry_".$data_inserted_type_arr[$i]."_genre_graph_temp_data WHERE ind_id=".$industry_id." and ind_year=".$cv_year);
																	}
																}
															}
															

															if(count($data_inserted_type_arr)>1)
															{
																$sql_ind_aggr_mood_avg_query = '';
																$sql_ind_aggr_mood_avg_query = "SELECT round(AVG(`aggressive`),2) as aggressive, round(AVG(`calm`),2) as calm, round(AVG(`chilled`),2) as chilled, round(AVG(`dark`),2) as dark, round(AVG(`energetic`),2) as energetic, round(AVG(`epic`),2) as epic, round(AVG(`happy`),2) as happy, round(AVG(`romantic`),2) as romantic, round(AVG(`sad`),2) as sad, round(AVG(`scary`),2) as scary, round(AVG(`sexy`),2) as sexy, round(AVG(`ethereal`),2) as ethereal, round(AVG(`uplifting`),2) as uplifting FROM `tbl_industry_mood_aggr_graph_temp_data` WHERE `ind_id` =".$industry_id." AND `ind_year`=".$cv_year;
																error_log("sql_ind_aggr_mood_avg_query:".$sql_ind_aggr_mood_avg_query);
																$ind_result = $conn->query($sql_ind_aggr_mood_avg_query);
																if ($ind_result->num_rows > 0) {
																	while($ind_result_row = $ind_result->fetch_assoc()) {
																		$chk_ind_mood_aggr_qry = "SELECT * FROM `tbl_industry_mood_aggr_graph_data` WHERE `ind_id`=".$industry_id." and `ind_year`=".$cv_year;
																		$chk_ind_mood_aggr_qry_result = $conn->query($chk_ind_mood_aggr_qry);
																		if($chk_ind_mood_aggr_qry_result->num_rows > 0)
																		{
																			$ind_s1 = "UPDATE `tbl_industry_mood_aggr_graph_data` SET `aggressive`=".$ind_result_row['aggressive'].",`calm`=".$ind_result_row['calm'].",`chilled`=".$ind_result_row['chilled'].",`dark`=".$ind_result_row['dark'].",`energetic`=".$ind_result_row['energetic'].",`epic`=".$ind_result_row['epic'].",`happy`=".$ind_result_row['happy'].",`romantic`=".$ind_result_row['romantic'].",`sad`=".$ind_result_row['sad'].",`scary`=".$ind_result_row['scary'].",`sexy`=".$ind_result_row['sexy'].",`ethereal`=".$ind_result_row['ethereal'].",`uplifting`=".$ind_result_row['uplifting']." WHERE `ind_id`=".$industry_id." and `ind_year`=".$cv_year;
																		}
																		else
																		{
																			$ind_s1 = "insert into tbl_industry_mood_aggr_graph_data (ind_id,ind_year,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values(".$industry_id.",".$cv_year.",".$ind_result_row['aggressive'].",".$ind_result_row['calm'].",".$ind_result_row['chilled'].",".$ind_result_row['dark'].",".$ind_result_row['energetic'].",".$ind_result_row['epic'].",".$ind_result_row['happy'].",".$ind_result_row['romantic'].",".$ind_result_row['sad'].",".$ind_result_row['scary'].",".$ind_result_row['sexy'].",".$ind_result_row['ethereal'].",".$ind_result_row['uplifting'].")";
																		}
																		error_log("tbl_industry_mood_aggr_graph_data:".$ind_s1);
																		$conn->query($ind_s1);
																	}
																}									
																				
																					
																$sql_ind_aggr_genre_avg_query = "SELECT round(AVG(`ambient`),2) as ambient, round(AVG(`blues`),2) as blues, round(AVG(`classical`),2) as classical, round(AVG(`country`),2) as country, round(AVG(`electronicDance`),2) as electronicDance, round(AVG(`folk`),2) as folk, round(AVG(`indieAlternative`),2) as indieAlternative, round(AVG(`jazz`),2) as jazz, round(AVG(`latin`),2) as latin, round(AVG(`metal`),2) as metal, round(AVG(`pop`),2) as pop, round(AVG(`punk`),2) as punk, round(AVG(`rapHipHop`),2) as rapHipHop, round(AVG(`reggae`),2) as reggae, round(AVG(`rnb`),2) as rnb, round(AVG(`rock`),2) as rock, round(AVG(`singerSongwriter`),2) as singerSongwriter FROM `tbl_industry_genre_aggr_graph_temp_data` WHERE `ind_id` =".$industry_id." AND `ind_year`=".$cv_year;
																error_log("sql_ind_aggr_genre_avg_query:".$sql_ind_aggr_genre_avg_query);
																$ind_result1 = $conn->query($sql_ind_aggr_genre_avg_query);
																if ($ind_result1->num_rows > 0) {
																	while($ind_result1_row = $ind_result1->fetch_assoc()) {
																		$chk_ind_genre_aggr_qry = "SELECT * FROM `tbl_industry_genre_aggr_graph_data` WHERE `ind_id`=".$industry_id." and `ind_year`=".$cv_year;
																		$chk_ind_genre_aggr_qry_result = $conn->query($chk_ind_genre_aggr_qry);
																		if($chk_ind_genre_aggr_qry_result->num_rows > 0)
																		{
																			$ind_s11 = "UPDATE `tbl_industry_genre_aggr_graph_data` SET `ambient`=".$ind_result1_row['ambient'].",`blues`=".$ind_result1_row['blues'].",`classical`=".$ind_result1_row['classical'].",`country`=".$ind_result1_row['country'].",`electronicDance`=".$ind_result1_row['electronicDance'].",`folk`=".$ind_result1_row['folk'].",`indieAlternative`=".$ind_result1_row['indieAlternative'].",`jazz`=".$ind_result1_row['jazz'].",`latin`=".$ind_result1_row['latin'].",`metal`=".$ind_result1_row['metal'].",`pop`=".$ind_result1_row['pop'].",`punk`=".$ind_result1_row['punk'].",`rapHipHop`=".$ind_result1_row['rapHipHop'].",`reggae`=".$ind_result1_row['reggae'].",`rnb`=".$ind_result1_row['rnb'].",`rock`=".$ind_result1_row['rock'].",`singerSongwriter`=".$ind_result1_row['singerSongwriter']." WHERE `ind_id`=".$industry_id." and `ind_year`=".$cv_year;
																		}
																		else
																		{
																			$ind_s11 = "insert into tbl_industry_genre_aggr_graph_data (ind_id,ind_year,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values(".$industry_id.",".$cv_year.",".$ind_result1_row['ambient'].",".$ind_result1_row['blues'].",".$ind_result1_row['classical'].",".$ind_result1_row['country'].",".$ind_result1_row['electronicDance'].",".$ind_result1_row['folk'].",".$ind_result1_row['indieAlternative'].",".$ind_result1_row['jazz'].",".$ind_result1_row['latin'].",".$ind_result1_row['metal'].",".$ind_result1_row['pop'].",".$ind_result1_row['punk'].",".$ind_result1_row['rapHipHop'].",".$ind_result1_row['reggae'].",".$ind_result1_row['rnb'].",".$ind_result1_row['rock'].",".$ind_result1_row['singerSongwriter'].")";
																		}							

																		error_log("tbl_industry_genre_aggr_graph_data:".$ind_s11);
																		$conn->query($ind_s11);
																	}
																}
															}
															$conn->query("DELETE FROM tbl_industry_mood_aggr_graph_temp_data WHERE ind_id=".$industry_id." and ind_year=".$cv_year);
															$conn->query("DELETE FROM tbl_industry_genre_aggr_graph_temp_data WHERE ind_id=".$industry_id." and ind_year=".$cv_year);
															error_log("Message : Both Graphs Average Data inserted");
															error_log("====================================================================================================================================================");
														}
													}
													else
													{
														error_log("There are some cvs pending for analysis or indiviual graph generation");
													}
												}
												else
												{
													error_log("There are some cvs pending in priority process tbl for download");
												}
												
											}
											else
											{
												error_log("There are some cvs pending for download");
											}
										}
										error_log("Industry graph generation completed for Industry - ".$industry_id." ".$cv_year);
									}
									
									if($sub_ind_id != '')
									{										
										error_log("Sub Industry graph generation started for Sub Industry - ".$sub_ind_id." ".$cv_year);
										$get_request_and_meta_status_qry = "select DISTINCT(a.status) as request_status, (select DISTINCT(a.status) from tbl_social_spyder_graph_meta_data a join tbl_cvs on a.cv_id = tbl_cvs.cv_id where a.is_active=0 and tbl_cvs.status=1 and tbl_cvs.is_active=0 and tbl_cvs.industry_id='".$ind_id."' and tbl_cvs.sub_industry_id='".$sub_ind_id."' and tbl_cvs.cv_year='".$cv_year."' and a.status=0) as meta_status from tbl_social_spyder_graph_request_data a join tbl_cvs on a.cv_id = tbl_cvs.cv_id where a.is_active=0 and a.status=0 and tbl_cvs.status=1 and tbl_cvs.is_active=0 and tbl_cvs.industry_id='".$ind_id."' and tbl_cvs.sub_industry_id='".$sub_ind_id."' and tbl_cvs.cv_year='".$cv_year."'";
										$get_request_and_meta_status_qry_res = $conn->query($get_request_and_meta_status_qry);
										if ($get_request_and_meta_status_qry_res->num_rows > 0) {
											error_log("Some CV Processing is pending so graph generation for Sub Industry - ".$sub_ind_id." ".$cv_year." is stoped");
										}
										else
										{
											$chk_mood_aggr_graph_temp_data_query = "select * from tbl_sub_industry_mood_aggr_graph_temp_data where sind_id=".$sub_ind_id." and sind_year=".$cv_year;
											$chk_mood_aggr_graph_temp_data_query_res = $conn->query($chk_mood_aggr_graph_temp_data_query);
											if($chk_mood_aggr_graph_temp_data_query_res_res->num_rows > 0)
											{
												$conn->query("DELETE FROM tbl_sub_industry_mood_aggr_graph_temp_data WHERE sind_id=".$sub_ind_id." and sind_year=".$cv_year);
												error_log("exisiting data of sub industry- ".$sub_ind_id." ".$cv_year." is deleted from tbl_sub_industry_mood_aggr_graph_temp_data");
											}
											
											$chk_genre_aggr_graph_temp_data_query = "select * from tbl_sub_industry_genre_aggr_graph_temp_data where sind_id=".$sub_ind_id." and sind_year=".$cv_year;
											$chk_genre_aggr_graph_temp_data_query_res = $conn->query($chk_genre_aggr_graph_temp_data_query);
											if($chk_genre_aggr_graph_temp_data_query_res->num_rows > 0)
											{
												$conn->query("DELETE FROM tbl_sub_industry_genre_aggr_graph_temp_data WHERE sind_id=".$sub_ind_id." and sind_year=".$cv_year);
												error_log("exisiting data of sub industry- ".$sub_ind_id." ".$cv_year." is deleted from tbl_sub_industry_genre_aggr_graph_temp_data");
											}
											
											$get_cv_ids_qry = "select * from tbl_cvs where status=1 AND is_active = 0 AND industry_id='".$ind_id."' AND sub_industry_id='".$sub_ind_id."' AND cv_year='".$cv_year."'";
											$get_cv_ids_qry_res = $conn->query($get_cv_ids_qry);
											$industry_wise_cv_id_array = array();
											while($get_cv_ids_qry_res_row = $get_cv_ids_qry_res->fetch_assoc()) {
												array_push($brand_id_array, $get_cv_ids_qry_res_row['cv_id']);
											}
											
											for($i=0; $i<count($brand_id_array); $i++)
											{
												//echo "brand_id:".$brand_id_array[$i]."<br>";
												$brand_id = $brand_id_array[$i];
												
												$sql_paras = "SELECT 
															cyanite.LTrack_id,
															cyanite.brand_id,
															json_extract(cyanite.mood, '$.aggressive') AS aggressive,
															json_extract(cyanite.mood, '$.calm') AS calm,
															json_extract(cyanite.mood, '$.chilled') AS chilled,
															json_extract(cyanite.mood, '$.dark') AS dark,
															json_extract(cyanite.mood, '$.energetic') AS energetic,
															json_extract(cyanite.mood, '$.epic') AS epic,
															json_extract(cyanite.mood, '$.happy') AS happy,
															json_extract(cyanite.mood, '$.romantic') AS romantic,
															json_extract(cyanite.mood, '$.sad') AS sad,
															json_extract(cyanite.mood, '$.scary') AS scary,
															json_extract(cyanite.mood, '$.sexy') AS sexy,
															json_extract(cyanite.mood, '$.ethereal') AS ethereal,
															json_extract(cyanite.mood, '$.uplifting') AS uplifting,
															json_extract(cyanite.genre, '$.ambient') AS ambient,
															json_extract(cyanite.genre, '$.blues') AS blues,
															json_extract(cyanite.genre, '$.classical') AS classical,
															json_extract(cyanite.genre, '$.country') AS country,
															json_extract(cyanite.genre, '$.electronicDance') AS electronicDance,
															json_extract(cyanite.genre, '$.folk') AS folk,
															json_extract(cyanite.genre, '$.indieAlternative') AS indieAlternative,
															json_extract(cyanite.genre, '$.jazz') AS jazz,
															json_extract(cyanite.genre, '$.latin') AS latin,
															json_extract(cyanite.genre, '$.metal') AS metal,
															json_extract(cyanite.genre, '$.pop') AS pop,
															json_extract(cyanite.genre, '$.punk') AS punk,
															json_extract(cyanite.genre, '$.rapHipHop') AS rapHipHop,
															json_extract(cyanite.genre, '$.reggae') AS reggae,
															json_extract(cyanite.genre, '$.rnb') AS rnb,
															json_extract(cyanite.genre, '$.rock') AS rock,
															json_extract(cyanite.genre, '$.singerSongwriter') AS singerSongwriter FROM cyanite where cyanite.brand_id=".$brand_id." and cyanite.is_active=0";
															
															$sql_paras_res = $conn->query($sql_paras);
															$track_id_array = array();
															if ($sql_paras_res->num_rows > 0) {
																
																$ins_ind_aggr_mood_temp_query = "insert into tbl_sub_industry_mood_aggr_graph_temp_data (sind_id,sind_year,track_id,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values ";
																
																$ins_ind_aggr_genre_temp_query = "insert into tbl_sub_industry_genre_aggr_graph_temp_data (sind_id,sind_year,track_id,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values ";
																
																while($sql_paras_res_row = $sql_paras_res->fetch_assoc()) {
																	$LTrack_id = $sql_paras_res_row["LTrack_id"];
																	error_log("----------------------------------------------------------------------------------------------------------------------------------------------------");
																	error_log("track id extracting for sub industry graph generation : ".$LTrack_id);																	
																	
																	$ins_ind_aggr_mood_temp_query .= "(".$sub_ind_id.",".$cv_year.",".$sql_paras_res_row['LTrack_id'].",".$sql_paras_res_row['aggressive'].",".$sql_paras_res_row['calm'].",".$sql_paras_res_row['chilled'].",".$sql_paras_res_row['dark'].",".$sql_paras_res_row['energetic'].",".$sql_paras_res_row['epic'].",".$sql_paras_res_row['happy'].",".$sql_paras_res_row['romantic'].",".$sql_paras_res_row['sad'].",".$sql_paras_res_row['scary'].",".$sql_paras_res_row['sexy'].",".$sql_paras_res_row['ethereal'].",".$sql_paras_res_row['uplifting']."),";																	
																	$ins_ind_aggr_genre_temp_query .= "(".$sub_ind_id.",".$cv_year.",".$sql_paras_res_row['LTrack_id'].",".$sql_paras_res_row['ambient'].",".$row['blues'].",".$sql_paras_res_row['classical'].",".$sql_paras_res_row['country'].",".$sql_paras_res_row['electronicDance'].",".$sql_paras_res_row['folk'].",".$sql_paras_res_row['indieAlternative'].",".$sql_paras_res_row['jazz'].",".$sql_paras_res_row['latin'].",".$sql_paras_res_row['metal'].",".$sql_paras_res_row['pop'].",".$sql_paras_res_row['punk'].",".$sql_paras_res_row['rapHipHop'].",".$sql_paras_res_row['reggae'].",".$sql_paras_res_row['rnb'].",".$sql_paras_res_row['rock'].",".$sql_paras_res_row['singerSongwriter']."),";
																	
																	//array_push($track_id_array,$LTrack_id);
																		
																}

																$multi_ins_ind_aggr_mood_temp_query = rtrim($ins_ind_aggr_mood_temp_query,",");
																error_log("multi_ins_sub_ind_aggr_mood_temp_query is generated");
																$conn->query($multi_ins_ind_aggr_mood_temp_query);
																																
																$multi_ins_ind_aggr_genre_temp_query = rtrim($ins_ind_aggr_genre_temp_query,",");
																error_log("multi_sub_ins_ind_aggr_genre_temp_query is generated");
																$conn->query($multi_ins_ind_aggr_genre_temp_query);
															}
											}
											
											$conn->query("DELETE FROM tbl_sub_industry_mood_aggr_graph_data WHERE sind_id=".$sub_ind_id." and sind_year=".$cv_year);
											$conn->query("DELETE FROM tbl_sub_industry_genre_aggr_graph_data WHERE sind_id=".$sub_ind_id." and sind_year=".$cv_year);
											
											$sql_ind_aggr_mood_avg_query = "SELECT round(AVG(`aggressive`),2) as aggressive, round(AVG(`calm`),2) as calm, round(AVG(`chilled`),2) as chilled, round(AVG(`dark`),2) as dark, round(AVG(`energetic`),2) as energetic, round(AVG(`epic`),2) as epic, round(AVG(`happy`),2) as happy, round(AVG(`romantic`),2) as romantic, round(AVG(`sad`),2) as sad, round(AVG(`scary`),2) as scary, round(AVG(`sexy`),2) as sexy, round(AVG(`ethereal`),2) as ethereal, round(AVG(`uplifting`),2) as uplifting FROM `tbl_sub_industry_mood_aggr_graph_temp_data` WHERE `sind_id` =".$sub_ind_id." and sind_year=".$cv_year;
											error_log("sql_ind_aggr_mood_avg_query:".$sql_ind_aggr_mood_avg_query);
											$sql_ind_aggr_mood_avg_query_result = $conn->query($sql_ind_aggr_mood_avg_query);
											if ($sql_ind_aggr_mood_avg_query_result->num_rows > 0) {
												while($sql_ind_aggr_mood_avg_query_result_row = $sql_ind_aggr_mood_avg_query_result->fetch_assoc()) {
													$ind_mood_ins = "insert into tbl_sub_industry_mood_aggr_graph_data(sind_id,sind_year,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values(".$sub_ind_id.",".$cv_year.",".$sql_ind_aggr_mood_avg_query_result_row['aggressive'].",".$sql_ind_aggr_mood_avg_query_result_row['calm'].",".$sql_ind_aggr_mood_avg_query_result_row['chilled'].",".$sql_ind_aggr_mood_avg_query_result_row['dark'].",".$sql_ind_aggr_mood_avg_query_result_row['energetic'].",".$sql_ind_aggr_mood_avg_query_result_row['epic'].",".$sql_ind_aggr_mood_avg_query_result_row['happy'].",".$sql_ind_aggr_mood_avg_query_result_row['romantic'].",".$sql_ind_aggr_mood_avg_query_result_row['sad'].",".$sql_ind_aggr_mood_avg_query_result_row['scary'].",".$sql_ind_aggr_mood_avg_query_result_row['sexy'].",".$sql_ind_aggr_mood_avg_query_result_row['ethereal'].",".$sql_ind_aggr_mood_avg_query_result_row['uplifting'].")";	
													if($conn->query($ind_mood_ins))
													{
														$conn->query("DELETE FROM tbl_sub_industry_mood_aggr_graph_temp_data WHERE sind_id=".$sub_ind_id." and sind_year=".$cv_year);
														error_log("sub_industry_mood_aggr_graph data insetred for sub industry ".$sub_ind_id." ".$cv_year);
													}													
													
												}
											}			
																
											$sql_ind_aggr_genre_avg_query = "SELECT round(AVG(`ambient`),2) as ambient, round(AVG(`blues`),2) as blues, round(AVG(`classical`),2) as classical, round(AVG(`country`),2) as country, round(AVG(`electronicDance`),2) as electronicDance, round(AVG(`folk`),2) as folk, round(AVG(`indieAlternative`),2) as indieAlternative, round(AVG(`jazz`),2) as jazz, round(AVG(`latin`),2) as latin, round(AVG(`metal`),2) as metal, round(AVG(`pop`),2) as pop, round(AVG(`punk`),2) as punk, round(AVG(`rapHipHop`),2) as rapHipHop, round(AVG(`reggae`),2) as reggae, round(AVG(`rnb`),2) as rnb, round(AVG(`rock`),2) as rock, round(AVG(`singerSongwriter`),2) as singerSongwriter FROM `tbl_sub_industry_genre_aggr_graph_temp_data` WHERE `sind_id` =".$sub_ind_id." and sind_year=".$cv_year;
											error_log("sql_ind_aggr_genre_avg_query:".$sql_ind_aggr_genre_avg_query);
											$sql_ind_aggr_genre_avg_query_result = $conn->query($sql_ind_aggr_genre_avg_query);
											if ($sql_ind_aggr_genre_avg_query_result->num_rows > 0) {
												while($sql_ind_aggr_genre_avg_query_result_row = $sql_ind_aggr_genre_avg_query_result->fetch_assoc()) {
													$ind_genre_ins = "insert into tbl_sub_industry_genre_aggr_graph_data(sind_id,sind_year,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values(".$sub_ind_id.",".$cv_year.",".$sql_ind_aggr_genre_avg_query_result_row['ambient'].",".$sql_ind_aggr_genre_avg_query_result_row['blues'].",".$sql_ind_aggr_genre_avg_query_result_row['classical'].",".$sql_ind_aggr_genre_avg_query_result_row['country'].",".$sql_ind_aggr_genre_avg_query_result_row['electronicDance'].",".$sql_ind_aggr_genre_avg_query_result_row['folk'].",".$sql_ind_aggr_genre_avg_query_result_row['indieAlternative'].",".$sql_ind_aggr_genre_avg_query_result_row['jazz'].",".$sql_ind_aggr_genre_avg_query_result_row['latin'].",".$sql_ind_aggr_genre_avg_query_result_row['metal'].",".$sql_ind_aggr_genre_avg_query_result_row['pop'].",".$sql_ind_aggr_genre_avg_query_result_row['punk'].",".$sql_ind_aggr_genre_avg_query_result_row['rapHipHop'].",".$sql_ind_aggr_genre_avg_query_result_row['reggae'].",".$sql_ind_aggr_genre_avg_query_result_row['rnb'].",".$sql_ind_aggr_genre_avg_query_result_row['rock'].",".$sql_ind_aggr_genre_avg_query_result_row['singerSongwriter'].")";							
											
													if($conn->query($ind_genre_ins))
													{
														$conn->query("DELETE FROM tbl_sub_industry_genre_aggr_graph_temp_data WHERE sind_id=".$sub_ind_id." and sind_year=".$cv_year);
														error_log("sub_industry_genre_aggr_graph data insetred for sub industry ".$sub_ind_id." ".$cv_year);
													}
												}
											}
											error_log("Sub Industry graph generation completed for Sub Industry - ".$sub_ind_id." ".$cv_year);
										}
										
									}

								}
							}
						}
						
					}					
				}
				
				error_log("====================================================================================================================================================");
			}			
		}
		catch(Exception $e)
		{
			error_log("page : [db_dump] : function [aggr_of_aggr] : error : ".$e->getMessage());
			$sonic_functions->trigger_log_email("db_dump","aggr_of_aggr	",$e->getMessage());
		}
	}

	function ins_updt_monthly_graph_data($cv_id_array)
	{
		$dbcon = include('config/config.php');
		$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);

		foreach($cv_id_array as $cv_id)
		{
			$get_process_types_qry = "SELECT DISTINCT (process_type) as process_type FROM `tbl_social_spyder_graph_meta_data` WHERE cv_id=".$cv_id." and is_active=0";
			$get_process_types_qry_result = $conn->query($get_process_types_qry);
			if ($get_process_types_qry_result->num_rows > 0) 
			{
				//$process_arr = array();
				while($get_process_types_qry_result_row = $get_process_types_qry_result->fetch_assoc())
				{
					$current_process_type = $get_process_types_qry_result_row['process_type'];
					//echo $current_process_type."<br>";
					$get_months_qry = "SELECT DISTINCT(substring_index(substring_index(substring_index(video_published_at,'T',1),'-',2),'-',-1)) as month FROM `tbl_social_spyder_graph_meta_data` WHERE process_type='".$current_process_type."' and cv_id=".$cv_id." and is_active=0 and status >2 ORDER by month asc";
					$get_months_qry_result = $conn->query($get_months_qry);
					if ($get_months_qry_result->num_rows > 0) 
					{
						while($get_months_qry_result_row = $get_months_qry_result->fetch_assoc())
						{
							if($current_process_type != 'twitter')
							{							
								//echo $get_months_qry_result_row['month']."<br>";
								$month = $get_months_qry_result_row['month'];
								//echo $cv_id."----".$process_type."----".$month."<br>";
								$get_month_data_qry = "SELECT cv_id,track_id FROM `tbl_social_spyder_graph_meta_data` WHERE process_type='".$current_process_type."' and cv_id=".$cv_id." and `video_published_at` like '%-".$month."-%' and is_active=0 and status >2;";
								$get_month_data_qry_result = $conn->query($get_month_data_qry);
								if ($get_month_data_qry_result->num_rows > 0) 
								{
									$track_id_arr = array();
									while($get_month_data_qry_result_row = $get_month_data_qry_result->fetch_assoc())
									{
										array_push($track_id_arr,$get_month_data_qry_result_row['track_id']);
									}
									//print_r($track_id_arr);
									if(count($track_id_arr) !=0)
									{
										$track_id_arr_str = implode(",",$track_id_arr);
										$get_months_cyanite_data_qry = "SELECT 
						                    cyanite.LTrack_id,
						                    cyanite.brand_id,
						                    json_extract(cyanite.mood, '$.aggressive') AS aggressive,
						                    json_extract(cyanite.mood, '$.calm') AS calm,
						                    json_extract(cyanite.mood, '$.chilled') AS chilled,
						                    json_extract(cyanite.mood, '$.dark') AS dark,
						                    json_extract(cyanite.mood, '$.energetic') AS energetic,
						                    json_extract(cyanite.mood, '$.epic') AS epic,
						                    json_extract(cyanite.mood, '$.happy') AS happy,
						                    json_extract(cyanite.mood, '$.romantic') AS romantic,
						                    json_extract(cyanite.mood, '$.sad') AS sad,
						                    json_extract(cyanite.mood, '$.scary') AS scary,
						                    json_extract(cyanite.mood, '$.sexy') AS sexy,
						                    json_extract(cyanite.mood, '$.ethereal') AS ethereal,
						                    json_extract(cyanite.mood, '$.uplifting') AS uplifting,
											json_extract(cyanite.genre, '$.ambient') AS ambient,
											json_extract(cyanite.genre, '$.blues') AS blues,
											json_extract(cyanite.genre, '$.classical') AS classical,
											json_extract(cyanite.genre, '$.country') AS country,
											json_extract(cyanite.genre, '$.electronicDance') AS electronicDance,
											json_extract(cyanite.genre, '$.folk') AS folk,
											json_extract(cyanite.genre, '$.indieAlternative') AS indieAlternative,
											json_extract(cyanite.genre, '$.jazz') AS jazz,
											json_extract(cyanite.genre, '$.latin') AS latin,
											json_extract(cyanite.genre, '$.metal') AS metal,
											json_extract(cyanite.genre, '$.pop') AS pop,
											json_extract(cyanite.genre, '$.punk') AS punk,
											json_extract(cyanite.genre, '$.rapHipHop') AS rapHipHop,
											json_extract(cyanite.genre, '$.reggae') AS reggae,
											json_extract(cyanite.genre, '$.rnb') AS rnb,
											json_extract(cyanite.genre, '$.rock') AS rock,
											json_extract(cyanite.genre, '$.singerSongwriter') AS singerSongwriter  
						                    FROM cyanite where cyanite.brand_id=".$cv_id." and cyanite.LTrack_id IN (".$track_id_arr_str.")  and cyanite.process_type='".$current_process_type."' and cyanite.is_active=0";
						                $get_months_cyanite_data_qry_result = $conn->query($get_months_cyanite_data_qry);
										if ($get_months_cyanite_data_qry_result->num_rows > 0) 
										{
											if($current_process_type == 'youtube')
											{
												$process_type = 1;
											}
											elseif($current_process_type == 'instagram')
											{
												$process_type = 2;
											}
											else
											{
												$process_type = 3;
											}

											$ins_month_mood_temp_query = "insert into tbl_month_mood_graph_temp_data (cv_id,track_id,process_type,month,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values ";

											$ins_month_genre_temp_query = "insert into tbl_month_genre_graph_temp_data (cv_id,track_id,process_type,month,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values ";
											while($get_months_cyanite_data_qry_result_row = $get_months_cyanite_data_qry_result->fetch_assoc())
											{
												$ins_month_mood_temp_query .= "(".$cv_id.",".$get_months_cyanite_data_qry_result_row['LTrack_id'].",".$process_type.",".$month.",".$get_months_cyanite_data_qry_result_row['aggressive'].",".$get_months_cyanite_data_qry_result_row['calm'].",".$get_months_cyanite_data_qry_result_row['chilled'].",".$get_months_cyanite_data_qry_result_row['dark'].",".$get_months_cyanite_data_qry_result_row['energetic'].",".$get_months_cyanite_data_qry_result_row['epic'].",".$get_months_cyanite_data_qry_result_row['happy'].",".$get_months_cyanite_data_qry_result_row['romantic'].",".$get_months_cyanite_data_qry_result_row['sad'].",".$get_months_cyanite_data_qry_result_row['scary'].",".$get_months_cyanite_data_qry_result_row['sexy'].",".$get_months_cyanite_data_qry_result_row['ethereal'].",".$get_months_cyanite_data_qry_result_row['uplifting']."),";

												$ins_month_genre_temp_query .= "(".$cv_id.",".$get_months_cyanite_data_qry_result_row['LTrack_id'].",".$process_type.",".$month.",".$get_months_cyanite_data_qry_result_row['ambient'].",".$get_months_cyanite_data_qry_result_row['blues'].",".$get_months_cyanite_data_qry_result_row['classical'].",".$get_months_cyanite_data_qry_result_row['country'].",".$get_months_cyanite_data_qry_result_row['electronicDance'].",".$get_months_cyanite_data_qry_result_row['folk'].",".$get_months_cyanite_data_qry_result_row['indieAlternative'].",".$get_months_cyanite_data_qry_result_row['jazz'].",".$get_months_cyanite_data_qry_result_row['latin'].",".$get_months_cyanite_data_qry_result_row['metal'].",".$get_months_cyanite_data_qry_result_row['pop'].",".$get_months_cyanite_data_qry_result_row['punk'].",".$get_months_cyanite_data_qry_result_row['rapHipHop'].",".$get_months_cyanite_data_qry_result_row['reggae'].",".$get_months_cyanite_data_qry_result_row['rnb'].",".$get_months_cyanite_data_qry_result_row['rock'].",".$get_months_cyanite_data_qry_result_row['singerSongwriter']."),";
											}
											$multi_ins_month_mood_temp_query = rtrim($ins_month_mood_temp_query,",");
											//echo $multi_ins_month_mood_temp_query."<br><br>";

											$multi_ins_month_genre_temp_query = rtrim($ins_month_genre_temp_query,",");
											//echo $multi_ins_month_genre_temp_query."<br><br>";

											//echo $cv_id."----".$process_type."----".$month."<br>";

											if($conn->query($multi_ins_month_mood_temp_query) && $conn->query($multi_ins_month_genre_temp_query))
											{
												error_log("[db_dump] : function [ins_updt_monthly_graph_data] : Mood and Genre graph data is inserted into temp table for month:".$month." of cv:".$cv_id);

												$month_mood_avg_query = "SELECT round(AVG(`aggressive`),2) as aggressive, round(AVG(`calm`),2) as calm, round(AVG(`chilled`),2) as chilled, round(AVG(`dark`),2) as dark, round(AVG(`energetic`),2) as energetic, round(AVG(`epic`),2) as epic, round(AVG(`happy`),2) as happy, round(AVG(`romantic`),2) as romantic, round(AVG(`sad`),2) as sad, round(AVG(`scary`),2) as scary, round(AVG(`sexy`),2) as sexy, round(AVG(`ethereal`),2) as ethereal, round(AVG(`uplifting`),2) as uplifting FROM `tbl_month_mood_graph_temp_data` WHERE `cv_id` =".$cv_id." and process_type='".$process_type."' and month=".$month;
												//echo $month_mood_avg_query."<br><br>";
												$month_mood_avg_query_result = $conn->query($month_mood_avg_query);
												if ($month_mood_avg_query_result->num_rows > 0)
												{
													$chk_month_mood_graph_data_query = "select * from tbl_month_mood_graph_data where cv_id =".$cv_id." and process_type=".$process_type." and month=".$month;
													$chk_month_mood_graph_data_query_res = $conn->query($chk_month_mood_graph_data_query);
													if($chk_month_mood_graph_data_query_res->num_rows > 0)
													{
														while($month_mood_avg_query_result_row = $month_mood_avg_query_result->fetch_assoc())
											  			{
															$ms1 = "UPDATE `tbl_month_genre_graph_data` SET `aggressive`='".$month_mood_avg_query_result_row['aggressive']."',`calm`='".$month_mood_avg_query_result_row['calm']."',`chilled`='".$month_mood_avg_query_result_row['chilled']."',`dark`='".$month_mood_avg_query_result_row['dark']."',`energetic`='".$month_mood_avg_query_result_row['energetic']."',`epic`='".$month_mood_avg_query_result_row['epic']."',`happy`='".$month_mood_avg_query_result_row['happy']."',`romantic`='".$month_mood_avg_query_result_row['romantic']."',`sad`='".$month_mood_avg_query_result_row['sad']."',`scary`='".$month_mood_avg_query_result_row['scary']."',`sexy`='".$month_mood_avg_query_result_row['sexy']."',`ethereal`='".$month_mood_avg_query_result_row['ethereal']."',`uplifting`='".$month_mood_avg_query_result_row['uplifting']."' WHERE cv_id =".$cv_id." and process_type=".$process_type." and month=".$month;
															error_log("page : [db_dump] : function [ins_updt_monthly_graph_data] update query generated for cv_id=".$cv_id);
															//echo $ms1."-----------------------------------------------------------------------------<br><br>";
														}
														//$conn->query("DELETE FROM tbl_month_mood_graph_data WHERE cv_id=".$cv_id);
														
													}
													else
													{
														while($month_mood_avg_query_result_row = $month_mood_avg_query_result->fetch_assoc())
											  			{
															$ms1 = "insert into tbl_month_mood_graph_data(cv_id,process_type,month,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values(".$cv_id.",".$process_type.",".$month.",".$month_mood_avg_query_result_row['aggressive'].",".$month_mood_avg_query_result_row['calm'].",".$month_mood_avg_query_result_row['chilled'].",".$month_mood_avg_query_result_row['dark'].",".$month_mood_avg_query_result_row['energetic'].",".$month_mood_avg_query_result_row['epic'].",".$month_mood_avg_query_result_row['happy'].",".$month_mood_avg_query_result_row['romantic'].",".$month_mood_avg_query_result_row['sad'].",".$month_mood_avg_query_result_row['scary'].",".$month_mood_avg_query_result_row['sexy'].",".$month_mood_avg_query_result_row['ethereal'].",".$month_mood_avg_query_result_row['uplifting'].")";
															error_log("page : [db_dump] : function [ins_updt_monthly_graph_data] insert query generated for cv_id=".$cv_id);
															//echo $ms1."-----------------------------------------------------------------------------<br><br>";
														}
													}

													if($conn->query($ms1))
													{
														//echo "data inserted for cv".$cv_id." and process_type=".$process_type." and month:".$month." into tbl_month_mood_graph_data<br>";
														error_log("[db_dump] : function [ins_updt_monthly_graph_data] : Aggr data inserted / updated for cv".$cv_id." and process_type=".$process_type." and month:".$month." into tbl_month_mood_graph_data");
													}
												}

												$month_genre_avg_query = "SELECT round(AVG(`ambient`),2) as ambient, round(AVG(`blues`),2) as blues, round(AVG(`classical`),2) as classical, round(AVG(`country`),2) as country, round(AVG(`electronicDance`),2) as electronicDance, round(AVG(`folk`),2) as folk, round(AVG(`indieAlternative`),2) as indieAlternative, round(AVG(`jazz`),2) as jazz, round(AVG(`latin`),2) as latin, round(AVG(`metal`),2) as metal, round(AVG(`pop`),2) as pop, round(AVG(`punk`),2) as punk, round(AVG(`rapHipHop`),2) as rapHipHop, round(AVG(`reggae`),2) as reggae, round(AVG(`rnb`),2) as rnb, round(AVG(`rock`),2) as rock, round(AVG(`singerSongwriter`),2) as singerSongwriter FROM `tbl_month_genre_graph_temp_data` WHERE `cv_id` =".$cv_id." and process_type='".$process_type."' and month=".$month;
												//echo $month_genre_avg_query."<br><br>";
												$month_genre_avg_query_result = $conn->query($month_genre_avg_query);
												if ($month_genre_avg_query_result->num_rows > 0)
												{
													$chk_month_genre_graph_data_query = "select * from tbl_month_genre_graph_data where cv_id =".$cv_id." and process_type=".$process_type." and month=".$month;
													$chk_month_genre_graph_data_query_res = $conn->query($chk_month_genre_graph_data_query);
													if($chk_month_genre_graph_data_query_res->num_rows > 0)
													{
														while($month_genre_avg_query_result_row = $month_genre_avg_query_result->fetch_assoc())
										  				{
															//$conn->query("DELETE FROM tbl_month_genre_graph_data WHERE cv_id=".$cv_id);
															$ms2 = "UPDATE `tbl_month_genre_graph_data` SET `ambient`='".$month_genre_avg_query_result_row['ambient']."',`blues`='".$month_genre_avg_query_result_row['blues']."',`classical`='".$month_genre_avg_query_result_row['classical']."',`country`='".$month_genre_avg_query_result_row['country']."',`electronicDance`='".$month_genre_avg_query_result_row['electronicDance']."',`folk`='".$month_genre_avg_query_result_row['folk']."',`indieAlternative`='".$month_genre_avg_query_result_row['indieAlternative']."',`jazz`='".$month_genre_avg_query_result_row['jazz']."',`latin`='".$month_genre_avg_query_result_row['latin']."',`metal`='".$month_genre_avg_query_result_row['metal']."',`pop`='".$month_genre_avg_query_result_row['pop']."',`punk`='".$month_genre_avg_query_result_row['punk']."',`rapHipHop`='".$month_genre_avg_query_result_row['rapHipHop']."',`reggae`='".$month_genre_avg_query_result_row['reggae']."',`rnb`='".$month_genre_avg_query_result_row['rnb']."',`rock`='".$month_genre_avg_query_result_row['rock']."',`singerSongwriter`='".$month_genre_avg_query_result_row['singerSongwriter']."' WHERE cv_id =".$cv_id." and process_type=".$process_type." and month=".$month;
															error_log("page : [db_dump] : function [ins_updt_monthly_graph_data] update query generated for cv_id=".$cv_id);
															//echo $ms2."-----------------------------------------------------------------------------<br><br>";
														}
													}
													else
													{
														while($month_genre_avg_query_result_row = $month_genre_avg_query_result->fetch_assoc())
											  			{
															$ms2 = "insert into tbl_month_genre_graph_data(cv_id,process_type,month,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values(".$cv_id.",".$process_type.",".$month.",".$month_genre_avg_query_result_row['ambient'].",".$month_genre_avg_query_result_row['blues'].",".$month_genre_avg_query_result_row['classical'].",".$month_genre_avg_query_result_row['country'].",".$month_genre_avg_query_result_row['electronicDance'].",".$month_genre_avg_query_result_row['folk'].",".$month_genre_avg_query_result_row['indieAlternative'].",".$month_genre_avg_query_result_row['jazz'].",".$month_genre_avg_query_result_row['latin'].",".$month_genre_avg_query_result_row['metal'].",".$month_genre_avg_query_result_row['pop'].",".$month_genre_avg_query_result_row['punk'].",".$month_genre_avg_query_result_row['rapHipHop'].",".$month_genre_avg_query_result_row['reggae'].",".$month_genre_avg_query_result_row['rnb'].",".$month_genre_avg_query_result_row['rock'].",".$month_genre_avg_query_result_row['singerSongwriter'].")";
															//echo $ms2."-----------------------------------------------------------------------------<br><br>";
														}
													}
													if($conn->query($ms2))
													{
														//echo "data inserted for cv".$cv_id." and process_type=".$process_type." and month:".$month." into tbl_month_genre_graph_data<br>";
														error_log("[db_dump] : function [ins_updt_monthly_graph_data] : Aggr data inserted / updated for cv".$cv_id." and process_type=".$process_type." and month:".$month." into tbl_month_genre_graph_data");
													}
												}
											}
										}
									}
								}
							}
							else
							{
								
								//echo date_format(date_create($get_months_qry_result_row['month']),"M")."<br>";
								$month = date_format(date_create($get_months_qry_result_row['month']),"m");
								$get_month_data_qry = "SELECT cv_id,track_id FROM `tbl_social_spyder_graph_meta_data` WHERE process_type='".$current_process_type."' and cv_id=".$cv_id." and `video_published_at` like '% ".date_format(date_create($get_months_qry_result_row['month']),"M")." %' and is_active=0 and status >2;";
								$get_month_data_qry_result = $conn->query($get_month_data_qry);
								if ($get_month_data_qry_result->num_rows > 0) 
								{
									$track_id_arr = array();
									while($get_month_data_qry_result_row = $get_month_data_qry_result->fetch_assoc())
									{
										array_push($track_id_arr,$get_month_data_qry_result_row['track_id']);
									}
									
									if(count($track_id_arr) !=0)
									{
										$track_id_arr_str = implode(",",$track_id_arr);
										$get_months_cyanite_data_qry = "SELECT 
						                    cyanite.LTrack_id,
						                    cyanite.brand_id,
						                    json_extract(cyanite.mood, '$.aggressive') AS aggressive,
						                    json_extract(cyanite.mood, '$.calm') AS calm,
						                    json_extract(cyanite.mood, '$.chilled') AS chilled,
						                    json_extract(cyanite.mood, '$.dark') AS dark,
						                    json_extract(cyanite.mood, '$.energetic') AS energetic,
						                    json_extract(cyanite.mood, '$.epic') AS epic,
						                    json_extract(cyanite.mood, '$.happy') AS happy,
						                    json_extract(cyanite.mood, '$.romantic') AS romantic,
						                    json_extract(cyanite.mood, '$.sad') AS sad,
						                    json_extract(cyanite.mood, '$.scary') AS scary,
						                    json_extract(cyanite.mood, '$.sexy') AS sexy,
						                    json_extract(cyanite.mood, '$.ethereal') AS ethereal,
						                    json_extract(cyanite.mood, '$.uplifting') AS uplifting,
											json_extract(cyanite.genre, '$.ambient') AS ambient,
											json_extract(cyanite.genre, '$.blues') AS blues,
											json_extract(cyanite.genre, '$.classical') AS classical,
											json_extract(cyanite.genre, '$.country') AS country,
											json_extract(cyanite.genre, '$.electronicDance') AS electronicDance,
											json_extract(cyanite.genre, '$.folk') AS folk,
											json_extract(cyanite.genre, '$.indieAlternative') AS indieAlternative,
											json_extract(cyanite.genre, '$.jazz') AS jazz,
											json_extract(cyanite.genre, '$.latin') AS latin,
											json_extract(cyanite.genre, '$.metal') AS metal,
											json_extract(cyanite.genre, '$.pop') AS pop,
											json_extract(cyanite.genre, '$.punk') AS punk,
											json_extract(cyanite.genre, '$.rapHipHop') AS rapHipHop,
											json_extract(cyanite.genre, '$.reggae') AS reggae,
											json_extract(cyanite.genre, '$.rnb') AS rnb,
											json_extract(cyanite.genre, '$.rock') AS rock,
											json_extract(cyanite.genre, '$.singerSongwriter') AS singerSongwriter  
						                    FROM cyanite where cyanite.brand_id=".$cv_id." and cyanite.LTrack_id IN (".$track_id_arr_str.")  and cyanite.process_type='".$current_process_type."' and cyanite.is_active=0";
						                $get_months_cyanite_data_qry_result = $conn->query($get_months_cyanite_data_qry);
										if ($get_months_cyanite_data_qry_result->num_rows > 0) 
										{
											$process_type = 4;
											$ins_month_mood_temp_query = "insert into tbl_month_mood_graph_temp_data (cv_id,track_id,process_type,month,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values ";

											$ins_month_genre_temp_query = "insert into tbl_month_genre_graph_temp_data (cv_id,track_id,process_type,month,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values ";
											while($get_months_cyanite_data_qry_result_row = $get_months_cyanite_data_qry_result->fetch_assoc())
											{
												$ins_month_mood_temp_query .= "(".$cv_id.",".$get_months_cyanite_data_qry_result_row['LTrack_id'].",".$process_type.",".$month.",".$get_months_cyanite_data_qry_result_row['aggressive'].",".$get_months_cyanite_data_qry_result_row['calm'].",".$get_months_cyanite_data_qry_result_row['chilled'].",".$get_months_cyanite_data_qry_result_row['dark'].",".$get_months_cyanite_data_qry_result_row['energetic'].",".$get_months_cyanite_data_qry_result_row['epic'].",".$get_months_cyanite_data_qry_result_row['happy'].",".$get_months_cyanite_data_qry_result_row['romantic'].",".$get_months_cyanite_data_qry_result_row['sad'].",".$get_months_cyanite_data_qry_result_row['scary'].",".$get_months_cyanite_data_qry_result_row['sexy'].",".$get_months_cyanite_data_qry_result_row['ethereal'].",".$get_months_cyanite_data_qry_result_row['uplifting']."),";

												$ins_month_genre_temp_query .= "(".$cv_id.",".$get_months_cyanite_data_qry_result_row['LTrack_id'].",".$process_type.",".$month.",".$get_months_cyanite_data_qry_result_row['ambient'].",".$get_months_cyanite_data_qry_result_row['blues'].",".$get_months_cyanite_data_qry_result_row['classical'].",".$get_months_cyanite_data_qry_result_row['country'].",".$get_months_cyanite_data_qry_result_row['electronicDance'].",".$get_months_cyanite_data_qry_result_row['folk'].",".$get_months_cyanite_data_qry_result_row['indieAlternative'].",".$get_months_cyanite_data_qry_result_row['jazz'].",".$get_months_cyanite_data_qry_result_row['latin'].",".$get_months_cyanite_data_qry_result_row['metal'].",".$get_months_cyanite_data_qry_result_row['pop'].",".$get_months_cyanite_data_qry_result_row['punk'].",".$get_months_cyanite_data_qry_result_row['rapHipHop'].",".$get_months_cyanite_data_qry_result_row['reggae'].",".$get_months_cyanite_data_qry_result_row['rnb'].",".$get_months_cyanite_data_qry_result_row['rock'].",".$get_months_cyanite_data_qry_result_row['singerSongwriter']."),";
											}
											$multi_ins_month_mood_temp_query = rtrim($ins_month_mood_temp_query,",");
											//echo $multi_ins_month_mood_temp_query."<br><br>";

											$multi_ins_month_genre_temp_query = rtrim($ins_month_genre_temp_query,",");
											//echo $multi_ins_month_genre_temp_query."<br><br>";

											//echo $cv_id."----".$process_type."----".$month."<br>";

											if($conn->query($multi_ins_month_mood_temp_query) && $conn->query($multi_ins_month_genre_temp_query))
											{
												error_log("[db_dump] : function [ins_updt_monthly_graph_data] : Mood and Genre graph data is inserted into temp table for month:".$month." of cv:".$cv_id);
												$month_mood_avg_query = "SELECT round(AVG(`aggressive`),2) as aggressive, round(AVG(`calm`),2) as calm, round(AVG(`chilled`),2) as chilled, round(AVG(`dark`),2) as dark, round(AVG(`energetic`),2) as energetic, round(AVG(`epic`),2) as epic, round(AVG(`happy`),2) as happy, round(AVG(`romantic`),2) as romantic, round(AVG(`sad`),2) as sad, round(AVG(`scary`),2) as scary, round(AVG(`sexy`),2) as sexy, round(AVG(`ethereal`),2) as ethereal, round(AVG(`uplifting`),2) as uplifting FROM `tbl_month_mood_graph_temp_data` WHERE `cv_id` =".$cv_id." and process_type='".$process_type."' and month=".$month;
												//echo $month_mood_avg_query."<br><br>";
												$month_mood_avg_query_result = $conn->query($month_mood_avg_query);
												if ($month_mood_avg_query_result->num_rows > 0)
												{
													$chk_month_mood_graph_data_query = "select * from tbl_month_mood_graph_data where cv_id =".$cv_id." and process_type=".$process_type." and month=".$month;
													$chk_month_mood_graph_data_query_res = $conn->query($chk_month_mood_graph_data_query);
													if($chk_month_mood_graph_data_query_res->num_rows > 0)
													{
														while($month_mood_avg_query_result_row = $month_mood_avg_query_result->fetch_assoc())
											  			{
															$ms1 = "UPDATE `tbl_month_mood_graph_data` SET `aggressive`='".$month_mood_avg_query_result_row['aggressive']."',`calm`='".$month_mood_avg_query_result_row['calm']."',`chilled`='".$month_mood_avg_query_result_row['chilled']."',`dark`='".$month_mood_avg_query_result_row['dark']."',`energetic`='".$month_mood_avg_query_result_row['energetic']."',`epic`='".$month_mood_avg_query_result_row['epic']."',`happy`='".$month_mood_avg_query_result_row['happy']."',`romantic`='".$month_mood_avg_query_result_row['romantic']."',`sad`='".$month_mood_avg_query_result_row['sad']."',`scary`='".$month_mood_avg_query_result_row['scary']."',`sexy`='".$month_mood_avg_query_result_row['sexy']."',`ethereal`='".$month_mood_avg_query_result_row['ethereal']."',`uplifting`='".$month_mood_avg_query_result_row['uplifting']."' WHERE cv_id =".$cv_id." and process_type=".$process_type." and month=".$month;
															error_log("page : [db_dump] : function [ins_updt_monthly_graph_data] update query generated for cv_id=".$cv_id);
															//echo $ms1."-----------------------------------------------------------------------------<br><br>";
														}
													}
													else
													{
														while($month_mood_avg_query_result_row = $month_mood_avg_query_result->fetch_assoc())
											  			{
															$ms1 = "insert into tbl_month_mood_graph_data(cv_id,process_type,month,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values(".$cv_id.",".$process_type.",".$month.",".$month_mood_avg_query_result_row['aggressive'].",".$month_mood_avg_query_result_row['calm'].",".$month_mood_avg_query_result_row['chilled'].",".$month_mood_avg_query_result_row['dark'].",".$month_mood_avg_query_result_row['energetic'].",".$month_mood_avg_query_result_row['epic'].",".$month_mood_avg_query_result_row['happy'].",".$month_mood_avg_query_result_row['romantic'].",".$month_mood_avg_query_result_row['sad'].",".$month_mood_avg_query_result_row['scary'].",".$month_mood_avg_query_result_row['sexy'].",".$month_mood_avg_query_result_row['ethereal'].",".$month_mood_avg_query_result_row['uplifting'].")";
															error_log("page : [db_dump] : function [ins_updt_monthly_graph_data] insert query generated for cv_id=".$cv_id);
															//echo $ms1."-----------------------------------------------------------------------------<br><br>";
														}
													}												
													if($conn->query($ms1))
													{
														//echo "data inserted for cv".$cv_id." and process_type=".$process_type." and month:".$month." into tbl_month_mood_graph_data<br>";
														error_log("[db_dump] : function [ins_updt_monthly_graph_data] : Aggr data inserted / updated for cv".$cv_id." and process_type=".$process_type." and month:".$month." into tbl_month_mood_graph_data");
													}
												}

												$month_genre_avg_query = "SELECT round(AVG(`ambient`),2) as ambient, round(AVG(`blues`),2) as blues, round(AVG(`classical`),2) as classical, round(AVG(`country`),2) as country, round(AVG(`electronicDance`),2) as electronicDance, round(AVG(`folk`),2) as folk, round(AVG(`indieAlternative`),2) as indieAlternative, round(AVG(`jazz`),2) as jazz, round(AVG(`latin`),2) as latin, round(AVG(`metal`),2) as metal, round(AVG(`pop`),2) as pop, round(AVG(`punk`),2) as punk, round(AVG(`rapHipHop`),2) as rapHipHop, round(AVG(`reggae`),2) as reggae, round(AVG(`rnb`),2) as rnb, round(AVG(`rock`),2) as rock, round(AVG(`singerSongwriter`),2) as singerSongwriter FROM `tbl_month_genre_graph_temp_data` WHERE `cv_id` =".$cv_id." and process_type='".$process_type."' and month=".$month;
												//echo $month_genre_avg_query."<br><br>";
												$month_genre_avg_query_result = $conn->query($month_genre_avg_query);
												if ($month_genre_avg_query_result->num_rows > 0)
												{
													$chk_month_genre_graph_data_query = "select * from tbl_month_genre_graph_data where cv_id =".$cv_id." and process_type=".$process_type." and month=".$month;
													$chk_month_genre_graph_data_query_res = $conn->query($chk_month_genre_graph_data_query);
													if($chk_month_genre_graph_data_query_res->num_rows > 0)
													{
														while($month_genre_avg_query_result_row = $month_genre_avg_query_result->fetch_assoc())
										  				{
															//$conn->query("DELETE FROM tbl_month_genre_graph_data WHERE cv_id=".$cv_id);
															$ms2 = "UPDATE `tbl_month_genre_graph_data` SET `ambient`='".$month_genre_avg_query_result_row['ambient']."',`blues`='".$month_genre_avg_query_result_row['blues']."',`classical`='".$month_genre_avg_query_result_row['classical']."',`country`='".$month_genre_avg_query_result_row['country']."',`electronicDance`='".$month_genre_avg_query_result_row['electronicDance']."',`folk`='".$month_genre_avg_query_result_row['folk']."',`indieAlternative`='".$month_genre_avg_query_result_row['indieAlternative']."',`jazz`='".$month_genre_avg_query_result_row['jazz']."',`latin`='".$month_genre_avg_query_result_row['latin']."',`metal`='".$month_genre_avg_query_result_row['metal']."',`pop`='".$month_genre_avg_query_result_row['pop']."',`punk`='".$month_genre_avg_query_result_row['punk']."',`rapHipHop`='".$month_genre_avg_query_result_row['rapHipHop']."',`reggae`='".$month_genre_avg_query_result_row['reggae']."',`rnb`='".$month_genre_avg_query_result_row['rnb']."',`rock`='".$month_genre_avg_query_result_row['rock']."',`singerSongwriter`='".$month_genre_avg_query_result_row['singerSongwriter']."' WHERE cv_id =".$cv_id." and process_type=".$process_type." and month=".$month;
															error_log("page : [db_dump] : function [ins_updt_monthly_graph_data] update query generated for cv_id=".$cv_id);
															//echo $ms2."-----------------------------------------------------------------------------<br><br>";
														}
													}
													else
													{
											  			while($month_genre_avg_query_result_row = $month_genre_avg_query_result->fetch_assoc())
											  			{
															$ms2 = "insert into tbl_month_genre_graph_data(cv_id,process_type,month,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values(".$cv_id.",".$process_type.",".$month.",".$month_genre_avg_query_result_row['ambient'].",".$month_genre_avg_query_result_row['blues'].",".$month_genre_avg_query_result_row['classical'].",".$month_genre_avg_query_result_row['country'].",".$month_genre_avg_query_result_row['electronicDance'].",".$month_genre_avg_query_result_row['folk'].",".$month_genre_avg_query_result_row['indieAlternative'].",".$month_genre_avg_query_result_row['jazz'].",".$month_genre_avg_query_result_row['latin'].",".$month_genre_avg_query_result_row['metal'].",".$month_genre_avg_query_result_row['pop'].",".$month_genre_avg_query_result_row['punk'].",".$month_genre_avg_query_result_row['rapHipHop'].",".$month_genre_avg_query_result_row['reggae'].",".$month_genre_avg_query_result_row['rnb'].",".$month_genre_avg_query_result_row['rock'].",".$month_genre_avg_query_result_row['singerSongwriter'].")";
															error_log("page : [db_dump] : function [ins_updt_monthly_graph_data] insert query generated for cv_id=".$cv_id);
															//echo $ms2."-----------------------------------------------------------------------------<br><br>";
														}
													}
													if($conn->query($ms2))
													{
														//echo "data inserted for cv".$cv_id." and process_type=".$process_type." and month:".$month." into tbl_month_genre_graph_data<br>";
														error_log("[db_dump] : function [ins_updt_monthly_graph_data] : Aggr data inserted / updated for cv".$cv_id." and process_type=".$process_type." and month:".$month." into tbl_month_genre_graph_data");
													}
												}
											}
										}
									}
								}
							}
						}
					}
					else
					{										
						//echo "no month found for process_type".$process_type." cv ".$cv_id;
						error_log("[db_dump] : function [ins_updt_monthly_graph_data] : no month found for process_type".$current_process_type." cv ".$cv_id);
					}
				}

				if($get_process_types_qry_result->num_rows > 1)
				{
					$process_type = 5;
					$get_mnt_qry = "SELECT DISTINCT(`month`) as month FROM `tbl_month_genre_graph_temp_data` WHERE `cv_id`=".$cv_id;
					$get_mnt_qry_result = $conn->query($get_mnt_qry);
					if($get_mnt_qry_result->num_rows>0)
					{
						while ($get_mnt_qry_result_row = $get_mnt_qry_result->fetch_assoc()) {
							$mood_avg_query = "SELECT round(AVG(`aggressive`),2) as aggressive, round(AVG(`calm`),2) as calm, round(AVG(`chilled`),2) as chilled, round(AVG(`dark`),2) as dark, round(AVG(`energetic`),2) as energetic, round(AVG(`epic`),2) as epic, round(AVG(`happy`),2) as happy, round(AVG(`romantic`),2) as romantic, round(AVG(`sad`),2) as sad, round(AVG(`scary`),2) as scary, round(AVG(`sexy`),2) as sexy, round(AVG(`ethereal`),2) as ethereal, round(AVG(`uplifting`),2) as uplifting FROM `tbl_month_mood_graph_temp_data` WHERE `cv_id` =".$cv_id." and month=".$get_mnt_qry_result_row['month'];
							//echo $mood_avg_query."<br><br>";
							$mood_avg_query_result = $conn->query($mood_avg_query);
							if ($mood_avg_query_result->num_rows > 0)
							{
								$chk_month_mood_graph_data_query = "select * from tbl_month_mood_graph_data where cv_id =".$cv_id." and process_type=".$process_type." and month=".$get_mnt_qry_result_row['month'];
								$chk_month_mood_graph_data_query_res = $conn->query($chk_month_mood_graph_data_query);
								if($chk_month_mood_graph_data_query_res->num_rows > 0)
								{
						  			while($mood_avg_query_result_row = $mood_avg_query_result->fetch_assoc())
						  			{
										$ms3 = "UPDATE `tbl_month_mood_graph_data` SET `aggressive`='".$mood_avg_query_result_row['aggressive']."',`calm`='".$mood_avg_query_result_row['calm']."',`chilled`='".$mood_avg_query_result_row['chilled']."',`dark`='".$mood_avg_query_result_row['dark']."',`energetic`='".$mood_avg_query_result_row['energetic']."',`epic`='".$mood_avg_query_result_row['epic']."',`happy`='".$mood_avg_query_result_row['happy']."',`romantic`='".$mood_avg_query_result_row['romantic']."',`sad`='".$mood_avg_query_result_row['sad']."',`scary`='".$mood_avg_query_result_row['scary']."',`sexy`='".$mood_avg_query_result_row['sexy']."',`ethereal`='".$mood_avg_query_result_row['ethereal']."',`uplifting`='".$mood_avg_query_result_row['uplifting']."' WHERE cv_id =".$cv_id." and process_type=".$process_type." and month=".$get_mnt_qry_result_row['month'];
										error_log("page : [db_dump] : function [ins_updt_monthly_graph_data] update query generated for cv_id=".$cv_id);
										//echo $ms3."-----------------------------------------------------------------------------<br><br>";
									}
								}
								else
								{
									while($mood_avg_query_result_row = $mood_avg_query_result->fetch_assoc())
						  			{
										$ms3 = "insert into tbl_month_mood_graph_data(cv_id,process_type,month,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values(".$cv_id.",".$process_type.",".$get_mnt_qry_result_row['month'].",".$mood_avg_query_result_row['aggressive'].",".$mood_avg_query_result_row['calm'].",".$mood_avg_query_result_row['chilled'].",".$mood_avg_query_result_row['dark'].",".$mood_avg_query_result_row['energetic'].",".$mood_avg_query_result_row['epic'].",".$mood_avg_query_result_row['happy'].",".$mood_avg_query_result_row['romantic'].",".$mood_avg_query_result_row['sad'].",".$mood_avg_query_result_row['scary'].",".$mood_avg_query_result_row['sexy'].",".$mood_avg_query_result_row['ethereal'].",".$mood_avg_query_result_row['uplifting'].")";
										error_log("page : [db_dump] : function [ins_updt_monthly_graph_data] insert query generated for cv_id=".$cv_id);
										//echo $ms3."<br>-----------------------------------------------------------------------------<br><br>";
									}
								}
								if($conn->query($ms3))
								{
									//echo "data inserted for cv".$cv_id." and process_type=".$process_type." and month:".$get_mnt_qry_result_row['month']."<br>";
									$conn->query("DELETE FROM `tbl_month_mood_graph_temp_data` WHERE `cv_id` =".$cv_id." and month=".$get_mnt_qry_result_row['month']);
									error_log("[db_dump] : function [ins_updt_monthly_graph_data] : Aggr data inserted / updated for cv".$cv_id." and process_type=".$process_type." and month:".$get_mnt_qry_result_row['month']);
								}
							}

							$genre_avg_query = "SELECT round(AVG(`ambient`),2) as ambient, round(AVG(`blues`),2) as blues, round(AVG(`classical`),2) as classical, round(AVG(`country`),2) as country, round(AVG(`electronicDance`),2) as electronicDance, round(AVG(`folk`),2) as folk, round(AVG(`indieAlternative`),2) as indieAlternative, round(AVG(`jazz`),2) as jazz, round(AVG(`latin`),2) as latin, round(AVG(`metal`),2) as metal, round(AVG(`pop`),2) as pop, round(AVG(`punk`),2) as punk, round(AVG(`rapHipHop`),2) as rapHipHop, round(AVG(`reggae`),2) as reggae, round(AVG(`rnb`),2) as rnb, round(AVG(`rock`),2) as rock, round(AVG(`singerSongwriter`),2) as singerSongwriter FROM `tbl_month_genre_graph_temp_data` WHERE `cv_id` =".$cv_id." and month=".$get_mnt_qry_result_row['month'];
							//echo $genre_avg_query."<br><br>";
							$genre_avg_query_result = $conn->query($genre_avg_query);
							if ($genre_avg_query_result->num_rows > 0)
							{
								$chk_month_mood_graph_data_query = "select * from tbl_month_genre_graph_data where cv_id =".$cv_id." and process_type=".$process_type." and month=".$get_mnt_qry_result_row['month'];
								$chk_month_mood_graph_data_query_res = $conn->query($chk_month_mood_graph_data_query);
								if($chk_month_mood_graph_data_query_res->num_rows > 0)
								{
						  			while($mood_avg_query_result_row = $mood_avg_query_result->fetch_assoc())
						  			{
										$ms3 = "UPDATE `tbl_month_genre_graph_data` SET `ambient`='".$genre_avg_query_result_row['ambient']."',`blues`='".$genre_avg_query_result_row['blues']."',`classical`='".$genre_avg_query_result_row['classical']."',`country`='".$genre_avg_query_result_row['country']."',`electronicDance`='".$genre_avg_query_result_row['electronicDance']."',`folk`='".$genre_avg_query_result_row['folk']."',`indieAlternative`='".$genre_avg_query_result_row['indieAlternative']."',`jazz`='".$genre_avg_query_result_row['jazz']."',`latin`='".$genre_avg_query_result_row['latin']."',`metal`='".$genre_avg_query_result_row['metal']."',`pop`='".$genre_avg_query_result_row['pop']."',`punk`='".$genre_avg_query_result_row['punk']."',`rapHipHop`='".$genre_avg_query_result_row['rapHipHop']."',`reggae`='".$genre_avg_query_result_row['reggae']."',`rnb`='".$genre_avg_query_result_row['rnb']."',`rock`='".$genre_avg_query_result_row['rock']."',`singerSongwriter`='".$genre_avg_query_result_row['singerSongwriter']."' WHERE cv_id =".$cv_id." and process_type=".$process_type." and month=".$get_mnt_qry_result_row['month'];
										error_log("page : [db_dump] : function [ins_updt_monthly_graph_data] update query generated for cv_id=".$cv_id);
										//echo $ms3."-----------------------------------------------------------------------------<br><br>";
									}
								}
								else
								{
									while($genre_avg_query_result_row = $genre_avg_query_result->fetch_assoc())
						  			{
										$ms4 = "insert into tbl_month_genre_graph_data(cv_id,process_type,month,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values(".$cv_id.",".$process_type.",".$get_mnt_qry_result_row['month'].",".$genre_avg_query_result_row['ambient'].",".$genre_avg_query_result_row['blues'].",".$genre_avg_query_result_row['classical'].",".$genre_avg_query_result_row['country'].",".$genre_avg_query_result_row['electronicDance'].",".$genre_avg_query_result_row['folk'].",".$genre_avg_query_result_row['indieAlternative'].",".$genre_avg_query_result_row['jazz'].",".$genre_avg_query_result_row['latin'].",".$genre_avg_query_result_row['metal'].",".$genre_avg_query_result_row['pop'].",".$genre_avg_query_result_row['punk'].",".$genre_avg_query_result_row['rapHipHop'].",".$genre_avg_query_result_row['reggae'].",".$genre_avg_query_result_row['rnb'].",".$genre_avg_query_result_row['rock'].",".$genre_avg_query_result_row['singerSongwriter'].")";
										//echo $ms4."<br>-----------------------------------------------------------------------------<br><br>";
										
									}
								}

								if($conn->query($ms4))
								{
									//echo "data inserted for cv".$cv_id." and process_type=".$process_type." and month:".$get_mnt_qry_result_row['month']."<br>";
									$conn->query("DELETE FROM `tbl_month_genre_graph_temp_data` WHERE `cv_id` =".$cv_id." and month=".$get_mnt_qry_result_row['month']);
									error_log("[db_dump] : function [ins_updt_monthly_graph_data] : Aggr data inserted / updated for cv".$cv_id." and process_type=".$process_type." and month:".$get_mnt_qry_result_row['month']);
								}
							}
						}
					}
				}
				else
				{
					$conn->query("DELETE FROM `tbl_month_mood_graph_temp_data` WHERE `cv_id` =".$cv_id);
					$conn->query("DELETE FROM `tbl_month_genre_graph_temp_data` WHERE `cv_id` =".$cv_id);
				}
			}
			else
			{
				//echo "no process_type found for cv ".$value;
				error_log("[db_dump] : function [ins_updt_monthly_graph_data] : no process_type found for cv".$cv_id);
			}
		}
	}
}

?>
