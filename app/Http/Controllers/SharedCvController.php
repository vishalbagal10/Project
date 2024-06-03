<?php

namespace App\Http\Controllers;

use App\Models\SharedCv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use App\Helpers\UserSystemInfoHelper;
use Illuminate\Support\Facades\Mail;
use App\Mail\SurgeNotificationEmail;
use App\Helpers\ErrorMailSender;

class SharedCvController extends Controller
{
    function verifyShareLink(Request $request)
    {
        $token = $request->token;
        //echo $token;
        return view('frontend.views.shared_cv_access_authenticator',['token' => $token]);
        /* $link_active = DB::table('tbl_shared_cv')
                    ->where('share_link_token', '=', $token)
                    ->where('is_active', '=', 0)
                    ->first();
        if($link_active != '')
        {
            return view('frontend.views.shared_cv_access_authenticator',['token' => $token]);
        }
        else
        {

        } */
    }

    public function getMusicExpenditurePerVideoAvgData($industry_id)
    {
        $cv_ids_array = [];

        $cvs_as_per_industry = DB::table('tbl_cvs')
                    ->join('tbl_cv_block_15_data', 'tbl_cvs.cv_id', '=', 'tbl_cv_block_15_data.cv_id')
                    ->where('tbl_cvs.industry_id', $industry_id)
                    ->where('tbl_cvs.status', 1)
                    ->where('tbl_cvs.is_active', 0)
                    ->whereNotNull('tbl_cv_block_15_data.b15_number')
                    ->get();

        foreach($cvs_as_per_industry as $cv_items)
        {
            array_push($cv_ids_array, $cv_items->cv_id);
        }

        $b15_sum_data = DB::table('tbl_cv_block_15_data')
            ->whereIn('cv_id', $cv_ids_array)
            ->where('is_active', 0)
            ->sum('b15_number');

        return $b15_sum_data."_".count($cv_ids_array);

        /* $cv_ids_array = [];

        $cvs_as_per_industry = DB::table('tbl_cvs')
                    ->where('industry_id', $industry_id)
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->get();

        foreach($cvs_as_per_industry as $cv_items)
        {
            array_push($cv_ids_array, $cv_items->cv_id);
        }

        $b15_sum_data_array = [];
        foreach($cv_ids_array as $cv_id)
        {
            $b15_sum_data = DB::table('tbl_cv_block_15_data')
            ->where('cv_id', $cv_id)
            ->where('is_active', 0)
            ->first();

            if($b15_sum_data->b15_number !='' && $b15_sum_data->b15_number !=null)
            {
                array_push($b15_sum_data_array,$b15_sum_data->b15_number);
            }
        }

        return $b15_sum_data_array; */
    }

    public function getMusicExpenditurePerYearAvgData($industry_id)
    {
        $cv_ids_array = [];

        $cvs_as_per_industry = DB::table('tbl_cvs')
                    ->join('tbl_cv_block_14_data', 'tbl_cvs.cv_id', '=', 'tbl_cv_block_14_data.cv_id')
                    ->where('tbl_cvs.industry_id', $industry_id)
                    ->where('tbl_cvs.status', 1)
                    ->where('tbl_cvs.is_active', 0)
                    ->whereNotNull('tbl_cv_block_14_data.b14_number')
                    ->get();

        foreach($cvs_as_per_industry as $cv_items)
        {
            array_push($cv_ids_array, $cv_items->cv_id);
        }

        $b14_sum_data = DB::table('tbl_cv_block_14_data')
            ->whereIn('cv_id', $cv_ids_array)
            ->where('is_active', 0)
            ->sum('b14_number');

        return $b14_sum_data."_".count($cv_ids_array);

        /* $b14_sum_data_array = [];
        foreach($cv_ids_array as $cv_id)
        {
            $b14_sum_data = DB::table('tbl_cv_block_14_data')
            ->where('cv_id', $cv_id)
            ->where('is_active', 0)
            ->first();

            if($b14_sum_data->b14_number !='' && $b14_sum_data->b14_number !=null)
            {
                array_push($b14_sum_data_array,$b14_sum_data->b14_number);
            }
        }

        return $b14_sum_data_array; */
    }

    public function getMoneySpentOnAudioAvgData($industry_id)
    {
        $cv_ids_array = [];

        $cvs_as_per_industry = DB::table('tbl_cvs')
                    ->where('industry_id', $industry_id)
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->get();

        foreach($cvs_as_per_industry as $cv_items)
        {
            array_push($cv_ids_array, $cv_items->cv_id);
        }

        $b11_sum_data_array = [];
        foreach($cv_ids_array as $cv_id)
        {
            $b11_sum_data = DB::table('tbl_cv_block_11_data')
            ->where('cv_id', $cv_id)
            ->where('is_active', 0)
            ->first();

            if($b11_sum_data->b11_number !='' && $b11_sum_data->b11_number !=null)
            {
                array_push($b11_sum_data_array,$b11_sum_data->b11_number);
            }
        }

        return $b11_sum_data_array;
    }

    public function getIndustryAvgData($industry_id)
    {
        $cv_ids_array = [];

        $cvs_as_per_industry = DB::table('tbl_cvs')
                    ->where('industry_id', $industry_id)
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->get();

        foreach($cvs_as_per_industry as $cv_items)
        {
            array_push($cv_ids_array, $cv_items->cv_id);
        }

        $insudtry_yes_avg_data_array = [];
        $insudtry_no_avg_data_array = [];
        foreach($cv_ids_array as $cv_id)
        {
            $insudtry_avg_data = DB::table('tbl_cv_block_7_data')
                        ->where('cv_id', $cv_id)
                        ->where('is_active', 0)
                        ->get();
            foreach($insudtry_avg_data as $data)
            {
                if($data->b7_name == 'yes' || $data->b7_name == 'Yes' || $data->b7_name == 'YES')
                {
                    if($data->b7_number!='' && $data->b7_number !=null)
                    {
                        array_push($insudtry_yes_avg_data_array,$data->b7_number);
                    }
                }
                if($data->b7_name == 'no' || $data->b7_name == 'No' || $data->b7_name == 'NO')
                {
                    if($data->b7_number!='' && $data->b7_number !=null)
                    {
                        array_push($insudtry_no_avg_data_array,$data->b7_number);
                    }
                }
            }
        }
        return ['insudtry_yes_avg_data_array'=>$insudtry_yes_avg_data_array, 'insudtry_no_avg_data_array'=>$insudtry_no_avg_data_array];

    }

