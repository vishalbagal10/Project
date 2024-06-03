<?php

// log started
$file = "logs/veritonic_uploader/veritonic_uploader_log_".date('Y-m-d').".log";
if(!is_file($file)){
    //Some simple example content.
    $contents = '';
    //Save our content to the file.
    file_put_contents("logs/veritonic_uploader/veritonic_uploader_log_".date('Y-m-d').".log", $contents);
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

$config_data = include('../config.php');

$conn = new mysqli($config_data['servername'], $config_data['username'], $config_data['password'], $config_data['dbname']);

$get_asset_data_qry = "SELECT tbl_assets.*, tbl_tool_users.veritonic_key FROM `tbl_assets` join tbl_tool_users on tbl_assets.sent_from = tbl_tool_users.uid WHERE tbl_assets.is_active = 0 AND tbl_assets.asset_d_status = 1 and tbl_assets.asset_veritonic_u_status = 0 and tbl_assets.v_status = 0 order by tbl_assets.c_date asc limit 25";

$get_asset_data_qry_res = $conn->query($get_asset_data_qry);

if($get_asset_data_qry_res->num_rows > 0)
{
  while($get_asset_data_qry_res_row = $get_asset_data_qry_res->fetch_assoc())
  {
    if($get_asset_data_qry_res_row['asset_upload_at'] == 0 || $get_asset_data_qry_res_row['asset_upload_at'] == 2)
    {
      $upload_curl = curl_init();

      $asset_id = $get_asset_data_qry_res_row['asset_id'];
      
      $file_name = $get_asset_data_qry_res_row['asset_name'];

      // $file_path = "http://localhost:7474/scs/".$get_asset_data_qry_res_row['asset_u_link']; // LOCAL SERVER
      // $file_path = "https://taxonomy.logthis.in/".$get_asset_data_qry_res_row['asset_u_link']; // TEST SERVER
      $file_path = "https://taxonomy.sonic-hub.com/".$get_asset_data_qry_res_row['asset_u_link']; // LIVE SERVER

      echo "<br>file_path->".$file_path."<br><br>";

      $get_asset_type_qry = "SELECT * FROM `tbl_veritonic_asset_types` WHERE asset_type_id =".$get_asset_data_qry_res_row['asset_type_id'];
      $get_asset_type_qry_res = $conn->query($get_asset_type_qry);
      $get_asset_type_qry_res_row = $get_asset_type_qry_res->fetch_assoc();
      //$asset_type = '"'.$get_asset_type_qry_res_row['asset_type_value'].'"';
      $asset_type = $get_asset_type_qry_res_row['asset_type_value'];
      
      $asset_country_arr = [];
      if($get_asset_data_qry_res_row['asset_market_id'] != 0 && $get_asset_data_qry_res_row['asset_market_id'] != '0')
      {
        $asset_market_data_arr = explode(",",$get_asset_data_qry_res_row['asset_market_id']);
        $country_id_arr = [];
        foreach($asset_market_data_arr as $asset_market_data)
        {
          if(explode("#_#",$asset_market_data)[0] == 0 || explode("#_#",$asset_market_data)[0] == '0')
          {
            $get_geo_region_data_qry = "SELECT * FROM `tbl_geo_region_mapping` WHERE geo_region_id = ".explode("#_#",$asset_market_data)[1]." AND geo_region_type = 0";
          }
          else
          {
            $get_geo_region_data_qry = "SELECT * FROM `tbl_geo_region_mapping` WHERE geo_region_id = ".explode("#_#",$asset_market_data)[0]." AND pgrnd_id = ".explode("#_#",$asset_market_data)[1]." AND geo_region_type = 1";
          }
          $get_geo_region_data_qry_res = $conn->query($get_geo_region_data_qry);
          $get_geo_region_data_qry_res_row = $get_geo_region_data_qry_res->fetch_assoc();

          if(!in_array($get_geo_region_data_qry_res_row['vcountry_id'], $country_id_arr))
            array_push($country_id_arr, $get_geo_region_data_qry_res_row['vcountry_id']);
        }
        

        $get_asset_country_qry = "SELECT * FROM `tbl_veritonic_countries` WHERE country_id IN (".implode(",",$country_id_arr).")";
        $get_asset_country_qry_res = $conn->query($get_asset_country_qry);
        //$asset_country_str = '[';
        
        if($get_asset_data_qry_res->num_rows > 1)
        {
          while($get_asset_country_qry_res_row = $get_asset_country_qry_res->fetch_assoc())
          {
            //array_push($asset_country_arr,'"'.$get_asset_country_qry_res_row['country_value'].'"');
            //echo $get_asset_country_qry_res_row['country_value'];
            array_push($asset_country_arr,$get_asset_country_qry_res_row['country_value']);
            //$asset_country_str .= '"'.$get_asset_country_qry_res_row['country_value'].'",';
          }
        }
        else
        {
          $get_asset_country_qry_res_row = $get_asset_country_qry_res->fetch_assoc();
          //array_push($asset_country_arr,'"'.$get_asset_country_qry_res_row['country_value'].'"');
          array_push($asset_country_arr,$get_asset_country_qry_res_row['country_value']);
          //$asset_country_str .= '"'.$get_asset_country_qry_res_row['country_value'].'",';
        }
        //print_r($asset_country_arr);
        $asset_country = implode(",",$asset_country_arr);
        //$asset_country = rtrim($asset_country_str,",")."]";
      }

      echo "asset_country_arr<br>";
      print_r($asset_country_arr);
      echo "<br><br>"; 
      
      $asset_language_arr = [];
      if($get_asset_data_qry_res_row['asset_language_id'] != 0 && $get_asset_data_qry_res_row['asset_language_id'] != '0')
      {
        $get_asset_language_qry = "SELECT * FROM `tbl_veritonic_languages` WHERE language_id IN (".$get_asset_data_qry_res_row['asset_language_id'].")";
        $get_asset_language_qry_res = $conn->query($get_asset_language_qry);
        //$asset_language_str = '[';
        
        if($get_asset_language_qry_res->num_rows > 1)
        {
          while($get_asset_language_qry_res_row = $get_asset_language_qry_res->fetch_assoc())
          {
            //array_push($asset_language_arr,'"'.$get_asset_language_qry_res_row['language_value'].'"');
            array_push($asset_language_arr,$get_asset_language_qry_res_row['language_value']);
            //$asset_language_str .= '"'.$get_asset_language_qry_res_row['language_value'].'",';
          }
        }
        else
        {
          $get_asset_language_qry_res_row = $get_asset_language_qry_res->fetch_assoc();
          //array_push($asset_language_arr,'"'.$get_asset_language_qry_res_row['language_value'].'"');
          array_push($asset_language_arr,$get_asset_language_qry_res_row['language_value']);
          //$asset_language_str .= '"'.$get_asset_language_qry_res_row['language_value'].'",';
        }
        $asset_language = implode(",",$asset_language_arr);
        //$asset_language = rtrim($asset_language_str,",")."]";
      }

      echo "asset_language_arr<br>";
      print_r($asset_language_arr); 
      echo "<br><br>";

      $asset_industry_idarr = [];
      if (strpos($get_asset_data_qry_res_row['asset_industry_id'], '#_#') !== false)
      {
          $asset_industry_id_arr = explode("#_#",$get_asset_data_qry_res_row['asset_industry_id']);
          if($asset_industry_id_arr[0] != 0)
          {
            $get_veritonic_industry_value_qry = "SELECT tbl_veritonic_industries.* FROM `tbl_industry_mapping` INNER JOIN tbl_veritonic_industries on tbl_industry_mapping.vind_id = tbl_veritonic_industries.industry_id WHERE tbl_industry_mapping.ind_type = 1 AND tbl_industry_mapping.ind_id =".$asset_industry_id_arr[0];
            $get_veritonic_industry_value_qry_res = $conn->query($get_veritonic_industry_value_qry);
            $get_veritonic_industry_value_qry_res_row = $get_veritonic_industry_value_qry_res->fetch_assoc();
            array_push($asset_industry_idarr,strtolower($get_veritonic_industry_value_qry_res_row['industry_value']));
          }
          else
          {
            $get_veritonic_industry_value_qry = "SELECT tbl_veritonic_industries.* FROM `tbl_industry_mapping` INNER JOIN tbl_veritonic_industries on tbl_industry_mapping.vind_id = tbl_veritonic_industries.industry_id WHERE tbl_industry_mapping.ind_type = 0 AND tbl_industry_mapping.ind_id =".$asset_industry_id_arr[1];
            $get_veritonic_industry_value_qry_res = $conn->query($get_veritonic_industry_value_qry);
            $get_veritonic_industry_value_qry_res_row = $get_veritonic_industry_value_qry_res->fetch_assoc();
            array_push($asset_industry_idarr,strtolower($get_veritonic_industry_value_qry_res_row['industry_value']));
          }
      }
      else
      {
        $asset_industry_idarr = [];
      }

      echo "asset_industry_idarr<br>";
      print_r($asset_industry_idarr); 
      echo "<br><br>";
      
      if(count($asset_country_arr)>0 && count($asset_language_arr)>0 && count($asset_industry_idarr)>0)
      {
        echo "IF";
        $upload_postData = [ "filename" => $file_name,
          "type" => "track",
          "src_file" => $file_path,
          "metadata" => ["track_type"=>$asset_type,"tags" => [ "country"=>$asset_country_arr,"industry"=>$asset_industry_idarr,"language"=>$asset_language_arr]]
        ];
      }
      elseif(count($asset_country_arr) == 0 && count($asset_language_arr) == 0 && count($asset_industry_idarr)>0)
      {
        echo "ELSE IF";
        $upload_postData = [ "filename" => $file_name,
          "type" => "track",
          "src_file" => $file_path,
          "metadata" => ["track_type"=>$asset_type,"tags" => [ "industry"=>$asset_industry_idarr]]
        ];
      }
      elseif(count($asset_country_arr) > 0 && count($asset_language_arr) > 0 && count($asset_industry_idarr) == 0)
      {
        echo "ELSE IF";
        $upload_postData = [ "filename" => $file_name,
          "type" => "track",
          "src_file" => $file_path,
          "metadata" => ["track_type"=>$asset_type,"tags" => [ "country"=>$asset_country_arr, "language"=>$asset_language_arr]]
        ];
      }
      else
      {

        echo "ELSE";
        $upload_postData = [ "filename" => $file_name,
          "type" => "track",
          "src_file" => $file_path,
          "metadata" => ["track_type"=>$asset_type]
        ];
      }
      

      print_r($upload_postData);
      echo "<br><br>";

      if($get_asset_data_qry_res_row['veritonic_key'] !='' && $get_asset_data_qry_res_row['veritonic_key'] != null)
      {
        curl_setopt_array($upload_curl, array(
          CURLOPT_URL => 'https://api.veritonic.com/api/v3/uploads',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_SSL_VERIFYPEER => false,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>json_encode($upload_postData),
          CURLOPT_HTTPHEADER => array(
            'authorization: bearer '.$get_asset_data_qry_res_row['veritonic_key'],
            'Content-Type: application/json'
          ),
        ));

        $upload_response = curl_exec($upload_curl);
        $upload_err = curl_error($upload_curl);
        curl_close($upload_curl);

        echo "---------------------------------------UPLOAD API REQUEST DATA---------------------------------------";
        echo json_encode($upload_postData);
        echo "---------------------------------------UPLOAD API REQUEST DATA---------------------------------------";

        echo "---------------------------------------UPLOAD API RESPONSE---------------------------------------";
        echo $upload_response;
        echo "---------------------------------------UPLOAD API RESPONSE---------------------------------------";

        $upload_result = json_decode($upload_response,true);
        $upload_id = $upload_result['upload_id'];
        //echo $upload_id;

        if ($upload_err)
        {
              echo "cURL Error #:" . $upload_err;
              error_log("Error occured after calling  upload API -".$upload_err);
              $conn->query("UPDATE `tbl_assets` SET `v_status` = 3, `asset_veritonic_u_status`=2 WHERE `asset_id` ='".$asset_id."'");
        }
        else
        {
          $upload_id = $upload_result['upload_id'];
          if($upload_id != '' && $upload_id != null)
          {
            error_log("Response recieved for Upload ID -".$upload_id." after calling upload API");
            $presigned_url = $upload_result['upload']['url'];
            $presigned_content_type = $upload_result['upload']['fields']['Content-Type'];
            $presigned_key = $upload_result['upload']['fields']['key'];
            $presigned_x_amz_algorithm = $upload_result['upload']['fields']['x-amz-algorithm'];
            $presigned_x_amz_credential = $upload_result['upload']['fields']['x-amz-credential'];
            $presigned_x_amz_date = $upload_result['upload']['fields']['x-amz-date'];
            $presigned_x_amz_security_token = $upload_result['upload']['fields']['x-amz-security-token'];
            $presigned_policy = $upload_result['upload']['fields']['policy'];
            $presigned_x_amz_signature = $upload_result['upload']['fields']['x-amz-signature'];
            $presigned_curl = curl_init();

            $presigned_postData = [
                "Content-Type" => $presigned_content_type,
                "key" => $presigned_key,
                "x-amz-algorithm" => $presigned_x_amz_algorithm,
                "x-amz-credential" => $presigned_x_amz_credential,
                "x-amz-date" => $presigned_x_amz_date,
                "x-amz-security-token" => $presigned_x_amz_security_token,
                "policy" => $presigned_policy,
                "x-amz-signature" => $presigned_x_amz_signature,
                "file" => new CURLFILE($file_path)
            ];
            print_r($presigned_postData);
            echo "2<br><br>";
            error_log("presigned_postData".json_encode($presigned_postData));

            curl_setopt_array($presigned_curl, array(
              CURLOPT_URL => $presigned_url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_SSL_VERIFYPEER => false,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              //CURLOPT_POSTFIELDS => array('Content-Type' => 'audio/mpeg','key' => $presigned_key,'x-amz-algorithm' => $presigned_x_amz_algorithm,'x-amz-credential' => $presigned_x_amz_credential,'x-amz-date' => $presigned_x_amz_date,'x-amz-security-token' => $presigned_x_amz_security_token,'policy' => $presigned_policy,'x-amz-signature' => $presigned_x_amz_signature,'file'=> new CURLFILE($file_path)),
              CURLOPT_POSTFIELDS => $presigned_postData,
            ));

            $presigned_response = curl_exec($presigned_curl);
            $presigned_status_code = curl_getinfo($presigned_curl, CURLINFO_HTTP_CODE);
            $presigned_err = curl_error($presigned_curl);

            curl_close($presigned_curl);

            echo "---------------------------------------PRESIGNED API RESPONSE---------------------------------------";
            echo $presigned_response;
            error_log("presigned_response".$presigned_response);
            echo "---------------------------------------PRESIGNED API RESPONSE---------------------------------------";

            if ($presigned_err)
            {
                  echo "cURL Error #:" . $presigned_err;
                  error_log("Error occured after calling  presigned API -".$presigned_err);
                  $conn->query("UPDATE `tbl_assets` SET `v_status` = 3, `asset_veritonic_u_status`=2 WHERE `asset_id` ='".$asset_id."'");
            }
            else
            {
              error_log("presigned status code -".$presigned_status_code." after calling presigned API");
              if($presigned_status_code == 200 || $presigned_status_code == '200' || $presigned_status_code == 204 || $presigned_status_code == '204')
              {
                error_log("Response recieved for Presigned ID -".$presigned_status_code." after calling presigned API");

                $patch_curl = curl_init();

                $patch_postData = [ "uploaded" => true ];

                curl_setopt_array($patch_curl, array(
                  CURLOPT_URL => "https://api.veritonic.com/api/v3/uploads/".$upload_id,
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_SSL_VERIFYPEER => false,
                  CURLOPT_ENCODING => "",
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 30,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => "PATCH",
                  //CURLOPT_POSTFIELDS => "{\r\n\t\"uploaded\": true\r\n}",
                  CURLOPT_POSTFIELDS => json_encode($patch_postData),
                  CURLOPT_HTTPHEADER => array(
                    "authorization: bearer ".$get_asset_data_qry_res_row['veritonic_key'],
                    'content-type' => 'application/json'
                  ),
                ));

                $patch_response = curl_exec($patch_curl);
                $patch_err = curl_error($patch_curl);

                curl_close($patch_curl);

                if ($patch_err)
                {
                  echo "patch_err cURL Error #:" . $patch_err;
                  error_log("Error occured after calling  patch API -".$patch_err);
                  $conn->query("UPDATE `tbl_assets` SET `v_status` = 3, `asset_veritonic_u_status`=2 WHERE `asset_id` ='".$asset_id."'");
                }
                else
                {
                  echo $patch_response;
                  error_log("patch API response -".$patch_response);
                  //$conn = new mysqli($config_data['servername'], $config_data['username'], $config_data['password'], $config_data['dbname']);
                  error_log("INSERT INTO `tbl_veritonic`(`upload_id`) VALUES ('".$upload_id."')");
                  if($conn->query("INSERT INTO `tbl_veritonic`(`upload_id`) VALUES ('".$upload_id."')") === TRUE)
                  {
                    $v_id = $conn->insert_id;
                    $c_date = date('Y-m-d H:i:s');
                    if($conn->query("UPDATE `tbl_assets` SET `asset_veritonic_u_status`=1, `asset_veritonic_u_date` = '".$c_date."', `v_id` = ".$v_id.", `v_status` = 1 WHERE `asset_id` ='".$asset_id."'") === TRUE)
                    {
                      error_log("Upload ID -".$upload_id." successfully inserted into veritonic table and v_id=".$v_id." and v_status=1 successfully updated into asset table");
                      echo "Upload ID -".$upload_id." successfully inserted into veritonic table and v_id=".$v_id." and v_status=1 successfully updated into asset table";
                    }
                    else
                    {
                      error_log("Error ocurred while updating v_id=".$v_id." and v_status=1 for asset=".$asset_id." into asset table");
                    }
                  }
                  else
                  {
                    error_log("Error ocurred while inserting Upload ID -".$upload_id." into veritonic table");
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
      if($conn->query("UPDATE `tbl_assets` SET `v_status`=2, `asset_veritonic_u_status`=3 WHERE `asset_id`='".$get_asset_data_qry_res_row['asset_id']."'"))
      {
        error_log("Assets asset_veritonic_u_status set to 3 (not require) and v_status set to 2 (complete) and not uplaoded at veritonic because asset has upload at 1");
      }
      else
      {
        error_log("Something went wrong while updating v_status of asset ".$get_asset_data_qry_res_row['asset_id']);
      }
    }   
  }
  $conn->close();
}
else
{
  error_log("No asstes found for upload");
}
error_log("Ended at");
error_log(date('Y/m/d h:i:s', time()));
error_log("==========================================================================================================================================");
