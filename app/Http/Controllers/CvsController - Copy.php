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

        return $b15_sum_data_array;
    }

    public function getMusicExpenditurePerYearAvgData($industry_id)
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

        $b14_sum_data_array = [];
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

        return $b14_sum_data_array;
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
        $cv_block_2_data = DB::table('tbl_cv_block_2_data')->where('cv_id', '=', $id)->where('is_active', '=', 0)->first();
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
        array_push($data,$cv_data,$cv_block_2_data,$cv_block_3_data,$cv_block_4_data,$cv_block_5_data,$cv_block_6_data,$cv_block_7_data,$cv_block_8_data,$cv_block_9_data,$cv_block_10_data,$cv_block_11_data,$cv_block_12_data,$cv_block_13_data,$cv_block_14_data,$cv_block_15_data,$music_teaste_data);
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
        $music_taste_data = DB::table('tbl_music_taste')->where('is_active', '=', 0)->get();
        $experience_data = DB::table('tbl_experience')->where('is_active', '=', 0)->get();
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
        
        if(base64_decode($type) == 'brand')
        {
            //return view('backend.views.add_brand_cv', ['cv_data'=>$cv_data, 'industry_data'=>$industry_data, 'music_taste_data'=>$music_taste_data, 'experience_data'=>$experience_data, 'qualitative_data'=>$qualitative_data, 'social_media_icon_data'=>$social_media_icon_data]);
            return view('backend.views.add_brand_cv', ['cv_data'=>$cv_data, 'cv_parent_ids_array'=>$cv_parent_ids_array, 'industry_data'=>$industry_data, 'music_taste_data'=>$music_taste_data, 'experience_data'=>$experience_data, 'qualitative_data'=>$qualitative_data, 'footer_template_data'=>$footer_template_data]);
        }
        else
        {
            //return view('backend.views.add_industry_cv', ['cv_data'=>$cv_data, 'industry_data'=>$industry_data, 'music_taste_data'=>$music_taste_data, 'experience_data'=>$experience_data, 'qualitative_data'=>$qualitative_data, 'social_media_icon_data'=>$social_media_icon_data]);
            return view('backend.views.add_industry_cv', ['cv_data'=>$cv_data, 'cv_parent_ids_array'=>$cv_parent_ids_array, 'industry_data'=>$industry_data, 'music_taste_data'=>$music_taste_data, 'experience_data'=>$experience_data, 'qualitative_data'=>$qualitative_data, 'footer_template_data'=>$footer_template_data]);
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
        if($request->parent_cv_name != '' && $request->parent_cv_name != '0#_#sel')
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
        'industry_id' => $request->industry_name,
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
            return back()->with('fail', 'Something went wrong while inserting cv data, please try again!');
        }
        //echo $id;
        if ($id == 0 || $id == '')
        {
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

                if($image->move(public_path('images/cv_logos/original'), $img_name))
                {
                    
                    if(DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_logo' => $img_name]))
                    {

                    }
                    else
                    {
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

            if($request->cv_banner_desktop != '')
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
                        return back()->with('fail', 'Something went wrong while inserting cv data, please try again!');
                    } 
                }
            }

            if($request->cv_banner_ipad != '')
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
                        return back()->with('fail', 'Something went wrong while inserting cv data, please try again!');
                    } 
                }
            }

            if($request->cv_banner_mobile != '')
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
                        return back()->with('fail', 'Something went wrong while inserting cv data, please try again!');
                    } 
                }
            }

            if($request->section_2_title !='' && $request->ranking !='')
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
            }

            if($request->cv_music_taste_name_ids !='')
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
            }

            if($request->section_4_title !='' && $request->about_description !='')
            {
                $block_4_data = [
                    'b4_title' => $request->section_4_title,
                    'b4_description' => $request->about_description,
                    'cv_id' => $id,
                    'created_by' => session('LoggedUser')
                ];
                //DB::table('tbl_cv_block_4_data')->updateOrInsert($block_4_data);
                if(DB::table('tbl_cv_block_4_data')->insertOrIgnore($block_4_data))
                {

                }
                else
                {
                    return back()->with('fail', 'Something went wrong while inserting section 4 data, please try again!');
                }
            }


            if($request->smDataCount !='0' && $request->smDataCount !='')
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
                        return back()->with('fail', 'Something went wrong while inserting section 5 data, please try again!');
                    }
                }
            }

            if($request->section_6_title !='' && $request->sonic_logo_audio_file !='')
            {
                /* $request->validate([
                    "sonic_logo_audio_file"=>'mimes:mp3,wav,wma,aac,m4a,ogg'
                ]); */
                $file = $request->sonic_logo_audio_file;
                $file_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$id, 'UTF-8').'.'.$file->getClientOriginalExtension());
                $file->move(public_path('audios/cv_audios'), $file_name);
                $block_6_data = [
                    'b6_title' => $request->section_6_title,
                    'b6_name' => $file_name,
                    'cv_id' => $id,
                    'created_by' => session('LoggedUser')
                ];
                
                //DB::table('tbl_cv_block_6_data')->updateOrInsert($block_6_data);
                if(DB::table('tbl_cv_block_6_data')->insertOrIgnore($block_6_data))
                {

                }
                else
                {
                    return back()->with('fail', 'Something went wrong while inserting section 6 data, please try again!');
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
                            return back()->with('fail', 'Something went wrong while inserting section 7 data, please try again!');
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

                    if($video_title != '' && $video_link !='')
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
                            return back()->with('fail', 'Something went wrong while inserting section 9 data, please try again!');
                        }
                    }
                }
            }

            if($request->section_10_title !='' && $request->a_day_in_my_life_count !='')
            {

                /* if($request->section_10_bg_image != '')
                {
                    $image = $request->section_10_bg_image;
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

                    if($image->move(public_path('images/section_10_bg_images'), $img_name))
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
                $a_day_in_my_life_count = $request->a_day_in_my_life_count;
                for($i=0; $i<$a_day_in_my_life_count; $i++)
                {
                    $name_id = "cv_a_day_in_my_life_name_id_".$i;
                    $number = "cv_a_day_in_my_life_number_".$i;
                    $color = "cv_a_day_in_my_life_color_".$i;

                    if($name_id != '' && $number !='')
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
            }

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

            if($request->smsDataCount !='0' && $request->smsDataCount !='')
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
            }

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

                    if($efb_name_id != '' && $efb_number !='')
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

                    //if($mepy_number != '' || $mepy_description !='')
                    if($mepy_number != '')
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

                    //if($mepv_number != '' || $mepv_description !='')
                    if($mepv_number != '')
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
                            'chn_name' => $request->$ychn_name,
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

                        $request_json = '{ "process_type" : "youtube", "name": "'.$request->$ychn_name.'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$id.'","chn_id":"y_'.$chn_id.'","c_date":"'.$c_date.'" }';

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
                            'chn_name' => $request->$ichn_name,
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

                        $request_json = '{ "process_type" : "instagram", "name": "'.$request->$ichn_name.'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$id.'","chn_id":"i_'.$chn_id.'","c_date":"'.$c_date.'" }';

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
                            'chn_name' => $request->$tchn_name,
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

                        $request_json = '{ "process_type" : "tiktok", "name": "'.$request->$tchn_name.'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$id.'","chn_id":"t_'.$chn_id.'","c_date":"'.$c_date.'" }';

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
                            return back()->with('fail', 'Something went wrong while inserting section 18 data, please try again!');
                        }
                    }
                } 
            }
            
            return redirect('brand-cvs')->with('success','Brand Sonic Radar data inserted successfully');
        }
    }

    public function listBrandCvs()
    {
        return view('backend.views.brand_cv_list');
    }

    function getBrandCvs(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('tbl_cvs')->orderBy('cv_id', 'desc')->get();
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
                            //$actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here eidt" class="edit btn btn-success btn-sm">Edit</a> <a href="disable-cv/'.base64_encode($data->cv_id).'" title="Click here to disable brand" class="delete btn btn-success btn-sm">Disable</a> <a href="duplicate-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to duplicate" class="edit btn btn-success btn-sm">Duplicate</a>';
                            $actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to eidt" class="edit btn btn-success btn-sm">Edit</a> <a href="disable-cv/'.base64_encode($data->cv_id).'" title="Click here to disable brand" class="delete btn btn-success btn-sm">Disable</a> <span class="btn btn-success btn-sm" onclick=previewCV("'.$data->cv_id.'","publish")>Preview & Publish</span> <a href="duplicate-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to duplicate" class="edit btn btn-success btn-sm">Duplicate</a>';
                        }
                        else
                        {
                            //$actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here eidt" class="edit btn btn-success btn-sm">Edit</a> <a href="disable-cv/'.base64_encode($data->cv_id).'" title="Click here to disable brand" class="delete btn btn-success btn-sm">Disable</a> <a href="duplicate-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to duplicate" class="edit btn btn-success btn-sm">Duplicate</a>';
                            $actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to eidt" class="edit btn btn-success btn-sm">Edit</a> <a href="disable-cv/'.base64_encode($data->cv_id).'" title="Click here to disable brand" class="delete btn btn-success btn-sm">Disable</a> <span class="btn btn-success btn-sm" onclick=cvPublishUnpublishConfirmationModal("'.$data->cv_id.'","unpublish")>Unpublish</span> <a href="duplicate-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to duplicate" class="edit btn btn-success btn-sm">Duplicate</a>';
                        }
                    }
                    else
                    {
                        //$actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here eidt" class="edit btn btn-success btn-sm">Edit</a> <a href="enable-cv/'.base64_encode($data->cv_id).'" title="Click here to enable brand" class="edit btn btn-success btn-sm">Enable</a>';
                        $actionBtn = '<a href="enable-cv/'.base64_encode($data->cv_id).'" title="Click here to enable brand" class="edit btn btn-success btn-sm">Enable</a> <a href="duplicate-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to duplicate" class="edit btn btn-success btn-sm">Duplicate</a>';
                        /* if($data->status == '0')
                        {
                            //$actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here eidt" class="edit btn btn-success btn-sm">Edit</a> <a href="enable-cv/'.base64_encode($data->cv_id).'" title="Click here to enable brand" class="edit btn btn-success btn-sm">Enable</a> <a href="duplicate-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to duplicate" class="edit btn btn-success btn-sm">Duplicate</a>';
                            $actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here eidt" class="edit btn btn-success btn-sm">Edit</a> <a href="enable-cv/'.base64_encode($data->cv_id).'" title="Click here to enable brand" class="edit btn btn-success btn-sm">Enable</a><span onclick=cvPublishUnpublishConfirmationModal("'.$data->cv_id.'","publish")>Publish</span>';
                        }
                        else
                        {
                            //$actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here eidt" class="edit btn btn-success btn-sm">Edit</a> <a href="enable-cv/'.base64_encode($data->cv_id).'" title="Click here to enable brand" class="edit btn btn-success btn-sm">Enable</a> <a href="duplicate-brand-cv/'.base64_encode($data->cv_id).'" title="Click here to duplicate" class="edit btn btn-success btn-sm">Duplicate</a>';
                            $actionBtn = '<a href="edit-brand-cv/'.base64_encode($data->cv_id).'" title="Click here eidt" class="edit btn btn-success btn-sm">Edit</a> <a href="enable-cv/'.base64_encode($data->cv_id).'" title="Click here to enable brand" class="edit btn btn-success btn-sm">Enable</a><span onclick=cvPublishUnpublishConfirmationModal("'.$data->cv_id.'","unpublish")>Publish</span>';
                        } */
                    }
                    return $actionBtn;
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
            return back()->with('fail', 'Something went wrong, please try again!');
        }


    }

    function editBrandCv($id)
    {
        //echo 'edit brand cv section';
        $cv_data = DB::table('tbl_cvs')->where('cv_id', '=', base64_decode($id))->first();
        $cv_parent_data = DB::table('tbl_cvs')->where('is_active', '=', 0)->get();
        $cv_parent_ids = DB::table('tbl_cvs')->where('parent_id', '!=', null)->where('parent_id', '!=', '')->where('is_active', '=', 0)->get();
        $industry_data = DB::table('tbl_industry')->where('is_active', '=', 0)->get();
        $music_taste_data = DB::table('tbl_music_taste')->where('is_active', '=', 0)->get();
        $experience_data = DB::table('tbl_experience')->where('is_active', '=', 0)->get();
        $qualitative_data = DB::table('tbl_qualitative')->where('is_active', '=', 0)->get();
        $footer_template_data = DB::table('tbl_footer_template')->where('is_active', '=', 0)->get();
        $cv_block_2_data = DB::table('tbl_cv_block_2_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->first();
        $cv_block_3_data = DB::table('tbl_cv_block_3_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_4_data = DB::table('tbl_cv_block_4_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->first();
        $cv_block_5_data = DB::table('tbl_cv_block_5_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_6_data = DB::table('tbl_cv_block_6_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->first();
        $cv_block_7_data = DB::table('tbl_cv_block_7_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_8_data = DB::table('tbl_cv_block_8_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_9_data = DB::table('tbl_cv_block_9_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_10_data = DB::table('tbl_cv_block_10_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_11_data = DB::table('tbl_cv_block_11_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_12_data = DB::table('tbl_cv_block_12_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_13_data = DB::table('tbl_cv_block_13_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_14_data = DB::table('tbl_cv_block_14_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_15_data = DB::table('tbl_cv_block_15_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_16_data = DB::table('tbl_cv_block_16_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_17_data = DB::table('tbl_cv_block_17_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_18_data = DB::table('tbl_cv_block_18_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        if(count($cv_block_3_data)==0)
        {
            $cv_block_3_data = '';
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
        //print_r($cv_block_8_data);exit;
        return view('backend.views.edit_brand_cv', ['cv_data'=>$cv_data, 'cv_parent_data'=>$cv_parent_data, 'cv_parent_ids_array'=>$cv_parent_ids_array, 'industry_data'=>$industry_data, 'music_taste_data'=>$music_taste_data, 'experience_data'=>$experience_data, 'qualitative_data'=>$qualitative_data, 'footer_template_data'=>$footer_template_data, 'cv_block_2_data'=>$cv_block_2_data, 'cv_block_3_data'=>$cv_block_3_data, 'cv_block_4_data'=>$cv_block_4_data, 'cv_block_5_data'=>$cv_block_5_data, 'cv_block_6_data'=>$cv_block_6_data, 'cv_block_7_data'=>$cv_block_7_data, 'cv_block_8_data'=>$cv_block_8_data, 'cv_block_9_data'=>$cv_block_9_data, 'cv_block_10_data'=>$cv_block_10_data, 'cv_block_11_data'=>$cv_block_11_data, 'cv_block_12_data'=>$cv_block_12_data, 'cv_block_13_data'=>$cv_block_13_data, 'cv_block_14_data'=>$cv_block_14_data, 'cv_block_15_data'=>$cv_block_15_data, 'cv_block_16_data'=>$cv_block_16_data, 'cv_block_17_data'=>$cv_block_17_data, 'cv_block_18_data'=>$cv_block_18_data]);
        //return view('backend.views.edit_brand_cv', ['cv_data'=>$cv_data, 'cv_parent_data'=>$cv_parent_data, 'cv_parent_ids_array'=>$cv_parent_ids_array, 'industry_data'=>$industry_data, 'music_taste_data'=>$music_taste_data, 'experience_data'=>$experience_data, 'qualitative_data'=>$qualitative_data, 'footer_template_data'=>$footer_template_data, 'cv_block_2_data'=>$cv_block_2_data, 'cv_block_3_data'=>$cv_block_3_data, 'cv_block_4_data'=>$cv_block_4_data, 'cv_block_5_data'=>$cv_block_5_data, 'cv_block_6_data'=>$cv_block_6_data, 'cv_block_7_data'=>$cv_block_7_data, 'cv_block_9_data'=>$cv_block_9_data, 'cv_block_10_data'=>$cv_block_10_data, 'cv_block_11_data'=>$cv_block_11_data, 'cv_block_12_data'=>$cv_block_12_data, 'cv_block_13_data'=>$cv_block_13_data, 'cv_block_14_data'=>$cv_block_14_data, 'cv_block_15_data'=>$cv_block_15_data]);
    }

    function updateBrandCv(Request $request)
    {
        //return $request->input();

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

        if($request->parent_cv_name != '' && $request->parent_cv_name != '0#_#sel')
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

            if($image->move(public_path('images/cv_logos/original'), $img_name))
            {
                DB::table('tbl_cvs')->where('cv_id', $request->cv_id)->update(['cv_logo' => '']);
                $update_query =  DB::table('tbl_cvs')
                            ->where('cv_id', $request->cv_id)
                            ->update(['type' => $request->cv_type,'parent_id' => $parent_id,'parent' => $parent_name,'cv_name' => $request->cv_name,'cv_logo' => $img_name,'cv_date' => $request->cv_date,'industry_id'=>$industry_name_id,'footer_template_id'=>$footer_template_name_id, 'edited_by'=>session('LoggedUser'), 'md_flag'=>$missing_data_flag_name]); 
            }
        }
        else
        {
            DB::table('tbl_cvs')->where('cv_id', $request->cv_id)->update(['is_active' => '1']);
            $update_query =  DB::table('tbl_cvs')
                            ->where('cv_id', $request->cv_id)
                            ->update(['type' => $request->cv_type,'parent_id' => $parent_id,'parent' => $parent_name,'cv_name' => $request->cv_name,'cv_date' => $request->cv_date,'industry_id'=>$industry_name_id,'footer_template_id'=>$footer_template_name_id,'is_active' => '0', 'edited_by'=>session('LoggedUser'), 'md_flag'=>$missing_data_flag_name]);
        }

        if($request->hasfile('cv_banner_desktop'))
        {
            //$request->validate(["cv_banner_desktop"=>'image|mimes:jpeg,png,jpg|max:2048']);
            
            $image = $request->cv_banner_desktop;
            $img_name = str_replace(" ","-",mb_strtolower($request->cv_type."_".Str::substr($request->cv_name,0,4)."_".$request->cv_id, 'UTF-8').'.'.$image->getClientOriginalExtension());

            if($image->move(public_path('images/cv_banners/desktop'), $img_name))
            {
                DB::table('tbl_cvs')->where('cv_id', $request->cv_id)->update(['cv_banner_desktop' => '']);
                $update_query =  DB::table('tbl_cvs')
                            ->where('cv_id', $request->cv_id)
                            ->update(['type' => $request->cv_type,'parent_id' => $parent_id,'parent' => $parent_name,'cv_name' => $request->cv_name,'cv_banner_desktop' => $img_name,'cv_date' => $request->cv_date,'industry_id'=>$industry_name_id,'footer_template_id'=>$footer_template_name_id, 'edited_by'=>session('LoggedUser'), 'md_flag'=>$missing_data_flag_name]); 
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
                            ->update(['type' => $request->cv_type,'parent_id' => $parent_id,'parent' => $parent_name,'cv_name' => $request->cv_name,'cv_banner_ipad' => $img_name,'cv_date' => $request->cv_date,'industry_id'=>$industry_name_id,'footer_template_id'=>$footer_template_name_id, 'edited_by'=>session('LoggedUser'), 'md_flag'=>$missing_data_flag_name]); 
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
                            ->update(['type' => $request->cv_type,'parent_id' => $parent_id,'parent' => $parent_name,'cv_name' => $request->cv_name,'cv_banner_mobile' => $img_name,'cv_date' => $request->cv_date,'industry_id'=>$industry_name_id,'footer_template_id'=>$footer_template_name_id, 'edited_by'=>session('LoggedUser'), 'md_flag'=>$missing_data_flag_name]); 
            }
        }
        
        if($update_query)
        {
            if($request->section_2_title !='' && $request->ranking !='')
            {
                $block_2_data = [
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
                }
            }            

            DB::table('tbl_cv_block_3_data')->where('cv_id', $request->cv_id)->update(['is_active' => 1,'edited_by' => session('LoggedUser')]);
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

                $block_4_data = [
                    'b4_id' => $request->section_4_id,
                    'b4_title' => $request->section_4_title,
                    'b4_description' => $request->about_description,
                    'cv_id' =>  $request->cv_id,
                    'created_by' => session('LoggedUser'),
                    'edited_by' => session('LoggedUser')
                ];
                
                try
                { 
                    DB::table('tbl_cv_block_4_data')->upsert($block_4_data, ['b4_id','cv_id'], ['b4_title','b4_description','cv_id','created_by','edited_by']);
                    //return redirect('brand-cvs')->with('success','Brand CV data updated successfully');
                }
                catch(\Illuminate\Database\QueryException $ex)
                { 
                    //return ['error' => 'error update user']; 
                    return back()->with('fail', 'Something went wrong while updating section 4 data, please try again!');
                }
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
                    return back()->with('fail', 'Something went wrong while updating section 5 data, please try again!');
                }
            }

            if($request->hasfile('sonic_logo_audio_file'))
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
                    return back()->with('fail', 'Something went wrong while updating section 7 data, please try again!');
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
                        return back()->with('fail', 'Something went wrong while updating section 9 data, please try again!');
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
                        return back()->with('fail', 'Something went wrong while updating section 9 data, please try again!');
                    }
                } 
            }

            $a_day_in_my_life_count = $request->a_day_in_my_life_count;
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
            }


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

            
            $imgArray = GetSocialMediaIconsData::getSMData();
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
            }


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
                        return back()->with('fail', 'Something went wrong while updating section 13 data, please try again!');
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
                        return back()->with('fail', 'Something went wrong while updating section 13 data, please try again!');
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
                        return back()->with('fail', 'Something went wrong while updating section 14 data, please try again!');
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
                            return back()->with('fail', 'Something went wrong while updating section 14 data, please try again!');
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
                        return back()->with('fail', 'Something went wrong while updating section 15 data, please try again!');
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
                            return back()->with('fail', 'Something went wrong while updating section 15 data, please try again!');
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
                        'chn_name' => $request->$ychn_name,
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
                        'chn_name' => $request->$ychn_name,
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
                                
                                $request_json = '{ "process_type" : "youtube", "name": "'.$request->$ychn_name.'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$request->cv_id.'","chn_id":"y_'.$request->$ychn_id.'","c_date":"'.$c_date.'" }';
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
                            return back()->with('fail', 'Something went wrong while updating section 9 data, please try again!');
                        }
                    }
                    else
                    {
                        try
                        {
                            $chn_id = DB::table('tbl_cv_block_16_data')->insertGetId($block_16_insert_data);

                            $request_json = '{ "process_type" : "youtube", "name": "'.$request->$ychn_name.'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$request->cv_id.'","chn_id":"y_'.$chn_id.'","c_date":"'.$c_date.'" }';
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
                            return back()->with('fail', 'Something went wrong while updating section 9 data, please try again!');
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
                        'chn_name' => $request->$ichn_name,
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
                        'chn_name' => $request->$ichn_name,
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
                                
                                $request_json = '{ "process_type" : "instagram", "name": "'.$request->$ichn_name.'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$request->cv_id.'","chn_id":"i_'.$request->$ichn_id.'","c_date":"'.$c_date.'" }';
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

                            $request_json = '{ "process_type" : "instagram", "name": "'.$request->$ichn_name.'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$request->cv_id.'","chn_id":"i_'.$chn_id.'","c_date":"'.$c_date.'" }';
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
                        'chn_name' => $request->$tchn_name,
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
                        'chn_name' => $request->$tchn_name,
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
                                
                                $request_json = '{ "process_type" : "tiktok", "name": "'.$request->$tchn_name.'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$request->cv_id.'","chn_id":"t_'.$request->$tchn_id.'","c_date":"'.$c_date.'" }';
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
                            return back()->with('fail', 'Something went wrong while updating section 18 data, please try again!');
                        }
                    }
                    else
                    {
                        try
                        {
                            $chn_id = DB::table('tbl_cv_block_18_data')->insertGetId($block_18_insert_data);

                            $request_json = '{ "process_type" : "tiktok", "name": "'.$request->$tchn_name.'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$request->cv_id.'","chn_id":"t_'.$chn_id.'","c_date":"'.$c_date.'" }';
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
                            return back()->with('fail', 'Something went wrong while updating section 18 data, please try again!');
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

    function publishCV(Request $request)
    {
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
                return redirect('brand-cvs')->with('success','Brand Sonic Radar published successfully');
            }
            else
            {
                return redirect('brand-cvs')->with('success','Brand Sonic Radar unpublished successfully');
            }
        }
        else
        {
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
            return back()->with('fail', 'Something went wrong while publishing Brand Sonic Radar, please try again!');
        }
    }

    function duplicateBrandCv($id)
    {
        //echo 'edit brand cv section';
        $cv_data = DB::table('tbl_cvs')->where('cv_id', '=', base64_decode($id))->first();
        $cv_parent_data = DB::table('tbl_cvs')->where('is_active', '=', 0)->get();
        $cv_parent_ids = DB::table('tbl_cvs')->where('parent_id', '!=', null)->where('parent_id', '!=', '')->where('is_active', '=', 0)->get();
        $industry_data = DB::table('tbl_industry')->where('is_active', '=', 0)->get();
        $music_taste_data = DB::table('tbl_music_taste')->where('is_active', '=', 0)->get();
        $experience_data = DB::table('tbl_experience')->where('is_active', '=', 0)->get();
        $qualitative_data = DB::table('tbl_qualitative')->where('is_active', '=', 0)->get();
        $footer_template_data = DB::table('tbl_footer_template')->where('is_active', '=', 0)->get();
        $cv_block_2_data = DB::table('tbl_cv_block_2_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->first();
        $cv_block_3_data = DB::table('tbl_cv_block_3_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_4_data = DB::table('tbl_cv_block_4_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->first();
        $cv_block_5_data = DB::table('tbl_cv_block_5_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_6_data = DB::table('tbl_cv_block_6_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->first();
        $cv_block_7_data = DB::table('tbl_cv_block_7_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_8_data = DB::table('tbl_cv_block_8_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_9_data = DB::table('tbl_cv_block_9_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_10_data = DB::table('tbl_cv_block_10_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_11_data = DB::table('tbl_cv_block_11_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_12_data = DB::table('tbl_cv_block_12_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_13_data = DB::table('tbl_cv_block_13_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_14_data = DB::table('tbl_cv_block_14_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_15_data = DB::table('tbl_cv_block_15_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_16_data = DB::table('tbl_cv_block_16_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_17_data = DB::table('tbl_cv_block_17_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        $cv_block_18_data = DB::table('tbl_cv_block_18_data')->where('cv_id', '=', base64_decode($id))->where('is_active', '=', 0)->get();
        if(count($cv_block_3_data)==0)
        {
            $cv_block_3_data = '';
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
        //print_r($cv_block_8_data);exit;
        return view('backend.views.duplicate_brand_cv', ['cv_data'=>$cv_data, 'cv_parent_data'=>$cv_parent_data, 'cv_parent_ids_array'=>$cv_parent_ids_array, 'industry_data'=>$industry_data, 'music_taste_data'=>$music_taste_data, 'experience_data'=>$experience_data, 'qualitative_data'=>$qualitative_data, 'footer_template_data'=>$footer_template_data, 'cv_block_2_data'=>$cv_block_2_data, 'cv_block_3_data'=>$cv_block_3_data, 'cv_block_4_data'=>$cv_block_4_data, 'cv_block_5_data'=>$cv_block_5_data, 'cv_block_6_data'=>$cv_block_6_data, 'cv_block_7_data'=>$cv_block_7_data, 'cv_block_8_data'=>$cv_block_8_data, 'cv_block_9_data'=>$cv_block_9_data, 'cv_block_10_data'=>$cv_block_10_data, 'cv_block_11_data'=>$cv_block_11_data, 'cv_block_12_data'=>$cv_block_12_data, 'cv_block_13_data'=>$cv_block_13_data, 'cv_block_14_data'=>$cv_block_14_data, 'cv_block_15_data'=>$cv_block_15_data, 'cv_block_16_data'=>$cv_block_16_data, 'cv_block_17_data'=>$cv_block_17_data, 'cv_block_18_data'=>$cv_block_18_data]);
        //return view('backend.views.edit_brand_cv', ['cv_data'=>$cv_data, 'cv_parent_data'=>$cv_parent_data, 'cv_parent_ids_array'=>$cv_parent_ids_array, 'industry_data'=>$industry_data, 'music_taste_data'=>$music_taste_data, 'experience_data'=>$experience_data, 'qualitative_data'=>$qualitative_data, 'footer_template_data'=>$footer_template_data, 'cv_block_2_data'=>$cv_block_2_data, 'cv_block_3_data'=>$cv_block_3_data, 'cv_block_4_data'=>$cv_block_4_data, 'cv_block_5_data'=>$cv_block_5_data, 'cv_block_6_data'=>$cv_block_6_data, 'cv_block_7_data'=>$cv_block_7_data, 'cv_block_9_data'=>$cv_block_9_data, 'cv_block_10_data'=>$cv_block_10_data, 'cv_block_11_data'=>$cv_block_11_data, 'cv_block_12_data'=>$cv_block_12_data, 'cv_block_13_data'=>$cv_block_13_data, 'cv_block_14_data'=>$cv_block_14_data, 'cv_block_15_data'=>$cv_block_15_data]);
    }

    function saveDuplicateBrandCv(Request $request)
    {
        //$social_media_data = GetSocialMediaIconsData::index();
        //print_r($social_media_data);
        
        $request->validate([
            "cv_type"=>'required',
            "cv_name"=>'required',
            "cv_date"=>'required',
            "industry_name"=>'required|not_in:0'
        ]);
        // echo $request->cv_logo;
        // return $request->input();
        // exit;
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
        'industry_id' => $request->industry_name,
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
            return back()->with('fail', 'Something went wrong while inserting cv data, please try again!');
        }
        //echo $id;
        if ($id == 0 || $id == '')
        {
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

                if($image->move(public_path('images/cv_logos/original'), $img_name))
                {
                    DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_logo' => '']);
                    DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_logo' => $img_name, 'edited_by'=>session('LoggedUser')]); 
                }
            }
            else
            {
                // DB::table('tbl_cvs')->where('cv_id', $id)->update(['is_active' => '1']);
                $currnt_cv_logo = DB::table('tbl_cvs')->where('cv_id', $request->cv_id)->first();
                if($currnt_cv_logo != '')
                {
                    DB::table('tbl_cvs')->where('cv_id', $id)->update(['cv_logo' => $currnt_cv_logo->cv_logo, 'is_active' => '0', 'edited_by'=>session('LoggedUser')]);
                }
            }

            if($request->hasfile('cv_banner_desktop'))
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
            }

            if($request->section_2_title !='' || $request->ranking !='')
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
            }

            if($request->cv_music_taste_name_ids !='')
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
            }

            if($request->section_4_title !='' || $request->about_description !='')
            {
                $block_4_data = [
                    'b4_title' => $request->section_4_title,
                    'b4_description' => $request->about_description,
                    'cv_id' => $id,
                    'created_by' => session('LoggedUser')
                ];
                //DB::table('tbl_cv_block_4_data')->updateOrInsert($block_4_data);
                if(DB::table('tbl_cv_block_4_data')->insertOrIgnore($block_4_data))
                {

                }
                else
                {
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

            if($request->hasfile('sonic_logo_audio_file'))
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
                            return back()->with('fail', 'Something went wrong while inserting section 9 data, please try again!');
                        }
                    }
                }
            }

            if($request->section_10_title !='' && $request->a_day_in_my_life_count !='')
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
            }            

            if($request->smsDataCount !='0' || $request->smsDataCount !='')
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
            }

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
                            'chn_name' => $request->$ychn_name,
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

                        $request_json = '{ "process_type" : "youtube", "name": "'.$request->$ychn_name.'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$id.'","chn_id":"y_'.$chn_id.'","c_date":"'.$c_date.'" }';

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
                            'chn_name' => $request->$ichn_name,
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

                        $request_json = '{ "process_type" : "instagram", "name": "'.$request->$ichn_name.'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$id.'","chn_id":"i_'.$chn_id.'","c_date":"'.$c_date.'" }';

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
                            'chn_name' => $request->$tchn_name,
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

                        $request_json = '{ "process_type" : "tiktok", "name": "'.$request->$tchn_name.'","crate_name":"'.str_replace(" ","_",$request->cv_name)."_".explode("-",$request->cv_date)[1].'","crate_id":"'.$crate_id.'","start_date":"'.$chn_start_date.'","end_date":"'.$chn_end_date.'","brand_id":"'.$id.'","chn_id":"t_'.$chn_id.'","c_date":"'.$c_date.'" }';

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
        return view('backend.views.social_media_youtube_sync_list');
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
                            $genre_status_qry = DB::table('tbl_cv_block_16_genre_graph_data')->where('cv_id', '=', $data->cv_id)->where('is_Active','=',0)->get();
                            $mood_status_qry = DB::table('tbl_cv_block_16_mood_graph_data')->where('cv_id', '=', $data->cv_id)->where('is_Active','=',0)->get();
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

    public function listInstagramSyncCvs()
    {
        return view('backend.views.social_media_instagram_sync_list');
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
                            $genre_status_qry = DB::table('tbl_cv_block_17_genre_graph_data')->where('cv_id', '=', $data->cv_id)->where('is_Active','=',0)->get();
                            $mood_status_qry = DB::table('tbl_cv_block_17_mood_graph_data')->where('cv_id', '=', $data->cv_id)->where('is_Active','=',0)->get();
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
        return view('backend.views.social_media_tiktok_sync_list');
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
                            $genre_status_qry = DB::table('tbl_cv_block_18_genre_graph_data')->where('cv_id', '=', $data->cv_id)->where('is_Active','=',0)->get();
                            $mood_status_qry = DB::table('tbl_cv_block_18_mood_graph_data')->where('cv_id', '=', $data->cv_id)->where('is_Active','=',0)->get();
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
    
}