    function sharedCv(Request $request)
    {
        try
        {
            $cv_data = DB::table('tbl_shared_cv')
                    ->where('email', '=', $request->semail)
                    ->where('share_link_token', '=', $request->token)
                    ->where('is_active', '=', 0)
                    ->first();
            if($cv_data !='')
            {
                $curr_date = Carbon::today();
                if($curr_date->lt($cv_data->link_expiry_date))
                {
                    $user_ip = UserSystemInfoHelper::get_ip();
                    $user_browser = UserSystemInfoHelper::get_browsers();
                    $user_device = UserSystemInfoHelper::get_device();
                    $user_os = UserSystemInfoHelper::get_os();

                    $activity_data = ['scv_id' => $cv_data->id,
                                'cv_id' => $cv_data->cv_id,
                                'email' => $cv_data->email,
                                'ip' => $user_ip,
                                'browser' => $user_browser,
                                'os' => $user_os,
                                'device' => $user_device
                                ];
                    try
                    {
                        DB::table('tbl_shared_cv_activity')->insert($activity_data);

                        $sharing_time = config('custom.sharing_time');

                        $results = DB::select( DB::raw("SELECT COUNT(*) as view_count FROM `tbl_shared_cv_activity` WHERE `scv_id`= '$cv_data->id' AND `cv_id`= '$cv_data->cv_id' AND `email`= '$cv_data->email' AND (`created_at` between DATE_SUB(NOW(),INTERVAL '$sharing_time' MINUTE)and now())") );

                        $view_count = $results[0]->view_count;

                        $total_cv_view_count = DB::table('tbl_shared_cv_activity')->where('cv_id', '=', $cv_data->cv_id)->where('scv_id', '=', $cv_data->id)->where('email', '=', $cv_data->email)->count();

                        //print_r($total_cv_view_count); exit;

                        DB::table('tbl_shared_cv')
                            ->where('id', $cv_data->id)
                            ->update(['view_count' => $total_cv_view_count, 'edited_by'=>session('LoggedUser')]);

                        if($view_count >= config('custom.sharing_view_count'))
                        {
                            $get_cv_name_with_year = DB::table('tbl_cvs')->where("cv_id", "=", $cv_data->cv_id)->first();

                            $email_data = [
                                'cv_name' => $get_cv_name_with_year->cv_name." ".explode("-",$get_cv_name_with_year->cv_date)[1] ,
                                'view_count' => $view_count,
                                'sharing_time' => $sharing_time,
                                'email' => $cv_data->email
                            ];

                            $admin_to_mail_id = config('custom.admin_to_mail_id');
                            $bcc_mail_id = config('custom.bcc_mail_id');
                            //Mail::to("support@wits.bz")->send(new SurgeNotificationEmail($email_data));
                            Mail::to($admin_to_mail_id)->bcc($bcc_mail_id)->send(new SurgeNotificationEmail($email_data));
                        }

                        $cv_data = DB::table('tbl_cvs')->where('cv_id', '=', $cv_data->cv_id)->first();

                        //$child_cv = DB::table('tbl_cvs')->where('parent_id', '=', base64_decode($cvid))->where('status', '=', 1)->where('is_active', '=', 0)->first();

                        if($cv_data->parent_id != '' && $cv_data->parent_id != null)
                        {
                            //$cv_parent_list = DB::table('tbl_cvs')->where('parent_id', '=', $cv_data->parent_id)->where('status', '=', 1)->where('is_active', '=', 0)->get();
                            $parent_cv = DB::table('tbl_cvs')->where('cv_id', '=', $cv_data->parent_id)->where('status', '=', 1)->where('is_active', '=', 0)->first();
                            //array_push($cv_parent_list,$parent_cv);
                        }
                        else
                        {
                            $parent_cv = null;
                        }
                        if($parent_cv != null)
                        {
                            $parent_cv_overall_ranking = DB::table('tbl_best_in_audio_brands')->where('cv_id', '=', $parent_cv->cv_id)->where('is_active', '=', 0)->first();

                        }
                        else
                        {
                            $parent_cv_overall_ranking = '';
                        }
                        //print_r($parent_cv_overall_ranking);exit;

                        $cv_block_2_data = DB::table('tbl_best_in_audio_brands')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->first();
                        $cv_block_3_data = DB::table('tbl_cv_block_3_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->get();
                        $cv_block_4_data = DB::table('tbl_cv_block_4_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->first();
                        $cv_block_5_data = DB::table('tbl_cv_block_5_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->get();
                        $cv_block_6_data = DB::table('tbl_cv_block_6_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->orderBy('b6_id','asc')->get();
                        $cv_block_7_data = DB::table('tbl_cv_block_7_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->get();
                        $cv_block_8_data = DB::table('tbl_cv_block_8_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->get();
                        $cv_block_9_data = DB::table('tbl_cv_block_9_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->get();
                        $cv_block_10_data = DB::table('tbl_cv_block_10_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->get();
                        $cv_block_11_data = DB::table('tbl_cv_block_11_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->get();
                        $cv_block_12_data = DB::table('tbl_cv_block_12_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->get();
                        // $cv_block_13_data = DB::table('tbl_cv_block_13_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->get();
                        $cv_block_13_data = DB::table('tbl_cv_block_13_data')
                            ->join('tbl_experience', 'tbl_cv_block_13_data.b13_name_id', '=', 'tbl_experience.experience_id')
                            ->select('tbl_cv_block_13_data.*', 'tbl_experience.experience_name')
                            ->where('tbl_cv_block_13_data.cv_id', '=', $cv_data->cv_id)
                            ->where('tbl_cv_block_13_data.is_active', '=', 0)
                            ->orderBy('tbl_experience.display_order', 'ASC')
                            ->get();
                        $cv_block_14_data = DB::table('tbl_cv_block_14_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->first();
                        $cv_block_15_data = DB::table('tbl_cv_block_15_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->first();
                        /* $cv_block_16_mood_graph_data = DB::table('tbl_cv_block_16_mood_graph_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->first();
                        $cv_block_16_genre_graph_data = DB::table('tbl_cv_block_16_genre_graph_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->first();
                        $cv_block_17_mood_graph_data = DB::table('tbl_cv_block_17_mood_graph_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->first();
                        $cv_block_17_genre_graph_data = DB::table('tbl_cv_block_17_genre_graph_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->first();
                        $cv_block_18_mood_graph_data = DB::table('tbl_cv_block_18_mood_graph_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->first();
                        $cv_block_18_genre_graph_data = DB::table('tbl_cv_block_18_genre_graph_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->first();
                        $cv_block_19_mood_graph_data = DB::table('tbl_cv_block_19_mood_graph_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->first();
                        $cv_block_19_genre_graph_data = DB::table('tbl_cv_block_19_genre_graph_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->first();
                        $cv_mood_aggr_graph_data = DB::table('tbl_mood_aggr_graph_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->first();
                        $cv_genre_aggr_graph_data = DB::table('tbl_genre_aggr_graph_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->first(); */
                        $footer_template_data = DB::table('tbl_footer_template')->where('footer_template_id', '=', $cv_data->footer_template_id)->first();

                        /* $cv_genre_aggr_graph_values_data = DB::table('tbl_genre_aggr_graph_data')->select('ambient','blues','classical','country','electronicDance','folk','indieAlternative','jazz','latin','metal','pop','punk','rapHipHop','reggae','rnb','rock','singerSongwriter')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->first();
                        $cv_genre_aggr_graph_values_arr = (array)$cv_genre_aggr_graph_values_data;
                        $cv_genre_aggr_graph_values_arr1 = (array)$cv_genre_aggr_graph_values_data;
                        rsort($cv_genre_aggr_graph_values_arr);
                        $top3 = array_slice($cv_genre_aggr_graph_values_arr, 0, 3);
                        $top_3_genre = array();
                        foreach ($top3 as $key => $val) {
                            //echo "key-".$key."----------- val-".$val."<br>";
                            $key = array_search ($val, $cv_genre_aggr_graph_values_arr1);
                            unset($cv_genre_aggr_graph_values_arr1[$key]);
                            $top_3_genre[$key] = $val;
                        }

                        if(count($top_3_genre)==0)
                        {
                            $top_3_genre = '';
                        } */

                        if(count($cv_block_3_data)==0)
                        {
                            $cv_block_3_data = '';
                            $music_taste_data = [];
                        }
                        else
                        {
                            $mti_ids_array = [];
                            for($mti = 0; $mti < count($cv_block_3_data); $mti++)
                            {
                                array_push($mti_ids_array,$cv_block_3_data[$mti]->b3_title_id);
                            }
                            if(count($mti_ids_array)>0)
                            {
                                $music_taste_data = DB::table('tbl_music_taste')
                                    ->whereIn('music_taste_id', $mti_ids_array)
                                    ->get();
                            }
                            else
                            {
                                $music_taste_data = [];
                            }
                        }
                        if(count($music_taste_data)==0)
                        {
                            $music_taste_data = '';
                        }
                        if(count($cv_block_5_data)==0)
                        {
                            $cv_block_5_data = '';
                        }
                        // print_r($cv_block_6_data);
                        if(count($cv_block_6_data)==0)
                        {
                            // echo 'if';
                            $cv_block_6_data = '';
                        }
                        else
                        {
                            $cv_block_6_data_arr = [];
                            foreach($cv_block_6_data as $b6key => $b6data)
                            {

                                if($b6data->b6_name != null && $b6data->b6_name !='')
                                {
                                    //echo $b6data->b6_name;
                                    array_push($cv_block_6_data_arr, $b6data->b6_name);
                                }

                            }

                            if(empty($cv_block_6_data_arr))
                            {
                                $cv_block_6_data = '';
                            }

                        }
                        // print_r($cv_block_6_data);
                        // exit;
                        if(count($cv_block_7_data)==0)
                        {
                            $cv_block_7_data = '';
                        }
                        //print_r($cv_block_7_data); exit;
                        if(count($cv_block_8_data)==0)
                        {
                            $cv_block_8_data = '';
                        }
                        if(count($cv_block_9_data)==0)
                        {
                            $cv_block_9_data = '';
                        }
                        if(count($cv_block_10_data)==0)
                        {
                            $cv_block_10_data = '';
                            $qualitative_data = [];
                        }
                        else
                        {
                            $qlti_ids_array = [];
                            $qualitative_data = [];

                            for($qlti = 0; $qlti < count($cv_block_10_data); $qlti++)
                            {
                                if($cv_block_10_data[$qlti]->b10_name_id != 0 && ($cv_block_10_data[$qlti]->b10_number !='' && $cv_block_10_data[$qlti]->b10_number !=null))
                                {
                                    array_push($qlti_ids_array,$cv_block_10_data[$qlti]->b10_name_id);
                                    $qualitative_id_data = DB::table('tbl_qualitative')
                                        ->where('qualitative_id', $cv_block_10_data[$qlti]->b10_name_id)
                                        ->first();
                                    //array_push($qualitative_data,$qualitative_id_data->qualitative_name);
                                    $qualitative_data[$qualitative_id_data->qualitative_name.'$_$'.$cv_block_10_data[$qlti]->b10_color] = $cv_block_10_data[$qlti]->b10_number;
                                }
                            }
                        }
                        if(count($qualitative_data)==0)
                        {
                            $qualitative_data = '';
                        }
                        //print_r($qualitative_data);exit;
                        if(count($cv_block_11_data)==0)
                        {
                            $cv_block_11_data = '';
                        }
                        if(count($cv_block_12_data)==0)
                        {
                            $cv_block_12_data = '';
                        }
                        //print_r($cv_block_12_data); exit;
                        if(count($cv_block_13_data)==0)
                        {
                            $cv_block_13_data = '';
                            $experience_data = [];
                            $experience_excluded_data = [];
                        }
                        else
                        {
                            $ei_ids_array = [];
                            $experience_data = [];
                            for($ei = 0; $ei < count($cv_block_13_data); $ei++)
                            {
                                if($cv_block_13_data[$ei]->b13_name_id !='' && $cv_block_13_data[$ei]->b13_name_id != null  && $cv_block_13_data[$ei]->b13_name_id != 0)
                                {
                                    $experience_qry_data = DB::table('tbl_experience')
                                    ->where('experience_id', $cv_block_13_data[$ei]->b13_name_id)
                                    ->first();
                                    $experience_data[$experience_qry_data->experience_name] = $cv_block_13_data[$ei]->b13_number;
                                    array_push($ei_ids_array,$cv_block_13_data[$ei]->b13_name_id);
                                }

                            }
                            if(count($ei_ids_array)>0)
                            {
                                /* $experience_data = DB::table('tbl_experience')
                                    ->whereIn('experience_id', $ei_ids_array)
                                    ->get(); */
                                $experience_excluded_data =  DB::table('tbl_experience')
                                ->whereNotIn('experience_id', $ei_ids_array)
                                ->where("is_active", '=', '0')
                                ->get();
                            }
                            else
                            {
                                // $experience_data = [];
                                $experience_excluded_data = [];
                            }
                        }

                        if(count($experience_data)==0)
                        {
                            $experience_data = '';
                            $experience_excluded_data = '';
                        }

                        /* $distinct_months_data = DB::table('tbl_month_mood_graph_data')->select('month')->distinct()->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->get();
                        $distinct_months_arr_data = [];
                        //print_r($distinct_months_data);
                        if(count($distinct_months_data) == 0)
                        {
                            $distinct_months_arr_data = '';
                        }
                        else
                        {
                            foreach($distinct_months_data as $month)
                            {
                                array_push($distinct_months_arr_data, $month->month);
                            }
                        } */
                        $distinct_months_arr_data = '';

                        $mood_genre_video_data = DB::table('tbl_mood_genre_yt_videos')->where('cv_id','=',$cv_data->cv_id)->where('is_active', '=', 0)->first();
                        $mood_video_data_arr = [];
                        $genre_video_data_arr = [];
                        if($mood_genre_video_data !='')
                        {
                            if($mood_genre_video_data->mood_v1_id!='')
                            {
                                $mood_video_data_arr[$mood_genre_video_data->mood_v1_id] = $mood_genre_video_data->mood_v1_title;
                            }
                            if($mood_genre_video_data->mood_v2_id!='')
                            {
                                $mood_video_data_arr[$mood_genre_video_data->mood_v2_id] = $mood_genre_video_data->mood_v2_title;
                            }
                            if($mood_genre_video_data->mood_v3_id!='')
                            {
                                $mood_video_data_arr[$mood_genre_video_data->mood_v3_id] = $mood_genre_video_data->mood_v3_title;
                            }

                            if($mood_genre_video_data->genre_v1_id!='')
                            {
                                $genre_video_data_arr[$mood_genre_video_data->genre_v1_id] = $mood_genre_video_data->genre_v1_title;
                            }
                            if($mood_genre_video_data->genre_v2_id!='')
                            {
                                $genre_video_data_arr[$mood_genre_video_data->genre_v2_id] = $mood_genre_video_data->genre_v2_title;
                            }
                            if($mood_genre_video_data->genre_v3_id!='')
                            {
                                $genre_video_data_arr[$mood_genre_video_data->genre_v3_id] = $mood_genre_video_data->genre_v3_title;
                            }
                        }

                        $mood_video_graph_data_arr = [];
                        //================ get mood video asset data amp tag and value ===============
                        foreach ($mood_video_data_arr as $mvdakey => $mvdavalue) {
                            $mvideo_id = explode("$|$",$mvdakey)[1];

                            $mood_asset_graph_data =  DB::select(DB::raw("SELECT tbl_asset_processed_amp_main_mood_tag_data.*,tbl_amp_main_mood_tag_master.tag_name FROM `tbl_social_spyder_graph_meta_data`
                            LEFT JOIN tbl_assets on tbl_social_spyder_graph_meta_data.asset_id = tbl_assets.id
                            LEFT JOIN tbl_asset_processed_amp_main_mood_tag_data on tbl_assets.cs_asset_id = tbl_asset_processed_amp_main_mood_tag_data.asset_id
                            LEFT JOIN tbl_amp_main_mood_tag_master on tbl_asset_processed_amp_main_mood_tag_data.amp_main_mood_tag = tbl_amp_main_mood_tag_master.tag_id
                            WHERE cv_id = '".$cv_data->cv_id."' and tbl_social_spyder_graph_meta_data.is_active = 0 and tbl_social_spyder_graph_meta_data.cs_status = 1 and tbl_social_spyder_graph_meta_data.process_type = 'youtube' and video_id = '".$mvideo_id."'
                            and tbl_assets.is_active = 0 and tbl_assets.cs_d_status = 1 and tbl_assets.cs_response_status = 2 and tbl_amp_main_mood_tag_master.is_active = 0 and tbl_asset_processed_amp_main_mood_tag_data.is_active = 0"));

                            /* $mood_asset_graph_data =  DB::table('tbl_social_spyder_graph_meta_data')
                                ->join('tbl_assets', 'tbl_social_spyder_graph_meta_data.asset_id', '=', 'tbl_assets.id')
                                ->join('tbl_asset_processed_amp_main_mood_tag_data', 'tbl_assets.cs_asset_id', '=', 'tbl_asset_processed_amp_main_mood_tag_data.asset_id')
                                ->join('tbl_amp_main_mood_tag_master', 'tbl_asset_processed_amp_main_mood_tag_data.amp_main_mood_tag', '=', 'tbl_amp_main_mood_tag_master.tag_id')
                                ->select('tbl_asset_processed_amp_main_mood_tag_data.*', 'tbl_amp_main_mood_tag_master.tag_name')
                                ->where('tbl_social_spyder_graph_meta_data.cv_id', '=', $cv_data->cv_id)
                                ->where('tbl_social_spyder_graph_meta_data.is_active', '=', 0)
                                ->where('tbl_social_spyder_graph_meta_data.cs_status', '=', 1)
                                ->where('tbl_social_spyder_graph_meta_data.process_type', '=', 'youtube')
                                ->where('tbl_social_spyder_graph_meta_data.video_id', '=', $mvideo_id)
                                ->whereNotNull('tbl_social_spyder_graph_meta_data.asset_id')
                                ->where('tbl_assets.is_active', '=', 0)
                                ->where('tbl_assets.cs_d_status', '=', 1)
                                ->where('tbl_assets.cs_response_status', '=', 2)
                                ->where('tbl_amp_main_mood_tag_master.is_active', '=', 0)
                                ->where('tbl_asset_processed_amp_main_mood_tag_data.is_active', '=', 0)
                                ->get(); */

                            if(count($mood_asset_graph_data) > 0){
                                foreach ($mood_asset_graph_data as $magdkey => $magdvalue) {
                                    //$mood_video_graph_data_arr[$mvdakey][$magdvalue->tag_name] = $magdvalue->amp_main_mood_tag_value;
                                    $mood_video_graph_data_arr[$mvideo_id][$magdvalue->tag_name] = $magdvalue->amp_main_mood_tag_value;
                                }
                            }
                        }

                        $genre_video_graph_data_arr = [];
                        //================ get genre video asset data amp tag and value ===============
                        foreach ($genre_video_data_arr as $gvdakey => $gvdavalue) {
                            $gvideo_id = explode("$|$",$gvdakey)[1];

                            $genre_asset_graph_data =  DB::select(DB::raw("SELECT tbl_asset_processed_amp_main_mood_tag_data.*,tbl_amp_main_mood_tag_master.tag_name FROM `tbl_social_spyder_graph_meta_data`
                            LEFT JOIN tbl_assets on tbl_social_spyder_graph_meta_data.asset_id = tbl_assets.id
                            LEFT JOIN tbl_asset_processed_amp_main_mood_tag_data on tbl_assets.cs_asset_id = tbl_asset_processed_amp_main_mood_tag_data.asset_id
                            LEFT JOIN tbl_amp_main_mood_tag_master on tbl_asset_processed_amp_main_mood_tag_data.amp_main_mood_tag = tbl_amp_main_mood_tag_master.tag_id
                            WHERE cv_id = '".$cv_data->cv_id."' and tbl_social_spyder_graph_meta_data.is_active = 0 and tbl_social_spyder_graph_meta_data.cs_status = 1 and tbl_social_spyder_graph_meta_data.process_type = 'youtube' and video_id = '".$gvideo_id."'
                            and tbl_assets.is_active = 0 and tbl_assets.cs_d_status = 1 and tbl_assets.cs_response_status = 2 and tbl_amp_main_mood_tag_master.is_active = 0 and tbl_asset_processed_amp_main_mood_tag_data.is_active = 0"));

                            if(count($genre_asset_graph_data) > 0){
                                foreach ($genre_asset_graph_data as $gagdkey => $gagdvalue) {
                                    //$genre_video_graph_data_arr[$gvdakey][$gagdvalue->tag_name] = $gagdvalue->amp_main_mood_tag_value;
                                    $genre_video_graph_data_arr[$gvideo_id][$gagdvalue->tag_name] = $gagdvalue->amp_main_mood_tag_value;
                                }
                            }
                        }

                        $pre_year = $cv_data->cv_year-1;
                        $start_date = $pre_year."-01-01";
                        $end_date = $pre_year."-12-31";


                        $social_media_stats_s1 = [];
                        $social_media_stats_os = [];
                        /* $get_social_stats_yt_desc_data = DB::table('tbl_social_blade_master')
                                                ->join('tbl_social_blade_yt_chnls_daily_data', 'tbl_social_blade_master.id', '=', 'tbl_social_blade_yt_chnls_daily_data.mt_id')
                                                ->select('tbl_social_blade_yt_chnls_daily_data.*')
                                                //->where('tbl_social_blade_master.cv_id', '=', $cv_data->cv_id)
                                                ->where('tbl_social_blade_master.cv_name', 'like', $cv_data->cv_name."%")
                                                ->where('tbl_social_blade_master.social_media_id', '=', 1)
                                                ->whereBetween('tbl_social_blade_yt_chnls_daily_data.date', [$start_date, $end_date])
                                                ->orderBy('tbl_social_blade_yt_chnls_daily_data.date','desc')->first();
                        $get_social_stats_yt_asc_data = DB::table('tbl_social_blade_master')
                                                ->join('tbl_social_blade_yt_chnls_daily_data', 'tbl_social_blade_master.id', '=', 'tbl_social_blade_yt_chnls_daily_data.mt_id')
                                                ->select('tbl_social_blade_yt_chnls_daily_data.*')
                                                //->where('tbl_social_blade_master.cv_id', '=', base64_decode($cvid))
                                                ->where('tbl_social_blade_master.cv_name', 'like', $cv_data->cv_name."%")
                                                ->where('tbl_social_blade_master.social_media_id', '=', 1)
                                                ->whereBetween('tbl_social_blade_yt_chnls_daily_data.date', [$start_date, $end_date])
                                                ->orderBy('tbl_social_blade_yt_chnls_daily_data.date','asc')->first();
                        $social_stats_yt_s1_data = [];
                        $social_stats_yt_os_data = [];
                        if($get_social_stats_yt_desc_data !='' && $get_social_stats_yt_asc_data!='')
                        {
                            $social_stats_yt_s1_data['type'] = 'yt';
                            $social_stats_yt_s1_data['Subscribers'] = $get_social_stats_yt_desc_data->subs;
                            $social_stats_yt_s1_data['Views'] = $get_social_stats_yt_desc_data->views;
                            $social_stats_yt_os_data['type'] = 'yt';
                            $social_stats_yt_os_data['Subscribers'] = $get_social_stats_yt_desc_data->subs."_".$get_social_stats_yt_asc_data->subs;
                            $social_stats_yt_os_data['Views'] = $get_social_stats_yt_desc_data->views."_".$get_social_stats_yt_asc_data->views;
                        }
                        $get_social_stats_ig_desc_data = DB::table('tbl_social_blade_master')
                                                ->join('tbl_social_blade_ig_chnls_daily_data', 'tbl_social_blade_master.id', '=', 'tbl_social_blade_ig_chnls_daily_data.mt_id')
                                                ->select('tbl_social_blade_ig_chnls_daily_data.*')
                                                //->where('tbl_social_blade_master.cv_id', '=', base64_decode($cvid))
                                                ->where('tbl_social_blade_master.cv_name', 'like', $cv_data->cv_name."%")
                                                ->where('tbl_social_blade_master.social_media_id', '=', 2)
                                                ->whereBetween('tbl_social_blade_ig_chnls_daily_data.date', [$start_date, $end_date])
                                                ->orderBy('tbl_social_blade_ig_chnls_daily_data.date','desc')->first();
                        $get_social_stats_ig_asc_data = DB::table('tbl_social_blade_master')
                                                ->join('tbl_social_blade_ig_chnls_daily_data', 'tbl_social_blade_master.id', '=', 'tbl_social_blade_ig_chnls_daily_data.mt_id')
                                                ->select('tbl_social_blade_ig_chnls_daily_data.*')
                                                //->where('tbl_social_blade_master.cv_id', '=', base64_decode($cvid))
                                                ->where('tbl_social_blade_master.cv_name', 'like', $cv_data->cv_name."%")
                                                ->where('tbl_social_blade_master.social_media_id', '=', 2)
                                                ->whereBetween('tbl_social_blade_ig_chnls_daily_data.date', [$start_date, $end_date])
                                                ->orderBy('tbl_social_blade_ig_chnls_daily_data.date','asc')->first();
                        $social_stats_ig_s1_data = [];
                        $social_stats_ig_os_data = [];
                        if($get_social_stats_ig_desc_data !='' && $get_social_stats_ig_asc_data!='')
                        {
                            $social_stats_ig_s1_data['type'] = 'ig';
                            $social_stats_ig_s1_data['Flwrs'] = $get_social_stats_ig_desc_data->followers;
                            $social_stats_ig_s1_data['Media'] = $get_social_stats_ig_desc_data->media;
                            $social_stats_ig_s1_data['Avg.Likes'] = $get_social_stats_ig_desc_data->avg_likes;
                            $social_stats_ig_s1_data['Avg.Comnts'] = $get_social_stats_ig_desc_data->avg_comments;
                            $social_stats_ig_os_data['type'] = 'ig';
                            $social_stats_ig_os_data['Followers'] = $get_social_stats_ig_desc_data->followers."_".$get_social_stats_ig_asc_data->followers;
                            $social_stats_ig_os_data['Media'] = $get_social_stats_ig_desc_data->media."_".$get_social_stats_ig_asc_data->media;
                        }

                        $get_social_stats_tt_desc_data = DB::table('tbl_social_blade_master')
                                                ->join('tbl_social_blade_tt_chnls_daily_data', 'tbl_social_blade_master.id', '=', 'tbl_social_blade_tt_chnls_daily_data.mt_id')
                                                ->select('tbl_social_blade_tt_chnls_daily_data.*')
                                                //->where('tbl_social_blade_master.cv_id', '=', base64_decode($cvid))
                                                ->where('tbl_social_blade_master.cv_name', 'like', $cv_data->cv_name."%")
                                                ->where('tbl_social_blade_master.social_media_id', '=', 3)
                                                ->whereBetween('tbl_social_blade_tt_chnls_daily_data.date', [$start_date, $end_date])
                                                ->orderBy('tbl_social_blade_tt_chnls_daily_data.date','desc')->first();
                        $get_social_stats_tt_asc_data = DB::table('tbl_social_blade_master')
                                                ->join('tbl_social_blade_tt_chnls_daily_data', 'tbl_social_blade_master.id', '=', 'tbl_social_blade_tt_chnls_daily_data.mt_id')
                                                ->select('tbl_social_blade_tt_chnls_daily_data.*')
                                                //->where('tbl_social_blade_master.cv_id', '=', base64_decode($cvid))
                                                ->where('tbl_social_blade_master.cv_name', 'like', $cv_data->cv_name."%")
                                                ->where('tbl_social_blade_master.social_media_id', '=', 3)
                                                ->whereBetween('tbl_social_blade_tt_chnls_daily_data.date', [$start_date, $end_date])
                                                ->orderBy('tbl_social_blade_tt_chnls_daily_data.date','asc')->first();
                        $social_stats_tt_s1_data = [];
                        $social_stats_tt_os_data = [];
                        if($get_social_stats_tt_desc_data !='' && $get_social_stats_tt_asc_data!='')
                        {
                            $social_stats_tt_s1_data['type'] = 'tt';
                            $social_stats_tt_s1_data['Followers'] = $get_social_stats_tt_desc_data->followers;
                            $social_stats_tt_s1_data['Likes'] = $get_social_stats_tt_desc_data->likes;
                            $social_stats_tt_s1_data['Media'] = $get_social_stats_tt_desc_data->uploads;
                            $social_stats_tt_os_data['type'] = 'tt';
                            $social_stats_tt_os_data['Followers'] = $get_social_stats_tt_desc_data->followers."_".$get_social_stats_tt_asc_data->followers;
                            $social_stats_tt_os_data['Likes'] = $get_social_stats_tt_desc_data->likes."_".$get_social_stats_tt_asc_data->likes;
                            $social_stats_tt_os_data['Media'] = $get_social_stats_tt_desc_data->uploads."_".$get_social_stats_tt_asc_data->uploads;
                        }

                        $get_social_stats_twt_desc_data = DB::table('tbl_social_blade_master')
                                                ->join('tbl_social_blade_twt_chnls_daily_data', 'tbl_social_blade_master.id', '=', 'tbl_social_blade_twt_chnls_daily_data.mt_id')
                                                ->select('tbl_social_blade_twt_chnls_daily_data.*')
                                                //->where('tbl_social_blade_master.cv_id', '=', base64_decode($cvid))
                                                ->where('tbl_social_blade_master.cv_name', 'like', $cv_data->cv_name."%")
                                                ->where('tbl_social_blade_master.social_media_id', '=', 4)
                                                ->whereBetween('tbl_social_blade_twt_chnls_daily_data.date', [$start_date, $end_date])
                                                ->orderBy('tbl_social_blade_twt_chnls_daily_data.date','desc')->first();
                        $get_social_stats_twt_asc_data = DB::table('tbl_social_blade_master')
                                                ->join('tbl_social_blade_twt_chnls_daily_data', 'tbl_social_blade_master.id', '=', 'tbl_social_blade_twt_chnls_daily_data.mt_id')
                                                ->select('tbl_social_blade_twt_chnls_daily_data.*')
                                                //->where('tbl_social_blade_master.cv_id', '=', base64_decode($cvid))
                                                ->where('tbl_social_blade_master.cv_name', 'like', $cv_data->cv_name."%")
                                                ->where('tbl_social_blade_master.social_media_id', '=', 4)
                                                ->whereBetween('tbl_social_blade_twt_chnls_daily_data.date', [$start_date, $end_date])
                                                ->orderBy('tbl_social_blade_twt_chnls_daily_data.date','asc')->first();
                        $social_stats_twt_s1_data = [];
                        $social_stats_twt_os_data = [];
                        if($get_social_stats_twt_desc_data !='' && $get_social_stats_twt_asc_data!='')
                        {
                            $social_stats_twt_s1_data['type'] = 'twt';
                            $social_stats_twt_s1_data['Followers'] = $get_social_stats_twt_desc_data->followers;
                            $social_stats_twt_s1_data['Tweets'] = $get_social_stats_twt_desc_data->tweets;
                            $social_stats_twt_s1_data['Favorites'] = $get_social_stats_twt_desc_data->favorites;
                            $social_stats_twt_os_data['type'] = 'twt';
                            $social_stats_twt_os_data['Followers'] = $get_social_stats_twt_desc_data->followers."_".$get_social_stats_twt_asc_data->followers;
                            $social_stats_twt_os_data['Tweets'] = $get_social_stats_twt_desc_data->tweets."_".$get_social_stats_twt_asc_data->tweets;
                            $social_stats_twt_os_data['Favorites'] = $get_social_stats_twt_desc_data->favorites."_".$get_social_stats_twt_asc_data->favorites;
                            }

                        if(!empty($social_stats_yt_s1_data))
                        {
                            array_push($social_media_stats_s1,$social_stats_yt_s1_data);
                        }
                        if(!empty($social_stats_ig_s1_data))
                        {
                            array_push($social_media_stats_s1,$social_stats_ig_s1_data);
                        }
                        if(!empty($social_stats_tt_s1_data))
                        {
                            array_push($social_media_stats_s1,$social_stats_tt_s1_data);
                        }
                        if(!empty($social_stats_twt_s1_data))
                        {
                            array_push($social_media_stats_s1,$social_stats_twt_s1_data);
                        }

                        if(!empty($social_stats_yt_os_data))
                        {
                            array_push($social_media_stats_os,$social_stats_yt_os_data);
                        }

                        if(!empty($social_stats_ig_os_data))
                        {
                            array_push($social_media_stats_os,$social_stats_ig_os_data);
                        }

                        if(!empty($social_stats_tt_os_data))
                        {
                            array_push($social_media_stats_os,$social_stats_tt_os_data);
                        }

                        if(!empty($social_stats_twt_os_data))
                        {
                            array_push($social_media_stats_os,$social_stats_twt_os_data);
                        } */

                        $chk_sb_entry = DB::table('tbl_social_blade_master')
                            ->select('id','social_media_id')
                            ->where('tbl_social_blade_master.cv_name', 'like', $cv_data->cv_name."%")
                            ->where('tbl_social_blade_master.data_fetched_from', '<=', $end_date)
                            ->groupBy('social_media_id')
                            ->where('tbl_social_blade_master.data_fetched_to', '>=', $start_date)->get();
                        $social_media_id_array = [];
                        $social_media_array = [];
                        foreach($chk_sb_entry as $sb_data)
                        {
                            $social_media_array[$sb_data->social_media_id] = $sb_data->id;
                            array_push($social_media_id_array, $sb_data->social_media_id);
                        }
                        if(count($chk_sb_entry)>0)
                        {
                            if(count($chk_sb_entry) == 4)
                            {
                                $get_social_stats_yt_desc_data = DB::table('tbl_social_blade_master')
                                                ->join('tbl_social_blade_yt_chnls_daily_data', 'tbl_social_blade_master.id', '=', 'tbl_social_blade_yt_chnls_daily_data.mt_id')
                                                ->select('tbl_social_blade_yt_chnls_daily_data.*')
                                                //->where('tbl_social_blade_master.cv_id', '=', base64_decode($cvid))
                                                //->where('tbl_social_blade_master.cv_name', 'like', $cv_data->cv_name."%")
                                                ->where('tbl_social_blade_master.id', '=', $social_media_array[1])
                                                ->where('tbl_social_blade_master.social_media_id', '=', 1)
                                                ->whereBetween('tbl_social_blade_yt_chnls_daily_data.date', [$start_date, $end_date])
                                                ->orderBy('tbl_social_blade_yt_chnls_daily_data.date','desc')->first();
                                $get_social_stats_yt_asc_data = DB::table('tbl_social_blade_master')
                                                        ->join('tbl_social_blade_yt_chnls_daily_data', 'tbl_social_blade_master.id', '=', 'tbl_social_blade_yt_chnls_daily_data.mt_id')
                                                        ->select('tbl_social_blade_yt_chnls_daily_data.*')
                                                        //->where('tbl_social_blade_master.cv_id', '=', base64_decode($cvid))
                                                        //->where('tbl_social_blade_master.cv_name', 'like', $cv_data->cv_name."%")
                                                        ->where('tbl_social_blade_master.id', '=', $social_media_array[1])
                                                        ->where('tbl_social_blade_master.social_media_id', '=', 1)
                                                        ->whereBetween('tbl_social_blade_yt_chnls_daily_data.date', [$start_date, $end_date])
                                                        ->orderBy('tbl_social_blade_yt_chnls_daily_data.date','asc')->first();
                                $social_stats_yt_s1_data = [];
                                $social_stats_yt_os_data = [];
                                //print_r($get_social_stats_yt_desc_data); echo "<br>"; print_r($get_social_stats_yt_asc_data); echo "<br>";
                                if($get_social_stats_yt_desc_data && $get_social_stats_yt_asc_data)
                                {
                                    //echo "in yt<br>";
                                    $social_stats_yt_s1_data['type'] = 'yt';
                                    $social_stats_yt_s1_data['Subscribers'] = $get_social_stats_yt_desc_data->subs;
                                    $social_stats_yt_s1_data['Views'] = $get_social_stats_yt_desc_data->views;
                                    $social_stats_yt_os_data['type'] = 'yt';
                                    $social_stats_yt_os_data['Subscribers'] = $get_social_stats_yt_desc_data->subs."_".$get_social_stats_yt_asc_data->subs;
                                    $social_stats_yt_os_data['Views'] = $get_social_stats_yt_desc_data->views."_".$get_social_stats_yt_asc_data->views;
                                }
                                else
                                {
                                    $social_stats_yt_s1_data = [];
                                    $social_stats_yt_os_data = [];
                                }
                                //echo "social_stats_yt_s1_data".count($social_stats_yt_s1_data)."<br>social_stats_yt_os_data".count($social_stats_yt_os_data)."<br>";
                                $get_social_stats_ig_desc_data = DB::table('tbl_social_blade_master')
                                                        ->join('tbl_social_blade_ig_chnls_daily_data', 'tbl_social_blade_master.id', '=', 'tbl_social_blade_ig_chnls_daily_data.mt_id')
                                                        ->select('tbl_social_blade_ig_chnls_daily_data.*')
                                                        //->where('tbl_social_blade_master.cv_id', '=', base64_decode($cvid))
                                                        //->where('tbl_social_blade_master.cv_name', 'like', $cv_data->cv_name."%")
                                                        ->where('tbl_social_blade_master.id', '=', $social_media_array[2])
                                                        ->where('tbl_social_blade_master.social_media_id', '=', 2)
                                                        ->whereBetween('tbl_social_blade_ig_chnls_daily_data.date', [$start_date, $end_date])
                                                        ->orderBy('tbl_social_blade_ig_chnls_daily_data.date','desc')->first();
                                $get_social_stats_ig_asc_data = DB::table('tbl_social_blade_master')
                                                        ->join('tbl_social_blade_ig_chnls_daily_data', 'tbl_social_blade_master.id', '=', 'tbl_social_blade_ig_chnls_daily_data.mt_id')
                                                        ->select('tbl_social_blade_ig_chnls_daily_data.*')
                                                        //->where('tbl_social_blade_master.cv_id', '=', base64_decode($cvid))
                                                        //->where('tbl_social_blade_master.cv_name', 'like', $cv_data->cv_name."%")
                                                        ->where('tbl_social_blade_master.id', '=', $social_media_array[2])
                                                        ->where('tbl_social_blade_master.social_media_id', '=', 2)
                                                        ->whereBetween('tbl_social_blade_ig_chnls_daily_data.date', [$start_date, $end_date])
                                                        ->orderBy('tbl_social_blade_ig_chnls_daily_data.date','asc')->first();
                                $social_stats_ig_s1_data = [];
                                $social_stats_ig_os_data = [];
                                //print_r($get_social_stats_ig_desc_data); echo "<br>"; print_r($get_social_stats_ig_asc_data); echo "<br>";
                                if($get_social_stats_ig_desc_data && $get_social_stats_ig_asc_data)
                                {
                                    //echo "in ig<br>";
                                    $social_stats_ig_s1_data['type'] = 'ig';
                                    $social_stats_ig_s1_data['Flwrs'] = $get_social_stats_ig_desc_data->followers;
                                    $social_stats_ig_s1_data['Media'] = $get_social_stats_ig_desc_data->media;
                                    $social_stats_ig_s1_data['Avg.Likes'] = $get_social_stats_ig_desc_data->avg_likes;
                                    $social_stats_ig_s1_data['Avg.Comnts'] = $get_social_stats_ig_desc_data->avg_comments;
                                    $social_stats_ig_os_data['type'] = 'ig';
                                    $social_stats_ig_os_data['Followers'] = $get_social_stats_ig_desc_data->followers."_".$get_social_stats_ig_asc_data->followers;
                                    $social_stats_ig_os_data['Media'] = $get_social_stats_ig_desc_data->media."_".$get_social_stats_ig_asc_data->media;
                                }
                                else
                                {
                                    $social_stats_ig_s1_data = [];
                                    $social_stats_ig_os_data = [];
                                }
                                //echo "social_stats_ig_s1_data".count($social_stats_ig_s1_data)."<br>social_stats_ig_os_data".count($social_stats_ig_os_data)."<br>";
                                $get_social_stats_tt_desc_data = DB::table('tbl_social_blade_master')
                                                        ->join('tbl_social_blade_tt_chnls_daily_data', 'tbl_social_blade_master.id', '=', 'tbl_social_blade_tt_chnls_daily_data.mt_id')
                                                        ->select('tbl_social_blade_tt_chnls_daily_data.*')
                                                        //->where('tbl_social_blade_master.cv_id', '=', base64_decode($cvid))
                                                        //->where('tbl_social_blade_master.cv_name', 'like', $cv_data->cv_name."%")
                                                        ->where('tbl_social_blade_master.id', '=', $social_media_array[3])
                                                        ->where('tbl_social_blade_master.social_media_id', '=', 3)
                                                        ->whereBetween('tbl_social_blade_tt_chnls_daily_data.date', [$start_date, $end_date])
                                                        ->orderBy('tbl_social_blade_tt_chnls_daily_data.date','desc')->first();
                                $get_social_stats_tt_asc_data = DB::table('tbl_social_blade_master')
                                                        ->join('tbl_social_blade_tt_chnls_daily_data', 'tbl_social_blade_master.id', '=', 'tbl_social_blade_tt_chnls_daily_data.mt_id')
                                                        ->select('tbl_social_blade_tt_chnls_daily_data.*')
                                                        //->where('tbl_social_blade_master.cv_id', '=', base64_decode($cvid))
                                                        //->where('tbl_social_blade_master.cv_name', 'like', $cv_data->cv_name."%")
                                                        ->where('tbl_social_blade_master.id', '=', $social_media_array[3])
                                                        ->where('tbl_social_blade_master.social_media_id', '=', 3)
                                                        ->whereBetween('tbl_social_blade_tt_chnls_daily_data.date', [$start_date, $end_date])
                                                        ->orderBy('tbl_social_blade_tt_chnls_daily_data.date','asc')->first();
                                $social_stats_tt_s1_data = [];
                                $social_stats_tt_os_data = [];
                                //print_r($get_social_stats_tt_desc_data); echo "<br>"; print_r($get_social_stats_tt_asc_data); echo "<br>";
                                if($get_social_stats_tt_desc_data && $get_social_stats_tt_asc_data)
                                {
                                    //echo "in tt<br>";
                                    $social_stats_tt_s1_data['type'] = 'tt';
                                    $social_stats_tt_s1_data['Followers'] = $get_social_stats_tt_desc_data->followers;
                                    $social_stats_tt_s1_data['Likes'] = $get_social_stats_tt_desc_data->likes;
                                    $social_stats_tt_s1_data['Media'] = $get_social_stats_tt_desc_data->uploads;
                                    $social_stats_tt_os_data['type'] = 'tt';
                                    $social_stats_tt_os_data['Followers'] = $get_social_stats_tt_desc_data->followers."_".$get_social_stats_tt_asc_data->followers;
                                    $social_stats_tt_os_data['Likes'] = $get_social_stats_tt_desc_data->likes."_".$get_social_stats_tt_asc_data->likes;
                                    $social_stats_tt_os_data['Media'] = $get_social_stats_tt_desc_data->uploads."_".$get_social_stats_tt_asc_data->uploads;
                                }
                                else
                                {
                                    $social_stats_tt_s1_data = [];
                                    $social_stats_tt_os_data = [];
                                }
                                //echo "social_stats_tt_s1_data".count($social_stats_tt_s1_data)."<br>social_stats_tt_os_data".count($social_stats_tt_os_data)."<br>";
                                $get_social_stats_twt_desc_data = DB::table('tbl_social_blade_master')
                                                        ->join('tbl_social_blade_twt_chnls_daily_data', 'tbl_social_blade_master.id', '=', 'tbl_social_blade_twt_chnls_daily_data.mt_id')
                                                        ->select('tbl_social_blade_twt_chnls_daily_data.*')
                                                        //->where('tbl_social_blade_master.cv_id', '=', base64_decode($cvid))
                                                        //->where('tbl_social_blade_master.cv_name', 'like', $cv_data->cv_name."%")
                                                        ->where('tbl_social_blade_master.id', '=', $social_media_array[4])
                                                        ->where('tbl_social_blade_master.social_media_id', '=', 4)
                                                        ->whereBetween('tbl_social_blade_twt_chnls_daily_data.date', [$start_date, $end_date])
                                                        ->orderBy('tbl_social_blade_twt_chnls_daily_data.date','desc')->first();
                                $get_social_stats_twt_asc_data = DB::table('tbl_social_blade_master')
                                                        ->join('tbl_social_blade_twt_chnls_daily_data', 'tbl_social_blade_master.id', '=', 'tbl_social_blade_twt_chnls_daily_data.mt_id')
                                                        ->select('tbl_social_blade_twt_chnls_daily_data.*')
                                                        //->where('tbl_social_blade_master.cv_id', '=', base64_decode($cvid))
                                                        //->where('tbl_social_blade_master.cv_name', 'like', $cv_data->cv_name."%")
                                                        ->where('tbl_social_blade_master.id', '=', $social_media_array[4])
                                                        ->where('tbl_social_blade_master.social_media_id', '=', 4)
                                                        ->whereBetween('tbl_social_blade_twt_chnls_daily_data.date', [$start_date, $end_date])
                                                        ->orderBy('tbl_social_blade_twt_chnls_daily_data.date','asc')->first();
                                $social_stats_twt_s1_data = [];
                                $social_stats_twt_os_data = [];
                                //print_r($get_social_stats_twt_desc_data); echo "<br>"; print_r($get_social_stats_twt_asc_data); echo "<br>";
                                if($get_social_stats_twt_desc_data && $get_social_stats_twt_asc_data)
                                {
                                    //echo "in twt<br>";
                                    $social_stats_twt_s1_data['type'] = 'twt';
                                    $social_stats_twt_s1_data['Followers'] = $get_social_stats_twt_desc_data->followers;
                                    $social_stats_twt_s1_data['Tweets'] = $get_social_stats_twt_desc_data->tweets;
                                    $social_stats_twt_s1_data['Favorites'] = $get_social_stats_twt_desc_data->favorites;
                                    $social_stats_twt_os_data['type'] = 'twt';
                                    $social_stats_twt_os_data['Followers'] = $get_social_stats_twt_desc_data->followers."_".$get_social_stats_twt_asc_data->followers;
                                    $social_stats_twt_os_data['Tweets'] = $get_social_stats_twt_desc_data->tweets."_".$get_social_stats_twt_asc_data->tweets;
                                    $social_stats_twt_os_data['Favorites'] = $get_social_stats_twt_desc_data->favorites."_".$get_social_stats_twt_asc_data->favorites;
                                }
                                else
                                {
                                    $social_stats_twt_s1_data = [];
                                    $social_stats_twt_os_data = [];
                                }
                                //echo "social_stats_twt_s1_data".count($social_stats_twt_s1_data)."<br>social_stats_twt_os_data".count($social_stats_twt_os_data)."<br>";
                            }
                            else
                            {
                                if(in_array('1',$social_media_id_array))
                                {
                                    $get_social_stats_yt_desc_data = DB::table('tbl_social_blade_master')
                                                ->join('tbl_social_blade_yt_chnls_daily_data', 'tbl_social_blade_master.id', '=', 'tbl_social_blade_yt_chnls_daily_data.mt_id')
                                                ->select('tbl_social_blade_yt_chnls_daily_data.*')
                                                //->where('tbl_social_blade_master.cv_id', '=', base64_decode($cvid))
                                                //->where('tbl_social_blade_master.cv_name', 'like', $cv_data->cv_name."%")
                                                ->where('tbl_social_blade_master.id', '=', $social_media_array[1])
                                                ->where('tbl_social_blade_master.social_media_id', '=', 1)
                                                ->whereBetween('tbl_social_blade_yt_chnls_daily_data.date', [$start_date, $end_date])
                                                ->orderBy('tbl_social_blade_yt_chnls_daily_data.date','desc')->first();
                                    $get_social_stats_yt_asc_data = DB::table('tbl_social_blade_master')
                                                            ->join('tbl_social_blade_yt_chnls_daily_data', 'tbl_social_blade_master.id', '=', 'tbl_social_blade_yt_chnls_daily_data.mt_id')
                                                            ->select('tbl_social_blade_yt_chnls_daily_data.*')
                                                            //->where('tbl_social_blade_master.cv_id', '=', base64_decode($cvid))
                                                            //->where('tbl_social_blade_master.cv_name', 'like', $cv_data->cv_name."%")
                                                            ->where('tbl_social_blade_master.id', '=', $social_media_array[1])
                                                            ->where('tbl_social_blade_master.social_media_id', '=', 1)
                                                            ->whereBetween('tbl_social_blade_yt_chnls_daily_data.date', [$start_date, $end_date])
                                                            ->orderBy('tbl_social_blade_yt_chnls_daily_data.date','asc')->first();
                                    $social_stats_yt_s1_data = [];
                                    $social_stats_yt_os_data = [];
                                    //print_r($get_social_stats_yt_desc_data); echo "<br>"; print_r($get_social_stats_yt_asc_data); echo "<br>";
                                    if($get_social_stats_yt_desc_data && $get_social_stats_yt_asc_data)
                                    {
                                        //echo "in yt<br>";
                                        $social_stats_yt_s1_data['type'] = 'yt';
                                        $social_stats_yt_s1_data['Subscribers'] = $get_social_stats_yt_desc_data->subs;
                                        $social_stats_yt_s1_data['Views'] = $get_social_stats_yt_desc_data->views;
                                        $social_stats_yt_os_data['type'] = 'yt';
                                        $social_stats_yt_os_data['Subscribers'] = $get_social_stats_yt_desc_data->subs."_".$get_social_stats_yt_asc_data->subs;
                                        $social_stats_yt_os_data['Views'] = $get_social_stats_yt_desc_data->views."_".$get_social_stats_yt_asc_data->views;
                                    }
                                    else
                                    {
                                        $social_stats_yt_s1_data = [];
                                        $social_stats_yt_os_data = [];
                                    }
                                }
                                else
                                {
                                    $social_stats_yt_s1_data = [];
                                    $social_stats_yt_os_data = [];
                                }

                                if(in_array('2',$social_media_id_array))
                                {
                                    $get_social_stats_ig_desc_data = DB::table('tbl_social_blade_master')
                                                        ->join('tbl_social_blade_ig_chnls_daily_data', 'tbl_social_blade_master.id', '=', 'tbl_social_blade_ig_chnls_daily_data.mt_id')
                                                        ->select('tbl_social_blade_ig_chnls_daily_data.*')
                                                        //->where('tbl_social_blade_master.cv_id', '=', base64_decode($cvid))
                                                        //->where('tbl_social_blade_master.cv_name', 'like', $cv_data->cv_name."%")
                                                        ->where('tbl_social_blade_master.id', '=', $social_media_array[2])
                                                        ->where('tbl_social_blade_master.social_media_id', '=', 2)
                                                        ->whereBetween('tbl_social_blade_ig_chnls_daily_data.date', [$start_date, $end_date])
                                                        ->orderBy('tbl_social_blade_ig_chnls_daily_data.date','desc')->first();
                                    $get_social_stats_ig_asc_data = DB::table('tbl_social_blade_master')
                                                            ->join('tbl_social_blade_ig_chnls_daily_data', 'tbl_social_blade_master.id', '=', 'tbl_social_blade_ig_chnls_daily_data.mt_id')
                                                            ->select('tbl_social_blade_ig_chnls_daily_data.*')
                                                            //->where('tbl_social_blade_master.cv_id', '=', base64_decode($cvid))
                                                            //->where('tbl_social_blade_master.cv_name', 'like', $cv_data->cv_name."%")
                                                            ->where('tbl_social_blade_master.id', '=', $social_media_array[2])
                                                            ->where('tbl_social_blade_master.social_media_id', '=', 2)
                                                            ->whereBetween('tbl_social_blade_ig_chnls_daily_data.date', [$start_date, $end_date])
                                                            ->orderBy('tbl_social_blade_ig_chnls_daily_data.date','asc')->first();
                                    $social_stats_ig_s1_data = [];
                                    $social_stats_ig_os_data = [];
                                    //print_r($get_social_stats_ig_desc_data); echo "<br>"; print_r($get_social_stats_ig_asc_data); echo "<br>";
                                    if($get_social_stats_ig_desc_data && $get_social_stats_ig_asc_data)
                                    {
                                        //echo "in ig<br>";
                                        $social_stats_ig_s1_data['type'] = 'ig';
                                        $social_stats_ig_s1_data['Flwrs'] = $get_social_stats_ig_desc_data->followers;
                                        $social_stats_ig_s1_data['Media'] = $get_social_stats_ig_desc_data->media;
                                        $social_stats_ig_s1_data['Avg.Likes'] = $get_social_stats_ig_desc_data->avg_likes;
                                        $social_stats_ig_s1_data['Avg.Comnts'] = $get_social_stats_ig_desc_data->avg_comments;
                                        $social_stats_ig_os_data['type'] = 'ig';
                                        $social_stats_ig_os_data['Followers'] = $get_social_stats_ig_desc_data->followers."_".$get_social_stats_ig_asc_data->followers;
                                        $social_stats_ig_os_data['Media'] = $get_social_stats_ig_desc_data->media."_".$get_social_stats_ig_asc_data->media;
                                    }
                                    else
                                    {
                                        $social_stats_ig_s1_data = [];
                                        $social_stats_ig_os_data = [];
                                    }
                                }
                                else
                                {
                                    $social_stats_ig_s1_data = [];
                                    $social_stats_ig_os_data = [];
                                }

                                if(in_array('3',$social_media_id_array))
                                {
                                    $get_social_stats_tt_desc_data = DB::table('tbl_social_blade_master')
                                                        ->join('tbl_social_blade_tt_chnls_daily_data', 'tbl_social_blade_master.id', '=', 'tbl_social_blade_tt_chnls_daily_data.mt_id')
                                                        ->select('tbl_social_blade_tt_chnls_daily_data.*')
                                                        //->where('tbl_social_blade_master.cv_id', '=', base64_decode($cvid))
                                                        //->where('tbl_social_blade_master.cv_name', 'like', $cv_data->cv_name."%")
                                                        ->where('tbl_social_blade_master.id', '=', $social_media_array[3])
                                                        ->where('tbl_social_blade_master.social_media_id', '=', 3)
                                                        ->whereBetween('tbl_social_blade_tt_chnls_daily_data.date', [$start_date, $end_date])
                                                        ->orderBy('tbl_social_blade_tt_chnls_daily_data.date','desc')->first();
                                    $get_social_stats_tt_asc_data = DB::table('tbl_social_blade_master')
                                                            ->join('tbl_social_blade_tt_chnls_daily_data', 'tbl_social_blade_master.id', '=', 'tbl_social_blade_tt_chnls_daily_data.mt_id')
                                                            ->select('tbl_social_blade_tt_chnls_daily_data.*')
                                                            //->where('tbl_social_blade_master.cv_id', '=', base64_decode($cvid))
                                                            //->where('tbl_social_blade_master.cv_name', 'like', $cv_data->cv_name."%")
                                                            ->where('tbl_social_blade_master.id', '=', $social_media_array[3])
                                                            ->where('tbl_social_blade_master.social_media_id', '=', 3)
                                                            ->whereBetween('tbl_social_blade_tt_chnls_daily_data.date', [$start_date, $end_date])
                                                            ->orderBy('tbl_social_blade_tt_chnls_daily_data.date','asc')->first();
                                    $social_stats_tt_s1_data = [];
                                    $social_stats_tt_os_data = [];
                                    //print_r($get_social_stats_tt_desc_data); echo "<br>"; print_r($get_social_stats_tt_asc_data); echo "<br>";
                                    if($get_social_stats_tt_desc_data && $get_social_stats_tt_asc_data)
                                    {
                                        //echo "in tt<br>";
                                        $social_stats_tt_s1_data['type'] = 'tt';
                                        $social_stats_tt_s1_data['Followers'] = $get_social_stats_tt_desc_data->followers;
                                        $social_stats_tt_s1_data['Likes'] = $get_social_stats_tt_desc_data->likes;
                                        $social_stats_tt_s1_data['Media'] = $get_social_stats_tt_desc_data->uploads;
                                        $social_stats_tt_os_data['type'] = 'tt';
                                        $social_stats_tt_os_data['Followers'] = $get_social_stats_tt_desc_data->followers."_".$get_social_stats_tt_asc_data->followers;
                                        $social_stats_tt_os_data['Likes'] = $get_social_stats_tt_desc_data->likes."_".$get_social_stats_tt_asc_data->likes;
                                        $social_stats_tt_os_data['Media'] = $get_social_stats_tt_desc_data->uploads."_".$get_social_stats_tt_asc_data->uploads;
                                    }
                                    else
                                    {
                                        $social_stats_tt_s1_data = [];
                                        $social_stats_tt_os_data = [];
                                    }
                                }
                                else
                                {
                                    $social_stats_tt_s1_data = [];
                                    $social_stats_tt_os_data = [];
                                }

                                if(in_array('4',$social_media_id_array))
                                {
                                    $get_social_stats_twt_desc_data = DB::table('tbl_social_blade_master')
                                                        ->join('tbl_social_blade_twt_chnls_daily_data', 'tbl_social_blade_master.id', '=', 'tbl_social_blade_twt_chnls_daily_data.mt_id')
                                                        ->select('tbl_social_blade_twt_chnls_daily_data.*')
                                                        //->where('tbl_social_blade_master.cv_id', '=', base64_decode($cvid))
                                                        //->where('tbl_social_blade_master.cv_name', 'like', $cv_data->cv_name."%")
                                                        ->where('tbl_social_blade_master.id', '=', $social_media_array[4])
                                                        ->where('tbl_social_blade_master.social_media_id', '=', 4)
                                                        ->whereBetween('tbl_social_blade_twt_chnls_daily_data.date', [$start_date, $end_date])
                                                        ->orderBy('tbl_social_blade_twt_chnls_daily_data.date','desc')->first();
                                    $get_social_stats_twt_asc_data = DB::table('tbl_social_blade_master')
                                                            ->join('tbl_social_blade_twt_chnls_daily_data', 'tbl_social_blade_master.id', '=', 'tbl_social_blade_twt_chnls_daily_data.mt_id')
                                                            ->select('tbl_social_blade_twt_chnls_daily_data.*')
                                                            //->where('tbl_social_blade_master.cv_id', '=', base64_decode($cvid))
                                                            //->where('tbl_social_blade_master.cv_name', 'like', $cv_data->cv_name."%")
                                                            ->where('tbl_social_blade_master.id', '=', $social_media_array[4])
                                                            ->where('tbl_social_blade_master.social_media_id', '=', 4)
                                                            ->whereBetween('tbl_social_blade_twt_chnls_daily_data.date', [$start_date, $end_date])
                                                            ->orderBy('tbl_social_blade_twt_chnls_daily_data.date','asc')->first();
                                    $social_stats_twt_s1_data = [];
                                    $social_stats_twt_os_data = [];
                                    //print_r($get_social_stats_twt_desc_data); echo "<br>"; print_r($get_social_stats_twt_asc_data); echo "<br>";
                                    if($get_social_stats_twt_desc_data && $get_social_stats_twt_asc_data)
                                    {
                                        //echo "in twt<br>";
                                        $social_stats_twt_s1_data['type'] = 'twt';
                                        $social_stats_twt_s1_data['Followers'] = $get_social_stats_twt_desc_data->followers;
                                        $social_stats_twt_s1_data['Tweets'] = $get_social_stats_twt_desc_data->tweets;
                                        $social_stats_twt_s1_data['Favorites'] = $get_social_stats_twt_desc_data->favorites;
                                        $social_stats_twt_os_data['type'] = 'twt';
                                        $social_stats_twt_os_data['Followers'] = $get_social_stats_twt_desc_data->followers."_".$get_social_stats_twt_asc_data->followers;
                                        $social_stats_twt_os_data['Tweets'] = $get_social_stats_twt_desc_data->tweets."_".$get_social_stats_twt_asc_data->tweets;
                                        $social_stats_twt_os_data['Favorites'] = $get_social_stats_twt_desc_data->favorites."_".$get_social_stats_twt_asc_data->favorites;
                                    }
                                    else
                                    {
                                        $social_stats_twt_s1_data = [];
                                        $social_stats_twt_os_data = [];
                                    }
                                }
                                else
                                {
                                    $social_stats_twt_s1_data = [];
                                    $social_stats_twt_os_data = [];
                                }
                            }

                            if(count($social_stats_yt_s1_data)>0)
                            {
                                array_push($social_media_stats_s1,$social_stats_yt_s1_data);
                            }
                            if(count($social_stats_yt_os_data)>0)
                            {
                                array_push($social_media_stats_os,$social_stats_yt_os_data);
                            }

                            if(count($social_stats_ig_s1_data)>0)
                            {
                                array_push($social_media_stats_s1,$social_stats_ig_s1_data);
                            }
                            if(count($social_stats_ig_os_data)>0)
                            {
                                array_push($social_media_stats_os,$social_stats_ig_os_data);
                            }

                            if(count($social_stats_tt_s1_data)>0)
                            {
                                array_push($social_media_stats_s1,$social_stats_tt_s1_data);
                            }
                            if(count($social_stats_tt_os_data)>0)
                            {
                                array_push($social_media_stats_os,$social_stats_tt_os_data);
                            }

                            if(count($social_stats_twt_s1_data)>0)
                            {
                                array_push($social_media_stats_s1,$social_stats_twt_s1_data);
                            }
                            if(count($social_stats_twt_os_data)>0)
                            {
                                array_push($social_media_stats_os,$social_stats_twt_os_data);
                            }

                        }
                        else
                        {
                            $social_media_stats_s1 = [];
                            $social_media_stats_os = [];
                        }

                        $tbl_experience_data = DB::table('tbl_experience')
                        ->where('is_active',0)
                        ->orderBy('display_order','ASC')
                        ->get();


                        // sonic logo main mood data============================
                        $cv_block_6_data_with_cs_status_data = DB::table('tbl_cv_block_6_data')
                                                ->where('cv_id', '=', $cv_data->cv_id)
                                                ->where('is_active', '=', 0)
                                                ->where('cs_status', '=', 1)
                                                ->whereNotNull('assets_id')
                                                ->whereNotNull('b6_name')
                                                ->orderBy('b6_id','asc')
                                                ->get();

                        if(count($cv_block_6_data_with_cs_status_data) > 0){
                            $sonicLogoMainMoodGraphData = [];
                            foreach($cv_block_6_data_with_cs_status_data as $cv_block_6_data_with_cs_status){
                                $assets_id = $cv_block_6_data_with_cs_status->assets_id;
                                $b6_id = $cv_block_6_data_with_cs_status->b6_id;
                                $sonic_logo_main_mood_tag_data = DB::table('tbl_assets')
                                            ->join('tbl_asset_processed_sonic_logo_main_mood_tag_data','tbl_assets.cs_asset_id','=','tbl_asset_processed_sonic_logo_main_mood_tag_data.asset_id')
                                            ->join('tbl_sonic_logo_main_mood_tag_master','tbl_asset_processed_sonic_logo_main_mood_tag_data.sonic_logo_main_mood_tag_id','=','tbl_sonic_logo_main_mood_tag_master.tag_id')
                                            ->select('tbl_asset_processed_sonic_logo_main_mood_tag_data.*','tbl_sonic_logo_main_mood_tag_master.tag_name')
                                            ->where('id', '=', $assets_id)
                                            ->where('cs_d_status', '=', 1)
                                            ->where('cs_response_status', '=', 2)
                                            ->where('tbl_assets.is_active', '=', 0)
                                            ->where('tbl_asset_processed_sonic_logo_main_mood_tag_data.is_active', '=', 0)
                                            ->where('tbl_sonic_logo_main_mood_tag_master.is_active', '=', 0)
                                            ->get();
                                if(count($sonic_logo_main_mood_tag_data) > 0){
                                    foreach($sonic_logo_main_mood_tag_data as $value){
                                        $sonicLogoMainMoodGraphData[$b6_id][$value->tag_name] = $value->sonic_logo_main_mood_tag_value;
                                    }
                                }

                            }
                        }else{
                            $sonicLogoMainMoodGraphData = [];
                        }

                        // $cv_id_datas = DB::table('tbl_cvs')->where('cv_id','=',$cv_data->cv_id)->where('status', '=', 1)->where('is_active', '=', 0)->first();
                        $cv_id_dates_of_year_and_inid = DB::table('tbl_cvs')
                                            ->where('is_active', '=', 0)
                                            ->where('status', '=', 1)
                                            ->where('cv_year', '=', $cv_data->cv_year)
                                            ->where('industry_id', '=', $cv_data->industry_id)
                                            ->get();

                        $cv_id_array_of_year_and_inid = [];
                        foreach($cv_id_dates_of_year_and_inid as $value){
                            array_push($cv_id_array_of_year_and_inid,$value->cv_id);
                        }

                        $cv_logo_dates_of_year_and_inid = DB::table('tbl_cv_block_6_data')
                                                ->distinct()
                                                ->where('is_active', '=', 0)
                                                ->whereIn('cv_id', $cv_id_array_of_year_and_inid)
                                                ->whereNotNull('b6_name')
                                                ->get();

                        $cv_logo_array_of_year_and_inid = [];
                        if(count($cv_logo_dates_of_year_and_inid) > 0){

                            foreach($cv_logo_dates_of_year_and_inid as $value){
                                array_push($cv_logo_array_of_year_and_inid,$value->cv_id);
                            }

                            $countCvsAsPerTheIndustry = count($cv_id_array_of_year_and_inid);
                            $countSonicLogoExistAsPerTheIndustry = count($cv_logo_array_of_year_and_inid);
                            $countSonicLogoAsPerTheCvIndustryData = [$countSonicLogoExistAsPerTheIndustry,($countCvsAsPerTheIndustry-$countSonicLogoExistAsPerTheIndustry)];
                        }else{
                            $countCvsAsPerTheIndustry = count($cv_id_array_of_year_and_inid);
                            $countSonicLogoExistAsPerTheIndustry = count($cv_logo_array_of_year_and_inid);
                            $countSonicLogoAsPerTheCvIndustryData = [$countSonicLogoExistAsPerTheIndustry,($countCvsAsPerTheIndustry-$countSonicLogoExistAsPerTheIndustry)];
                        }


                        // Get Mood and Genre Graph data
                        /* $process_type_array = ['youtube', 'instagram', 'tiktok', 'twitter'];
                        $avg_asset_id_arr = [];
                        $yt_asset_id_arr = [];
                        $ig_asset_id_arr = [];
                        $tt_asset_id_arr = [];
                        $twt_asset_id_arr = [];
                        foreach($process_type_array as $process_type)
                        {
                            // echo "process_type=>".$process_type."<br><br>";
                            $get_asset_data = DB::table('tbl_cvs')
                                                ->join('tbl_social_spyder_graph_meta_data','tbl_social_spyder_graph_meta_data.cv_id','=','tbl_cvs.cv_id')
                                                ->join('tbl_assets','tbl_assets.id','=','tbl_social_spyder_graph_meta_data.asset_id')
                                                ->select('tbl_assets.*')
                                                ->where('tbl_cvs.cv_id', '=', $cv_data->cv_id)
                                                // ->where('tbl_cvs.cv_id', '=', 1111)
                                                ->whereNotNull('tbl_social_spyder_graph_meta_data.asset_id')
                                                ->where('tbl_assets.is_active', '=', 0)
                                                ->where('tbl_assets.cs_response_status', '=', 2)
                                                ->where('tbl_social_spyder_graph_meta_data.process_type', '=', $process_type)
                                                ->get();

                            foreach($get_asset_data as $asset_data)
                            {
                                // echo "asset_data=>".$asset_data->id."|".$asset_data->cs_asset_id."<br>";
                                array_push($avg_asset_id_arr, $asset_data->cs_asset_id);
                                if($process_type == 'youtube')
                                    array_push($yt_asset_id_arr, $asset_data->cs_asset_id);
                                if($process_type == 'instagram')
                                    array_push($ig_asset_id_arr, $asset_data->cs_asset_id);
                                if($process_type == 'tiktok')
                                    array_push($tt_asset_id_arr, $asset_data->cs_asset_id);
                                if($process_type == 'twitter')
                                    array_push($twt_asset_id_arr, $asset_data->cs_asset_id);
                            }
                        }
                        print_r($yt_asset_id_arr);
                        echo "<br>***************************<br>";
                        print_r($ig_asset_id_arr);
                        echo "<br>***************************<br>";
                        print_r($tt_asset_id_arr);
                        echo "<br>***************************<br>";
                        print_r($twt_asset_id_arr);
                        echo "<br>***************************<br>";
                        print_r($avg_asset_id_arr);
                        echo "<br>--------------------------------------------------------------<br><br>"; */


                        $yt_mood_data = DB::table('tbl_social_media_yt_mood_graph_data')
                        ->where('cv_id', '=', $cv_data->cv_id)
                        ->where('is_active', '=', 0)
                        ->get();
                        // print_r($yt_mood_data);
                        // echo "<br>#########################################################<br>";
                        $yt_genre_data = DB::table('tbl_social_media_yt_genre_graph_data')
                        ->where('cv_id', '=', $cv_data->cv_id)
                        ->where('is_active', '=', 0)
                        ->get();
                        // print_r($yt_genre_data);
                        // echo "<br>||||||||||||||||||||||||||||||||||||||||||||||||||||||||||<br><br>";

                        $ig_mood_data = DB::table('tbl_social_media_ig_mood_graph_data')
                        ->where('cv_id', '=', $cv_data->cv_id)
                        ->where('is_active', '=', 0)
                        ->get();
                        // print_r($ig_mood_data);
                        // echo "<br>#########################################################<br>";
                        $ig_genre_data = DB::table('tbl_social_media_ig_genre_graph_data')
                        ->where('cv_id', '=', $cv_data->cv_id)
                        ->where('is_active', '=', 0)
                        ->get();
                        // print_r($ig_genre_data);
                        // echo "<br>||||||||||||||||||||||||||||||||||||||||||||||||||||||||||<br><br>";

                        $tt_mood_data = DB::table('tbl_social_media_tt_mood_graph_data')
                        ->where('cv_id', '=', $cv_data->cv_id)
                        ->where('is_active', '=', 0)
                        ->get();
                        // print_r($tt_mood_data);
                        // echo "<br>#########################################################<br>";
                        $tt_genre_data = DB::table('tbl_social_media_tt_genre_graph_data')
                        ->where('cv_id', '=', $cv_data->cv_id)
                        ->where('is_active', '=', 0)
                        ->get();
                        // print_r($tt_genre_data);
                        // echo "<br>||||||||||||||||||||||||||||||||||||||||||||||||||||||||||<br><br>";

                        $twt_mood_data = DB::table('tbl_social_media_twt_mood_graph_data')
                        ->where('cv_id', '=', $cv_data->cv_id)
                        ->where('is_active', '=', 0)
                        ->get();
                        // print_r($twt_mood_data);
                        // echo "<br>#########################################################<br>";
                        $twt_genre_data = DB::table('tbl_social_media_twt_genre_graph_data')
                        ->where('cv_id', '=', $cv_data->cv_id)
                        ->where('is_active', '=', 0)
                        ->get();
                        // print_r($twt_genre_data);
                        // echo "<br>||||||||||||||||||||||||||||||||||||||||||||||||||||||||||<br><br>";

                        $avg_mood_data = DB::table('tbl_social_media_aggr_mood_graph_data')
                        ->where('cv_id', '=', $cv_data->cv_id)
                        ->where('is_active', '=', 0)
                        ->get();
                        // print_r($avg_mood_data);
                        // echo "<br>#########################################################<br>";
                        $avg_genre_data = DB::table('tbl_social_media_aggr_genre_graph_data')
                        ->where('cv_id', '=', $cv_data->cv_id)
                        ->where('is_active', '=', 0)
                        ->get();
                        // print_r($avg_genre_data);
                        // echo "<br>||||||||||||||||||||||||||||||||||||||||||||||||||||||||||<br><br>";

                        $cv_genre_aggr_graph_values_arr = [];
                        $cv_genre_aggr_graph_values_arr1 = [];
                        foreach ($avg_genre_data as $val) {
                            $cv_genre_aggr_graph_values_arr[$val->lbl_name] = $val->lbl_value;
                            $cv_genre_aggr_graph_values_arr1[$val->lbl_name] = $val->lbl_value;

                        }

                        rsort($cv_genre_aggr_graph_values_arr);
                        $top3 = array_slice($cv_genre_aggr_graph_values_arr, 0, 3);
                        $top_3_genre = array();
                        foreach ($top3 as $key => $val) {
                            $key = array_search ($val, $cv_genre_aggr_graph_values_arr1);
                            unset($cv_genre_aggr_graph_values_arr1[$key]);
                            $top_3_genre[$key] = $val;
                        }
                        if(count($top_3_genre)==0)
                        {
                            $top_3_genre = '';
                        }

                        $cv_block_16_mood_graph_data = $yt_mood_data;
                        $cv_block_16_genre_graph_data = $yt_genre_data;
                        $cv_block_17_mood_graph_data = $ig_mood_data;
                        $cv_block_17_genre_graph_data = $ig_genre_data;
                        $cv_block_18_mood_graph_data = $tt_mood_data;
                        $cv_block_18_genre_graph_data = $tt_genre_data;
                        $cv_block_19_mood_graph_data = $twt_mood_data;
                        $cv_block_19_genre_graph_data = $twt_genre_data;
                        $cv_mood_aggr_graph_data = $avg_mood_data;
                        $cv_genre_aggr_graph_data = $avg_genre_data;

                        //======== Social Media Exist Or Not As Per The Cv_id =======================
                        $social_media_data_exist_array = [];

                        $tbl_cv_block_16_data = DB::table('tbl_cv_block_16_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->get();
                        $tbl_cv_block_17_data = DB::table('tbl_cv_block_17_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->get()->count();
                        $tbl_cv_block_18_data = DB::table('tbl_cv_block_18_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->get()->count();
                        $tbl_cv_block_19_data = DB::table('tbl_cv_block_19_data')->where('cv_id', '=', $cv_data->cv_id)->where('is_active', '=', 0)->get()->count();

                        $yt_start_date_arr = [];
                        $yt_end_date_arr = [];
                        if(count($tbl_cv_block_16_data) > 0){
                            array_push($social_media_data_exist_array, "YouTube");
                            foreach($tbl_cv_block_16_data as $cvb16data)
                            {
                                array_push($yt_start_date_arr, $cvb16data->start_date);
                                array_push($yt_end_date_arr, $cvb16data->end_date);
                            }
                        }
                        rsort($yt_start_date_arr);
                        rsort($yt_end_date_arr);

                        if($tbl_cv_block_17_data > 0){
                            array_push($social_media_data_exist_array, "Instagram");
                        }
                        if($tbl_cv_block_18_data > 0){
                            array_push($social_media_data_exist_array, "TikTok");
                        }
                        if($tbl_cv_block_19_data > 0){
                            array_push($social_media_data_exist_array, "Twitter");
                        }
                        if(count($social_media_data_exist_array) > 1){
                            array_unshift($social_media_data_exist_array, "Aggregate");
                        }

                        $most_populer_videos_data = [];
                        if(count($yt_start_date_arr)>0 && count($yt_end_date_arr)>0)
                        {
                            //============ most populer video data as per views count===================
                            $most_populer_videos_metadata = DB::table('tbl_social_spyder_graph_meta_data')
                            ->join('tbl_assets','tbl_social_spyder_graph_meta_data.asset_id','tbl_assets.id')
                            ->select('tbl_social_spyder_graph_meta_data.*','tbl_assets.cs_asset_id')
                            ->where('tbl_social_spyder_graph_meta_data.cv_id', '=', $cv_data->cv_id)
                            ->where('tbl_social_spyder_graph_meta_data.is_active', '=', 0)
                            ->where('tbl_social_spyder_graph_meta_data.start_date', '>=', $yt_start_date_arr[0])
                            ->where('tbl_social_spyder_graph_meta_data.end_date', '<=', $yt_end_date_arr[0])
                            ->whereNotNull('tbl_social_spyder_graph_meta_data.views')
                            ->whereNotNull('tbl_social_spyder_graph_meta_data.asset_id')
                            ->where('tbl_social_spyder_graph_meta_data.cs_status', '=', 1)
                            ->where('tbl_assets.is_active', '=', 0)
                            ->where('tbl_assets.cs_response_status', '=', 2)
                            ->whereNotNull('tbl_assets.cs_asset_id')
                            ->orderBy('tbl_social_spyder_graph_meta_data.views','desc')
                            ->limit(3)
                            ->get();
                            // echo "<pre>";
                            // print_r($most_populer_videos_metadata);die;
                            $most_populer_videos_data = [];
                            if(count($most_populer_videos_metadata) > 0){

                                foreach ($most_populer_videos_metadata as $mpvmdkey => $mpvmdvalue) {

                                    /* $asset_id = $mpvmdvalue->asset_id;
                                    $tbl_assets_data = DB::table('tbl_assets')->where('id', '=', $asset_id)->where('cs_d_status', '=', 1)->where('cs_response_status', '=', 2)->where('is_active', '=', 0)->first();
                                    if($tbl_assets_data != ''){
                                        $tbl_assets_cs_asset_id = $tbl_assets_data->cs_asset_id;
                                        $tbl_asset_processed_amp_main_mood_tag_data = DB::table('tbl_asset_processed_amp_main_mood_tag_data')
                                                ->join('tbl_amp_main_mood_tag_master','tbl_asset_processed_amp_main_mood_tag_data.amp_main_mood_tag','tbl_amp_main_mood_tag_master.tag_id')
                                                ->select('tbl_asset_processed_amp_main_mood_tag_data.*','tbl_amp_main_mood_tag_master.tag_name as tag_name')
                                                ->where('asset_id','=',$tbl_assets_cs_asset_id)
                                                ->where('tbl_asset_processed_amp_main_mood_tag_data.is_active','=',0)
                                                ->where('tbl_amp_main_mood_tag_master.is_active','=',0)
                                                ->get();
                                        if(count($tbl_asset_processed_amp_main_mood_tag_data) > 0){
                                            foreach($tbl_asset_processed_amp_main_mood_tag_data as $key=>$value){
                                                $most_populer_videos_data[$mpvmdvalue->video_id][$value->tag_name] = $value->amp_main_mood_tag_value;
                                            }
                                        }
                                    } */

                                    $tbl_asset_processed_amp_main_mood_tag_data = DB::table('tbl_asset_processed_amp_main_mood_tag_data')
                                                ->join('tbl_amp_main_mood_tag_master','tbl_asset_processed_amp_main_mood_tag_data.amp_main_mood_tag','tbl_amp_main_mood_tag_master.tag_id')
                                                ->select('tbl_asset_processed_amp_main_mood_tag_data.*','tbl_amp_main_mood_tag_master.tag_name as tag_name')
                                                ->where('asset_id','=',$mpvmdvalue->cs_asset_id)
                                                ->where('tbl_asset_processed_amp_main_mood_tag_data.is_active','=',0)
                                                ->where('tbl_amp_main_mood_tag_master.is_active','=',0)
                                                ->get();
                                    if(count($tbl_asset_processed_amp_main_mood_tag_data) > 0){
                                        foreach($tbl_asset_processed_amp_main_mood_tag_data as $key=>$value){
                                            $most_populer_videos_data[$mpvmdvalue->video_id][$value->tag_name] = $value->amp_main_mood_tag_value;
                                        }
                                    }
                                }

                            }else{
                                $most_populer_videos_data = [];
                            }
                        }

                        // =======================vido Analysed data=========================================
                        $yt_videos_analysed_data = [];
                        $ig_videos_analysed_data = [];
                        $tt_videos_analysed_data = [];
                        $twt_videos_analysed_data = [];
                        $video_analysed_tab_data  = [];
                        $video_analysed_tab_data_val  = [];

                        if(count($social_media_data_exist_array)>0)
                        {
                            foreach ($social_media_data_exist_array as $smdeakey => $smdeavalue) {

                                $video_analysed_count = DB::table('tbl_social_spyder_graph_meta_data')
                                ->join('tbl_assets','tbl_social_spyder_graph_meta_data.asset_id','tbl_assets.id')
                                ->select('tbl_social_spyder_graph_meta_data.*')
                                ->where('tbl_social_spyder_graph_meta_data.cv_id', '=', $cv_data->cv_id)
                                ->where('tbl_social_spyder_graph_meta_data.process_type', '=', $smdeavalue)
                                ->where('tbl_social_spyder_graph_meta_data.is_active', '=', 0)
                                ->whereNotNull('tbl_social_spyder_graph_meta_data.asset_id')
                                ->where('tbl_social_spyder_graph_meta_data.cs_status', '=', 1)
                                ->where('tbl_assets.is_active', '=', 0)
                                ->where('tbl_assets.cs_response_status', '=', 2)
                                ->whereNotNull('tbl_assets.cs_asset_id')
                                ->get()->count();

                                /* if($smdeavalue == "YouTube" && $video_analysed_count > 0){

                                    // array_push($yt_videos_analysed_data, $video_analysed_count);
                                    $video_analysed_tab_data[$smdeavalue] = $video_analysed_count;
                                    // array_push($video_analysed_tab_data, $smdeavalue);

                                }else if($smdeavalue == "Instagram" && $video_analysed_count > 0){

                                    // array_push($ig_videos_analysed_data, $video_analysed_count);
                                    $video_analysed_tab_data[$smdeavalue] = $video_analysed_count;
                                    // array_push($video_analysed_tab_data, $smdeavalue);

                                }else if($smdeavalue == "TikTok" && $video_analysed_count > 0){

                                    // array_push($tt_videos_analysed_data, $video_analysed_count);
                                    $video_analysed_tab_data[$smdeavalue] = $video_analysed_count;
                                    // array_push($video_analysed_tab_data, $smdeavalue);


                                }else if($smdeavalue == "Twitter" && $video_analysed_count > 0){

                                    // array_push($twt_videos_analysed_data, $video_analysed_count);
                                    $video_analysed_tab_data[$smdeavalue] = $video_analysed_count;
                                    // array_push($video_analysed_tab_data, $smdeavalue);

                                } */
                                if($video_analysed_count > 0){

                                    // array_push($twt_videos_analysed_data, $video_analysed_count);
                                    $video_analysed_tab_data[$smdeavalue] = $video_analysed_count;
                                    // array_push($video_analysed_tab_data, $smdeavalue);

                                }
                            }
                        }

                        //return view('frontend.views.shared_cv', ['cv_data'=>$cv_data, 'parent_cv'=>$parent_cv, 'footer_template_data'=>$footer_template_data, 'cv_block_2_data'=>$cv_block_2_data, 'parent_cv_overall_ranking'=>$parent_cv_overall_ranking, 'cv_block_3_data'=>$cv_block_3_data, 'music_taste_data'=>$music_taste_data, 'cv_block_4_data'=>$cv_block_4_data, 'cv_block_5_data'=>$cv_block_5_data, 'cv_block_6_data'=>$cv_block_6_data, 'cv_block_7_data'=>$cv_block_7_data, 'cv_block_8_data'=>$cv_block_8_data, 'cv_block_9_data'=>$cv_block_9_data, 'cv_block_10_data'=>$cv_block_10_data, 'qualitative_data'=>$qualitative_data, 'cv_block_11_data'=>$cv_block_11_data, 'cv_block_12_data'=>$cv_block_12_data, 'cv_block_13_data'=>$cv_block_13_data, 'experience_data'=>$experience_data, 'cv_block_14_data'=>$cv_block_14_data, 'cv_block_15_data'=>$cv_block_15_data]);
                        return view('frontend.views.shared_cv', ['tbl_experience_data'=>$tbl_experience_data, 'cv_data'=>$cv_data, 'footer_template_data'=>$footer_template_data, 'cv_block_2_data'=>$cv_block_2_data, 'parent_cv'=>$parent_cv, 'parent_cv_overall_ranking'=>$parent_cv_overall_ranking, 'cv_block_3_data'=>$cv_block_3_data, 'music_taste_data'=>$music_taste_data, 'cv_block_4_data'=>$cv_block_4_data, 'cv_block_5_data'=>$cv_block_5_data, 'cv_block_6_data'=>$cv_block_6_data, 'cv_block_7_data'=>$cv_block_7_data, 'cv_block_8_data'=>$cv_block_8_data, 'cv_block_9_data'=>$cv_block_9_data, 'cv_block_10_data'=>$cv_block_10_data, 'qualitative_data'=>$qualitative_data, 'cv_block_11_data'=>$cv_block_11_data, 'cv_block_12_data'=>$cv_block_12_data, 'cv_block_13_data'=>$cv_block_13_data, 'experience_data'=>$experience_data, 'experience_excluded_data'=>$experience_excluded_data, 'cv_block_14_data'=>$cv_block_14_data, 'cv_block_15_data'=>$cv_block_15_data, 'cv_block_16_mood_graph_data'=>$cv_block_16_mood_graph_data, 'cv_block_16_genre_graph_data'=>$cv_block_16_genre_graph_data, 'cv_block_17_mood_graph_data'=>$cv_block_17_mood_graph_data, 'cv_block_17_genre_graph_data'=>$cv_block_17_genre_graph_data, 'cv_block_18_mood_graph_data'=>$cv_block_18_mood_graph_data, 'cv_block_18_genre_graph_data'=>$cv_block_18_genre_graph_data, 'cv_block_19_mood_graph_data'=>$cv_block_19_mood_graph_data, 'cv_block_19_genre_graph_data'=>$cv_block_19_genre_graph_data, 'cv_mood_aggr_graph_data'=>$cv_mood_aggr_graph_data, 'cv_genre_aggr_graph_data'=>$cv_genre_aggr_graph_data, 'top_3_genre'=>$top_3_genre, 'distinct_months_arr_data'=>$distinct_months_arr_data, 'mood_video_data_arr'=>$mood_video_data_arr, 'genre_video_data_arr'=>$genre_video_data_arr, 'social_media_stats_slide1_data'=>$social_media_stats_s1, 'social_media_stats_other_slide_data'=>$social_media_stats_os,'sonicLogoMainMoodGraphData'=>$sonicLogoMainMoodGraphData,'countSonicLogoAsPerTheCvIndustryData'=>$countSonicLogoAsPerTheCvIndustryData,"social_media_data_exist_array"=>$social_media_data_exist_array,"most_populer_videos_data"=>$most_populer_videos_data,"mood_video_graph_data_arr"=>$mood_video_graph_data_arr,"genre_video_graph_data_arr"=>$genre_video_graph_data_arr,"video_analysed_tab_data"=>$video_analysed_tab_data,"yt_videos_analysed_data"=>$yt_videos_analysed_data,"ig_videos_analysed_data"=>$ig_videos_analysed_data,"tt_videos_analysed_data"=>$tt_videos_analysed_data,"twt_videos_analysed_data"=>$twt_videos_analysed_data]);
                    }
                    catch(\Illuminate\Database\QueryException $ex)
                    {
                        return back()->with('fail', 'Invalid email or Validity is expired.');
                    }
                }
                else
                {
                    return back()->with('fail', 'Invalid email or Validity is expired.');
                }
            }
            else
            {
                return back()->with('fail', 'Invalid email or Validity is expired.');
            }
        }
        catch(\Illuminate\Database\QueryException $ex)
        {
            return back()->with('fail', 'Invalid email or Validity is expired.');
        }
    }

    public function listSharedCv()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.sharedcv_list',['cvs_year_data'=>$cvs_year]);
    }

    function getSharedCv(Request $request)
    {
        if ($request->ajax()) {

            $data = DB::table('tbl_shared_cv')
            ->join('tbl_cvs', 'tbl_shared_cv.cv_id', '=', 'tbl_cvs.cv_id')
            ->join('tbl_users', 'tbl_shared_cv.shared_by', '=', 'tbl_users.uid')
            ->select('tbl_shared_cv.*', 'tbl_cvs.cv_name', 'tbl_cvs.cv_date', 'tbl_users.name as shared_by')
            //->select('tbl_shared_cv.*', DB::raw("CONCAT(tbl_cvs.cv_name,' ',tbl_cvs.cv_date) as cv_name"),'tbl_users.name as shared_by')
            ->orderBy('id', 'desc')
            ->get();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('cv_name', function($data){
                    $cv_name = $data->cv_name." ".explode("-",$data->cv_date)[1];
                    return $cv_name;
                })
                ->addColumn('action', function($data){
                    if($data->is_active == '0')
                    {
                        $actionBtn = '<a href="disable-sharedcv/'.base64_encode($data->id).'" title="Click here to disable shared cv" class="delete btn btn-success btn-sm" onClick="addLoader()">Disable</a>';
                    }
                    else
                    {
                        $actionBtn = '<a href="enable-sharedcv/'.base64_encode($data->id).'" title="Click here to enable shared cv" class="edit btn btn-success btn-sm" onClick="addLoader()">Enable</a>';
                    }
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->setRowId(function($data){
                    return $data->id;
                })
                ->make(true);
        }
    }

    function enableSharedCv($id)
    {
        $update_query = DB::table('tbl_shared_cv')
                            ->where('id', base64_decode($id))
                            ->update(['is_active' => '0', 'edited_by'=>session('LoggedUser')]);

        if($update_query)
        {
            return back()->with('success','CV enabled successfully');
        }
        else
        {
            $error_data = "Something went wrong while enabling shared cv-".base64_decode($id);
            ErrorMailSender::sendErrorMail($error_data);
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }

    function disableSharedCv($id)
    {
        // echo $id;
        // exit;
        $update_query = DB::table('tbl_shared_cv')
                            ->where('id', base64_decode($id))
                            ->update(['is_active' => '1', 'edited_by'=>session('LoggedUser')]);

        if($update_query)
        {
            return back()->with('success','CV disabled successfully');
        }
        else
        {
            $error_data = "Something went wrong while disabling shared cv-".base64_decode($id);
            ErrorMailSender::sendErrorMail($error_data);
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }
}
