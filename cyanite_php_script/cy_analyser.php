<?php
include 'sonic/sonic_functions.php';
require_once('cyanite_php_clone.php');
set_time_limit(0);

// log started
$file = "logs/dynamic_analysis_log_".date('Y-m-d').".log";
if(!is_file($file)){
    //Some simple example content.
    $contents = date('Y-m-d h:i:s');
    //Save our content to the file.
    file_put_contents("logs/dynamic_analysis_log_".date('Y-m-d').".log", $contents);
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

//$url_token = $_GET["token"];
//echo "<br>url token : ".$url_token;

$sonic_functions = new sonic_functions();
$cyanite_php_clone = new cyanite_php_clone();

$validate_request = 1;//$sonic_functions->validate_url_token($url_token);

if($validate_request == 1){

	$current_date = date('Y-m-d h:i:s');
	$generate_process_token = $sonic_functions->generate_token($current_date);
	$validate_process_token = $sonic_functions->validate_token_process($generate_process_token, $current_date,"php");

	if($validate_process_token == "1"){

		//echo "<br>cron 2 started ..";

		error_log(date('Y/m/d h:i:s', time()));
		error_log("==============================================");

		$status = $cyanite_php_clone->fetch_analysis();
		
		$cyanite_php_clone->store_mood_genere();
		
		$cyanite_php_clone->aggregate_of_aggregate();

		$ct = date('H:i:s');
		if(($ct > '23:00:00' && $ct < '24:00:00') || ($ct > '06:00:00' && $ct < '07:00:00') || ($ct > '12:00:00' && $ct < '01:00:00')  || ($ct > '18:00:00' && $ct < '19:00:00'))
		{
			$cyanite_php_clone->generate_monthwise_data_graphs();
		}

	}

}
else{

	echo "access denied";
	//$sonic_functions->trigger_log_email("URL token","cron1 --","URL token mismatched.");

}



?>
