<?php

//$conn = new mysqli('localhost', 'root', '', 'scgen');
$config = include('config.php');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

$response = '{"status":{"success":true,"status":200},"info":{"access":{"seconds_to_expire":2592000,"expires_at":"05\/07\/2022 05:12:26 EDT"},"credits":{"available":5612,"premium-credits":0}},"data":{"id":{"id":"UCEt7ekRGBVL0khAEeRa0t8g","username":"flex123x1xxx","cusername":"flexlive","display_name":"PayPal"},"general":{"created_at":"2013-02-16","channel_type":"people","geo":{"country_code":"RU","country":"Russian Federation"},"branding":{"avatar":"https:\/\/yt3.ggpht.com\/ytc\/AAUvwnhyo5Iz0PIAF_xY-j_jxLNUW6gQLvX3-TQpzXf01w=s88-c-k-c0x00ffffff-no-rj","banner":"","website":"https:\/\/www.youtube.com\/channel\/UCEt7ekRGBVL0khAEeRa0t8g","social":{"facebook":null,"twitter":"https:\/\/twitter.com\/F1ex1337","twitch":null,"instagram":"https:\/\/www.instagram.com\/flex1337\/","linkedin":null,"discord":null}}},"statistics":{"total":{"uploads":0,"subscribers":395000,"views":2269267},"growth":{"subs":{"1":0,"3":0,"7":0,"14":0,"30":0,"60":0,"90":0,"180":0,"365":0},"views":{"1":0,"3":0,"7":0,"14":0,"30":0,"60":0,"90":0,"180":0,"365":0}}},"misc":{"grade":{"color":"#dd9700","grade":"B-"},"sb_verified":true,"made_for_kids":false,"tags":[]},"ranks":{"sbrank":857510,"subscribers":2897,"views":1895879,"country":943,"channel_type":1360}}}';



$decodedData = json_decode($response, true);
$t = $decodedData['data'];
//$t = json_decode(json_encode($decodedData['data']['statistics'], true), true);
print_r($t);
if (array_key_exists("subs",$t))
{
  echo 'y';
}
else
{
  echo 'n';
}
$key_checker_arr = $decodedData['data'];

if(array_key_exists("statistics",$key_checker_arr) || array_key_exists("ranks",$key_checker_arr))
{
  $statistics_data_arr = json_decode(json_encode($decodedData['data']['statistics'], true), true);

  $data_arr = json_decode(json_encode($decodedData['data'], true), true);

  
    $yt_srg_data_ins_qry = "INSERT INTO `tbl_social_blade_yt_chnls_statistics_ranks_growth_data`(`mt_id`, `statistics_total_uploads`, `statistics_total_subscribers`, `statistics_total_views`, `ranks_sbrank`, `ranks_subscribers`, `ranks_views`, `ranks_country`, `ranks_channel_type`, `statistics_growth_subs_1`, `statistics_growth_subs_3`, `statistics_growth_subs_7`, `statistics_growth_subs_14`, `statistics_growth_subs_30`, `statistics_growth_subs_60`, `statistics_growth_subs_90`, `statistics_growth_subs_180`, `statistics_growth_subs_365`, `statistics_growth_views_1`, `statistics_growth_views_3`, `statistics_growth_views_7`, `statistics_growth_views_14`, `statistics_growth_views_30`, `statistics_growth_views_60`, `statistics_growth_views_90`, `statistics_growth_views_180`, `statistics_growth_views_365`) VALUES ";

      //   echo "mt_id:".$mt_id."<br>";
      //   echo "statistics_total_uploads:".$statistics_data_arr['total']['uploads']."<br>";
      //   echo "statistics_total_subscribers:".$statistics_data_arr['total']['subscribers']."<br>";
      //   echo "statistics_total_views:".$statistics_data_arr['total']['views']."<br>";

      $yt_srg_data_ins_qry .= "(".$mt_id.",";
      if(array_key_exists("total",$statistics_data_arr))
      {
        $yt_srg_data_ins_qry .= "'".$statistics_data_arr['total']['uploads']."',";
        $yt_srg_data_ins_qry .= "'".$statistics_data_arr['total']['subscribers']."',";
        $yt_srg_data_ins_qry .= "'".$statistics_data_arr['total']['views']."',";
      }
      else
      {
        $yt_srg_data_ins_qry .= ",";
        $yt_srg_data_ins_qry .= ",";
        $yt_srg_data_ins_qry .= ",";
      }

      //echo "ranks_sbrank:".$data_arr['ranks']['sbrank']."<br>";
      // echo "ranks_subscribers:".$data_arr['ranks']['subscribers']."<br>";
      // echo "ranks_views:".$data_arr['ranks']['views']."<br>";
      // echo "ranks_country:".$data_arr['ranks']['country']."<br>";
      // echo "ranks_channel_type:".$data_arr['ranks']['channel_type']."<br>***************************<br>";
      if(array_key_exists("ranks",$key_checker_arr))
      {
        $yt_srg_data_ins_qry .= "'".$data_arr['ranks']['sbrank']."',";
        $yt_srg_data_ins_qry .= "'".$data_arr['ranks']['subscribers']."',";
        $yt_srg_data_ins_qry .= "'".$data_arr['ranks']['views']."',";
        $yt_srg_data_ins_qry .= "'".$data_arr['ranks']['country']."',";
        $yt_srg_data_ins_qry .= "'".$data_arr['ranks']['channel_type']."',";
      }
      else
      {
        $yt_srg_data_ins_qry .= ",";
        $yt_srg_data_ins_qry .= ",";
        $yt_srg_data_ins_qry .= ",";
        $yt_srg_data_ins_qry .= ",";
        $yt_srg_data_ins_qry .= ",";
      }

      // echo "statistics_growth_subs_1:".$statistics_data_arr['growth']['subs']['1']."<br>";
      // echo "statistics_growth_subs_3:".$statistics_data_arr['growth']['subs']['3']."<br>";
      // echo "statistics_growth_subs_7:".$statistics_data_arr['growth']['subs']['7']."<br>";
      // echo "statistics_growth_subs_14:".$statistics_data_arr['growth']['subs']['14']."<br>";
      // echo "statistics_growth_subs_30:".$statistics_data_arr['growth']['subs']['30']."<br>";
      // echo "statistics_growth_subs_60:".$statistics_data_arr['growth']['subs']['60']."<br>";
      // echo "statistics_growth_subs_90:".$statistics_data_arr['growth']['subs']['90']."<br>";
      // echo "statistics_growth_subs_180:".$statistics_data_arr['growth']['subs']['180']."<br>";
      // echo "statistics_growth_subs_365:".$statistics_data_arr['growth']['subs']['365']."<br>";

      if(array_key_exists("growth",$statistics_data_arr))
      {
        $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['subs']['1']."',";
        $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['subs']['3']."',";
        $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['subs']['7']."',";
        $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['subs']['14']."',";
        $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['subs']['30']."',";
        $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['subs']['60']."',";
        $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['subs']['90']."',";
        $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['subs']['180']."',";
        $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['subs']['365']."',";
      }
      else
      {
        $yt_srg_data_ins_qry .= ",";
        $yt_srg_data_ins_qry .= ",";
        $yt_srg_data_ins_qry .= ",";
        $yt_srg_data_ins_qry .= ",";
        $yt_srg_data_ins_qry .= ",";
        $yt_srg_data_ins_qry .= ",";
        $yt_srg_data_ins_qry .= ",";
        $yt_srg_data_ins_qry .= ",";
        $yt_srg_data_ins_qry .= ",";
      }

      // echo "statistics_growth_views_1:".$statistics_data_arr['growth']['views']['1']."<br>";
      // echo "statistics_growth_views_3:".$statistics_data_arr['growth']['views']['3']."<br>";
      // echo "statistics_growth_views_7:".$statistics_data_arr['growth']['views']['7']."<br>";
      // echo "statistics_growth_views_14:".$statistics_data_arr['growth']['views']['14']."<br>";
      // echo "statistics_growth_views_30:".$statistics_data_arr['growth']['views']['30']."<br>";
      // echo "statistics_growth_views_60:".$statistics_data_arr['growth']['views']['60']."<br>";
      // echo "statistics_growth_views_90:".$statistics_data_arr['growth']['views']['90']."<br>";
      // echo "statistics_growth_views_180:".$statistics_data_arr['growth']['views']['180']."<br>";
      // echo "statistics_growth_views_365:".$statistics_data_arr['growth']['views']['365']."<br>";
      if(array_key_exists("growth",$statistics_data_arr))
      {
        $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['views']['1']."',";
        $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['views']['3']."',";
        $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['views']['7']."',";
        $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['views']['14']."',";
        $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['views']['30']."',";
        $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['views']['60']."',";
        $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['views']['90']."',";
        $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['views']['180']."',";
        $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['views']['365']."'),";
      }
      else
      {
        $yt_srg_data_ins_qry .= ",";
        $yt_srg_data_ins_qry .= ",";
        $yt_srg_data_ins_qry .= ",";
        $yt_srg_data_ins_qry .= ",";
        $yt_srg_data_ins_qry .= ",";
        $yt_srg_data_ins_qry .= ",";
        $yt_srg_data_ins_qry .= ",";
        $yt_srg_data_ins_qry .= ",";
        $yt_srg_data_ins_qry .= "),";
      }

      $final_yt_srg_data_ins_qry = rtrim($yt_srg_data_ins_qry,",");
      echo "<br>final_yt_srg_data_ins_qry:".$final_yt_srg_data_ins_qry."<br><br>";
      
  
}
exit;

