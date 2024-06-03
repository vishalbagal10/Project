<?php

namespace App\Http\Controllers;

use App\Models\Cvs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use App\Helpers\GetSocialMediaIconsData;
use Illuminate\Support\Facades\URL;
use Intervention\Image\Facades\Image;
use GuzzleHttp\Client;
use App\Helpers\GenerateAuthToken;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use PDF;
use App\Helpers\ErrorMailSender;
use Illuminate\Support\Facades\File;

class CvsController extends Controller
{
    public function getSMData()
    {
        $smData = GetSocialMediaIconsData::getSMData();
        //print_r(json_encode($smData));exit;

       $html = '';
       $html .= '       <div class="nav-tabs-custom">';
       $html .= '			<ul class="nav nav-tabs">';
       $html .= '			</ul>';
       $html .= '			<div class="tab-content">';
       $html .= '				<div id="divSMtab1" class="tab-pane active">';
       $html .= '                   <div id="divSMTabBox1" class="box" style="height: 250px; overflow-y: auto; border: 0 none; box-shadow: none;">';
       $html .= '                       <div>';
        foreach($smData as $data)
        {
            //echo $data['dirname'];
            //$dir = str_replace("\","",$data['dirname']);
            //$html .= '                           <span class="socialMediaIcons"><img alt="" onclick=\'assignVals("", "", "'.URL::to('public/images/social_media_icons').'/'.$data['filename'].'.'.$data['extension'].'", "")\' style="width:50px; height:auto;" src="'.URL::to('/public/images/social_media_icons').'/'.$data['filename'].'.'.$data['extension'].'"></span>';
            $html .= '                           <span class="socialMediaIcons"><img alt="" onclick=\'assignVals("'.URL::to('public/images/social_media_icons').'/'.$data['filename'].'.'.$data['extension'].'")\' style="width:50px; height:auto;" src="'.URL::to('/public/images/social_media_icons').'/'.$data['filename'].'.'.$data['extension'].'"></span>';
        }
        $html .= '                       </div>';
        $html .= '                   </div>';
        $html .= '               </div> ';
        $html .= '			</div> ';
        $html .= '		</div> ';

        //echo $html;

        return $html;
    }

    function getMusicTeasteData($ids)
    {
        $music_taste_data = DB::table('tbl_music_taste')
                    ->whereIn('music_taste_id', explode(",",$ids))
                    ->get();
        $mtData = '';
        for($i=0; $i<count($music_taste_data); $i++)
        {
            $mtData .='<li><div class="icon_hol_parent"><span class="icon_hol"><img src="../public/images/music_taste_icons/thumbnail/'.$music_taste_data[$i]->music_taste_icon_name.'" alt="'.$music_taste_data[$i]->music_taste_name.'"></span></div><h4 class="lp_section_03_caption mt-2">'.$music_taste_data[$i]->music_taste_name.'</h4></li>';
        }
        return $mtData;
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
            ->where('cv_id', $cv_ids_array)
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

    function previewCvData($id)
    {
        $cv_data = DB::table('tbl_cvs')->where('cv_id', '=', $id)->first();
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
        $cv_block_2_data = DB::table('tbl_best_in_audio_brands')->where('cv_id', '=', $id)->where('is_active', '=', 0)->first();
        $cv_block_3_data = DB::table('tbl_cv_block_3_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->get();
        $cv_block_4_data = DB::table('tbl_cv_block_4_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->first();
        $cv_block_5_data = DB::table('tbl_cv_block_5_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->get();
        $cv_block_6_data = DB::table('tbl_cv_block_6_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->first();
        $cv_block_7_data = DB::table('tbl_cv_block_7_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->get();
        $cv_block_8_data = DB::table('tbl_cv_block_8_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->get();
        $cv_block_9_data = DB::table('tbl_cv_block_9_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->get();
        $cv_block_10_data = DB::table('tbl_cv_block_10_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->get();
        $cv_block_11_data = DB::table('tbl_cv_block_11_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->get();
        $cv_block_12_data = DB::table('tbl_cv_block_12_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->get();
        $cv_block_13_data = DB::table('tbl_cv_block_13_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->get();
        $cv_block_14_data = DB::table('tbl_cv_block_14_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->get();
        $cv_block_15_data = DB::table('tbl_cv_block_15_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->get();

        /* $cv_genre_aggr_graph_values_data = DB::table('tbl_genre_aggr_graph_data')->select('ambient','blues','classical','country','electronicDance','folk','indieAlternative','jazz','latin','metal','pop','punk','rapHipHop','reggae','rnb','rock','singerSongwriter')->where('cv_id', '=', $id)->where('is_active', '=', 0)->first();
        $cv_genre_aggr_graph_values_arr = (array)$cv_genre_aggr_graph_values_data;
        $cv_genre_aggr_graph_values_arr1 = (array)$cv_genre_aggr_graph_values_data;
        rsort($cv_genre_aggr_graph_values_arr);
        $top3 = array_slice($cv_genre_aggr_graph_values_arr, 0, 3);
        $top_3_genre = array();

        foreach ($top3 as $key => $val) {
            //echo "key-".$key."----------- val-".$val."<br>";
            $key = array_search ($val, $cv_genre_aggr_graph_values_arr1);
            unset($cv_genre_aggr_graph_values_arr1[$key]);
            // $top_3_genre[$key] = $val;
            array_push($top_3_genre,$key."_".$val);
        }

        if(count($top_3_genre)==0)
        {
            $top_3_genre = '';
        } */

        $avg_genre_data = DB::table('tbl_social_media_aggr_genre_graph_data')
        ->where('cv_id', '=', $id)
        ->where('is_active', '=', 0)
        ->get();
        //print_r($avg_genre_data);
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

        $music_teaste_data_ids_array = [];

        if(count($cv_block_3_data)==0)
        {
            $cv_block_3_data = '';
        }
        else
        {
            for($smi = 0; $smi<count($cv_block_3_data); $smi++)
            {
                array_push($music_teaste_data_ids_array,$cv_block_3_data[$smi]->b3_title_id);
            }
        }
        if(count($cv_block_5_data)==0)
        {
            $cv_block_5_data = '';
        }
        if(count($cv_block_7_data)==0)
        {
            $cv_block_7_data = '';
        }
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
        }
        if(count($cv_block_11_data)==0)
        {
            $cv_block_11_data = '';
        }
        if(count($cv_block_12_data)==0)
        {
            $cv_block_12_data = '';
        }
        if(count($cv_block_13_data)==0)
        {
            $cv_block_13_data = '';
        }
        if(count($cv_block_14_data)==0)
        {
            $cv_block_14_data = '';
        }
        if(count($cv_block_15_data)==0)
        {
            $cv_block_15_data = '';
        }
        if($music_teaste_data_ids_array != '')
        {
            $music_teaste_data = implode(",",$music_teaste_data_ids_array);
        }
        else
        {
            $music_teaste_data = '';
        }

        $data = [];
        array_push($data,$cv_data,$cv_block_2_data,$cv_block_3_data,$cv_block_4_data,$cv_block_5_data,$cv_block_6_data,$cv_block_7_data,$cv_block_8_data,$cv_block_9_data,$cv_block_10_data,$cv_block_11_data,$cv_block_12_data,$cv_block_13_data,$cv_block_14_data,$cv_block_15_data,$music_teaste_data,$parent_cv_overall_ranking,$top_3_genre);
        //print_r($data); exit;
        return  $data;
    }

    function getQualitativeName($ids)
    {
        $qualitative_data = DB::table('tbl_qualitative')
                    ->whereIn('qualitative_id', explode(",",$ids))
                    ->get();
        $adimlData = [];
        for($i=0; $i<count($qualitative_data); $i++)
        {
            array_push($adimlData,$qualitative_data[$i]->qualitative_name);
        }
        return $adimlData;
    }

    function getExperienceName($ids)
    {
        $experience_data = DB::table('tbl_experience')
                    ->whereIn('experience_id', explode(",",$ids))
                    ->orderBy('display_order','asc')
                    ->get();
        $mtuoaData = [];
        for($i=0; $i<count($experience_data); $i++)
        {
            array_push($mtuoaData,$experience_data[$i]->experience_name);
        }
        return $mtuoaData;
    }

    function addCv($type)
    {
        $cv_data = DB::table('tbl_cvs')->where('is_active', '=', 0)->get();
        $industry_data = DB::table('tbl_industry')->where('is_active', '=', 0)->get();
        $sub_industry_data =  DB::table('tbl_sub_industry')->where('is_active', '=', 0)->get();
        $music_taste_data = DB::table('tbl_music_taste')->where('is_active', '=', 0)->get();
        $experience_data = DB::table('tbl_experience')->where('is_active', '=', 0)->orderBy('display_order', 'asc')->get();
        $qualitative_data = DB::table('tbl_qualitative')->where('is_active', '=', 0)->get();
        $footer_template_data = DB::table('tbl_footer_template')->where('is_active', '=', 0)->get();
        $cv_parent_ids = DB::table('tbl_cvs')->where('parent_id', '!=', null)->where('parent_id', '!=', '')->where('is_active', '=', 0)->get();
        //$social_media_icon_data = DB::table('tbl_social_media')->where('is_active', '=', 0)->get();

        if(count($cv_parent_ids)==0)
        {
            $cv_parent_ids_array = [];
        }
        else
        {
            $cv_parent_ids_array = [];
            foreach($cv_parent_ids as $pid)
            {
                array_push($cv_parent_ids_array,$pid->parent_id);
            }
        }

        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();

        if(base64_decode($type) == 'brand')
        {
            //return view('backend.views.add_brand_cv', ['cv_data'=>$cv_data, 'industry_data'=>$industry_data, 'music_taste_data'=>$music_taste_data, 'experience_data'=>$experience_data, 'qualitative_data'=>$qualitative_data, 'social_media_icon_data'=>$social_media_icon_data]);
            return view('backend.views.add_brand_cv', ['cv_data'=>$cv_data, 'cv_parent_ids_array'=>$cv_parent_ids_array, 'industry_data'=>$industry_data, 'sub_industry_data'=>$sub_industry_data, 'music_taste_data'=>$music_taste_data, 'experience_data'=>$experience_data, 'qualitative_data'=>$qualitative_data, 'footer_template_data'=>$footer_template_data,'cvs_year_data'=>$cvs_year]);
        }
        else
        {
            //return view('backend.views.add_industry_cv', ['cv_data'=>$cv_data, 'industry_data'=>$industry_data, 'music_taste_data'=>$music_taste_data, 'experience_data'=>$experience_data, 'qualitative_data'=>$qualitative_data, 'social_media_icon_data'=>$social_media_icon_data]);
            return view('backend.views.add_industry_cv', ['cv_data'=>$cv_data, 'cv_parent_ids_array'=>$cv_parent_ids_array, 'industry_data'=>$industry_data, 'sub_industry_data'=>$sub_industry_data, 'music_taste_data'=>$music_taste_data, 'experience_data'=>$experience_data, 'qualitative_data'=>$qualitative_data, 'footer_template_data'=>$footer_template_data,'cvs_year_data'=>$cvs_year]);
        }
    }

    function saveBrandCv(Request $request)
    {
        //$social_media_data = GetSocialMediaIconsData::index();
        //print_r($social_media_data);
        /* $request->validate([
            "cv_type"=>'required',
            "cv_name"=>'required',
            "cv_date"=>'required',
            "cv_logo"=>'required|image|mimes:jpeg,png,jpg|max:2048',
            "cv_banner_desktop"=>'required|image|mimes:jpeg,png,jpg|max:2048',
            "cv_banner_ipad"=>'required|image|mimes:jpeg,png,jpg|max:2048',
            "cv_banner_mobile"=>'required|image|mimes:jpeg,png,jpg|max:2048',
            "industry_name"=>'required|not_in:0'
        ]); */


        $request->validate([
            "cv_type"=>'required',
            "cv_name"=>'required',
            "cv_date"=>'required',
            "industry_name"=>'required|not_in:0'
        ]);
        //return $request->input();
        //echo $request->parent_cv_name;
        if($request->parent_cv_name != '' || $request->parent_cv_name != '0#_#sel')
        {

            $parent_data = Str::of($request->parent_cv_name)->split('/#_#/');
            $parent_id = ($parent_data[0] != 0 || $parent_data[0] != '0') ? $parent_data[0] : null;
            $parent_name = ($parent_data[1] != 'sel') ? $parent_data[1] : null;
        }
        else
        {
            $parent_id = null;
            $parent_name = null;
        }

        /* $id = DB::table('tbl_cvs')->insertGetId(
            ['type' => $request->cv_type,
            'parent_id' => $parent_id,
            'parent' => $parent_name,
            'cv_name' => $request->cv_name,
            'cv_date' => $request->cv_date,
            'industry_id' => $request->industry_name,
            'footer_template_id' => $request->footer_template_name,
            'created_by' => session('LoggedUser'),]
        ); */

        $block_1_data = ['type' => $request->cv_type,
        'parent_id' => $parent_id,
        'parent' => $parent_name,
        'cv_name' => $request->cv_name,
        'cv_date' => $request->cv_date,
        'cv_year' => explode('-',$request->cv_date)[1],
        'industry_id' => $request->industry_name,
        'sub_industry_id' => $request->sub_industry_name,
        'footer_template_id' => $request->footer_template_name,
        'created_by' => session('LoggedUser'),
        'md_flag' => $request->missing_data_flag_name];
        if(DB::table('tbl_cvs')->insertOrIgnore($block_1_data))
        {
            $last_inserted_id = DB::table('tbl_cvs')
            ->where('type', $request->cv_type)
            ->where('parent_id', $parent_id)
            ->where('parent', $parent_name)
            ->where('cv_name', $request->cv_name)
            ->where('cv_date', $request->cv_date)
            ->where('industry_id', $request->industry_name)
            ->where('footer_template_id', $request->footer_template_name)
            ->first();
            $id = $last_inserted_id->cv_id;
        }
        else
        {
            $error_data = "Something went wrong while inserting block 1 data";
            ErrorMailSender::sendErrorMail($error_data);
            return back()->with('fail', 'Something went wrong while inserting cv data, please try again!');
        }
        //echo $id;
        if ($id == 0 || $id == '')
        {
            $error_data = "Something went wrong while inserting block 1 data";
            ErrorMailSender::sendErrorMail($error_data);
            return back()->with('fail', 'Something went wrong while inserting cv data, please try again!');
        }
        else
        {
            if($request->cv_logo != '')
            {
                $image = $request->cv_logo;
                $img_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8').'.'.$image->getClientOriginalExtension());

                $destinationPath = public_path('/images/cv_logos/thumbnail');
                $img = Image::make($image->path());
                $img->resize(300, 300, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath.'/'.$img_name);

                $destinationPath = public_path('/images/cv_logos/medium');
                $img = Image::make($image->path());
                $img->resize(600, 600, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath.'/'.$img_name);

                $destinationPath = public_path('/images/cv_banners/desktop');
                $img = Image::make($image->path());
                $img->resize(500, 500, function ($constraint1) {
                    $constraint1->aspectRatio();
                });
                $img1 = Image::canvas(1600, 410, '#ffffff')->insert($img, 'center');
                $img1->save($destinationPath.'/'.$img_name);

                $destinationPath = public_path('/images/cv_banners/ipad');
                $img = Image::make($image->path());
                $img->resize(400, 400, function ($constraint1) {
                    $constraint1->aspectRatio();
                });
                $img1 = Image::canvas(1024, 262, '#ffffff')->insert($img, 'center');
                $img1->save($destinationPath.'/'.$img_name);

                $destinationPath = public_path('/images/cv_banners/mobile');
                $img = Image::make($image->path());
                $img->resize(400, 400, function ($constraint1) {
                    $constraint1->aspectRatio();
                });
                $img1 = Image::canvas(640, 260, '#ffffff')->insert($img, 'center');
                $img1->save($destinationPath.'/'.$img_name);

                if($image->move(public_path('images/cv_logos/original'), $img_name))
                {

                    if(DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_logo' => $img_name]))
                    {
                        DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_banner_desktop' => $img_name]);
                        DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_banner_ipad' => $img_name]);
                        DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_banner_mobile' => $img_name]);
                    }
                    else
                    {
                        $error_data = "Something went wrong while updating cv_logo data of cv-".$id;
                        ErrorMailSender::sendErrorMail($error_data);
                        return back()->with('fail', 'Something went wrong while inserting cv data, please try again!');
                    }
                }

                /* if($image->move(public_path('images/cv_logos/desktop'), $img_name))
                {
                    if($request->cv_logo_ipad != '')
                    {
                        $image = $request->cv_logo_ipad;
                        $img_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8').'.'.$image->getClientOriginalExtension());
                        $image->move(public_path('images/cv_logos/ipad'), $img_name);
                    }

                    if($request->cv_logo_mobile != '')
                    {
                        $image = $request->cv_logo_mobile;
                        $img_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8').'.'.$image->getClientOriginalExtension());
                        $image->move(public_path('images/cv_logos/mobile'), $img_name);
                    }

                    if(DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_logo' => $img_name]))
                    {

                    }
                    else
                    {
                        return back()->with('fail', 'Something went wrong while inserting cv data, please try again!');
                    }
                }  */
            }

            /* if($request->cv_banner_desktop != '')
            {
                $image = $request->cv_banner_desktop;
                $img_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8').'.'.$image->getClientOriginalExtension());
                if($image->move(public_path('images/cv_banners/desktop'), $img_name))
                {
                    if(DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_banner_desktop' => $img_name]))
                    {

                    }
                    else
                    {
                        $error_data = "Something went wrong while updating cv_banner_desktop data of cv-".$id;
                        ErrorMailSender::sendErrorMail($error_data);
                        return back()->with('fail', 'Something went wrong while inserting cv data, please try again!');
                    }
                }
            } */

            /* if($request->cv_banner_ipad != '')
            {
                $image = $request->cv_banner_ipad;
                $img_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8').'.'.$image->getClientOriginalExtension());
                if($image->move(public_path('images/cv_banners/ipad'), $img_name))
                {
                    if(DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_banner_ipad' => $img_name]))
                    {

                    }
                    else
                    {
                        $error_data = "Something went wrong while updating cv_banner_ipad data of cv-".$id;
                        ErrorMailSender::sendErrorMail($error_data);
                        return back()->with('fail', 'Something went wrong while inserting cv data, please try again!');
                    }
                }
            } */

            /* if($request->cv_banner_mobile != '')
            {
                $image = $request->cv_banner_mobile;
                $img_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8').'.'.$image->getClientOriginalExtension());
                if($image->move(public_path('images/cv_banners/mobile'), $img_name))
                {
                    if(DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_banner_mobile' => $img_name]))
                    {

                    }
                    else
                    {
                        $error_data = "Something went wrong while updating cv_banner_mobile data of cv-".$id;
                        ErrorMailSender::sendErrorMail($error_data);
                        return back()->with('fail', 'Something went wrong while inserting cv data, please try again!');
                    }
                }
            } */

            /* if($request->section_2_title !='' || $request->ranking !='')
            {
                $block_2_data = [
                    'b2_title' => $request->section_2_title,
                    'b2_value' => $request->ranking,
                    'cv_id' => $id,
                    'created_by' => session('LoggedUser')
                ];
                //DB::table('tbl_cv_block_2_data')->updateOrInsert($block_2_data);
                if(DB::table('tbl_cv_block_2_data')->insertOrIgnore($block_2_data))
                {

                }
                else
                {
                    return back()->with('fail', 'Something went wrong while inserting section 2 data, please try again!');
                }
            } */

            /* if($request->cv_music_taste_name_ids !='')
            {
                $cv_music_taste_name_ids_array = explode(',' , $request->cv_music_taste_name_ids);
                for($i=0; $i<count($cv_music_taste_name_ids_array); $i++)
                {
                    $block_3_data = [
                        'b3_title' => $request->section_3_title,
                        'b3_title_id' => $cv_music_taste_name_ids_array[$i],
                        'cv_id' => $id,
                        'created_by' => session('LoggedUser')
                    ];
                    //DB::table('tbl_cv_block_3_data')->updateOrInsert($block_3_data);
                    if(DB::table('tbl_cv_block_3_data')->insertOrIgnore($block_3_data))
                    {

                    }
                    else
                    {
                        return back()->with('fail', 'Something went wrong while inserting section 3 data, please try again!');
                    }
                }
            } */

            if($request->section_4_title !='' || $request->about_description !='' || $request->about_key_findings !='')
            {
                $block_4_data = [
                    'b4_title' => $request->section_4_title,
                    'b4_description' => $request->about_description,
                    'b4_key_findings' => $request->about_key_findings,
                    'cv_id' => $id,
                    'created_by' => session('LoggedUser')
                ];
                //DB::table('tbl_cv_block_4_data')->updateOrInsert($block_4_data);
                if(DB::table('tbl_cv_block_4_data')->insertOrIgnore($block_4_data))
                {

                }
                else
                {
                    $error_data = "Something went wrong while inserting block_4_data data of cv-".$id;
                    ErrorMailSender::sendErrorMail($error_data);
                    return back()->with('fail', 'Something went wrong while inserting section 4 data, please try again!');
                }
            }


            if($request->smDataCount !='0' || $request->smDataCount !='')
            {
                $imgArray = GetSocialMediaIconsData::getSMData();
                $img_name = count($imgArray);
                $smDataCount = $request->smDataCount;
                for($i=0; $i<$smDataCount; $i++)
                {
                    $smTrIcon = "smTrIcon_".$i;
                    $smTrUrl = "smTrUrl_".$i;
                    $smTrName = "smTrName_".$i;

                    if(Str::contains($request->$smTrIcon,[';base64,']))
                    {
                        $img_name++;
                        $image_parts = explode(";base64,", $request->$smTrIcon);
                        $image_type_aux = explode("image/", $image_parts[0]);
                        $image_type = $image_type_aux[1];
                        $image_base64 = base64_decode($image_parts[1]);
                        $file = public_path('images/social_media_icons/original').'/'.$img_name.'.'.$image_type;
                        file_put_contents($file, $image_base64);
                        $destinationPath = public_path('/images/social_media_icons');
                        $img = Image::make($file);
                        $img->resize(200, 200, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($destinationPath.'/'.$img_name.'.'.$image_type);

                        $block_5_data = [
                            'b5_icon_name' => $img_name.'.'.$image_type,
                            'b5_link' => $request->$smTrUrl,
                            'b5_link_name' => $request->$smTrName,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser')
                        ];
                    }
                    else
                    {
                        $block_5_data = [
                            'b5_icon_name' => Str::of($request->$smTrIcon)->afterLast('/'),
                            'b5_link' => $request->$smTrUrl,
                            'b5_link_name' => $request->$smTrName,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser')
                        ];
                    }
                    //DB::table('tbl_cv_block_5_data')->updateOrInsert($block_5_data);
                    if(DB::table('tbl_cv_block_5_data')->insertOrIgnore($block_5_data))
                    {

                    }
                    else
                    {
                        $error_data = "Something went wrong while inserting block_5_data data of cv-".$id;
                        ErrorMailSender::sendErrorMail($error_data);
                        return back()->with('fail', 'Something went wrong while inserting section 6 data, please try again!');
                    }
                }
            }

            /* if($request->section_6_title !='')
            {
                if($request->sonic_logo_audio_file !='')
                {
                    // $request->validate([
                    //     "sonic_logo_audio_file"=>'mimes:mp3,wav,wma,aac,m4a,ogg'
                    // ]);
                    $file = $request->sonic_logo_audio_file;
                    $file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8').'.'.$file->getClientOriginalExtension());
                    $file->move(public_path('audios/cv_audios'), $file_name);
                    $block_6_data = [
                        'b6_title' => $request->section_6_title,
                        'b6_name' => $file_name,
                        'cv_id' => $id,
                        'created_by' => session('LoggedUser')
                    ];
                }
                else
                {
                    $block_6_data = [
                        'b6_title' => $request->section_6_title,
                        'cv_id' => $id,
                        'created_by' => session('LoggedUser')
                    ];
                }
                //DB::table('tbl_cv_block_6_data')->updateOrInsert($block_6_data);
                if(DB::table('tbl_cv_block_6_data')->insertOrIgnore($block_6_data))
                {

                }
                else
                {
                    return back()->with('fail', 'Something went wrong while inserting section 6 data, please try again!');
                }
            } */

            if($request->sonic_logo_count !='')
            {
                $sonic_logo_count = $request->sonic_logo_count;
                for($i=0; $i<$sonic_logo_count; $i++)
                {
                    //$legend_name = "cv_sonic_usage_legend_name_".$i;
                    //$legend_number = "cv_sonic_usage_legend_number_".$i;
                    $audio_title = "section_6_title_".$i;
                    $audio_name = "sonic_logo_audio_file_".$i;
                    $audio_id = "section_6_id_".$i;

                    //echo "audio_title:".$request->$audio_title."<br>audio_name:".$request->$audio_name."<br>audio_id:".$request->$audio_id."<br>";
                    //echo $request->$audio_name;

                    if($request->$audio_name !='')
                    {
                        // $request->validate([
                        //     "sonic_logo_audio_file"=>'mimes:mp3,wav,wma,aac,m4a,ogg'
                        // ]);
                        $file = $request->$audio_name;
                        $file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8').'_'.$i.'.'.$file->getClientOriginalExtension());
                        $file->move(public_path('audios/cv_audios'), $file_name);
                        $block_6_data = [
                            'b6_title' => $request->$audio_title,
                            'b6_name' => $file_name,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser')
                        ];
                    }
                    else
                    {
                        $block_6_data = [
                            'b6_title' => $request->$audio_title,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser')
                        ];
                    }
                    //DB::table('tbl_cv_block_6_data')->updateOrInsert($block_6_data);
                    if(DB::table('tbl_cv_block_6_data')->insertOrIgnore($block_6_data))
                    {

                    }
                    else
                    {
                        $error_data = "Something went wrong while inserting block_6_data data of cv-".$id;
                        ErrorMailSender::sendErrorMail($error_data);
                        return back()->with('fail', 'Something went wrong while inserting section 12 data, please try again!');
                    }
                }
            }

            if($request->section_7_title !='' && $request->sonic_usage_count !='')
            {
                $sonic_usage_count = $request->sonic_usage_count;
                for($i=0; $i<$sonic_usage_count; $i++)
                {
                    $legend_name = "cv_sonic_usage_legend_name_".$i;
                    $legend_number = "cv_sonic_usage_legend_number_".$i;

                    if($legend_name != '' || $legend_number !='')
                    {
                        $block_7_data = [
                            'b7_title' => $request->section_7_title,
                            'b7_name' => $request->$legend_name,
                            'b7_number' => $request->$legend_number,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser')
                        ];

                        //DB::table('tbl_cv_block_7_data')->updateOrInsert($block_7_data);
                        if(DB::table('tbl_cv_block_7_data')->insertOrIgnore($block_7_data))
                        {

                        }
                        else
                        {
                            $error_data = "Something went wrong while inserting block_7_data data of cv-".$id;
                            ErrorMailSender::sendErrorMail($error_data);
                            return back()->with('fail', 'Something went wrong while inserting section 13 data, please try again!');
                        }
                    }
                }
            }

            /* if($request->section_8_title !='' && $request->sonic_usage_industry_avg_count !='')
            {
                $sonic_usage_industry_avg_count = $request->sonic_usage_industry_avg_count;
                for($i=0; $i<$sonic_usage_industry_avg_count; $i++)
                {
                    $legend_name = "cv_sonic_usage_industry_avg_legend_name_".$i;
                    $legend_number = "cv_sonic_usage_industry_avg_legend_number_".$i;
                    $legend_color = "cv_sonic_usage_industry_avg_legend_color_".$i;

                    if($legend_name != '' || $legend_number !='')
                    {
                        $block_8_data = [
                            'b8_title' => $request->section_8_title,
                            'b8_name' => $request->$legend_name,
                            'b8_number' => $request->$legend_number,
                            'b8_color_code' => $request->$legend_color,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser')
                        ];

                        //DB::table('tbl_cv_block_8_data')->updateOrInsert($block_8_data);
                        if(DB::table('tbl_cv_block_8_data')->insertOrIgnore($block_8_data))
                        {

                        }
                        else
                        {
                            return back()->with('fail', 'Something went wrong while inserting section 8 data, please try again!');
                        }
                    }
                }
            } */

            if($request->section_9_title !='' && $request->most_popular_video_count !='')
            {
                $most_popular_video_count = $request->most_popular_video_count;
                for($i=0; $i<$most_popular_video_count; $i++)
                {
                    $video_title = "cv_most_popular_video_title_".$i;
                    $video_link = "cv_most_popular_video_link_".$i;

                    if($video_title != '' || $video_link !='')
                    {
                        $block_9_data = [
                            'b9_title' => $request->section_9_title,
                            'b9_video_title' => $request->$video_title,
                            'b9_video_link' => $request->$video_link,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser')
                        ];

                        //DB::table('tbl_cv_block_9_data')->updateOrInsert($block_9_data);
                        if(DB::table('tbl_cv_block_9_data')->insertOrIgnore($block_9_data))
                        {

                        }
                        else
                        {
                            $error_data = "Something went wrong while inserting block_9_data data of cv-".$id;
                            ErrorMailSender::sendErrorMail($error_data);
                            return back()->with('fail', 'Something went wrong while inserting section 5 data, please try again!');
                        }
                    }
                }
            }

            /* if($request->section_10_title !='' && $request->a_day_in_my_life_count !='')
            {

                $img_name = '';
                $a_day_in_my_life_count = $request->a_day_in_my_life_count;
                for($i=0; $i<$a_day_in_my_life_count; $i++)
                {
                    $name_id = "cv_a_day_in_my_life_name_id_".$i;
                    $number = "cv_a_day_in_my_life_number_".$i;
                    $color = "cv_a_day_in_my_life_color_".$i;

                    if($name_id != '' || $number !='')
                    {
                        $block_10_data = [
                            'b10_title' => $request->section_10_title,
                            //'b10_bg_color' => $request->section_10_bg_color,
                            'b10_name_id' => $request->$name_id,
                            'b10_number' => $request->$number,
                            'b10_color' => $request->$color,
                            'b10_bg_image' => $img_name,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser')
                        ];

                        //DB::table('tbl_cv_block_10_data')->updateOrInsert($block_10_data);
                        if(DB::table('tbl_cv_block_10_data')->insertOrIgnore($block_10_data))
                        {

                        }
                        else
                        {
                            return back()->with('fail', 'Something went wrong while inserting section 10 data, please try again!');
                        }
                    }
                }
            } */

            /* if($request->section_11_title !='' && $request->msoa_count !='')
            {
                $msoa_count = $request->msoa_count;
                for($i=0; $i<$msoa_count; $i++)
                {
                    $msoa_number = "msoa_number_".$i;
                    $msoa_description = "msoa_description_".$i;

                    if($msoa_number != '' || $msoa_description !='')
                    {
                        $block_11_data = [
                            'b11_title' => $request->section_11_title,
                            'b11_number' => $request->$msoa_number,
                            'b11_description' => $request->$msoa_description,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser')
                        ];

                        //DB::table('tbl_cv_block_11_data')->updateOrInsert($block_11_data);
                        if(DB::table('tbl_cv_block_11_data')->insertOrIgnore($block_11_data))
                        {

                        }
                        else
                        {
                            return back()->with('fail', 'Something went wrong while inserting section 11 data, please try again!');
                        }
                    }
                }
            } */

            /* if($request->smsDataCount !='0' || $request->smsDataCount !='')
            {
                $imgArray = GetSocialMediaIconsData::getSMData();
                $img_name = count($imgArray);
                $smsDataCount = $request->smsDataCount;
                for($i=0; $i<$smsDataCount; $i++)
                {
                    $smsTrIcon = "smsTrIcon_".$i;
                    $smsTrUrl = "smsTrUrl_".$i;
                    $smsTrName = "smsTrName_".$i;
                    $smsTrNumber = "smsTrNumber_".$i;
                    $smsTrtxt = "smsTrTxt_".$i;

                    if(Str::contains($request->$smsTrIcon,[';base64,']))
                    {
                        $img_name++;
                        $image_parts = explode(";base64,", $request->$smsTrIcon);
                        $image_type_aux = explode("image/", $image_parts[0]);
                        $image_type = $image_type_aux[1];
                        $image_base64 = base64_decode($image_parts[1]);
                        $file = public_path('images/social_media_icons/original').'/'.$img_name.'.'.$image_type;
                        file_put_contents($file, $image_base64);
                        $destinationPath = public_path('/images/social_media_icons');
                        $img = Image::make($file);
                        $img->resize(200, 200, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($destinationPath.'/'.$img_name.'.'.$image_type);

                        $block_12_data = [
                            'b12_title' => $request->section_12_title,
                            'b12_icon_name' => $img_name.'.'.$image_type,
                            'b12_link' => $request->$smsTrUrl,
                            'b12_link_name' => $request->$smsTrName,
                            'b12_link_number' => $request->$smsTrNumber,
                            'b12_link_txt' => $request->$smsTrtxt,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser')
                        ];
                    }
                    else
                    {
                        $block_12_data = [
                            'b12_title' => $request->section_12_title,
                            'b12_icon_name' => Str::of($request->$smsTrIcon)->afterLast('/'),
                            'b12_link' => $request->$smsTrUrl,
                            'b12_link_name' => $request->$smsTrName,
                            'b12_link_number' => $request->$smsTrNumber,
                            'b12_link_txt' => $request->$smsTrtxt,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser')
                        ];
                    }
                    //DB::table('tbl_cv_block_12_data')->updateOrInsert($block_12_data);
                    if(DB::table('tbl_cv_block_12_data')->insertOrIgnore($block_12_data))
                    {

                    }
                    else
                    {
                        return back()->with('fail', 'Something went wrong while inserting section 12 data, please try again!');
                    }
                }
            } */

            if($request->section_13_title !='' && $request->efb_count !='')
            {
                /* if($request->section_13_bg_image != '')
                {
                    $image = $request->section_13_bg_image;
                    $img_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8').'.'.$image->getClientOriginalExtension());

                    // $destinationPath = public_path('/images/section_10_bg_images/thumbnail');
                    // $img = Image::make($image->path());
                    // $img->resize(960, 1470, function ($constraint) {
                    //     $constraint->aspectRatio();
                    // })->save($destinationPath.'/'.$img_name);

                    // $destinationPath = public_path('/images/section_10_bg_images/medium');
                    // $img = Image::make($image->path());
                    // $img->resize(960, 1840, function ($constraint) {
                    //     $constraint->aspectRatio();
                    // })->save($destinationPath.'/'.$img_name);

                    if($image->move(public_path('images/section_13_bg_images'), $img_name))
                    {
                    }
                    else
                    {
                        return back()->with('fail', 'Something went wrong while uploading image for section 10, please try again!');
                    }
                }
                else
                {
                    $img_name = '';
                } */
                $img_name = '';
                $efb_count = $request->efb_count;
                for($i=0; $i<$efb_count; $i++)
                {
                    $efb_name_id = "cv_efb_name_id_".$i;
                    $efb_number = "cv_efb_number_".$i;

                    if($efb_name_id != '' || $efb_number !='')
                    {
                        $block_13_data = [
                            'b13_title' => $request->section_13_title,
                            //'b13_bg_color' => $request->section_13_bg_color,
                            'b13_name_id' => $request->$efb_name_id,
                            'b13_number' => $request->$efb_number,
                            'b13_bg_image' => $img_name,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser')
                        ];

                        //DB::table('tbl_cv_block_13_data')->updateOrInsert($block_13_data);
                        if(DB::table('tbl_cv_block_13_data')->insertOrIgnore($block_13_data))
                        {

                        }
                        else
                        {
                            $error_data = "Something went wrong while inserting block_13_data data of cv-".$id;
                            ErrorMailSender::sendErrorMail($error_data);
                            return back()->with('fail', 'Something went wrong while inserting section 16 data, please try again!');
                        }
                    }
                }
            }

            if($request->section_14_title !='' && $request->mepy_count !='')
            {
                $mepy_count = $request->mepy_count;
                for($i=0; $i<$mepy_count; $i++)
                {
                    $mepy_number = "mepy_number_".$i;
                    $mepy_description = "mepy_description_".$i;

                    if($mepy_number != '' || $mepy_description !='')
                    {
                        $block_14_data = [
                            'b14_title' => $request->section_14_title,
                            'b14_number' => $request->$mepy_number,
                            'b14_description' => $request->$mepy_description,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser')
                        ];

                        //DB::table('tbl_cv_block_14_data')->updateOrInsert($block_14_data);
                        if(DB::table('tbl_cv_block_14_data')->insertOrIgnore($block_14_data))
                        {

                        }
                        else
                        {
                            $error_data = "Something went wrong while inserting block_14_data data of cv-".$id;
                            ErrorMailSender::sendErrorMail($error_data);
                            return back()->with('fail', 'Something went wrong while inserting section 17 data, please try again!');
                        }
                    }
                }
            }

            if($request->section_15_title !='' && $request->mepv_count !='')
            {
                $mepv_count = $request->mepv_count;
                for($i=0; $i<$mepv_count; $i++)
                {
                    $mepv_number = "mepv_number_".$i;
                    $mepv_description = "mepv_description_".$i;

                    if($mepv_number != '' || $mepv_description !='')
                    {
                        $block_15_data = [
                            'b15_title' => $request->section_15_title,
                            'b15_number' => $request->$mepv_number,
                            'b15_description' => $request->$mepv_description,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser')
                        ];

                        //DB::table('tbl_cv_block_15_data')->updateOrInsert($block_15_data);
                        if(DB::table('tbl_cv_block_15_data')->insertOrIgnore($block_15_data))
                        {
                        }
                        else
                        {
                            $error_data = "Something went wrong while inserting block_15_data data of cv-".$id;
                            ErrorMailSender::sendErrorMail($error_data);
                            return back()->with('fail', 'Something went wrong while inserting section 18 data, please try again!');
                        }
                    }
                }
            }
            $c_date = date('Y-m-d h:i:s');
            if($request->ychn_name_0 !='' && $request->ychn_count !='')
            {
                $ychn_count = $request->ychn_count;

                for($i=0; $i<$ychn_count; $i++)
                {
                    $ychn_name = 'ychn_name_'.$i;
                    $og_ychn_start_date = 'ychn_start_date_'.$i;
                    $og_ychn_end_date = 'ychn_end_date_'.$i;
                    /* echo "ychn_name:".$request->$ychn_name."<br><br>";
                    echo "og_ychn_start_date:".$request->$og_ychn_start_date."<br><br>";
                    echo "og_ychn_end_date:".$request->$og_ychn_start_date."<br><br>";
                    echo "<br><br><br><br>"; */

                    if($request->$ychn_name !='')
                    {
                        if($request->$og_ychn_start_date !='')
                        {
                            $chn_start_date = explode("-",$request->$og_ychn_start_date)[1]."-".explode("-",$request->$og_ychn_start_date)[0]."-01 00:00:00";
                            $ychn_start_date = $request->$og_ychn_start_date;
                        }
                        else
                        {
                            $chn_start_date = explode("-",$request->cv_date)[1]."-01-01 00:00:00";
                            $ychn_start_date = "01-".explode("-",$request->cv_date)[1];
                        }

                        if($request->$og_ychn_end_date !='')
                        {
                            $end_date_month = explode("-",$request->$og_ychn_end_date)[0];
                            $month_with_31days = array("01","03","05","07","08","10","12");
                            if(in_array($end_date_month, $month_with_31days))
                            {
                                $end_month_days = "-31 00:00:00";
                            }
                            elseif($end_date_month == "02")
                            {
                                $end_month_days = "-28 00:00:00";
                            }
                            else
                            {
                                $end_month_days = "-30 00:00:00";
                            }
                            $chn_end_date = explode("-",$request->$og_ychn_end_date)[1]."-".$end_date_month.$end_month_days;
                            $ychn_end_date = $request->$og_ychn_end_date;
                        }
                        else
                        {
                            $chn_end_date = explode("-",$request->cv_date)[1]."-12-31 00:00:00";
                            $ychn_end_date = "12-".explode("-",$request->cv_date)[1];
                        }

                        $block_16_data = [
                            'chn_name' => preg_replace('/^@/', '', $request->$ychn_name),
                            'start_date' => $chn_start_date,
                            'start_month' => $ychn_start_date,
                            'end_date' => $chn_end_date,
                            'end_month' => $ychn_end_date,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser'),
                            'c_date' => $c_date
                        ];
                        /* print_r($block_16_data);
                        echo "<br><br>"; */
                        $chn_id = DB::table('tbl_cv_block_16_data')->insertGetId($block_16_data);

                        $get_crate_id = DB::table('tbl_social_spyder_graph_meta_data')->where('cv_id','=',$request->cv_id)->where('is_active','=',0)->first();
                        if($get_crate_id != '')
                        {
                            $crate_id = $get_crate_id->crate_id;
                        }
                        else
                        {
                            $crate_id = "";
                        }

                        $request_json = '{ "process_type" : "youtube", "name": "'.preg_replace('/^@/', '', $request->$ychn_name).'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$id.'","chn_id":"y_'.$chn_id.'","c_date":"'.$c_date.'" }';

                        $request_data = [
                            'cv_id' => $id,
                            'chn_id' => "y_".$chn_id,
                            //'request_json' => str_replace("]","",str_replace("[","",json_encode($request_json))),
                            'request_json' => $request_json,
                            'process_type' => "youtube",
                            'created_by' => session('LoggedUser'),
                            'c_date' => $c_date
                        ];
                        //print_r($request_data);
                        if(DB::table('tbl_social_spyder_graph_request_data')->insert($request_data))
                        {

                        }
                        else
                        {
                            $error_data = "Something went wrong while inserting block_16_data data of cv-".$id;
                            ErrorMailSender::sendErrorMail($error_data);
                            return back()->with('fail', 'Something went wrong while inserting section 8 data, please try again!');
                        }
                    }
                }
            }

            if($request->ichn_name_0 !='' && $request->ichn_count !='')
            {
                $ichn_count = $request->ichn_count;

                for($i=0; $i<$ichn_count; $i++)
                {
                    $ichn_name = 'ichn_name_'.$i;
                    $og_ichn_start_date = 'ichn_start_date_'.$i;
                    $og_ichn_end_date = 'ichn_end_date_'.$i;
                    /* echo "ichn_name:".$request->$ichn_name."<br><br>";
                    echo "og_ichn_start_date:".$request->$og_ichn_start_date."<br><br>";
                    echo "og_ichn_end_date:".$request->$og_ichn_start_date."<br><br>";
                    echo "<br><br><br><br>"; */

                    if($request->$ichn_name !='')
                    {
                        if($request->$og_ichn_start_date !='')
                        {
                            $chn_start_date = explode("-",$request->$og_ichn_start_date)[1]."-".explode("-",$request->$og_ichn_start_date)[0]."-01 00:00:00";
                            $ichn_start_date = $request->$og_ichn_start_date;
                        }
                        else
                        {
                            $chn_start_date = explode("-",$request->cv_date)[1]."-01-01 00:00:00";
                            $ichn_start_date = "01-".explode("-",$request->cv_date)[1];
                        }

                        if($request->$og_ichn_end_date !='')
                        {
                            $end_date_month = explode("-",$request->$og_ichn_end_date)[0];
                            $month_with_31days = array("01","03","05","07","08","10","12");
                            if(in_array($end_date_month, $month_with_31days))
                            {
                                $end_month_days = "-31 00:00:00";
                            }
                            elseif($end_date_month == "02")
                            {
                                $end_month_days = "-28 00:00:00";
                            }
                            else
                            {
                                $end_month_days = "-30 00:00:00";
                            }
                            $chn_end_date = explode("-",$request->$og_ichn_end_date)[1]."-".$end_date_month.$end_month_days;
                            $ichn_end_date = $request->$og_ichn_end_date;
                        }
                        else
                        {
                            $chn_end_date = explode("-",$request->cv_date)[1]."-12-31 00:00:00";
                            $ichn_end_date = "12-".explode("-",$request->cv_date)[1];
                        }

                        $block_17_data = [
                            'chn_name' => preg_replace('/^@/', '', $request->$ichn_name),
                            'start_date' => $chn_start_date,
                            'start_month' => $ichn_start_date,
                            'end_date' => $chn_end_date,
                            'end_month' => $ichn_end_date,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser'),
                            'c_date' => $c_date
                        ];
                        /* print_r($block_17_data);
                        echo "<br><br>"; */
                        $chn_id = DB::table('tbl_cv_block_17_data')->insertGetId($block_17_data);

                        $get_crate_id = DB::table('tbl_social_spyder_graph_meta_data')->where('cv_id','=',$request->cv_id)->where('is_active','=',0)->first();
                        if($get_crate_id != '')
                        {
                            $crate_id = $get_crate_id->crate_id;
                        }
                        else
                        {
                            $crate_id = "";
                        }

                        $request_json = '{ "process_type" : "instagram", "name": "'.preg_replace('/^@/', '', $request->$ichn_name).'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$id.'","chn_id":"i_'.$chn_id.'","c_date":"'.$c_date.'" }';

                        $request_data = [
                            'cv_id' => $id,
                            'chn_id' => "i_".$chn_id,
                            //'request_json' => str_replace("]","",str_replace("[","",json_encode($request_json))),
                            'request_json' => $request_json,
                            'process_type' => "instagram",
                            'created_by' => session('LoggedUser'),
                            'c_date' => $c_date
                        ];
                        //print_r($request_data);
                        if(DB::table('tbl_social_spyder_graph_request_data')->insert($request_data))
                        {

                        }
                        else
                        {
                            $error_data = "Something went wrong while inserting block_17_data data of cv-".$id;
                            ErrorMailSender::sendErrorMail($error_data);
                            return back()->with('fail', 'Something went wrong while inserting section 9 data, please try again!');
                        }
                    }
                }
            }

            if($request->tchn_name_0 !='' && $request->tchn_count !='')
            {
                $tchn_count = $request->tchn_count;

                for($i=0; $i<$tchn_count; $i++)
                {
                    $tchn_name = 'tchn_name_'.$i;
                    $og_tchn_start_date = 'tchn_start_date_'.$i;
                    $og_tchn_end_date = 'tchn_end_date_'.$i;
                    /* echo "tchn_name:".$request->$tchn_name."<br><br>";
                    echo "og_tchn_start_date:".$request->$og_tchn_start_date."<br><br>";
                    echo "og_tchn_end_date:".$request->$og_tchn_end_date."<br><br>";
                    echo "<br><br><br><br>"; */

                    if($request->$tchn_name !='')
                    {
                        if($request->$og_tchn_start_date !='')
                        {
                            $chn_start_date = explode("-",$request->$og_tchn_start_date)[1]."-".explode("-",$request->$og_tchn_start_date)[0]."-01 00:00:00";
                            $tchn_start_date = $request->$og_tchn_start_date;
                        }
                        else
                        {
                            $chn_start_date = explode("-",$request->cv_date)[1]."-01-01 00:00:00";
                            $tchn_start_date = "01-".explode("-",$request->cv_date)[1];
                        }

                        if($request->$og_tchn_end_date !='')
                        {
                            $end_date_month = explode("-",$request->$og_tchn_end_date)[0];
                            $month_with_31days = array("01","03","05","07","08","10","12");
                            if(in_array($end_date_month, $month_with_31days))
                            {
                                $end_month_days = "-31 00:00:00";
                            }
                            elseif($end_date_month == "02")
                            {
                                $end_month_days = "-28 00:00:00";
                            }
                            else
                            {
                                $end_month_days = "-30 00:00:00";
                            }
                            $chn_end_date = explode("-",$request->$og_tchn_end_date)[1]."-".$end_date_month.$end_month_days;
                            $tchn_end_date = $request->$og_tchn_end_date;
                        }
                        else
                        {
                            $chn_end_date = explode("-",$request->cv_date)[1]."-12-31 00:00:00";
                            $tchn_end_date = "12-".explode("-",$request->cv_date)[1];
                        }

                        $block_18_data = [
                            'chn_name' => preg_replace('/^@/', '', $request->$tchn_name),
                            'start_date' => $chn_start_date,
                            'start_month' => $tchn_start_date,
                            'end_date' => $chn_end_date,
                            'end_month' => $tchn_end_date,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser'),
                            'c_date' => $c_date
                        ];
                        /* print_r($block_18_data);
                        echo "<br><br>"; */
                        $chn_id = DB::table('tbl_cv_block_18_data')->insertGetId($block_18_data);

                        $get_crate_id = DB::table('tbl_social_spyder_graph_meta_data')->where('cv_id','=',$request->cv_id)->where('is_active','=',0)->first();
                        if($get_crate_id != '')
                        {
                            $crate_id = $get_crate_id->crate_id;
                        }
                        else
                        {
                            $crate_id = "";
                        }

                        $request_json = '{ "process_type" : "tiktok", "name": "'.preg_replace('/^@/', '', $request->$tchn_name).'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$id.'","chn_id":"t_'.$chn_id.'","c_date":"'.$c_date.'" }';

                        $request_data = [
                            'cv_id' => $id,
                            'chn_id' => "t_".$chn_id,
                            //'request_json' => str_replace("]","",str_replace("[","",json_encode($request_json))),
                            'request_json' => $request_json,
                            'process_type' => "tiktok",
                            'created_by' => session('LoggedUser'),
                            'c_date' => $c_date
                        ];
                        //print_r($request_data);
                        if(DB::table('tbl_social_spyder_graph_request_data')->insert($request_data))
                        {

                        }
                        else
                        {
                            $error_data = "Something went wrong while inserting block_18_data data of cv-".$id;
                            ErrorMailSender::sendErrorMail($error_data);
                            return back()->with('fail', 'Something went wrong while inserting section 10 data, please try again!');
                        }
                    }
                }
            }

            if($request->twtchn_name_0 !='' && $request->twtchn_count !='')
            {
                $twtchn_count = $request->twtchn_count;

                for($i=0; $i<$twtchn_count; $i++)
                {
                    $twtchn_name = 'twtchn_name_'.$i;
                    $og_twtchn_start_date = 'twtchn_start_date_'.$i;
                    $og_twtchn_end_date = 'twtchn_end_date_'.$i;
                    /* echo "twtchn_name:".$request->$twtchn_name."<br><br>";
                    echo "og_twtchn_start_date:".$request->$og_twtchn_start_date."<br><br>";
                    echo "og_twtchn_end_date:".$request->$og_twtchn_end_date."<br><br>";
                    echo "<br><br><br><br>"; */

                    if($request->$twtchn_name !='')
                    {
                        if($request->$og_twtchn_start_date !='')
                        {
                            $chn_start_date = explode("-",$request->$og_twtchn_start_date)[1]."-".explode("-",$request->$og_twtchn_start_date)[0]."-01 00:00:00";
                            $twtchn_start_date = $request->$og_twtchn_start_date;
                        }
                        else
                        {
                            $chn_start_date = explode("-",$request->cv_date)[1]."-01-01 00:00:00";
                            $twtchn_start_date = "01-".explode("-",$request->cv_date)[1];
                        }

                        if($request->$og_twtchn_end_date !='')
                        {
                            $end_date_month = explode("-",$request->$og_twtchn_end_date)[0];
                            $month_with_31days = array("01","03","05","07","08","10","12");
                            if(in_array($end_date_month, $month_with_31days))
                            {
                                $end_month_days = "-31 00:00:00";
                            }
                            elseif($end_date_month == "02")
                            {
                                $end_month_days = "-28 00:00:00";
                            }
                            else
                            {
                                $end_month_days = "-30 00:00:00";
                            }
                            $chn_end_date = explode("-",$request->$og_twtchn_end_date)[1]."-".$end_date_month.$end_month_days;
                            $twtchn_end_date = $request->$og_twtchn_end_date;
                        }
                        else
                        {
                            $chn_end_date = explode("-",$request->cv_date)[1]."-12-31 00:00:00";
                            $twtchn_end_date = "12-".explode("-",$request->cv_date)[1];
                        }

                        $block_19_data = [
                            'chn_name' => preg_replace('/^@/', '', $request->$twtchn_name),
                            'start_date' => $chn_start_date,
                            'start_month' => $twtchn_start_date,
                            'end_date' => $chn_end_date,
                            'end_month' => $twtchn_end_date,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser'),
                            'c_date' => $c_date
                        ];
                        /* print_r($block_19_data);
                        echo "<br><br>"; */
                        $chn_id = DB::table('tbl_cv_block_19_data')->insertGetId($block_19_data);

                        $get_crate_id = DB::table('tbl_social_spyder_graph_meta_data')->where('cv_id','=',$request->cv_id)->where('is_active','=',0)->first();
                        if($get_crate_id != '')
                        {
                            $crate_id = $get_crate_id->crate_id;
                        }
                        else
                        {
                            $crate_id = "";
                        }

                        $request_json = '{ "process_type" : "twitter", "name": "'.preg_replace('/^@/', '', $request->$twtchn_name).'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$id.'","chn_id":"twt_'.$chn_id.'","c_date":"'.$c_date.'" }';

                        $request_data = [
                            'cv_id' => $id,
                            'chn_id' => "twt_".$chn_id,
                            //'request_json' => str_replace("]","",str_replace("[","",json_encode($request_json))),
                            'request_json' => $request_json,
                            'process_type' => "twitter",
                            'created_by' => session('LoggedUser'),
                            'c_date' => $c_date
                        ];
                        //print_r($request_data);
                        if(DB::table('tbl_social_spyder_graph_request_data')->insert($request_data))
                        {

                        }
                        else
                        {
                            $error_data = "Something went wrong while inserting block_19_data data of cv-".$id;
                            ErrorMailSender::sendErrorMail($error_data);
                            return back()->with('fail', 'Something went wrong while inserting section 11 data, please try again!');
                        }
                    }
                }
            }

            return redirect('brand-cvs')->with('success','Brand Sonic Radar data inserted successfully');
        }
    }

    public function listBrandCvs()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();

        return view('backend.views.brand_cv_list',['cvs_year_data'=>$cvs_year]);
    }

    function getBrandCvs(Request $request)
    {
        /* if ($request->ajax()) {
            $data = DB::table('tbl_cvs')->orderBy('cv_id', 'desc')->latest();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('parent', function($data){
                    $parent_cv_year = DB::table('tbl_cvs')->where('cv_id','=',$data->parent_id)->first();
                    if($parent_cv_year !='')
                    {
                        $parent = $data->parent." ".explode("-",$parent_cv_year->cv_date)[1];
                    }
                    else
                    {
                        $parent = '';
                    }
                    return $parent;
                })
                ->addColumn('industry', function($data){
                    $industry = DB::table('tbl_industry')->where('industry_id','=',$data->industry_id)->first();
                    if($industry !='')
                    {
                        $industry_name = $industry->industry_name;
                    }
                    else
                    {
                        $industry_name = '';
                    }
                    return $industry_name;
                })
                ->addColumn('sub_industry', function($data){
                    $sub_industry = DB::table('tbl_sub_industry')->where('sub_industry_id','=',$data->sub_industry_id)->first();
                    if($sub_industry !='')
                    {
                        $sub_industry_name = $sub_industry->sub_industry_name;
                    }
                    else
                    {
                        $sub_industry_name = '';
                    }
                    return $sub_industry_name;
                })
                ->addColumn('md_flag', function($data){
                    if($data->md_flag == null || $data->md_flag == '' || $data->md_flag == '-')
                    {
                        $md_flag = '-';
                    }
                    else
                    {
                        $md_flag = $data->md_flag;
                    }
                    return $md_flag;
                })
                ->addColumn('action', function($data){
                    if($data->is_active == '0')
                    {
                        if($data->status == '0')
                        {
                            //$actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here edit" class="edit btn btn-success btn-sm">Edit</a> <a href="disable-cv/'.base64_encode($data->cv_id).'" title="Click here to disable brand" class="delete btn btn-success btn-sm">Disable</a> <a href="duplicate-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to duplicate" class="edit btn btn-success btn-sm">Duplicate</a>';
                            $actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to edit" class="edit btn btn-success btn-sm">Edit</a> <a href="disable-cv/'.base64_encode($data->cv_id).'" title="Click here to disable brand" class="delete btn btn-success btn-sm">Disable</a> <span class="btn btn-success btn-sm" onclick=previewCV("'.$data->cv_id.'","publish")>Preview & Publish</span> <a href="duplicate-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to duplicate" class="edit btn btn-success btn-sm">Duplicate</a>';
                        }
                        else
                        {
                            //$actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here edit" class="edit btn btn-success btn-sm">Edit</a> <a href="disable-cv/'.base64_encode($data->cv_id).'" title="Click here to disable brand" class="delete btn btn-success btn-sm">Disable</a> <a href="duplicate-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to duplicate" class="edit btn btn-success btn-sm">Duplicate</a>';
                            $actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to edit" class="edit btn btn-success btn-sm">Edit</a> <a href="disable-cv/'.base64_encode($data->cv_id).'" title="Click here to disable brand" class="delete btn btn-success btn-sm">Disable</a> <span class="btn btn-success btn-sm" onclick=cvPublishUnpublishConfirmationModal("'.$data->cv_id.'","unpublish")>Unpublish</span> <a href="duplicate-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to duplicate" class="edit btn btn-success btn-sm">Duplicate</a>';
                        }
                    }
                    else
                    {
                        //$actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here edit" class="edit btn btn-success btn-sm">Edit</a> <a href="enable-cv/'.base64_encode($data->cv_id).'" title="Click here to enable brand" class="edit btn btn-success btn-sm">Enable</a>';
                        $actionBtn = '<a href="enable-cv/'.base64_encode($data->cv_id).'" title="Click here to enable brand" class="edit btn btn-success btn-sm">Enable</a> <a href="duplicate-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to duplicate" class="edit btn btn-success btn-sm">Duplicate</a>';
                        //  if($data->status == '0')
                        // {
                        //     //$actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here edit" class="edit btn btn-success btn-sm">Edit</a> <a href="enable-cv/'.base64_encode($data->cv_id).'" title="Click here to enable brand" class="edit btn btn-success btn-sm">Enable</a> <a href="duplicate-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to duplicate" class="edit btn btn-success btn-sm">Duplicate</a>';
                        //     $actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here edit" class="edit btn btn-success btn-sm">Edit</a> <a href="enable-cv/'.base64_encode($data->cv_id).'" title="Click here to enable brand" class="edit btn btn-success btn-sm">Enable</a><span onclick=cvPublishUnpublishConfirmationModal("'.$data->cv_id.'","publish")>Publish</span>';
                        // }
                        // else
                        // {
                        //     //$actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here edit" class="edit btn btn-success btn-sm">Edit</a> <a href="enable-cv/'.base64_encode($data->cv_id).'" title="Click here to enable brand" class="edit btn btn-success btn-sm">Enable</a> <a href="duplicate-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to duplicate" class="edit btn btn-success btn-sm">Duplicate</a>';
                        //     $actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here edit" class="edit btn btn-success btn-sm">Edit</a> <a href="enable-cv/'.base64_encode($data->cv_id).'" title="Click here to enable brand" class="edit btn btn-success btn-sm">Enable</a><span onclick=cvPublishUnpublishConfirmationModal("'.$data->cv_id.'","unpublish")>Publish</span>';
                        // }
                    }
                    return $actionBtn;
                })
                ->filter(function ($instance) use ($request) {
                    if ($request->get('status') == 'Disabled') {
                        $instance->where('is_active', 1);
                    }
                    else
                    {
                        if($request->get('status') == 'Published')
                        {
                            $instance->where('status', 1);
                        }
                        if($request->get('status') == 'Unpublished')
                        {
                            $instance->where('status', 0);
                        }
                    }
                    if (!empty($request->get('search'))) {
                        $instance->where(function($w) use($request){
                           $search = $request->get('search');
                           $w->orWhere('cv_name', 'LIKE', "%$search%");
                       });
                   }
                })
                ->rawColumns(['action'])
                ->setRowId(function($data){
                    return $data->cv_id;
                })
                ->make(true);
        } */

        if ($request->ajax()) {
            $data = DB::table('tbl_cvs')
            ->join('tbl_industry', 'tbl_cvs.industry_id', '=', 'tbl_industry.industry_id')
            ->leftjoin('tbl_sub_industry', 'tbl_cvs.sub_industry_id', '=', 'tbl_sub_industry.sub_industry_id')
            ->select('tbl_cvs.*','tbl_industry.industry_name','tbl_sub_industry.sub_industry_name')
            //->where('tbl_cvs.sub_industry_id', null)
            //->orderBy('tbl_cvs.cv_id', 'desc')->latest();
            ->orderBy('tbl_cvs.cv_id', 'desc')->get();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('cv_date', function ($data) {
                    $year = explode("-",$data->cv_date)[1];
                    $month = explode("-",$data->cv_date)[0];
                    return $year."-".$month;
                })
                // ->editColumn('cv_date', function ($data) {

                //     return [
                //        'display' => e($data->cv_date),
                //        'timestamp' => strtotime("01-".$data->cv_date)
                //     ];
                //  })
                // ->editColumn('cv_date', function ($data) {
                //     $year = explode("-",$data->cv_date)[1];
                //     $month = explode("-",$data->cv_date)[0];
                //     $datec=date_create("01-".$data->cv_date);
                //     $ffgfhjghadhjsa =  date_format($datec,"Y/m/d");
                //     return [
                //     'display' => $data->cv_date,
                //     'timestamp' => $ffgfhjghadhjsa
                //     ];
                // })
                // ->editColumn('cv_date', function ($data) {
                //     $datec = date_create("01-" . $data->cv_date);
                //     $timestamp = date_format($datec, "U"); // Get the Unix timestamp
                //     return $timestamp;
                // })
                // ->addColumn('cv_date', function ($data) {
                //     // Format the "date" column as needed
                //     $cvdate = "01-".$data->cv_date;
                //     return date('Y/m/d', strtotime($cvdate));
                // })
                // ->rawColumns(['cv_date'])
                // ->orderColumn('cv_date', 'cv_date $1')

                //->orderColumn('cv_date', 'YEAR(cv_date) $1')
                // ->addColumn('cv_date', function($data){
                //     $year = explode("-",$data->cv_date)[1];
                //     return (int) $year;
                // })
                // ->rawColumns(['cv_date'])
                // ->orderColumn('cv_date', 'cv_date $1')
                ->addColumn('parent', function($data){
                    $parent_cv_year = DB::table('tbl_cvs')->where('cv_id','=',$data->parent_id)->first();
                    if($parent_cv_year !='')
                    {
                        $parent = $data->parent." ".explode("-",$parent_cv_year->cv_date)[1];
                    }
                    else
                    {
                        $parent = '';
                    }
                    return $parent;
                })
                // ->rawColumns(['parent'])
                // ->orderColumn('parent', 'parent $1')
                ->addColumn('industry', function($data){
                    /* $industry = DB::table('tbl_industry')->where('industry_id','=',$data->industry_id)->first();
                    if($industry !='')
                    {
                        $industry_name = $industry->industry_name;
                    }
                    else
                    {
                        $industry_name = '';
                    } */
                    $industry_name = $data->industry_name;
                    return $industry_name;
                })
                // ->rawColumns(['industry'])
                // ->orderColumn('industry', 'tbl_industry.industry_name $1')
                ->addColumn('sub_industry', function($data){
                    /* $sub_industry = DB::table('tbl_sub_industry')->where('sub_industry_id','=',$data->sub_industry_id)->first();
                    if($sub_industry !='')
                    {
                        $sub_industry_name = $sub_industry->sub_industry_name;
                    }
                    else
                    {
                        $sub_industry_name = '';
                    } */
                    $sub_industry_name = $data->sub_industry_name;
                    return $sub_industry_name;
                })
                // ->rawColumns(['sub_industry'])
                // ->orderColumn('sub_industry', 'tbl_sub_industry.sub_industry_name $1')
                ->addColumn('md_flag', function($data){
                    if($data->md_flag == null || $data->md_flag == '' || $data->md_flag == '-')
                    {
                        $md_flag = '-';
                    }
                    else
                    {
                        $md_flag = $data->md_flag;
                    }
                    return $md_flag;
                })
                // ->rawColumns(['md_flag'])
                // ->orderColumn('md_flag', 'md_flag $1')
                ->addColumn('action', function($data){
                    if($data->is_active == '0')
                    {
                        if($data->status == '0')
                        {
                            //$actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here edit" class="edit btn btn-success btn-sm">Edit</a> <a href="disable-cv/'.base64_encode($data->cv_id).'" title="Click here to disable brand" class="delete btn btn-success btn-sm">Disable</a> <a href="duplicate-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to duplicate" class="edit btn btn-success btn-sm">Duplicate</a>';
                            $actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to edit" class="edit btn btn-success btn-sm">Edit</a> <a href="disable-cv/'.base64_encode($data->cv_id).'" title="Click here to disable brand" class="delete btn btn-success btn-sm">Disable</a> <span class="btn btn-success btn-sm" onclick=previewCV("'.$data->cv_id.'","publish")>Preview & Publish</span> <a href="duplicate-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to duplicate" class="edit btn btn-success btn-sm">Duplicate</a>';
                        }
                        else
                        {
                            //$actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here edit" class="edit btn btn-success btn-sm">Edit</a> <a href="disable-cv/'.base64_encode($data->cv_id).'" title="Click here to disable brand" class="delete btn btn-success btn-sm">Disable</a> <a href="duplicate-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to duplicate" class="edit btn btn-success btn-sm">Duplicate</a>';
                            $actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to edit" class="edit btn btn-success btn-sm">Edit</a> <a href="disable-cv/'.base64_encode($data->cv_id).'" title="Click here to disable brand" class="delete btn btn-success btn-sm">Disable</a> <span class="btn btn-success btn-sm" onclick=cvPublishUnpublishConfirmationModal("'.$data->cv_id.'","unpublish")>Unpublish</span> <a href="duplicate-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to duplicate" class="edit btn btn-success btn-sm">Duplicate</a>';
                        }
                    }
                    else
                    {
                        //$actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here edit" class="edit btn btn-success btn-sm">Edit</a> <a href="enable-cv/'.base64_encode($data->cv_id).'" title="Click here to enable brand" class="edit btn btn-success btn-sm">Enable</a>';
                        $actionBtn = '<a href="enable-cv/'.base64_encode($data->cv_id).'" title="Click here to enable brand" class="edit btn btn-success btn-sm">Enable</a> <a href="duplicate-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to duplicate" class="edit btn btn-success btn-sm">Duplicate</a>';
                        //  if($data->status == '0')
                        // {
                        //     //$actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here edit" class="edit btn btn-success btn-sm">Edit</a> <a href="enable-cv/'.base64_encode($data->cv_id).'" title="Click here to enable brand" class="edit btn btn-success btn-sm">Enable</a> <a href="duplicate-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to duplicate" class="edit btn btn-success btn-sm">Duplicate</a>';
                        //     $actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here edit" class="edit btn btn-success btn-sm">Edit</a> <a href="enable-cv/'.base64_encode($data->cv_id).'" title="Click here to enable brand" class="edit btn btn-success btn-sm">Enable</a><span onclick=cvPublishUnpublishConfirmationModal("'.$data->cv_id.'","publish")>Publish</span>';
                        // }
                        // else
                        // {
                        //     //$actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here edit" class="edit btn btn-success btn-sm">Edit</a> <a href="enable-cv/'.base64_encode($data->cv_id).'" title="Click here to enable brand" class="edit btn btn-success btn-sm">Enable</a> <a href="duplicate-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to duplicate" class="edit btn btn-success btn-sm">Duplicate</a>';
                        //     $actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here edit" class="edit btn btn-success btn-sm">Edit</a> <a href="enable-cv/'.base64_encode($data->cv_id).'" title="Click here to enable brand" class="edit btn btn-success btn-sm">Enable</a><span onclick=cvPublishUnpublishConfirmationModal("'.$data->cv_id.'","unpublish")>Publish</span>';
                        // }
                    }
                    return $actionBtn;
                })
                ->filter(function ($instance) use ($request) {
                    if ($request->get('status') == 'Disabled') {
                        $instance->where('tbl_cvs.is_active', 1);
                    }
                    else
                    {
                        if($request->get('status') == 'Published')
                        {
                            $instance->where('tbl_cvs.status', 1);
                        }
                        if($request->get('status') == 'Unpublished')
                        {
                            $instance->where('tbl_cvs.status', 0);
                        }
                    }
                    if (!empty($request->get('search'))) {
                        $instance->where(function($w) use($request){
                           $search = $request->get('search');
                           $w->orWhere('cv_name', 'LIKE', "%$search%")
                           ->orWhere('industry_name', 'LIKE', "%$search%")
                           ->orWhere('sub_industry_name', 'LIKE', "%$search%");
                       });
                   }
                })
                ->rawColumns(['action'])
                ->setRowId(function($data){
                    return $data->cv_id;
                })
                ->make(true);
        }
    }

    function enableCv($id)
    {
        $update_cv = DB::table('tbl_cvs')
                            ->where('cv_id', base64_decode($id))
                            ->update(['is_active' => '0', 'edited_by'=>session('LoggedUser')]);

        if($update_cv)
        {
            return back()->with('success','Brand Sonic Radar enabled successfully');
        }
        else
        {
            $error_data = "Something went wrong while enabling cv-".base64_decode($id);
            ErrorMailSender::sendErrorMail($error_data);
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }

    function disableCv($id)
    {
        // echo $id;
        // exit;
        $update_cv = DB::table('tbl_cvs')
                            ->where('cv_id', base64_decode($id))
                            ->update(['is_active' => '1', 'edited_by'=>session('LoggedUser')]);

        if($update_cv)
        {
            DB::table('tbl_cvs')->where('parent_id', base64_decode($id))->update(['parent_id' => null, 'parent'=>null]);
            return back()->with('success','Brand Sonic Radar disabled successfully');
        }
        else
        {
            $error_data = "Something went wrong while disabling cv-".base64_decode($id);
            ErrorMailSender::sendErrorMail($error_data);
            return back()->with('fail', 'Something went wrong, please try again!');
        }


    }

    function editBrandCv($id)
    {
        $cvid = base64_decode($id);
        //$graph_status = DB::select(DB::raw("SELECT tbl_cvs.cv_id FROM tbl_cvs WHERE EXISTS (select cv_id from tbl_cv_block_16_genre_graph_data where tbl_cv_block_16_genre_graph_data.cv_id = ".base64_decode($id).") OR EXISTS (select cv_id from tbl_cv_block_17_genre_graph_data where tbl_cv_block_17_genre_graph_data.cv_id = ".base64_decode($id).") OR EXISTS (select cv_id from tbl_cv_block_18_genre_graph_data where tbl_cv_block_18_genre_graph_data.cv_id = ".base64_decode($id).") OR EXISTS (select cv_id from tbl_cv_block_19_genre_graph_data where tbl_cv_block_19_genre_graph_data.cv_id = ".base64_decode($id).")"));
        // $graph_status = DB::select(DB::raw("SELECT 'tbl_cv_block_16_genre_graph_data' AS `table`, cv_id FROM tbl_cv_block_16_genre_graph_data WHERE cv_id = $cvid UNION SELECT 'tbl_cv_block_17_genre_graph_data', cv_id FROM tbl_cv_block_17_genre_graph_data WHERE cv_id = $cvid UNION SELECT 'tbl_cv_block_18_genre_graph_data', cv_id FROM tbl_cv_block_18_genre_graph_data WHERE cv_id = $cvid UNION SELECT 'tbl_cv_block_19_genre_graph_data', cv_id FROM tbl_cv_block_19_genre_graph_data WHERE cv_id = $cvid"));
        $graph_status = DB::select(DB::raw("SELECT 'tbl_social_media_yt_genre_graph_data' AS `table`, cv_id FROM tbl_social_media_yt_genre_graph_data WHERE cv_id = $cvid UNION SELECT 'tbl_social_media_ig_genre_graph_data', cv_id FROM tbl_social_media_ig_genre_graph_data WHERE cv_id = $cvid UNION SELECT 'tbl_social_media_tt_genre_graph_data', cv_id FROM tbl_social_media_tt_genre_graph_data WHERE cv_id = $cvid UNION SELECT 'tbl_social_media_twt_genre_graph_data', cv_id FROM tbl_social_media_twt_genre_graph_data WHERE cv_id = $cvid"));
        //echo 'edit brand cv section';
        if(count($graph_status) == 0)
        {
            $graph_status = '';
        }
        $cv_data = DB::table('tbl_cvs')->where('cv_id', '=', base64_decode($id))->first();
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
        $cv_parent_data = DB::table('tbl_cvs')->where('is_active', '=', 0)->get();
        $cv_parent_ids = DB::table('tbl_cvs')->where('parent_id', '!=', null)->where('parent_id', '!=', '')->where('is_active', '=', 0)->get();
        $industry_data = DB::table('tbl_industry')->where('is_active', '=', 0)->get();
        $sub_industry_data =  DB::table('tbl_sub_industry')->where('is_active', '=', 0)->get();
        $sub_ind_parent_ids = DB::table('tbl_cvs')->select('industry_id')->where('sub_industry_id', '!=', null)->where('sub_industry_id', '!=', '')->where('sub_industry_id', '!=', 0)->where('status', '=', 1)->where('is_active', '=', 0)->distinct()->get();
        $music_taste_data = DB::table('tbl_music_taste')->where('is_active', '=', 0)->get();
        $experience_data = DB::table('tbl_experience')->where('is_active', '=', 0)->orderBy('display_order', 'asc')->get();
        $qualitative_data = DB::table('tbl_qualitative')->where('is_active', '=', 0)->get();
        $footer_template_data = DB::table('tbl_footer_template')->where('is_active', '=', 0)->get();
        $cv_block_2_data = DB::table('tbl_best_in_audio_brands')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->first();
        $cv_block_3_data = DB::table('tbl_cv_block_3_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_4_data = DB::table('tbl_cv_block_4_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->first();
        $cv_block_5_data = DB::table('tbl_cv_block_5_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_6_data = DB::table('tbl_cv_block_6_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_7_data = DB::table('tbl_cv_block_7_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_8_data = DB::table('tbl_cv_block_8_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_9_data = DB::table('tbl_cv_block_9_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_10_data = DB::table('tbl_cv_block_10_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_11_data = DB::table('tbl_cv_block_11_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_12_data = DB::table('tbl_cv_block_12_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_13_qdata = DB::table('tbl_cv_block_13_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_14_data = DB::table('tbl_cv_block_14_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_15_data = DB::table('tbl_cv_block_15_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_16_data = DB::table('tbl_cv_block_16_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_17_data = DB::table('tbl_cv_block_17_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_18_data = DB::table('tbl_cv_block_18_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_19_data = DB::table('tbl_cv_block_19_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();

        /* $cv_genre_aggr_graph_values_data = DB::table('tbl_genre_aggr_graph_data')->select('ambient','blues','classical','country','electronicDance','folk','indieAlternative','jazz','latin','metal','pop','punk','rapHipHop','reggae','rnb','rock','singerSongwriter')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->first();
        $cv_genre_aggr_graph_values_arr = (array)$cv_genre_aggr_graph_values_data;
        $cv_genre_aggr_graph_values_arr1 = (array)$cv_genre_aggr_graph_values_data;
        rsort($cv_genre_aggr_graph_values_arr);
        $top3 = array_slice($cv_genre_aggr_graph_values_arr, 0, 3);
        $top_3_genre = array();

        foreach ($top3 as $key => $val) {
            //echo "key-".$key."----------- val-".$val."<br>";
            $key = array_search ($val, $cv_genre_aggr_graph_values_arr1);
            unset($cv_genre_aggr_graph_values_arr1[$key]);
            // $top_3_genre[$key] = $val;
            array_push($top_3_genre,$key."_".$val);
        }

        if(count($top_3_genre)==0)
        {
            $top_3_genre = '';
        } */
        $avg_genre_data = DB::table('tbl_social_media_aggr_genre_graph_data')
        ->where('cv_id', '=', base64_decode($id))
        ->where('is_active', '=', 0)
        ->get();
        //print_r($avg_genre_data);
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


        if(count($cv_block_3_data)==0)
        {
            $cv_block_3_data = '';
        }
        if(count($cv_block_5_data)==0)
        {
            $cv_block_5_data = '';
        }
        if(count($cv_block_6_data)==0)
        {
            $cv_block_6_data = '';
        }
        if(count($cv_block_7_data)==0)
        {
            $cv_block_7_data = '';
        }
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
        }
        if(count($cv_block_11_data)==0)
        {
            $cv_block_11_data = '';
        }
        if(count($cv_block_12_data)==0)
        {
            $cv_block_12_data = '';
        }

        if(count($cv_block_13_qdata)==0)
        {
            $cv_block_13_data = [];
        }
        else
        {
            $cv_block_13_data = [];
            foreach($cv_block_13_qdata as $cb13data)
            {
                $cv_block_13_data[$cb13data->b13_name_id] = $cb13data->b13_id."#_#".$cb13data->b13_number;
            }
        }
        // print_r($experience_data);
        // print_r($cv_block_13_data); exit;
        if(count($cv_block_14_data)==0)
        {
            $cv_block_14_data = '';
        }
        if(count($cv_block_15_data)==0)
        {
            $cv_block_15_data = '';
        }
        if(count($cv_block_16_data)==0)
        {
            $cv_block_16_data = '';
        }
        if(count($cv_block_17_data)==0)
        {
            $cv_block_17_data = '';
        }
        if(count($cv_block_18_data)==0)
        {
            $cv_block_18_data = '';
        }
        if(count($cv_block_19_data)==0)
        {
            $cv_block_19_data = '';
        }
        if(count($cv_parent_ids)==0)
        {
            $cv_parent_ids_array = [];
        }
        else
        {
            $cv_parent_ids_array = [];
            foreach($cv_parent_ids as $pid)
            {
                if($pid->parent_id!=$cv_data->parent_id)
                {
                    array_push($cv_parent_ids_array,$pid->parent_id);
                }
            }
        }
        $sub_ind_parent_ids_arr = [];
        if(count($sub_ind_parent_ids)==0)
        {
            $sub_ind_parent_ids_arr = [];
        }
        else
        {
            foreach($sub_ind_parent_ids as $sipdata)
            {
                array_push($sub_ind_parent_ids_arr, $sipdata->industry_id);
            }
        }
        //print_r($cv_block_8_data);exit;
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        /* $experience_data = [];
        foreach($experience_qdata as $ed)
        {
            $experience_data[$ed->experience_id] = $ed->experience_name;
        } */

        return view('backend.views.edit_brand_cv', ['cv_data'=>$cv_data, 'parent_cv'=>$parent_cv, 'cv_parent_data'=>$cv_parent_data, 'cv_parent_ids_array'=>$cv_parent_ids_array, 'industry_data'=>$industry_data, 'sub_industry_data'=>$sub_industry_data, 'sub_ind_parent_ids'=>$sub_ind_parent_ids_arr, 'parent_cv_overall_ranking'=>$parent_cv_overall_ranking, 'top_3_genre'=>$top_3_genre, 'music_taste_data'=>$music_taste_data, 'experience_data'=>$experience_data, 'qualitative_data'=>$qualitative_data, 'footer_template_data'=>$footer_template_data, 'cv_block_2_data'=>$cv_block_2_data, 'cv_block_3_data'=>$cv_block_3_data, 'cv_block_4_data'=>$cv_block_4_data, 'cv_block_5_data'=>$cv_block_5_data, 'cv_block_6_data'=>$cv_block_6_data, 'cv_block_7_data'=>$cv_block_7_data, 'cv_block_8_data'=>$cv_block_8_data, 'cv_block_9_data'=>$cv_block_9_data, 'cv_block_10_data'=>$cv_block_10_data, 'cv_block_11_data'=>$cv_block_11_data, 'cv_block_12_data'=>$cv_block_12_data, 'cv_block_13_data'=>$cv_block_13_data, 'cv_block_14_data'=>$cv_block_14_data, 'cv_block_15_data'=>$cv_block_15_data, 'cv_block_16_data'=>$cv_block_16_data, 'cv_block_17_data'=>$cv_block_17_data, 'cv_block_18_data'=>$cv_block_18_data, 'cv_block_19_data'=>$cv_block_19_data,'cvs_year_data'=>$cvs_year,'graph_status'=>$graph_status]);
        //return view('backend.views.edit_brand_cv', ['cv_data'=>$cv_data, 'cv_parent_data'=>$cv_parent_data, 'cv_parent_ids_array'=>$cv_parent_ids_array, 'industry_data'=>$industry_data, 'music_taste_data'=>$music_taste_data, 'experience_data'=>$experience_data, 'qualitative_data'=>$qualitative_data, 'footer_template_data'=>$footer_template_data, 'cv_block_2_data'=>$cv_block_2_data, 'cv_block_3_data'=>$cv_block_3_data, 'cv_block_4_data'=>$cv_block_4_data, 'cv_block_5_data'=>$cv_block_5_data, 'cv_block_6_data'=>$cv_block_6_data, 'cv_block_7_data'=>$cv_block_7_data, 'cv_block_9_data'=>$cv_block_9_data, 'cv_block_10_data'=>$cv_block_10_data, 'cv_block_11_data'=>$cv_block_11_data, 'cv_block_12_data'=>$cv_block_12_data, 'cv_block_13_data'=>$cv_block_13_data, 'cv_block_14_data'=>$cv_block_14_data, 'cv_block_15_data'=>$cv_block_15_data]);
    }

    function updateBrandCv(Request $request)
    {
        //return $request->input();
        /* $efb_count = $request->efb_count;
        $section_13_file_name = '';
        for($i=0; $i<$efb_count; $i++)
        {
            $efb_name_id = "cv_efb_name_id_".$i;
            $efb_number = "cv_efb_number_".$i;
            $efb_id = "cv_efb_id_".$i;

            echo "efb_name_id->".$efb_name_id." | efb_number->".$efb_number." || efb_id->".$efb_id." ||| ".$request->$efb_id;

            $block_13_update_data = [
                'b13_title' => $request->section_13_title,
                //'b13_bg_color' => $request->section_13_bg_color,
                'b13_name_id' => $request->$efb_name_id,
                'b13_number' => $request->$efb_number,
                'b13_bg_image' => $section_13_file_name,
                'cv_id' => $request->cv_id,
                'is_active' => 0,
                'edited_by' => session('LoggedUser')
            ];

            $block_13_insert_data = [
                'b13_title' => $request->section_13_title,
                //'b13_bg_color' => $request->section_13_bg_color,
                'b13_name_id' => $request->$efb_name_id,
                'b13_number' => $request->$efb_number,
                'b13_bg_image' => $section_13_file_name,
                'cv_id' => $request->cv_id,
                'is_active' => 0,
                'created_by' => session('LoggedUser')
            ];

            $chk_data = DB::table('tbl_cv_block_13_data')->where('b13_id', $request->$efb_id)->where('cv_id', $request->cv_id)->first();
            if($chk_data != '')
            {
                print_r($block_13_insert_data);
                echo "<br><br>";
            }
            else
            {
                print_r($block_13_update_data);
                echo "<br><br>";
            }
        }

        return $request->input(); */
        $request->validate([
            "cv_type"=>'required',
            "cv_name"=>'required',
            "cv_date"=>'required',
            "cv_id"=>'required'
        ]);

        if($request->industry_name == '' || $request->industry_name == null)
        {
            $industry_name_id = $request->old_industry_name;
        }
        else
        {
            $industry_name_id = $request->industry_name;
        }
        if($request->industry_name == $request->old_industry_name)
        {
            if($request->sub_industry_name == '' || $request->sub_industry_name == null)
            {
                $sub_industry_name_id = $request->old_sub_industry_name;
            }
            else
            {
                $sub_industry_name_id = $request->sub_industry_name;
            }
        }
        else
        {
            $sub_industry_name_id = $request->sub_industry_name;
        }

        if($request->parent_cv_name != '' || $request->parent_cv_name != '0#_#sel')
        {

            $parent_data = Str::of($request->parent_cv_name)->split('/#_#/');
            $parent_id = ($parent_data[0] != 0 || $parent_data[0] != '0') ? $parent_data[0] : null;
            $parent_name = ($parent_data[1] != 'sel') ? $parent_data[1] : null;
        }
        else
        {
            $parent_data = Str::of($request->old_parent_cv_name)->split('/#_#/');
            $parent_id = ($parent_data[0] != 0 || $parent_data[0] != '0') ? $parent_data[0] : null;
            $parent_name = ($parent_data[1] != 'sel') ? $parent_data[1] : null;
        }

        if($request->footer_template_name == '' || $request->footer_template_name == null)
        {
            $footer_template_name_id = $request->old_footer_template_name;
        }
        else
        {
            $footer_template_name_id = $request->footer_template_name;
        }

        if($request->missing_data_flag_name == '' || $request->missing_data_flag_name == null)
        {
            $missing_data_flag_name = $request->old_missing_data_flag_name;
        }
        else
        {
            $missing_data_flag_name = $request->missing_data_flag_name;
        }

        if($request->hasfile('cv_logo'))
        {
            //$request->validate(["cv_logo"=>'image|mimes:jpeg,png,jpg|max:2048']);

            /* $old_cv_data = DB::table('tbl_cvs')->where('cv_id', $request->cv_id)->first();
            if($old_cv_data->cv_name != $request->cv_name || $old_cv_data->cv_logo != $request->cv_logo)
            {
                $file_path = public_path('/images/cv_logos/').$old_cv_data->cv_name;
                unlink($file_path);
            }  */

            $image = $request->cv_logo;
            $img_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$request->cv_id, 'UTF-8').'.'.$image->getClientOriginalExtension());
            //print_r($image);
            //print_r($image->path());exit;
            $destinationPath = public_path('/images/cv_logos/thumbnail');
            $img = Image::make($image->path());
            $img->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$img_name);

            $destinationPath = public_path('/images/cv_logos/medium');
            $img = Image::make($image->path());
            $img->resize(600, 600, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$img_name);


            $destinationPath = public_path('/images/cv_banners/desktop');
            $img = Image::make($image->path());
            $img->resize(500, 500, function ($constraint1) {
                $constraint1->aspectRatio();
            });
            $img1 = Image::canvas(1600, 410, '#ffffff')->insert($img, 'center');
            $img1->save($destinationPath.'/'.$img_name);

            $destinationPath = public_path('/images/cv_banners/ipad');
            $img = Image::make($image->path());
            $img->resize(400, 400, function ($constraint1) {
                $constraint1->aspectRatio();
            });
            $img1 = Image::canvas(1024, 262, '#ffffff')->insert($img, 'center');
            $img1->save($destinationPath.'/'.$img_name);

            $destinationPath = public_path('/images/cv_banners/mobile');
            $img = Image::make($image->path());
            $img->resize(400, 400, function ($constraint1) {
                $constraint1->aspectRatio();
            });
            $img1 = Image::canvas(640, 260, '#ffffff')->insert($img, 'center');
            $img1->save($destinationPath.'/'.$img_name);

            if($image->move(public_path('images/cv_logos/original'), $img_name))
            {
                DB::table('tbl_cvs')->where('cv_id', $request->cv_id)->update(['cv_logo' => '']);
                $update_query =  DB::table('tbl_cvs')
                            ->where('cv_id', $request->cv_id)
                            ->update(['type' => $request->cv_type,'parent_id' => $parent_id,'parent' => $parent_name,'cv_name' => $request->cv_name,'cv_logo' => $img_name,'cv_date' => $request->cv_date,'cv_year' => explode('-',$request->cv_date)[1],'industry_id'=>$industry_name_id,'sub_industry_id'=>$sub_industry_name_id,'footer_template_id'=>$footer_template_name_id, 'edited_by'=>session('LoggedUser'), 'md_flag'=>$missing_data_flag_name]);
                if($update_query)
                {
                    DB::table('tbl_cvs')->where('cv_id', $request->cv_id)->update(['cv_banner_desktop' => '']);
                    $update_cv_banner_desktop_query =  DB::table('tbl_cvs')
                                ->where('cv_id', $request->cv_id)
                                ->update(['cv_banner_desktop' => $img_name]);

                    DB::table('tbl_cvs')->where('cv_id', $request->cv_id)->update(['cv_banner_ipad' => '']);
                    $update_cv_banner_ipad_query =  DB::table('tbl_cvs')
                                ->where('cv_id', $request->cv_id)
                                ->update(['cv_banner_ipad' => $img_name]);

                    DB::table('tbl_cvs')->where('cv_id', $request->cv_id)->update(['cv_banner_mobile' => '']);
                    $update_cv_banner_mobile_query =  DB::table('tbl_cvs')
                                ->where('cv_id', $request->cv_id)
                                ->update(['cv_banner_mobile' => $img_name]);
                }
            }
        }
        else
        {
            DB::table('tbl_cvs')->where('cv_id', $request->cv_id)->update(['is_active' => '1']);
            $update_query =  DB::table('tbl_cvs')
                            ->where('cv_id', $request->cv_id)
                            ->update(['type' => $request->cv_type,'parent_id' => $parent_id,'parent' => $parent_name,'cv_name' => $request->cv_name,'cv_date' => $request->cv_date,'cv_year' => explode('-',$request->cv_date)[1],'industry_id'=>$industry_name_id,'sub_industry_id'=>$sub_industry_name_id,'footer_template_id'=>$footer_template_name_id,'is_active' => '0', 'edited_by'=>session('LoggedUser'), 'md_flag'=>$missing_data_flag_name]);
        }

        /* if($request->hasfile('cv_banner_desktop'))
        {
            //$request->validate(["cv_banner_desktop"=>'image|mimes:jpeg,png,jpg|max:2048']);

            $image = $request->cv_banner_desktop;
            $img_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$request->cv_id, 'UTF-8').'.'.$image->getClientOriginalExtension());

            if($image->move(public_path('images/cv_banners/desktop'), $img_name))
            {
                DB::table('tbl_cvs')->where('cv_id', $request->cv_id)->update(['cv_banner_desktop' => '']);
                $update_query =  DB::table('tbl_cvs')
                            ->where('cv_id', $request->cv_id)
                            ->update(['type' => $request->cv_type,'parent_id' => $parent_id,'parent' => $parent_name,'cv_name' => $request->cv_name,'cv_banner_desktop' => $img_name,'cv_date' => $request->cv_date,'cv_year' => explode('-',$request->cv_date)[1],'industry_id'=>$industry_name_id,'footer_template_id'=>$footer_template_name_id, 'edited_by'=>session('LoggedUser'), 'md_flag'=>$missing_data_flag_name]);
            }
        }

        if($request->hasfile('cv_banner_ipad'))
        {
            //$request->validate(["cv_banner_ipad"=>'image|mimes:jpeg,png,jpg|max:2048']);

            $image = $request->cv_banner_ipad;
            $img_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$request->cv_id, 'UTF-8').'.'.$image->getClientOriginalExtension());

            if($image->move(public_path('images/cv_banners/ipad'), $img_name))
            {
                DB::table('tbl_cvs')->where('cv_id', $request->cv_id)->update(['cv_banner_ipad' => '']);
                $update_query =  DB::table('tbl_cvs')
                            ->where('cv_id', $request->cv_id)
                            ->update(['type' => $request->cv_type,'parent_id' => $parent_id,'parent' => $parent_name,'cv_name' => $request->cv_name,'cv_banner_ipad' => $img_name,'cv_date' => $request->cv_date,'cv_year' => explode('-',$request->cv_date)[1],'industry_id'=>$industry_name_id,'footer_template_id'=>$footer_template_name_id, 'edited_by'=>session('LoggedUser'), 'md_flag'=>$missing_data_flag_name]);
            }
        }

        if($request->hasfile('cv_banner_mobile'))
        {
            //$request->validate(["cv_banner_mobile"=>'image|mimes:jpeg,png,jpg|max:2048']);

            $image = $request->cv_banner_mobile;
            $img_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$request->cv_id, 'UTF-8').'.'.$image->getClientOriginalExtension());

            if($image->move(public_path('images/cv_banners/mobile'), $img_name))
            {
                DB::table('tbl_cvs')->where('cv_id', $request->cv_id)->update(['cv_banner_mobile' => '']);
                $update_query =  DB::table('tbl_cvs')
                            ->where('cv_id', $request->cv_id)
                            ->update(['type' => $request->cv_type,'parent_id' => $parent_id,'parent' => $parent_name,'cv_name' => $request->cv_name,'cv_banner_mobile' => $img_name,'cv_date' => $request->cv_date,'cv_year' => explode('-',$request->cv_date)[1],'industry_id'=>$industry_name_id,'footer_template_id'=>$footer_template_name_id, 'edited_by'=>session('LoggedUser'), 'md_flag'=>$missing_data_flag_name]);
            }
        } */

        if($update_query)
        {
            /* $block_2_data = [
                'b2_id' => $request->section_2_id,
                'b2_title' => $request->section_2_title,
                'b2_value' => $request->ranking,
                'cv_id' => $request->cv_id,
                'created_by' => session('LoggedUser'),
                'edited_by' => session('LoggedUser')
            ];

            try
            {
                DB::table('tbl_cv_block_2_data')->upsert($block_2_data, ['b2_id','cv_id'], ['b2_title','b2_value','cv_id','created_by','edited_by']);
                //return redirect('brand-cvs')->with('success','Brand CV data updated successfully');
            }
            catch(\Illuminate\Database\QueryException $ex)
            {
                //return ['error' => 'error update user'];
                return back()->with('fail', 'Something went wrong while updating section 2 data, please try again!');
            } */

            /* DB::table('tbl_cv_block_3_data')->where('cv_id', $request->cv_id)->update(['is_active' => 1,'edited_by' => session('LoggedUser')]);
            $cv_music_taste_name_ids_array = explode(',' , $request->cv_music_taste_name_ids);
            //print_r($cv_music_taste_name_ids_array); echo $request->cv_music_taste_name_ids; exit;
            if($request->cv_music_taste_name_ids!='' && $request->cv_music_taste_name_ids != null)
            {
                for($i=0; $i<count($cv_music_taste_name_ids_array); $i++)
                {

                    $block_3_update_data = [
                        'b3_title' => $request->section_3_title,
                        'b3_title_id' => $cv_music_taste_name_ids_array[$i],
                        'cv_id' => $request->cv_id,
                        'is_active' => 0,
                        'edited_by' => session('LoggedUser')
                    ];
                    $block_3_insert_data = [
                        'b3_title' => $request->section_3_title,
                        'b3_title_id' => $cv_music_taste_name_ids_array[$i],
                        'cv_id' => $request->cv_id,
                        'is_active' => 0,
                        'created_by' => session('LoggedUser')
                    ];

                    $chk_data = DB::table('tbl_cv_block_3_data')->where('b3_title_id', $cv_music_taste_name_ids_array[$i])->where('cv_id', $request->cv_id)->first();
                    if($chk_data != '')
                    {
                        try
                        {
                            DB::table('tbl_cv_block_3_data')->where('b3_title_id', $cv_music_taste_name_ids_array[$i])->where('cv_id', $request->cv_id)->update($block_3_update_data);
                        }
                        catch(\Illuminate\Database\QueryException $ex)
                        {
                            //return ['error' => 'error update user'];
                            return back()->with('fail', 'Something went wrong while updating section 3 data, please try again!');
                        }
                    }
                    else
                    {
                        try
                        {
                            DB::table('tbl_cv_block_3_data')->insert($block_3_insert_data);
                        }
                        catch(\Illuminate\Database\QueryException $ex)
                        {
                            //return ['error' => 'error update user'];
                            return back()->with('fail', 'Something went wrong while updating section 3 data, please try again!');
                        }
                    }
                }
            } */

            $block_4_data = [
                'b4_id' => $request->section_4_id,
                'b4_title' => $request->section_4_title,
                'b4_description' => $request->about_description,
                'b4_key_findings' => $request->about_key_findings,
                'cv_id' =>  $request->cv_id,
                'created_by' => session('LoggedUser'),
                'edited_by' => session('LoggedUser')
            ];

            try
            {
                DB::table('tbl_cv_block_4_data')->upsert($block_4_data, ['b4_id','cv_id'], ['b4_title','b4_description','b4_key_findings','cv_id','created_by','edited_by']);
                //return redirect('brand-cvs')->with('success','Brand CV data updated successfully');
            }
            catch(\Illuminate\Database\QueryException $ex)
            {
                //return ['error' => 'error update user'];
                return back()->with('fail', 'Something went wrong while updating section 4 data, please try again!');
            }

            $imgArray = GetSocialMediaIconsData::getSMData();
            $img_name = count($imgArray);
            $smDataCount = $request->smDataCount;
            DB::table('tbl_cv_block_5_data')->where('cv_id', $request->cv_id)->update(['is_active' => 1,'edited_by' => session('LoggedUser')]);
            for($i=0; $i<$smDataCount; $i++)
            {
                $smTrIcon = "smTrIcon_".$i;
                $smTrUrl = "smTrUrl_".$i;
                $smTrName = "smTrName_".$i;
                $smTrid = "smTrid_".$i;

                if(Str::contains($request->$smTrIcon,[';base64,']))
                {
                    $img_name++;
                    $image_parts = explode(";base64,", $request->$smTrIcon);
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type = $image_type_aux[1];
                    $image_base64 = base64_decode($image_parts[1]);
                    $file = public_path('images/social_media_icons/original').'/'.$img_name.'.'.$image_type;
                    file_put_contents($file, $image_base64);
                    $destinationPath = public_path('/images/social_media_icons');
                    $img = Image::make($file);
                    $img->resize(200, 200, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($destinationPath.'/'.$img_name.'.'.$image_type);

                    $b5_icon_name = $img_name.'.'.$image_type;
                }
                else
                {
                    $b5_icon_name = Str::of($request->$smTrIcon)->afterLast('/');
                }

                $block_5_data = [
                    'b5_id' => $request->$smTrid,
                    'b5_icon_name' => $b5_icon_name,
                    'b5_link' => $request->$smTrUrl,
                    'b5_link_name' => $request->$smTrName,
                    'cv_id' => $request->cv_id,
                    'is_active' => 0,
                    'created_by' => session('LoggedUser'),
                    'edited_by' => session('LoggedUser')
                ];

                try
                {
                    DB::table('tbl_cv_block_5_data')->upsert($block_5_data, ['b5_id','cv_id'], ['b5_icon_name','b5_link','b5_link_name','cv_id','is_active','created_by','edited_by']);
                }
                catch(\Illuminate\Database\QueryException $ex)
                {
                    //return ['error' => 'error update user'];
                    return back()->with('fail', 'Something went wrong while updating section 6 data, please try again!');
                }
            }

            /* if($request->hasfile('sonic_logo_audio_file'))
            {

                $file = $request->sonic_logo_audio_file;
                $file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$request->cv_id, 'UTF-8').'.'.$file->getClientOriginalExtension());
                $file->move(public_path('audios/cv_audios'), $file_name);
                $block_6_data = [
                    'b6_id' => $request->section_6_id,
                    'b6_title' => $request->section_6_title,
                    'b6_name' => $file_name,
                    'cv_id' => $request->cv_id,
                    'created_by' => session('LoggedUser'),
                    'edited_by' => session('LoggedUser')
                ];
            }
            else
            {
                $block_6_data = [
                    'b6_id' => $request->section_6_id,
                    'b6_title' => $request->section_6_title,
                    'cv_id' => $request->cv_id,
                    'created_by' => session('LoggedUser'),
                    'edited_by' => session('LoggedUser')
                ];
            }
            try
            {
                if($request->hasfile('sonic_logo_audio_file'))
                {
                    DB::table('tbl_cv_block_6_data')->upsert($block_6_data, ['b6_id','cv_id'], ['b6_title','b6_name','cv_id','created_by','edited_by']);
                    //return redirect('brand-cvs')->with('success','Brand CV data updated successfully');
                }
                else
                {
                    DB::table('tbl_cv_block_6_data')->upsert($block_6_data, ['b6_id','cv_id'], ['b6_title','cv_id','created_by','edited_by']);
                    //return redirect('brand-cvs')->with('success','Brand CV data updated successfully');
                }
            }
            catch(\Illuminate\Database\QueryException $ex)
            {
                //return ['error' => 'error update user'];
                return back()->with('fail', 'Something went wrong while updating section 6 data, please try again!');
            } */

            $sonic_logo_count = $request->sonic_logo_count;
            DB::table('tbl_cv_block_6_data')->where('cv_id', $request->cv_id)->update(['is_active' => 1,'edited_by' => session('LoggedUser')]);
            $inactive_mp3_data = DB::table('tbl_cv_block_6_data')->where('cv_id', '=', $request->cv_id)->get();
            $inactive_mp3_id_arr = [];
            foreach($inactive_mp3_data as $mp3data)
            {
                array_push($inactive_mp3_id_arr,$mp3data->b6_id);
            }
            for($i=0; $i<$sonic_logo_count; $i++)
            {
                $audio_title = "section_6_title_".$i;
                $audio_name = "sonic_logo_audio_file_".$i;
                $audio_id = "section_6_id_".$i;

                //echo "audio_title:".$request->$audio_title."<br>audio_name:".$request->$audio_name."<br>audio_id:".$request->$audio_id."<br>";
                try
                {
                    if($request->hasfile($audio_name))
                    {
                        if($request->$audio_name != '')
                        {
                            $file = $request->$audio_name;
                            $file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$request->cv_id, 'UTF-8')."_".$i.'.'.$file->getClientOriginalExtension());
                            $file->move(public_path('audios/cv_audios'), $file_name);
                            $block_6_data = [
                                'b6_id' => $request->$audio_id,
                                'b6_title' => $request->$audio_title,
                                'b6_name' => $file_name,
                                'cv_id' => $request->cv_id,
                                'is_active' => 0,
                                'created_by' => session('LoggedUser'),
                                'edited_by' => session('LoggedUser')
                            ];
                            DB::table('tbl_cv_block_6_data')->upsert($block_6_data, ['b6_id','cv_id'], ['b6_title','b6_name','cv_id','is_active','created_by','edited_by']);
                            //return redirect('brand-cvs')->with('success','Brand CV data updated successfully');
                        }
                    }
                    else
                    {
                        $audio_name = "old_sonic_logo_audio_file_".$i;
                        if($request->$audio_id != '' && $request->$audio_name != '')
                        {
                            $mp3_data = DB::table('tbl_cv_block_6_data')->where('cv_id', '=', $request->cv_id)->where('b6_id', '=', $request->$audio_id)->first();
                            $old_file_name = $mp3_data->b6_name;
                            if(substr_count($old_file_name,".")>1)
                            {
                                if(strpos($old_file_name, '.wav') !== false || strpos($old_file_name, '.WAV') !== false)
                                {
                                    $new_file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$request->cv_id, 'UTF-8')."_".$i.'.'.substr($old_file_name,strrpos($old_file_name,"mp3")));
                                }
                                elseif(strpos($old_file_name, '.aac') !== false || strpos($old_file_name, '.AAC') !== false)
                                {
                                    $new_file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$request->cv_id, 'UTF-8')."_".$i.'.'.substr($old_file_name,strrpos($old_file_name,"aac")));
                                }
                                elseif(strpos($old_file_name, '.m4a') !== false || strpos($old_file_name, '.M4A') !== false)
                                {
                                    $new_file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$request->cv_id, 'UTF-8')."_".$i.'.'.substr($old_file_name,strrpos($old_file_name,"m4a")));
                                }
                                elseif(strpos($old_file_name, '.mp4') !== false || strpos($old_file_name, '.MP4') !== false)
                                {
                                    $new_file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$request->cv_id, 'UTF-8')."_".$i.'.'.substr($old_file_name,strrpos($old_file_name,"wav")));
                                }
                                elseif(strpos($old_file_name, '.wma') !== false || strpos($old_file_name, '.WMA') !== false)
                                {
                                    $new_file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$request->cv_id, 'UTF-8')."_".$i.'.'.substr($old_file_name,strrpos($old_file_name,"wma")));
                                }
                                elseif(strpos($old_file_name, '.flac') !== false || strpos($old_file_name, '.FLAC') !== false)
                                {
                                    $new_file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$request->cv_id, 'UTF-8')."_".$i.'.'.substr($old_file_name,strrpos($old_file_name,"flac")));
                                }
                                else
                                {
                                    $new_file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$request->cv_id, 'UTF-8')."_".$i.'.'.substr($old_file_name,strrpos($old_file_name,"mp3")));
                                }

                            }
                            else
                            {
                                $new_file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$request->cv_id, 'UTF-8')."_".$i.'.'.explode(".",$old_file_name)[1]);
                            }
                            if($old_file_name != $new_file_name)
                            {
                                rename(public_path('audios/cv_audios/').$old_file_name, public_path('audios/cv_audios/').$new_file_name);
                            }

                            $block_6_data = [
                                'b6_id' => $request->$audio_id,
                                'b6_title' => $request->$audio_title,
                                'b6_name' => $new_file_name,
                                'cv_id' => $request->cv_id,
                                'is_active' => 0,
                                'created_by' => session('LoggedUser'),
                                'edited_by' => session('LoggedUser')
                            ];
                            DB::table('tbl_cv_block_6_data')->upsert($block_6_data, ['b6_id','cv_id'], ['b6_title','b6_name','cv_id','is_active','created_by','edited_by']);
                            //return redirect('brand-cvs')->with('success','Brand CV data updated successfully');
                        }

                    }
                }
                catch(\Illuminate\Database\QueryException $ex)
                {
                    //return ['error' => 'error update user'];
                    return back()->with('fail', 'Something went wrong while updating section 12 data, please try again!');
                }

            }

            $sonic_usage_count = $request->sonic_usage_count;
            for($i=0; $i<$sonic_usage_count; $i++)
            {
                $legend_name = "cv_sonic_usage_legend_name_".$i;
                $legend_number = "cv_sonic_usage_legend_number_".$i;
                $legend_id = "cv_sonic_usage_legend_id_".$i;

                $block_7_data = [
                    'b7_id' => $request->$legend_id,
                    'b7_title' => $request->section_7_title,
                    'b7_name' => $request->$legend_name,
                    'b7_number' => $request->$legend_number,
                    'cv_id' => $request->cv_id,
                    'created_by' => session('LoggedUser'),
                    'edited_by' => session('LoggedUser')
                ];

                try
                {
                    DB::table('tbl_cv_block_7_data')->upsert($block_7_data, ['b7_id','cv_id'], ['b7_title','b7_name','b7_number','cv_id','created_by','edited_by']);
                    //return redirect('brand-cvs')->with('success','Brand CV data updated successfully');
                }
                catch(\Illuminate\Database\QueryException $ex)
                {
                    //return ['error' => 'error update user'];
                    return back()->with('fail', 'Something went wrong while updating section 13 data, please try again!');
                }
            }

            /* $sonic_usage_industry_avg_count = $request->sonic_usage_industry_avg_count;
            DB::table('tbl_cv_block_8_data')->where('cv_id', $request->cv_id)->update(['is_active' => 1,'edited_by' => session('LoggedUser')]);
            for($i=0; $i<$sonic_usage_industry_avg_count; $i++)
            {
                $legend_name = "cv_sonic_usage_industry_avg_legend_name_".$i;
                $legend_number = "cv_sonic_usage_industry_avg_legend_number_".$i;
                $legend_color = "cv_sonic_usage_industry_avg_legend_color_".$i;
                $legend_id = "cv_sonic_usage_industry_avg_legend_id_".$i;

                $block_8_update_data = [
                    'b8_title' => $request->section_8_title,
                    'b8_name' => $request->$legend_name,
                    'b8_number' => $request->$legend_number,
                    'b8_color_code' => $request->$legend_color,
                    'cv_id' => $request->cv_id,
                    'is_active' => 0,
                    'edited_by' => session('LoggedUser')
                ];

                $block_8_insert_data = [
                    'b8_title' => $request->section_8_title,
                    'b8_name' => $request->$legend_name,
                    'b8_number' => $request->$legend_number,
                    'b8_color_code' => $request->$legend_color,
                    'cv_id' => $request->cv_id,
                    'is_active' => 0,
                    'created_by' => session('LoggedUser')
                ];

                $chk_data = DB::table('tbl_cv_block_8_data')->where('b8_id', $request->$legend_id)->where('cv_id', $request->cv_id)->first();
                if($chk_data != '')
                {
                    try
                    {
                        DB::table('tbl_cv_block_8_data')->where('b8_id', $request->$legend_id)->where('cv_id', $request->cv_id)->update($block_8_update_data);
                    }
                    catch(\Illuminate\Database\QueryException $ex)
                    {
                        //return ['error' => 'error update user'];
                        return back()->with('fail', 'Something went wrong while updating section 8 data, please try again!');
                    }
                }
                else
                {
                    try
                    {
                        DB::table('tbl_cv_block_8_data')->insert($block_8_insert_data);
                    }
                    catch(\Illuminate\Database\QueryException $ex)
                    {
                        //return ['error' => 'error update user'];
                        return back()->with('fail', 'Something went wrong while updating section 8 data, please try again!');
                    }
                }
            } */

            $most_popular_video_count = $request->most_popular_video_count;
            DB::table('tbl_cv_block_9_data')->where('cv_id', $request->cv_id)->update(['is_active' => 1,'edited_by' => session('LoggedUser')]);
            for($i=0; $i<$most_popular_video_count; $i++)
            {
                $video_title = "cv_most_popular_video_title_".$i;
                $video_link = "cv_most_popular_video_link_".$i;
                $video_id = "cv_most_popular_video_id_".$i;

                $block_9_update_data = [
                    'b9_title' => $request->section_9_title,
                    'b9_video_title' => $request->$video_title,
                    'b9_video_link' => $request->$video_link,
                    'cv_id' => $request->cv_id,
                    'is_active' => 0,
                    'edited_by' => session('LoggedUser')
                ];

                $block_9_insert_data = [
                    'b9_title' => $request->section_9_title,
                    'b9_video_title' => $request->$video_title,
                    'b9_video_link' => $request->$video_link,
                    'cv_id' => $request->cv_id,
                    'created_by' => session('LoggedUser')
                ];

                $chk_data = DB::table('tbl_cv_block_9_data')->where('b9_id', $request->$video_id)->where('cv_id', $request->cv_id)->first();
                if($chk_data != '')
                {
                    try
                    {
                        DB::table('tbl_cv_block_9_data')->where('b9_id', $request->$video_id)->where('cv_id', $request->cv_id)->update($block_9_update_data);
                    }
                    catch(\Illuminate\Database\QueryException $ex)
                    {
                        //return ['error' => 'error update user'];
                        return back()->with('fail', 'Something went wrong while updating section 5 data, please try again!');
                    }
                }
                else
                {
                    try
                    {
                        DB::table('tbl_cv_block_9_data')->insert($block_9_insert_data);
                    }
                    catch(\Illuminate\Database\QueryException $ex)
                    {
                        //return ['error' => 'error update user'];
                        return back()->with('fail', 'Something went wrong while updating section 5 data, please try again!');
                    }
                }
            }

            /* $a_day_in_my_life_count = $request->a_day_in_my_life_count;
            DB::table('tbl_cv_block_10_data')->where('cv_id', $request->cv_id)->update(['is_active' => 1,'edited_by' => session('LoggedUser')]);
            if($request->hasfile('section_10_bg_image'))
            {

                $file = $request->section_10_bg_image;
                $section_10_file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$request->cv_id, 'UTF-8').'.'.$file->getClientOriginalExtension());
                $file->move(public_path('images/section_10_bg_images'), $section_10_file_name);
            }
            else
            {
                if($request->old_section_10_bg_image != '')
                {
                    $section_10_file_name = $request->old_section_10_bg_image;
                }
                else
                {
                    $section_10_file_name = '';
                }
            }
            for($i=0; $i<$a_day_in_my_life_count; $i++)
            {
                $name_id = "cv_a_day_in_my_life_name_id_".$i;
                $number = "cv_a_day_in_my_life_number_".$i;
                $color = "cv_a_day_in_my_life_color_".$i;
                $life_id = "cv_a_day_in_my_life_id_".$i;

                $block_10_update_data = [
                    'b10_title' => $request->section_10_title,
                    //'b10_bg_color' => $request->section_10_bg_color,
                    'b10_name_id' => $request->$name_id,
                    'b10_number' => $request->$number,
                    'b10_color' => $request->$color,
                    'b10_bg_image' => $section_10_file_name,
                    'cv_id' => $request->cv_id,
                    'is_active' => 0,
                    'edited_by' => session('LoggedUser')
                ];

                $block_10_insert_data = [
                    'b10_title' => $request->section_10_title,
                    //'b10_bg_color' => $request->section_10_bg_color,
                    'b10_name_id' => $request->$name_id,
                    'b10_number' => $request->$number,
                    'b10_color' => $request->$color,
                    'b10_bg_image' => $section_10_file_name,
                    'cv_id' => $request->cv_id,
                    'is_active' => 0,
                    'created_by' => session('LoggedUser')
                ];

                $chk_data = DB::table('tbl_cv_block_10_data')->where('b10_id', $request->$life_id)->where('cv_id', $request->cv_id)->first();
                if($chk_data != '')
                {
                    try
                    {
                        DB::table('tbl_cv_block_10_data')->where('b10_id', $request->$life_id)->where('cv_id', $request->cv_id)->update($block_10_update_data);
                    }
                    catch(\Illuminate\Database\QueryException $ex)
                    {
                        //return ['error' => 'error update user'];
                        return back()->with('fail', 'Something went wrong while updating section 10 data, please try again!');
                    }
                }
                else
                {
                    try
                    {
                        DB::table('tbl_cv_block_10_data')->insert($block_10_insert_data);
                    }
                    catch(\Illuminate\Database\QueryException $ex)
                    {
                        //return ['error' => 'error update user'];
                        return back()->with('fail', 'Something went wrong while updating section 10 data, please try again!');
                    }
                }
            } */


            /* $msoa_count = $request->msoa_count;
            DB::table('tbl_cv_block_11_data')->where('cv_id', $request->cv_id)->update(['is_active' => 1,'edited_by' => session('LoggedUser')]);
            for($i=0; $i<$msoa_count; $i++)
            {
                $msoa_number = "msoa_number_".$i;
                $msoa_description = "msoa_description_".$i;
                $msoa_id = "msoa_id_".$i;

                $block_11_update_data = [
                    'b11_title' => $request->section_11_title,
                    'b11_number' => $request->$msoa_number,
                    'b11_description' => $request->$msoa_description,
                    'cv_id' => $request->cv_id,
                    'is_active' => 0,
                    'edited_by' => session('LoggedUser')
                ];

                $block_11_insert_data = [
                    'b11_title' => $request->section_11_title,
                    'b11_number' => $request->$msoa_number,
                    'b11_description' => $request->$msoa_description,
                    'cv_id' => $request->cv_id,
                    'is_active' => 0,
                    'created_by' => session('LoggedUser')
                ];

                //DB::table('tbl_cv_block_11_data')->updateOrInsert($block_11_data);
                $chk_data = DB::table('tbl_cv_block_11_data')->where('b11_id', $request->$msoa_id)->where('cv_id', $request->cv_id)->first();
                if($chk_data != '')
                {
                    try
                    {
                        DB::table('tbl_cv_block_11_data')->where('b11_id', $request->$msoa_id)->where('cv_id', $request->cv_id)->update($block_11_update_data);
                    }
                    catch(\Illuminate\Database\QueryException $ex)
                    {
                        //return ['error' => 'error update user'];
                        return back()->with('fail', 'Something went wrong while updating section 11 data, please try again!');
                    }
                }
                else
                {
                    if($request->$msoa_number !='' && $request->$msoa_description !='')
                    {
                        try
                        {
                            DB::table('tbl_cv_block_11_data')->insert($block_11_insert_data);
                        }
                        catch(\Illuminate\Database\QueryException $ex)
                        {
                            //return ['error' => 'error update user'];
                            return back()->with('fail', 'Something went wrong while updating section 11 data, please try again!');
                        }
                    }
                }
            } */


            /* $imgArray = GetSocialMediaIconsData::getSMData();
            $img_name = count($imgArray);
            $smsDataCount = $request->smsDataCount;
            DB::table('tbl_cv_block_12_data')->where('cv_id', $request->cv_id)->update(['is_active' => 1,'edited_by' => session('LoggedUser')]);
            for($i=0; $i<$smsDataCount; $i++)
            {
                $smsTrIcon = "smsTrIcon_".$i;
                $smsTrUrl = "smsTrUrl_".$i;
                $smsTrName = "smsTrName_".$i;
                $smsTrNumber = "smsTrNumber_".$i;
                $smsTrTxt = "smsTrTxt_".$i;
                $smsTrid = "smsTrid_".$i;


                if(Str::contains($request->$smsTrIcon,[';base64,']))
                {
                    $img_name++;
                    $image_parts = explode(";base64,", $request->$smsTrIcon);
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type = $image_type_aux[1];
                    $image_base64 = base64_decode($image_parts[1]);
                    $file = public_path('images/social_media_icons/original').'/'.$img_name.'.'.$image_type;
                    file_put_contents($file, $image_base64);
                    $destinationPath = public_path('/images/social_media_icons');
                    $img = Image::make($file);
                    $img->resize(200, 200, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($destinationPath.'/'.$img_name.'.'.$image_type);

                    $b12_icon_name = $img_name.'.'.$image_type;
                }
                else
                {
                    $b12_icon_name = Str::of($request->$smsTrIcon)->afterLast('/');
                }

                $block_12_data = [
                    'b12_id' => $request->$smsTrid,
                    'b12_title' => $request->section_12_title,
                    'b12_icon_name' => $b12_icon_name,
                    'b12_link' => $request->$smsTrUrl,
                    'b12_link_name' => $request->$smsTrName,
                    'b12_link_number' => $request->$smsTrNumber,
                    'b12_link_txt' => $request->$smsTrTxt,
                    'cv_id' => $request->cv_id,
                    'is_active' => 0,
                    'created_by' => session('LoggedUser'),
                    'edited_by' => session('LoggedUser')
                ];

                try
                {
                    DB::table('tbl_cv_block_12_data')->upsert($block_12_data, ['b12_id','cv_id'], ['b12_title','b12_icon_name','b12_link','b12_link_name','b12_link_number','b12_link_txt','cv_id','is_active','created_by','edited_by']);
                }
                catch(\Illuminate\Database\QueryException $ex)
                {
                    //return ['error' => 'error update user'];
                    return back()->with('fail', 'Something went wrong while updating section 12 data, please try again!');
                }
            } */


            $efb_count = $request->efb_count;
            DB::table('tbl_cv_block_13_data')->where('cv_id', $request->cv_id)->update(['is_active' => 1,'edited_by' => session('LoggedUser')]);
            if($request->hasfile('section_13_bg_image'))
            {

                $file = $request->section_13_bg_image;
                $section_13_file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$request->cv_id, 'UTF-8').'.'.$file->getClientOriginalExtension());
                $file->move(public_path('images/section_13_bg_images'), $section_13_file_name);
            }
            else
            {
                if($request->old_section_13_bg_image != '')
                {
                    $section_13_file_name = $request->old_section_13_bg_image;
                }
                else
                {
                    $section_13_file_name = '';
                }
            }
            for($i=0; $i<$efb_count; $i++)
            {
                $efb_name_id = "cv_efb_name_id_".$i;
                $efb_number = "cv_efb_number_".$i;
                $efb_id = "cv_efb_id_".$i;

                $block_13_update_data = [
                    'b13_title' => $request->section_13_title,
                    //'b13_bg_color' => $request->section_13_bg_color,
                    'b13_name_id' => $request->$efb_name_id,
                    'b13_number' => $request->$efb_number,
                    'b13_bg_image' => $section_13_file_name,
                    'cv_id' => $request->cv_id,
                    'is_active' => 0,
                    'edited_by' => session('LoggedUser')
                ];

                $block_13_insert_data = [
                    'b13_title' => $request->section_13_title,
                    //'b13_bg_color' => $request->section_13_bg_color,
                    'b13_name_id' => $request->$efb_name_id,
                    'b13_number' => $request->$efb_number,
                    'b13_bg_image' => $section_13_file_name,
                    'cv_id' => $request->cv_id,
                    'is_active' => 0,
                    'created_by' => session('LoggedUser')
                ];

                $chk_data = DB::table('tbl_cv_block_13_data')->where('b13_id', $request->$efb_id)->where('cv_id', $request->cv_id)->first();
                if($chk_data != '')
                {
                    try
                    {
                        DB::table('tbl_cv_block_13_data')->where('b13_id', $request->$efb_id)->where('cv_id', $request->cv_id)->update($block_13_update_data);
                    }
                    catch(\Illuminate\Database\QueryException $ex)
                    {
                        //return ['error' => 'error update user'];
                        return back()->with('fail', 'Something went wrong while updating section 16 data, please try again!');
                    }
                }
                else
                {
                    try
                    {
                        DB::table('tbl_cv_block_13_data')->insert($block_13_insert_data);
                    }
                    catch(\Illuminate\Database\QueryException $ex)
                    {
                        //return ['error' => 'error update user'];
                        return back()->with('fail', 'Something went wrong while updating section 16 data, please try again!');
                    }
                }
            }


            $mepy_count = $request->mepy_count;
            DB::table('tbl_cv_block_14_data')->where('cv_id', $request->cv_id)->update(['is_active' => 1,'edited_by' => session('LoggedUser')]);
            for($i=0; $i<$mepy_count; $i++)
            {
                $mepy_number = "mepy_number_".$i;
                $mepy_description = "mepy_description_".$i;
                $mepy_id = "mepy_id_".$i;

                $block_14_update_data = [
                    'b14_title' => $request->section_14_title,
                    'b14_number' => $request->$mepy_number,
                    'b14_description' => $request->$mepy_description,
                    'cv_id' => $request->cv_id,
                    'is_active' => 0,
                    'edited_by' => session('LoggedUser')
                ];

                $block_14_insert_data = [
                    'b14_title' => $request->section_14_title,
                    'b14_number' => $request->$mepy_number,
                    'b14_description' => $request->$mepy_description,
                    'cv_id' => $request->cv_id,
                    'is_active' => 0,
                    'created_by' => session('LoggedUser')
                ];


                $chk_data = DB::table('tbl_cv_block_14_data')->where('b14_id', $request->$mepy_id)->where('cv_id', $request->cv_id)->first();
                if($chk_data != '')
                {
                    try
                    {
                        DB::table('tbl_cv_block_14_data')->where('b14_id', $request->$mepy_id)->where('cv_id', $request->cv_id)->update($block_14_update_data);
                    }
                    catch(\Illuminate\Database\QueryException $ex)
                    {
                        //return ['error' => 'error update user'];
                        return back()->with('fail', 'Something went wrong while updating section 17 data, please try again!');
                    }
                }
                else
                {
                    if($request->$mepy_number !='' && $request->$mepy_description !='')
                    {
                        try
                        {
                            DB::table('tbl_cv_block_14_data')->insert($block_14_insert_data);
                        }
                        catch(\Illuminate\Database\QueryException $ex)
                        {
                            //return ['error' => 'error update user'];
                            return back()->with('fail', 'Something went wrong while updating section 17 data, please try again!');
                        }
                    }
                }
            }


            $mepv_count = $request->mepv_count;
            DB::table('tbl_cv_block_15_data')->where('cv_id', $request->cv_id)->update(['is_active' => 1,'edited_by' => session('LoggedUser')]);
            for($i=0; $i<$mepv_count; $i++)
            {
                $mepv_number = "mepv_number_".$i;
                $mepv_description = "mepv_description_".$i;
                $mepv_id = "mepv_id_".$i;

                $block_15_update_data = [
                    'b15_title' => $request->section_15_title,
                    'b15_number' => $request->$mepv_number,
                    'b15_description' => $request->$mepv_description,
                    'cv_id' => $request->cv_id,
                    'is_active' => 0,
                    'edited_by' => session('LoggedUser')
                ];

                $block_15_insert_data = [
                    'b15_title' => $request->section_15_title,
                    'b15_number' => $request->$mepv_number,
                    'b15_description' => $request->$mepv_description,
                    'cv_id' => $request->cv_id,
                    'is_active' => 0,
                    'created_by' => session('LoggedUser')
                ];

                $chk_data = DB::table('tbl_cv_block_15_data')->where('b15_id', $request->$mepv_id)->where('cv_id', $request->cv_id)->first();
                if($chk_data != '')
                {
                    try
                    {
                        DB::table('tbl_cv_block_15_data')->where('b15_id', $request->$mepv_id)->where('cv_id', $request->cv_id)->update($block_15_update_data);
                    }
                    catch(\Illuminate\Database\QueryException $ex)
                    {
                        //return ['error' => 'error update user'];
                        return back()->with('fail', 'Something went wrong while updating section 18 data, please try again!');
                    }
                }
                else
                {
                    if($request->$mepv_number !='' && $request->$mepv_description !='')
                    {
                        try
                        {
                            DB::table('tbl_cv_block_15_data')->insert($block_15_insert_data);
                        }
                        catch(\Illuminate\Database\QueryException $ex)
                        {
                            //return ['error' => 'error update user'];
                            return back()->with('fail', 'Something went wrong while updating section 18 data, please try again!');
                        }
                    }
                }
            }

            $c_date = date('Y-m-d h:i:s');
            $ychn_count = $request->ychn_count;
            $get_chn_ids = DB::table('tbl_cv_block_16_data')->where('cv_id', '=', $request->cv_id)->where('is_active','=', 0)->get();
            if(count($get_chn_ids)>0)
            {
                foreach($get_chn_ids as $chnID)
                {
                    DB::table('tbl_social_spyder_graph_request_data')->where('cv_id', $request->cv_id)->where('chn_id', "y_".$chnID->b16_id)->update(['is_active' => 1,'edited_by' => session('LoggedUser')]);
                }
            }
            DB::table('tbl_cv_block_16_data')->where('cv_id', $request->cv_id)->update(['is_active' => 1,'edited_by' => session('LoggedUser')]);

            for($i=0; $i<$ychn_count; $i++)
            {
                $ychn_name = 'ychn_name_'.$i;
                $og_ychn_start_date = 'ychn_start_date_'.$i;
                $og_ychn_end_date = 'ychn_end_date_'.$i;
                $ychn_id = "ychn_id_".$i;
                /* echo "ychn_name:".$request->$ychn_name."<br><br>";
                echo "og_ychn_start_date:".$request->$og_ychn_start_date."<br><br>";
                echo "og_ychn_end_date:".$request->$og_ychn_start_date."<br><br>"; */


                if($request->$ychn_name !='')
                {
                    if($request->$og_ychn_start_date !='')
                    {
                        $chn_start_date = explode("-",$request->$og_ychn_start_date)[1]."-".explode("-",$request->$og_ychn_start_date)[0]."-01 00:00:00";
                        $ychn_start_date = $request->$og_ychn_start_date;
                    }
                    else
                    {
                        $chn_start_date = explode("-",$request->cv_date)[1]."-01-01 00:00:00";
                        $ychn_start_date = "01-".explode("-",$request->cv_date)[1];
                    }

                    if($request->$og_ychn_end_date !='')
                    {
                        $end_date_month = explode("-",$request->$og_ychn_end_date)[0];
                        $month_with_31days = array("01","03","05","07","08","10","12");
                        if(in_array($end_date_month, $month_with_31days))
                        {
                            $end_month_days = "-31 00:00:00";
                        }
                        elseif($end_date_month == "02")
                        {
                            $end_month_days = "-28 00:00:00";
                        }
                        else
                        {
                            $end_month_days = "-30 00:00:00";
                        }
                        $chn_end_date = explode("-",$request->$og_ychn_end_date)[1]."-".$end_date_month.$end_month_days;
                        $ychn_end_date = $request->$og_ychn_end_date;
                    }
                    else
                    {
                        $chn_end_date = explode("-",$request->cv_date)[1]."-12-31 00:00:00";
                        $ychn_end_date = "12-".explode("-",$request->cv_date)[1];
                    }

                    $block_16_update_data = [
                        'chn_name' => preg_replace('/^@/', '', $request->$ychn_name),
                        'start_date' => $chn_start_date,
                        'start_month' => $ychn_start_date,
                        'end_date' => $chn_end_date,
                        'end_month' => $ychn_end_date,
                        'cv_id' => $request->cv_id,
                        'is_active' => 0,
                        'edited_by' => session('LoggedUser'),
                        'c_date' => $c_date
                    ];

                    $block_16_insert_data = [
                        'chn_name' => preg_replace('/^@/', '', $request->$ychn_name),
                        'start_date' => $chn_start_date,
                        'start_month' => $ychn_start_date,
                        'end_date' => $chn_end_date,
                        'end_month' => $ychn_end_date,
                        'cv_id' => $request->cv_id,
                        'created_by' => session('LoggedUser'),
                        'c_date' => $c_date
                    ];
                    /* print_r($block_16_data);
                    echo "<br><br>"; */
                    $get_crate_id = DB::table('tbl_social_spyder_graph_meta_data')->where('cv_id','=',$request->cv_id)->where('is_active','=',0)->first();
                    if($get_crate_id != '')
                    {
                        $crate_id = $get_crate_id->crate_id;
                    }
                    else
                    {
                        $crate_id = "";
                    }
                    $request_json = array();
                    $chn_id = null;
                    $chk_data = DB::table('tbl_cv_block_16_data')->where('b16_id', $request->$ychn_id)->where('cv_id', $request->cv_id)->first();
                    if($chk_data != '')
                    {
                        try
                        {
                            DB::table('tbl_cv_block_16_data')->where('b16_id', $request->$ychn_id)->where('cv_id', $request->cv_id)->update($block_16_update_data);
                            if($chk_data->chn_name != $request->$ychn_name || $chk_data->start_month != $request->$og_ychn_start_date || $chk_data->end_month != $request->$og_ychn_end_date)
                            {
                                $chn_id = $request->$ychn_id;

                                $request_json = '{ "process_type" : "youtube", "name": "'.preg_replace('/^@/', '', $request->$ychn_name).'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$request->cv_id.'","chn_id":"y_'.$request->$ychn_id.'","c_date":"'.$c_date.'" }';
                                $request_upsert_data = [
                                    'cv_id' => $request->cv_id,
                                    'chn_id' => "y_".$chn_id,
                                    //'request_json' => json_encode(str_replace("]","",str_replace("[","",json_encode($request_json)))),
                                    'request_json' => $request_json,
                                    'status' => 0,
                                    'is_active' => 0,
                                    'process_type' => "youtube",
                                    'edited_by' => session('LoggedUser'),
                                    'c_date' => $c_date
                                ];
                                try
                                {
                                    DB::table('tbl_social_spyder_graph_request_data')->updateOrInsert(['cv_id' => $request->cv_id, 'chn_id' => "y_".$chn_id],$request_upsert_data);
                                }
                                catch(\Illuminate\Database\QueryException $ex)
                                {
                                    //return ['error' => 'error update user'];
                                    return back()->with('fail', 'Something went wrong while updating request json data, please try again!');
                                }
                            }
                            else
                            {
                                DB::table('tbl_social_spyder_graph_request_data')->where('chn_id', "y_".$request->$ychn_id)->where('cv_id', $request->cv_id)->update(['is_active' => 0,'edited_by' => session('LoggedUser'),'c_date'=>$c_date]);
                            }
                        }
                        catch(\Illuminate\Database\QueryException $ex)
                        {
                            //return ['error' => 'error update user'];
                            return back()->with('fail', 'Something went wrong while updating section 8 data, please try again!');
                        }
                    }
                    else
                    {
                        try
                        {
                            $chn_id = DB::table('tbl_cv_block_16_data')->insertGetId($block_16_insert_data);

                            $request_json = '{ "process_type" : "youtube", "name": "'.preg_replace('/^@/', '', $request->$ychn_name).'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$request->cv_id.'","chn_id":"y_'.$chn_id.'","c_date":"'.$c_date.'" }';
                            $request_upsert_data = [
                                'cv_id' => $request->cv_id,
                                'chn_id' => "y_".$chn_id,
                                //'request_json' => json_encode(str_replace("]","",str_replace("[","",json_encode($request_json)))),
                                'request_json' => $request_json,
                                'process_type' => "youtube",
                                'created_by' => session('LoggedUser'),
                                'c_date' => $c_date
                            ];
                            try
                            {
                                DB::table('tbl_social_spyder_graph_request_data')->updateOrInsert(['cv_id' => $request->cv_id, 'chn_id' => "y_".$chn_id],$request_upsert_data);
                            }
                            catch(\Illuminate\Database\QueryException $ex)
                            {
                                //return ['error' => 'error update user'];
                                return back()->with('fail', 'Something went wrong while updating request json data, please try again!');
                            }
                        }
                        catch(\Illuminate\Database\QueryException $ex)
                        {
                            //return ['error' => 'error update user'];
                            return back()->with('fail', 'Something went wrong while updating section 8 data, please try again!');
                        }
                    }
                }
            }

            $ichn_count = $request->ichn_count;
            $get_chn_ids = DB::table('tbl_cv_block_17_data')->where('cv_id', '=', $request->cv_id)->where('is_active','=', 0)->get();
            if(count($get_chn_ids)>0)
            {
                foreach($get_chn_ids as $chnID)
                {
                    DB::table('tbl_social_spyder_graph_request_data')->where('cv_id', $request->cv_id)->where('chn_id', "i_".$chnID->b17_id)->update(['is_active' => 1,'edited_by' => session('LoggedUser')]);
                }
            }
            DB::table('tbl_cv_block_17_data')->where('cv_id', $request->cv_id)->update(['is_active' => 1,'edited_by' => session('LoggedUser')]);

            for($i=0; $i<$ichn_count; $i++)
            {
                $ichn_name = 'ichn_name_'.$i;
                $og_ichn_start_date = 'ichn_start_date_'.$i;
                $og_ichn_end_date = 'ichn_end_date_'.$i;
                $ichn_id = "ichn_id_".$i;
                /* echo "ichn_name:".$request->$ichn_name."<br><br>";
                echo "og_ichn_start_date:".$request->$og_ichn_start_date."<br><br>";
                echo "og_ichn_end_date:".$request->$og_ichn_start_date."<br><br>"; */
                if($request->$ichn_name !='')
                {
                    if($request->$og_ichn_start_date !='')
                    {
                        $chn_start_date = explode("-",$request->$og_ichn_start_date)[1]."-".explode("-",$request->$og_ichn_start_date)[0]."-01 00:00:00";
                        $ichn_start_date = $request->$og_ichn_start_date;
                    }
                    else
                    {
                        $chn_start_date = explode("-",$request->cv_date)[1]."-01-01 00:00:00";
                        $ichn_start_date = "01-".explode("-",$request->cv_date)[1];
                    }

                    if($request->$og_ichn_end_date !='')
                    {
                        $end_date_month = explode("-",$request->$og_ichn_end_date)[0];
                        $month_with_31days = array("01","03","05","07","08","10","12");
                        if(in_array($end_date_month, $month_with_31days))
                        {
                            $end_month_days = "-31 00:00:00";
                        }
                        elseif($end_date_month == "02")
                        {
                            $end_month_days = "-28 00:00:00";
                        }
                        else
                        {
                            $end_month_days = "-30 00:00:00";
                        }
                        $chn_end_date = explode("-",$request->$og_ichn_end_date)[1]."-".$end_date_month.$end_month_days;
                        $ichn_end_date = $request->$og_ichn_end_date;
                    }
                    else
                    {
                        $chn_end_date = explode("-",$request->cv_date)[1]."-12-31 00:00:00";
                        $ichn_end_date = "12-".explode("-",$request->cv_date)[1];
                    }

                    $block_17_update_data = [
                        'chn_name' => preg_replace('/^@/', '', $request->$ichn_name),
                        'start_date' => $chn_start_date,
                        'start_month' => $ichn_start_date,
                        'end_date' => $chn_end_date,
                        'end_month' => $ichn_end_date,
                        'cv_id' => $request->cv_id,
                        'is_active' => 0,
                        'edited_by' => session('LoggedUser'),
                        'c_date' => $c_date
                    ];

                    $block_17_insert_data = [
                        'chn_name' => preg_replace('/^@/', '', $request->$ichn_name),
                        'start_date' => $chn_start_date,
                        'start_month' => $ichn_start_date,
                        'end_date' => $chn_end_date,
                        'end_month' => $ichn_end_date,
                        'cv_id' => $request->cv_id,
                        'created_by' => session('LoggedUser'),
                        'c_date' => $c_date
                    ];
                    /* print_r($block_17_data);
                    echo "<br><br>"; */
                    $get_crate_id = DB::table('tbl_social_spyder_graph_meta_data')->where('cv_id','=',$request->cv_id)->where('is_active','=',0)->first();
                    if($get_crate_id != '')
                    {
                        $crate_id = $get_crate_id->crate_id;
                    }
                    else
                    {
                        $crate_id = "";
                    }
                    $request_json = array();
                    $chn_id = null;
                    $chk_data = DB::table('tbl_cv_block_17_data')->where('b17_id', $request->$ichn_id)->where('cv_id', $request->cv_id)->first();
                    if($chk_data != '')
                    {
                        try
                        {
                            DB::table('tbl_cv_block_17_data')->where('b17_id', $request->$ichn_id)->where('cv_id', $request->cv_id)->update($block_17_update_data);
                            if($chk_data->chn_name != $request->$ichn_name || $chk_data->start_month != $request->$og_ichn_start_date || $chk_data->end_month != $request->$og_ichn_end_date)
                            {
                                $chn_id = $request->$ichn_id;

                                $request_json = '{ "process_type" : "instagram", "name": "'.preg_replace('/^@/', '', $request->$ichn_name).'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$request->cv_id.'","chn_id":"i_'.$request->$ichn_id.'","c_date":"'.$c_date.'" }';
                                $request_upsert_data = [
                                    'cv_id' => $request->cv_id,
                                    'chn_id' => "i_".$chn_id,
                                    //'request_json' => json_encode(str_replace("]","",str_replace("[","",json_encode($request_json)))),
                                    'request_json' => $request_json,
                                    'status' => 0,
                                    'is_active' => 0,
                                    'process_type' => "instagram",
                                    'edited_by' => session('LoggedUser'),
                                    'c_date' => $c_date
                                ];
                                try
                                {
                                    DB::table('tbl_social_spyder_graph_request_data')->updateOrInsert(['cv_id' => $request->cv_id, 'chn_id' => "i_".$chn_id],$request_upsert_data);
                                }
                                catch(\Illuminate\Database\QueryException $ex)
                                {
                                    //return ['error' => 'error update user'];
                                    return back()->with('fail', 'Something went wrong while updating request json data, please try again!');
                                }
                            }
                            else
                            {
                                DB::table('tbl_social_spyder_graph_request_data')->where('chn_id', "i_".$request->$ichn_id)->where('cv_id', $request->cv_id)->update(['is_active' => 0,'edited_by' => session('LoggedUser'),'c_date' => $c_date]);
                            }
                        }
                        catch(\Illuminate\Database\QueryException $ex)
                        {
                            //return ['error' => 'error update user'];
                            return back()->with('fail', 'Something went wrong while updating section 9 data, please try again!');
                        }
                    }
                    else
                    {
                        try
                        {
                            $chn_id = DB::table('tbl_cv_block_17_data')->insertGetId($block_17_insert_data);

                            $request_json = '{ "process_type" : "instagram", "name": "'.preg_replace('/^@/', '', $request->$ichn_name).'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$request->cv_id.'","chn_id":"i_'.$chn_id.'","c_date":"'.$c_date.'" }';
                            $request_upsert_data = [
                                'cv_id' => $request->cv_id,
                                'chn_id' => "i_".$chn_id,
                                //'request_json' => json_encode(str_replace("]","",str_replace("[","",json_encode($request_json)))),
                                'request_json' => $request_json,
                                'process_type' => "instagram",
                                'created_by' => session('LoggedUser'),
                                'c_date' => $c_date
                            ];
                            try
                            {
                                DB::table('tbl_social_spyder_graph_request_data')->updateOrInsert(['cv_id' => $request->cv_id, 'chn_id' => "i_".$chn_id],$request_upsert_data);
                            }
                            catch(\Illuminate\Database\QueryException $ex)
                            {
                                //return ['error' => 'error update user'];
                                return back()->with('fail', 'Something went wrong while updating request json data, please try again!');
                            }
                        }
                        catch(\Illuminate\Database\QueryException $ex)
                        {
                            //return ['error' => 'error update user'];
                            return back()->with('fail', 'Something went wrong while updating section 9 data, please try again!');
                        }
                    }
                }
            }

            $tchn_count = $request->tchn_count;
            $get_chn_ids = DB::table('tbl_cv_block_18_data')->where('cv_id', '=', $request->cv_id)->where('is_active','=', 0)->get();
            if(count($get_chn_ids)>0)
            {
                foreach($get_chn_ids as $chnID)
                {
                    DB::table('tbl_social_spyder_graph_request_data')->where('cv_id', $request->cv_id)->where('chn_id', "t_".$chnID->b18_id)->update(['is_active' => 1,'edited_by' => session('LoggedUser')]);
                }
            }
            DB::table('tbl_cv_block_18_data')->where('cv_id', $request->cv_id)->update(['is_active' => 1,'edited_by' => session('LoggedUser')]);

            for($i=0; $i<$tchn_count; $i++)
            {
                $tchn_name = 'tchn_name_'.$i;
                $og_tchn_start_date = 'tchn_start_date_'.$i;
                $og_tchn_end_date = 'tchn_end_date_'.$i;
                $tchn_id = "tchn_id_".$i;
                /* echo "tchn_name:".$request->$tchn_name."<br><br>";
                echo "og_tchn_start_date:".$request->$og_tchn_start_date."<br><br>";
                echo "og_tchn_end_date:".$request->$og_tchn_start_date."<br><br>"; */
                if($request->$tchn_name !='')
                {
                    if($request->$og_tchn_start_date !='')
                    {
                        $chn_start_date = explode("-",$request->$og_tchn_start_date)[1]."-".explode("-",$request->$og_tchn_start_date)[0]."-01 00:00:00";
                        $tchn_start_date = $request->$og_tchn_start_date;
                    }
                    else
                    {
                        $chn_start_date = explode("-",$request->cv_date)[1]."-01-01 00:00:00";
                        $tchn_start_date = "01-".explode("-",$request->cv_date)[1];
                    }

                    if($request->$og_tchn_end_date !='')
                    {
                        $end_date_month = explode("-",$request->$og_tchn_end_date)[0];
                        $month_with_31days = array("01","03","05","07","08","10","12");
                        if(in_array($end_date_month, $month_with_31days))
                        {
                            $end_month_days = "-31 00:00:00";
                        }
                        elseif($end_date_month == "02")
                        {
                            $end_month_days = "-28 00:00:00";
                        }
                        else
                        {
                            $end_month_days = "-30 00:00:00";
                        }
                        $chn_end_date = explode("-",$request->$og_tchn_end_date)[1]."-".$end_date_month.$end_month_days;
                        $tchn_end_date = $request->$og_tchn_end_date;
                    }
                    else
                    {
                        $chn_end_date = explode("-",$request->cv_date)[1]."-12-31 00:00:00";
                        $tchn_end_date = "12-".explode("-",$request->cv_date)[1];
                    }

                    $block_18_update_data = [
                        'chn_name' => preg_replace('/^@/', '', $request->$tchn_name),
                        'start_date' => $chn_start_date,
                        'start_month' => $tchn_start_date,
                        'end_date' => $chn_end_date,
                        'end_month' => $tchn_end_date,
                        'cv_id' => $request->cv_id,
                        'is_active' => 0,
                        'edited_by' => session('LoggedUser'),
                        'c_date' => $c_date
                    ];

                    $block_18_insert_data = [
                        'chn_name' => preg_replace('/^@/', '', $request->$tchn_name),
                        'start_date' => $chn_start_date,
                        'start_month' => $tchn_start_date,
                        'end_date' => $chn_end_date,
                        'end_month' => $tchn_end_date,
                        'cv_id' => $request->cv_id,
                        'created_by' => session('LoggedUser'),
                        'c_date' => $c_date
                    ];
                    /* print_r($block_18_data);
                    echo "<br><br>"; */
                    $get_crate_id = DB::table('tbl_social_spyder_graph_meta_data')->where('cv_id','=',$request->cv_id)->where('is_active','=',0)->first();
                    if($get_crate_id != '')
                    {
                        $crate_id = $get_crate_id->crate_id;
                    }
                    else
                    {
                        $crate_id = "";
                    }
                    $request_json = array();
                    $chn_id = null;
                    $chk_data = DB::table('tbl_cv_block_18_data')->where('b18_id', $request->$tchn_id)->where('cv_id', $request->cv_id)->first();
                    if($chk_data != '')
                    {
                        try
                        {
                            DB::table('tbl_cv_block_18_data')->where('b18_id', $request->$tchn_id)->where('cv_id', $request->cv_id)->update($block_18_update_data);
                            if($chk_data->chn_name != $request->$tchn_name || $chk_data->start_month != $request->$og_tchn_start_date || $chk_data->end_month != $request->$og_tchn_end_date)
                            {
                                $chn_id = $request->$tchn_id;

                                $request_json = '{ "process_type" : "tiktok", "name": "'.preg_replace('/^@/', '', $request->$tchn_name).'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$request->cv_id.'","chn_id":"t_'.$request->$tchn_id.'","c_date":"'.$c_date.'" }';
                                $request_upsert_data = [
                                    'cv_id' => $request->cv_id,
                                    'chn_id' => "t_".$chn_id,
                                    //'request_json' => json_encode(str_replace("]","",str_replace("[","",json_encode($request_json)))),
                                    'request_json' => $request_json,
                                    'status' => 0,
                                    'is_active' => 0,
                                    'process_type' => "tiktok",
                                    'edited_by' => session('LoggedUser'),
                                    'c_date' => $c_date
                                ];
                                try
                                {
                                    DB::table('tbl_social_spyder_graph_request_data')->updateOrInsert(['cv_id' => $request->cv_id, 'chn_id' => "t_".$chn_id],$request_upsert_data);
                                }
                                catch(\Illuminate\Database\QueryException $ex)
                                {
                                    //return ['error' => 'error update user'];
                                    return back()->with('fail', 'Something went wrong while updating request json data, please try again!');
                                }
                            }
                            else
                            {
                                DB::table('tbl_social_spyder_graph_request_data')->where('chn_id', "t_".$request->$tchn_id)->where('cv_id', $request->cv_id)->update(['is_active' => 0,'edited_by' => session('LoggedUser'),'c_date' => $c_date]);
                            }
                        }
                        catch(\Illuminate\Database\QueryException $ex)
                        {
                            //return ['error' => 'error update user'];
                            return back()->with('fail', 'Something went wrong while updating section 10 data, please try again!');
                        }
                    }
                    else
                    {
                        try
                        {
                            $chn_id = DB::table('tbl_cv_block_18_data')->insertGetId($block_18_insert_data);

                            $request_json = '{ "process_type" : "tiktok", "name": "'.preg_replace('/^@/', '', $request->$tchn_name).'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$request->cv_id.'","chn_id":"t_'.$chn_id.'","c_date":"'.$c_date.'" }';
                            $request_upsert_data = [
                                'cv_id' => $request->cv_id,
                                'chn_id' => "t_".$chn_id,
                                //'request_json' => json_encode(str_replace("]","",str_replace("[","",json_encode($request_json)))),
                                'request_json' => $request_json,
                                'process_type' => "tiktok",
                                'created_by' => session('LoggedUser'),
                                'c_date' => $c_date
                            ];
                            try
                            {
                                DB::table('tbl_social_spyder_graph_request_data')->updateOrInsert(['cv_id' => $request->cv_id, 'chn_id' => "t_".$chn_id],$request_upsert_data);
                            }
                            catch(\Illuminate\Database\QueryException $ex)
                            {
                                //return ['error' => 'error update user'];
                                return back()->with('fail', 'Something went wrong while updating request json data, please try again!');
                            }
                        }
                        catch(\Illuminate\Database\QueryException $ex)
                        {
                            //return ['error' => 'error update user'];
                            return back()->with('fail', 'Something went wrong while updating section 10 data, please try again!');
                        }
                    }
                }
            }

            $twtchn_count = $request->twtchn_count;
            $get_chn_ids = DB::table('tbl_cv_block_19_data')->where('cv_id', '=', $request->cv_id)->where('is_active','=', 0)->get();
            if(count($get_chn_ids)>0)
            {
                foreach($get_chn_ids as $chnID)
                {
                    DB::table('tbl_social_spyder_graph_request_data')->where('cv_id', $request->cv_id)->where('chn_id', "twt_".$chnID->b19_id)->update(['is_active' => 1,'edited_by' => session('LoggedUser')]);
                }
            }
            DB::table('tbl_cv_block_19_data')->where('cv_id', $request->cv_id)->update(['is_active' => 1,'edited_by' => session('LoggedUser')]);

            for($i=0; $i<$twtchn_count; $i++)
            {
                $twtchn_name = 'twtchn_name_'.$i;
                $og_twtchn_start_date = 'twtchn_start_date_'.$i;
                $og_twtchn_end_date = 'twtchn_end_date_'.$i;
                $twtchn_id = "twtchn_id_".$i;
                /* echo "twtchn_name:".$request->$twtchn_name."<br><br>";
                echo "og_twtchn_start_date:".$request->$og_twtchn_start_date."<br><br>";
                echo "og_twtchn_end_date:".$request->$og_twtchn_start_date."<br><br>"; */
                if($request->$twtchn_name !='')
                {
                    if($request->$og_twtchn_start_date !='')
                    {
                        $chn_start_date = explode("-",$request->$og_twtchn_start_date)[1]."-".explode("-",$request->$og_twtchn_start_date)[0]."-01 00:00:00";
                        $twtchn_start_date = $request->$og_twtchn_start_date;
                    }
                    else
                    {
                        $chn_start_date = explode("-",$request->cv_date)[1]."-01-01 00:00:00";
                        $twtchn_start_date = "01-".explode("-",$request->cv_date)[1];
                    }

                    if($request->$og_twtchn_end_date !='')
                    {
                        $end_date_month = explode("-",$request->$og_twtchn_end_date)[0];
                        $month_with_31days = array("01","03","05","07","08","10","12");
                        if(in_array($end_date_month, $month_with_31days))
                        {
                            $end_month_days = "-31 00:00:00";
                        }
                        elseif($end_date_month == "02")
                        {
                            $end_month_days = "-28 00:00:00";
                        }
                        else
                        {
                            $end_month_days = "-30 00:00:00";
                        }
                        $chn_end_date = explode("-",$request->$og_twtchn_end_date)[1]."-".$end_date_month.$end_month_days;
                        $twtchn_end_date = $request->$og_twtchn_end_date;
                    }
                    else
                    {
                        $chn_end_date = explode("-",$request->cv_date)[1]."-12-31 00:00:00";
                        $twtchn_end_date = "12-".explode("-",$request->cv_date)[1];
                    }

                    $block_19_update_data = [
                        'chn_name' => preg_replace('/^@/', '', $request->$twtchn_name),
                        'start_date' => $chn_start_date,
                        'start_month' => $twtchn_start_date,
                        'end_date' => $chn_end_date,
                        'end_month' => $twtchn_end_date,
                        'cv_id' => $request->cv_id,
                        'is_active' => 0,
                        'edited_by' => session('LoggedUser'),
                        'c_date' => $c_date
                    ];

                    $block_19_insert_data = [
                        'chn_name' => preg_replace('/^@/', '', $request->$twtchn_name),
                        'start_date' => $chn_start_date,
                        'start_month' => $twtchn_start_date,
                        'end_date' => $chn_end_date,
                        'end_month' => $twtchn_end_date,
                        'cv_id' => $request->cv_id,
                        'created_by' => session('LoggedUser'),
                        'c_date' => $c_date
                    ];
                    /* print_r($block_19_data);
                    echo "<br><br>"; */
                    $get_crate_id = DB::table('tbl_social_spyder_graph_meta_data')->where('cv_id','=',$request->cv_id)->where('is_active','=',0)->first();
                    if($get_crate_id != '')
                    {
                        $crate_id = $get_crate_id->crate_id;
                    }
                    else
                    {
                        $crate_id = "";
                    }
                    $request_json = array();
                    $chn_id = null;
                    $chk_data = DB::table('tbl_cv_block_19_data')->where('b19_id', $request->$twtchn_id)->where('cv_id', $request->cv_id)->first();
                    if($chk_data != '')
                    {
                        try
                        {
                            DB::table('tbl_cv_block_19_data')->where('b19_id', $request->$twtchn_id)->where('cv_id', $request->cv_id)->update($block_19_update_data);
                            if($chk_data->chn_name != $request->$twtchn_name || $chk_data->start_month != $request->$og_twtchn_start_date || $chk_data->end_month != $request->$og_twtchn_end_date)
                            {
                                $chn_id = $request->$twtchn_id;

                                $request_json = '{ "process_type" : "twitter", "name": "'.preg_replace('/^@/', '', $request->$twtchn_name).'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$request->cv_id.'","chn_id":"twt_'.$request->$twtchn_id.'","c_date":"'.$c_date.'" }';
                                $request_upsert_data = [
                                    'cv_id' => $request->cv_id,
                                    'chn_id' => "twt_".$chn_id,
                                    //'request_json' => json_encode(str_replace("]","",str_replace("[","",json_encode($request_json)))),
                                    'request_json' => $request_json,
                                    'status' => 0,
                                    'is_active' => 0,
                                    'process_type' => "twitter",
                                    'edited_by' => session('LoggedUser'),
                                    'c_date' => $c_date
                                ];
                                try
                                {
                                    DB::table('tbl_social_spyder_graph_request_data')->updateOrInsert(['cv_id' => $request->cv_id, 'chn_id' => "twt_".$chn_id],$request_upsert_data);
                                }
                                catch(\Illuminate\Database\QueryException $ex)
                                {
                                    //return ['error' => 'error update user'];
                                    return back()->with('fail', 'Something went wrong while updating request json data, please try again!');
                                }
                            }
                            else
                            {
                                DB::table('tbl_social_spyder_graph_request_data')->where('chn_id', "twt_".$request->$twtchn_id)->where('cv_id', $request->cv_id)->update(['is_active' => 0,'edited_by' => session('LoggedUser'),'c_date' => $c_date]);
                            }
                        }
                        catch(\Illuminate\Database\QueryException $ex)
                        {
                            //return ['error' => 'error update user'];
                            return back()->with('fail', 'Something went wrong while updating section 11 data, please try again!');
                        }
                    }
                    else
                    {
                        try
                        {
                            $chn_id = DB::table('tbl_cv_block_19_data')->insertGetId($block_19_insert_data);

                            $request_json = '{ "process_type" : "twitter", "name": "'.preg_replace('/^@/', '', $request->$twtchn_name).'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$request->cv_id.'","chn_id":"twt_'.$chn_id.'","c_date":"'.$c_date.'" }';
                            $request_upsert_data = [
                                'cv_id' => $request->cv_id,
                                'chn_id' => "twt_".$chn_id,
                                //'request_json' => json_encode(str_replace("]","",str_replace("[","",json_encode($request_json)))),
                                'request_json' => $request_json,
                                'process_type' => "twitter",
                                'created_by' => session('LoggedUser'),
                                'c_date' => $c_date
                            ];
                            try
                            {
                                DB::table('tbl_social_spyder_graph_request_data')->updateOrInsert(['cv_id' => $request->cv_id, 'chn_id' => "twt_".$chn_id],$request_upsert_data);
                            }
                            catch(\Illuminate\Database\QueryException $ex)
                            {
                                //return ['error' => 'error update user'];
                                return back()->with('fail', 'Something went wrong while updating request json data, please try again!');
                            }
                        }
                        catch(\Illuminate\Database\QueryException $ex)
                        {
                            //return ['error' => 'error update user'];
                            return back()->with('fail', 'Something went wrong while updating section 11 data, please try again!');
                        }
                    }
                }
            }

            return redirect('brand-cvs')->with('success','Brand Sonic Radar data updated successfully');
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }



    function duplicateBrandCv($id)
    {
        //echo 'edit brand cv section';
        $cv_data = DB::table('tbl_cvs')->where('cv_id', '=', base64_decode($id))->first();
        $cv_parent_data = DB::table('tbl_cvs')->where('is_active', '=', 0)->get();
        $cv_parent_ids = DB::table('tbl_cvs')->where('parent_id', '!=', null)->where('parent_id', '!=', '')->where('is_active', '=', 0)->get();
        $industry_data = DB::table('tbl_industry')->where('is_active', '=', 0)->get();
        $sub_industry_data =  DB::table('tbl_sub_industry')->where('is_active', '=', 0)->get();
        $sub_ind_parent_ids = DB::table('tbl_cvs')->select('industry_id')->where('sub_industry_id', '!=', null)->where('sub_industry_id', '!=', '')->where('sub_industry_id', '!=', 0)->where('status', '=', 1)->where('is_active', '=', 0)->distinct()->get();
        $music_taste_data = DB::table('tbl_music_taste')->where('is_active', '=', 0)->get();
        $experience_data = DB::table('tbl_experience')->where('is_active', '=', 0)->orderBy('display_order', 'asc')->get();
        $qualitative_data = DB::table('tbl_qualitative')->where('is_active', '=', 0)->get();
        $footer_template_data = DB::table('tbl_footer_template')->where('is_active', '=', 0)->get();
        $cv_block_2_data = DB::table('tbl_best_in_audio_brands')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->first();
        $cv_block_3_data = DB::table('tbl_cv_block_3_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_4_data = DB::table('tbl_cv_block_4_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->first();
        $cv_block_5_data = DB::table('tbl_cv_block_5_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_6_data = DB::table('tbl_cv_block_6_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_7_data = DB::table('tbl_cv_block_7_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_8_data = DB::table('tbl_cv_block_8_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_9_data = DB::table('tbl_cv_block_9_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_10_data = DB::table('tbl_cv_block_10_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_11_data = DB::table('tbl_cv_block_11_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_12_data = DB::table('tbl_cv_block_12_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_13_qdata = DB::table('tbl_cv_block_13_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_14_data = DB::table('tbl_cv_block_14_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_15_data = DB::table('tbl_cv_block_15_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_16_data = DB::table('tbl_cv_block_16_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_17_data = DB::table('tbl_cv_block_17_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_18_data = DB::table('tbl_cv_block_18_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_19_data = DB::table('tbl_cv_block_19_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        if(count($cv_block_3_data)==0)
        {
            $cv_block_3_data = '';
        }
        if(count($cv_block_5_data)==0)
        {
            $cv_block_5_data = '';
        }
        if(count($cv_block_6_data)==0)
        {
            $cv_block_6_data = '';
        }
        if(count($cv_block_7_data)==0)
        {
            $cv_block_7_data = '';
        }
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
        }
        if(count($cv_block_11_data)==0)
        {
            $cv_block_11_data = '';
        }
        if(count($cv_block_12_data)==0)
        {
            $cv_block_12_data = '';
        }
        if(count($cv_block_13_qdata)==0)
        {
            $cv_block_13_data = [];
        }
        else
        {
            $cv_block_13_data = [];
            foreach($cv_block_13_qdata as $cb13data)
            {
                $cv_block_13_data[$cb13data->b13_name_id] = $cb13data->b13_id."#_#".$cb13data->b13_number;
            }
        }
        if(count($cv_block_14_data)==0)
        {
            $cv_block_14_data = '';
        }
        if(count($cv_block_15_data)==0)
        {
            $cv_block_15_data = '';
        }
        if(count($cv_block_16_data)==0)
        {
            $cv_block_16_data = '';
        }
        if(count($cv_block_17_data)==0)
        {
            $cv_block_17_data = '';
        }
        if(count($cv_block_18_data)==0)
        {
            $cv_block_18_data = '';
        }
        if(count($cv_block_19_data)==0)
        {
            $cv_block_19_data = '';
        }
        if(count($cv_parent_ids)==0)
        {
            $cv_parent_ids_array = [];
        }
        else
        {
            $cv_parent_ids_array = [];
            foreach($cv_parent_ids as $pid)
            {
                if($pid->parent_id!=$cv_data->parent_id)
                {
                    array_push($cv_parent_ids_array,$pid->parent_id);
                }
            }
        }
        $sub_ind_parent_ids_arr = [];
        if(count($sub_ind_parent_ids)==0)
        {
            $sub_ind_parent_ids_arr = [];
        }
        else
        {
            foreach($sub_ind_parent_ids as $sipdata)
            {
                array_push($sub_ind_parent_ids_arr, $sipdata->industry_id);
            }
        }

        //print_r($cv_block_8_data);exit;
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.duplicate_brand_cv', ['cv_data'=>$cv_data, 'cv_parent_data'=>$cv_parent_data, 'cv_parent_ids_array'=>$cv_parent_ids_array, 'industry_data'=>$industry_data, 'sub_industry_data'=>$sub_industry_data, 'sub_ind_parent_ids'=>$sub_ind_parent_ids_arr, 'music_taste_data'=>$music_taste_data, 'experience_data'=>$experience_data, 'qualitative_data'=>$qualitative_data, 'footer_template_data'=>$footer_template_data, 'cv_block_2_data'=>$cv_block_2_data, 'cv_block_3_data'=>$cv_block_3_data, 'cv_block_4_data'=>$cv_block_4_data, 'cv_block_5_data'=>$cv_block_5_data, 'cv_block_6_data'=>$cv_block_6_data, 'cv_block_7_data'=>$cv_block_7_data, 'cv_block_8_data'=>$cv_block_8_data, 'cv_block_9_data'=>$cv_block_9_data, 'cv_block_10_data'=>$cv_block_10_data, 'cv_block_11_data'=>$cv_block_11_data, 'cv_block_12_data'=>$cv_block_12_data, 'cv_block_13_data'=>$cv_block_13_data, 'cv_block_14_data'=>$cv_block_14_data, 'cv_block_15_data'=>$cv_block_15_data, 'cv_block_16_data'=>$cv_block_16_data, 'cv_block_17_data'=>$cv_block_17_data, 'cv_block_18_data'=>$cv_block_18_data, 'cv_block_19_data'=>$cv_block_19_data,'cvs_year_data'=>$cvs_year]);
        //return view('backend.views.edit_brand_cv', ['cv_data'=>$cv_data, 'cv_parent_data'=>$cv_parent_data, 'cv_parent_ids_array'=>$cv_parent_ids_array, 'industry_data'=>$industry_data, 'music_taste_data'=>$music_taste_data, 'experience_data'=>$experience_data, 'qualitative_data'=>$qualitative_data, 'footer_template_data'=>$footer_template_data, 'cv_block_2_data'=>$cv_block_2_data, 'cv_block_3_data'=>$cv_block_3_data, 'cv_block_4_data'=>$cv_block_4_data, 'cv_block_5_data'=>$cv_block_5_data, 'cv_block_6_data'=>$cv_block_6_data, 'cv_block_7_data'=>$cv_block_7_data, 'cv_block_9_data'=>$cv_block_9_data, 'cv_block_10_data'=>$cv_block_10_data, 'cv_block_11_data'=>$cv_block_11_data, 'cv_block_12_data'=>$cv_block_12_data, 'cv_block_13_data'=>$cv_block_13_data, 'cv_block_14_data'=>$cv_block_14_data, 'cv_block_15_data'=>$cv_block_15_data]);
    }

    function saveDuplicateBrandCv(Request $request)
    {
        $request->validate([
            "cv_type"=>'required',
            "cv_name"=>'required',
            "cv_date"=>'required',
            "industry_name"=>'required|not_in:0'
        ]);
        // echo $request->cv_logo;
        // return $request->input();
        // exit;

        if($request->industry_name == '' || $request->industry_name == null)
        {
            $industry_name_id = $request->old_industry_name;
        }
        else
        {
            $industry_name_id = $request->industry_name;
        }
        if($request->industry_name == $request->old_industry_name)
        {
            if($request->sub_industry_name == '' || $request->sub_industry_name == null)
            {
                $sub_industry_name_id = $request->old_sub_industry_name;
            }
            else
            {
                $sub_industry_name_id = $request->sub_industry_name;
            }
        }
        else
        {
            $sub_industry_name_id = $request->sub_industry_name;
        }

        if($request->parent_cv_name != '' || $request->parent_cv_name != '0#_#sel')
        {

            $parent_data = Str::of($request->parent_cv_name)->split('/#_#/');
            $parent_id = ($parent_data[0] != 0 || $parent_data[0] != '0') ? $parent_data[0] : null;
            $parent_name = ($parent_data[1] != 'sel') ? $parent_data[1] : null;
        }
        else
        {
            $parent_id = null;
            $parent_name = null;
        }



        $block_1_data = ['type' => $request->cv_type,
        'parent_id' => $parent_id,
        'parent' => $parent_name,
        'cv_name' => $request->cv_name,
        'cv_date' => $request->cv_date,
        'cv_year' => explode('-',$request->cv_date)[1],
        'industry_id'=>$industry_name_id,
        'sub_industry_id'=>$sub_industry_name_id,
        'footer_template_id' => $request->footer_template_name,
        'created_by' => session('LoggedUser'),
        'md_flag' => $request->missing_data_flag_name];
        if(DB::table('tbl_cvs')->insertOrIgnore($block_1_data))
        {
            $last_inserted_id = DB::table('tbl_cvs')
            ->where('type', $request->cv_type)
            ->where('parent_id', $parent_id)
            ->where('parent', $parent_name)
            ->where('cv_name', $request->cv_name)
            ->where('cv_date', $request->cv_date)
            ->where('industry_id', $request->industry_name)
            ->where('footer_template_id', $request->footer_template_name)
            ->first();
            $id = $last_inserted_id->cv_id;
        }
        else
        {
            $error_data = "Something went wrong while inserting block_1_data";
            ErrorMailSender::sendErrorMail($error_data);
            return back()->with('fail', 'Something went wrong while inserting cv data, please try again!');
        }
        //echo $id;
        if ($id == 0 || $id == '')
        {
            $error_data = "Something went wrong while inserting block_1_data";
            ErrorMailSender::sendErrorMail($error_data);
            return back()->with('fail', 'Something went wrong while inserting cv data, please try again!');
        }
        else
        {
            if($request->hasfile('cv_logo'))
            {
                //$request->validate(["cv_logo"=>'image|mimes:jpeg,png,jpg|max:2048']);
                $image = $request->cv_logo;
                $img_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8').'.'.$image->getClientOriginalExtension());
                //print_r($image);
                //print_r($image->path());exit;
                $destinationPath = public_path('/images/cv_logos/thumbnail');
                $img = Image::make($image->path());
                $img->resize(300, 300, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath.'/'.$img_name);

                $destinationPath = public_path('/images/cv_logos/medium');
                $img = Image::make($image->path());
                $img->resize(600, 600, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath.'/'.$img_name);

                $destinationPath = public_path('/images/cv_banners/desktop');
                $img = Image::make($image->path());
                $img->resize(500, 500, function ($constraint1) {
                    $constraint1->aspectRatio();
                });
                $img1 = Image::canvas(1600, 410, '#ffffff')->insert($img, 'center');
                $img1->save($destinationPath.'/'.$img_name);

                $destinationPath = public_path('/images/cv_banners/ipad');
                $img = Image::make($image->path());
                $img->resize(400, 400, function ($constraint1) {
                    $constraint1->aspectRatio();
                });
                $img1 = Image::canvas(1024, 262, '#ffffff')->insert($img, 'center');
                $img1->save($destinationPath.'/'.$img_name);

                $destinationPath = public_path('/images/cv_banners/mobile');
                $img = Image::make($image->path());
                $img->resize(400, 400, function ($constraint1) {
                    $constraint1->aspectRatio();
                });
                $img1 = Image::canvas(640, 260, '#ffffff')->insert($img, 'center');
                $img1->save($destinationPath.'/'.$img_name);

                if($image->move(public_path('images/cv_logos/original'), $img_name))
                {
                    DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_logo' => '']);
                    if(DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_logo' => $img_name, 'edited_by'=>session('LoggedUser')]))
                    {
                        DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_banner_desktop' => $img_name]);
                        DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_banner_ipad' => $img_name]);
                        DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_banner_mobile' => $img_name]);
                    }
                }
            }
            else
            {
                // DB::table('tbl_cvs')->where('cv_id', $id)->update(['is_active' => '1']);
                $currnt_cv_logo = DB::table('tbl_cvs')->where('cv_id', $request->cv_id)->first();

                if($currnt_cv_logo->cv_logo != '' && $currnt_cv_logo->cv_logo != null)
                {
                    $oldPath = '/images/cv_logos/original/'.$currnt_cv_logo->cv_logo;

                    $fileExtension = File::extension($oldPath);
                    $newName = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8')).'.'.$fileExtension;
                    $newPathWithName = '/images/cv_logos/original/'.$newName;

                    if (File::copy(public_path($oldPath) , public_path($newPathWithName)))
                    {
                        if(DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_logo' => $newName, 'is_active' => '0', 'edited_by'=>session('LoggedUser')]))
                        {
                            $oldThumbnailPath = '/images/cv_logos/thumbnail/'.$currnt_cv_logo->cv_logo;
                            $thumbnailFileExtension = File::extension($oldThumbnailPath);
                            $newThumbnailName = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8')).'.'.$thumbnailFileExtension;
                            $newThumbnailPathWithName = '/images/cv_logos/thumbnail/'.$newThumbnailName;
                            File::copy(public_path($oldThumbnailPath), public_path($newThumbnailPathWithName));

                            $oldMediumPath = '/images/cv_logos/medium/'.$currnt_cv_logo->cv_logo;
                            $mediumFileExtension = File::extension($oldMediumPath);
                            $newMediumName = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8')).'.'.$mediumFileExtension;
                            $newMediumPathWithName = '/images/cv_logos/thumbnail/'.$newMediumName;
                            File::copy(public_path($oldMediumPath), public_path($newMediumPathWithName));

                            $oldDesktopPath = '/images/cv_banners/desktop/'.$currnt_cv_logo->cv_logo;
                            $desktopFileExtension = File::extension($oldDesktopPath);
                            $newDesktopName = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8')).'.'.$desktopFileExtension;
                            $newDesktopPathWithName = '/images/cv_banners/desktop/'.$newDesktopName;
                            File::copy(public_path($oldDesktopPath), public_path($newDesktopPathWithName));
                            DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_banner_desktop' => $newDesktopName]);

                            $oldIpadPath = '/images/cv_banners/ipad/'.$currnt_cv_logo->cv_logo;
                            $ipadFileExtension = File::extension($oldIpadPath);
                            $newIpadName = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8')).'.'.$ipadFileExtension;
                            $newIpadPathWithName = '/images/cv_banners/ipad/'.$newIpadName;
                            File::copy(public_path($oldIpadPath) , public_path($newIpadPathWithName));
                            DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_banner_ipad' => $newIpadName]);

                            $oldMobilePath = '/images/cv_banners/mobile/'.$currnt_cv_logo->cv_logo;
                            $mobileFileExtension = File::extension($oldMobilePath);
                            $newMobileName = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8')).'.'.$mobileFileExtension;
                            $newMobilePathWithName = '/images/cv_banners/mobile/'.$newMobileName;
                            File::copy(public_path($oldMobilePath), public_path($newMobilePathWithName));
                            DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_banner_mobile' => $newMobileName]);
                        }
                    }

                }
            }

            /* if($request->hasfile('cv_banner_desktop'))
            {
                //$request->validate(["cv_banner_desktop"=>'image|mimes:jpeg,png,jpg|max:2048']);

                $image = $request->cv_banner_desktop;
                $img_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8').'.'.$image->getClientOriginalExtension());

                if($image->move(public_path('images/cv_banners/desktop'), $img_name))
                {
                    DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_banner_desktop' => '']);
                    DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_banner_desktop' => $img_name, 'edited_by'=>session('LoggedUser')]);
                }
            }
            else
            {
                $currnt_cv_banner_desktop = DB::table('tbl_cvs')->where('cv_id', $request->cv_id)->first();
                if($currnt_cv_banner_desktop != '')
                {
                    DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_banner_desktop' => $currnt_cv_banner_desktop->cv_banner_desktop, 'edited_by'=>session('LoggedUser')]);
                }
            }

            if($request->hasfile('cv_banner_ipad'))
            {
                //$request->validate(["cv_banner_ipad"=>'image|mimes:jpeg,png,jpg|max:2048']);

                $image = $request->cv_banner_ipad;
                $img_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8').'.'.$image->getClientOriginalExtension());

                if($image->move(public_path('images/cv_banners/ipad'), $img_name))
                {
                    DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_banner_ipad' => '']);
                    DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_banner_ipad' => $img_name, 'edited_by'=>session('LoggedUser')]);
                }
            }
            else
            {
                $currnt_cv_banner_ipad = DB::table('tbl_cvs')->where('cv_id', $request->cv_id)->first();
                if($currnt_cv_banner_ipad != '')
                {
                    DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_banner_ipad' => $currnt_cv_banner_ipad->cv_banner_ipad, 'edited_by'=>session('LoggedUser')]);
                }
            }

            if($request->hasfile('cv_banner_mobile'))
            {
                //$request->validate(["cv_banner_mobile"=>'image|mimes:jpeg,png,jpg|max:2048']);

                $image = $request->cv_banner_mobile;
                $img_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8').'.'.$image->getClientOriginalExtension());

                if($image->move(public_path('images/cv_banners/mobile'), $img_name))
                {
                    DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_banner_mobile' => '']);
                    DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_banner_mobile' => $img_name, 'edited_by'=>session('LoggedUser')]);
                }
            }
            else
            {
                $currnt_cv_banner_mobile = DB::table('tbl_cvs')->where('cv_id', $request->cv_id)->first();
                if($currnt_cv_banner_mobile != '')
                {
                    DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_banner_mobile' => $currnt_cv_banner_mobile->cv_banner_mobile, 'edited_by'=>session('LoggedUser')]);
                }
            } */

            /* if($request->section_2_title !='' || $request->ranking !='')
            {
                $block_2_data = [
                    'b2_title' => $request->section_2_title,
                    'b2_value' => $request->ranking,
                    'cv_id' => $id,
                    'created_by' => session('LoggedUser')
                ];
                //DB::table('tbl_cv_block_2_data')->updateOrInsert($block_2_data);
                if(DB::table('tbl_cv_block_2_data')->insertOrIgnore($block_2_data))
                {

                }
                else
                {
                    return back()->with('fail', 'Something went wrong while inserting section 2 data, please try again!');
                }
            } */

            /* if($request->cv_music_taste_name_ids !='')
            {
                $cv_music_taste_name_ids_array = explode(',' , $request->cv_music_taste_name_ids);
                for($i=0; $i<count($cv_music_taste_name_ids_array); $i++)
                {
                    $block_3_data = [
                        'b3_title' => $request->section_3_title,
                        'b3_title_id' => $cv_music_taste_name_ids_array[$i],
                        'cv_id' => $id,
                        'created_by' => session('LoggedUser')
                    ];
                    //DB::table('tbl_cv_block_3_data')->updateOrInsert($block_3_data);
                    if(DB::table('tbl_cv_block_3_data')->insertOrIgnore($block_3_data))
                    {

                    }
                    else
                    {
                        return back()->with('fail', 'Something went wrong while inserting section 3 data, please try again!');
                    }
                }
            } */

            if($request->section_4_title !='' || $request->about_description !='' || $request->about_key_findings !='')
            {
                $block_4_data = [
                    'b4_title' => $request->section_4_title,
                    'b4_description' => $request->about_description,
                    'b4_key_findings' => $request->about_key_findings,
                    'cv_id' => $id,
                    'created_by' => session('LoggedUser')
                ];
                //DB::table('tbl_cv_block_4_data')->updateOrInsert($block_4_data);
                if(DB::table('tbl_cv_block_4_data')->insertOrIgnore($block_4_data))
                {

                }
                else
                {
                    $error_data = "Something went wrong while inserting block_4_data of cv-".$id;
                    ErrorMailSender::sendErrorMail($error_data);
                    return back()->with('fail', 'Something went wrong while inserting section 4 data, please try again!');
                }
            }

            if($request->smDataCount !='0' || $request->smDataCount !='')
            {
                $imgArray = GetSocialMediaIconsData::getSMData();
                $img_name = count($imgArray);
                $smDataCount = $request->smDataCount;
                for($i=0; $i<$smDataCount; $i++)
                {
                    $smTrIcon = "smTrIcon_".$i;
                    $smTrUrl = "smTrUrl_".$i;
                    $smTrName = "smTrName_".$i;

                    if(Str::contains($request->$smTrIcon,[';base64,']))
                    {
                        $img_name++;
                        $image_parts = explode(";base64,", $request->$smTrIcon);
                        $image_type_aux = explode("image/", $image_parts[0]);
                        $image_type = $image_type_aux[1];
                        $image_base64 = base64_decode($image_parts[1]);
                        $file = public_path('images/social_media_icons/original').'/'.$img_name.'.'.$image_type;
                        file_put_contents($file, $image_base64);
                        $destinationPath = public_path('/images/social_media_icons');
                        $img = Image::make($file);
                        $img->resize(200, 200, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($destinationPath.'/'.$img_name.'.'.$image_type);

                        $block_5_data = [
                            'b5_icon_name' => $img_name.'.'.$image_type,
                            'b5_link' => $request->$smTrUrl,
                            'b5_link_name' => $request->$smTrName,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser')
                        ];
                    }
                    else
                    {
                        $block_5_data = [
                            'b5_icon_name' => Str::of($request->$smTrIcon)->afterLast('/'),
                            'b5_link' => $request->$smTrUrl,
                            'b5_link_name' => $request->$smTrName,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser')
                        ];
                    }
                    //DB::table('tbl_cv_block_5_data')->updateOrInsert($block_5_data);
                    if(DB::table('tbl_cv_block_5_data')->insertOrIgnore($block_5_data))
                    {

                    }
                    else
                    {
                        $error_data = "Something went wrong while inserting block_5_data of cv-".$id;
                        ErrorMailSender::sendErrorMail($error_data);
                        return back()->with('fail', 'Something went wrong while inserting section 5 data, please try again!');
                    }
                }
            }

            /* $imgArray = GetSocialMediaIconsData::getSMData();
            $img_name = count($imgArray);
            $smDataCount = $request->smDataCount;
            DB::table('tbl_cv_block_5_data')->where('cv_id', $id)->update(['is_active' => 1,'edited_by' => session('LoggedUser')]);
            for($i=0; $i<$smDataCount; $i++)
            {
                $smTrIcon = "smTrIcon_".$i;
                $smTrUrl = "smTrUrl_".$i;
                $smTrName = "smTrName_".$i;
                $smTrid = "smTrid_".$i;

                if(Str::contains($request->$smTrIcon,[';base64,']))
                {
                    $img_name++;
                    $image_parts = explode(";base64,", $request->$smTrIcon);
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type = $image_type_aux[1];
                    $image_base64 = base64_decode($image_parts[1]);
                    $file = public_path('images/social_media_icons/original').'/'.$img_name.'.'.$image_type;
                    file_put_contents($file, $image_base64);
                    $destinationPath = public_path('/images/social_media_icons');
                    $img = Image::make($file);
                    $img->resize(200, 200, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($destinationPath.'/'.$img_name.'.'.$image_type);

                    $b5_icon_name = $img_name.'.'.$image_type;
                }
                else
                {
                    $b5_icon_name = Str::of($request->$smTrIcon)->afterLast('/');
                }

                $block_5_data = [
                    'b5_id' => $request->$smTrid,
                    'b5_icon_name' => $b5_icon_name,
                    'b5_link' => $request->$smTrUrl,
                    'b5_link_name' => $request->$smTrName,
                    'cv_id' => $request->cv_id,
                    'is_active' => 0,
                    'created_by' => session('LoggedUser'),
                    'edited_by' => session('LoggedUser')
                ];

                try
                {
                    DB::table('tbl_cv_block_5_data')->upsert($block_5_data, ['b5_id','cv_id'], ['b5_icon_name','b5_link','b5_link_name','cv_id','is_active','created_by','edited_by']);
                }
                catch(\Illuminate\Database\QueryException $ex)
                {
                    //return ['error' => 'error update user'];
                    return back()->with('fail', 'Something went wrong while updating section 5 data, please try again!');
                }
            } */

            /* if($request->hasfile('sonic_logo_audio_file'))
            {

                $file = $request->sonic_logo_audio_file;
                $file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8').'.'.$file->getClientOriginalExtension());
                $file->move(public_path('audios/cv_audios'), $file_name);
                $block_6_data = [
                    'b6_title' => $request->section_6_title,
                    'b6_name' => $file_name,
                    'cv_id' => $id,
                    'edited_by' => session('LoggedUser')
                ];
                DB::table('tbl_cv_block_6_data')->upsert($block_6_data, ['cv_id'], ['b6_title','b6_name','cv_id','edited_by']);
            }
            else
            {
                $currnt_sonic_logo_audio_file = DB::table('tbl_cv_block_6_data')->where('cv_id', $request->cv_id)->first();
                if($currnt_sonic_logo_audio_file != '')
                {
                    $block_6_data = [
                        'b6_title' => $request->section_6_title,
                        'b6_name' => $currnt_sonic_logo_audio_file->b6_name,
                        'cv_id' => $id,
                        'edited_by' => session('LoggedUser')
                    ];
                    DB::table('tbl_cv_block_6_data')->upsert($block_6_data, ['cv_id'], ['b6_title','b6_name','cv_id','edited_by']);
                }
                else
                {
                    $block_6_data = [
                        'b6_title' => $request->section_6_title,
                        'cv_id' => $id,
                        'edited_by' => session('LoggedUser')
                    ];
                    DB::table('tbl_cv_block_6_data')->upsert($block_6_data, ['cv_id'], ['b6_title','cv_id','created_by','edited_by']);
                }
            } */


            $sonic_logo_count = $request->sonic_logo_count;
            for($i=0; $i<$sonic_logo_count; $i++)
            {
                $audio_title = "section_6_title_".$i;
                $audio_name = "sonic_logo_audio_file_".$i;
                $audio_id = "section_6_id_".$i;

                //echo "audio_title:".$request->$audio_title."<br>audio_name:".$request->$audio_name."<br>audio_id:".$request->$audio_id."<br>";
                try
                {
                    if($request->hasfile($audio_name))
                    {
                        if($request->$audio_name != '')
                        {
                            $file = $request->$audio_name;
                            $file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8')."_".$i.'.'.$file->getClientOriginalExtension());
                            $file->move(public_path('audios/cv_audios'), $file_name);
                            $block_6_data = [
                                'b6_title' => $request->$audio_title,
                                'b6_name' => $file_name,
                                'cv_id' => $id,
                                'is_active' => 0,
                                'created_by' => session('LoggedUser'),
                                'edited_by' => session('LoggedUser')
                            ];
                            DB::table('tbl_cv_block_6_data')->upsert($block_6_data, ['cv_id'], ['b6_title','b6_name','cv_id','is_active','created_by','edited_by']);
                            //return redirect('brand-cvs')->with('success','Brand CV data updated successfully');
                        }
                    }
                    else
                    {
                        //echo 'in else <br>';
                        $audio_name = "old_sonic_logo_audio_file_".$i;
                        if($request->$audio_id != '' && $request->$audio_name != '')
                        {
                            $mp3_data = DB::table('tbl_cv_block_6_data')->where('cv_id', '=', $request->cv_id)->where('b6_id', '=', $request->$audio_id)->first();
                            $old_file_name = $mp3_data->b6_name;
                            if(substr_count($old_file_name,".")>1)
                            {
                                if(strpos($old_file_name, '.wav') !== false || strpos($old_file_name, '.WAV') !== false)
                                {
                                    $new_file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8')."_".$i.'.'.substr($old_file_name,strrpos($old_file_name,"mp3")));
                                }
                                elseif(strpos($old_file_name, '.aac') !== false || strpos($old_file_name, '.AAC') !== false)
                                {
                                    $new_file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8')."_".$i.'.'.substr($old_file_name,strrpos($old_file_name,"aac")));
                                }
                                elseif(strpos($old_file_name, '.m4a') !== false || strpos($old_file_name, '.M4A') !== false)
                                {
                                    $new_file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8')."_".$i.'.'.substr($old_file_name,strrpos($old_file_name,"m4a")));
                                }
                                elseif(strpos($old_file_name, '.mp4') !== false || strpos($old_file_name, '.MP4') !== false)
                                {
                                    $new_file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8')."_".$i.'.'.substr($old_file_name,strrpos($old_file_name,"wav")));
                                }
                                elseif(strpos($old_file_name, '.wma') !== false || strpos($old_file_name, '.WMA') !== false)
                                {
                                    $new_file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8')."_".$i.'.'.substr($old_file_name,strrpos($old_file_name,"wma")));
                                }
                                elseif(strpos($old_file_name, '.flac') !== false || strpos($old_file_name, '.FLAC') !== false)
                                {
                                    $new_file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8')."_".$i.'.'.substr($old_file_name,strrpos($old_file_name,"flac")));
                                }
                                else
                                {
                                    $new_file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8')."_".$i.'.'.substr($old_file_name,strrpos($old_file_name,"mp3")));
                                }
                            }
                            else
                            {
                                $new_file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8')."_".$i.'.'.explode(".",$old_file_name)[1]);
                            }
                            //echo 'in if'.$old_file_name.'----'.$new_file_name.' <br>';
                            if($old_file_name != $new_file_name)
                            {
                                copy(public_path('audios/cv_audios/').$old_file_name, public_path('audios/cv_audios/').$new_file_name);
                            }

                            $block_6_data = [
                                'b6_title' => $request->$audio_title,
                                'b6_name' => $new_file_name,
                                'cv_id' => $id,
                                'is_active' => 0,
                                'created_by' => session('LoggedUser'),
                                'edited_by' => session('LoggedUser')
                            ];
                            DB::table('tbl_cv_block_6_data')->upsert($block_6_data, ['cv_id'], ['b6_title','b6_name','cv_id','is_active','created_by','edited_by']);
                            //return redirect('brand-cvs')->with('success','Brand CV data updated successfully');
                        }

                    }
                }
                catch(\Illuminate\Database\QueryException $ex)
                {
                    //return ['error' => 'error update user'];
                    $error_data = "Something went wrong while inserting / updating block_6_data of cv-".$id;
                    ErrorMailSender::sendErrorMail($error_data);
                    return back()->with('fail', 'Something went wrong while updating section 6 data, please try again!');
                }

            }

            /* if($request->section_6_title !='')
            {
                if($request->sonic_logo_audio_file !='')
                {

                    $file = $request->sonic_logo_audio_file;
                    $file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8').'.'.$file->getClientOriginalExtension());
                    $file->move(public_path('audios/cv_audios'), $file_name);
                    $block_6_data = [
                        'b6_title' => $request->section_6_title,
                        'b6_name' => $file_name,
                        'cv_id' => $id,
                        'created_by' => session('LoggedUser')
                    ];
                }
                else
                {
                    $block_6_data = [
                        'b6_title' => $request->section_6_title,
                        'cv_id' => $id,
                        'created_by' => session('LoggedUser')
                    ];
                }
                //DB::table('tbl_cv_block_6_data')->updateOrInsert($block_6_data);
                if(DB::table('tbl_cv_block_6_data')->insertOrIgnore($block_6_data))
                {

                }
                else
                {
                    return back()->with('fail', 'Something went wrong while inserting section 6 data, please try again!');
                }
            } */

            if($request->section_7_title !='' && $request->sonic_usage_count !='')
            {
                $sonic_usage_count = $request->sonic_usage_count;
                for($i=0; $i<$sonic_usage_count; $i++)
                {
                    $legend_name = "cv_sonic_usage_legend_name_".$i;
                    $legend_number = "cv_sonic_usage_legend_number_".$i;

                    if($legend_name != '' || $legend_number !='')
                    {
                        $block_7_data = [
                            'b7_title' => $request->section_7_title,
                            'b7_name' => $request->$legend_name,
                            'b7_number' => $request->$legend_number,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser')
                        ];

                        //DB::table('tbl_cv_block_7_data')->updateOrInsert($block_7_data);
                        if(DB::table('tbl_cv_block_7_data')->insertOrIgnore($block_7_data))
                        {

                        }
                        else
                        {
                            $error_data = "Something went wrong while inserting block_7_data of cv-".$id;
                            ErrorMailSender::sendErrorMail($error_data);
                            return back()->with('fail', 'Something went wrong while inserting section 7 data, please try again!');
                        }
                    }
                }
            }

            if($request->section_9_title !='' && $request->most_popular_video_count !='')
            {
                $most_popular_video_count = $request->most_popular_video_count;
                for($i=0; $i<$most_popular_video_count; $i++)
                {
                    $video_title = "cv_most_popular_video_title_".$i;
                    $video_link = "cv_most_popular_video_link_".$i;

                    if($video_title != '' || $video_link !='')
                    {
                        $block_9_data = [
                            'b9_title' => $request->section_9_title,
                            'b9_video_title' => $request->$video_title,
                            'b9_video_link' => $request->$video_link,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser')
                        ];

                        //DB::table('tbl_cv_block_9_data')->updateOrInsert($block_9_data);
                        if(DB::table('tbl_cv_block_9_data')->insertOrIgnore($block_9_data))
                        {

                        }
                        else
                        {
                            $error_data = "Something went wrong while inserting block_9_data of cv-".$id;
                            ErrorMailSender::sendErrorMail($error_data);
                            return back()->with('fail', 'Something went wrong while inserting section 9 data, please try again!');
                        }
                    }
                }
            }

            /* if($request->section_10_title !='' && $request->a_day_in_my_life_count !='')
            {
                $img_name = '';
                $a_day_in_my_life_count = $request->a_day_in_my_life_count;
                for($i=0; $i<$a_day_in_my_life_count; $i++)
                {
                    $name_id = "cv_a_day_in_my_life_name_id_".$i;
                    $number = "cv_a_day_in_my_life_number_".$i;
                    $color = "cv_a_day_in_my_life_color_".$i;

                    if($name_id != '' || $number !='')
                    {
                        $block_10_data = [
                            'b10_title' => $request->section_10_title,
                            //'b10_bg_color' => $request->section_10_bg_color,
                            'b10_name_id' => $request->$name_id,
                            'b10_number' => $request->$number,
                            'b10_color' => $request->$color,
                            'b10_bg_image' => $img_name,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser')
                        ];

                        //DB::table('tbl_cv_block_10_data')->updateOrInsert($block_10_data);
                        if(DB::table('tbl_cv_block_10_data')->insertOrIgnore($block_10_data))
                        {

                        }
                        else
                        {
                            return back()->with('fail', 'Something went wrong while inserting section 10 data, please try again!');
                        }
                    }
                }
            } */

            /* if($request->smsDataCount !='0' || $request->smsDataCount !='')
            {
                $imgArray = GetSocialMediaIconsData::getSMData();
                $img_name = count($imgArray);
                $smsDataCount = $request->smsDataCount;
                for($i=0; $i<$smsDataCount; $i++)
                {
                    $smsTrIcon = "smsTrIcon_".$i;
                    $smsTrUrl = "smsTrUrl_".$i;
                    $smsTrName = "smsTrName_".$i;
                    $smsTrNumber = "smsTrNumber_".$i;
                    $smsTrtxt = "smsTrTxt_".$i;

                    if(Str::contains($request->$smsTrIcon,[';base64,']))
                    {
                        $img_name++;
                        $image_parts = explode(";base64,", $request->$smsTrIcon);
                        $image_type_aux = explode("image/", $image_parts[0]);
                        $image_type = $image_type_aux[1];
                        $image_base64 = base64_decode($image_parts[1]);
                        $file = public_path('images/social_media_icons/original').'/'.$img_name.'.'.$image_type;
                        file_put_contents($file, $image_base64);
                        $destinationPath = public_path('/images/social_media_icons');
                        $img = Image::make($file);
                        $img->resize(200, 200, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($destinationPath.'/'.$img_name.'.'.$image_type);

                        $block_12_data = [
                            'b12_title' => $request->section_12_title,
                            'b12_icon_name' => $img_name.'.'.$image_type,
                            'b12_link' => $request->$smsTrUrl,
                            'b12_link_name' => $request->$smsTrName,
                            'b12_link_number' => $request->$smsTrNumber,
                            'b12_link_txt' => $request->$smsTrtxt,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser')
                        ];
                    }
                    else
                    {
                        $block_12_data = [
                            'b12_title' => $request->section_12_title,
                            'b12_icon_name' => Str::of($request->$smsTrIcon)->afterLast('/'),
                            'b12_link' => $request->$smsTrUrl,
                            'b12_link_name' => $request->$smsTrName,
                            'b12_link_number' => $request->$smsTrNumber,
                            'b12_link_txt' => $request->$smsTrtxt,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser')
                        ];
                    }
                    //DB::table('tbl_cv_block_12_data')->updateOrInsert($block_12_data);
                    if(DB::table('tbl_cv_block_12_data')->insertOrIgnore($block_12_data))
                    {

                    }
                    else
                    {
                        return back()->with('fail', 'Something went wrong while inserting section 12 data, please try again!');
                    }
                }
            } */

            if($request->section_13_title !='' && $request->efb_count !='')
            {

                $img_name = '';
                $efb_count = $request->efb_count;
                for($i=0; $i<$efb_count; $i++)
                {
                    $efb_name_id = "cv_efb_name_id_".$i;
                    $efb_number = "cv_efb_number_".$i;

                    if($efb_name_id != '' || $efb_number !='')
                    {
                        $block_13_data = [
                            'b13_title' => $request->section_13_title,
                            //'b13_bg_color' => $request->section_13_bg_color,
                            'b13_name_id' => $request->$efb_name_id,
                            'b13_number' => $request->$efb_number,
                            'b13_bg_image' => $img_name,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser')
                        ];

                        //DB::table('tbl_cv_block_13_data')->updateOrInsert($block_13_data);
                        if(DB::table('tbl_cv_block_13_data')->insertOrIgnore($block_13_data))
                        {

                        }
                        else
                        {
                            $error_data = "Something went wrong while inserting block_13_data of cv-".$id;
                            ErrorMailSender::sendErrorMail($error_data);
                            return back()->with('fail', 'Something went wrong while inserting section 13 data, please try again!');
                        }
                    }
                }
            }

            if($request->section_14_title !='' && $request->mepy_count !='')
            {
                $mepy_count = $request->mepy_count;
                for($i=0; $i<$mepy_count; $i++)
                {
                    $mepy_number = "mepy_number_".$i;
                    $mepy_description = "mepy_description_".$i;

                    if($mepy_number != '' || $mepy_description !='')
                    {
                        $block_14_data = [
                            'b14_title' => $request->section_14_title,
                            'b14_number' => $request->$mepy_number,
                            'b14_description' => $request->$mepy_description,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser')
                        ];

                        //DB::table('tbl_cv_block_14_data')->updateOrInsert($block_14_data);
                        if(DB::table('tbl_cv_block_14_data')->insertOrIgnore($block_14_data))
                        {

                        }
                        else
                        {
                            $error_data = "Something went wrong while inserting block_14_data of cv-".$id;
                            ErrorMailSender::sendErrorMail($error_data);
                            return back()->with('fail', 'Something went wrong while inserting section 14 data, please try again!');
                        }
                    }
                }
            }

            if($request->section_15_title !='' && $request->mepv_count !='')
            {
                $mepv_count = $request->mepv_count;
                for($i=0; $i<$mepv_count; $i++)
                {
                    $mepv_number = "mepv_number_".$i;
                    $mepv_description = "mepv_description_".$i;

                    if($mepv_number != '' || $mepv_description !='')
                    {
                        $block_15_data = [
                            'b15_title' => $request->section_15_title,
                            'b15_number' => $request->$mepv_number,
                            'b15_description' => $request->$mepv_description,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser')
                        ];

                        //DB::table('tbl_cv_block_15_data')->updateOrInsert($block_15_data);
                        if(DB::table('tbl_cv_block_15_data')->insertOrIgnore($block_15_data))
                        {
                        }
                        else
                        {
                            $error_data = "Something went wrong while inserting block_15_data of cv-".$id;
                            ErrorMailSender::sendErrorMail($error_data);
                            return back()->with('fail', 'Something went wrong while inserting section 15 data, please try again!');
                        }
                    }
                }
            }

            $c_date = date('Y-m-d h:i:s');
            if($request->ychn_name_0 !='' && $request->ychn_count !='')
            {
                $ychn_count = $request->ychn_count;

                for($i=0; $i<$ychn_count; $i++)
                {
                    $ychn_name = 'ychn_name_'.$i;
                    $og_ychn_start_date = 'ychn_start_date_'.$i;
                    $og_ychn_end_date = 'ychn_end_date_'.$i;


                    if($request->$ychn_name !='')
                    {
                        if($request->$og_ychn_start_date !='')
                        {
                            $chn_start_date = explode("-",$request->$og_ychn_start_date)[1]."-".explode("-",$request->$og_ychn_start_date)[0]."-01 00:00:00";
                            $ychn_start_date = $request->$og_ychn_start_date;
                        }
                        else
                        {
                            $chn_start_date = explode("-",$request->cv_date)[1]."-01-01 00:00:00";
                            $ychn_start_date = "01-".explode("-",$request->cv_date)[1];
                        }

                        if($request->$og_ychn_end_date !='')
                        {
                            $end_date_month = explode("-",$request->$og_ychn_end_date)[0];
                            $month_with_31days = array("01","03","05","07","08","10","12");
                            if(in_array($end_date_month, $month_with_31days))
                            {
                                $end_month_days = "-31 00:00:00";
                            }
                            elseif($end_date_month == "02")
                            {
                                $end_month_days = "-28 00:00:00";
                            }
                            else
                            {
                                $end_month_days = "-30 00:00:00";
                            }
                            $chn_end_date = explode("-",$request->$og_ychn_end_date)[1]."-".$end_date_month.$end_month_days;
                            $ychn_end_date = $request->$og_ychn_end_date;
                        }
                        else
                        {
                            $chn_end_date = explode("-",$request->cv_date)[1]."-12-31 00:00:00";
                            $ychn_end_date = "12-".explode("-",$request->cv_date)[1];
                        }

                        $block_16_data = [
                            'chn_name' => preg_replace('/^@/', '', $request->$ychn_name),
                            'start_date' => $chn_start_date,
                            'start_month' => $ychn_start_date,
                            'end_date' => $chn_end_date,
                            'end_month' => $ychn_end_date,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser'),
                            'c_date' => $c_date
                        ];

                        $chn_id = DB::table('tbl_cv_block_16_data')->insertGetId($block_16_data);

                        $get_crate_id = DB::table('tbl_social_spyder_graph_meta_data')->where('cv_id','=',$request->cv_id)->where('is_active','=',0)->first();
                        if($get_crate_id != '')
                        {
                            $crate_id = $get_crate_id->crate_id;
                        }
                        else
                        {
                            $crate_id = "";
                        }

                        $request_json = '{ "process_type" : "youtube", "name": "'.preg_replace('/^@/', '', $request->$ychn_name).'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$id.'","chn_id":"y_'.$chn_id.'","c_date":"'.$c_date.'" }';

                        $request_data = [
                            'cv_id' => $id,
                            'chn_id' => "y_".$chn_id,
                            //'request_json' => str_replace("]","",str_replace("[","",json_encode($request_json))),
                            'request_json' => $request_json,
                            'process_type' => "youtube",
                            'created_by' => session('LoggedUser'),
                            'c_date' => $c_date
                        ];
                        //print_r($request_data);
                        if(DB::table('tbl_social_spyder_graph_request_data')->insert($request_data))
                        {

                        }
                        else
                        {
                            $error_data = "Something went wrong while inserting block_16_data of cv-".$id;
                            ErrorMailSender::sendErrorMail($error_data);
                            return back()->with('fail', 'Something went wrong while inserting section 16 data, please try again!');
                        }
                    }
                }
            }

            if($request->ichn_name_0 !='' && $request->ichn_count !='')
            {
                $ichn_count = $request->ichn_count;

                for($i=0; $i<$ichn_count; $i++)
                {
                    $ichn_name = 'ichn_name_'.$i;
                    $og_ichn_start_date = 'ichn_start_date_'.$i;
                    $og_ichn_end_date = 'ichn_end_date_'.$i;

                    if($request->$ichn_name !='')
                    {
                        if($request->$og_ichn_start_date !='')
                        {
                            $chn_start_date = explode("-",$request->$og_ichn_start_date)[1]."-".explode("-",$request->$og_ichn_start_date)[0]."-01 00:00:00";
                            $ichn_start_date = $request->$og_ichn_start_date;
                        }
                        else
                        {
                            $chn_start_date = explode("-",$request->cv_date)[1]."-01-01 00:00:00";
                            $ichn_start_date = "01-".explode("-",$request->cv_date)[1];
                        }

                        if($request->$og_ichn_end_date !='')
                        {
                            $end_date_month = explode("-",$request->$og_ichn_end_date)[0];
                            $month_with_31days = array("01","03","05","07","08","10","12");
                            if(in_array($end_date_month, $month_with_31days))
                            {
                                $end_month_days = "-31 00:00:00";
                            }
                            elseif($end_date_month == "02")
                            {
                                $end_month_days = "-28 00:00:00";
                            }
                            else
                            {
                                $end_month_days = "-30 00:00:00";
                            }
                            $chn_end_date = explode("-",$request->$og_ichn_end_date)[1]."-".$end_date_month.$end_month_days;
                            $ichn_end_date = $request->$og_ichn_end_date;
                        }
                        else
                        {
                            $chn_end_date = explode("-",$request->cv_date)[1]."-12-31 00:00:00";
                            $ichn_end_date = "12-".explode("-",$request->cv_date)[1];
                        }

                        $block_17_data = [
                            'chn_name' => preg_replace('/^@/', '', $request->$ichn_name),
                            'start_date' => $chn_start_date,
                            'start_month' => $ichn_start_date,
                            'end_date' => $chn_end_date,
                            'end_month' => $ichn_end_date,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser'),
                            'c_date' => $c_date
                        ];

                        $chn_id = DB::table('tbl_cv_block_17_data')->insertGetId($block_17_data);

                        $get_crate_id = DB::table('tbl_social_spyder_graph_meta_data')->where('cv_id','=',$request->cv_id)->where('is_active','=',0)->first();
                        if($get_crate_id != '')
                        {
                            $crate_id = $get_crate_id->crate_id;
                        }
                        else
                        {
                            $crate_id = "";
                        }

                        $request_json = '{ "process_type" : "instagram", "name": "'.preg_replace('/^@/', '', $request->$ichn_name).'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$id.'","chn_id":"i_'.$chn_id.'","c_date":"'.$c_date.'" }';

                        $request_data = [
                            'cv_id' => $id,
                            'chn_id' => "i_".$chn_id,
                            //'request_json' => str_replace("]","",str_replace("[","",json_encode($request_json))),
                            'request_json' => $request_json,
                            'process_type' => "instagram",
                            'created_by' => session('LoggedUser'),
                            'c_date' => $c_date
                        ];
                        //print_r($request_data);
                        if(DB::table('tbl_social_spyder_graph_request_data')->insert($request_data))
                        {

                        }
                        else
                        {
                            $error_data = "Something went wrong while inserting block_17_data of cv-".$id;
                            ErrorMailSender::sendErrorMail($error_data);
                            return back()->with('fail', 'Something went wrong while inserting section 17 data, please try again!');
                        }
                    }
                }
            }

            if($request->tchn_name_0 !='' && $request->tchn_count !='')
            {
                $tchn_count = $request->tchn_count;

                for($i=0; $i<$tchn_count; $i++)
                {
                    $tchn_name = 'tchn_name_'.$i;
                    $og_tchn_start_date = 'tchn_start_date_'.$i;
                    $og_tchn_end_date = 'tchn_end_date_'.$i;

                    if($request->$tchn_name !='')
                    {
                        if($request->$og_tchn_start_date !='')
                        {
                            $chn_start_date = explode("-",$request->$og_tchn_start_date)[1]."-".explode("-",$request->$og_tchn_start_date)[0]."-01 00:00:00";
                            $tchn_start_date = $request->$og_tchn_start_date;
                        }
                        else
                        {
                            $chn_start_date = explode("-",$request->cv_date)[1]."-01-01 00:00:00";
                            $tchn_start_date = "01-".explode("-",$request->cv_date)[1];
                        }

                        if($request->$og_tchn_end_date !='')
                        {
                            $end_date_month = explode("-",$request->$og_tchn_end_date)[0];
                            $month_with_31days = array("01","03","05","07","08","10","12");
                            if(in_array($end_date_month, $month_with_31days))
                            {
                                $end_month_days = "-31 00:00:00";
                            }
                            elseif($end_date_month == "02")
                            {
                                $end_month_days = "-28 00:00:00";
                            }
                            else
                            {
                                $end_month_days = "-30 00:00:00";
                            }
                            $chn_end_date = explode("-",$request->$og_tchn_end_date)[1]."-".$end_date_month.$end_month_days;
                            $tchn_end_date = $request->$og_tchn_end_date;
                        }
                        else
                        {
                            $chn_end_date = explode("-",$request->cv_date)[1]."-12-31 00:00:00";
                            $tchn_end_date = "12-".explode("-",$request->cv_date)[1];
                        }

                        $block_18_data = [
                            'chn_name' => preg_replace('/^@/', '', $request->$tchn_name),
                            'start_date' => $chn_start_date,
                            'start_month' => $tchn_start_date,
                            'end_date' => $chn_end_date,
                            'end_month' => $tchn_end_date,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser'),
                            'c_date' => $c_date
                        ];

                        $chn_id = DB::table('tbl_cv_block_18_data')->insertGetId($block_18_data);

                        $get_crate_id = DB::table('tbl_social_spyder_graph_meta_data')->where('cv_id','=',$request->cv_id)->where('is_active','=',0)->first();
                        if($get_crate_id != '')
                        {
                            $crate_id = $get_crate_id->crate_id;
                        }
                        else
                        {
                            $crate_id = "";
                        }

                        $request_json = '{ "process_type" : "tiktok", "name": "'.preg_replace('/^@/', '', $request->$tchn_name).'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$id.'","chn_id":"t_'.$chn_id.'","c_date":"'.$c_date.'" }';

                        $request_data = [
                            'cv_id' => $id,
                            'chn_id' => "t_".$chn_id,
                            //'request_json' => str_replace("]","",str_replace("[","",json_encode($request_json))),
                            'request_json' => $request_json,
                            'process_type' => "tiktok",
                            'created_by' => session('LoggedUser'),
                            'c_date' => $c_date
                        ];
                        //print_r($request_data);
                        if(DB::table('tbl_social_spyder_graph_request_data')->insert($request_data))
                        {

                        }
                        else
                        {
                            $error_data = "Something went wrong while inserting block_18_data of cv-".$id;
                            ErrorMailSender::sendErrorMail($error_data);
                            return back()->with('fail', 'Something went wrong while inserting section 18 data, please try again!');
                        }
                    }
                }
            }

            if($request->twtchn_name_0 !='' && $request->twtchn_count !='')
            {
                $twtchn_count = $request->twtchn_count;

                for($i=0; $i<$twtchn_count; $i++)
                {
                    $twtchn_name = 'twtchn_name_'.$i;
                    $og_twtchn_start_date = 'twtchn_start_date_'.$i;
                    $og_twtchn_end_date = 'twtchn_end_date_'.$i;

                    if($request->$twtchn_name !='')
                    {
                        if($request->$og_twtchn_start_date !='')
                        {
                            $chn_start_date = explode("-",$request->$og_twtchn_start_date)[1]."-".explode("-",$request->$og_twtchn_start_date)[0]."-01 00:00:00";
                            $twtchn_start_date = $request->$og_twtchn_start_date;
                        }
                        else
                        {
                            $chn_start_date = explode("-",$request->cv_date)[1]."-01-01 00:00:00";
                            $twtchn_start_date = "01-".explode("-",$request->cv_date)[1];
                        }

                        if($request->$og_twtchn_end_date !='')
                        {
                            $end_date_month = explode("-",$request->$og_twtchn_end_date)[0];
                            $month_with_31days = array("01","03","05","07","08","10","12");
                            if(in_array($end_date_month, $month_with_31days))
                            {
                                $end_month_days = "-31 00:00:00";
                            }
                            elseif($end_date_month == "02")
                            {
                                $end_month_days = "-28 00:00:00";
                            }
                            else
                            {
                                $end_month_days = "-30 00:00:00";
                            }
                            $chn_end_date = explode("-",$request->$og_twtchn_end_date)[1]."-".$end_date_month.$end_month_days;
                            $twtchn_end_date = $request->$og_twtchn_end_date;
                        }
                        else
                        {
                            $chn_end_date = explode("-",$request->cv_date)[1]."-12-31 00:00:00";
                            $twtchn_end_date = "12-".explode("-",$request->cv_date)[1];
                        }

                        $block_19_data = [
                            'chn_name' => preg_replace('/^@/', '', $request->$twtchn_name),
                            'start_date' => $chn_start_date,
                            'start_month' => $twtchn_start_date,
                            'end_date' => $chn_end_date,
                            'end_month' => $twtchn_end_date,
                            'cv_id' => $id,
                            'created_by' => session('LoggedUser'),
                            'c_date' => $c_date
                        ];

                        $chn_id = DB::table('tbl_cv_block_19_data')->insertGetId($block_19_data);

                        $get_crate_id = DB::table('tbl_social_spyder_graph_meta_data')->where('cv_id','=',$request->cv_id)->where('is_active','=',0)->first();
                        if($get_crate_id != '')
                        {
                            $crate_id = $get_crate_id->crate_id;
                        }
                        else
                        {
                            $crate_id = "";
                        }

                        $request_json = '{ "process_type" : "twitter", "name": "'.preg_replace('/^@/', '', $request->$twtchn_name).'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$id.'","chn_id":"twt_'.$chn_id.'","c_date":"'.$c_date.'" }';

                        $request_data = [
                            'cv_id' => $id,
                            'chn_id' => "twt_".$chn_id,
                            //'request_json' => str_replace("]","",str_replace("[","",json_encode($request_json))),
                            'request_json' => $request_json,
                            'process_type' => "twitter",
                            'created_by' => session('LoggedUser'),
                            'c_date' => $c_date
                        ];
                        //print_r($request_data);
                        if(DB::table('tbl_social_spyder_graph_request_data')->insert($request_data))
                        {

                        }
                        else
                        {
                            $error_data = "Something went wrong while inserting block_19_data of cv-".$id;
                            ErrorMailSender::sendErrorMail($error_data);
                            return back()->with('fail', 'Something went wrong while inserting section 18 data, please try again!');
                        }
                    }
                }
            }

            return redirect('brand-cvs')->with('success','Brand Sonic Radar data inserted successfully');
        }
    }

    public function listYoutubeSyncCvs()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.social_media_youtube_sync_list',['cvs_year_data'=>$cvs_year]);
    }


    function getYoutubeSyncCvs(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('tbl_cv_block_16_data')
            ->join('tbl_cvs', 'tbl_cv_block_16_data.cv_id', '=', 'tbl_cvs.cv_id')
            ->select('tbl_cv_block_16_data.*', 'tbl_cvs.cv_id', 'tbl_cvs.cv_name', 'tbl_cvs.cv_date')
            ->where('tbl_cv_block_16_data.is_active','=','0')
            ->get();


            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('cv_name', function($data){
                    $cv_name = $data->cv_name." ".explode("-",$data->cv_date)[1];
                    return $cv_name;
                })
                ->addColumn('sdate', function($data){
                    $sdate = str_replace(" 00:00:00","",$data->start_date)." To ".str_replace(" 00:00:00","",$data->end_date);
                    return $sdate;
                })
                ->addColumn('status', function($data){
                    $status_qry1 = DB::table('tbl_social_spyder_graph_request_data')->where('chn_id', '=', 'y_'.$data->b16_id)->where('status', '!=', '2')->where('is_active','=','0')->first();
                    if($status_qry1 != '')
                    {
                        if($status_qry1->status == 0)
                        {
                            $status = "Pending";
                        }
                        else
                        {
                            $status = "In Process";
                        }

                    }
                    else
                    {
                        $status_qry = DB::table('tbl_social_spyder_graph_meta_data')->where('chn_id', '=', 'y_'.$data->b16_id)->where('status', '<', '4')->where('is_active','=','0')->get();
                        if(count($status_qry) == 0)
                        {
                            $genre_status_qry = DB::table('tbl_social_media_yt_genre_graph_data')->where('cv_id', '=', $data->cv_id)->where('is_Active','=',0)->get();
                            $mood_status_qry = DB::table('tbl_social_media_yt_mood_graph_data')->where('cv_id', '=', $data->cv_id)->where('is_Active','=',0)->get();
                            if(count($genre_status_qry) == 0 && count($mood_status_qry) == 0)
                            {
                                $status = "No Data";
                            }
                            else
                            {
                                $status = "Complete";
                            }

                        }
                        else
                        {
                            $status = "In Process";
                        }
                    }
                    return $status;
                })
                ->setRowId(function($data){
                    return $data->cv_id;
                })
                ->make(true);
        }
    }

    /* function getYoutubeSyncCvs(Request $request)
    {


            $data = DB::select(DB::raw("SELECT DISTINCT tbl_cvs.cv_id, tbl_cvs.cv_name, tbl_cvs.cv_date, tbl_cv_block_16_data.chn_name,tbl_cv_block_16_data.b16_id, tbl_cv_block_16_data.start_date, tbl_cv_block_16_data.end_date,tbl_social_spyder_graph_request_data.status as status1,tbl_social_spyder_graph_meta_data.status as status2
            FROM tbl_cvs INNER JOIN tbl_cv_block_16_data ON tbl_cv_block_16_data.cv_id = tbl_cvs.cv_id INNER JOIN tbl_social_spyder_graph_request_data ON TRIM(LEADING 'y_' FROM tbl_social_spyder_graph_request_data.chn_id ) = tbl_cv_block_16_data.b16_id AND tbl_social_spyder_graph_request_data.is_active=0 INNER JOIN tbl_social_spyder_graph_meta_data ON TRIM(LEADING 'y_' FROM tbl_social_spyder_graph_meta_data.chn_id ) = tbl_cv_block_16_data.b16_id AND tbl_social_spyder_graph_meta_data.is_active=0 WHERE tbl_cv_block_16_data.cv_id = tbl_cvs.cv_id and tbl_cv_block_16_data.is_active=0 and tbl_cvs.status=1"));
            $data = json_encode($data);

            $data1 = json_decode($data);

             return Datatables::of($data1)
                ->addIndexColumn()
                ->addColumn('cv_name', function($data1){
                    $cv_name = $data1->cv_name." ".explode("-",$data1->cv_date)[1];
                    return $cv_name;
                })
                ->addColumn('sdate', function($data1){
                    $sdate = str_replace(" 00:00:00","",$data1->start_date)." To ".str_replace(" 00:00:00","",$data1->end_date);
                    return $sdate;
                })
                ->addColumn('status', function($data1){
                    if($data1->status2 == '' || $data1->status2 == NULL)
                    {
                        $status = "No Data";
                    }
                    else
                    {
                        if($data1->status2 == 6 )
                        {
                            $status = "Complete";
                        }
                        else
                        {
                            if($data1->status1 == 0)
                            {
                                $status = "Pending";
                            }
                            else
                            {
                                $status = "In Process";
                            }
                        }
                    }

                    return $status;
                })
                ->setRowId(function($data1){
                    return $data1->cv_id;
                })
                ->make(true);

    } */

    public function listInstagramSyncCvs()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.social_media_instagram_sync_list',['cvs_year_data'=>$cvs_year]);
    }

    function getInstagramSyncCvs(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('tbl_cv_block_17_data')
            ->join('tbl_cvs', 'tbl_cv_block_17_data.cv_id', '=', 'tbl_cvs.cv_id')
            ->select('tbl_cv_block_17_data.*', 'tbl_cvs.cv_id', 'tbl_cvs.cv_name', 'tbl_cvs.cv_date')
            ->where('tbl_cv_block_17_data.is_active','=','0')
            ->get();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('cv_name', function($data){
                    $cv_name = $data->cv_name." ".explode("-",$data->cv_date)[1];
                    return $cv_name;
                })
                ->addColumn('sdate', function($data){
                    $sdate = str_replace(" 00:00:00","",$data->start_date)." To ".str_replace(" 00:00:00","",$data->end_date);
                    return $sdate;
                })
                ->addColumn('status', function($data){
                    $status_qry1 = DB::table('tbl_social_spyder_graph_request_data')->where('chn_id', '=', 'i_'.$data->b17_id)->where('status', '!=', '2')->where('is_active','=','0')->first();
                    if($status_qry1 != '')
                    {
                        if($status_qry1->status == 0)
                        {
                            $status = "Pending";
                        }
                        else
                        {
                            $status = "In Process";
                        }
                    }
                    else
                    {
                        $status_qry = DB::table('tbl_social_spyder_graph_meta_data')->where('chn_id', '=', 'i_'.$data->b17_id)->where('status', '<', '4')->where('is_active','=','0')->get();
                        if(count($status_qry) == 0)
                        {
                            $genre_status_qry = DB::table('tbl_social_media_ig_genre_graph_data')->where('cv_id', '=', $data->cv_id)->where('is_Active','=',0)->get();
                            $mood_status_qry = DB::table('tbl_social_media_ig_mood_graph_data')->where('cv_id', '=', $data->cv_id)->where('is_Active','=',0)->get();
                            if(count($genre_status_qry) == 0 && count($mood_status_qry) == 0)
                            {
                                $status = "No Data";
                            }
                            else
                            {
                                $status = "Complete";
                            }
                        }
                        else
                        {
                            $status = "In Process";
                        }
                    }
                    return $status;
                })
                ->setRowId(function($data){
                    return $data->cv_id;
                })
                ->make(true);
        }
    }

    public function listTiktokSyncCvs()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.social_media_tiktok_sync_list',['cvs_year_data'=>$cvs_year]);
    }

    function getTiktokSyncCvs(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('tbl_cv_block_18_data')
            ->join('tbl_cvs', 'tbl_cv_block_18_data.cv_id', '=', 'tbl_cvs.cv_id')
            ->select('tbl_cv_block_18_data.*', 'tbl_cvs.cv_id', 'tbl_cvs.cv_name', 'tbl_cvs.cv_date')
            ->where('tbl_cv_block_18_data.is_active','=','0')
            ->get();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('cv_name', function($data){
                    $cv_name = $data->cv_name." ".explode("-",$data->cv_date)[1];
                    return $cv_name;
                })
                ->addColumn('sdate', function($data){
                    $sdate = str_replace(" 00:00:00","",$data->start_date)." To ".str_replace(" 00:00:00","",$data->end_date);
                    return $sdate;
                })
                ->addColumn('status', function($data){
                    $status_qry1 = DB::table('tbl_social_spyder_graph_request_data')->where('chn_id', '=', 't_'.$data->b18_id)->where('status', '!=', '2')->where('is_active','=','0')->first();
                    if($status_qry1 != '')
                    {
                        if($status_qry1->status == 0)
                        {
                            $status = "Pending";
                        }
                        else
                        {
                            $status = "In Process";
                        }
                    }
                    else
                    {
                        $status_qry = DB::table('tbl_social_spyder_graph_meta_data')->where('chn_id', '=', 't_'.$data->b18_id)->where('status', '<', '4')->where('is_active','=','0')->get();
                        if(count($status_qry) == 0)
                        {
                            $genre_status_qry = DB::table('tbl_social_media_tt_genre_graph_data')->where('cv_id', '=', $data->cv_id)->where('is_Active','=',0)->get();
                            $mood_status_qry = DB::table('tbl_social_media_tt_mood_graph_data')->where('cv_id', '=', $data->cv_id)->where('is_Active','=',0)->get();
                            if(count($genre_status_qry) == 0 && count($mood_status_qry) == 0)
                            {
                                $status = "No Data";
                            }
                            else
                            {
                                $status = "Complete";
                            }
                        }
                        else
                        {
                            $status = "In Process";
                        }
                    }
                    return $status;
                })
                ->setRowId(function($data){
                    return $data->cv_id;
                })
                ->make(true);
        }
    }

    public function listSocialMediaSyncPendingProcessCvs()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.social_media_sync_pending_process_list',['cvs_year_data'=>$cvs_year]);
    }

    function getSocialMediaSyncPendingProcessCvs(Request $request)
    {
        if ($request->ajax()) {
            /* $data = DB::table('tbl_social_spyder_graph_request_data')
            ->join('tbl_cvs', 'tbl_social_spyder_graph_request_data.cv_id', '=', 'tbl_cvs.cv_id')
            ->select('tbl_cvs.cv_id', 'tbl_cvs.cv_name', 'tbl_cvs.cv_date')
            ->where('tbl_social_spyder_graph_request_data.is_active','=','0')
            ->where('tbl_social_spyder_graph_request_data.status','=','0')
            ->where('tbl_cvs.status','=','1')
            ->get(); */

            //$data1 = DB::select(DB::raw("select DISTINCT tbl_cvs.cv_id, tbl_cvs.cv_name, tbl_cvs.cv_date, tbl_cvs.industry_id, tbl_cvs.sub_industry_id from tbl_social_spyder_graph_request_data a join tbl_cvs on a.cv_id = tbl_cvs.cv_id where a.status=0 and a.is_active=0 and tbl_cvs.status=1 and tbl_cvs.is_active=0"));
            //$data1 = DB::select(DB::raw("select DISTINCT tbl_cvs.cv_id, tbl_cvs.cv_name, tbl_cvs.cv_date, tbl_cvs.industry_id, tbl_cvs.sub_industry_id from tbl_social_spyder_graph_request_data a join tbl_cvs on a.cv_id = tbl_cvs.cv_id where a.status=0 and a.is_active=0 and tbl_cvs.status=1 and tbl_cvs.is_active=0 and NOT EXISTS ( SELECT * from tbl_social_media_sync_process_data where cv_id = a.cv_id and is_active=0)"));
            //$data1 = DB::select(DB::raw("select DISTINCT tbl_cvs.cv_id as cvid, tbl_cvs.cv_name, tbl_cvs.cv_date, tbl_cvs.industry_id, tbl_cvs.sub_industry_id from tbl_cvs left join tbl_social_spyder_graph_request_data a on a.cv_id = tbl_cvs.cv_id where a.is_active=0 and tbl_cvs.status=1 and tbl_cvs.is_active=0 and tbl_cvs.cv_id NOT IN ( SELECT cv_id from tbl_social_media_sync_process_data where is_active=0) and tbl_cvs.cv_id NOT IN ( SELECT cv_id from tbl_social_spyder_graph_meta_data where is_active=0 and status=6)"));
            //$data1 = DB::select(DB::raw("select DISTINCT tbl_cvs.cv_id as cvid, tbl_cvs.cv_name, tbl_cvs.cv_date, tbl_cvs.industry_id, tbl_cvs.sub_industry_id from tbl_cvs left join tbl_social_spyder_graph_request_data a on a.cv_id = tbl_cvs.cv_id where a.is_active=0 and tbl_cvs.status=1 and tbl_cvs.is_active=0 and tbl_cvs.cv_id NOT IN ( SELECT cv_id from tbl_social_media_sync_process_data where (yt!=2 OR ig!=2 OR tt!=2 OR twt!=2) AND is_active=0)"));
            $data1 = DB::select(DB::raw("select DISTINCT tbl_cvs.cv_id as cvid, tbl_cvs.cv_name, tbl_cvs.cv_date, tbl_cvs.industry_id, tbl_cvs.sub_industry_id from tbl_cvs left join tbl_social_spyder_graph_request_data a on a.cv_id = tbl_cvs.cv_id where a.is_active=0 and tbl_cvs.status=1 and tbl_cvs.is_active=0 order by (tbl_cvs.updated_at) desc"));
            //$data1 = DB::select(DB::raw("SELECT DISTINCT t1.cv_id as cvid, t1.cv_name, t1.cv_date, t1.industry_id, t1.sub_industry_id FROM tbl_cvs as t1 JOIN tbl_social_spyder_graph_request_data as t2 ON t2.cv_id = t1.cv_id JOIN tbl_social_media_sync_process_data as t3 ON t3.cv_id = t1.cv_id WHERE t2.is_active=0 and t1.status=1 and t1.is_active=0 and (t3.yt>=2 && t3.ig>=2 && t3.tt>=2 && t3.twt>=2) and t3.is_active=0 ORDER BY t1.cv_year DESC"));
            $data1 = json_encode($data1);

            $data = json_decode($data1);

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('cv_name', function($data){
                    $cv_name = $data->cv_name." ".explode("-",$data->cv_date)[1];
                    return $cv_name;
                })
                ->addColumn('industry_name', function($data){
                    $get_industry_name = DB::table('tbl_industry')->where('industry_id', $data->industry_id)->first();
                    if($get_industry_name != '')
                    {
                        $industry_name = $get_industry_name->industry_name;
                    }
                    else
                    {
                        $industry_name = '-';
                    }
                    return $industry_name;
                })
                ->addColumn('sub_industry_name', function($data){
                    $get_sub_industry_name = DB::table('tbl_sub_industry')->where('sub_industry_id', $data->sub_industry_id)->first();
                    if($get_sub_industry_name != '')
                    {
                        $sub_industry_name = $get_sub_industry_name->sub_industry_name;
                    }
                    else
                    {
                        $sub_industry_name = '-';
                    }
                    return $sub_industry_name;
                })
                ->addColumn('action', function($data){

                    $chk_data = DB::table('tbl_social_media_sync_process_data')->where('cv_id', $data->cvid)->where(function($query) {
                        /* $query->orWhere('yt', '=', 0)
                              ->orWhere('ig', '=', 0)
                              ->orWhere('tt', '=', 0)
                              ->orWhere('twt', '=', 0)
                              ->orWhere('aggr', '=', 0); */
                            $query->orWhere('yt', '<', 2)
                                ->orWhere('ig', '<', 2)
                                ->orWhere('tt', '<', 2)
                                ->orWhere('twt', '<', 2);
                    })
                    ->where('is_active', '=', '0')->first();
                    if($chk_data != '')
                    {
                        $actionBtn = '<span class="edit btn btn-success btn-sm" style="cursor:not-allowed; opacity:0.5">re-process</span>';
                    }
                    else
                    {
                        $actionBtn = '<a href="add-brand-cv-to-process-queue/'.base64_encode($data->cvid."#_#"."addToProcessQueue").'" title="Click here to add CV to process queue" class="edit btn btn-success btn-sm">re-process</a>';
                    }
                    //$actionBtn = '<a href="add-brand-cv-to-process-queue/'.base64_encode($data->cv_id).'" title="Click here to add CV to process queue" class="edit btn btn-success btn-sm">Add to process queue</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->setRowId(function($data){
                    return $data->cvid;
                })
                ->make(true);
        }
    }

    public function listSocialMediaSyncInProcessCvs()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.social_media_sync_in_process_list',['cvs_year_data'=>$cvs_year]);
    }

    function getSocialMediaSyncInProcessCvs(Request $request)
    {
        if ($request->ajax()) {

            $data = DB::table('tbl_social_media_sync_process_data')
            ->join('tbl_cvs', 'tbl_social_media_sync_process_data.cv_id', '=', 'tbl_cvs.cv_id')
            ->select('tbl_social_media_sync_process_data.*','tbl_cvs.cv_id', 'tbl_cvs.cv_name', 'tbl_cvs.cv_date')
            ->where('tbl_social_media_sync_process_data.is_active','=','0')
            ->where(function($query) {
                $query->orWhere('tbl_social_media_sync_process_data.yt','<','2')
                      ->orWhere('tbl_social_media_sync_process_data.ig','<','2')
                      ->orWhere('tbl_social_media_sync_process_data.tt','<','2')
                      ->orWhere('tbl_social_media_sync_process_data.twt','<','2');
            })
            ->where('tbl_cvs.status','=','1')
            ->orderBy('tbl_social_media_sync_process_data.created_at','DESC')
            ->orderBy('tbl_social_media_sync_process_data.updated_at','DESC')
            ->get();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('cv_name', function($data){
                    //$get_cv_data = DB::table('tbl_cvs')->where('cv_id', '=', $data->cv_id)->first();
                    $cv_name = $data->cv_name." ".explode("-",$data->cv_date)[1];
                    return $cv_name;
                })
                ->addColumn('yt', function($data){
                    if($data->yt == 0)
                    {
                        $yt_status = "<span style='color:orange'>In Queue</span>";
                    }
                    if($data->yt == 1)
                    {
                        $yt_status = "<span style='color:orange'>In Process</span>";
                    }
                    if($data->yt == 2 || $data->yt == 6)
                    {
                        $yt_status = "<span style='color:green'>Process Completed</span>";
                    }
                    if($data->yt == 3)
                    {
                        $yt_status = "No Channel";
                    }
                    if($data->yt == 4)
                    {
                        $yt_status = "No Data";
                    }
                    if($data->yt == 5)
                    {
                        $yt_status = "Channel Not Found";
                    }
                    return $yt_status;
                })
                ->addColumn('ig', function($data){
                    if($data->ig == 0)
                    {
                        $ig_status = "<span style='color:orange'>In Queue</span>";
                    }
                    if($data->ig == 1)
                    {
                        $ig_status = "<span style='color:orange'>In Process</span>";
                    }
                    if($data->ig == 2 || $data->ig == 6)
                    {
                        $ig_status = "<span style='color:green'>Process Completed</span>";
                    }
                    if($data->ig == 3)
                    {
                        $ig_status = "No Channel";
                    }
                    if($data->ig == 4)
                    {
                        $ig_status = "No Data";
                    }
                    if($data->ig == 5)
                    {
                        $ig_status = "Channel Not Found";
                    }
                    return $ig_status;
                })
                ->addColumn('tt', function($data){
                    if($data->tt == 0)
                    {
                        $tt_status = "<span style='color:orange'>In Queue</span>";
                    }
                    if($data->tt == 1)
                    {
                        $tt_status = "<span style='color:orange'>In Process</span>";
                    }
                    if($data->tt == 2 || $data->tt == 6)
                    {
                        $tt_status = "<span style='color:green'>Process Completed</span>";
                    }
                    if($data->tt == 3)
                    {
                        $tt_status = "No Channel";
                    }
                    if($data->tt == 4)
                    {
                        $tt_status = "No Data";
                    }
                    if($data->tt == 5)
                    {
                        $tt_status = "Channel Not Found";
                    }
                    return $tt_status;
                })
                ->addColumn('twt', function($data){
                    if($data->twt == 0)
                    {
                        $twt_status = "<span style='color:orange'>In Queue</span>";
                    }
                    if($data->twt == 1)
                    {
                        $twt_status = "<span style='color:orange'>In Process</span>";
                    }
                    if($data->twt == 2 || $data->twt == 6)
                    {
                        $twt_status = "<span style='color:green'>Process Completed</span>";
                    }
                    if($data->twt == 3)
                    {
                        $twt_status = "No Channel";
                    }
                    if($data->twt == 4)
                    {
                        $twt_status = "No Data";
                    }
                    if($data->twt == 5)
                    {
                        $twt_status = "Channel Not Found";
                    }
                    return $twt_status;
                })
                /* ->addColumn('aggr', function($data){
                    if($data->aggr == 0)
                    {
                        $aggr_status = "<span style='color:orange'>In Queue</span>";
                    }
                    if($data->aggr == 1)
                    {
                        $aggr_status = "-";
                    }
                    if($data->aggr == 2)
                    {
                        if($data->yt == 2 && $data->ig == 2 && $data->tt == 2 && $data->twt == 2)
                        {
                            $aggr_status = "<span style='color:green'>Process Completed</span>";
                        }
                        else
                        {
                            $aggr_status = "<span style='color:orange'>In Process</span>";
                        }

                    }
                    if($data->aggr == 3)
                    {
                        $aggr_status = "-";
                    }
                    if($data->aggr == 4)
                    {
                        $aggr_status = "-";
                    }
                    return $aggr_status;
                }) */
                //->rawColumns(['yt','ig','tt','twt','aggr'])
                ->rawColumns(['yt','ig','tt','twt'])
                ->setRowId(function($data){
                    return $data->cv_id;
                })
                ->make(true);
        }
    }

    public function listSocialMediaSyncCompletedProcessCvs()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.social_media_sync_completed_process_list',['cvs_year_data'=>$cvs_year]);
    }

    function getSocialMediaSyncCompletedProcessCvs(Request $request)
    {
        if ($request->ajax()) {

            $data = DB::table('tbl_social_media_sync_process_data')
            ->join('tbl_cvs', 'tbl_social_media_sync_process_data.cv_id', '=', 'tbl_cvs.cv_id')
            ->select('tbl_social_media_sync_process_data.*','tbl_cvs.cv_id', 'tbl_cvs.cv_name', 'tbl_cvs.cv_date', 'tbl_cvs.industry_id', 'tbl_cvs.sub_industry_id')
            ->where('tbl_social_media_sync_process_data.is_active','=','0')
            ->where('tbl_social_media_sync_process_data.yt','>','1')
            ->where('tbl_social_media_sync_process_data.ig','>','1')
            ->where('tbl_social_media_sync_process_data.tt','>','1')
            ->where('tbl_social_media_sync_process_data.twt','>','1')
            //->where('tbl_social_media_sync_process_data.aggr','>','1')
            ->where('tbl_cvs.status','=','1')
            ->orderBy('tbl_social_media_sync_process_data.updated_at','DESC')
            ->get();

            /* $data = DB::table('tbl_social_media_sync_process_data')
            ->join('tbl_cvs', 'tbl_social_media_sync_process_data.cv_id', '=', 'tbl_cvs.cv_id')
            ->select('tbl_social_media_sync_process_data.*','tbl_cvs.cv_id', 'tbl_cvs.cv_name', 'tbl_cvs.cv_date', 'tbl_cvs.industry_id', 'tbl_cvs.sub_industry_id')
            ->where('tbl_social_media_sync_process_data.is_active','=','0')
            ->where(function($query) {
                $query->orWhere('tbl_social_media_sync_process_data.yt','>','1')
                      ->orWhere('tbl_social_media_sync_process_data.ig','>','1')
                      ->orWhere('tbl_social_media_sync_process_data.tt','>','1');
            })
            //->where('tbl_social_media_sync_process_data.aggr','>','1')
            ->where('tbl_cvs.status','=','1')
            ->get();  */




            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('cv_name', function($data){
                    $cv_name = $data->cv_name." ".explode("-",$data->cv_date)[1];
                    return $cv_name;
                })
                ->addColumn('industry_name', function($data){
                    $get_industry_name = DB::table('tbl_industry')->where('industry_id', $data->industry_id)->first();
                    if($get_industry_name != '')
                    {
                        $industry_name = $get_industry_name->industry_name;
                    }
                    else
                    {
                        $industry_name = '-';
                    }
                    return $industry_name;
                })
                ->addColumn('sub_industry_name', function($data){
                    $get_sub_industry_name = DB::table('tbl_sub_industry')->where('sub_industry_id', $data->sub_industry_id)->first();
                    if($get_sub_industry_name != '')
                    {
                        $sub_industry_name = $get_sub_industry_name->sub_industry_name;
                    }
                    else
                    {
                        $sub_industry_name = '-';
                    }
                    return $sub_industry_name;
                })
                ->addColumn('status', function($data){

                    $status = "<span style='color:green'>Process Completed</span>";

                    return $status;
                })
                ->rawColumns(['status'])
                ->setRowId(function($data){
                    return $data->cv_id;
                })
                ->make(true);
        }
    }

    function addCvToProcessQueue($id)
    {
        //$cv_id = base64_decode($id);
        $idd = base64_decode($id);
        $id = base64_encode(explode('#_#',$idd)[0]);
        $cv_id = explode('#_#',$idd)[0];
        $called_from = explode('#_#',$idd)[1];
        $chk_data = DB::table('tbl_social_media_sync_process_data')->where('cv_id', $cv_id)->first();

        if($chk_data != '')
        {
            $cv_status = $chk_data->status;
            $yt = $chk_data->yt;
            $ig = $chk_data->ig;
            $tt = $chk_data->tt;
            $twt = $chk_data->twt;
            $aggr = $chk_data->aggr;
            $yt_last_process_count = $chk_data->yt_last_process_count;
            $ig_last_process_count = $chk_data->ig_last_process_count;
            $tt_last_process_count = $chk_data->tt_last_process_count;
            $twt_last_process_count = $chk_data->twt_last_process_count;

            //echo "yt".$yt."<br>ig".$ig."<br>tt".$tt."<br>aggr".$aggr."<br>";
            //$get_sm_data_flag = DB::table('tbl_social_spyder_graph_request_data')->where('cv_id', base64_decode($id))->where('status', '=', 0)->where('is_active', '=', 0)->get();
            //$get_sm_data_flag = DB::select(DB::raw("SELECT DISTINCT(process_type) FROM tbl_social_spyder_graph_request_data WHERE cv_id='$cv_id' and status=0 and is_active=0"));

            $get_sm_data_flag = DB::select(DB::raw("SELECT DISTINCT process_type, status, request_json FROM tbl_social_spyder_graph_request_data WHERE cv_id='$cv_id' and is_active=0"));

            DB::table('tbl_social_media_sync_process_data')->where('cv_id', base64_decode($id))->update(['is_active' => '1']);
            $process_type_arr = [];
            foreach($get_sm_data_flag as $get_sm_data_flag_data)
            {
                if($get_sm_data_flag_data->process_type == 'youtube')
                {
                    if($get_sm_data_flag_data->status == 2)
                    {
                        $curdate=strtotime(date('Y-m-d'));
                        $request_json = $get_sm_data_flag_data->request_json;
                        $request_json_data_array = json_decode($request_json, True);
                        $end_date = strtotime($request_json_data_array['end_date']);

                        if($yt != 0)
                        {
                            $get_pending = DB::table('tbl_social_spyder_graph_meta_data')->where('cv_id',$cv_id)->where('is_active', '=', 0)->where('status', '>', 4)->where('process_type', '=', 'youtube')->orderBy('updated_at','desc')->first();
                            if($get_pending != '')
                            {
                                $last_process_date = strtotime(explode(" ",$get_pending->updated_at)[0]);
                                if($end_date > $last_process_date)
                                {
                                    $yt = 0;
                                    $yt_last_process_count = 0;
                                    $cv_status = 0;
                                    array_push($process_type_arr, 'youtube');
                                }
                                else
                                {
                                    $yt = 6;
                                }
                            }
                            else
                            {
                                $yt = 0;
                                $cv_status = 0;
                                array_push($process_type_arr, 'youtube');
                            }
                        }
                    }
                    else
                    {
                        $yt = 0;
                        $cv_status = 0;
                        array_push($process_type_arr, 'youtube');
                    }
                }
                if($get_sm_data_flag_data->process_type == 'instagram')
                {
                    if($get_sm_data_flag_data->status == 2)
                    {
                        $curdate=strtotime(date('Y-m-d'));
                        $request_json = $get_sm_data_flag_data->request_json;
                        $request_json_data_array = json_decode($request_json, True);
                        $end_date = strtotime($request_json_data_array['end_date']);

                        if($ig != 0)
                        {
                            /* if($end_date > $curdate)
                            {
                                $ig = 0;
                            }
                            else
                            {
                                $ig = 6;
                            } */
                            $get_pending = DB::table('tbl_social_spyder_graph_meta_data')->where('cv_id',$cv_id)->where('is_active', '=', 0)->where('status', '>', 4)->where('process_type', '=', 'instagram')->orderBy('updated_at','desc')->first();
                            if($get_pending != '')
                            {
                                $last_process_date = strtotime(explode(" ",$get_pending->updated_at)[0]);
                                if($end_date > $last_process_date)
                                {
                                    $ig = 0;
                                    $ig_last_process_count = 0;
                                    $cv_status = 0;
                                    array_push($process_type_arr, 'instagram');
                                }
                                else
                                {
                                    $ig = 6;
                                }
                            }
                            else
                            {
                                $ig = 0;
                                $cv_status = 0;
                                array_push($process_type_arr, 'instagram');
                            }

                        }
                    }
                    else
                    {
                        $ig = 0;
                        $cv_status = 0;
                        array_push($process_type_arr, 'instagram');
                    }
                }
                if($get_sm_data_flag_data->process_type == 'tiktok')
                {
                    if($get_sm_data_flag_data->status == 2)
                    {
                        $curdate=strtotime(date('Y-m-d'));
                        $request_json = $get_sm_data_flag_data->request_json;
                        $request_json_data_array = json_decode($request_json, True);
                        $end_date = strtotime($request_json_data_array['end_date']);

                        if($tt != 0)
                        {
                            /* if($end_date > $curdate)
                            {
                                $tt = 0;
                            }
                            else
                            {
                                $tt = 6;
                            } */
                            $get_pending = DB::table('tbl_social_spyder_graph_meta_data')->where('cv_id',$cv_id)->where('is_active', '=', 0)->where('status', '>', 4)->where('process_type', '=', 'tiktok')->orderBy('updated_at','desc')->first();
                            if($get_pending != '')
                            {
                                $last_process_date = strtotime(explode(" ",$get_pending->updated_at)[0]);
                                if($end_date > $last_process_date)
                                {
                                    $tt = 0;
                                    $tt_last_process_count = 0;
                                    $cv_status = 0;
                                    array_push($process_type_arr, 'tiktok');
                                }
                                else
                                {
                                    $tt = 6;
                                }
                            }
                            else
                            {
                                $tt = 0;
                                $cv_status = 0;
                                array_push($process_type_arr, 'tiktok');
                            }

                        }
                    }
                    else
                    {
                        $tt = 0;
                        $cv_status = 0;
                        array_push($process_type_arr, 'tiktok');
                    }
                }
                if($get_sm_data_flag_data->process_type == 'twitter')
                {
                    if($get_sm_data_flag_data->status == 2)
                    {
                        $curdate=strtotime(date('Y-m-d'));
                        $request_json = $get_sm_data_flag_data->request_json;
                        $request_json_data_array = json_decode($request_json, True);
                        $end_date = strtotime($request_json_data_array['end_date']);

                        if($twt != 0)
                        {
                            /* if($end_date > $curdate)
                            {
                                $twt = 0;
                            }
                            else
                            {
                                $twt = 6;
                            } */
                            $get_pending = DB::table('tbl_social_spyder_graph_meta_data')->where('cv_id',$cv_id)->where('is_active', '=', 0)->where('status', '>', 4)->where('process_type', '=', 'twitter')->orderBy('updated_at','desc')->first();
                            if($get_pending != '')
                            {
                                $last_process_date = strtotime(explode(" ",$get_pending->updated_at)[0]);
                                if($end_date > $last_process_date)
                                {
                                    $twt = 0;
                                    $twt_last_process_count = 0;
                                    $cv_status = 0;
                                    array_push($process_type_arr, 'twitter');
                                }
                                else
                                {
                                    $twt = 6;
                                }
                            }
                            else
                            {
                                $twt = 0;
                                $cv_status = 0;
                                array_push($process_type_arr, 'twitter');
                            }

                        }
                    }
                    else
                    {
                        $twt = 0;
                        $cv_status = 0;
                        array_push($process_type_arr, 'twitter');
                    }
                }
            }

            if(($yt < 2 && $ig < 2) || ($yt < 2 && $tt < 2) || ($yt < 2 && $twt < 2) || ($ig < 2 && $tt < 2) || ($ig < 2 && $twt < 2) || ($tt < 2 && $twt < 2))
            {
                $aggr = 0;
            }
            else
            {
                $aggr = 3;
            }

            $updt_data = [
                'cv_id' => base64_decode($id),
                'yt' => $yt,
                'yt_last_process_count' => $yt_last_process_count,
                'ig' => $ig,
                'ig_last_process_count' => $ig_last_process_count,
                'tt' => $tt,
                'tt_last_process_count' => $tt_last_process_count,
                'twt' => $twt,
                'twt_last_process_count' => $twt_last_process_count,
                'aggr' => $aggr,
                'status' => $cv_status,
                'is_active' => 0,
                'edited_by' => session('LoggedUser')
            ];

            $updt_request_data = [
                'uploaded_start_id' => 0,
                'uploaded_end_id' => 0,
                'new_status' => 0,
                'edited_by' => session('LoggedUser')
            ];

            DB::table('tbl_social_spyder_graph_request_data')->where('cv_id', base64_decode($id))->whereIn('process_type', $process_type_arr)->update($updt_request_data);

            //print_r($updt_data);exit;
            if(DB::table('tbl_social_media_sync_process_data')->where('cv_id', base64_decode($id))->update($updt_data))
            {
                if($called_from == 'addToProcessQueue'){
                    return back()->with('success','Brand Sonic Radar added to process queue successfully');
                }else{
                    return 1;
                }
                //return view('backend.views.social_media_sync_in_process_list');
            }
            else
            {
                return back()->with('fail', 'Something went wrong, please try again!');
            }



        }
        else
        {
            $yt = 3;
            $ig = 3;
            $tt = 3;
            $twt = 3;
            $aggr = 3;
            //$get_sm_data_flag = DB::table('tbl_social_spyder_graph_request_data')->where('cv_id', base64_decode($id))->where('status', '=', 0)->where('is_active', '=', 0)->get();
            $get_sm_data_flag = DB::select(DB::raw("SELECT DISTINCT process_type, status, request_json FROM tbl_social_spyder_graph_request_data WHERE cv_id='$cv_id' and is_active=0"));
            foreach($get_sm_data_flag as $get_sm_data_flag_data)
            {
                if($get_sm_data_flag_data->process_type == 'youtube')
                {
                    if($get_sm_data_flag_data->status == 2)
                    {
                        $curdate=strtotime(date('Y-m-d'));
                        $request_json = $get_sm_data_flag_data->request_json;
                        $request_json_data_array = json_decode($request_json, True);
                        $end_date = strtotime($request_json_data_array['end_date']);

                        if($yt != 0)
                        {
                            /* if($end_date > $curdate)
                            {
                                $yt = 0;
                            }
                            else
                            {
                                $yt = 6;
                            } */
                            $get_pending = DB::table('tbl_social_spyder_graph_meta_data')->where('cv_id',$cv_id)->where('is_active', '=', 0)->where('status', '>', 4)->where('process_type', '=', 'youtube')->orderBy('updated_at','desc')->first();
                            if($get_pending != '')
                            {
                                $last_process_date = strtotime(explode(" ",$get_pending->updated_at)[0]);
                                if($end_date > $last_process_date)
                                {
                                    $yt = 0;
                                }
                                else
                                {
                                    $yt = 6;
                                }
                            }
                            else
                            {
                                $yt = 0;
                            }

                        }

                    }
                    else
                    {
                        $yt = 0;
                    }
                }
                if($get_sm_data_flag_data->process_type == 'instagram')
                {
                    if($get_sm_data_flag_data->status == 2)
                    {
                        $curdate=strtotime(date('Y-m-d'));
                        $request_json = $get_sm_data_flag_data->request_json;
                        $request_json_data_array = json_decode($request_json, True);
                        $end_date = strtotime($request_json_data_array['end_date']);

                        if($ig != 0)
                        {
                            /* if($end_date > $curdate)
                            {
                                $ig = 0;
                            }
                            else
                            {
                                $ig = 6;
                            } */
                            $get_pending = DB::table('tbl_social_spyder_graph_meta_data')->where('cv_id',$cv_id)->where('is_active', '=', 0)->where('status', '>', 4)->where('process_type', '=', 'instagram')->orderBy('updated_at','desc')->first();
                            if($get_pending != '')
                            {
                                $last_process_date = strtotime(explode(" ",$get_pending->updated_at)[0]);
                                if($end_date > $last_process_date)
                                {
                                    $ig = 0;
                                }
                                else
                                {
                                    $ig = 6;
                                }
                            }
                            else
                            {
                                $ig = 0;
                            }

                        }
                    }
                    else
                    {
                        $ig = 0;
                    }
                }
                if($get_sm_data_flag_data->process_type == 'tiktok')
                {
                    if($get_sm_data_flag_data->status == 2)
                    {
                        $curdate=strtotime(date('Y-m-d'));
                        $request_json = $get_sm_data_flag_data->request_json;
                        $request_json_data_array = json_decode($request_json, True);
                        $end_date = strtotime($request_json_data_array['end_date']);

                        if($tt != 0)
                        {
                            /* if($end_date > $curdate)
                            {
                                $tt = 0;
                            }
                            else
                            {
                                $tt = 6;
                            } */
                            $get_pending = DB::table('tbl_social_spyder_graph_meta_data')->where('cv_id',$cv_id)->where('is_active', '=', 0)->where('status', '>', 4)->where('process_type', '=', 'tiktok')->orderBy('updated_at','desc')->first();
                            if($get_pending != '')
                            {
                                $last_process_date = strtotime(explode(" ",$get_pending->updated_at)[0]);
                                if($end_date > $last_process_date)
                                {
                                    $tt = 0;
                                }
                                else
                                {
                                    $tt = 6;
                                }
                            }
                            else
                            {
                                $tt = 0;
                            }

                        }
                    }
                    else
                    {
                        $tt = 0;
                    }
                }
                if($get_sm_data_flag_data->process_type == 'twitter')
                {
                    if($get_sm_data_flag_data->status == 2)
                    {
                        $curdate=strtotime(date('Y-m-d'));
                        $request_json = $get_sm_data_flag_data->request_json;
                        $request_json_data_array = json_decode($request_json, True);
                        $end_date = strtotime($request_json_data_array['end_date']);

                        if($twt != 0)
                        {
                            /* if($end_date > $curdate)
                            {
                                $twt = 0;
                            }
                            else
                            {
                                $twt = 6;
                            } */
                            $get_pending = DB::table('tbl_social_spyder_graph_meta_data')->where('cv_id',$cv_id)->where('is_active', '=', 0)->where('status', '>', 4)->where('process_type', '=', 'twitter')->orderBy('updated_at','desc')->first();
                            if($get_pending != '')
                            {
                                $last_process_date = strtotime(explode(" ",$get_pending->updated_at)[0]);
                                if($end_date > $last_process_date)
                                {
                                    $twt = 0;
                                }
                                else
                                {
                                    $twt = 6;
                                }
                            }
                            else
                            {
                                $twt = 0;
                            }

                        }
                    }
                    else
                    {
                        $twt = 0;
                    }
                }
            }

            //echo "yt".$yt."<br>ig".$ig."<br>tt".$tt."<br>twt".$twt."<br>";

            if(($yt < 2 && $ig < 2) || ($yt < 2 && $tt < 2) || ($yt < 2 && $tt < 2) || ($yt < 2 && $twt < 2) || ($ig < 2 && $tt < 2) || ($ig < 2 && $twt < 2) || ($tt < 2 && $twt < 2))
            {
                $aggr = 0;
            }

            $ins_data = [
                'cv_id' => base64_decode($id),
                'yt' => $yt,
                'ig' => $ig,
                'tt' => $tt,
                'twt' => $twt,
                'aggr' => $aggr,
                'created_by' => session('LoggedUser')
            ];

            if(DB::table('tbl_social_media_sync_process_data')->insert($ins_data))
            {
                if($called_from == 'addToProcessQueue'){
                   return back()->with('success','Brand Sonic Radar added to process queue successfully');
                }else{
                    return 1;
                }
                //return view('backend.views.social_media_sync_in_process_list');
            }
            else
            {
                return back()->with('fail', 'Something went wrong, please try again!');
            }
        }
    }


    function publishCV(Request $request)
    {
        //dd($request);die;
        //return $request->input();
        if($request->status_type == "publish")
        {
            $update_status_query =  DB::table('tbl_cvs')
                            ->where('cv_id', $request->cv_id)
                            ->update(['status' => 1, 'edited_by'=>session('LoggedUser')]);
        }
        else
        {
            $update_status_query =  DB::table('tbl_cvs')
                            ->where('cv_id', $request->cv_id)
                            ->update(['status' => 0, 'edited_by'=>session('LoggedUser')]);
        }
        if($update_status_query)
        {
            if($request->status_type == "publish")
            {
                $id = base64_encode($request->cv_id."#_#"."publishCv");
                $response = $this->addCvToProcessQueue($id);
                $response = $this->triggeraddCvToSocialBladeProcessQueue($request->cv_id);
                return redirect('brand-cvs')->with('success','Brand Sonic Radar published successfully');

            }
            else
            {
                return redirect('brand-cvs')->with('success','Brand Sonic Radar unpublished successfully');
            }
        }
        else
        {
            $error_data = "Something went wrong while publishing / unpublishing cv-".$request->cv_id;
            ErrorMailSender::sendErrorMail($error_data);
            return back()->with('fail', 'Something went wrong while publishing Brand Sonic Radar, please try again!');
        }
    }

    function unpublishCV(Request $request)
    {
        $update_status_query =  DB::table('tbl_cvs')
                            ->where('cv_id', $request->cv_id)
                            ->update(['status' => 1, 'edited_by'=>session('LoggedUser')]);
        if($update_status_query)
        {
            return redirect('brand-cvs')->with('success','Brand Sonic Radar published successfully');
        }
        else
        {
            $error_data = "Something went wrong while unpublishing cv-".$request->cv_id;
            ErrorMailSender::sendErrorMail($error_data);
            return back()->with('fail', 'Something went wrong while publishing Brand Sonic Radar, please try again!');
        }
    }

    public function listBestInAudioBrands()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.best_in_audio_brands_list',['cvs_year_data'=>$cvs_year]);
    }

    function getBestInAudioBrands($year)
    {
        $cv_year = base64_decode($year);
        //echo $cv_year;

        $total_cv_count_data = DB::table('tbl_config')->where('type','=','biabl_'.$cv_year)->where('is_active','=',0)->first();
        if($total_cv_count_data == '')
        {
            $total_cv_count = 100;
            $ins_config_data = [
                'type' => 'biabl_'.$cv_year,
                'value' => $total_cv_count,
                'created_by' => session('LoggedUser')
            ];
            DB::table('tbl_config')->insert($ins_config_data);
            $total_cv_count_data = DB::table('tbl_config')->where('type','=','biabl_'.$cv_year)->where('is_active','=',0)->first();
        }

        $cv_data = DB::select(DB::raw("SELECT * FROM `tbl_cvs` WHERE `cv_year`=".$cv_year." and is_active=0 and status=1 and cv_id NOT IN (SELECT cv_id FROM tbl_best_in_audio_brands WHERE cv_name != '' and is_active=0)"));

        $best_audio_brands_data = DB::table('tbl_best_in_audio_brands')->where('cv_year','=',$cv_year)->where('is_active','=',0)->get();
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        $best_audio_brands_data_sr_no = array();
        $best_audio_brands_data_cv_name = array();
        $best_audio_brands_data_cv_percentage = array();
        $best_audio_brands_data_cv_id = array();
        if(count($best_audio_brands_data) != 0)
        {
            foreach($best_audio_brands_data as $best_audio_brands_data_content)
            {
                if($best_audio_brands_data_content->sr_no != '' && $best_audio_brands_data_content->sr_no != null)
                {
                    array_push($best_audio_brands_data_sr_no, $best_audio_brands_data_content->sr_no);
                    $best_audio_brands_data_cv_name[$best_audio_brands_data_content->sr_no] = $best_audio_brands_data_content->cv_name;
                    $best_audio_brands_data_cv_id[$best_audio_brands_data_content->sr_no] = $best_audio_brands_data_content->cv_id;
                    $best_audio_brands_data_cv_percentage[$best_audio_brands_data_content->sr_no] = $best_audio_brands_data_content->percent_number;
                }
            }
        }
        if(count($best_audio_brands_data_sr_no) == 0)
        {
            $best_audio_brands_data_sr_no = '';
        }
        if(count($best_audio_brands_data_cv_name) == 0)
        {
            $best_audio_brands_data_cv_name = '';
        }
        if(count($best_audio_brands_data_cv_id) == 0)
        {
            $best_audio_brands_data_cv_id = '';
        }
        if(count($best_audio_brands_data_cv_percentage) == 0)
        {
            $best_audio_brands_data_cv_percentage = '';
        }
        return view('backend.views.best_in_audio_brands_list',['cvs_year_data'=>$cvs_year,'cv_data'=>$cv_data,'best_audio_brands_data'=>$best_audio_brands_data,'cv_year'=>$year,'best_audio_brands_data_sr_no'=>$best_audio_brands_data_sr_no,'best_audio_brands_data_cv_name'=>$best_audio_brands_data_cv_name,'best_audio_brands_data_cv_id'=>$best_audio_brands_data_cv_id,'best_audio_brands_data_cv_percentage'=>$best_audio_brands_data_cv_percentage,'total_cv_count_data'=>$total_cv_count_data]);
    }

    function getCVYearsForSideBar()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        print_r($cvs_year);
    }

    function addReplaceDisableInBestAudioBrands(Request $request)
    {
        //return $request->input();
        if(explode("_",$request->status_type)[1] == 'add')
        {
            if($request->audio_brand_efficiency_percentage_number != '')
            {
                $insert_data = ['cv_name' => base64_decode($request->cv_name)." ".base64_decode($request->cv_year),
                'percent_number'=> $request->audio_brand_efficiency_percentage_number,
                'cv_year' => base64_decode($request->cv_year),
                'cv_id' => base64_decode($request->cv_id),
                'sr_no' => $request->tbl_id,
                'created_by' => session('LoggedUser')];
                if(DB::table('tbl_best_in_audio_brands')->insert($insert_data))
                {
                    return redirect('best-in-audio-brands-list/'.base64_encode(base64_decode($request->cv_year)))->with('success',$request->tbl_id.'$#$'.'Brand successfully added into Best Audio Brands List');
                }
                else
                {
                    return back()->with('fail', $request->tbl_id.'$#$'.'Something went wrong while adding brand into Best Audio Brands List, please try again!');
                }
            }
            else
            {
                return back()->with('fail', $request->tbl_id.'$#$'.'Something went wrong while adding brand into Best Audio Brands List, please try again!');
            }

        }
        elseif(explode("_",$request->status_type)[1] == 'replace')
        {
            if($request->audio_brand_efficiency_percentage_number != '')
            {
                $update_data = ['cv_name' => base64_decode($request->cv_name)." ".base64_decode($request->cv_year),
                'percent_number'=> $request->audio_brand_efficiency_percentage_number,
                'cv_year' => base64_decode($request->cv_year),
                'cv_id' => base64_decode($request->cv_id),
                'edited_by' => session('LoggedUser')];

                /* $update_query =  DB::table('tbl_best_in_audio_brands')
                ->where('cv_id', $request->cv_id)
                                ->update($update_data); */

                if(DB::table('tbl_best_in_audio_brands')->where('sr_no', $request->tbl_id)->where('cv_id', base64_decode($request->old_cv_id))->update($update_data))
                {
                    return redirect('best-in-audio-brands-list/'.base64_encode(base64_decode($request->cv_year)))->with('success',$request->tbl_id.'$#$'.'Brand replaced successfully into Best Audio Brands List');
                }
                else
                {
                    return back()->with('fail', $request->tbl_id.'$#$'.'Something went wrong while replacing brand into Best Audio Brands List, please try again!');
                }
            }
            else
            {
                return back()->with('fail', $request->tbl_id.'$#$'.'Something went wrong while replacing brand into Best Audio Brands List, please try again!');
            }
        }
        else
        {
            $update_data = ['is_active' => 1,
            'edited_by' => session('LoggedUser')];
            if(DB::table('tbl_best_in_audio_brands')->where('sr_no', $request->tbl_id)->where('cv_id', base64_decode($request->old_cv_id))->update($update_data))
            {
                return redirect('best-in-audio-brands-list/'.base64_encode(base64_decode($request->cv_year)))->with('success',$request->tbl_id.'$#$'.'Brand deleted successfully from Best Audio Brands List');
            }
            else
            {
                return back()->with('fail', $request->tbl_id.'$#$'.'Something went wrong while deleting brand from Best Audio Brands List, please try again!');
            }
        }
        //exit;
    }

    function updateBestAudioBrandPercent(Request $request)
    {
        //return $request->input();
        $percentage_number = $request->percentage_number;
        $cv_id = base64_decode($request->cv_id);
        $update_status_query =  DB::table('tbl_best_in_audio_brands')
                            ->where('cv_id', $cv_id)
                            ->update(['percent_number' => $percentage_number, 'edited_by'=>session('LoggedUser')]);
        if($update_status_query)
        {
            return back()->with('success',$request->tbl_id.'$#$'.'Brand Percentage Number updated successfully');
        }
        else
        {
            return back()->with('fail', $request->tbl_id.'$#$'.'Something went wrong while updating Brand Percentage Number, please try again!');
        }
    }

    function saveTotalBestInAudioBrandsCvCount(Request $request)
    {
        $updt_data = [
            'id' => $request->con_id,
            'type' => $request->con_type,
            'value' => $request->con_val,
            'edited_by' => session('LoggedUser')
        ];
        if($request->con_val != 0 && $request->con_val !='')
        {
            $count_val_arr = [];
            for($i=1;$i<=$request->old_con_val; $i++)
            {
                array_push($count_val_arr, $i);
            }
            //$count_val_arr_str = implode(",",$count_val_arr);
            //$chk_data = DB::select(DB::raw("SELECT * FROM `tbl_best_in_audio_brands` WHERE sr_no IN ($count_val_arr_str) AND cv_year = ".base64_decode($request->con_cv_year)." AND is_active=0 ORDER BY sr_no desc LIMIT 1"));
            $chk_data =  DB::table('tbl_best_in_audio_brands')->whereIn('sr_no', $count_val_arr)->where('cv_year', base64_decode($request->con_cv_year))->where('is_active', 0)->orderByDesc('sr_no')->first();

            if($chk_data == '')
            {
                if($request->con_val != $request->old_con_val)
                {
                    if(DB::table('tbl_config')->where('id', $request->con_id)->update($updt_data))
                    {
                        return redirect('best-in-audio-brands-list/'.$request->con_cv_year)->with('success','1$#$Total count for Best in Audio Brands of '.base64_decode($request->con_cv_year).' is set  successfully');
                    }
                    else
                    {
                        return back()->with('fail', '1$#$Something went wrong while updating total count for Best in Audio Brands of '.base64_decode($request->con_cv_year).', please try again!');
                    }
                }
            }
            else
            {
                if($request->con_val < $chk_data->sr_no)
                {
                    return back()->with('fail', 'There is '.$chk_data->cv_name.' Brand CV exist at position '.$chk_data->sr_no.' so count can not be set to '.$request->con_val);
                }
                else
                {
                    if($request->con_val != $request->old_con_val)
                    {
                        if(DB::table('tbl_config')->where('id', $request->con_id)->update($updt_data))
                        {
                            return redirect('best-in-audio-brands-list/'.$request->con_cv_year)->with('success','1$#$Total count for Best in Audio Brands of '.base64_decode($request->con_cv_year).' is set  successfully');
                        }
                        else
                        {
                            return back()->with('fail', '1$#$Something went wrong while updating total count for Best in Audio Brands of '.base64_decode($request->con_cv_year).', please try again!');
                        }
                    }
                    else
                    {
                        return redirect('best-in-audio-brands-list/'.$request->con_cv_year)->with('success','1$#$Total count for Best in Audio Brands of '.base64_decode($request->con_cv_year).' is set  successfully');
                    }
                }
            }
        }
        else
        {
            return back()->with('fail', '1$#$Count limit can not be set to 0');
        }
    }

    function createArchive(Request $request)
    {
        //return $request->input();
        $archive_name = $request->cv_name;
        $cvid = $request->cv_id;
        $cv_data = DB::table('tbl_cvs')->where('cv_id', '=', base64_decode($cvid))->first();
        /* echo 'cv_id:'.$cv_data->cv_id.'<br>';
        echo 'cv_name:'.$cv_data->cv_name.'<br>';
        echo 'cv_year:'.$cv_data->cv_year.'<br>';
        echo 'cv_banner_desk:'.$cv_data->cv_banner_desktop.'<br>';
        echo 'cv_banner_ipad:'.$cv_data->cv_banner_ipad.'<br>';
        echo 'cv_banner_mob:'.$cv_data->cv_banner_mobile.'<br>'; */
        $cv_id = $cv_data->cv_id;
        $cv_name = $cv_data->cv_name;
        $cv_year = $cv_data->cv_year;

        $cv_banner_desk = $cv_data->cv_banner_desktop;
        $cv_banner_ipad = $cv_data->cv_banner_ipad;
        $cv_banner_mob = $cv_data->cv_banner_mobile;

        $cv_block_2_data = DB::table('tbl_best_in_audio_brands')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
        if($cv_block_2_data != '')
        {
            //echo 'cv_oranking:'.$cv_block_2_data->sr_no.'<br>';
            $cv_oranking = $cv_block_2_data->sr_no;
        }
        else
        {
            //echo 'cv_oranking:<br>';
            $cv_oranking = '';
        }

        if($cv_data->parent_id != '' && $cv_data->parent_id != null)
        {
            $parent_cv = DB::table('tbl_cvs')->where('cv_id', '=', $cv_data->parent_id)->where('status', '=', 1)->where('is_active', '=', 0)->first();
            if($parent_cv !='' && $parent_cv != null)
            {
                $parent_cv_year = $parent_cv->cv_year;
            }
            else
            {
                $parent_cv_year = '';
            }
        }
        else
        {
            $parent_cv = null;
            $parent_cv_year = '';
        }
        if($parent_cv != null)
        {
            $parent_cv_overall_ranking = DB::table('tbl_best_in_audio_brands')->where('cv_id', '=', $parent_cv->cv_id)->where('is_active', '=', 0)->first();
            if($parent_cv_overall_ranking!='')
            {
                //echo 'cv_parent_oranking:'.$parent_cv_overall_ranking->sr_no.'<br>';
                $cv_parent_oranking = $parent_cv_overall_ranking->sr_no;
            }
            else
            {
                //echo 'cv_parent_oranking:<br>';
                $cv_parent_oranking = '';
            }
        }
        else
        {
            $parent_cv_overall_ranking = '';
            //echo 'cv_parent_oranking:<br>';
            $cv_parent_oranking = '';
        }

        $cv_block_4_data = DB::table('tbl_cv_block_4_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
        if($cv_block_4_data != '')
        {
            /* echo 'cv_abt_txt:'.$cv_block_4_data->b4_description.'<br>';
            echo 'cv_abt_key_findings:'.$cv_block_4_data->b4_key_findings.'<br>'; */
            $cv_abt_txt = $cv_block_4_data->b4_description;
            $cv_abt_key_findings = $cv_block_4_data->b4_key_findings;
        }
        else
        {
            /* echo 'cv_abt_txt:<br>';
            echo 'cv_abt_key_findings:<br>'; */
            $cv_abt_txt = '';
            $cv_abt_key_findings = '';
        }

        $cv_block_14_data = DB::table('tbl_cv_block_14_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->get();
        if(count($cv_block_14_data)!=0)
        {
            $b14_number = $cv_block_14_data[0]->b14_number;
            $industry_id = $cv_data->industry_id;
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
                ->where('cv_id', $cv_ids_array)
                ->where('is_active', 0)
                ->sum('b14_number');

            $b14_avg = $b14_number / $b14_sum_data;
            $b14_ind_published_cvsum = $b14_sum_data;
            $b14_ind_published_cvcount = count($cv_ids_array);
            $b14_ind_avg = $b14_ind_published_cvsum / $b14_ind_published_cvcount;

            if($b14_avg != '' && $b14_avg != null)
            {
                if($b14_number > number_format($b14_ind_avg))
                {
                    //echo 'cv_music_exp_per_yr:'.number_format($b14_avg,2).'|up<br>';
                    $cv_music_exp_per_yr = number_format($b14_avg,2).'|up';
                }
                else if($b14_number < number_format($b14_ind_avg))
                {
                    //echo 'cv_music_exp_per_yr:'.number_format($b14_avg,2).'|down<br>';
                    $cv_music_exp_per_yr = number_format($b14_avg,2).'|down';
                }
                else
                {
                    //echo 'cv_music_exp_per_yr:'.number_format($b14_avg,2).'|none<br>';
                    $cv_music_exp_per_yr = number_format($b14_avg,2).'|none';
                }
            }
            else
            {
                //echo 'cv_music_exp_per_yr:<br>';
                $cv_music_exp_per_yr = '';
            }

        }
        else
        {
            //echo 'cv_music_exp_per_yr:<br>';
            $cv_music_exp_per_yr = '';
        }

        $cv_block_15_data = DB::table('tbl_cv_block_15_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->get();
        if(count($cv_block_15_data)!=0)
        {
            $b15_number = $cv_block_15_data[0]->b15_number;
            $industry_id = $cv_data->industry_id;
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

            $b15_avg = $b15_number / $b15_sum_data;
            $b15_ind_published_cvsum = $b15_sum_data;
            $b15_ind_published_cvcount = count($cv_ids_array);
            $b15_ind_avg = $b15_ind_published_cvsum / $b15_ind_published_cvcount;

            if($b15_avg != '' && $b15_avg != null)
            {
                if($b15_number > number_format($b15_ind_avg))
                {
                    //echo 'cv_music_exp_per_vid:'.number_format($b15_avg,2).'|up<br>';
                    $cv_music_exp_per_vid = number_format($b15_avg,2).'|up';
                }
                else if($b15_number < number_format($b15_ind_avg))
                {
                    //echo 'cv_music_exp_per_vid:'.number_format($b15_avg,2).'|down<br>';
                    $cv_music_exp_per_vid = number_format($b15_avg,2).'|down';
                }
                else
                {
                    //echo 'cv_music_exp_per_vid:'.number_format($b15_avg,2).'|none<br>';
                    $cv_music_exp_per_vid = number_format($b15_avg,2).'|none';
                }
            }
            else
            {
                //echo 'cv_music_exp_per_vid:<br>';
                $cv_music_exp_per_vid = '';
            }

        }
        else
        {
            //echo 'cv_music_exp_per_vid:<br>';
            $cv_music_exp_per_vid = '';
        }

        $footer_template_data = DB::table('tbl_footer_template')->where('footer_template_id', '=', $cv_data->footer_template_id)->first();
        if($footer_template_data != '')
        {
            //echo 'cv_footer_txt:'.$footer_template_data->footer_description.'<br>';
            $cv_footer_txt = $footer_template_data->footer_description;
        }
        else
        {
            //echo 'cv_footer_txt:<br>';
            $cv_footer_txt = '';
        }

        $archive_id = DB::table('tbl_cvs_archive')->insertGetId(
            ['cv_id' => $cv_id, 'archive_name'=>$archive_name, 'cv_name' => $cv_name, 'cv_year' => $cv_year, 'parent_cv_year' => $parent_cv_year, 'cv_banner_desk' => $cv_banner_desk, 'cv_banner_ipad' => $cv_banner_ipad, 'cv_banner_mob' => $cv_banner_mob, 'cv_abt_txt' => $cv_abt_txt, 'cv_abt_key_findings' => $cv_abt_key_findings, 'cv_oranking' => $cv_oranking, 'cv_parent_oranking' => $cv_parent_oranking, 'cv_music_exp_per_yr' => $cv_music_exp_per_yr, 'cv_music_exp_per_vid' => $cv_music_exp_per_vid, 'cv_footer_txt' => $cv_footer_txt, 'created_by' => session('LoggedUser')]
        );

        if($archive_id != '')
        {
            $cv_block_5_data = DB::table('tbl_cv_block_5_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->get();
            $tbl_cv_social_media_archive_data = [];
            if(count($cv_block_5_data)!=0)
            {
                foreach($cv_block_5_data as $cb5data)
                {
                    if($cb5data->b5_icon_name !='' && $cb5data->b5_icon_name!=null)
                    {
                        array_push($tbl_cv_social_media_archive_data,['arch_id' => $archive_id, 'icon_name' => $cb5data->b5_icon_name,  'icon_link' => $cb5data->b5_link, 'icon_link_name' => $cb5data->b5_link_name]);
                    }
                }
            }
            if(count($tbl_cv_social_media_archive_data)!=0)
            {
                if(DB::table('tbl_cv_social_media_archive')->insert($tbl_cv_social_media_archive_data))
                {

                }
                else
                {
                    return back()->with('fail', 'Something went wrong while inserting social media data into database, please try again!');
                }
            }

            /* $cv_genre_aggr_graph_values_data = DB::table('tbl_genre_aggr_graph_data')->select('ambient','blues','classical','country','electronicDance','folk','indieAlternative','jazz','latin','metal','pop','punk','rapHipHop','reggae','rnb','rock','singerSongwriter')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
            $cv_genre_aggr_graph_values_arr = (array)$cv_genre_aggr_graph_values_data;
            $cv_genre_aggr_graph_values_arr1 = (array)$cv_genre_aggr_graph_values_data;
            rsort($cv_genre_aggr_graph_values_arr);
            $top3 = array_slice($cv_genre_aggr_graph_values_arr, 0, 3);
            $top_3_genre = array();
            foreach ($top3 as $key => $val) {
                $key = array_search ($val, $cv_genre_aggr_graph_values_arr1);
                unset($cv_genre_aggr_graph_values_arr1[$key]);
                $top_3_genre[$key] = $val;
            } */
            $avg_genre_data = DB::table('tbl_social_media_aggr_genre_graph_data')
            ->where('cv_id', '=', base64_decode($cvid))
            ->where('is_active', '=', 0)
            ->get();
            //print_r($avg_genre_data);
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
            $tbl_cv_fav_music_genres_archive_data = [];
            if(count($top_3_genre)!=0)
            {
                foreach($top_3_genre as $top3genreKey => $top3genreData)
                {
                    array_push($tbl_cv_fav_music_genres_archive_data,['arch_id' => $archive_id, 'title' => $top3genreKey]);
                }

            }
            if(count($tbl_cv_fav_music_genres_archive_data)!=0)
            {
                if(DB::table('tbl_cv_fav_music_genres_archive')->insert($tbl_cv_fav_music_genres_archive_data))
                {

                }
                else
                {
                    return back()->with('fail', 'Something went wrong while inserting Favorite Music Genres data into database, please try again!');
                }
            }

            $cv_block_16_mood_graph_data = DB::table('tbl_cv_block_16_mood_graph_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
            $tbl_cv_yt_mood_graph_archive_data = [];
            if($cv_block_16_mood_graph_data != '')
            {
                $tbl_cv_yt_mood_graph_archive_data = ['arch_id' => $archive_id, 'aggressive'=> $cv_block_16_mood_graph_data->aggressive, 'calm'=> $cv_block_16_mood_graph_data->calm, 'chilled'=> $cv_block_16_mood_graph_data->chilled, 'dark'=> $cv_block_16_mood_graph_data->dark, 'energetic'=> $cv_block_16_mood_graph_data->energetic, 'epic'=> $cv_block_16_mood_graph_data->epic, 'happy'=> $cv_block_16_mood_graph_data->happy, 'romantic'=> $cv_block_16_mood_graph_data->romantic, 'sad'=> $cv_block_16_mood_graph_data->sad, 'scary'=> $cv_block_16_mood_graph_data->scary, 'sexy'=> $cv_block_16_mood_graph_data->sexy, 'ethereal'=> $cv_block_16_mood_graph_data->ethereal, 'uplifting'=> $cv_block_16_mood_graph_data->uplifting];
            }
            else
            {
                $tbl_cv_yt_mood_graph_archive_data = [];
            }
            if(count($tbl_cv_yt_mood_graph_archive_data)!=0)
            {
                if(DB::table('tbl_cv_yt_mood_graph_archive')->insert($tbl_cv_yt_mood_graph_archive_data))
                {

                }
                else
                {
                    return back()->with('fail', 'Something went wrong while inserting YT Mood Graph data into database, please try again!');
                }
            }

            $cv_block_16_genre_graph_data = DB::table('tbl_social_media_yt_genre_graph_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
            $tbl_cv_yt_genre_graph_archive_data = [];
            if($cv_block_16_genre_graph_data != '')
            {
                $tbl_cv_yt_genre_graph_archive_data = ['arch_id' => $archive_id, 'ambient'=> $cv_block_16_genre_graph_data->ambient, 'blues'=> $cv_block_16_genre_graph_data->blues, 'classical'=> $cv_block_16_genre_graph_data->classical, 'country'=> $cv_block_16_genre_graph_data->country, 'electronicDance'=> $cv_block_16_genre_graph_data->electronicDance, 'folk'=> $cv_block_16_genre_graph_data->folk, 'indieAlternative'=> $cv_block_16_genre_graph_data->indieAlternative, 'jazz'=> $cv_block_16_genre_graph_data->jazz, 'latin'=> $cv_block_16_genre_graph_data->latin, 'metal'=> $cv_block_16_genre_graph_data->metal, 'pop'=> $cv_block_16_genre_graph_data->pop, 'punk'=> $cv_block_16_genre_graph_data->punk, 'rapHipHop'=> $cv_block_16_genre_graph_data->rapHipHop, 'reggae'=> $cv_block_16_genre_graph_data->reggae, 'rnb'=> $cv_block_16_genre_graph_data->rnb, 'rock'=> $cv_block_16_genre_graph_data->rock, 'singerSongwriter'=> $cv_block_16_genre_graph_data->singerSongwriter];
            }
            else
            {
                $tbl_cv_yt_genre_graph_archive_data = [];
            }
            if(count($tbl_cv_yt_genre_graph_archive_data)!=0)
            {
                if(DB::table('tbl_cv_yt_genre_graph_archive')->insert($tbl_cv_yt_genre_graph_archive_data))
                {

                }
                else
                {
                    return back()->with('fail', 'Something went wrong while inserting YT Genre Graph data into database, please try again!');
                }
            }

            $cv_block_17_mood_graph_data = DB::table('tbl_cv_block_17_mood_graph_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
            $tbl_cv_ig_mood_graph_archive_data = [];
            if($cv_block_17_mood_graph_data != '')
            {
                $tbl_cv_ig_mood_graph_archive_data = ['arch_id' => $archive_id, 'aggressive'=> $cv_block_17_mood_graph_data->aggressive, 'calm'=> $cv_block_17_mood_graph_data->calm, 'chilled'=> $cv_block_17_mood_graph_data->chilled, 'dark'=> $cv_block_17_mood_graph_data->dark, 'energetic'=> $cv_block_17_mood_graph_data->energetic, 'epic'=> $cv_block_17_mood_graph_data->epic, 'happy'=> $cv_block_17_mood_graph_data->happy, 'romantic'=> $cv_block_17_mood_graph_data->romantic, 'sad'=> $cv_block_17_mood_graph_data->sad, 'scary'=> $cv_block_17_mood_graph_data->scary, 'sexy'=> $cv_block_17_mood_graph_data->sexy, 'ethereal'=> $cv_block_17_mood_graph_data->ethereal, 'uplifting'=> $cv_block_17_mood_graph_data->uplifting];
            }
            else
            {
                $tbl_cv_ig_mood_graph_archive_data = [];
            }
            if(count($tbl_cv_ig_mood_graph_archive_data)!=0)
            {
                if(DB::table('tbl_cv_ig_mood_graph_archive')->insert($tbl_cv_ig_mood_graph_archive_data))
                {

                }
                else
                {
                    return back()->with('fail', 'Something went wrong while inserting IG Mood Graph data into database, please try again!');
                }
            }

            $cv_block_17_genre_graph_data = DB::table('tbl_social_media_ig_genre_graph_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
            $tbl_cv_ig_genre_graph_archive_data = [];
            if($cv_block_17_genre_graph_data != '')
            {
                $tbl_cv_ig_genre_graph_archive_data = ['arch_id' => $archive_id, 'ambient'=> $cv_block_17_genre_graph_data->ambient, 'blues'=> $cv_block_17_genre_graph_data->blues, 'classical'=> $cv_block_17_genre_graph_data->classical, 'country'=> $cv_block_17_genre_graph_data->country, 'electronicDance'=> $cv_block_17_genre_graph_data->electronicDance, 'folk'=> $cv_block_17_genre_graph_data->folk, 'indieAlternative'=> $cv_block_17_genre_graph_data->indieAlternative, 'jazz'=> $cv_block_17_genre_graph_data->jazz, 'latin'=> $cv_block_17_genre_graph_data->latin, 'metal'=> $cv_block_17_genre_graph_data->metal, 'pop'=> $cv_block_17_genre_graph_data->pop, 'punk'=> $cv_block_17_genre_graph_data->punk, 'rapHipHop'=> $cv_block_17_genre_graph_data->rapHipHop, 'reggae'=> $cv_block_17_genre_graph_data->reggae, 'rnb'=> $cv_block_17_genre_graph_data->rnb, 'rock'=> $cv_block_17_genre_graph_data->rock, 'singerSongwriter'=> $cv_block_17_genre_graph_data->singerSongwriter];
            }
            else
            {
                $tbl_cv_ig_genre_graph_archive_data = [];
            }
            if(count($tbl_cv_ig_genre_graph_archive_data)!=0)
            {
                if(DB::table('tbl_cv_ig_genre_graph_archive')->insert($tbl_cv_ig_genre_graph_archive_data))
                {

                }
                else
                {
                    return back()->with('fail', 'Something went wrong while inserting IG Genre Graph data into database, please try again!');
                }
            }

            $cv_block_18_mood_graph_data = DB::table('tbl_cv_block_18_mood_graph_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
            $tbl_cv_tt_mood_graph_archive_data = [];
            if($cv_block_18_mood_graph_data != '')
            {
                $tbl_cv_tt_mood_graph_archive_data = ['arch_id' => $archive_id, 'aggressive'=> $cv_block_18_mood_graph_data->aggressive, 'calm'=> $cv_block_18_mood_graph_data->calm, 'chilled'=> $cv_block_18_mood_graph_data->chilled, 'dark'=> $cv_block_18_mood_graph_data->dark, 'energetic'=> $cv_block_18_mood_graph_data->energetic, 'epic'=> $cv_block_18_mood_graph_data->epic, 'happy'=> $cv_block_18_mood_graph_data->happy, 'romantic'=> $cv_block_18_mood_graph_data->romantic, 'sad'=> $cv_block_18_mood_graph_data->sad, 'scary'=> $cv_block_18_mood_graph_data->scary, 'sexy'=> $cv_block_18_mood_graph_data->sexy, 'ethereal'=> $cv_block_18_mood_graph_data->ethereal, 'uplifting'=> $cv_block_18_mood_graph_data->uplifting];
            }
            else
            {
                $tbl_cv_tt_mood_graph_archive_data = [];
            }
            if(count($tbl_cv_tt_mood_graph_archive_data)!=0)
            {
                if(DB::table('tbl_cv_tt_mood_graph_archive')->insert($tbl_cv_tt_mood_graph_archive_data))
                {

                }
                else
                {
                    return back()->with('fail', 'Something went wrong while inserting TT Mood Graph data into database, please try again!');
                }
            }

            $cv_block_18_genre_graph_data = DB::table('tbl_social_media_tt_genre_graph_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
            $tbl_cv_tt_genre_graph_archive_data = [];
            if($cv_block_18_genre_graph_data != '')
            {
                $tbl_cv_tt_genre_graph_archive_data = ['arch_id' => $archive_id, 'ambient'=> $cv_block_18_genre_graph_data->ambient, 'blues'=> $cv_block_18_genre_graph_data->blues, 'classical'=> $cv_block_18_genre_graph_data->classical, 'country'=> $cv_block_18_genre_graph_data->country, 'electronicDance'=> $cv_block_18_genre_graph_data->electronicDance, 'folk'=> $cv_block_18_genre_graph_data->folk, 'indieAlternative'=> $cv_block_18_genre_graph_data->indieAlternative, 'jazz'=> $cv_block_18_genre_graph_data->jazz, 'latin'=> $cv_block_18_genre_graph_data->latin, 'metal'=> $cv_block_18_genre_graph_data->metal, 'pop'=> $cv_block_18_genre_graph_data->pop, 'punk'=> $cv_block_18_genre_graph_data->punk, 'rapHipHop'=> $cv_block_18_genre_graph_data->rapHipHop, 'reggae'=> $cv_block_18_genre_graph_data->reggae, 'rnb'=> $cv_block_18_genre_graph_data->rnb, 'rock'=> $cv_block_18_genre_graph_data->rock, 'singerSongwriter'=> $cv_block_18_genre_graph_data->singerSongwriter];
            }
            else
            {
                $tbl_cv_tt_genre_graph_archive_data = [];
            }
            if(count($tbl_cv_tt_genre_graph_archive_data)!=0)
            {
                if(DB::table('tbl_cv_tt_genre_graph_archive')->insert($tbl_cv_tt_genre_graph_archive_data))
                {

                }
                else
                {
                    return back()->with('fail', 'Something went wrong while inserting TT Genre Graph data into database, please try again!');
                }
            }

            $cv_block_19_mood_graph_data = DB::table('tbl_cv_block_19_mood_graph_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
            $tbl_cv_twt_mood_graph_archive_data = [];
            if($cv_block_19_mood_graph_data != '')
            {
                $tbl_cv_twt_mood_graph_archive_data = ['arch_id' => $archive_id, 'aggressive'=> $cv_block_19_mood_graph_data->aggressive, 'calm'=> $cv_block_19_mood_graph_data->calm, 'chilled'=> $cv_block_19_mood_graph_data->chilled, 'dark'=> $cv_block_19_mood_graph_data->dark, 'energetic'=> $cv_block_19_mood_graph_data->energetic, 'epic'=> $cv_block_19_mood_graph_data->epic, 'happy'=> $cv_block_19_mood_graph_data->happy, 'romantic'=> $cv_block_19_mood_graph_data->romantic, 'sad'=> $cv_block_19_mood_graph_data->sad, 'scary'=> $cv_block_19_mood_graph_data->scary, 'sexy'=> $cv_block_19_mood_graph_data->sexy, 'ethereal'=> $cv_block_19_mood_graph_data->ethereal, 'uplifting'=> $cv_block_19_mood_graph_data->uplifting];
            }
            else
            {
                $tbl_cv_twt_mood_graph_archive_data = [];
            }
            if(count($tbl_cv_twt_mood_graph_archive_data)!=0)
            {
                if(DB::table('tbl_cv_twt_mood_graph_archive')->insert($tbl_cv_twt_mood_graph_archive_data))
                {

                }
                else
                {
                    return back()->with('fail', 'Something went wrong while inserting TWT Mood Graph data into database, please try again!');
                }
            }

            $cv_block_19_genre_graph_data = DB::table('tbl_social_media_twt_genre_graph_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
            $tbl_cv_twt_genre_graph_archive_data = [];
            if($cv_block_19_genre_graph_data != '')
            {
                $tbl_cv_twt_genre_graph_archive_data = ['arch_id' => $archive_id, 'ambient'=> $cv_block_19_genre_graph_data->ambient, 'blues'=> $cv_block_19_genre_graph_data->blues, 'classical'=> $cv_block_19_genre_graph_data->classical, 'country'=> $cv_block_19_genre_graph_data->country, 'electronicDance'=> $cv_block_19_genre_graph_data->electronicDance, 'folk'=> $cv_block_19_genre_graph_data->folk, 'indieAlternative'=> $cv_block_19_genre_graph_data->indieAlternative, 'jazz'=> $cv_block_19_genre_graph_data->jazz, 'latin'=> $cv_block_19_genre_graph_data->latin, 'metal'=> $cv_block_19_genre_graph_data->metal, 'pop'=> $cv_block_19_genre_graph_data->pop, 'punk'=> $cv_block_19_genre_graph_data->punk, 'rapHipHop'=> $cv_block_19_genre_graph_data->rapHipHop, 'reggae'=> $cv_block_19_genre_graph_data->reggae, 'rnb'=> $cv_block_19_genre_graph_data->rnb, 'rock'=> $cv_block_19_genre_graph_data->rock, 'singerSongwriter'=> $cv_block_19_genre_graph_data->singerSongwriter];
            }
            else
            {
                $tbl_cv_twt_genre_graph_archive_data = [];
            }
            if(count($tbl_cv_twt_genre_graph_archive_data)!=0)
            {
                if(DB::table('tbl_cv_twt_genre_graph_archive')->insert($tbl_cv_twt_genre_graph_archive_data))
                {

                }
                else
                {
                    return back()->with('fail', 'Something went wrong while inserting TWT Genre Graph data into database, please try again!');
                }
            }

            $cv_mood_aggr_graph_data = DB::table('tbl_mood_aggr_graph_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
            $tbl_cv_aggr_mood_graph_archive_data = [];
            if($cv_mood_aggr_graph_data != '')
            {
                $tbl_cv_aggr_mood_graph_archive_data = ['arch_id' => $archive_id, 'aggressive'=> $cv_mood_aggr_graph_data->aggressive, 'calm'=> $cv_mood_aggr_graph_data->calm, 'chilled'=> $cv_mood_aggr_graph_data->chilled, 'dark'=> $cv_mood_aggr_graph_data->dark, 'energetic'=> $cv_mood_aggr_graph_data->energetic, 'epic'=> $cv_mood_aggr_graph_data->epic, 'happy'=> $cv_mood_aggr_graph_data->happy, 'romantic'=> $cv_mood_aggr_graph_data->romantic, 'sad'=> $cv_mood_aggr_graph_data->sad, 'scary'=> $cv_mood_aggr_graph_data->scary, 'sexy'=> $cv_mood_aggr_graph_data->sexy, 'ethereal'=> $cv_mood_aggr_graph_data->ethereal, 'uplifting'=> $cv_mood_aggr_graph_data->uplifting];
            }
            else
            {
                $tbl_cv_aggr_mood_graph_archive_data = [];
            }
            if(count($tbl_cv_aggr_mood_graph_archive_data)!=0)
            {
                if(DB::table('tbl_cv_aggr_mood_graph_archive')->insert($tbl_cv_aggr_mood_graph_archive_data))
                {

                }
                else
                {
                    return back()->with('fail', 'Something went wrong while inserting AGGR Mood Graph data into database, please try again!');
                }
            }

            //$cv_genre_aggr_graph_data = DB::table('tbl_genre_aggr_graph_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
            $cv_genre_aggr_graph_data = DB::table('tbl_social_media_aggr_genre_graph_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
            $tbl_cv_aggr_genre_graph_archive_data = [];
            if($cv_genre_aggr_graph_data != '')
            {
                //$tbl_cv_aggr_genre_graph_archive_data = ['arch_id' => $archive_id, 'ambient'=> $cv_genre_aggr_graph_data->ambient, 'blues'=> $cv_genre_aggr_graph_data->blues, 'classical'=> $cv_genre_aggr_graph_data->classical, 'country'=> $cv_genre_aggr_graph_data->country, 'electronicDance'=> $cv_genre_aggr_graph_data->electronicDance, 'folk'=> $cv_genre_aggr_graph_data->folk, 'indieAlternative'=> $cv_genre_aggr_graph_data->indieAlternative, 'jazz'=> $cv_genre_aggr_graph_data->jazz, 'latin'=> $cv_genre_aggr_graph_data->latin, 'metal'=> $cv_genre_aggr_graph_data->metal, 'pop'=> $cv_genre_aggr_graph_data->pop, 'punk'=> $cv_genre_aggr_graph_data->punk, 'rapHipHop'=> $cv_genre_aggr_graph_data->rapHipHop, 'reggae'=> $cv_genre_aggr_graph_data->reggae, 'rnb'=> $cv_genre_aggr_graph_data->rnb, 'rock'=> $cv_genre_aggr_graph_data->rock, 'singerSongwriter'=> $cv_genre_aggr_graph_data->singerSongwriter];
                foreach ($cv_genre_aggr_graph_data as $val) {
                    $tbl_cv_aggr_genre_graph_archive_data[$val->lbl_name] = $val->lbl_value;
                }
            }
            else
            {
                $tbl_cv_aggr_genre_graph_archive_data = [];
            }
            if(count($tbl_cv_aggr_genre_graph_archive_data)!=0)
            {
                if(DB::table('tbl_cv_aggr_genre_graph_archive')->insert($tbl_cv_aggr_genre_graph_archive_data))
                {

                }
                else
                {
                    return back()->with('fail', 'Something went wrong while inserting AGGR Genre Graph data into database, please try again!');
                }
            }

            $cv_block_6_data = DB::table('tbl_cv_block_6_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->get();
            $tbl_cv_sonic_logo_music_archive_data = [];
            if(count($cv_block_6_data)!=0)
            {
                foreach($cv_block_6_data as $cvb6data)
                {
                    if($cvb6data->b6_title !='' && $cvb6data->b6_title!=null)
                    {
                        array_push($tbl_cv_sonic_logo_music_archive_data,['arch_id' => $archive_id, 'titile' => $cvb6data->b6_title,  'name' => $cvb6data->b6_name]);
                    }
                }
            }
            if(count($tbl_cv_sonic_logo_music_archive_data)!=0)
            {
                if(DB::table('tbl_cv_sonic_logo_music_archive')->insert($tbl_cv_sonic_logo_music_archive_data))
                {

                }
                else
                {
                    return back()->with('fail', 'Something went wrong while inserting Sonic Logo data into database, please try again!');
                }
            }

            $cv_block_7_data = DB::table('tbl_cv_block_7_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->get();
            if(count($cv_block_7_data)!=0)
            {
                $yes_val = '';
                $no_val = '';
                foreach($cv_block_7_data as $cvb7data)
                {
                    if($cvb7data->b7_name == 'Yes' || $cvb7data->b7_name == 'yes' || $cvb7data->b7_name == 'YES')
                    {
                        $yes_val = $cvb7data->b7_number;
                    }
                    if($cvb7data->b7_name == 'No' || $cvb7data->b7_name == 'no' || $cvb7data->b7_name == 'NO')
                    {
                        $no_val = $cvb7data->b7_number;
                    }
                }
                if($yes_val != '' && $no_val != '')
                {
                    if(DB::table('tbl_cv_sonic_logo_usage_archive')->insert(['arch_id' => $archive_id, 'yes' => $yes_val,  'no' => $no_val]))
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
                        $ind_yes_val = '';
                        $ind_no_val = '';
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

                            $ind_yes_val = number_format(array_sum($insudtry_yes_avg_data_array)/count($insudtry_yes_avg_data_array));
                            $ind_no_val = number_format(array_sum($insudtry_no_avg_data_array)/count($insudtry_no_avg_data_array));
                        }
                        if($ind_yes_val != '' && $ind_no_val != '')
                        {
                            if(DB::table('tbl_cv_sonic_logo_usage_ind_avg_archive')->insert(['arch_id' => $archive_id, 'yes' => $ind_yes_val,  'no' => $ind_no_val]))
                            {

                            }
                            else
                            {
                                return back()->with('fail', 'Something went wrong while inserting Sonic Logo Usage Industry Avg Data into database, please try again!');
                            }
                        }
                    }
                    else
                    {
                        return back()->with('fail', 'Something went wrong while inserting Sonic Logo Usage data into database, please try again!');
                    }
                }
            }

            $cv_block_9_data = DB::table('tbl_cv_block_9_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->get();
            $tbl_cv_most_popular_videos_archive_data = [];
            if(count($cv_block_9_data)!=0)
            {
                foreach($cv_block_9_data as $cvb9data)
                {
                    array_push($tbl_cv_most_popular_videos_archive_data,['arch_id' => $archive_id, 'vid_title' => $cvb9data->b9_video_title,  'vid_link' => $cvb9data->b9_video_link]);
                }
            }
            if(count($tbl_cv_most_popular_videos_archive_data)!=0)
            {
                if(DB::table('tbl_cv_most_popular_videos_archive')->insert($tbl_cv_most_popular_videos_archive_data))
                {

                }
                else
                {
                    return back()->with('fail', 'Something went wrong while inserting Most Popular Videos Data into database, please try again!');
                }
            }

            $cv_block_10_data = DB::table('tbl_cv_block_10_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->get();
            $tbl_cv_day_in_my_life_archive_data = [];
            if(count($cv_block_10_data)!=0)
            {
                for($qlti = 0; $qlti < count($cv_block_10_data); $qlti++)
                {
                    if($cv_block_10_data[$qlti]->b10_name_id != 0)
                    {
                        $qualitative_id_data = DB::table('tbl_qualitative')
                            ->where('qualitative_id', $cv_block_10_data[$qlti]->b10_name_id)
                            ->first();
                        array_push($tbl_cv_day_in_my_life_archive_data,['arch_id' => $archive_id, 'title' => $qualitative_id_data->qualitative_name,  'percent' => $cv_block_10_data[$qlti]->b10_number]);
                    }
                }
            }
            if(count($tbl_cv_day_in_my_life_archive_data)!=0)
            {
                if(DB::table('tbl_cv_day_in_my_life_archive')->insert($tbl_cv_day_in_my_life_archive_data))
                {

                }
                else
                {
                    return back()->with('fail', 'Something went wrong while inserting A Day In My Life Data into database, please try again!');
                }
            }

            $cv_block_12_data = DB::table('tbl_cv_block_12_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->get();
            $tbl_cv_social_media_stats_archive_data = [];
            if(count($cv_block_12_data)!=0)
            {
                foreach($cv_block_12_data as $cvb12data)
                {
                    if($cvb12data->b12_icon_name != '' && $cvb12data->b12_icon_name !=null)
                    {
                        array_push($tbl_cv_social_media_stats_archive_data,['arch_id' => $archive_id, 'icon' => $cvb12data->b12_icon_name,  'icon_txt' => $cvb12data->b12_link_txt,  'icon_num' => $cvb12data->b12_link_number]);
                    }
                }
            }
            if(count($tbl_cv_social_media_stats_archive_data)!=0)
            {
                if(DB::table('tbl_cv_social_media_stats_archive')->insert($tbl_cv_social_media_stats_archive_data))
                {

                }
                else
                {
                    return back()->with('fail', 'Something went wrong while inserting Social Media Stats Data into database, please try again!');
                }
            }

            $cv_block_13_data = DB::table('tbl_cv_block_13_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->get();
            $tbl_cv_music_type_usage_archive_data = [];
            if(count($cv_block_13_data)!=0)
            {
                for($ei = 0; $ei < count($cv_block_13_data); $ei++)
                {
                    if($cv_block_13_data[$ei]->b13_name_id !=0)
                    {
                        $experience_data = DB::table('tbl_experience')
                        ->where('experience_id', $cv_block_13_data[$ei]->b13_name_id)
                        ->first();
                        array_push($tbl_cv_music_type_usage_archive_data,['arch_id' => $archive_id, 'title' => $experience_data->experience_name, 'title_id' => $cv_block_13_data[$ei]->b13_name_id,  'percent' => $cv_block_13_data[$ei]->b13_number]);
                    }
                    /* $experience_data = DB::table('tbl_experience')
                        ->where('experience_id', $cv_block_13_data[$ei]->b13_name_id)
                        ->first();
                        array_push($tbl_cv_music_type_usage_archive_data,['arch_id' => $archive_id, 'title' => $experience_data->experience_name, 'title_id' => $cv_block_13_data[$ei]->b13_name_id,  'percent' => $cv_block_13_data[$ei]->b13_number]); */
                }

                if(count($tbl_cv_music_type_usage_archive_data)!=0)
                {
                    if(DB::table('tbl_cv_music_type_usage_archive')->insert($tbl_cv_music_type_usage_archive_data))
                    {
                        //return redirect('brand-cvs')->with('success','Brand Sonic Radar Archived successfully');

                    }
                    else
                    {
                        return back()->with('fail', 'Something went wrong while inserting Music Types Usage Data into database, please try again!');
                    }
                }

                return back()->with('success','Brand Sonic Radar Archived successfully');

            }
        }
        else
        {
            return back()->with('fail', 'Something went wrong while creating archive / snapshot, please try again!');
        }
    }

    function updateArchive(Request $request)
    {
        //return $request->input();
        $archive_name = $request->cv_name;
        $cv_id = base64_decode($request->cv_id);
        $update_status_query =  DB::table('tbl_cvs_archive')
                            ->where('cv_id', $cv_id)
                            ->update(['archive_name' => $archive_name, 'edited_by'=>session('LoggedUser')]);
        if($update_status_query)
        {
            return back()->with('success','Archive / Snapshot name updated successfully');
        }
        else
        {
            return back()->with('fail', 'Something went wrong while updating Archive / Snapshot name, please try again!');
        }
    }

    public function listArchivedBrandCvs()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.archived_brands_list',['cvs_year_data'=>$cvs_year]);
    }

    function getArchivedBrandsList(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('tbl_cvs_archive')->where('is_active',0)->orderBy('arch_id', 'desc')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('archive_name', function($data){
                    $archive_name = '<div> <span id="txt_'.str_replace("=","",base64_encode($data->cv_id)).'">'.$data->archive_name.'</span> <form id="form_'.str_replace("=","",base64_encode($data->cv_id)).'" class="hide" action="update-archived-cv" method="post"><input type="hidden" id="'.str_replace("=","",base64_encode($data->cv_id)).'_token" name="_token"><input type="hidden" id="cv_id" name="cv_id" value="'.base64_encode($data->cv_id).'"><input type="text" id="cv_name" name="cv_name" class="card_inputs" style="width:100%; margin-bottom:5px;" value="'.$data->archive_name.'" /> <div style="float:right;"><button class="btn btn-primary btn-sm" type="submit" onClick="addLoader()">Save</button> <span class="btn btn-success btn-sm" onClick=hideArchiveForm("form_'.base64_encode($data->cv_id).'")>Cancel</span></div> </form> <span class="btn btn-primary btn-sm" id="editIcon_'.str_replace("=","",base64_encode($data->cv_id)).'" onClick=showArchiveForm("form_'.base64_encode($data->cv_id).'") style="float: right;"><i class="fas fa-edit"></i></span> </div>';
                    return $archive_name;
                })
                ->addColumn('cv_name', function($data){
                    $cv_name = $data->cv_name." ".$data->cv_year;
                    return $cv_name;
                })
                ->addColumn('cv_archive_dt', function($data){
                    $cv_archive_dt = $data->cv_archive_dt;
                    return $cv_archive_dt;
                })
                ->addColumn('action', function($data){
                    if($data->is_active == '0')
                    {
                        $actionBtn = '<a href="display-archived-cv/'.base64_encode($data->arch_id).'" title="Click here to Preview" class="btn btn-success btn-sm" target="_blank">Preview</a> <a href="delete-archived-cv/'.base64_encode($data->arch_id).'" title="Click here to delete archive" class="delete btn btn-success btn-sm">Delete</a>';
                    }
                    return $actionBtn;
                })
                ->rawColumns(['action','archive_name'])
                ->setRowId(function($data){
                    return $data->arch_id;
                })
                ->make(true);
        }
    }

    function deleteArchivedCV($id)
    {
        $update_cv = DB::table('tbl_cvs_archive')
                            ->where('arch_id', base64_decode($id))
                            ->update(['is_active' => '1', 'edited_by'=>session('LoggedUser')]);

        if($update_cv)
        {
            return back()->with('success','Archive deleted successfully');
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }

    function displayArchivedCV($archid)
    {
        $arch_cv_data = DB::table('tbl_cvs_archive')->where('arch_id', '=', base64_decode($archid))->first();

        $arch_cv_social_media_archive = DB::table('tbl_cv_social_media_archive')->where('arch_id', '=', base64_decode($archid))->get();
        $arch_cv_fav_music_genres_archive = DB::table('tbl_cv_fav_music_genres_archive')->where('arch_id', '=', base64_decode($archid))->get();
        $arch_cv_yt_mood_graph_archive = DB::table('tbl_cv_yt_mood_graph_archive')->where('arch_id', '=', base64_decode($archid))->first();
        $arch_cv_yt_genre_graph_archive = DB::table('tbl_cv_yt_genre_graph_archive')->where('arch_id', '=', base64_decode($archid))->first();
        $arch_cv_ig_mood_graph_archive = DB::table('tbl_cv_ig_mood_graph_archive')->where('arch_id', '=', base64_decode($archid))->first();
        $arch_cv_ig_genre_graph_archive = DB::table('tbl_cv_ig_genre_graph_archive')->where('arch_id', '=', base64_decode($archid))->first();
        $arch_cv_tt_mood_graph_archive = DB::table('tbl_cv_tt_mood_graph_archive')->where('arch_id', '=', base64_decode($archid))->first();
        $arch_cv_tt_genre_graph_archive = DB::table('tbl_cv_tt_genre_graph_archive')->where('arch_id', '=', base64_decode($archid))->first();
        $arch_cv_twt_mood_graph_archive = DB::table('tbl_cv_twt_mood_graph_archive')->where('arch_id', '=', base64_decode($archid))->first();
        $arch_cv_twt_genre_graph_archive = DB::table('tbl_cv_twt_genre_graph_archive')->where('arch_id', '=', base64_decode($archid))->first();
        $arch_cv_aggr_mood_graph_archive = DB::table('tbl_cv_aggr_mood_graph_archive')->where('arch_id', '=', base64_decode($archid))->first();
        $arch_cv_aggr_genre_graph_archive = DB::table('tbl_cv_aggr_genre_graph_archive')->where('arch_id', '=', base64_decode($archid))->first();
        $arch_cv_sonic_logo_music_archive = DB::table('tbl_cv_sonic_logo_music_archive')->where('arch_id', '=', base64_decode($archid))->get();
        $arch_cv_sonic_logo_usage_archive = DB::table('tbl_cv_sonic_logo_usage_archive')->where('arch_id', '=', base64_decode($archid))->first();
        $arch_cv_sonic_logo_usage_ind_avg_archive = DB::table('tbl_cv_sonic_logo_usage_ind_avg_archive')->where('arch_id', '=', base64_decode($archid))->first();
        $arch_cv_most_popular_videos_archive = DB::table('tbl_cv_most_popular_videos_archive')->where('arch_id', '=', base64_decode($archid))->get();
        $arch_cv_day_in_my_life_archive = DB::table('tbl_cv_day_in_my_life_archive')->where('arch_id', '=', base64_decode($archid))->get();
        $arch_cv_social_media_stats_archive = DB::table('tbl_cv_social_media_stats_archive')->where('arch_id', '=', base64_decode($archid))->get();
        $arch_cv_music_type_usage_archive = DB::table('tbl_cv_music_type_usage_archive')->where('arch_id', '=', base64_decode($archid))->get();

        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();

        if(count($arch_cv_social_media_archive)==0)
        {
            $arch_cv_social_media_archive = '';
        }

        if(count($arch_cv_fav_music_genres_archive)==0)
        {
            $arch_cv_fav_music_genres_archive = '';
        }

        if(count($arch_cv_sonic_logo_music_archive)==0)
        {
            $arch_cv_sonic_logo_music_archive = '';
        }

        /* if(count($arch_cv_sonic_logo_usage_archive)==0)
        {
            $arch_cv_sonic_logo_usage_archive = '';
        }

        if(count($arch_cv_sonic_logo_usage_ind_avg_archive)==0)
        {
            $arch_cv_sonic_logo_usage_ind_avg_archive = '';
        } */

        if(count($arch_cv_most_popular_videos_archive)==0)
        {
            $arch_cv_most_popular_videos_archive = '';
        }

        if(count($arch_cv_day_in_my_life_archive)==0)
        {
            $arch_cv_day_in_my_life_archive = '';
        }

        if(count($arch_cv_social_media_stats_archive)==0)
        {
            $arch_cv_social_media_stats_archive = '';
        }

        if(count($arch_cv_music_type_usage_archive)!=0)
        {
            $arch_cv_music_type_usage_archive_titles = [];
            foreach ($arch_cv_music_type_usage_archive as $title_data)
            {
                array_push($arch_cv_music_type_usage_archive_titles,$title_data->title_id);
            }

            if(count($arch_cv_music_type_usage_archive_titles)!=0)
            {
                $excluded_arch_cv_music_type_usage_archive_titles_data = DB::table('tbl_experience')->whereNotIn('experience_id', $arch_cv_music_type_usage_archive_titles)->where("is_active", '=', '0')->get();
            }
        }
        else
        {
            $arch_cv_music_type_usage_archive = '';
            $arch_cv_music_type_usage_archive_titles = [];
            $excluded_arch_cv_music_type_usage_archive_titles_data = [];
        }

        return view('backend.views.display_archived_cv', ['arch_cv_data'=>$arch_cv_data, 'arch_cv_social_media_archive'=>$arch_cv_social_media_archive, 'arch_cv_fav_music_genres_archive'=>$arch_cv_fav_music_genres_archive, 'arch_cv_sonic_logo_music_archive'=>$arch_cv_sonic_logo_music_archive, 'arch_cv_sonic_logo_usage_archive'=>$arch_cv_sonic_logo_usage_archive, 'arch_cv_sonic_logo_usage_ind_avg_archive'=>$arch_cv_sonic_logo_usage_ind_avg_archive, 'arch_cv_most_popular_videos_archive'=>$arch_cv_most_popular_videos_archive, 'arch_cv_day_in_my_life_archive'=>$arch_cv_day_in_my_life_archive, 'arch_cv_social_media_stats_archive'=>$arch_cv_social_media_stats_archive, 'arch_cv_music_type_usage_archive'=>$arch_cv_music_type_usage_archive,  'excluded_arch_cv_music_type_usage_archive_titles_data'=> $excluded_arch_cv_music_type_usage_archive_titles_data, 'arch_cv_yt_mood_graph_archive'=>$arch_cv_yt_mood_graph_archive, 'arch_cv_yt_genre_graph_archive'=>$arch_cv_yt_genre_graph_archive, 'arch_cv_ig_mood_graph_archive'=>$arch_cv_ig_mood_graph_archive, 'arch_cv_ig_genre_graph_archive'=>$arch_cv_ig_genre_graph_archive, 'arch_cv_tt_mood_graph_archive'=>$arch_cv_tt_mood_graph_archive, 'arch_cv_tt_genre_graph_archive'=>$arch_cv_tt_genre_graph_archive, 'arch_cv_twt_mood_graph_archive'=>$arch_cv_twt_mood_graph_archive, 'arch_cv_twt_genre_graph_archive'=>$arch_cv_twt_genre_graph_archive, 'arch_cv_aggr_mood_graph_archive'=>$arch_cv_aggr_mood_graph_archive, 'arch_cv_aggr_genre_graph_archive'=>$arch_cv_aggr_genre_graph_archive, 'cvs_year_data'=>$cvs_year]);

    }

    public function generateArchivedPDF(Request $request)
	{
        /* echo "radarImg1-".$request->radarChartImg1."<br>----------------------------<br>";
        echo "radarImg2-".$request->radarImg2."<br>----------------------------<br>";
        echo "pieChartImg1-".$request->pieChartImg1."<br>----------------------------<br>";
        echo "pieChartImg2-".$request->pieChartImg2."<br>----------------------------<br>";
        echo "doughnutChartImg-".$request->doughnutChartImg."<br>----------------------------<br>";
        echo "barChartImg-".$request->barChartImg."<br>----------------------------<br>";
        echo "pdf_cv_id-".$request->pdf_cv_id."<br>----------------------------<br>"; */
        $archid = $request->pdf_cv_id;
        $mood_graph = $request->radarChartImg1;
        $mood_graph_btns = $request->radarChartImg1Btns;
        $genre_graph = $request->radarChartImg2;
        $genre_graph_btns = $request->radarChartImg2Btns;
        $sec14_val = $request->sec14Val;
        $sec15_val = $request->sec15Val;
        //exit;
        $arch_cv_data = DB::table('tbl_cvs_archive')->where('arch_id', '=', base64_decode($archid))->first();

        $arch_cv_social_media_archive = DB::table('tbl_cv_social_media_archive')->where('arch_id', '=', base64_decode($archid))->get();
        $arch_cv_fav_music_genres_archive = DB::table('tbl_cv_fav_music_genres_archive')->where('arch_id', '=', base64_decode($archid))->get();
        $arch_cv_most_popular_videos_archive = DB::table('tbl_cv_most_popular_videos_archive')->where('arch_id', '=', base64_decode($archid))->get();
        $arch_cv_social_media_stats_archive = DB::table('tbl_cv_social_media_stats_archive')->where('arch_id', '=', base64_decode($archid))->get();

        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();

        if(count($arch_cv_social_media_archive)==0)
        {
            $arch_cv_social_media_archive = '';
        }

        if(count($arch_cv_fav_music_genres_archive)==0)
        {
            $arch_cv_fav_music_genres_archive = '';
        }

        if(count($arch_cv_most_popular_videos_archive)==0)
        {
            $arch_cv_most_popular_videos_archive = '';
        }

        if(count($arch_cv_social_media_stats_archive)==0)
        {
            $arch_cv_social_media_stats_archive = '';
        }

        $cv_block_7_data = $request->pieChartImg1;
        $cv_block_8_data = $request->pieChartImg2;
        $cv_block_10_data = $request->doughnutChartImg;
        $cv_block_13_data = $request->barChartImg;
        $cv_block_14_data = $sec14_val;
        $cv_block_15_data = $sec15_val;

        $data = ['arch_cv_data'=>$arch_cv_data, 'cv_id_year_array'=>$cvs_year, 'arch_cv_social_media_archive'=>$arch_cv_social_media_archive, 'arch_cv_fav_music_genres_archive'=>$arch_cv_fav_music_genres_archive, 'cv_block_7_data'=>$cv_block_7_data, 'cv_block_8_data'=>$cv_block_8_data, 'arch_cv_most_popular_videos_archive'=>$arch_cv_most_popular_videos_archive, 'cv_block_10_data'=>$cv_block_10_data, 'arch_cv_social_media_stats_archive'=>$arch_cv_social_media_stats_archive, 'cv_block_13_data'=>$cv_block_13_data, 'mood_graph'=>$mood_graph, 'mood_graph_btns'=>$mood_graph_btns, 'genre_graph'=>$genre_graph, 'genre_graph_btns'=>$genre_graph_btns];
		/* $pdf = PDF::loadView('backend.views.pdf_doc', $data, [], [
            'watermark'      => 'SONIC RADAR',
            'show_watermark' => true
        ]); */
        $pdf = PDF::loadView('backend.views.archive_pdf_doc', $data);
        //$pdf = PDF::loadView('backend.views.pdf_doc', $data,[],['defaultPagebreakType'=>'slice'/* 'defaultPagebreakType'=>'clonebycss' *//* 'defaultPagebreakType'=>'cloneall' */]);
        return $pdf->stream('archived-sonic-radar-'.$arch_cv_data->cv_name.'_'.$arch_cv_data->cv_year.'_'.$arch_cv_data->cv_archive_dt.'.pdf');
        //return $pdf->download('document.pdf');
	}

    function socialBladeSync()
    {
        //$cv_data = DB::table('tbl_cvs')->where('is_active', '=', 0)->where('status','=',1)->get();

        $cv_data = DB::select(DB::raw("SELECT * FROM tbl_cvs WHERE EXISTS (SELECT DISTINCT(cv_id) FROM tbl_social_spyder_graph_request_data WHERE tbl_social_spyder_graph_request_data.cv_id = tbl_cvs.cv_id and tbl_social_spyder_graph_request_data.is_active=0) and tbl_cvs.status=1 and tbl_cvs.is_active=0"));

        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();

        return view('backend.views.add_cv_to_social_blade_process', ['cv_data'=>$cv_data, 'cvs_year_data'=>$cvs_year]);
    }

    function getChannelsNames($id)
    {
        $cv_id = explode('$_$',base64_decode($id))[0];
        //return $cv_id;
        $chn_data_arr = [];
        $yt_data = DB::table('tbl_cv_block_16_data')
            ->where('cv_id', $cv_id)
            ->where('is_active', 0)
            ->get();
        $counter = 1;
        foreach($yt_data as $ytdata)
        {
            //$chn_data_arr['1'] = $ytdata->chn_name;
            $chn_data_arr["1-".$counter] = $ytdata->chn_name;
            $counter++;
        }
        $ig_data = DB::table('tbl_cv_block_17_data')
            ->where('cv_id', $cv_id)
            ->where('is_active', 0)
            ->get();
        foreach($ig_data as $igdata)
        {
            //$chn_data_arr['2'] = $igdata->chn_name;
            $chn_data_arr["2-".$counter] = $igdata->chn_name;
            $counter++;
        }
        $tt_data = DB::table('tbl_cv_block_18_data')
            ->where('cv_id', $cv_id)
            ->where('is_active', 0)
            ->get();
        foreach($tt_data as $ttdata)
        {
            //$chn_data_arr['3'] = $ttdata->chn_name;
            $chn_data_arr["3-".$counter] = $ttdata->chn_name;
            $counter++;
        }
        $twt_data = DB::table('tbl_cv_block_19_data')
            ->where('cv_id', $cv_id)
            ->where('is_active', 0)
            ->get();
        foreach($twt_data as $twtdata)
        {
            //$chn_data_arr['4'] = $twtdata->chn_name;
            $chn_data_arr["4-".$counter] = $twtdata->chn_name;
            $counter++;
        }
        return $chn_data_arr;
    }

    function addCvToSocialBladeProcessQueue(Request $request)
    {
        //dd($request);die;
        /* $request->validate([
            "channel_name"=>'required',
            "add_brand_in_social_blade_sync_process"=>'required|not_in:0'
        ]); */

        //return $request->input();

        $cv_id = explode('$_$',base64_decode($request->add_brand_in_social_blade_sync_process))[0];
        $cv_name = str_replace("$#$", " ",explode('$_$',base64_decode($request->add_brand_in_social_blade_sync_process))[1]);
        $cv_year = explode("$#$",explode('$_$',base64_decode($request->add_brand_in_social_blade_sync_process))[1])[1]-1;
        $start_date = $cv_year."-01-01";
        $end_date = $cv_year."-12-31";


        $yt_chnl_names_count = $request->yt_social_media_name_count;
        $ig_chnl_names_count = $request->ig_social_media_name_count;
        $tt_chnl_names_count = $request->tt_social_media_name_count;
        $twt_chnl_names_count = $request->twt_social_media_name_count;
        $ins_master_data = [];
        /* echo "yt_chnl_names_count:-".$yt_chnl_names_count."<br>";
        echo "ig_chnl_names_count:-".$ig_chnl_names_count."<br>";
        echo "tt_chnl_names_count:-".$tt_chnl_names_count."<br>";
        echo "twt_chnl_names_count:-".$twt_chnl_names_count."<br>"; */
        if($yt_chnl_names_count > 0 && $yt_chnl_names_count != '')
        {
            $yt_data_arr = [];
            for($i=0; $i<$yt_chnl_names_count; $i++)
            {
                $current_yt_sel_chnl_name = "yt_social_media_name_".$i;

                $chk_yt_data_qry = DB::table('tbl_social_blade_master')
                ->where('cv_name', 'like', explode("$#$",explode('$_$',base64_decode($request->add_brand_in_social_blade_sync_process))[1])[0]."%")
                //->where('data_fetched_from', '<=', $end_date)
                //->where('data_fetched_to', '>=', $start_date)
                ->where('chn_name', '=', $request->$current_yt_sel_chnl_name)
                ->where('social_media_id', '=', 1)
                ->where('is_active', 0)
                ->first();
                if($chk_yt_data_qry == '')
                {
                    if($request->$current_yt_sel_chnl_name != '0' && $request->$current_yt_sel_chnl_name !='')
                    {

                        //$ins_master_data .= "['cv_id' =>".$cv_id.",'cv_name' => '".$cv_name."','chn_name' => '".$request->$current_yt_sel_chnl_name."','social_media_id' => 1 ,'created_by' => ".session('LoggedUser')."],";
                        $yt_data_arr['cv_id'] = $cv_id;
                        $yt_data_arr['cv_name'] = $cv_name;
                        $yt_data_arr['chn_name'] = $request->$current_yt_sel_chnl_name;
                        $yt_data_arr['social_media_id'] = 1;
                        $yt_data_arr['created_by'] = session('LoggedUser');
                    }
                }
                if(!empty($yt_data_arr) && count($yt_data_arr)>0)
                {
                    array_push($ins_master_data, $yt_data_arr);
                }
            }
        }
        //echo $ins_master_data;
        if($ig_chnl_names_count > 0 && $ig_chnl_names_count != '')
        {
            $ig_data_arr = [];
            for($i=0; $i<$ig_chnl_names_count; $i++)
            {
                $current_ig_sel_chnl_name = "ig_social_media_name_".$i;
                $chk_ig_data_qry = DB::table('tbl_social_blade_master')
                ->where('cv_name', 'like', explode("$#$",explode('$_$',base64_decode($request->add_brand_in_social_blade_sync_process))[1])[0]."%")
                //->where('data_fetched_from', '<=', $end_date)
                //->where('data_fetched_to', '>=', $start_date)
                ->where('chn_name', '=', $request->$current_ig_sel_chnl_name)
                ->where('social_media_id', '=', 2)
                ->where('is_active', 0)
                ->first();
                if($chk_ig_data_qry == '')
                {
                    if($request->$current_ig_sel_chnl_name != '0' && $request->$current_ig_sel_chnl_name !='')
                    {
                        //$ins_master_data .= "['cv_id' =>".$cv_id.",'cv_name' => ".$cv_name.",'chn_name' => ".$request->$current_ig_sel_chnl_name.",'social_media_id' => 2 ,'created_by' => ".session('LoggedUser')."],";
                        $ig_data_arr['cv_id'] = $cv_id;
                        $ig_data_arr['cv_name'] = $cv_name;
                        $ig_data_arr['chn_name'] = $request->$current_ig_sel_chnl_name;
                        $ig_data_arr['social_media_id'] = 2;
                        $ig_data_arr['created_by'] = session('LoggedUser');
                    }
                }
                if(!empty($ig_data_arr) && count($ig_data_arr)>0)
                {
                    array_push($ins_master_data, $ig_data_arr);
                }
            }
        }
        if($tt_chnl_names_count > 0 && $tt_chnl_names_count != '')
        {
            $tt_data_arr = [];
            for($i=0; $i<$tt_chnl_names_count; $i++)
            {
                $current_tt_sel_chnl_name = "tt_social_media_name_".$i;
                $chk_tt_data_qry = DB::table('tbl_social_blade_master')
                ->where('cv_name', 'like', explode("$#$",explode('$_$',base64_decode($request->add_brand_in_social_blade_sync_process))[1])[0]."%")
                //->where('data_fetched_from', '<=', $end_date)
                //->where('data_fetched_to', '>=', $start_date)
                ->where('chn_name', '=', $request->$current_tt_sel_chnl_name)
                ->where('social_media_id', '=', 3)
                ->where('is_active', 0)
                ->first();
                if($chk_tt_data_qry == '')
                {
                    if($request->$current_tt_sel_chnl_name !='0' && $request->$current_tt_sel_chnl_name !='')
                    {
                        //$ins_master_data .= "['cv_id' =>".$cv_id.",'cv_name' => ".$cv_name.",'chn_name' => ".$request->$current_tt_sel_chnl_name.",'social_media_id' => 3 ,'created_by' => ".session('LoggedUser')."],";
                        $tt_data_arr['cv_id'] = $cv_id;
                        $tt_data_arr['cv_name'] = $cv_name;
                        $tt_data_arr['chn_name'] = $request->$current_tt_sel_chnl_name;
                        $tt_data_arr['social_media_id'] = 3;
                        $tt_data_arr['created_by'] = session('LoggedUser');
                    }
                }
                if(!empty($tt_data_arr) && count($tt_data_arr)>0)
                {
                    array_push($ins_master_data, $tt_data_arr);
                }
            }
        }
        if($twt_chnl_names_count > 0 && $twt_chnl_names_count != '')
        {
            $twt_data_arr = [];
            for($i=0; $i<$twt_chnl_names_count; $i++)
            {
                $current_twt_sel_chnl_name = "twt_social_media_name_".$i;
                $chk_twt_data_qry = DB::table('tbl_social_blade_master')
                ->where('cv_name', 'like', explode("$#$",explode('$_$',base64_decode($request->add_brand_in_social_blade_sync_process))[1])[0]."%")
                //->where('data_fetched_from', '<=', $end_date)
                //->where('data_fetched_to', '>=', $start_date)
                ->where('chn_name', '=', $request->$current_twt_sel_chnl_name)
                ->where('social_media_id', '=', 4)
                ->where('is_active', 0)
                ->first();
                if($chk_twt_data_qry == '')
                {
                    if($request->$current_twt_sel_chnl_name !='0' && $request->$current_twt_sel_chnl_name !='')
                    {
                        //$ins_master_data .= "['cv_id' =>".$cv_id.",'cv_name' => ".$cv_name.",'chn_name' => ".$request->$current_twt_sel_chnl_name.",'social_media_id' => 4 ,'created_by' => ".session('LoggedUser')."],";
                        $twt_data_arr['cv_id'] = $cv_id;
                        $twt_data_arr['cv_name'] = $cv_name;
                        $twt_data_arr['chn_name'] = $request->$current_twt_sel_chnl_name;
                        $twt_data_arr['social_media_id'] = 4;
                        $twt_data_arr['created_by'] = session('LoggedUser');
                    }
                }
                if(!empty($twt_data_arr) && count($twt_data_arr)>0)
                {
                    array_push($ins_master_data, $twt_data_arr);
                }
            }
        }

        //print_r($ins_master_data); exit;

        if(!empty($ins_master_data))
        {
            $ins_master_data_query = DB::table('tbl_social_blade_master')->insert($ins_master_data);

            if($ins_master_data_query)
            {
                return back()->with('success','Brand Channel name added to process queue successfully');
            }
            else
            {
                return back()->with('fail', 'Something went wrong, please try again!');
            }
        }
        else
        {
            return back()->with('success','Provided Brand Channels are already processed');
        }
        /* $ins_master_data_query = DB::table('tbl_social_blade_master')->insert(
            ['cv_id' => $cv_id,
        'cv_name' => $cv_name,
        'chn_name' => $request->channel_name,
        'social_media_id' => $request->social_media_name,
        'created_by' => session('LoggedUser')]
        ); */
        //return redirect('brand-cvs')->with('success','Brand Sonic Radar data inserted successfully');
    }

    function triggeraddCvToSocialBladeProcessQueue($cv_id){

        $tbl_cvs_data = DB::table('tbl_cvs')->where('status', '=', 1)->where('is_active', 0)->where('cv_id','=',$cv_id)->first();

        if($tbl_cvs_data != ""){

            $cv_name = $tbl_cvs_data->cv_name.' '.$tbl_cvs_data->cv_year;

            $ins_master_data = [];

            $tbl_cv_block_16_data = DB::table('tbl_cv_block_16_data')->where('status', '=', 0)->where('is_active', 0)->where('cv_id','=',$cv_id)->get();

            if(count($tbl_cv_block_16_data) > 0){
                $yt_data_arr = [];
                foreach ($tbl_cv_block_16_data as $key => $value) {
                    $chk_yt_data_qry = DB::table('tbl_social_blade_master')
                    ->where('cv_name', 'like', $cv_name)
                    ->where('chn_name', '=', $value->chn_name)
                    ->where('social_media_id', '=', 1)
                    ->where('is_active', 0)
                    ->first();
                    if($chk_yt_data_qry == '')
                    {
                        $yt_data_arr['cv_id'] = $cv_id;
                        $yt_data_arr['cv_name'] = $cv_name;
                        $yt_data_arr['chn_name'] = $value->chn_name;
                        $yt_data_arr['social_media_id'] = 1;
                        $yt_data_arr['created_by'] = session('LoggedUser');

                    }
                    if(!empty($yt_data_arr) && count($yt_data_arr)>0)
                    {
                        array_push($ins_master_data, $yt_data_arr);
                    }
                }
            }

            $tbl_cv_block_17_data = DB::table('tbl_cv_block_17_data')->where('status', '=', 0)->where('is_active', 0)->where('cv_id','=',$cv_id)->get();

            if(count($tbl_cv_block_17_data) > 0){
                $ig_data_arr = [];
                foreach ($tbl_cv_block_17_data as $key => $value) {
                    $chk_ig_data_qry = DB::table('tbl_social_blade_master')
                    ->where('cv_name', 'like', $cv_name)
                    ->where('chn_name', '=', $value->chn_name)
                    ->where('social_media_id', '=', 2)
                    ->where('is_active', 0)
                    ->first();
                    if($chk_ig_data_qry == '')
                    {
                        $ig_data_arr['cv_id'] = $cv_id;
                        $ig_data_arr['cv_name'] = $cv_name;
                        $ig_data_arr['chn_name'] = $value->chn_name;
                        $ig_data_arr['social_media_id'] = 2;
                        $ig_data_arr['created_by'] = session('LoggedUser');

                    }
                    if(!empty($ig_data_arr) && count($ig_data_arr)>0)
                    {
                        array_push($ins_master_data, $ig_data_arr);
                    }
                }
            }


            $tbl_cv_block_18_data = DB::table('tbl_cv_block_18_data')->where('status', '=', 0)->where('is_active', 0)->where('cv_id','=',$cv_id)->get();

            if(count($tbl_cv_block_18_data) > 0){
                $tt_data_arr = [];
                foreach ($tbl_cv_block_18_data as $key => $value) {
                    $chk_tt_data_qry = DB::table('tbl_social_blade_master')
                    ->where('cv_name', 'like', $cv_name)
                    ->where('chn_name', '=', $value->chn_name)
                    ->where('social_media_id', '=', 3)
                    ->where('is_active', 0)
                    ->first();
                    if($chk_tt_data_qry == '')
                    {
                        $tt_data_arr['cv_id'] = $cv_id;
                        $tt_data_arr['cv_name'] = $cv_name;
                        $tt_data_arr['chn_name'] = $value->chn_name;
                        $tt_data_arr['social_media_id'] = 3;
                        $tt_data_arr['created_by'] = session('LoggedUser');

                    }
                    if(!empty($tt_data_arr) && count($tt_data_arr)>0)
                    {
                        array_push($ins_master_data, $tt_data_arr);
                    }
                }
            }

            $tbl_cv_block_19_data = DB::table('tbl_cv_block_19_data')->where('status', '=', 0)->where('is_active', 0)->where('cv_id','=',$cv_id)->get();

            if(count($tbl_cv_block_19_data) > 0){
                $twt_data_arr = [];
                foreach ($tbl_cv_block_19_data as $key => $value) {
                    $chk_twt_data_qry = DB::table('tbl_social_blade_master')
                    ->where('cv_name', 'like', $cv_name)
                    ->where('chn_name', '=', $value->chn_name)
                    ->where('social_media_id', '=', 4)
                    ->where('is_active', 0)
                    ->first();
                    if($chk_twt_data_qry == '')
                    {
                        $twt_data_arr['cv_id'] = $cv_id;
                        $twt_data_arr['cv_name'] = $cv_name;
                        $twt_data_arr['chn_name'] = $value->chn_name;
                        $twt_data_arr['social_media_id'] = 4;
                        $twt_data_arr['created_by'] = session('LoggedUser');

                    }
                    if(!empty($twt_data_arr) && count($twt_data_arr)>0)
                    {
                        array_push($ins_master_data, $twt_data_arr);
                    }
                }
            }

            if(!empty($ins_master_data))
            {
                $ins_master_data_query = DB::table('tbl_social_blade_master')->insert($ins_master_data);

                if($ins_master_data_query)
                {
                    return 1;
                }
            }
        }
    }

    public function listSocialBladeSyncInProcessCvs()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.social_blade_sync_in_process_list',['cvs_year_data'=>$cvs_year]);
    }

    function getSocialBladeSyncInProcessCvs(Request $request)
    {
        if ($request->ajax()) {

            $data = DB::table('tbl_social_blade_master')
            ->where('status','<',3)
            ->where('is_active','=',0)
            ->get();

            return Datatables::of($data)
                ->addIndexColumn()
                /* ->addColumn('chn_name', function($data){
                    if($data->status == 0)
                    {
                        $chn_name = '<div> <span id="txt_'.str_replace("=","",base64_encode($data->id)).'">'.$data->chn_name.'</span> <form id="form_'.str_replace("=","",base64_encode($data->id)).'" class="hide" action="update-channel-name-in-social-blade" method="post"><input type="hidden" id="'.str_replace("=","",base64_encode($data->id)).'_token" name="_token"><input type="hidden" id="cv_id" name="cv_id" value="'.base64_encode($data->id).'"><input type="text" id="chn_name" name="chn_name" class="card_inputs" style="width:100%; margin-bottom:5px;" value="'.$data->chn_name.'" /> <div style="float:right;"><button class="btn btn-primary btn-sm" type="submit" onClick="addLoader()">Save</button> <span class="btn btn-success btn-sm" onClick=hideArchiveForm("form_'.str_replace("=","",base64_encode($data->id)).'")>Cancel</span></div> </form> <span class="btn btn-primary btn-sm" id="editIcon_'.str_replace("=","",base64_encode($data->id)).'" onClick=showArchiveForm("form_'.str_replace("=","",base64_encode($data->id)).'") style="float: right;"><i class="fas fa-edit"></i></span> </div>';
                        return $chn_name;
                    }
                    else
                    {
                        $chn_name = '<div> <span>'.$data->chn_name.'</span></div>';
                        return $chn_name;
                    }
                }) */
                ->addColumn('social_media_id', function($data){
                    if($data->social_media_id == 1)
                    {
                        $social_media_id = '<div> <span>YouTube</span></div>';
                    }
                    if($data->social_media_id == 2)
                    {
                        $social_media_id = '<div> <span>Instagram</span></div>';
                    }
                    if($data->social_media_id == 3)
                    {
                        $social_media_id = '<div> <span>TikTok</span></div>';
                    }
                    if($data->social_media_id == 4)
                    {
                        $social_media_id = '<div> <span>Twitter</span></div>';
                    }
                    return $social_media_id;
                })
                ->addColumn('status', function($data){
                    if($data->status == 0)
                    {
                        $status = "<span style='color:orange'>In Queue</span>";
                    }
                    if($data->status == 1 || $data->status == 2)
                    {
                        $status = "<span style='color:orange'>In Process</span>";
                    }

                    return $status;
                })
                /* ->addColumn('action', function($data){
                    if($data->is_active == '0')
                    {
                        $actionBtn = '<a href="delete-from-social-blade-process/'.base64_encode($data->id).'" title="Click here to delete" class="delete btn btn-success btn-sm">Delete</a>';
                    }
                    return $actionBtn;
                }) */
                ->rawColumns(['social_media_id','status'])
                ->setRowId(function($data){
                    return $data->id;
                })
                ->make(true);
        }
    }

    function updateChannelNameInSocialBlade(Request $request)
    {
        //return $request->input();
        $chn_name = $request->chn_name;
        $cv_id = base64_decode($request->cv_id);
        $update_query =  DB::table('tbl_social_blade_master')
                            ->where('id', $cv_id)
                            ->update(['chn_name' => $chn_name, 'edited_by'=>session('LoggedUser')]);
        if($update_query)
        {
            return back()->with('success','Channel name updated successfully');
        }
        else
        {
            return back()->with('fail', 'Something went wrong while updating Channel name, please try again!');
        }
    }

    function deleteFromSocialBladeProcess($id)
    {
        $update_cv = DB::table('tbl_social_blade_master')
                            ->where('id', base64_decode($id))
                            ->update(['is_active' => '1', 'edited_by'=>session('LoggedUser')]);

        if($update_cv)
        {
            return back()->with('success','Process deleted successfully');
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }

    public function listSocialBladeSyncCompletedProcessCvs()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.social_blade_sync_completed_process_list',['cvs_year_data'=>$cvs_year]);
    }

    function getSocialBladeSyncCompletedProcessCvs(Request $request)
    {
        if ($request->ajax()) {

            $data = DB::table('tbl_social_blade_master')
            ->where('status','>=',3)
            ->where('is_active','=',0)
            ->get();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('social_media_id', function($data){
                    if($data->social_media_id == 1)
                    {
                        $social_media_id = '<div> <span>YouTube</span></div>';
                    }
                    if($data->social_media_id == 2)
                    {
                        $social_media_id = '<div> <span>Instagram</span></div>';
                    }
                    if($data->social_media_id == 3)
                    {
                        $social_media_id = '<div> <span>TikTok</span></div>';
                    }
                    if($data->social_media_id == 4)
                    {
                        $social_media_id = '<div> <span>Twitter</span></div>';
                    }
                    return $social_media_id;
                })
                ->addColumn('status', function($data){
                    if($data->status == 3)
                    {
                        $status = "<span style='color:green'>Completed</span>";
                    }
                    if($data->status == 4)
                    {
                        $status = "<span style='color:Red'>Not Found</span>";
                    }
                    if($data->status == 5)
                    {
                        $status = "<span style='color:orange'>Partialy Completed</span>";
                    }
                    return $status;
                })
                ->rawColumns(['social_media_id','status'])
                ->setRowId(function($data){
                    return $data->id;
                })
                ->make(true);
        }
    }

    function getHighResImg()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        //$cv_data = DB::select(DB::raw("SELECT tbl_cvs.cv_id,tbl_cvs.cv_name,tbl_cvs.cv_year FROM tbl_genre_aggr_graph_data LEFT JOIN tbl_cvs on tbl_genre_aggr_graph_data.cv_id=tbl_cvs.cv_id WHERE tbl_cvs.is_active=0 and tbl_cvs.status=1"));
        $cv_data = DB::select(DB::raw("SELECT tbl_cvs.cv_id,tbl_cvs.cv_name,tbl_cvs.cv_year FROM tbl_social_media_aggr_genre_graph_data LEFT JOIN tbl_cvs on tbl_social_media_aggr_genre_graph_data.cv_id=tbl_cvs.cv_id WHERE tbl_cvs.is_active=0 and tbl_cvs.status=1 GROUP BY tbl_social_media_aggr_genre_graph_data.cv_id"));
        $ind_data = DB::select(DB::raw("SELECT tbl_industry.industry_id,tbl_industry.industry_name,tbl_ind_social_media_aggr_genre_graph_data.year FROM tbl_ind_social_media_aggr_genre_graph_data LEFT JOIN tbl_industry on tbl_ind_social_media_aggr_genre_graph_data.ind_id=tbl_industry.industry_id WHERE tbl_industry.is_active=0 GROUP BY tbl_ind_social_media_aggr_genre_graph_data.ind_id"));
        $sind_data = DB::select(DB::raw("SELECT tbl_sub_industry.sub_industry_id,tbl_sub_industry.sub_industry_name,tbl_sind_social_media_aggr_genre_graph_data.year FROM tbl_sind_social_media_aggr_genre_graph_data LEFT JOIN tbl_sub_industry on tbl_sind_social_media_aggr_genre_graph_data.sind_id=tbl_sub_industry.sub_industry_id WHERE tbl_sub_industry.is_active=0 GROUP BY tbl_sind_social_media_aggr_genre_graph_data.sind_id"));

        /* $res_collection = collect($cv_search_query);
        $res_merged_1    = $res_collection->merge($ind_search_query);
        $res_merged_2    = $res_merged_1->merge($sind_search_query);
        $data[]   = $res_merged_2->all(); */

        return view('backend.views.download_high_res_images', ['cv_data'=>$cv_data,'ind_data'=>$ind_data,'sind_data'=>$sind_data,'cvs_year_data'=>$cvs_year]);
    }

    /* function getCvIndNames($sval_input)
    {
        //echo $sval_input;
        $sval = $sval_input;

        $search_query = DB::select(DB::raw("SELECT tbl_industry.industry_name AS name, tbl_industry.industry_id AS id , tbl_industry.ind_date AS cvdate FROM tbl_industry where industry_name LIKE '".$sval."%' and is_active = 0 UNION SELECT tbl_sub_industry.sub_industry_name AS name, tbl_sub_industry.sub_industry_id AS id , tbl_sub_industry.sub_ind_date AS cvdate FROM tbl_sub_industry where sub_industry_name LIKE '".$sval."%' and is_active = 0 and parent_industry_id != 0 UNION SELECT tbl_cvs.cv_name AS name, tbl_cvs.cv_id AS id, tbl_cvs.cv_date AS cvdate FROM tbl_cvs where cv_name LIKE '".$sval."%' and status = 1 and is_active = 0 group by tbl_cvs.cv_name ORDER BY name ASC"));

        if(count($search_query) != 0 && $search_query !='')
        {
            $output = '<div class="divAutocompleteScroll">';
            $output1 = '<div class="divDropDown_LP"><ul class="dropdown-menu"><li>Brands:</li>';
            $output2 = '<div class="divDropDown_RP"><ul class="dropdown-menu"><li>Industries:</li>';
        }
        else
        {
            $output = '<div><ul class="dropdown-menu"><li><span>No matching record found</span></li>';
        }
        foreach($search_query as $row)
        {
            if($row->cvdate != 'ind' && $row->cvdate != 'subind')
            {
                $output1 .= '<li onClick="getYears(\''.base64_encode('cv').'\',\''. base64_encode($row->name).'\')">'.$row->name.'</li>';
            }
            elseif($row->cvdate == 'ind' && $row->cvdate != 'subind')
            {
                $output2 .= '<li onClick="getYears(\''.base64_encode('ind').'\',\''. base64_encode($row->id).'\')>'.$row->name.'</li>';
            }
            else
            {
                $output2 .= '<li onClick="getYears(\''.base64_encode('sind').'\',\''. base64_encode($row->id).'\') class="subInd">'.$row->name.'</li>';
            }
        }
        $output1 .= '</ul></div>';
        $output2 .= '</ul></div>';

        $output .= $output1.$output2."</div>";

        echo $output;
    } */

    /* function getCvIndGraphData($graph_input)
    {

        $type = explode('$|$',$graph_input)[0];
        $sdata = explode('$|$',$graph_input)[1];
        $id = explode('$_$',base64_decode($sdata))[0];
        $year = explode('$_$',base64_decode($sdata))[1];

        //echo $type.":".base64_decode($sdata)."::".$sub_industry_id.":::".$cv_year;exit;

        $cv_data = DB::table('tbl_cvs')->select('cv_name')->where('cv_id', '=', $id)->first();

        if($type == 'ind')
        {
            $data = DB::table('tbl_industry')->select('industry_name')->where('industry_id', '=', $id)->first();
            $name_data = str_replace(" ","_",$data->industry_name)."_".$year;
            $mood_aggr_graph_data = DB::table('tbl_industry_mood_aggr_graph_data')->where('ind_id', '=', $id)->where('ind_year', '=', $year)->where('is_active', '=', 0)->first();
            $genre_aggr_graph_data = DB::table('tbl_industry_genre_aggr_graph_data')->where('ind_id', '=', $id)->where('ind_year', '=', $year)->where('is_active', '=', 0)->first();
        }
        else if($type == 'sind')
        {
            $data = DB::table('tbl_sub_industry')->select('sub_industry_name')->where('sub_industry_id', '=', $id)->first();
            $name_data = str_replace(" ","_",$data->sub_industry_name)."_".$year;
            $mood_aggr_graph_data = DB::table('tbl_sub_industry_mood_aggr_graph_data')->where('sind_id', '=', $id)->where('sind_year', '=', $year)->where('is_active', '=', 0)->first();
            $genre_aggr_graph_data = DB::table('tbl_sub_industry_genre_aggr_graph_data')->where('sind_id', '=', $id)->where('sind_year', '=', $year)->where('is_active', '=', 0)->first();
        }
        else
        {
            $data = DB::table('tbl_cvs')->select('cv_name')->where('cv_id', '=', $id)->first();
            $name_data = str_replace(" ","_",$data->cv_name)."_".$year;
            $mood_aggr_graph_data = DB::table('tbl_mood_aggr_graph_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->first();
            $genre_aggr_graph_data = DB::table('tbl_genre_aggr_graph_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->first();
        }

        if($mood_aggr_graph_data!='' && $genre_aggr_graph_data!='')
        {
            $mood_aggr_graph_dataset_data = [];
            $mood_aggr_graph_datalables_data = 'aggressive,calm,chilled,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting';

            array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->aggressive);
            array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->calm);
            array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->chilled);
            array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->energetic);
            array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->epic);
            array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->happy);
            array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->romantic);
            array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->sad);
            array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->scary);
            array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->sexy);
            array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->ethereal);
            array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->uplifting);

            $genre_aggr_graph_dataset_data = [];
            $genre_aggr_graph_datalables_data = 'ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter';
            $genre_bg_color_array = config('custom.genre_bg_color_array');

            array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->ambient);
            array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->blues);
            array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->classical);
            array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->country);
            array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->electronicDance);
            array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->folk);
            array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->indieAlternative);
            array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->jazz);
            array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->latin);
            array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->metal);
            array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->pop);
            array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->punk);
            array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->rapHipHop);
            array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->reggae);
            array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->rnb);
            array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->rock);
            array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->singerSongwriter);

            return ['mood_aggr_graph_dataset_data'=>$mood_aggr_graph_dataset_data, 'mood_aggr_graph_datalables_data'=>$mood_aggr_graph_datalables_data,'genre_aggr_graph_dataset_data'=>$genre_aggr_graph_dataset_data, 'genre_aggr_graph_datalables_data'=>$genre_aggr_graph_datalables_data,'genre_bg_color_array'=>$genre_bg_color_array,'name_data'=>$name_data];
        }
        else
        {
            return 'No Data';
        }


    } */

    function getCvIndGraphData($graph_input)
    {
        $type = explode('$|$',$graph_input)[0];
        $sdata = explode('$|$',$graph_input)[1];
        $id = explode('$_$',base64_decode($sdata))[0];
        $year = explode('$_$',base64_decode($sdata))[1];

        //echo $type.":".base64_decode($sdata);exit;

        $cv_data = DB::table('tbl_cvs')->select('cv_name')->where('cv_id', '=', $id)->first();

        if($type == 'find' || $type == 'fsind' || $type == 'fcv')
        {
            if($type == 'find')
            {
                $data = DB::table('tbl_industry')->select('industry_name')->where('industry_id', '=', $id)->first();
                $name_data = str_replace(" ","_",$data->industry_name)."_".$year;

                /* $mood_yt_graph_data = DB::table('tbl_industry_youtube_mood_graph_data')->where('ind_id', '=', $id)->where('ind_year', '=', $year)->where('is_active', '=', 0)->first();
                $genre_yt_graph_data = DB::table('tbl_industry_youtube_genre_graph_data')->where('ind_id', '=', $id)->where('ind_year', '=', $year)->where('is_active', '=', 0)->first();
                $mood_ig_graph_data = DB::table('tbl_industry_instagram_mood_graph_data')->where('ind_id', '=', $id)->where('ind_year', '=', $year)->where('is_active', '=', 0)->first();
                $genre_ig_graph_data = DB::table('tbl_industry_instagram_genre_graph_data')->where('ind_id', '=', $id)->where('ind_year', '=', $year)->where('is_active', '=', 0)->first();
                $mood_tt_graph_data = DB::table('tbl_industry_tiktok_mood_graph_data')->where('ind_id', '=', $id)->where('ind_year', '=', $year)->where('is_active', '=', 0)->first();
                $genre_tt_graph_data = DB::table('tbl_industry_tiktok_genre_graph_data')->where('ind_id', '=', $id)->where('ind_year', '=', $year)->where('is_active', '=', 0)->first();
                $mood_twt_graph_data = DB::table('tbl_industry_twitter_mood_graph_data')->where('ind_id', '=', $id)->where('ind_year', '=', $year)->where('is_active', '=', 0)->first();
                $genre_twt_graph_data = DB::table('tbl_industry_twitter_genre_graph_data')->where('ind_id', '=', $id)->where('ind_year', '=', $year)->where('is_active', '=', 0)->first();

                $mood_aggr_graph_data = DB::table('tbl_industry_mood_aggr_graph_data')->where('ind_id', '=', $id)->where('ind_year', '=', $year)->where('is_active', '=', 0)->first();
                $genre_aggr_graph_data = DB::table('tbl_industry_genre_aggr_graph_data')->where('ind_id', '=', $id)->where('ind_year', '=', $year)->where('is_active', '=', 0)->first(); */
                $mood_yt_graph_data = DB::table('tbl_ind_social_media_yt_mood_graph_data')
                ->where('ind_id','=',$id)
                ->where('year','=',$year)
                ->where('is_active', '=', 0)
                ->get();
                // print_r($yt_mood_data);
                // echo "<br>#########################################################<br>";
                $genre_yt_graph_data = DB::table('tbl_ind_social_media_yt_genre_graph_data')
                ->where('ind_id','=',$id)
                ->where('year','=',$year)
                ->where('is_active', '=', 0)
                ->get();
                // print_r($yt_genre_data);
                // echo "<br>||||||||||||||||||||||||||||||||||||||||||||||||||||||||||<br><br>";

                $mood_ig_graph_data = DB::table('tbl_ind_social_media_ig_mood_graph_data')
                ->where('ind_id','=',$id)
                ->where('year','=',$year)
                ->where('is_active', '=', 0)
                ->get();
                // print_r($ig_mood_data);
                // echo "<br>#########################################################<br>";
                $genre_ig_graph_data = DB::table('tbl_ind_social_media_ig_genre_graph_data')
                ->where('ind_id','=',$id)
                ->where('year','=',$year)
                ->where('is_active', '=', 0)
                ->get();
                // print_r($ig_genre_data);
                // echo "<br>||||||||||||||||||||||||||||||||||||||||||||||||||||||||||<br><br>";

                $mood_tt_graph_data = DB::table('tbl_ind_social_media_tt_mood_graph_data')
                ->where('ind_id','=',$id)
                ->where('year','=',$year)
                ->where('is_active', '=', 0)
                ->get();
                // print_r($tt_mood_data);
                // echo "<br>#########################################################<br>";
                $genre_tt_graph_data = DB::table('tbl_ind_social_media_tt_genre_graph_data')
                ->where('ind_id','=',$id)
                ->where('year','=',$year)
                ->where('is_active', '=', 0)
                ->get();
                // print_r($tt_genre_data);
                // echo "<br>||||||||||||||||||||||||||||||||||||||||||||||||||||||||||<br><br>";

                $mood_twt_graph_data = DB::table('tbl_ind_social_media_twt_mood_graph_data')
                ->where('ind_id','=',$id)
                ->where('year','=',$year)
                ->where('is_active', '=', 0)
                ->get();
                // print_r($twt_mood_data);
                // echo "<br>#########################################################<br>";
                $genre_twt_graph_data = DB::table('tbl_ind_social_media_twt_genre_graph_data')
                ->where('ind_id','=',$id)
                ->where('year','=',$year)
                ->where('is_active', '=', 0)
                ->get();
                // print_r($twt_genre_data);
                // echo "<br>||||||||||||||||||||||||||||||||||||||||||||||||||||||||||<br><br>";

                $mood_aggr_graph_data = DB::table('tbl_ind_social_media_aggr_mood_graph_data')
                ->where('ind_id','=',$id)
                ->where('year','=',$year)
                ->where('is_active', '=', 0)
                ->get();
                // print_r($avg_mood_data);
                // echo "<br>#########################################################<br>";

                $genre_aggr_graph_data = DB::table('tbl_ind_social_media_aggr_genre_graph_data')
                ->where('ind_id','=',$id)
                ->where('year','=',$year)
                ->where('is_active', '=', 0)
                ->get();

            }
            else if($type == 'fsind')
            {
                $data = DB::table('tbl_sub_industry')->select('sub_industry_name')->where('sub_industry_id', '=', $id)->first();
                $name_data = str_replace(" ","_",$data->sub_industry_name)."_".$year;

                /* $mood_yt_graph_data = DB::table('tbl_sub_industry_youtube_mood_graph_data')->where('sind_id', '=', $id)->where('sind_year', '=', $year)->where('is_active', '=', 0)->first();
                $genre_yt_graph_data = DB::table('tbl_sub_industry_youtube_genre_graph_data')->where('sind_id', '=', $id)->where('sind_year', '=', $year)->where('is_active', '=', 0)->first();
                $mood_ig_graph_data = DB::table('tbl_sub_industry_instagram_mood_graph_data')->where('sind_id', '=', $id)->where('sind_year', '=', $year)->where('is_active', '=', 0)->first();
                $genre_ig_graph_data = DB::table('tbl_sub_industry_instagram_genre_graph_data')->where('sind_id', '=', $id)->where('sind_year', '=', $year)->where('is_active', '=', 0)->first();
                $mood_tt_graph_data = DB::table('tbl_sub_industry_tiktok_mood_graph_data')->where('sind_id', '=', $id)->where('sind_year', '=', $year)->where('is_active', '=', 0)->first();
                $genre_tt_graph_data = DB::table('tbl_sub_industry_tiktok_genre_graph_data')->where('sind_id', '=', $id)->where('sind_year', '=', $year)->where('is_active', '=', 0)->first();
                $mood_twt_graph_data = DB::table('tbl_sub_industry_twitter_mood_graph_data')->where('sind_id', '=', $id)->where('sind_year', '=', $year)->where('is_active', '=', 0)->first();
                $genre_twt_graph_data = DB::table('tbl_sub_industry_twitter_genre_graph_data')->where('sind_id', '=', $id)->where('sind_year', '=', $year)->where('is_active', '=', 0)->first();

                $mood_aggr_graph_data = DB::table('tbl_sub_industry_mood_aggr_graph_data')->where('sind_id', '=', $id)->where('sind_year', '=', $year)->where('is_active', '=', 0)->first();
                $genre_aggr_graph_data = DB::table('tbl_sub_industry_genre_aggr_graph_data')->where('sind_id', '=', $id)->where('sind_year', '=', $year)->where('is_active', '=', 0)->first(); */

                $mood_yt_graph_data = DB::table('tbl_sind_social_media_yt_mood_graph_data')
                ->where('sind_id','=',$id)
                ->where('year','=',$year)
                ->where('is_active', '=', 0)
                ->get();

                $genre_yt_graph_data = DB::table('tbl_sind_social_media_yt_genre_graph_data')
                ->where('sind_id','=',$id)
                ->where('year','=',$year)
                ->where('is_active', '=', 0)
                ->get();


                $mood_ig_graph_data = DB::table('tbl_sind_social_media_ig_mood_graph_data')
                ->where('sind_id','=',$id)
                ->where('year','=',$year)
                ->where('is_active', '=', 0)
                ->get();

                $genre_ig_graph_data = DB::table('tbl_sind_social_media_ig_genre_graph_data')
                ->where('sind_id','=',$id)
                ->where('year','=',$year)
                ->where('is_active', '=', 0)
                ->get();


                $mood_tt_graph_data = DB::table('tbl_sind_social_media_tt_mood_graph_data')
                ->where('sind_id','=',$id)
                ->where('year','=',$year)
                ->where('is_active', '=', 0)
                ->get();

                $genre_tt_graph_data = DB::table('tbl_sind_social_media_tt_genre_graph_data')
                ->where('sind_id','=',$id)
                ->where('year','=',$year)
                ->where('is_active', '=', 0)
                ->get();


                $mood_twt_graph_data = DB::table('tbl_sind_social_media_twt_mood_graph_data')
                ->where('sind_id','=',$id)
                ->where('year','=',$year)
                ->where('is_active', '=', 0)
                ->get();

                $genre_twt_graph_data = DB::table('tbl_sind_social_media_twt_genre_graph_data')
                ->where('sind_id','=',$id)
                ->where('year','=',$year)
                ->where('is_active', '=', 0)
                ->get();


                $mood_aggr_graph_data = DB::table('tbl_sind_social_media_aggr_mood_graph_data')
                ->where('sind_id','=',$id)
                ->where('year','=',$year)
                ->where('is_active', '=', 0)
                ->get();

                $genre_aggr_graph_data = DB::table('tbl_sind_social_media_aggr_genre_graph_data')
                ->where('sind_id','=',$id)
                ->where('year','=',$year)
                ->where('is_active', '=', 0)
                ->get();

            }
            else
            {
                $data = DB::table('tbl_cvs')->select('cv_name')->where('cv_id', '=', $id)->first();
                $name_data = str_replace(" ","_",$data->cv_name)."_".$year;

                /* $mood_yt_graph_data = DB::table('tbl_cv_block_16_mood_graph_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->first();
                $genre_yt_graph_data = DB::table('tbl_cv_block_16_genre_graph_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->first();
                $mood_ig_graph_data = DB::table('tbl_cv_block_17_mood_graph_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->first();
                $genre_ig_graph_data = DB::table('tbl_cv_block_17_genre_graph_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->first();
                $mood_tt_graph_data = DB::table('tbl_cv_block_18_mood_graph_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->first();
                $genre_tt_graph_data = DB::table('tbl_cv_block_18_genre_graph_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->first();
                $mood_twt_graph_data = DB::table('tbl_cv_block_19_mood_graph_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->first();
                $genre_twt_graph_data = DB::table('tbl_cv_block_19_genre_graph_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->first();

                $mood_aggr_graph_data = DB::table('tbl_mood_aggr_graph_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->first();
                $genre_aggr_graph_data = DB::table('tbl_genre_aggr_graph_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->first(); */

                // Get Mood and Genre Graph data
                $process_type_array = ['youtube', 'instagram', 'tiktok', 'twitter'];
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
                                        ->where('tbl_cvs.cv_id', '=', $id)
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

                $mood_yt_graph_data = DB::table('tbl_social_media_yt_mood_graph_data')
                ->where('cv_id', '=', $id)
                ->where('is_active', '=', 0)
                ->get();

                $genre_yt_graph_data = DB::table('tbl_social_media_yt_genre_graph_data')
                ->where('cv_id', '=', $id)
                ->where('is_active', '=', 0)
                ->get();


                $mood_ig_graph_data = DB::table('tbl_social_media_ig_mood_graph_data')
                ->where('cv_id', '=', $id)
                ->where('is_active', '=', 0)
                ->get();

                $genre_ig_graph_data =  DB::table('tbl_social_media_ig_genre_graph_data')
                ->where('cv_id', '=', $id)
                ->where('is_active', '=', 0)
                ->get();


                $mood_tt_graph_data = DB::table('tbl_social_media_tt_mood_graph_data')
                ->where('cv_id', '=', $id)
                ->where('is_active', '=', 0)
                ->get();

                $genre_tt_graph_data =  DB::table('tbl_social_media_tt_genre_graph_data')
                ->where('cv_id', '=', $id)
                ->where('is_active', '=', 0)
                ->get();


                $mood_twt_graph_data = DB::table('tbl_social_media_twt_mood_graph_data')
                ->where('cv_id', '=', $id)
                ->where('is_active', '=', 0)
                ->get();

                $genre_twt_graph_data =  DB::table('tbl_social_media_twt_genre_graph_data')
                ->where('cv_id', '=', $id)
                ->where('is_active', '=', 0)
                ->get();


                $mood_aggr_graph_data = DB::table('tbl_social_media_aggr_mood_graph_data')
                ->where('cv_id', '=', $id)
                ->where('is_active', '=', 0)
                ->get();

                $genre_aggr_graph_data =  DB::table('tbl_social_media_aggr_genre_graph_data')
                ->where('cv_id', '=', $id)
                ->where('is_active', '=', 0)
                ->get();
            }

            if(($mood_yt_graph_data!='' && count($mood_yt_graph_data) !=0 && $genre_yt_graph_data!='' && count($genre_yt_graph_data) !=0) || ($mood_ig_graph_data!='' && count($mood_ig_graph_data) !=0 && $genre_ig_graph_data!='' && count($genre_ig_graph_data) !=0) || ($mood_tt_graph_data!='' && count($mood_tt_graph_data) !=0 && $genre_tt_graph_data!='' && count($genre_tt_graph_data) !=0) || ($mood_twt_graph_data!='' && count($mood_twt_graph_data) !=0 && $genre_twt_graph_data!='' && count($genre_twt_graph_data) !=0) || ($mood_aggr_graph_data!='' && count($mood_aggr_graph_data) !=0 && $genre_aggr_graph_data!='' && count($genre_aggr_graph_data) !=0))
            {

                if($mood_yt_graph_data!='' && count($mood_yt_graph_data) !=0 && $genre_yt_graph_data!='' && count($genre_yt_graph_data) !=0)
                {
                    $mood_yt_graph_dataset_data = [];
                    $mood_yt_graph_datalables_data =[];
                    /* $mood_yt_graph_datalables_data = 'aggressive,calm,chilled,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting';

                    array_push($mood_yt_graph_dataset_data,$mood_yt_graph_data->aggressive);
                    array_push($mood_yt_graph_dataset_data,$mood_yt_graph_data->calm);
                    array_push($mood_yt_graph_dataset_data,$mood_yt_graph_data->chilled);
                    array_push($mood_yt_graph_dataset_data,$mood_yt_graph_data->energetic);
                    array_push($mood_yt_graph_dataset_data,$mood_yt_graph_data->epic);
                    array_push($mood_yt_graph_dataset_data,$mood_yt_graph_data->happy);
                    array_push($mood_yt_graph_dataset_data,$mood_yt_graph_data->romantic);
                    array_push($mood_yt_graph_dataset_data,$mood_yt_graph_data->sad);
                    array_push($mood_yt_graph_dataset_data,$mood_yt_graph_data->scary);
                    array_push($mood_yt_graph_dataset_data,$mood_yt_graph_data->sexy);
                    array_push($mood_yt_graph_dataset_data,$mood_yt_graph_data->ethereal);
                    array_push($mood_yt_graph_dataset_data,$mood_yt_graph_data->uplifting); */

                    foreach($mood_yt_graph_data as $mytgddata)
                    {
                        array_push($mood_yt_graph_datalables_data,$mytgddata->lbl_name);
                        array_push($mood_yt_graph_dataset_data,round($mytgddata->lbl_value,2));
                    }

                    $genre_yt_graph_dataset_data = [];
                    $genre_yt_graph_datalables_data =[];
                    // $genre_yt_graph_datalables_data = 'ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter';
                    $genre_bg_color_array = config('custom.genre_bg_color_array');

                    /* array_push($genre_yt_graph_dataset_data,$genre_yt_graph_data->ambient);
                    array_push($genre_yt_graph_dataset_data,$genre_yt_graph_data->blues);
                    array_push($genre_yt_graph_dataset_data,$genre_yt_graph_data->classical);
                    array_push($genre_yt_graph_dataset_data,$genre_yt_graph_data->country);
                    array_push($genre_yt_graph_dataset_data,$genre_yt_graph_data->electronicDance);
                    array_push($genre_yt_graph_dataset_data,$genre_yt_graph_data->folk);
                    array_push($genre_yt_graph_dataset_data,$genre_yt_graph_data->indieAlternative);
                    array_push($genre_yt_graph_dataset_data,$genre_yt_graph_data->jazz);
                    array_push($genre_yt_graph_dataset_data,$genre_yt_graph_data->latin);
                    array_push($genre_yt_graph_dataset_data,$genre_yt_graph_data->metal);
                    array_push($genre_yt_graph_dataset_data,$genre_yt_graph_data->pop);
                    array_push($genre_yt_graph_dataset_data,$genre_yt_graph_data->punk);
                    array_push($genre_yt_graph_dataset_data,$genre_yt_graph_data->rapHipHop);
                    array_push($genre_yt_graph_dataset_data,$genre_yt_graph_data->reggae);
                    array_push($genre_yt_graph_dataset_data,$genre_yt_graph_data->rnb);
                    array_push($genre_yt_graph_dataset_data,$genre_yt_graph_data->rock);
                    array_push($genre_yt_graph_dataset_data,$genre_yt_graph_data->singerSongwriter); */

                    foreach($genre_yt_graph_data as $gytgddata)
                    {
                        array_push($genre_yt_graph_datalables_data,$gytgddata->lbl_name);
                        array_push($genre_yt_graph_dataset_data,round($gytgddata->lbl_value,2));
                    }
                }
                else
                {
                    $mood_yt_graph_dataset_data = [];
                    $mood_yt_graph_datalables_data =[];
                    // $mood_yt_graph_datalables_data = 'aggressive,calm,chilled,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting';
                    $genre_yt_graph_dataset_data = [];
                    $genre_yt_graph_datalables_data =[];
                    // $genre_yt_graph_datalables_data = 'ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter';
                    $genre_bg_color_array = config('custom.genre_bg_color_array');
                }

                if($mood_ig_graph_data!='' && count($mood_ig_graph_data) !=0 && $genre_ig_graph_data!='' && count($genre_ig_graph_data) !=0)
                {
                    $mood_ig_graph_dataset_data = [];
                    $mood_ig_graph_datalables_data = [];
                    /* $mood_ig_graph_datalables_data = 'aggressive,calm,chilled,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting';

                    array_push($mood_ig_graph_dataset_data,$mood_ig_graph_data->aggressive);
                    array_push($mood_ig_graph_dataset_data,$mood_ig_graph_data->calm);
                    array_push($mood_ig_graph_dataset_data,$mood_ig_graph_data->chilled);
                    array_push($mood_ig_graph_dataset_data,$mood_ig_graph_data->energetic);
                    array_push($mood_ig_graph_dataset_data,$mood_ig_graph_data->epic);
                    array_push($mood_ig_graph_dataset_data,$mood_ig_graph_data->happy);
                    array_push($mood_ig_graph_dataset_data,$mood_ig_graph_data->romantic);
                    array_push($mood_ig_graph_dataset_data,$mood_ig_graph_data->sad);
                    array_push($mood_ig_graph_dataset_data,$mood_ig_graph_data->scary);
                    array_push($mood_ig_graph_dataset_data,$mood_ig_graph_data->sexy);
                    array_push($mood_ig_graph_dataset_data,$mood_ig_graph_data->ethereal);
                    array_push($mood_ig_graph_dataset_data,$mood_ig_graph_data->uplifting); */

                    foreach($mood_ig_graph_data as $miggddata)
                    {
                        array_push($mood_ig_graph_datalables_data,$miggddata->lbl_name);
                        array_push($mood_ig_graph_dataset_data,round($miggddata->lbl_value,2));
                    }

                    $genre_ig_graph_dataset_data = [];
                    $genre_ig_graph_datalables_data = [];
                    // $genre_ig_graph_datalables_data = 'ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter';
                    $genre_bg_color_array = config('custom.genre_bg_color_array');

                    /* array_push($genre_ig_graph_dataset_data,$genre_ig_graph_data->ambient);
                    array_push($genre_ig_graph_dataset_data,$genre_ig_graph_data->blues);
                    array_push($genre_ig_graph_dataset_data,$genre_ig_graph_data->classical);
                    array_push($genre_ig_graph_dataset_data,$genre_ig_graph_data->country);
                    array_push($genre_ig_graph_dataset_data,$genre_ig_graph_data->electronicDance);
                    array_push($genre_ig_graph_dataset_data,$genre_ig_graph_data->folk);
                    array_push($genre_ig_graph_dataset_data,$genre_ig_graph_data->indieAlternative);
                    array_push($genre_ig_graph_dataset_data,$genre_ig_graph_data->jazz);
                    array_push($genre_ig_graph_dataset_data,$genre_ig_graph_data->latin);
                    array_push($genre_ig_graph_dataset_data,$genre_ig_graph_data->metal);
                    array_push($genre_ig_graph_dataset_data,$genre_ig_graph_data->pop);
                    array_push($genre_ig_graph_dataset_data,$genre_ig_graph_data->punk);
                    array_push($genre_ig_graph_dataset_data,$genre_ig_graph_data->rapHipHop);
                    array_push($genre_ig_graph_dataset_data,$genre_ig_graph_data->reggae);
                    array_push($genre_ig_graph_dataset_data,$genre_ig_graph_data->rnb);
                    array_push($genre_ig_graph_dataset_data,$genre_ig_graph_data->rock);
                    array_push($genre_ig_graph_dataset_data,$genre_ig_graph_data->singerSongwriter); */

                    foreach($genre_ig_graph_data as $giggddata)
                    {
                        array_push($genre_ig_graph_datalables_data,$giggddata->lbl_name);
                        array_push($genre_ig_graph_dataset_data,round($giggddata->lbl_value,2));
                    }
                }
                else
                {
                    $mood_ig_graph_dataset_data = [];
                    $mood_ig_graph_datalables_data = [];
                    // $mood_ig_graph_datalables_data = 'aggressive,calm,chilled,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting';
                    $genre_ig_graph_dataset_data = [];
                    $genre_ig_graph_datalables_data = [];
                    // $genre_ig_graph_datalables_data = 'ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter';
                    $genre_bg_color_array = config('custom.genre_bg_color_array');
                }

                if($mood_tt_graph_data!='' && count($mood_tt_graph_data) !=0 && $genre_tt_graph_data!='' && count($genre_tt_graph_data) !=0)
                {
                    $mood_tt_graph_dataset_data = [];
                    $mood_tt_graph_datalables_data = [];
                    /* $mood_tt_graph_datalables_data = 'aggressive,calm,chilled,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting';

                    array_push($mood_tt_graph_dataset_data,$mood_tt_graph_data->aggressive);
                    array_push($mood_tt_graph_dataset_data,$mood_tt_graph_data->calm);
                    array_push($mood_tt_graph_dataset_data,$mood_tt_graph_data->chilled);
                    array_push($mood_tt_graph_dataset_data,$mood_tt_graph_data->energetic);
                    array_push($mood_tt_graph_dataset_data,$mood_tt_graph_data->epic);
                    array_push($mood_tt_graph_dataset_data,$mood_tt_graph_data->happy);
                    array_push($mood_tt_graph_dataset_data,$mood_tt_graph_data->romantic);
                    array_push($mood_tt_graph_dataset_data,$mood_tt_graph_data->sad);
                    array_push($mood_tt_graph_dataset_data,$mood_tt_graph_data->scary);
                    array_push($mood_tt_graph_dataset_data,$mood_tt_graph_data->sexy);
                    array_push($mood_tt_graph_dataset_data,$mood_tt_graph_data->ethereal);
                    array_push($mood_tt_graph_dataset_data,$mood_tt_graph_data->uplifting); */

                    foreach($mood_tt_graph_data as $mttgddata)
                    {
                        array_push($mood_tt_graph_datalables_data,$mttgddata->lbl_name);
                        array_push($mood_tt_graph_dataset_data,round($mttgddata->lbl_value,2));
                    }

                    $genre_tt_graph_dataset_data = [];
                    $genre_tt_graph_datalables_data = [];
                    // $genre_tt_graph_datalables_data = 'ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter';
                    $genre_bg_color_array = config('custom.genre_bg_color_array');

                    /* array_push($genre_tt_graph_dataset_data,$genre_tt_graph_data->ambient);
                    array_push($genre_tt_graph_dataset_data,$genre_tt_graph_data->blues);
                    array_push($genre_tt_graph_dataset_data,$genre_tt_graph_data->classical);
                    array_push($genre_tt_graph_dataset_data,$genre_tt_graph_data->country);
                    array_push($genre_tt_graph_dataset_data,$genre_tt_graph_data->electronicDance);
                    array_push($genre_tt_graph_dataset_data,$genre_tt_graph_data->folk);
                    array_push($genre_tt_graph_dataset_data,$genre_tt_graph_data->indieAlternative);
                    array_push($genre_tt_graph_dataset_data,$genre_tt_graph_data->jazz);
                    array_push($genre_tt_graph_dataset_data,$genre_tt_graph_data->latin);
                    array_push($genre_tt_graph_dataset_data,$genre_tt_graph_data->metal);
                    array_push($genre_tt_graph_dataset_data,$genre_tt_graph_data->pop);
                    array_push($genre_tt_graph_dataset_data,$genre_tt_graph_data->punk);
                    array_push($genre_tt_graph_dataset_data,$genre_tt_graph_data->rapHipHop);
                    array_push($genre_tt_graph_dataset_data,$genre_tt_graph_data->reggae);
                    array_push($genre_tt_graph_dataset_data,$genre_tt_graph_data->rnb);
                    array_push($genre_tt_graph_dataset_data,$genre_tt_graph_data->rock);
                    array_push($genre_tt_graph_dataset_data,$genre_tt_graph_data->singerSongwriter); */

                    foreach($genre_tt_graph_data as $gttgddata)
                    {
                        array_push($genre_tt_graph_datalables_data,$gttgddata->lbl_name);
                        array_push($genre_tt_graph_dataset_data,round($gttgddata->lbl_value,2));
                    }
                }
                else
                {
                    $mood_tt_graph_dataset_data = [];
                    $mood_tt_graph_datalables_data = [];
                    // $mood_tt_graph_datalables_data = 'aggressive,calm,chilled,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting';
                    $genre_tt_graph_dataset_data = [];
                    $genre_tt_graph_datalables_data = [];
                    // $genre_tt_graph_datalables_data = 'ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter';
                    $genre_bg_color_array = config('custom.genre_bg_color_array');
                }

                if($mood_twt_graph_data!='' && count($mood_twt_graph_data) !=0 && $genre_twt_graph_data!='' && count($genre_twt_graph_data) !=0)
                {
                    $mood_twt_graph_dataset_data = [];
                    $mood_twt_graph_datalables_data = [];
                    /* $mood_twt_graph_datalables_data = 'aggressive,calm,chilled,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting';

                    array_push($mood_twt_graph_dataset_data,$mood_twt_graph_data->aggressive);
                    array_push($mood_twt_graph_dataset_data,$mood_twt_graph_data->calm);
                    array_push($mood_twt_graph_dataset_data,$mood_twt_graph_data->chilled);
                    array_push($mood_twt_graph_dataset_data,$mood_twt_graph_data->energetic);
                    array_push($mood_twt_graph_dataset_data,$mood_twt_graph_data->epic);
                    array_push($mood_twt_graph_dataset_data,$mood_twt_graph_data->happy);
                    array_push($mood_twt_graph_dataset_data,$mood_twt_graph_data->romantic);
                    array_push($mood_twt_graph_dataset_data,$mood_twt_graph_data->sad);
                    array_push($mood_twt_graph_dataset_data,$mood_twt_graph_data->scary);
                    array_push($mood_twt_graph_dataset_data,$mood_twt_graph_data->sexy);
                    array_push($mood_twt_graph_dataset_data,$mood_twt_graph_data->ethereal);
                    array_push($mood_twt_graph_dataset_data,$mood_twt_graph_data->uplifting); */

                    foreach($mood_twt_graph_data as $mtwtgddata)
                    {
                        array_push($mood_twt_graph_datalables_data,$mtwtgddata->lbl_name);
                        array_push($mood_twt_graph_dataset_data,round($mtwtgddata->lbl_value,2));
                    }

                    $genre_twt_graph_dataset_data = [];
                    $genre_twt_graph_datalables_data = [];
                    // $genre_twt_graph_datalables_data = 'ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter';
                    $genre_bg_color_array = config('custom.genre_bg_color_array');

                    /* array_push($genre_twt_graph_dataset_data,$genre_twt_graph_data->ambient);
                    array_push($genre_twt_graph_dataset_data,$genre_twt_graph_data->blues);
                    array_push($genre_twt_graph_dataset_data,$genre_twt_graph_data->classical);
                    array_push($genre_twt_graph_dataset_data,$genre_twt_graph_data->country);
                    array_push($genre_twt_graph_dataset_data,$genre_twt_graph_data->electronicDance);
                    array_push($genre_twt_graph_dataset_data,$genre_twt_graph_data->folk);
                    array_push($genre_twt_graph_dataset_data,$genre_twt_graph_data->indieAlternative);
                    array_push($genre_twt_graph_dataset_data,$genre_twt_graph_data->jazz);
                    array_push($genre_twt_graph_dataset_data,$genre_twt_graph_data->latin);
                    array_push($genre_twt_graph_dataset_data,$genre_twt_graph_data->metal);
                    array_push($genre_twt_graph_dataset_data,$genre_twt_graph_data->pop);
                    array_push($genre_twt_graph_dataset_data,$genre_twt_graph_data->punk);
                    array_push($genre_twt_graph_dataset_data,$genre_twt_graph_data->rapHipHop);
                    array_push($genre_twt_graph_dataset_data,$genre_twt_graph_data->reggae);
                    array_push($genre_twt_graph_dataset_data,$genre_twt_graph_data->rnb);
                    array_push($genre_twt_graph_dataset_data,$genre_twt_graph_data->rock);
                    array_push($genre_twt_graph_dataset_data,$genre_twt_graph_data->singerSongwriter); */

                    foreach($genre_twt_graph_data as $gtwtgddata)
                    {
                        array_push($genre_twt_graph_datalables_data,$gtwtgddata->lbl_name);
                        array_push($genre_twt_graph_dataset_data,round($gtwtgddata->lbl_value,2));
                    }
                }
                else
                {
                    $mood_twt_graph_dataset_data = [];
                    $mood_twt_graph_datalables_data = [];
                    // $mood_twt_graph_datalables_data = 'aggressive,calm,chilled,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting';
                    $genre_twt_graph_dataset_data = [];
                    $genre_twt_graph_datalables_data = [];
                    // $genre_twt_graph_datalables_data = 'ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter';
                    $genre_bg_color_array = config('custom.genre_bg_color_array');
                }

                if($mood_aggr_graph_data!='' && count($mood_aggr_graph_data) !=0 && $genre_aggr_graph_data!='' && count($genre_aggr_graph_data) !=0)
                {
                    $mood_aggr_graph_dataset_data = [];
                    $mood_aggr_graph_datalables_data = [];
                    /* $mood_aggr_graph_datalables_data = 'aggressive,calm,chilled,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting';

                    array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->aggressive);
                    array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->calm);
                    array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->chilled);
                    array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->energetic);
                    array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->epic);
                    array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->happy);
                    array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->romantic);
                    array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->sad);
                    array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->scary);
                    array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->sexy);
                    array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->ethereal);
                    array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->uplifting); */

                    foreach($mood_aggr_graph_data as $magddata)
                    {
                        array_push($mood_aggr_graph_datalables_data,$magddata->lbl_name);
                        array_push($mood_aggr_graph_dataset_data,round($magddata->lbl_value,2));
                    }

                    $genre_aggr_graph_dataset_data = [];
                    $genre_aggr_graph_datalables_data = [];
                    // $genre_aggr_graph_datalables_data = 'ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter';
                    $genre_bg_color_array = config('custom.genre_bg_color_array');

                    /* array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->ambient);
                    array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->blues);
                    array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->classical);
                    array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->country);
                    array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->electronicDance);
                    array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->folk);
                    array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->indieAlternative);
                    array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->jazz);
                    array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->latin);
                    array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->metal);
                    array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->pop);
                    array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->punk);
                    array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->rapHipHop);
                    array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->reggae);
                    array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->rnb);
                    array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->rock);
                    array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->singerSongwriter); */

                    foreach($genre_aggr_graph_data as $gagddata)
                    {
                        array_push($genre_aggr_graph_datalables_data,$gagddata->lbl_name);
                        array_push($genre_aggr_graph_dataset_data,round($gagddata->lbl_value,2));
                    }
                }
                else
                {
                    $mood_aggr_graph_dataset_data = [];
                    $mood_aggr_graph_datalables_data = [];
                    // $mood_aggr_graph_datalables_data = 'aggressive,calm,chilled,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting';
                    $genre_aggr_graph_dataset_data = [];
                    $genre_aggr_graph_datalables_data = [];
                    // $genre_aggr_graph_datalables_data = 'ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter';
                    $genre_bg_color_array = config('custom.genre_bg_color_array');
                }

                return ['msg'=>'Data', 'mood_yt_graph_dataset_data'=>$mood_yt_graph_dataset_data, 'mood_yt_graph_datalables_data'=>$mood_yt_graph_datalables_data,'genre_yt_graph_dataset_data'=>$genre_yt_graph_dataset_data, 'genre_yt_graph_datalables_data'=>$genre_yt_graph_datalables_data,'mood_ig_graph_dataset_data'=>$mood_ig_graph_dataset_data, 'mood_ig_graph_datalables_data'=>$mood_ig_graph_datalables_data,'genre_ig_graph_dataset_data'=>$genre_ig_graph_dataset_data, 'genre_ig_graph_datalables_data'=>$genre_ig_graph_datalables_data,'mood_tt_graph_dataset_data'=>$mood_tt_graph_dataset_data, 'mood_tt_graph_datalables_data'=>$mood_tt_graph_datalables_data,'genre_tt_graph_dataset_data'=>$genre_tt_graph_dataset_data, 'genre_tt_graph_datalables_data'=>$genre_tt_graph_datalables_data,'mood_twt_graph_dataset_data'=>$mood_twt_graph_dataset_data, 'mood_twt_graph_datalables_data'=>$mood_twt_graph_datalables_data,'genre_twt_graph_dataset_data'=>$genre_twt_graph_dataset_data, 'genre_twt_graph_datalables_data'=>$genre_twt_graph_datalables_data,'mood_aggr_graph_dataset_data'=>$mood_aggr_graph_dataset_data, 'mood_aggr_graph_datalables_data'=>$mood_aggr_graph_datalables_data,'genre_aggr_graph_dataset_data'=>$genre_aggr_graph_dataset_data, 'genre_aggr_graph_datalables_data'=>$genre_aggr_graph_datalables_data,'genre_bg_color_array'=>$genre_bg_color_array,'name_data'=>$name_data];
            }
            else
            {
                // return 'No Data';
                return ['msg'=>'No Data','name_data'=>$name_data];
            }
        }
        else
        {
            if($type == 'ind')
            {
                $data = DB::table('tbl_industry')->select('industry_name')->where('industry_id', '=', $id)->first();
                $name_data = str_replace(" ","_",$data->industry_name)."_".$year;
                // $mood_aggr_graph_data = DB::table('tbl_industry_mood_aggr_graph_data')->where('ind_id', '=', $id)->where('ind_year', '=', $year)->where('is_active', '=', 0)->first();
                // $genre_aggr_graph_data = DB::table('tbl_industry_genre_aggr_graph_data')->where('ind_id', '=', $id)->where('ind_year', '=', $year)->where('is_active', '=', 0)->first();
                $mood_aggr_graph_data = DB::table('tbl_ind_social_media_aggr_mood_graph_data')
                    ->where('ind_id','=',$id)
                    ->where('year','=',$year)
                    ->where('is_active', '=', 0)
                    ->get();
                // print_r($avg_mood_data);
                // echo "<br>#########################################################<br>";

                $genre_aggr_graph_data = DB::table('tbl_ind_social_media_aggr_genre_graph_data')
                    ->where('ind_id','=',$id)
                    ->where('year','=',$year)
                    ->where('is_active', '=', 0)
                    ->get();
            }
            else if($type == 'sind')
            {
                $data = DB::table('tbl_sub_industry')->select('sub_industry_name')->where('sub_industry_id', '=', $id)->first();
                $name_data = str_replace(" ","_",$data->sub_industry_name)."_".$year;
               /*  $mood_aggr_graph_data = DB::table('tbl_sub_industry_mood_aggr_graph_data')->where('sind_id', '=', $id)->where('sind_year', '=', $year)->where('is_active', '=', 0)->first();
                $genre_aggr_graph_data = DB::table('tbl_sub_industry_genre_aggr_graph_data')->where('sind_id', '=', $id)->where('sind_year', '=', $year)->where('is_active', '=', 0)->first(); */

                $mood_aggr_graph_data = DB::table('tbl_sind_social_media_aggr_mood_graph_data')
                ->where('sind_id','=',$id)
                ->where('year','=',$year)
                ->where('is_active', '=', 0)
                ->get();

                $genre_aggr_graph_data = DB::table('tbl_sind_social_media_aggr_genre_graph_data')
                ->where('sind_id','=',$id)
                ->where('year','=',$year)
                ->where('is_active', '=', 0)
                ->get();

            }
            else
            {
                $data = DB::table('tbl_cvs')->select('cv_name')->where('cv_id', '=', $id)->first();
                $name_data = str_replace(" ","_",$data->cv_name)."_".$year;
                /* $mood_aggr_graph_data = DB::table('tbl_mood_aggr_graph_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->first();
                $genre_aggr_graph_data = DB::table('tbl_genre_aggr_graph_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->first(); */

                // Get Mood and Genre Graph data
                /*$process_type_array = ['youtube', 'instagram', 'tiktok', 'twitter'];
                $avg_asset_id_arr = [];
                foreach($process_type_array as $process_type)
                {
                    // echo "process_type=>".$process_type."<br><br>";
                    $get_asset_data = DB::table('tbl_cvs')
                                        ->join('tbl_social_spyder_graph_meta_data','tbl_social_spyder_graph_meta_data.cv_id','=','tbl_cvs.cv_id')
                                        ->join('tbl_assets','tbl_assets.id','=','tbl_social_spyder_graph_meta_data.asset_id')
                                        ->select('tbl_assets.*')
                                        ->where('tbl_cvs.cv_id', '=', $id)
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
                    }
                }*/

                $mood_aggr_graph_data = DB::table('tbl_social_media_aggr_mood_graph_data')
                ->where('cv_id', '=', $id)
                ->where('is_active', '=', 0)
                ->get();

                $genre_aggr_graph_data = DB::table('tbl_social_media_aggr_genre_graph_data')
                ->where('cv_id', '=', $id)
                ->where('is_active', '=', 0)
                ->get();

            }

            if($mood_aggr_graph_data!='' && count($mood_aggr_graph_data) !=0 && $genre_aggr_graph_data!='' && count($genre_aggr_graph_data) !=0)
            {
                $mood_aggr_graph_dataset_data = [];
                $mood_aggr_graph_datalables_data = [];
                /* $mood_aggr_graph_datalables_data = 'aggressive,calm,chilled,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting';

                array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->aggressive);
                array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->calm);
                array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->chilled);
                array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->energetic);
                array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->epic);
                array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->happy);
                array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->romantic);
                array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->sad);
                array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->scary);
                array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->sexy);
                array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->ethereal);
                array_push($mood_aggr_graph_dataset_data,$mood_aggr_graph_data->uplifting); */

                foreach($mood_aggr_graph_data as $magddata)
                {
                    array_push($mood_aggr_graph_datalables_data,$magddata->lbl_name);
                    array_push($mood_aggr_graph_dataset_data,round($magddata->lbl_value,2));
                }

                $genre_aggr_graph_dataset_data = [];
                $genre_aggr_graph_datalables_data = [];
                // $genre_aggr_graph_datalables_data = 'ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter';
                $genre_bg_color_array = config('custom.genre_bg_color_array');

                /* array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->ambient);
                array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->blues);
                array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->classical);
                array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->country);
                array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->electronicDance);
                array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->folk);
                array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->indieAlternative);
                array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->jazz);
                array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->latin);
                array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->metal);
                array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->pop);
                array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->punk);
                array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->rapHipHop);
                array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->reggae);
                array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->rnb);
                array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->rock);
                array_push($genre_aggr_graph_dataset_data,$genre_aggr_graph_data->singerSongwriter); */

                foreach($genre_aggr_graph_data as $gagddata)
                {
                    array_push($genre_aggr_graph_datalables_data,$gagddata->lbl_name);
                    array_push($genre_aggr_graph_dataset_data,round($gagddata->lbl_value,2));
                }

                return ['msg'=>'Data','mood_aggr_graph_dataset_data'=>$mood_aggr_graph_dataset_data, 'mood_aggr_graph_datalables_data'=>$mood_aggr_graph_datalables_data,'genre_aggr_graph_dataset_data'=>$genre_aggr_graph_dataset_data, 'genre_aggr_graph_datalables_data'=>$genre_aggr_graph_datalables_data,'genre_bg_color_array'=>$genre_bg_color_array,'name_data'=>$name_data];
            }
            else
            {
                return ['msg'=>'No Data','name_data'=>$name_data];
            }
        }
    }




    function getRequestSnapshot(Request $request){
        $cvs_year = DB::table('tbl_cvs')
            ->select('cv_year')
            ->where('status', 1)
            ->where('is_active', 0)
            ->distinct()
            ->orderBy('cv_year', 'desc')
            ->get();
        return view('backend.views.request_cv_list',['cvs_year_data'=>$cvs_year]);
    }

    function getDemo(Request $request){
        $cvs_year = DB::table('tbl_cvs')
            ->select('cv_year')
            ->where('status', 1)
            ->where('is_active', 0)
            ->distinct()
            ->orderBy('cv_year', 'desc')
            ->get();
        return view('backend.views.demo_list',['cvs_year_data'=>$cvs_year]);
    }


    function listRequestSnapshot(Request $request){
        if ($request->ajax()) {
            $data = DB::table('tbl_request_cv')
            ->join('tbl_users', 'tbl_request_cv.request_uid', '=', 'tbl_users.uid')
            ->select('tbl_request_cv.*','tbl_users.email')
            ->where('tbl_request_cv.status', 0)
            ->orderBy('rs_id', 'desc')->get();
                return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('year', function($data){
                        if($data->year ==""){
                            $year =  "-";
                        }else{
                            $year =  $data->year;
                        }
                        return $year;
                    })
                    ->addColumn('market', function($data){
                        if($data->market ==""){
                            $market =  "-";
                        }else{
                            $market =  $data->market;
                        }
                        return $market;
                    })
                    ->addColumn('request_uid', function($data){
                        return $data->email;
                    })
                    ->setRowId(function($data){
                        return $data->rs_id;
                    })
                    ->make(true);
        }
    }
    function listGetdemo(Request $request){
        if ($request->ajax()) {
            $data = DB::table('tbl_cvs')
            ->join('tbl_industry', 'tbl_cvs.industry_id', '=', 'tbl_industry.industry_id')
            ->leftjoin('tbl_sub_industry', 'tbl_cvs.sub_industry_id', '=', 'tbl_sub_industry.sub_industry_id')
            ->select('tbl_cvs.*','tbl_industry.industry_name','tbl_sub_industry.sub_industry_name')
            ->orderBy('cv_id', 'desc')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                 ->addColumn('cv_date', function ($data) {
                    $year = explode("-",$data->cv_date)[1];
                    $month = explode("-",$data->cv_date)[0];
                    return $year."-".$month;
                })
                ->setRowId(function($data){
                    return $data->cv_id;
                })
                ->make(true);
        }
    }
}
