<?php

/**
 *
 */
namespace App\Helpers;
use Illuminate\Support\Facades\DB;
class GenerateAuthToken 
{
    public static function get_token($c_date) {
        $get_future_date = DB::table('tbl_config')->where('type','=','future_date')->where('is_active','=',0)->first();
        $f_date = str_replace(":","",str_replace(" ","",str_replace("-","",$get_future_date->value)));
		$encp1 = str_replace(":","",str_replace(" ","",str_replace("-","",$c_date)));
		$date_diff = $f_date-$encp1;
		$date_diff_insec = $date_diff*60;
        $server_enc_token = md5($date_diff_insec.'pyMp3D0wnL0der');
      return $server_enc_token;        
    }
}