// log started
$file = "sb_logs/social_blade_".date('Y-m-d').".log";
if(!is_file($file)){
    //Some simple example content.
    $contents = date('Y-m-d h:i:s');
    //Save our content to the file.
    file_put_contents("sb_logs/social_blade_".date('Y-m-d').".log", $contents);
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
error_log(date('Y/m/d h:i:s', time()));
error_log("Started==============================================");
$get_pending_data_qry = "SELECT * FROM `tbl_social_blade_master` WHERE `status` = 0 AND `is_active` = 0 ORDER BY created_at ASC";
$get_pending_data_qry_result = $conn->query($get_pending_data_qry);
if ($get_pending_data_qry_result->num_rows > 0)
{
  while($get_pending_data_qry_result_row = $get_pending_data_qry_result->fetch_assoc())
  {

    $client_id = $config['clientid'];
    $token = $config['token'];
    /*------------------------------------------------------------------------------------------------------------
    history: default - 1 credit, up to 30 days worth of data
    history: extended - 2 credits, up to 1 year worth of data (if not available -> auto downgrade)
    history: archive - 3 credits, up to 3 years worth of data (if not available -> auto downgrade)
    history: vault - 5 credits, up to 10 years worth of data (if not available -> auto downgrade) (Not for YouTube)
    ---------------------------------------------------------------------------------------------------------------*/
    $history = "archive"; 
    $query = $get_pending_data_qry_result_row['chn_name'];
    //$query = "socialblade";   
    /*------------------------------------------------------------------------------------------------------------
    The root user URL for the API is https://matrix.sbapis.com/b/{platform}/statistics, where {platform} is the name of the platform in lower case.
    ---------------------------------------------------------------------------------------------------------------*/
    //$platform = "youtube";
    //$platform = "instagram";
    //$platform = "tiktok";
    //$platform = "twitter";
    switch($get_pending_data_qry_result_row['social_media_id'])
    {
      case '1':
        $platform = "youtube";
        break;
      case '2':
        $platform = "instagram";
        break;
      case '3':
        $platform = "tiktok";
        break;
      case '4':
        $platform = "twitter";
        break;
    }

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://matrix.sbapis.com/b/".$platform."/statistics",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 60, // in seconds
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache",
        "Content-Type:application/json",
        "clientid:".$client_id,
        "history:".$history,
        "query:".$query,
        "token:".$token
      ),
    ));

    
    $err = curl_error($curl);

    curl_close($curl);
    $pattern = '/[^a-zA-Z0-9_ -]/s';
    $file_name = preg_replace($pattern,"",str_replace(" ","-",$get_pending_data_qry_result_row['cv_name']))."_".preg_replace($pattern,"",str_replace(" ","-",$get_pending_data_qry_result_row['chn_name']))."_".$platform."_".date('Y-m-d-h-i-s');

    if ($err) {
      echo "cURL Error #:" . $err;
      error_log("Something went wrong while fetching data of ".$get_pending_data_qry_result_row['chn_name']." from social blade platform");
    } else {
      
      //echo "response".$response;
      $decodedData = json_decode($response, true);
      if($decodedData['status']['success'] === false && $decodedData['status']['status'] == 404)
      {
        $updt_master_status_qry = "UPDATE `tbl_social_blade_master` SET `status`= 4 WHERE `id`=".$get_pending_data_qry_result_row['id'];
        if($conn->query($updt_master_status_qry))
        {
          error_log("Status updated successfully to 4(not found at social blade) for ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']." at id ".$get_pending_data_qry_result_row['id']." into tbl_social_blade_master");
        }
        else
        {
          error_log("Something went wrong while updating status to 4(not found at social blade) for ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']. "into tbl_social_blade_master");
        }       
      }
      else
      {
        if(file_put_contents("sb_json/".$file_name.".json", $response))
        {
          $data_fetched_from = $decodedData['data']['daily'][count($decodedData['data']['daily'])-1]['date'];
          $data_fetched_to = $decodedData['data']['daily'][0]['date'];
          $updt_master_qry = "UPDATE `tbl_social_blade_master` SET `json_name`='".$file_name."' ,`data_fetched_from`='".$data_fetched_from."' ,`data_fetched_to`='".$data_fetched_to."' ,`status`= 1 WHERE `id`=".$get_pending_data_qry_result_row['id'];
          //echo $updt_master_qry;
          if($conn->query($updt_master_qry))
          {
            error_log("Data updated successfully with status 1(in process) into tbl_social_blade_master of ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']." at id ".$get_pending_data_qry_result_row['id']);
            $mt_id = $get_pending_data_qry_result_row['id'];
            $daily_data_arr = $decodedData['data']['daily'];
            $data_arr = json_decode(json_encode($decodedData['data'], true), true);
            $statistics_data_arr = json_decode(json_encode($decodedData['data']['statistics'], true), true);
            
            if($get_pending_data_qry_result_row['social_media_id'] == 1)
            {
              $yt_ins_qry = "INSERT INTO `tbl_social_blade_yt_chnls_daily_data`(`mt_id`, `date`, `subs`, `views`) VALUES ";
              foreach($daily_data_arr as $daily_data)
              {
                // echo "mt_id:".$mt_id."<br>";
                // echo "date:".$daily_data['date']."<br>";
                // echo "subs:".$daily_data['subs']."<br>";
                // echo "views:".$daily_data['views']."<br>***************************<br>";
                $yt_ins_qry .= "(".$mt_id.",";
                $yt_ins_qry .= "'".$daily_data['date']."',";
                $yt_ins_qry .= "'".$daily_data['subs']."',";
                $yt_ins_qry .= "'".$daily_data['views']."'),";
              }
              $final_yt_ins_qry = rtrim($yt_ins_qry,",");
              //echo "<br>final_yt_ins_qry:".$final_yt_ins_qry."<br><br>";
              if($conn->query($final_yt_ins_qry))
              {
                error_log("Data inserted successfully into tbl_social_blade_yt_chnls_daily_data of ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']);
                $updt_master_status_qry = "UPDATE `tbl_social_blade_master` SET `status`= 2 WHERE `id`=".$get_pending_data_qry_result_row['id'];
                if($conn->query($updt_master_status_qry))
                {
                  error_log("Status updated successfully to 2(in process) for ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']." at id ".$get_pending_data_qry_result_row['id']." into tbl_social_blade_master");
                  $yt_srg_data_ins_qry = "INSERT INTO `tbl_social_blade_yt_chnls_statistics_ranks_growth_data`(`mt_id`, `statistics_total_uploads`, `statistics_total_subscribers`, `statistics_total_views`, `ranks_sbrank`, `ranks_subscribers`, `ranks_views`, `ranks_country`, `ranks_channel_type`, `statistics_growth_subs_1`, `statistics_growth_subs_3`, `statistics_growth_subs_7`, `statistics_growth_subs_14`, `statistics_growth_subs_30`, `statistics_growth_subs_60`, `statistics_growth_subs_90`, `statistics_growth_subs_180`, `statistics_growth_subs_365`, `statistics_growth_views_1`, `statistics_growth_views_3`, `statistics_growth_views_7`, `statistics_growth_views_14`, `statistics_growth_views_30`, `statistics_growth_views_60`, `statistics_growth_views_90`, `statistics_growth_views_180`, `statistics_growth_views_365`) VALUES ";
                  // foreach($statistics_data_arr as $statistics_data)
                  // {
                  //   echo "mt_id:".$mt_id."<br>";
                  //   echo "statistics_total_uploads:".$statistics_data['uploads']."<br>";
                  //   echo "statistics_total_subscribers:".$statistics_data['subscribers']."<br>";
                  //   echo "statistics_total_views:".$statistics_data['views']."<br>";

                  //   $yt_srg_data_ins_qry .= "(".$mt_id.",";
                  //   $yt_srg_data_ins_qry .= "'".$statistics_data['uploads']."',";
                  //   $yt_srg_data_ins_qry .= "'".$statistics_data['subscribers']."',";
                  //   $yt_srg_data_ins_qry .= "'".$statistics_data['views']."',";
                  // }

                  //   echo "mt_id:".$mt_id."<br>";
                  //   echo "statistics_total_uploads:".$statistics_data_arr['total']['uploads']."<br>";
                  //   echo "statistics_total_subscribers:".$statistics_data_arr['total']['subscribers']."<br>";
                  //   echo "statistics_total_views:".$statistics_data_arr['total']['views']."<br>";

                  $yt_srg_data_ins_qry .= "(".$mt_id.",";
                  $yt_srg_data_ins_qry .= "'".$statistics_data_arr['total']['uploads']."',";
                  $yt_srg_data_ins_qry .= "'".$statistics_data_arr['total']['subscribers']."',";
                  $yt_srg_data_ins_qry .= "'".$statistics_data_arr['total']['views']."',";

                  // foreach($ranks_data_arr as $ranks_data)
                  // {
                  //   // echo "ranks_sbrank:".$ranks_data['sbrank']."<br>";
                  //   // echo "ranks_subscribers:".$ranks_data['subscribers']."<br>";
                  //   // echo "ranks_views:".$ranks_data['views']."<br>";
                  //   // echo "ranks_country:".$ranks_data['country']."<br>";
                  //   // echo "ranks_channel_type:".$ranks_data['channel_type']."<br>***************************<br>";

                  //   $yt_srg_data_ins_qry .= "'".$ranks_data['sbrank']."',";
                  //   $yt_srg_data_ins_qry .= "'".$ranks_data['subscribers']."',";
                  //   $yt_srg_data_ins_qry .= "'".$ranks_data['views']."',";
                  //   $yt_srg_data_ins_qry .= "'".$ranks_data['country']."',";
                  //   $yt_srg_data_ins_qry .= "'".$ranks_data['channel_type']."'),";

                  // }

                  //echo "ranks_sbrank:".$data_arr['ranks']['sbrank']."<br>";
                  // echo "ranks_subscribers:".$data_arr['ranks']['subscribers']."<br>";
                  // echo "ranks_views:".$data_arr['ranks']['views']."<br>";
                  // echo "ranks_country:".$data_arr['ranks']['country']."<br>";
                  // echo "ranks_channel_type:".$data_arr['ranks']['channel_type']."<br>***************************<br>";

                  $yt_srg_data_ins_qry .= "'".$data_arr['ranks']['sbrank']."',";
                  $yt_srg_data_ins_qry .= "'".$data_arr['ranks']['subscribers']."',";
                  $yt_srg_data_ins_qry .= "'".$data_arr['ranks']['views']."',";
                  $yt_srg_data_ins_qry .= "'".$data_arr['ranks']['country']."',";
                  $yt_srg_data_ins_qry .= "'".$data_arr['ranks']['channel_type']."',";

                  // foreach($statistics_growth_data_arr as $statistics_growth_data)
                  // {
                  //   echo "statistics_growth_subs_1:".$statistics_growth_data['1']."<br>";
                  //   echo "statistics_growth_subs_3:".$statistics_growth_data['3']."<br>";
                  //   echo "statistics_growth_subs_7:".$statistics_growth_data['7']."<br>";
                  //   echo "statistics_growth_subs_14:".$statistics_growth_data['14']."<br>";
                  //   echo "statistics_growth_subs_30:".$statistics_growth_data['30']."<br>";
                  //   echo "statistics_growth_subs_60:".$statistics_growth_data['60']."<br>";
                  //   echo "statistics_growth_subs_90:".$statistics_growth_data['90']."<br>";
                  //   echo "statistics_growth_subs_180:".$statistics_growth_data['180']."<br>";
                  //   echo "statistics_growth_subs_365:".$statistics_growth_data['365']."<br>";

                  //   $yt_srg_data_ins_qry .= "'".$statistics_growth_data['1']."',";
                  //   $yt_srg_data_ins_qry .= "'".$statistics_growth_data['3']."',";
                  //   $yt_srg_data_ins_qry .= "'".$statistics_growth_data['7']."',";
                  //   $yt_srg_data_ins_qry .= "'".$statistics_growth_data['14']."',";
                  //   $yt_srg_data_ins_qry .= "'".$statistics_growth_data['30']."',";
                  //   $yt_srg_data_ins_qry .= "'".$statistics_growth_data['60']."',";
                  //   $yt_srg_data_ins_qry .= "'".$statistics_growth_data['90']."',";
                  //   $yt_srg_data_ins_qry .= "'".$statistics_growth_data['180']."',";
                  //   $yt_srg_data_ins_qry .= "'".$statistics_growth_data['365']."',";

                  //   // echo "statistics_growth_views_1:".$statistics_growth_data['1']."<br>";
                  //   // echo "statistics_growth_views_3:".$statistics_growth_data['3']."<br>";
                  //   // echo "statistics_growth_views_7:".$statistics_growth_data['7']."<br>";
                  //   // echo "statistics_growth_views_14:".$statistics_growth_data['14']."<br>";
                  //   // echo "statistics_growth_views_30:".$statistics_growth_data['30']."<br>";
                  //   // echo "statistics_growth_views_60:".$statistics_growth_data['60']."<br>";
                  //   // echo "statistics_growth_views_90:".$statistics_growth_data['90']."<br>";
                  //   // echo "statistics_growth_views_180:".$statistics_growth_data['180']."<br>";
                  //   // echo "statistics_growth_views_365:".$statistics_growth_data['365']."<br>";

                  //   $yt_srg_data_ins_qry .= "'".$statistics_growth_data['1']."',";
                  //   $yt_srg_data_ins_qry .= "'".$statistics_growth_data['3']."',";
                  //   $yt_srg_data_ins_qry .= "'".$statistics_growth_data['7']."',";
                  //   $yt_srg_data_ins_qry .= "'".$statistics_growth_data['14']."',";
                  //   $yt_srg_data_ins_qry .= "'".$statistics_growth_data['30']."',";
                  //   $yt_srg_data_ins_qry .= "'".$statistics_growth_data['60']."',";
                  //   $yt_srg_data_ins_qry .= "'".$statistics_growth_data['90']."',";
                  //   $yt_srg_data_ins_qry .= "'".$statistics_growth_data['180']."',";
                  //   $yt_srg_data_ins_qry .= "'".$statistics_growth_data['365']."',";
                  // }

                  // echo "statistics_growth_subs_1:".$statistics_data_arr['growth']['subs']['1']."<br>";
                  // echo "statistics_growth_subs_3:".$statistics_data_arr['growth']['subs']['3']."<br>";
                  // echo "statistics_growth_subs_7:".$statistics_data_arr['growth']['subs']['7']."<br>";
                  // echo "statistics_growth_subs_14:".$statistics_data_arr['growth']['subs']['14']."<br>";
                  // echo "statistics_growth_subs_30:".$statistics_data_arr['growth']['subs']['30']."<br>";
                  // echo "statistics_growth_subs_60:".$statistics_data_arr['growth']['subs']['60']."<br>";
                  // echo "statistics_growth_subs_90:".$statistics_data_arr['growth']['subs']['90']."<br>";
                  // echo "statistics_growth_subs_180:".$statistics_data_arr['growth']['subs']['180']."<br>";
                  // echo "statistics_growth_subs_365:".$statistics_data_arr['growth']['subs']['365']."<br>";

                  $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['subs']['1']."',";
                  $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['subs']['3']."',";
                  $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['subs']['7']."',";
                  $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['subs']['14']."',";
                  $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['subs']['30']."',";
                  $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['subs']['60']."',";
                  $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['subs']['90']."',";
                  $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['subs']['180']."',";
                  $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['subs']['365']."',";

                  // echo "statistics_growth_views_1:".$statistics_data_arr['growth']['views']['1']."<br>";
                  // echo "statistics_growth_views_3:".$statistics_data_arr['growth']['views']['3']."<br>";
                  // echo "statistics_growth_views_7:".$statistics_data_arr['growth']['views']['7']."<br>";
                  // echo "statistics_growth_views_14:".$statistics_data_arr['growth']['views']['14']."<br>";
                  // echo "statistics_growth_views_30:".$statistics_data_arr['growth']['views']['30']."<br>";
                  // echo "statistics_growth_views_60:".$statistics_data_arr['growth']['views']['60']."<br>";
                  // echo "statistics_growth_views_90:".$statistics_data_arr['growth']['views']['90']."<br>";
                  // echo "statistics_growth_views_180:".$statistics_data_arr['growth']['views']['180']."<br>";
                  // echo "statistics_growth_views_365:".$statistics_data_arr['growth']['views']['365']."<br>";

                  $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['views']['1']."',";
                  $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['views']['3']."',";
                  $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['views']['7']."',";
                  $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['views']['14']."',";
                  $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['views']['30']."',";
                  $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['views']['60']."',";
                  $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['views']['90']."',";
                  $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['views']['180']."',";
                  $yt_srg_data_ins_qry .= "'".$statistics_data_arr['growth']['views']['365']."'),";

                  $final_yt_srg_data_ins_qry = rtrim($yt_srg_data_ins_qry,",");
                  //echo "<br>final_yt_srg_data_ins_qry:".$final_yt_srg_data_ins_qry."<br><br>";
                  if($conn->query($final_yt_srg_data_ins_qry))
                  {
                    error_log("Data inserted successfully into tbl_social_blade_yt_chnls_statistics_ranks_growth_data of ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']);
                    $updt_master_status_qry = "UPDATE `tbl_social_blade_master` SET `status`= 3 WHERE `id`=".$get_pending_data_qry_result_row['id'];
                    if($conn->query($updt_master_status_qry))
                    {
                      error_log("Status updated successfully to 3(completed) for ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']." at id ".$get_pending_data_qry_result_row['id']." into tbl_social_blade_master");
                    }
                    else
                    {
                      error_log("Something went wrong while updating status of ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']. "into tbl_social_blade_master");
                    }
                  }
                  else
                  {
                    error_log("Something went wrong while inserting data to 3(completed) for ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']. "into tbl_social_blade_yt_chnls_statistics_ranks_growth_data");
                  }
                }
                else
                {
                  error_log("Something went wrong while updating status to 2(in process) for ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']. "into tbl_social_blade_master");
                }
              }
              else
              {
                error_log("Something went wrong while inserting data of ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']. "into tbl_social_blade_yt_chnls_daily_data");
              }
            }

            if($get_pending_data_qry_result_row['social_media_id'] == 2)
            {
              $ig_ins_qry = "INSERT INTO `tbl_social_blade_ig_chnls_daily_data`(`mt_id`, `date`, `followers`, `following`, `media`, `avg_likes`, `avg_comments`) VALUES ";
              foreach($daily_data_arr as $daily_data)
              {
                // echo "mt_id:".$mt_id."<br>";
                // echo "date:".$daily_data['date']."<br>";
                // echo "followers:".$daily_data['followers']."<br>";
                // echo "following:".$daily_data['following']."<br>";
                // echo "media:".$daily_data['media']."<br>";
                // echo "avg_likes:".$daily_data['avg_likes']."<br>";
                // echo "avg_comments:".$daily_data['avg_comments']."<br>***************************<br>";
                $ig_ins_qry .= "(".$mt_id.",";
                $ig_ins_qry .= "'".$daily_data['date']."',";
                $ig_ins_qry .= "'".$daily_data['followers']."',";
                $ig_ins_qry .= "'".$daily_data['following']."',";
                $ig_ins_qry .= "'".$daily_data['media']."',";
                $ig_ins_qry .= "'".$daily_data['avg_likes']."',";
                $ig_ins_qry .= "'".$daily_data['avg_comments']."'),";
              }
              $final_ig_ins_qry = rtrim($ig_ins_qry,",");
              //echo "<br>final_ig_ins_qry:".$final_ig_ins_qry."<br><br>";
              if($conn->query($final_ig_ins_qry))
              {
                error_log("Data inserted successfully into tbl_social_blade_ig_chnls_daily_data of ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']);
                $updt_master_status_qry = "UPDATE `tbl_social_blade_master` SET `status`= 2 WHERE `id`=".$get_pending_data_qry_result_row['id'];
                if($conn->query($updt_master_status_qry))
                {
                  error_log("Status updated successfully to 2(in process) for ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']." at id ".$get_pending_data_qry_result_row['id']." into tbl_social_blade_master");
                  $ig_srg_data_ins_qry = "INSERT INTO `tbl_social_blade_ig_chnls_statistics_ranks_growth_data`(`mt_id`, `statistics_total_media`, `statistics_total_followers`, `statistics_total_following`, `statistics_total_engagement_rate`, `ranks_sbrank`, `ranks_followers`, `ranks_following`, `ranks_media`, `ranks_engagement_rate`) VALUES ";
                  // foreach($statistics_data_arr as $statistics_data)
                  // {
                  //   // echo "mt_id:".$mt_id."<br>";
                  //   // echo "statistics_total_media:".$statistics_data['media']."<br>";
                  //   // echo "statistics_total_followers:".$statistics_data['followers']."<br>";
                  //   // echo "statistics_total_following:".$statistics_data['following']."<br>";
                  //   // echo "statistics_total_engagement_rate:".$statistics_data['engagement_rate']."<br>";

                  //   $ig_srg_data_ins_qry .= "(".$mt_id.",";
                  //   $ig_srg_data_ins_qry .= "'".$statistics_data['media']."',";
                  //   $ig_srg_data_ins_qry .= "'".$statistics_data['followers']."',";
                  //   $ig_srg_data_ins_qry .= "'".$statistics_data['following']."',";
                  //   $ig_srg_data_ins_qry .= "'".$statistics_data['engagement_rate']."',";
                    
                  // }

                  //   // echo "mt_id:".$mt_id."<br>";
                  //   // echo "statistics_total_media:".$statistics_data_arr['total']['media']."<br>";
                  //   // echo "statistics_total_followers:".$statistics_data_arr['total']['followers']."<br>";
                  //   // echo "statistics_total_following:".$statistics_data_arr['total']['following']."<br>";
                  //   // echo "statistics_total_engagement_rate:".$statistics_data_arr['total']['engagement_rate']."<br>";

                  $ig_srg_data_ins_qry .= "(".$mt_id.",";
                  $ig_srg_data_ins_qry .= "'".$statistics_data_arr['total']['media']."',";
                  $ig_srg_data_ins_qry .= "'".$statistics_data_arr['total']['followers']."',";
                  $ig_srg_data_ins_qry .= "'".$statistics_data_arr['total']['following']."',";
                  $ig_srg_data_ins_qry .= "'".$statistics_data_arr['total']['engagement_rate']."',";

                  // foreach($ranks_data_arr as $ranks_data)
                  // {
                  //   // echo "ranks_sbrank:".$ranks_data['sbrank']."<br>";
                  //   // echo "ranks_followers:".$ranks_data['followers']."<br>";
                  //   // echo "ranks_following:".$ranks_data['following']."<br>";
                  //   // echo "ranks_media:".$ranks_data['media']."<br>";
                  //   // echo "ranks_engagement_rate:".$ranks_data['engagement_rate']."<br>***************************<br>";

                  //   $ig_srg_data_ins_qry .= "'".$ranks_data['sbrank']."',";
                  //   $ig_srg_data_ins_qry .= "'".$ranks_data['followers']."',";
                  //   $ig_srg_data_ins_qry .= "'".$ranks_data['following']."',";
                  //   $ig_srg_data_ins_qry .= "'".$ranks_data['media']."',";
                  //   $ig_srg_data_ins_qry .= "'".$ranks_data['engagement_rate']."'),";
                    
                  // }

                  // echo "ranks_sbrank:".$data_arr['ranks']['sbrank']."<br>";
                  // echo "ranks_followers:".$data_arr['ranks']['followers']."<br>";
                  // echo "ranks_following:".$data_arr['ranks']['following']."<br>";
                  // echo "ranks_media:".$data_arr['ranks']['media']."<br>";
                  // echo "ranks_engagement_rate:".$data_arr['ranks']['engagement_rate']."<br>***************************<br>";

                  $ig_srg_data_ins_qry .= "'".$data_arr['ranks']['sbrank']."',";
                  $ig_srg_data_ins_qry .= "'".$data_arr['ranks']['followers']."',";
                  $ig_srg_data_ins_qry .= "'".$data_arr['ranks']['following']."',";
                  $ig_srg_data_ins_qry .= "'".$data_arr['ranks']['media']."',";
                  $ig_srg_data_ins_qry .= "'".$data_arr['ranks']['engagement_rate']."'),";

                  

                  $final_ig_srg_data_ins_qry = rtrim($ig_srg_data_ins_qry,",");
                  //echo "<br>final_ig_srg_data_ins_qry:".$final_ig_srg_data_ins_qry."<br><br>";
                  if($conn->query($final_ig_srg_data_ins_qry))
                  {
                    error_log("Data inserted successfully into tbl_social_blade_ig_chnls_statistics_ranks_growth_data of ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']);
                    $updt_master_status_qry = "UPDATE `tbl_social_blade_master` SET `status`= 3 WHERE `id`=".$get_pending_data_qry_result_row['id'];
                    if($conn->query($updt_master_status_qry))
                    {
                      error_log("Status updated successfully to 3(completed) for ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']." at id ".$get_pending_data_qry_result_row['id']." into tbl_social_blade_master");
                    }
                    else
                    {
                      error_log("Something went wrong while updating status to 3(completed) for ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']. "into tbl_social_blade_master");
                    }
                  }
                  else
                  {
                    error_log("Something went wrong while inserting data of ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']. "into tbl_social_blade_ig_chnls_statistics_ranks_growth_data");
                  }
                }
                else
                {
                  error_log("Something went wrong while updating status to 2(in process) for ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']. "into tbl_social_blade_master");
                }
              }
              else
              {
                error_log("Something went wrong while inserting data of ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']. "into tbl_social_blade_ig_chnls_daily_data");
              }
            }

            if($get_pending_data_qry_result_row['social_media_id'] == 3)
            {
              $tt_ins_qry = "INSERT INTO `tbl_social_blade_tt_chnls_daily_data`(`mt_id`, `date`, `followers`, `following`, `likes`, `uploads`) VALUES ";
              foreach($daily_data_arr as $daily_data)
              {
                // echo "mt_id:".$mt_id."<br>";
                // echo "date:".$daily_data['date']."<br>";
                // echo "followers:".$daily_data['followers']."<br>";
                // echo "following:".$daily_data['following']."<br>";
                // echo "likes:".$daily_data['likes']."<br>";
                // echo "uploads:".$daily_data['uploads']."<br>***************************<br>";
                $tt_ins_qry .= "(".$mt_id.",";
                $tt_ins_qry .= "'".$daily_data['date']."',";
                $tt_ins_qry .= "'".$daily_data['followers']."',";
                $tt_ins_qry .= "'".$daily_data['following']."',";
                $tt_ins_qry .= "'".$daily_data['likes']."',";
                $tt_ins_qry .= "'".$daily_data['uploads']."'),";
              }
              $final_tt_ins_qry = rtrim($tt_ins_qry,",");
              //echo "<br>final_tt_ins_qry:".$final_tt_ins_qry."<br><br>";
              if($conn->query($final_tt_ins_qry))
              {
                error_log("Data inserted successfully into tbl_social_blade_tt_chnls_daily_data of ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']);
                $updt_master_status_qry = "UPDATE `tbl_social_blade_master` SET `status`= 2 WHERE `id`=".$get_pending_data_qry_result_row['id'];
                if($conn->query($updt_master_status_qry))
                {
                  error_log("Status updated successfully to 2(in process) for ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']." at id ".$get_pending_data_qry_result_row['id']." into tbl_social_blade_master");
                  $tt_srg_data_ins_qry = "INSERT INTO `tbl_social_blade_tt_chnls_statistics_ranks_growth_data`(`mt_id`, `statistics_total_followers`, `statistics_total_following`, `statistics_total_uploads`, `statistics_total_likes`, `ranks_sbrank`, `ranks_followers`, `ranks_following`, `ranks_uploads`, `ranks_likes`) VALUES ";
                  // foreach($statistics_data_arr as $statistics_data)
                  // {
                  //   // echo "mt_id:".$mt_id."<br>";
                  //   // echo "statistics_total_followers:".$statistics_data['followers']."<br>";
                  //   // echo "statistics_total_following:".$statistics_data['following']."<br>";
                  //   // echo "statistics_total_uploads:".$statistics_data['uploads']."<br>";
                  //   // echo "statistics_total_likes:".$statistics_data['likes']."<br>";

                  //   $tt_srg_data_ins_qry .= "(".$mt_id.",";
                  //   $tt_srg_data_ins_qry .= "'".$statistics_data['followers']."',";
                  //   $tt_srg_data_ins_qry .= "'".$statistics_data['following']."',";
                  //   $tt_srg_data_ins_qry .= "'".$statistics_data['uploads']."',";
                  //   $tt_srg_data_ins_qry .= "'".$statistics_data['likes']."',";
                    
                  // }

                  // echo "mt_id:".$mt_id."<br>";
                  // echo "statistics_total_followers:".$statistics_data_arr['total']['followers']."<br>";
                  // echo "statistics_total_following:".$statistics_data_arr['total']['following']."<br>";
                  // echo "statistics_total_uploads:".$statistics_data_arr['total']['uploads']."<br>";
                  // echo "statistics_total_likes:".$statistics_data_arr['total']['likes']."<br>";

                  $tt_srg_data_ins_qry .= "(".$mt_id.",";
                  $tt_srg_data_ins_qry .= "'".$statistics_data_arr['total']['followers']."',";
                  $tt_srg_data_ins_qry .= "'".$statistics_data_arr['total']['following']."',";
                  $tt_srg_data_ins_qry .= "'".$statistics_data_arr['total']['uploads']."',";
                  $tt_srg_data_ins_qry .= "'".$statistics_data_arr['total']['likes']."',";

                  // foreach($ranks_data_arr as $ranks_data)
                  // {
                  //   // echo "ranks_sbrank:".$ranks_data['sbrank']."<br>";
                  //   // echo "ranks_followers:".$ranks_data['followers']."<br>";
                  //   // echo "ranks_following:".$ranks_data['following']."<br>";
                  //   // echo "ranks_uploads:".$ranks_data['uploads']."<br>";
                  //   // echo "ranks_likes:".$ranks_data['likes']."<br>***************************<br>";

                  //   $tt_srg_data_ins_qry .= "'".$ranks_data['sbrank']."',";
                  //   $tt_srg_data_ins_qry .= "'".$ranks_data['followers']."',";
                  //   $tt_srg_data_ins_qry .= "'".$ranks_data['following']."',";
                  //   $tt_srg_data_ins_qry .= "'".$ranks_data['uploads']."',";
                  //   $tt_srg_data_ins_qry .= "'".$ranks_data['likes']."'),";
                    
                  // }

                  // echo "ranks_sbrank:".$data_arr['ranks']['sbrank']."<br>";
                  // echo "ranks_followers:".$data_arr['ranks']['followers']."<br>";
                  // echo "ranks_following:".$data_arr['ranks']['following']."<br>";
                  // echo "ranks_uploads:".$data_arr['ranks']['uploads']."<br>";
                  // echo "ranks_likes:".$data_arr['ranks']['likes']."<br>***************************<br>";

                  $tt_srg_data_ins_qry .= "'".$data_arr['ranks']['sbrank']."',";
                  $tt_srg_data_ins_qry .= "'".$data_arr['ranks']['followers']."',";
                  $tt_srg_data_ins_qry .= "'".$data_arr['ranks']['following']."',";
                  $tt_srg_data_ins_qry .= "'".$data_arr['ranks']['uploads']."',";
                  $tt_srg_data_ins_qry .= "'".$data_arr['ranks']['likes']."'),";

                  $final_tt_srg_data_ins_qry = rtrim($tt_srg_data_ins_qry,",");
                  //echo "<br>final_tt_srg_data_ins_qry:".$final_tt_srg_data_ins_qry."<br><br>";
                  if($conn->query($final_tt_srg_data_ins_qry))
                  {
                    error_log("Data inserted successfully into tbl_social_blade_tt_chnls_statistics_ranks_growth_data of ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']);
                    $updt_master_status_qry = "UPDATE `tbl_social_blade_master` SET `status`= 3 WHERE `id`=".$get_pending_data_qry_result_row['id'];
                    if($conn->query($updt_master_status_qry))
                    {
                      error_log("Status updated successfully to 3(completed) for ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']." at id ".$get_pending_data_qry_result_row['id']." into tbl_social_blade_master");
                    }
                    else
                    {
                      error_log("Something went wrong while updating status to 3(completed) for ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']. "into tbl_social_blade_master");
                    }
                  }
                  else
                  {
                    error_log("Something went wrong while inserting data of ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']. "into tbl_social_blade_tt_chnls_statistics_ranks_growth_data");
                  }
                }
                else
                {
                  error_log("Something went wrong while updating status to 2(in process) for ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']. "into tbl_social_blade_master");
                }
              }
              else
              {
                error_log("Something went wrong while inserting data of ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']. "into tbl_social_blade_tt_chnls_daily_data");
              }
            }

            if($get_pending_data_qry_result_row['social_media_id'] == 4)
            {
              $twt_ins_qry = "INSERT INTO `tbl_social_blade_twt_chnls_daily_data`(`mt_id`, `date`, `followers`, `following`, `tweets`, `favorites`) VALUES ";
              foreach($daily_data_arr as $daily_data)
              {
                // echo "mt_id:".$mt_id."<br>";
                // echo "date:".$daily_data['date']."<br>";
                // echo "followers:".$daily_data['followers']."<br>";
                // echo "following:".$daily_data['following']."<br>";
                // echo "tweets:".$daily_data['tweets']."<br>";
                // echo "favorites:".$daily_data['favorites']."<br>***************************<br>";
                $twt_ins_qry .= "(".$mt_id.",";
                $twt_ins_qry .= "'".$daily_data['date']."',";
                $twt_ins_qry .= "'".$daily_data['followers']."',";
                $twt_ins_qry .= "'".$daily_data['following']."',";
                $twt_ins_qry .= "'".$daily_data['tweets']."',";
                $twt_ins_qry .= "'".$daily_data['favorites']."'),";
              }
              $final_twt_ins_qry = rtrim($twt_ins_qry,",");
              //echo "<br>final_twt_ins_qry:".$final_twt_ins_qry."<br><br>";
              if($conn->query($final_twt_ins_qry))
              {
                error_log("Data inserted successfully into tbl_social_blade_twt_chnls_daily_data of ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']);
                $updt_master_status_qry = "UPDATE `tbl_social_blade_master` SET `status`= 2 WHERE `id`=".$get_pending_data_qry_result_row['id'];
                if($conn->query($updt_master_status_qry))
                {
                  error_log("Status updated successfully to 2(in process) for ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']." at id ".$get_pending_data_qry_result_row['id']." into tbl_social_blade_master");
                  $twt_srg_data_ins_qry = "INSERT INTO `tbl_social_blade_twt_chnls_statistics_ranks_growth_data`(`mt_id`, `statistics_total_followers`, `statistics_total_following`, `statistics_total_tweets`, `ranks_sbrank`, `ranks_followers`, `ranks_following`, `ranks_tweets`) VALUES ";
                  // foreach($statistics_data_arr as $statistics_data)
                  // {
                  //   // echo "mt_id:".$mt_id."<br>";
                  //   // echo "statistics_total_followers:".$statistics_data['followers']."<br>";
                  //   // echo "statistics_total_following:".$statistics_data['following']."<br>";
                  //   // echo "statistics_total_tweets:".$statistics_data['tweets']."<br>";

                  //   $twt_srg_data_ins_qry .= "(".$mt_id.",";
                  //   $twt_srg_data_ins_qry .= "'".$statistics_data['followers']."',";
                  //   $twt_srg_data_ins_qry .= "'".$statistics_data['following']."',";
                  //   $twt_srg_data_ins_qry .= "'".$statistics_data['tweets']."',";                
                  // }

                  // echo "mt_id:".$mt_id."<br>";
                  // echo "statistics_total_followers:".$statistics_data_arr['total']['followers']."<br>";
                  // echo "statistics_total_following:".$statistics_data_arr['total']['following']."<br>";
                  // echo "statistics_total_tweets:".$statistics_data_arr['total']['tweets']."<br>";

                  $twt_srg_data_ins_qry .= "(".$mt_id.",";
                  $twt_srg_data_ins_qry .= "'".$statistics_data_arr['total']['followers']."',";
                  $twt_srg_data_ins_qry .= "'".$statistics_data_arr['total']['following']."',";
                  $twt_srg_data_ins_qry .= "'".$statistics_data_arr['total']['tweets']."',";   

                  // foreach($ranks_data_arr as $ranks_data)
                  // {
                  //   // echo "ranks_sbrank:".$ranks_data['sbrank']."<br>";
                  //   // echo "ranks_followers:".$ranks_data['followers']."<br>";
                  //   // echo "ranks_following:".$ranks_data['following']."<br>";
                  //   // echo "ranks_tweets:".$ranks_data['tweets']."<br>***************************<br>";

                  //   $twt_srg_data_ins_qry .= "'".$ranks_data['sbrank']."',";
                  //   $twt_srg_data_ins_qry .= "'".$ranks_data['followers']."',";
                  //   $twt_srg_data_ins_qry .= "'".$ranks_data['following']."',";
                  //   $twt_srg_data_ins_qry .= "'".$ranks_data['tweets']."'),";
                    
                  // }

                  // echo "ranks_sbrank:".$data_arr['ranks']['sbrank']."<br>";
                  // echo "ranks_followers:".$data_arr['ranks']['followers']."<br>";
                  // echo "ranks_following:".$data_arr['ranks']['following']."<br>";
                  // echo "ranks_tweets:".$data_arr['ranks']['tweets']."<br>***************************<br>";

                  $twt_srg_data_ins_qry .= "'".$data_arr['ranks']['sbrank']."',";
                  $twt_srg_data_ins_qry .= "'".$data_arr['ranks']['followers']."',";
                  $twt_srg_data_ins_qry .= "'".$data_arr['ranks']['following']."',";
                  $twt_srg_data_ins_qry .= "'".$data_arr['ranks']['tweets']."'),";

                  $final_twt_srg_data_ins_qry = rtrim($twt_srg_data_ins_qry,",");
                  //echo "<br>final_twt_srg_data_ins_qry:".$final_twt_srg_data_ins_qry."<br><br>";
                  if($conn->query($final_twt_srg_data_ins_qry))
                  {
                    error_log("Data inserted successfully into tbl_social_blade_twt_chnls_statistics_ranks_growth_data of ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']);
                    $updt_master_status_qry = "UPDATE `tbl_social_blade_master` SET `status`= 3 WHERE `id`=".$get_pending_data_qry_result_row['id'];
                    if($conn->query($updt_master_status_qry))
                    {
                      error_log("Status updated successfully to 3(completed) for ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']." at id ".$get_pending_data_qry_result_row['id']." into tbl_social_blade_master");
                    }
                    else
                    {
                      error_log("Something went wrong while updating status to 3(completed) for ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']. "into tbl_social_blade_master");
                    }
                  }
                  else
                  {
                    error_log("Something went wrong while inserting data of ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']. "into tbl_social_blade_twt_chnls_statistics_ranks_growth_data");
                  }
                }
                else
                {
                  error_log("Something went wrong while updating status to 2(in process) for ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']. "into tbl_social_blade_master");
                }
              }
              else
              {
                error_log("Something went wrong while inserting data of ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']. "into tbl_social_blade_twt_chnls_daily_data");
              }
            }
          }
          else
          {
            error_log("Something went wrong while updating data of ".$get_pending_data_qry_result_row['cv_name']." for channel ".$get_pending_data_qry_result_row['chn_name']. " into tbl_social_blade_master at id ".$get_pending_data_qry_result_row['id']);
          }

        }
        else
        {
          error_log("something went wrong while creating json file".$file_name);
        }        
      }      
    }
  }
}
else
{
  error_log("No pending data availabe for processing");
}
error_log(date('Y/m/d h:i:s', time()));
error_log("Ended==============================================");
?>