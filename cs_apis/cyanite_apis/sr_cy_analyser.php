<?php

// log started
$file = "logs/sr_cyanite_analysis/cyanite_analysis_data_log_".date('Y-m-d').".log";
if(!is_file($file)){
    //Some simple example content.
    $contents = date('Y-m-d h:i:s');
    //Save our content to the file.
    file_put_contents("logs/sr_cyanite_analysis/cyanite_analysis_data_log_".date('Y-m-d').".log", $contents);
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
error_log("Started at");
error_log(date('Y/m/d h:i:s', time()));
error_log("==========================================================================================================================================");

include 'sonic/sonic_functions.php';
require_once('cyanite_php_clone.php');
set_time_limit(0);

$sonic_functions = new sonic_functions();
$cyanite_php_clone = new cyanite_php_clone();

$status = $cyanite_php_clone->fetch_analysis(1);
echo "status".$status;
// if($status == 1)
	// $cyanite_php_clone->extract_required_cyanite_data();

error_log("Ended at");
error_log(date('Y/m/d h:i:s', time()));
error_log("==========================================================================================================================================");

?>
