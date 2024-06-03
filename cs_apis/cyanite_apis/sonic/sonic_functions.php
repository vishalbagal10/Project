<?php

require_once('./cyanite_php_clone.php');
require_once('db_dump.php');

class sonic_functions
{

	function validate_url_token($token){
		$sonic_functions = new sonic_functions();
		$my_token = "034cdae376d3e54f4038820c4e64e53c";	//file_get_contents("url_token.txt");
		
		if($my_token == $token){			
			return 1;
		}
		else{
			//error_log("page : [sonic_functions] : function [validate_url_token] : error : ".$e->getMessage());
			//$sonic_functions->trigger_log_email("sonic_functions","validate_url_token",$e->getMessage());
			return 0;
		}
	}

	function validate_token_process($token, $cdate, $process_type){
		$sonic_functions = new sonic_functions();
		//echo '{"et":'.$token.',"cdate":'.$cdate.',"atype":'.$process_type.'}';
		try{
			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => 'https://sonicradar.sonic-os.com/cyanite_php_script/get_et_status.php',
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_SSL_VERIFYPEER => false,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'POST',
			  CURLOPT_POSTFIELDS =>'{
			    "et":"'.$token.'",
			    "cdate":"'.$cdate.'",
			    "atype":"'.$process_type.'"
			}',
			  CURLOPT_HTTPHEADER => array(
			    'Content-Type: application/json'
			  ),
			));

			$response = curl_exec($curl);

			curl_close($curl);		
			//echo "response : ".$response;
			return $response;
		}
		catch(Exception $e)
		{
			error_log("page : [sonic_functions] : function [validate_token_process] : error : ".$e->getMessage());
			$sonic_functions->trigger_log_email("sonic_functions","validate_token_process",$e->getMessage());
			return 0;
		}
	}

	function check_and_create_crate(){
		$sonic_functions = new sonic_functions();
		try{
			$crate_id = 0;
			$crate_name = null;
			$brand_id = 0;

			$dbcon = include($_SERVER['DOCUMENT_ROOT'] .'/apis/config.php');
			$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);	

			$sql = "select crate_id, crate_name, cv_id from tbl_social_spyder_graph_meta_data where status=0 AND is_active = 0 GROUP BY cv_id";

			$result = $conn->query($sql);

			if ($result->num_rows > 0) {  
			  while($row = $result->fetch_assoc()) {

			  		$sql_2 = "SELECT DISTINCT(crate_id),(select count(id) from tbl_social_spyder_graph_meta_data where cv_id=".$row["cv_id"]." and crate_id='') as pending FROM `tbl_social_spyder_graph_meta_data` a where cv_id=".$row["cv_id"]." and crate_id <> ''";

			  		$result2 = $conn->query($sql_2);

			  		if ($result2->num_rows > 0) {
				  		while($row2 = $result2->fetch_assoc()) {

				  			if($row2["pending"]!=0){

				  				//update crate id's on fresh records of same brand id
				  				$sql3 = "update tbl_social_spyder_graph_meta_data set crate_id=".$row2["crate_id"]." where cv_id=".$row["cv_id"]." and is_active= 0";
				  				$conn->query($sql3);
				  			}
				  		}
			  		}
			  		else{ // no records = we dont have crate id and any pending brands so need to create new 1

				  		$crate_id = $row["crate_id"];
				  		$crate_name = $row["crate_name"];
				  		$brand_id = $row["cv_id"];

				  		//if($row["crate_id"]==null || $row["crate_id"]==""){

				  			// create crate
				  			$cyanite_php_clone = new cyanite_php_clone();
				  			$crate_id = $cyanite_php_clone->create_crate($crate_name);
				  			
				  			// update crate on table
				  			$db_dump = new db_dump();
				  			$db_dump->update_crate_id_matadata_table($crate_id, $brand_id);

				  		//}
			  		}
			  }
			}
			else{
				error_log("page : [sonic_functions] : function [check_and_create_crate] : error : No CV data available for processing");	
			}
			return "1";
		}
		catch(Exception $e)
		{
			error_log("page : [sonic_functions] : function [check_and_create_crate] : error : ".$e->getMessage());
			$sonic_functions->trigger_log_email("sonic_functions","check_and_create_crate",$e->getMessage());
			return 0;
		}
	}

	function trigger_log_email($process_name, $function_name, $error_message){

		// $to = "jay.sedani@gophygital.io";
		// $subject = "Cyanite SonicCV Error mail";
		// $msg = "process_name : ".$process_name." <br>function name : ".$function_name." <br>error message : ".$error_message;

		// $headers = "From: jay.sedani@gophygital.io";
		// $headers .= "MIME-Version: 1.0" . "\r\n";
		// $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n".
		// "CC: amol.thorat@gophygital.io,chetan.ningoo@gophygital.io";

		// mail($to,$subject,$msg,$headers);
	}

	function generate_token($c_date){
		$sonic_functions = new sonic_functions();
		try{
			$dbcon = include($_SERVER['DOCUMENT_ROOT'] .'/apis/config.php');
			$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);

			$f_date = "";

			$sql = "SELECT * FROM `tbl_config` where type='future_date'";
			$result = $conn->query($sql);

			if ($result->num_rows > 0) {		  
			  while($row = $result->fetch_assoc()) {
				$f_date = str_replace(":","",str_replace(" ","",str_replace("-","",$row['value'])));	        
			  }
			} else {
			  	$f_date = "NA";
			}
			$conn->close();

			if($f_date != "NA"){
				$encp1 = str_replace(":","",str_replace(" ","",str_replace("-","",$c_date)));
		        $date_diff = $f_date-$encp1;
		        $date_diff_insec = $date_diff*60;
		        $server_enc_token = md5($date_diff_insec.'cyMp3UpL0der');

		        return $server_enc_token;
			}
			else{
				return "future date is not available.";
			}
		}
		catch(Exception $e)
		{
			error_log("page : [sonic_functions] : function [generate_token] : error : ".$e->getMessage());
			$sonic_functions->trigger_log_email("sonic_functions","generate_token",$e->getMessage());
		}
	}
	
}






?>
