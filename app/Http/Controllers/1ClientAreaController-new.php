<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
/* use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use App\Helpers\GetSocialMediaIconsData;
use Illuminate\Support\Facades\URL;
use Intervention\Image\Facades\Image; */
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\ShareCvEmail;
use App\Mail\DeleteAccountRequestEmailToAdmin;
use Illuminate\Support\Arr;

class ClientAreaController extends Controller
{
    function index()
    {
        //$recently_added_cv = DB::table('tbl_cvs')->distinct()->where('status', '=', 1)->where('is_active', '=', 0)->orderBy('updated_at', 'DESC')->get();
        $recently_added_cv = DB::table('tbl_cvs')->where('status', '=', 1)->where('is_active', '=', 0)->where('parent', Null)->where('parent_id', Null)->orderBy('updated_at', 'DESC')->limit(30)->get();
        if(count($recently_added_cv) == 0)
        {
            $recently_added_cv = '';
        }
        /* else
        {
            foreach($recently_added_cv as $cv_data)
            {
                if($cv_data->cv_name!='' && $cv_data->cv_name!=null)
                {
                    array_push($cv_name_array,$cv_data->cv_id."$#$".$cv_data->cv_name);
                }
            }
        } */
        $totatl_published_cvs = DB::table('tbl_cvs')->where('status', '=', 1)->where('is_active', '=', 0)->count();

        $get_fav_music_genre = DB::select(DB::raw("SELECT tbl_cv_block_3_data.b3_title_id, COUNT(tbl_cv_block_3_data.b3_title_id) as id_count, tbl_music_taste.music_taste_name FROM tbl_cv_block_3_data join tbl_music_taste on tbl_cv_block_3_data.b3_title_id = tbl_music_taste.music_taste_id where tbl_cv_block_3_data.is_active=0 GROUP BY tbl_music_taste.music_taste_name, tbl_cv_block_3_data.b3_title_id HAVING COUNT(tbl_cv_block_3_data.b3_title_id) > 1 ORDER by id_count DESC LIMIT 1"));
        if(count($get_fav_music_genre) == 0)
        {
            $fav_music_genre = '';
        }
        else
        {
            $fav_music_genre = $get_fav_music_genre[0]->music_taste_name;
        }
        
        $get_sonic_logo_count = DB::table('tbl_cv_block_6_data')
                                    ->join('tbl_cvs', 'tbl_cv_block_6_data.cv_id', '=','tbl_cvs.cv_id')
                                    ->where('tbl_cvs.status', '=', 1)
                                    ->where('tbl_cv_block_6_data.is_active', '=', 0)
                                    ->whereNotNull('tbl_cv_block_6_data.b6_name')
                                    ->count();
        
        $get_sonic_logo_percent = round($get_sonic_logo_count / $totatl_published_cvs * 100);
        //$get_sonic_logo_percent = $totatl_published_cvs / $get_sonic_logo_count ;
        
        return view('backend.views.welcome', ['recently_added_cv'=>$recently_added_cv , 'totatl_published_cvs'=>$totatl_published_cvs, 'fav_music_genre'=>$fav_music_genre, 'get_sonic_logo_percent'=>$get_sonic_logo_percent]);
    }

    function getMethodology()
    {
        return view('backend.views.methodology');
    }

    function getCookiePolicy()
    {
        return view('backend.views.cookie_policy');
    }

    function getPrivacyPolicy()
    {
        return view('backend.views.privacy_policy');
    }

    function getCvNames($sval_input)
    {
        //echo $sval_input;
        $sval = $sval_input;
        
        /* $search_query = DB::table('tbl_cvs')->where('cv_name', 'like', '%'.$sval.'%')->where('status', '=', 1)->where('is_active', '=', 0)->orderBy('cv_date', 'DESC')->get();

        if(count($search_query) != 0 && $search_query !='')
        {
            $output = '<ul class="dropdown-menu">';
        }
        else
        {
            $output = '<ul class="dropdown-menu"><li><span>No matching record found</span></li>';
        }
        foreach($search_query as $row)
        {
            $output .= '<li><a href="display-cv/'.base64_encode($row->cv_id).'">'.$row->cv_name." ".explode("-",$row->cv_date)[1].'</a></li>';
        } */

        $search_query = DB::select(DB::raw("SELECT tbl_industry.industry_name AS name, tbl_industry.industry_id AS id , tbl_industry.ind_date AS cvdate FROM tbl_industry where industry_name LIKE '%".$sval."%' and is_active = 0 UNION SELECT tbl_sub_industry.sub_industry_name AS name, tbl_sub_industry.sub_industry_id AS id , tbl_sub_industry.sub_ind_date AS cvdate FROM tbl_sub_industry where sub_industry_name LIKE '".$sval."%' and is_active = 0 UNION SELECT tbl_cvs.cv_name AS name, tbl_cvs.cv_id AS id, tbl_cvs.cv_date AS cvdate FROM tbl_cvs where cv_name LIKE '".$sval."%' and status = 1 and is_active = 0 group by tbl_cvs.cv_name ORDER BY name ASC"));
              
        if(count($search_query) != 0 && $search_query !='')
        {
            $output = '<div>';
            $output1 = '<ul class="dropdown-menu"><li>Brands:</li>';
            $output2 = '<ul class="dropdown-menu"><li>Industries:</li>';
        }
        else
        {
            $output = '<div><ul class="dropdown-menu"><li><span>No matching record found</span></li></div>';
        }
        foreach($search_query as $row)
        {
            if($row->cvdate != 'ind' && $row->cvdate != 'subind')
            {
                $output1 .= '<li><a href="'.config('app.url').'/'.'display-cv-launcher/'.base64_encode($row->name).'">'.$row->name.'</a></li>';
            }
            elseif($row->cvdate == 'ind' && $row->cvdate != 'subind')
            {
                $output2 .= '<li><a href="'.config('app.url').'/'.'display-industry-cv-launcher/'.base64_encode($row->id).'$_$'.base64_encode(0).'">'.$row->name.'</a></li>';
            }
            else
            {
                $output2 .= '<li class="subInd"><a href="'.config('app.url').'/'.'display-sub-industry-cv-launcher/'.base64_encode($row->id).'$_$'.base64_encode(0).'">'.$row->name.'</a></li>';
            }
            //$output .= '<li><a href="display-cv/'.base64_encode($row->cv_id).'">'.$row->cv_name." ".explode("-",$row->cv_date)[1].'</a></li>';
        }
        $output1 .= '</ul>';
        $output2 .= '</ul>';

        $output .= $output1.$output2."</div>";
        
        echo $output;
    }

    /* function browseCV()
    {
        $industry_data = DB::table('tbl_industry')->where('is_active', '=', 0)->orderBy('industry_name', 'ASC')->get();
        //print_r($industry_data);exit;
        $industry_cv_data = '';
        //$cv_data = DB::table('tbl_cvs')->where('status', '=', 1)->where('is_active', '=', 0)->orderBy('cv_id', 'desc')->get();
        //$cv_data = DB::table('tbl_cvs')->where('status', '=', 1)->where('is_active', '=', 0)->orderBy(DB::raw('RAND()'))->get();
        $distinct_cv_data = DB::table('tbl_cvs')->select('cv_name')->distinct()->where('status', '=', 1)->where('is_active', '=', 0)->orderBy(DB::raw('RAND()'))->get();
        $cv_letters_AtoZ_array = [];
        $cv_data_array = [];
        foreach($distinct_cv_data as $dcd)
        {
            $cv_count_query = DB::table('tbl_cvs')->where('cv_name', '=', $dcd->cv_name)->where('status', '=', 1)->where('is_active', '=', 0)->orderBy('cv_date','desc')->get()->count();
            $cv_data = DB::table('tbl_cvs')->where('cv_name', '=', $dcd->cv_name)->where('status', '=', 1)->where('is_active', '=', 0)->orderBy('cv_date','desc')->first();
            //print_r($cv_count_query);
            //echo "------------------------------<br>";

            array_push($cv_letters_AtoZ_array,str_replace(substr($cv_data->cv_name,+1),"",$cv_data->cv_name));
            $encodeID = base64_encode($cv_data->cv_id);
            $json_array["aHref"] = "display-cv/".$encodeID;
            if($cv_data->cv_logo != '' && $cv_data->cv_logo != null)
            {
                $json_array["cvImageUrl"] = "public/images/cv_logos/thumbnail/".str_replace("'","$#$",$cv_data->cv_logo);
            }
            else
            {
                $json_array["cvImageUrl"] = "public/images/cv_logos/no-logo-300x300.jpg";
            }
            //$json_array["cvImageUrl"] = "public/images/cv_logos/thumbnail/".str_replace("'","$#$",$cv_data->cv_logo);
            $json_array["cvName"] = str_replace("'","$#$",$cv_data->cv_name);
            $json_array["cvId"] = $cv_data->cv_id;
            //$json_array["AtoZ"] = trim($data->cv_name,substr($data->cv_name,+1));
            $json_array["AtoZ"] = str_replace(substr($cv_data->cv_name,+1),"",$cv_data->cv_name);
            $json_array["IndustryId"] = $cv_data->industry_id;
            $json_array["cvCount"] = $cv_count_query;
            array_push($cv_data_array,$json_array); 
        }
        //print_r($distinct_cv_data);
        //exit;
        
        
        //$cv_letters_AtoZ_array = [];
        //$cv_data_array = [];
        
        // foreach($cv_data as $data)
        // {
        //     //array_push($cv_letters_AtoZ_array,trim($data->cv_name,substr($data->cv_name,+1)));
        //     array_push($cv_letters_AtoZ_array,str_replace(substr($data->cv_name,+1),"",$data->cv_name));
        //     $encodeID = base64_encode($data->cv_id);
        //     $json_array["aHref"] = "display-cv/".$encodeID;
        //     $json_array["cvImageUrl"] = "public/images/cv_logos/thumbnail/".$data->cv_logo;
        //     $json_array["cvName"] = $data->cv_name;
        //     $json_array["cvId"] = $data->cv_id;
        //     //$json_array["AtoZ"] = trim($data->cv_name,substr($data->cv_name,+1));
        //     $json_array["AtoZ"] = str_replace($data->cv_name,substr($data->cv_name,+1),"",$data->cv_name);
        //     $json_array["IndustryId"] = $data->industry_id;
        //     $cvData = '<li>
        //                     <div class="cat_logo_wrapper">
        //                         <a href="display-cv/'.base64_encode($data->cv_id).'" title="'.$data->cv_name.'">
        //                             <div class="cat_logo_inner">
        //                                 <div class="cat_logo_hol">
        //                                     <img src="public/images/cv_logos/thumbnail/'.$data->cv_logo.'" alt="'.$data->cv_name.'" class="logo">
        //                                 </div>
        //                             </div>
        //                             <span class="cat_logo_caption">'.$data->cv_name.'</span>
        //                         </a>
        //                     </div>
        //                 </li>'; 
        //     //array_push($cv_data_array, $cvData);
        //     array_push($cv_data_array,$json_array); 
        // }
        //$cv_data_json = '{"items":'.json_encode($cv_data_array).'}';
        $cv_data_json = json_encode($cv_data_array);
        //echo $cv_data_json; exit;

        $sub_industry_parent_ids = DB::table('tbl_sub_industry')->select(DB::raw('distinct(parent_industry_id) as parent_industry_id'))->where('is_active', '=', 0)->get();
        $parent_industry_ids = [];
        if(count($sub_industry_parent_ids)!=0)
        {
            foreach($sub_industry_parent_ids as $sipi)
            {
                array_push($parent_industry_ids, $sipi->parent_industry_id);
            }
        }

        return view('backend.views.browse_cv',['industry_data'=>$industry_data,'cv_letters_AtoZ_array'=>$cv_letters_AtoZ_array,'cv_data_json'=>$cv_data_json,'industry_cv_data'=>$industry_cv_data,'parent_industry_ids'=>$parent_industry_ids]);
    } */

    function browseCV($ids)
    {
        $industry_data = DB::table('tbl_industry')->where('is_active', '=', 0)->orderBy('industry_name', 'ASC')->get();
        $industry_cv_data = '';
        //echo base64_decode(explode('$_$',$ids)[1])."--------------".base64_decode(explode('$_$',$ids)[0]);
        $call_type = 'browse_'.base64_decode(explode('$_$',$ids)[0]);

        if(base64_decode(explode('$_$',$ids)[0]) == 0)
        {
            
            if(base64_decode(explode('$_$',$ids)[1]) == '0' || base64_decode(explode('$_$',$ids)[1]) == 'asc' || base64_decode(explode('$_$',$ids)[1]) == 'desc')
            {
                if(base64_decode(explode('$_$',$ids)[1]) == 'asc')
                {
                    $sort_order = 'asc';
                    //$distinct_cv_data = DB::table('tbl_cvs')->distinct()->where('status', '=', 1)->where('is_active', '=', 0)->orderBy('cv_name','asc')->paginate(15);
                    $distinct_cv_data = DB::table('tbl_cvs')->where('status', '=', 1)->where('is_active', '=', 0)->where('parent', Null)->where('parent_id', Null)->orderBy('cv_name','asc')->paginate(15);
                }
                elseif(base64_decode(explode('$_$',$ids)[1]) == 'desc')
                {
                    $sort_order = 'desc';
                    //$distinct_cv_data = DB::table('tbl_cvs')->distinct()->where('status', '=', 1)->where('is_active', '=', 0)->orderBy('cv_name','desc')->paginate(15);
                    $distinct_cv_data = DB::table('tbl_cvs')->where('status', '=', 1)->where('is_active', '=', 0)->where('parent', Null)->where('parent_id', Null)->orderBy('cv_name','desc')->paginate(15);
                }
                else
                {
                    $sort_order = '0';
                    //$distinct_cv_data = DB::table('tbl_cvs')->distinct()->where('status', '=', 1)->where('is_active', '=', 0)->paginate(15);
                    $distinct_cv_data = DB::table('tbl_cvs')->where('status', '=', 1)->where('is_active', '=', 0)->where('parent', Null)->where('parent_id', Null)->paginate(15);
                }
            }
            else
            {
                $sort_order = '0';
                $tmp_name = base64_decode(explode('$_$',$ids)[1]).'%';
                //$distinct_cv_data = DB::table('tbl_cvs')->distinct()->where('cv_name', 'like', $tmp_name)->where('status', '=', 1)->where('is_active', '=', 0)->paginate(15);
                $distinct_cv_data = DB::table('tbl_cvs')->where('cv_name', 'like', $tmp_name)->where('status', '=', 1)->where('is_active', '=', 0)->where('parent', Null)->where('parent_id', Null)->paginate(15);
            }            
        }
        $dist_cv_data = DB::table('tbl_cvs')->distinct()->where('status', '=', 1)->where('is_active', '=', 0)->get();
        $cv_letters_AtoZ_array = [];
        $cv_data_array = [];
        
        foreach($dist_cv_data as $dcd)
        {
            $cv_data = DB::table('tbl_cvs')->where('cv_name', '=', $dcd->cv_name)->where('status', '=', 1)->where('is_active', '=', 0)->orderBy('cv_date','desc')->first();
            array_push($cv_letters_AtoZ_array,str_replace(substr($cv_data->cv_name,+1),"",$cv_data->cv_name));
        }
        
        $sub_industry_parent_ids = DB::table('tbl_sub_industry')->select(DB::raw('distinct(parent_industry_id) as parent_industry_id'))->where('is_active', '=', 0)->get();
        $parent_industry_ids = [];
        if(count($sub_industry_parent_ids)!=0)
        {
            foreach($sub_industry_parent_ids as $sipi)
            {
                array_push($parent_industry_ids, $sipi->parent_industry_id);
            }
        }
        return view('backend.views.browse_cv',['industry_data'=>$industry_data,'cv_letters_AtoZ_array'=>$cv_letters_AtoZ_array,'distinct_cv_data'=>$distinct_cv_data,'industry_cv_data'=>$industry_cv_data,'parent_industry_ids'=>$parent_industry_ids,'call_type'=>$call_type,'sort_order'=>$sort_order]);
    }

    function getSubIndustryData($industry_id)
    {
        $sub_industry_data =  DB::table('tbl_sub_industry')->where('parent_industry_id', '=', base64_decode($industry_id))->where('is_active', '=', 0)->get();
        $sub_industry_ul_data = '';
        if(count($sub_industry_data)!=0)
        {
            $sub_industry_ul_data .= '<ul style="display:none;">';
            foreach($sub_industry_data as $sid)
            {
                //$sub_industry_ul_data .= '<li sindid="sind_['.$sid->sub_industry_id.']" id="sind_'.$sid->sub_industry_id.'"><a href="'.url('get-industry-cvs').'/'.base64_encode($sid->industry_id).'" class="link">'.$sid->industry_name.'<span class="sindMenu_arrowDown arrow"></span></a></li>';
                $sub_industry_ul_data .= '<li "sind_['.$sid->sub_industry_id.']" id="sind_'.$sid->sub_industry_id.'"><a href="'.url('get-sub-industry-cvs').'/'.base64_encode($sid->sub_industry_id).'$_$'.base64_encode(0).'" class="link">'.$sid->sub_industry_name.'<span class="sindMenu_arrowDown arrow"></span></a></li>';
            }
            $sub_industry_ul_data .= '</ul>';
            
        } 
        return $sub_industry_ul_data;                                           
        //return base64_decode($industry_id);
    }

    function getIndustryCvData($ids)
    {
        $industry_data = DB::table('tbl_industry')->where('is_active', '=', 0)->orderBy('industry_name', 'ASC')->get();

        $industry_id = base64_decode(explode('$_$',$ids)[0]); 
        $tmp_name = base64_decode(explode('$_$',$ids)[1]).'%';
        $call_type = 'industry_'.$industry_id;

        if(base64_decode(explode('$_$',$ids)[0]) == 0)
        {
            if(base64_decode(explode('$_$',$ids)[1]) == '0' || base64_decode(explode('$_$',$ids)[1]) == 'asc' || base64_decode(explode('$_$',$ids)[1]) == 'desc')
            {
                if(base64_decode(explode('$_$',$ids)[1]) == 'asc')
                {
                    $sort_order = 'asc';
                    //$distinct_cv_data = DB::table('tbl_cvs')->distinct()->where('status', '=', 1)->where('is_active', '=', 0)->orderBy('cv_name','asc')->paginate(15);
                    $distinct_cv_data = DB::table('tbl_cvs')->where('status', '=', 1)->where('is_active', '=', 0)->where('parent', Null)->where('parent_id', Null)->orderBy('cv_name','asc')->paginate(15);
                }
                elseif(base64_decode(explode('$_$',$ids)[1]) == 'desc')
                {
                    $sort_order = 'desc';
                    //$distinct_cv_data = DB::table('tbl_cvs')->distinct()->where('status', '=', 1)->where('is_active', '=', 0)->orderBy('cv_name','desc')->paginate(15);
                    $distinct_cv_data = DB::table('tbl_cvs')->where('status', '=', 1)->where('is_active', '=', 0)->where('parent', Null)->where('parent_id', Null)->orderBy('cv_name','desc')->paginate(15);
                }
                else
                {
                    $sort_order = '0';
                    //$distinct_cv_data = DB::table('tbl_cvs')->distinct()->where('status', '=', 1)->where('is_active', '=', 0)->paginate(15);
                    $distinct_cv_data = DB::table('tbl_cvs')->where('status', '=', 1)->where('is_active', '=', 0)->where('parent', Null)->where('parent_id', Null)->paginate(15);
                }                
            }
            else
            {
                $sort_order = '0';
                //$distinct_cv_data = DB::table('tbl_cvs')->distinct()->where('cv_name', 'like', $tmp_name)->where('status', '=', 1)->where('is_active', '=', 0)->paginate(15);
                $distinct_cv_data = DB::table('tbl_cvs')->where('cv_name', 'like', $tmp_name)->where('status', '=', 1)->where('is_active', '=', 0)->where('parent', Null)->where('parent_id', Null)->paginate(15);
            }
            $dist_cv_data = DB::table('tbl_cvs')->distinct()->where('status', '=', 1)->where('is_active', '=', 0)->get();
            $industry_cv_data = '';
        }
        else
        {
            if(base64_decode(explode('$_$',$ids)[1]) == '0' || base64_decode(explode('$_$',$ids)[1]) == 'asc' || base64_decode(explode('$_$',$ids)[1]) == 'desc')
            {
                if(base64_decode(explode('$_$',$ids)[1]) == 'asc')
                {
                    $sort_order = 'asc';
                    //$distinct_cv_data = DB::table('tbl_cvs')->distinct()->where('industry_id','=',$industry_id)->where('status', '=', 1)->where('is_active', '=', 0)->orderBy('cv_name','asc')->paginate(14);
                    $distinct_cv_data = DB::table('tbl_cvs')->where('industry_id','=',$industry_id)->where('status', '=', 1)->where('is_active', '=', 0)->where('parent', Null)->where('parent_id', Null)->orderBy('cv_name','asc')->paginate(14);
                    $industry_cv_data = DB::table('tbl_industry')->where('industry_id','=',$industry_id)->where('is_active', '=', 0)->first();
                }
                elseif(base64_decode(explode('$_$',$ids)[1]) == 'desc')
                {
                    $sort_order = 'desc';
                    //$distinct_cv_data = DB::table('tbl_cvs')->distinct()->where('industry_id','=',$industry_id)->where('status', '=', 1)->where('is_active', '=', 0)->orderBy('cv_name','desc')->paginate(14);
                    $distinct_cv_data = DB::table('tbl_cvs')->where('industry_id','=',$industry_id)->where('status', '=', 1)->where('is_active', '=', 0)->where('parent', Null)->where('parent_id', Null)->orderBy('cv_name','desc')->paginate(14);
                    $industry_cv_data = DB::table('tbl_industry')->where('industry_id','=',$industry_id)->where('is_active', '=', 0)->first();
                }
                else
                {
                    $sort_order = '0';
                    //$distinct_cv_data = DB::table('tbl_cvs')->distinct()->where('industry_id','=',$industry_id)->where('status', '=', 1)->where('is_active', '=', 0)->paginate(14);
                    $distinct_cv_data = DB::table('tbl_cvs')->where('industry_id','=',$industry_id)->where('status', '=', 1)->where('is_active', '=', 0)->where('parent', Null)->where('parent_id', Null)->paginate(14);
                    $industry_cv_data = DB::table('tbl_industry')->where('industry_id','=',$industry_id)->where('is_active', '=', 0)->first();
                }

            }
            else
            {
                $sort_order = '0';
                $distinct_cv_data = DB::table('tbl_cvs')->distinct()->where('cv_name', 'like', $tmp_name)->where('industry_id','=',$industry_id)->where('status', '=', 1)->where('is_active', '=', 0)->paginate(14);
                //$dist_cv_data = DB::table('tbl_cvs')->distinct()->where('cv_name', 'like', $tmp_name)->where('industry_id','=',$industry_id)->where('status', '=', 1)->where('is_active', '=', 0)->get();
                $industry_cv_data = DB::table('tbl_industry')->where('industry_id','=',$industry_id)->where('is_active', '=', 0)->first(); 
            }
            $dist_cv_data = DB::table('tbl_cvs')->distinct()->where('industry_id','=',$industry_id)->where('status', '=', 1)->where('is_active', '=', 0)->get();
        }
        
        $cv_letters_AtoZ_array = [];
        $cv_data_array = [];

        foreach($dist_cv_data as $dcd)
        {
            $cv_data = DB::table('tbl_cvs')->where('cv_name', '=', $dcd->cv_name)->where('status', '=', 1)->where('is_active', '=', 0)->orderBy('cv_date','desc')->first();
            array_push($cv_letters_AtoZ_array,str_replace(substr($cv_data->cv_name,+1),"",$cv_data->cv_name));            
        }

        $sub_industry_parent_ids = DB::table('tbl_sub_industry')->select(DB::raw('distinct(parent_industry_id) as parent_industry_id'))->where('is_active', '=', 0)->get();
        $parent_industry_ids = [];
        if(count($sub_industry_parent_ids)!=0)
        {
            foreach($sub_industry_parent_ids as $sipi)
            {
                array_push($parent_industry_ids, $sipi->parent_industry_id);
            }
        }

        return view('backend.views.browse_cv',['industry_data'=>$industry_data,'cv_letters_AtoZ_array'=>$cv_letters_AtoZ_array,'distinct_cv_data'=>$distinct_cv_data,'industry_cv_data'=>$industry_cv_data,'parent_industry_ids'=>$parent_industry_ids,'call_type'=>$call_type,'sort_order'=>$sort_order]);
    }

    function getSubIndustryCvData($ids)
    {
        $sub_industry_id = base64_decode(explode('$_$',$ids)[0]); 
        $tmp_name = base64_decode(explode('$_$',$ids)[1]).'%';
        $call_type = 'sub-industry_'.$sub_industry_id;

        $industry_data = DB::table('tbl_industry')->where('is_active', '=', 0)->orderBy('industry_name', 'ASC')->get();
        $parent_industry_data = DB::table('tbl_industry')->join('tbl_sub_industry', 'tbl_industry.industry_id', '=', 'tbl_sub_industry.parent_industry_id')->where('sub_industry_id', '=', $sub_industry_id)->first();
        
        if(base64_decode(explode('$_$',$ids)[0]) == 0)
        {
            if(base64_decode(explode('$_$',$ids)[1]) == '0' || base64_decode(explode('$_$',$ids)[1]) == 'asc' || base64_decode(explode('$_$',$ids)[1]) == 'desc')
            {
                if(base64_decode(explode('$_$',$ids)[1]) == 'asc')
                {
                    $sort_order = 'asc';
                    //$distinct_cv_data = DB::table('tbl_cvs')->distinct()->where('status', '=', 1)->where('is_active', '=', 0)->orderBy('cv_name','asc')->paginate(15);
                    $distinct_cv_data = DB::table('tbl_cvs')->where('status', '=', 1)->where('is_active', '=', 0)->where('parent', Null)->where('parent_id', Null)->orderBy('cv_name','asc')->paginate(15);
                }
                elseif(base64_decode(explode('$_$',$ids)[1]) == 'desc')
                {
                    $sort_order = 'desc';
                    //$distinct_cv_data = DB::table('tbl_cvs')->distinct()->where('status', '=', 1)->where('is_active', '=', 0)->orderBy('cv_name','desc')->paginate(15);
                    $distinct_cv_data = DB::table('tbl_cvs')->where('status', '=', 1)->where('is_active', '=', 0)->where('parent', Null)->where('parent_id', Null)->orderBy('cv_name','desc')->paginate(15);
                }
                else
                {
                    $sort_order = '0';
                    //$distinct_cv_data = DB::table('tbl_cvs')->distinct()->where('status', '=', 1)->where('is_active', '=', 0)->paginate(15);
                    $distinct_cv_data = DB::table('tbl_cvs')->where('status', '=', 1)->where('is_active', '=', 0)->where('parent', Null)->where('parent_id', Null)->paginate(15);
                }
            }
            else
            {
                $sort_order = '0';
                //$distinct_cv_data = DB::table('tbl_cvs')->distinct()->where('cv_name', 'like', $tmp_name)->where('status', '=', 1)->where('is_active', '=', 0)->paginate(15);
                $distinct_cv_data = DB::table('tbl_cvs')->where('cv_name', 'like', $tmp_name)->where('status', '=', 1)->where('is_active', '=', 0)->where('parent', Null)->where('parent_id', Null)->paginate(15);
            }
            $dist_cv_data = DB::table('tbl_cvs')->distinct()->where('status', '=', 1)->where('is_active', '=', 0)->get();
            
            $industry_cv_data = '';
        }
        else
        {
            if(base64_decode(explode('$_$',$ids)[1]) == '0' || base64_decode(explode('$_$',$ids)[1]) == 'asc' || base64_decode(explode('$_$',$ids)[1]) == 'desc')
            {
                if(base64_decode(explode('$_$',$ids)[1]) == 'asc')
                {
                    $sort_order = 'asc';
                    //$distinct_cv_data = DB::table('tbl_cvs')->distinct()->where('sub_industry_id','=',$sub_industry_id)->where('status', '=', 1)->where('is_active', '=', 0)->orderBy('cv_name','asc')->paginate(14);
                    $distinct_cv_data = DB::table('tbl_cvs')->where('sub_industry_id','=',$sub_industry_id)->where('status', '=', 1)->where('is_active', '=', 0)->where('parent', Null)->where('parent_id', Null)->orderBy('cv_name','asc')->paginate(14);
                    $sub_industry_cv_data = DB::table('tbl_sub_industry')->where('sub_industry_id','=',$sub_industry_id)->where('is_active', '=', 0)->first();
                }
                elseif(base64_decode(explode('$_$',$ids)[1]) == 'desc')
                {
                    $sort_order = 'desc';
                    //$distinct_cv_data = DB::table('tbl_cvs')->distinct()->where('sub_industry_id','=',$sub_industry_id)->where('status', '=', 1)->where('is_active', '=', 0)->orderBy('cv_name','desc')->paginate(14);
                    $distinct_cv_data = DB::table('tbl_cvs')->where('sub_industry_id','=',$sub_industry_id)->where('status', '=', 1)->where('is_active', '=', 0)->where('parent', Null)->where('parent_id', Null)->orderBy('cv_name','desc')->paginate(14);
                    $sub_industry_cv_data = DB::table('tbl_sub_industry')->where('sub_industry_id','=',$sub_industry_id)->where('is_active', '=', 0)->first();
                }
                else
                {
                    $sort_order = '0';
                    //$distinct_cv_data = DB::table('tbl_cvs')->distinct()->where('sub_industry_id','=',$sub_industry_id)->where('status', '=', 1)->where('is_active', '=', 0)->paginate(14);
                    $distinct_cv_data = DB::table('tbl_cvs')->where('sub_industry_id','=',$sub_industry_id)->where('status', '=', 1)->where('is_active', '=', 0)->where('parent', Null)->where('parent_id', Null)->paginate(14);
                    $sub_industry_cv_data = DB::table('tbl_sub_industry')->where('sub_industry_id','=',$sub_industry_id)->where('is_active', '=', 0)->first();
                }
            }
            else
            {
                $sort_order = '0';
                //$distinct_cv_data = DB::table('tbl_cvs')->distinct()->where('cv_name', 'like', $tmp_name)->where('sub_industry_id','=',$sub_industry_id)->where('status', '=', 1)->where('is_active', '=', 0)->paginate(14);
                $distinct_cv_data = DB::table('tbl_cvs')->where('cv_name', 'like', $tmp_name)->where('sub_industry_id','=',$sub_industry_id)->where('status', '=', 1)->where('is_active', '=', 0)->where('parent', Null)->where('parent_id', Null)->paginate(14);
                $sub_industry_cv_data = DB::table('tbl_sub_industry')->where('sub_industry_id','=',$sub_industry_id)->where('is_active', '=', 0)->first();
            }
            $dist_cv_data = DB::table('tbl_cvs')->distinct()->where('sub_industry_id','=',$sub_industry_id)->where('status', '=', 1)->where('is_active', '=', 0)->get();
        }

        $cv_letters_AtoZ_array = [];
        $cv_data_array = [];

        foreach($dist_cv_data as $dcd)
        {
            $cv_data = DB::table('tbl_cvs')->where('cv_name', '=', $dcd->cv_name)->where('status', '=', 1)->where('is_active', '=', 0)->orderBy('cv_date','desc')->first();
            array_push($cv_letters_AtoZ_array,str_replace(substr($cv_data->cv_name,+1),"",$cv_data->cv_name));             
        }
        
        $sub_industry_parent_ids = DB::table('tbl_sub_industry')->select(DB::raw('distinct(parent_industry_id) as parent_industry_id'))->where('is_active', '=', 0)->get();
        $parent_industry_ids = [];
        if(count($sub_industry_parent_ids)!=0)
        {
            foreach($sub_industry_parent_ids as $sipi)
            {
                array_push($parent_industry_ids, $sipi->parent_industry_id);
            }
        }

        return view('backend.views.browse_cv',['industry_data'=>$industry_data,'cv_letters_AtoZ_array'=>$cv_letters_AtoZ_array,'distinct_cv_data'=>$distinct_cv_data,'parent_industry_data'=>$parent_industry_data,'sub_industry_cv_data'=>$sub_industry_cv_data,'parent_industry_ids'=>$parent_industry_ids,'call_type'=>$call_type,'sort_order'=>$sort_order]);
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

    public function getSubIndustryAvgData($sub_industry_id)
    {
        /* $cv_ids_array = [];
        
        $cvs_as_per_industry = DB::table('tbl_cvs')
                    ->where('sub_industry_id', $sub_industry_id)
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->get();
        
        foreach($cvs_as_per_industry as $cv_items)
        {
            array_push($cv_ids_array, $cv_items->cv_id);
        }

        $sub_insudtry_yes_avg_data_array = [];
        $sub_insudtry_no_avg_data_array = [];
        foreach($cv_ids_array as $cv_id)
        {
            $sub_insudtry_avg_data = DB::table('tbl_cv_block_7_data')
                        ->where('cv_id', $cv_id)                        
                        ->where('is_active', 0)
                        ->get();
            foreach($sub_insudtry_avg_data as $data)
            {
                if($data->b7_name == 'yes' || $data->b7_name == 'Yes' || $data->b7_name == 'YES')
                {
                    if($data->b7_number!='' && $data->b7_number !=null)
                    {
                        array_push($sub_insudtry_yes_avg_data_array,$data->b7_number);
                    }                    
                }
                if($data->b7_name == 'no' || $data->b7_name == 'No' || $data->b7_name == 'NO')
                {
                    if($data->b7_number!='' && $data->b7_number !=null)
                    {
                        array_push($sub_insudtry_no_avg_data_array,$data->b7_number);
                    }                    
                }
            }
        }
        
        return ['sub_insudtry_yes_avg_data_array'=>$sub_insudtry_yes_avg_data_array, 'sub_insudtry_no_avg_data_array'=>$sub_insudtry_no_avg_data_array]; */
        $cv_ids_array = [];
        
        $cvs_as_per_industry = DB::table('tbl_cvs')
                    ->where('sub_industry_id', $sub_industry_id)
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
                    
        /* $b15_sum_data_array = [];
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

    public function getSubIndustryMusicExpenditurePerVideoAvgData($sub_industry_id)
    {
        $cv_ids_array = [];
        
        $cvs_as_per_industry = DB::table('tbl_cvs')
                    ->join('tbl_cv_block_15_data', 'tbl_cvs.cv_id', '=', 'tbl_cv_block_15_data.cv_id')
                    ->where('tbl_cvs.sub_industry_id', $sub_industry_id)
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
    }

    public function getSubIndustryMusicExpenditurePerYearAvgData($sub_industry_id)
    {
        $cv_ids_array = [];
       
        $cvs_as_per_industry = DB::table('tbl_cvs')
                    ->join('tbl_cv_block_14_data', 'tbl_cvs.cv_id', '=', 'tbl_cv_block_14_data.cv_id')
                    ->where('tbl_cvs.sub_industry_id', $sub_industry_id)
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

    function displayCV($cvid)
    {
        $cv_data = DB::table('tbl_cvs')->where('cv_id', '=', base64_decode($cvid))->first();

        $cv_id_dates = DB::table('tbl_cvs')->where('cv_name', '=', $cv_data->cv_name)->where('status', '=', 1)->where('is_active', '=', 0)->orderBy('cv_date','desc')->get();
        
        $cv_id_year_array = [];
        foreach($cv_id_dates as $cv_id_date)
        {
            /* if($cv_id_date->cv_date != $cv_data->cv_date)
            {
                $year = explode("-",$cv_id_date->cv_date);
                array_push($cv_id_year_array,$cv_id_date->cv_id."$#$".$year[1]);
            } */
            $year = explode("-",$cv_id_date->cv_date);
            array_push($cv_id_year_array,$cv_id_date->cv_id."$#$".$year[1]);
        }
        
        $child_cv = DB::table('tbl_cvs')->where('parent_id', '=', base64_decode($cvid))->where('status', '=', 1)->where('is_active', '=', 0)->first();
        
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
            $parent_cv_overall_ranking = DB::table('tbl_cv_block_2_data')->where('cv_id', '=', $parent_cv->cv_id)->where('is_active', '=', 0)->first();
            
        }
        else
        {
            $parent_cv_overall_ranking = ''; 
        }
        
        if($cv_data->industry_id !='' && $cv_data->industry_id != null)
        {
            $cv_industry = DB::table('tbl_industry')->where('industry_id', '=', $cv_data->industry_id)->where('is_active', '=', 0)->first();
        }
        else
        {
            $cv_industry = '';
        }
        
        if($cv_data->sub_industry_id !='' && $cv_data->sub_industry_id != null)
        {
            $cv_sub_industry = DB::table('tbl_sub_industry')->where('sub_industry_id', '=', $cv_data->sub_industry_id)->where('is_active', '=', 0)->first();
        }
        else
        {
            $cv_sub_industry = '';
        }

        //print_r($parent_cv_overall_ranking);exit;

        /* $cv_parent_data = DB::table('tbl_cvs')->where('is_active', '=', 0)->get();
        $industry_data = DB::table('tbl_industry')->where('is_active', '=', 0)->get();
        $music_taste_data = DB::table('tbl_music_taste')->where('is_active', '=', 0)->get();
        $experience_data = DB::table('tbl_experience')->where('is_active', '=', 0)->get();
        $qualitative_data = DB::table('tbl_qualitative')->where('is_active', '=', 0)->get();
        $footer_template_data = DB::table('tbl_footer_template')->where('is_active', '=', 0)->get(); */
        $cv_block_2_data = DB::table('tbl_cv_block_2_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
        $cv_block_3_data = DB::table('tbl_cv_block_3_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->get();
        $cv_block_4_data = DB::table('tbl_cv_block_4_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
        $cv_block_5_data = DB::table('tbl_cv_block_5_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->get();
        $cv_block_6_data = DB::table('tbl_cv_block_6_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
        $cv_block_7_data = DB::table('tbl_cv_block_7_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->get();
        $cv_block_8_data = DB::table('tbl_cv_block_8_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->get();
        $cv_block_9_data = DB::table('tbl_cv_block_9_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->get();
        $cv_block_10_data = DB::table('tbl_cv_block_10_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->get();
        $cv_block_11_data = DB::table('tbl_cv_block_11_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->get();
        $cv_block_12_data = DB::table('tbl_cv_block_12_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->get();
        $cv_block_13_data = DB::table('tbl_cv_block_13_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->get();
        $cv_block_14_data = DB::table('tbl_cv_block_14_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->get();
        $cv_block_15_data = DB::table('tbl_cv_block_15_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->get();
        $cv_block_16_mood_graph_data = DB::table('tbl_cv_block_16_mood_graph_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
        $cv_block_16_genre_graph_data = DB::table('tbl_cv_block_16_genre_graph_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
        $cv_block_17_mood_graph_data = DB::table('tbl_cv_block_17_mood_graph_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
        $cv_block_17_genre_graph_data = DB::table('tbl_cv_block_17_genre_graph_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first(); 
        $cv_block_18_mood_graph_data = DB::table('tbl_cv_block_18_mood_graph_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
        $cv_block_18_genre_graph_data = DB::table('tbl_cv_block_18_genre_graph_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
        $cv_block_19_mood_graph_data = DB::table('tbl_cv_block_19_mood_graph_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
        $cv_block_19_genre_graph_data = DB::table('tbl_cv_block_19_genre_graph_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
        $cv_mood_aggr_graph_data = DB::table('tbl_mood_aggr_graph_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
        $cv_genre_aggr_graph_data = DB::table('tbl_genre_aggr_graph_data')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
        $footer_template_data = DB::table('tbl_footer_template')->where('footer_template_id', '=', $cv_data->footer_template_id)->first();
        
        $cv_genre_aggr_graph_values_data = DB::table('tbl_genre_aggr_graph_data')->select('ambient','blues','classical','country','electronicDance','folk','indieAlternative','jazz','latin','metal','pop','punk','rapHipHop','reggae','rnb','rock','singerSongwriter')->where('cv_id', '=', base64_decode($cvid))->where('is_active', '=', 0)->first();
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
        }
        
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
                if($cv_block_10_data[$qlti]->b10_name_id != 0)
                {
                    array_push($qlti_ids_array,$cv_block_10_data[$qlti]->b10_name_id);
                    $qualitative_id_data = DB::table('tbl_qualitative')
                        ->where('qualitative_id', $cv_block_10_data[$qlti]->b10_name_id)
                        ->first();
                    array_push($qualitative_data,$qualitative_id_data->qualitative_name);
                }                
            }
            //print_r($qualitative_data); exit;
            /* if(count($qlti_ids_array)>0)
            {
                $qualitative_data = DB::table('tbl_qualitative')
                    ->whereIn('qualitative_id', $qlti_ids_array)
                    ->get();
            }
            else
            {
                $qualitative_data = [];
            } */
        }
        if(count($qualitative_data)==0)
        {
            $qualitative_data = '';
        }
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
            for($ei = 0; $ei < count($cv_block_13_data); $ei++)
            {
                array_push($ei_ids_array,$cv_block_13_data[$ei]->b13_name_id);
            }
            if(count($ei_ids_array)>0)
            {
                $experience_data = DB::table('tbl_experience')
                    ->whereIn('experience_id', $ei_ids_array)
                    ->get();
                $experience_excluded_data =  DB::table('tbl_experience')
                ->whereNotIn('experience_id', $ei_ids_array)
                ->where("is_active", '=', '0')
                ->get();    
            }
            else
            {
                $experience_data = [];
                $experience_excluded_data = [];
            }
        }
        //print_r($cv_block_13_data); exit;
        if(count($experience_data)==0)
        {
            $experience_data = '';
            $experience_excluded_data = '';
        }
        if(count($cv_block_14_data)==0)
        {
            $cv_block_14_data = '';
        }
        //print_r($cv_block_14_data); exit;
        if(count($cv_block_15_data)==0)
        {
            $cv_block_15_data = '';
        }
        //print_r($cv_block_15_data); exit;
        /* if(count($cv_block_16_mood_graph_data)==0)
        {
            $cv_block_16_mood_graph_data = '';
        }
        if(count($cv_block_16_genre_graph_data)==0)
        {
            $cv_block_16_genre_graph_data = '';
        } */
        /* if(count($cv_block_17_mood_graph_data)==0)
        {
            $cv_block_17_mood_graph_data = '';
        } 
        if(count($cv_block_17_genre_graph_data)==0)
        {
            $cv_block_17_genre_graph_data = '';
        }*/
        /* if(count($cv_block_18_mood_graph_data)==0)
        {
            $cv_block_18_mood_graph_data = '';
        } 
        if(count($cv_block_18_genre_graph_data)==0)
        {
            $cv_block_18_genre_graph_data = '';
        }*/
        /* if(count($cv_mood_aggr_graph_data)==0)
        {
            $cv_mood_aggr_graph_data = '';
        }
        if(count($cv_genre_aggr_graph_data)==0)
        {
            $cv_genre_aggr_graph_data = '';
        } */
        /* if(count($cv_parent_list)==0)
        {
            $cv_parent_list = '';
        } */
        //$distinct_cv_names = DB::table('tbl_cvs')->select('cv_name')->distinct()->where('status', '=', 1)->where('is_active', '=', 0)->get();
        $cv_names_ids = DB::table('tbl_cvs')->where('status', '=', 1)->where('is_active', '=', 0)->orderBY('cv_name','ASC')->get();
        $distinct_cv_industries = DB::table('tbl_cvs')->select('industry_id')->distinct()->where('status', '=', 1)->where('is_active', '=', 0)->get();
        $industry_data = DB::table('tbl_industry')->where('is_active', '=', 0)->orderBY('industry_name','ASC')->get();
        $distinct_cv_sub_industries = DB::table('tbl_cvs')->select('sub_industry_id')->distinct()->where('status', '=', 1)->where('is_active', '=', 0)->get();
        $sub_industry_data = DB::table('tbl_sub_industry')->where('is_active', '=', 0)->orderBY('sub_industry_name','ASC')->get();
        //return view('backend.views.display_cv', ['cv_data'=>$cv_data, 'cv_parent_data'=>$cv_parent_data, 'industry_data'=>$industry_data, 'music_taste_data'=>$music_taste_data, 'experience_data'=>$experience_data, 'qualitative_data'=>$qualitative_data, 'footer_template_data'=>$footer_template_data, 'cv_block_2_data'=>$cv_block_2_data, 'cv_block_3_data'=>$cv_block_3_data, 'cv_block_4_data'=>$cv_block_4_data, 'cv_block_5_data'=>$cv_block_5_data, 'cv_block_6_data'=>$cv_block_6_data, 'cv_block_7_data'=>$cv_block_7_data, 'cv_block_8_data'=>$cv_block_8_data, 'cv_block_9_data'=>$cv_block_9_data, 'cv_block_10_data'=>$cv_block_10_data, 'cv_block_11_data'=>$cv_block_11_data, 'cv_block_12_data'=>$cv_block_12_data, 'cv_block_13_data'=>$cv_block_13_data, 'cv_block_14_data'=>$cv_block_14_data, 'cv_block_15_data'=>$cv_block_15_data]);
        return view('backend.views.display_cv', ['cv_data'=>$cv_data, 'cv_id_year_array'=>$cv_id_year_array, 'parent_cv'=>$parent_cv, 'child_cv'=>$child_cv, 'footer_template_data'=>$footer_template_data, 'cv_block_2_data'=>$cv_block_2_data, 'parent_cv_overall_ranking'=>$parent_cv_overall_ranking, 'cv_block_3_data'=>$cv_block_3_data, 'music_taste_data'=>$music_taste_data, 'cv_block_4_data'=>$cv_block_4_data, 'cv_block_5_data'=>$cv_block_5_data, 'cv_block_6_data'=>$cv_block_6_data, 'cv_block_7_data'=>$cv_block_7_data, 'cv_block_8_data'=>$cv_block_8_data, 'cv_block_9_data'=>$cv_block_9_data, 'cv_block_10_data'=>$cv_block_10_data, 'qualitative_data'=>$qualitative_data, 'cv_block_11_data'=>$cv_block_11_data, 'cv_block_12_data'=>$cv_block_12_data, 'cv_block_13_data'=>$cv_block_13_data, 'experience_data'=>$experience_data, 'experience_excluded_data'=>$experience_excluded_data, 'cv_block_14_data'=>$cv_block_14_data, 'cv_block_15_data'=>$cv_block_15_data, 'cv_block_16_mood_graph_data'=>$cv_block_16_mood_graph_data, 'cv_block_16_genre_graph_data'=>$cv_block_16_genre_graph_data, 'cv_block_17_mood_graph_data'=>$cv_block_17_mood_graph_data, 'cv_block_17_genre_graph_data'=>$cv_block_17_genre_graph_data, 'cv_block_18_mood_graph_data'=>$cv_block_18_mood_graph_data, 'cv_block_18_genre_graph_data'=>$cv_block_18_genre_graph_data, 'cv_block_19_mood_graph_data'=>$cv_block_19_mood_graph_data, 'cv_block_19_genre_graph_data'=>$cv_block_19_genre_graph_data, 'cv_mood_aggr_graph_data'=>$cv_mood_aggr_graph_data, 'cv_genre_aggr_graph_data'=>$cv_genre_aggr_graph_data, 'cv_names_ids'=>$cv_names_ids, 'distinct_cv_industries'=>$distinct_cv_industries, 'industry_data'=>$industry_data, 'distinct_cv_sub_industries'=>$distinct_cv_sub_industries, 'sub_industry_data'=>$sub_industry_data, 'cv_industry'=>$cv_industry, 'cv_sub_industry'=>$cv_sub_industry, 'top_3_genre'=>$top_3_genre]);
    }

    function displayCVLauncher($cvname)
    {
        $cv_data = DB::table('tbl_cvs')->where('cv_name', '=', base64_decode($cvname))->where('status', '=', 1)->where('is_active', '=', 0)->orderBy('cv_date','desc')->get();
        
        if($cv_data[0]->industry_id !='' && $cv_data[0]->industry_id != null)
        {
            $cv_industry = DB::table('tbl_industry')->where('industry_id', '=', $cv_data[0]->industry_id)->where('is_active', '=', 0)->first();
        }
        else
        {
            $cv_industry = '';
        }
        
        if($cv_data[0]->sub_industry_id !='' && $cv_data[0]->sub_industry_id != null)
        {
            $cv_sub_industry = DB::table('tbl_sub_industry')->where('sub_industry_id', '=', $cv_data[0]->sub_industry_id)->where('is_active', '=', 0)->first();
        }
        else
        {
            $cv_sub_industry = '';
        }
        return view('backend.views.display_cv_launcher',['cv_data'=>$cv_data,'cv_industry'=>$cv_industry,'cv_sub_industry'=>$cv_sub_industry]);
    }

    function cvShare(Request $request)
    {
        //return $request->input();
        $request->validate([
            "email"=>['required','email:rfc,dns'],
            "link_validity"=>'required|not_in:0'
        ]);
        
        $link_expiry_date = Carbon::now()->addDays($request->link_validity*7)->toDateString(); 
        $sharing_data = ['cv_id' => base64_decode($request->cv_id),
        'email' => $request->email,
        'link_validity' => $request->link_validity,
        'shared_by' => session('LoggedUser'),
        //'view_count' => config('custom.sharing_view_count'),
        'link_expiry_date' => $link_expiry_date
        ];
        if($request->email !='' && $request->cv_id !='' && $request->link_validity !='')
        {
            $id = DB::table('tbl_shared_cv')->insertGetId($sharing_data);

            if($id != '')
            {
                $current_date_time = Carbon::now()->toDateTimeString();
                $token = sha1($request->email.$id.$current_date_time);
                $get_user_name = DB::table('tbl_users')->where('uid', '=', session('LoggedUser'))->first();
                $get_cv_name_with_year = DB::table('tbl_cvs')->where('cv_id', '=', base64_decode($request->cv_id))->first();
                $email_data = [
                    'email' => $request->email,
                    'validity_days' => $request->link_validity * 7,
                    'sender_name' => $get_user_name->name,
                    'cv_name' => $get_cv_name_with_year->cv_name." ".explode("-",$get_cv_name_with_year->cv_date)[1],
                    'token' => $token
                ];

                try
                {
                    $cc_mail_id = config('custom.cc_mail_id');
                    $bcc_mail_id = config('custom.bcc_mail_id');
                    //Mail::to($request->email)->cc("support@wits.bz")->send(new ShareCvEmail($email_data));
                    Mail::to($request->email)->cc($cc_mail_id)->bcc($bcc_mail_id)->send(new ShareCvEmail($email_data));
                }
                catch(\Illuminate\Database\QueryException $ex)
                { 
                    return back()->with('fail', 'Something went wrong while sending email, please try again!');
                }
                
                try
                { 
                    DB::table('tbl_shared_cv')->where('id', $id)->update(['share_link_token' => $token]);
                }
                catch(\Illuminate\Database\QueryException $ex)
                { 
                    return back()->with('fail', 'Something went wrong, please try again!');
                }

                return back()->with('success','Sonic Radar shared successfully');
            }
            else
            {
                return back()->with('fail', 'Something went wrong, please try again!');
            }
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }

    function cvCompare(Request $request)
    {
        //return $request->input();
        $request->validate([
            "compare_option"=>'required',
        ]);

        if($request->compare_option != '')
        {
            if($request->compare_option == 'barnd_cv')
            {
                $request->validate([
                    "compare_option"=>'required',
                    "compare_brand_cv_id"=>'required|not_in:0'
                ]);
                if($request->cv_id != '' && $request->compare_brand_cv_id != '')
                {
                    $cv1_data = DB::table('tbl_cvs')->where('cv_id', '=', base64_decode($request->cv_id))->first();

                    if($cv1_data->parent_id != '' && $cv1_data->parent_id != null)
                    {
                        $cv1_parent_cv = DB::table('tbl_cvs')->where('cv_id', '=', $cv1_data->parent_id)->where('status', '=', 1)->where('is_active', '=', 0)->first();           
                    }
                    else
                    {
                        $cv1_parent_cv = null;
                    }
                    if($cv1_parent_cv != null)
                    {
                        $cv1_parent_cv_overall_ranking = DB::table('tbl_cv_block_2_data')->where('cv_id', '=', $cv1_parent_cv->cv_id)->where('is_active', '=', 0)->first();
                        
                    }
                    else
                    {
                        $cv1_parent_cv_overall_ranking = ''; 
                    }
                    //print_r($parent_cv_overall_ranking);exit;
                    
                    $cv1_block_2_data = DB::table('tbl_cv_block_2_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_3_data = DB::table('tbl_cv_block_3_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_4_data = DB::table('tbl_cv_block_4_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_5_data = DB::table('tbl_cv_block_5_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_6_data = DB::table('tbl_cv_block_6_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_7_data = DB::table('tbl_cv_block_7_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_8_data = DB::table('tbl_cv_block_8_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_9_data = DB::table('tbl_cv_block_9_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_10_data = DB::table('tbl_cv_block_10_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_11_data = DB::table('tbl_cv_block_11_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_12_data = DB::table('tbl_cv_block_12_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_13_data = DB::table('tbl_cv_block_13_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_14_data = DB::table('tbl_cv_block_14_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_15_data = DB::table('tbl_cv_block_15_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_footer_template_data = DB::table('tbl_footer_template')->where('footer_template_id', '=', $cv1_data->footer_template_id)->first();

                    $cv1_block_16_mood_graph_data = DB::table('tbl_cv_block_16_mood_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_16_genre_graph_data = DB::table('tbl_cv_block_16_genre_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_17_mood_graph_data = DB::table('tbl_cv_block_17_mood_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_17_genre_graph_data = DB::table('tbl_cv_block_17_genre_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first(); 
                    $cv1_block_18_mood_graph_data = DB::table('tbl_cv_block_18_mood_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_18_genre_graph_data = DB::table('tbl_cv_block_18_genre_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_19_mood_graph_data = DB::table('tbl_cv_block_19_mood_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_19_genre_graph_data = DB::table('tbl_cv_block_19_genre_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_mood_aggr_graph_data = DB::table('tbl_mood_aggr_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_genre_aggr_graph_data = DB::table('tbl_genre_aggr_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    
                    $cv1_genre_aggr_graph_values_data = DB::table('tbl_genre_aggr_graph_data')->select('ambient','blues','classical','country','electronicDance','folk','indieAlternative','jazz','latin','metal','pop','punk','rapHipHop','reggae','rnb','rock','singerSongwriter')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_genre_aggr_graph_values_arr = (array)$cv1_genre_aggr_graph_values_data;
                    $cv1_genre_aggr_graph_values_arr1 = (array)$cv1_genre_aggr_graph_values_data;
                    rsort($cv1_genre_aggr_graph_values_arr);
                    $cv1_top3 = array_slice($cv1_genre_aggr_graph_values_arr, 0, 3);
                    $cv1_top_3_genre = array();        
                    foreach ($cv1_top3 as $cv1_key => $cv1_val) {
                        //echo "cv1_key-".$cv1_key."----------- cv1_val-".$cv1_val."<br>";
                        $cv1_key = array_search ($cv1_val, $cv1_genre_aggr_graph_values_arr1);
                        unset($cv1_genre_aggr_graph_values_arr1[$cv1_key]);
                        $cv1_top_3_genre[$cv1_key] = $cv1_val;
                    }
                    
                    if(count($cv1_top_3_genre)==0)
                    {
                        $cv1_top_3_genre = '';
                    }
                    
                    if(count($cv1_block_3_data)==0)
                    {
                        $cv1_block_3_data = '';
                        $cv1_music_taste_data = [];
                    }
                    else
                    {
                        $cv1_mti_ids_array = [];
                        for($cv1_mti = 0; $cv1_mti < count($cv1_block_3_data); $cv1_mti++)
                        {
                            array_push($cv1_mti_ids_array,$cv1_block_3_data[$cv1_mti]->b3_title_id);
                        }
                        if(count($cv1_mti_ids_array)>0)
                        {
                            $cv1_music_taste_data = DB::table('tbl_music_taste')
                                ->whereIn('music_taste_id', $cv1_mti_ids_array)
                                ->get();
                        }
                        else
                        {
                            $cv1_music_taste_data = [];
                        }
                    }   
                    if(count($cv1_music_taste_data)==0)
                    {
                        $cv1_music_taste_data = '';
                    }  
                    if(count($cv1_block_5_data)==0)
                    {
                        $cv1_block_5_data = '';
                    }
                    if(count($cv1_block_7_data)==0)
                    {
                        $cv1_block_7_data = '';
                    }
                    if(count($cv1_block_8_data)==0)
                    {
                        $cv1_block_8_data = '';
                    }
                    if(count($cv1_block_9_data)==0)
                    {
                        $cv1_block_9_data = '';
                    }
                    if(count($cv1_block_10_data)==0)
                    {
                        $cv1_block_10_data = '';
                        $cv1_qualitative_data = [];
                    }
                    else
                    {
                        $cv1_qlti_ids_array = [];
                        $cv1_qualitative_data = [];
                        for($cv1_qlti = 0; $cv1_qlti < count($cv1_block_10_data); $cv1_qlti++)
                        {
                            array_push($cv1_qlti_ids_array,$cv1_block_10_data[$cv1_qlti]->b10_name_id);
                            $cv1_qualitative_id_data = DB::table('tbl_qualitative')
                                ->where('qualitative_id', $cv1_block_10_data[$cv1_qlti]->b10_name_id)
                                ->first();
                            array_push($cv1_qualitative_data,$cv1_qualitative_id_data->qualitative_name);
                        }
                        /* if(count($cv1_qlti_ids_array)>0)
                        {
                            $cv1_qualitative_data = DB::table('tbl_qualitative')
                                ->whereIn('qualitative_id', $cv1_qlti_ids_array)
                                ->get();
                        }
                        else
                        {
                            $cv1_qualitative_data = [];
                        } */
                    }
                    if(count($cv1_qualitative_data)==0)
                    {
                        $cv1_qualitative_data = '';
                    }
                    if(count($cv1_block_11_data)==0)
                    {
                        $cv1_block_11_data = '';
                    }
                    if(count($cv1_block_12_data)==0)
                    {
                        $cv1_block_12_data = '';
                    }
                    if(count($cv1_block_13_data)==0)
                    {
                        $cv1_block_13_data = '';
                        $cv1_experience_data = [];
                        $cv1_experience_excluded_data = [];
                    }
                    else
                    {
                        $cv1_ei_ids_array = [];
                        for($cv1_ei = 0; $cv1_ei < count($cv1_block_13_data); $cv1_ei++)
                        {
                            array_push($cv1_ei_ids_array,$cv1_block_13_data[$cv1_ei]->b13_name_id);
                        }
                        if(count($cv1_ei_ids_array)>0)
                        {
                            $cv1_experience_data = DB::table('tbl_experience')
                                ->whereIn('experience_id', $cv1_ei_ids_array)
                                ->get();
                            $cv1_experience_excluded_data =  DB::table('tbl_experience')
                                ->whereNotIn('experience_id', $cv1_ei_ids_array)
                                ->where("is_active", '=', '0')
                                ->get();
                        }
                        else
                        {
                            $cv1_experience_data = [];
                            $cv1_experience_excluded_data = [];
                        }
                    }
                    if(count($cv1_experience_data)==0)
                    {
                        $cv1_experience_data = '';
                        $cv1_experience_excluded_data = '';
                    }
                    if(count($cv1_block_14_data)==0)
                    {
                        $cv1_block_14_data = '';
                    }
                    if(count($cv1_block_15_data)==0)
                    {
                        $cv1_block_15_data = '';
                    }

                    $cv2_data = DB::table('tbl_cvs')->where('cv_id', '=', base64_decode($request->compare_brand_cv_id))->first();

                    if($cv2_data->parent_id != '' && $cv2_data->parent_id != null)
                    {
                        $cv2_parent_cv = DB::table('tbl_cvs')->where('cv_id', '=', $cv2_data->parent_id)->where('status', '=', 1)->where('is_active', '=', 0)->first();           
                    }
                    else
                    {
                        $cv2_parent_cv = null;
                    }
                    if($cv2_parent_cv != null)
                    {
                        $cv2_parent_cv_overall_ranking = DB::table('tbl_cv_block_2_data')->where('cv_id', '=', $cv2_parent_cv->cv_id)->where('is_active', '=', 0)->first();
                        
                    }
                    else
                    {
                        $cv2_parent_cv_overall_ranking = ''; 
                    }
                    //print_r($parent_cv_overall_ranking);exit;
                    
                    $cv2_block_2_data = DB::table('tbl_cv_block_2_data')->where('cv_id', '=', $cv2_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv2_block_3_data = DB::table('tbl_cv_block_3_data')->where('cv_id', '=', $cv2_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv2_block_4_data = DB::table('tbl_cv_block_4_data')->where('cv_id', '=', $cv2_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv2_block_5_data = DB::table('tbl_cv_block_5_data')->where('cv_id', '=', $cv2_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv2_block_6_data = DB::table('tbl_cv_block_6_data')->where('cv_id', '=', $cv2_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv2_block_7_data = DB::table('tbl_cv_block_7_data')->where('cv_id', '=', $cv2_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv2_block_8_data = DB::table('tbl_cv_block_8_data')->where('cv_id', '=', $cv2_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv2_block_9_data = DB::table('tbl_cv_block_9_data')->where('cv_id', '=', $cv2_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv2_block_10_data = DB::table('tbl_cv_block_10_data')->where('cv_id', '=', $cv2_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv2_block_11_data = DB::table('tbl_cv_block_11_data')->where('cv_id', '=', $cv2_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv2_block_12_data = DB::table('tbl_cv_block_12_data')->where('cv_id', '=', $cv2_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv2_block_13_data = DB::table('tbl_cv_block_13_data')->where('cv_id', '=', $cv2_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv2_block_14_data = DB::table('tbl_cv_block_14_data')->where('cv_id', '=', $cv2_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv2_block_15_data = DB::table('tbl_cv_block_15_data')->where('cv_id', '=', $cv2_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv2_footer_template_data = DB::table('tbl_footer_template')->where('footer_template_id', '=', $cv2_data->footer_template_id)->first();

                    $cv2_block_16_mood_graph_data = DB::table('tbl_cv_block_16_mood_graph_data')->where('cv_id', '=', $cv2_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv2_block_16_genre_graph_data = DB::table('tbl_cv_block_16_genre_graph_data')->where('cv_id', '=', $cv2_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv2_block_17_mood_graph_data = DB::table('tbl_cv_block_17_mood_graph_data')->where('cv_id', '=', $cv2_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv2_block_17_genre_graph_data = DB::table('tbl_cv_block_17_genre_graph_data')->where('cv_id', '=', $cv2_data->cv_id)->where('is_active', '=', 0)->first(); 
                    $cv2_block_18_mood_graph_data = DB::table('tbl_cv_block_18_mood_graph_data')->where('cv_id', '=', $cv2_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv2_block_18_genre_graph_data = DB::table('tbl_cv_block_18_genre_graph_data')->where('cv_id', '=', $cv2_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv2_block_19_mood_graph_data = DB::table('tbl_cv_block_19_mood_graph_data')->where('cv_id', '=', $cv2_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv2_block_19_genre_graph_data = DB::table('tbl_cv_block_19_genre_graph_data')->where('cv_id', '=', $cv2_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv2_mood_aggr_graph_data = DB::table('tbl_mood_aggr_graph_data')->where('cv_id', '=', $cv2_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv2_genre_aggr_graph_data = DB::table('tbl_genre_aggr_graph_data')->where('cv_id', '=', $cv2_data->cv_id)->where('is_active', '=', 0)->first();
                    
                    $cv2_genre_aggr_graph_values_data = DB::table('tbl_genre_aggr_graph_data')->select('ambient','blues','classical','country','electronicDance','folk','indieAlternative','jazz','latin','metal','pop','punk','rapHipHop','reggae','rnb','rock','singerSongwriter')->where('cv_id', '=', $cv2_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv2_genre_aggr_graph_values_arr = (array)$cv2_genre_aggr_graph_values_data;
                    $cv2_genre_aggr_graph_values_arr1 = (array)$cv2_genre_aggr_graph_values_data;
                    rsort($cv2_genre_aggr_graph_values_arr);
                    $cv2_top3 = array_slice($cv2_genre_aggr_graph_values_arr, 0, 3);
                    $cv2_top_3_genre = array();        
                    foreach ($cv2_top3 as $cv2_key => $cv2_val) {
                        //echo "cv2_key-".$cv2_key."----------- cv2_val-".$cv2_val."<br>";
                        $cv2_key = array_search ($cv2_val, $cv2_genre_aggr_graph_values_arr1);
                        unset($cv2_genre_aggr_graph_values_arr1[$cv2_key]);
                        $cv2_top_3_genre[$cv2_key] = $cv2_val;
                    }
                    
                    if(count($cv2_top_3_genre)==0)
                    {
                        $cv2_top_3_genre = '';
                    }
                    
                    if(count($cv2_block_3_data)==0)
                    {
                        $cv2_block_3_data = '';
                        $cv2_music_taste_data = [];
                    }
                    else
                    {
                        $cv2_mti_ids_array = [];
                        for($cv2_mti = 0; $cv2_mti < count($cv2_block_3_data); $cv2_mti++)
                        {
                            array_push($cv2_mti_ids_array,$cv2_block_3_data[$cv2_mti]->b3_title_id);
                        }
                        if(count($cv2_mti_ids_array)>0)
                        {
                            $cv2_music_taste_data = DB::table('tbl_music_taste')
                                ->whereIn('music_taste_id', $cv2_mti_ids_array)
                                ->get();
                        }
                        else
                        {
                            $cv2_music_taste_data = [];
                        }
                    }   
                    if(count($cv2_music_taste_data)==0)
                    {
                        $cv2_music_taste_data = '';
                    }  
                    if(count($cv2_block_5_data)==0)
                    {
                        $cv2_block_5_data = '';
                    }
                    if(count($cv2_block_7_data)==0)
                    {
                        $cv2_block_7_data = '';
                    }
                    if(count($cv2_block_8_data)==0)
                    {
                        $cv2_block_8_data = '';
                    }
                    if(count($cv2_block_9_data)==0)
                    {
                        $cv2_block_9_data = '';
                    }
                    if(count($cv2_block_10_data)==0)
                    {
                        $cv2_block_10_data = '';
                        $cv2_qualitative_data = [];
                    }
                    else
                    {
                        $cv2_qlti_ids_array = [];
                        $cv2_qualitative_data = [];
                        for($cv2_qlti = 0; $cv2_qlti < count($cv2_block_10_data); $cv2_qlti++)
                        {
                            array_push($cv2_qlti_ids_array,$cv2_block_10_data[$cv2_qlti]->b10_name_id);
                            $cv2_qualitative_id_data = DB::table('tbl_qualitative')
                                ->where('qualitative_id', $cv2_block_10_data[$cv2_qlti]->b10_name_id)
                                ->first();
                            array_push($cv2_qualitative_data,$cv2_qualitative_id_data->qualitative_name);
                        }
                        /* if(count($cv2_qlti_ids_array)>0)
                        {
                            $cv2_qualitative_data = DB::table('tbl_qualitative')
                                ->whereIn('qualitative_id', $cv2_qlti_ids_array)
                                ->get();
                        }
                        else
                        {
                            $cv2_qualitative_data = [];
                        } */
                    }
                    if(count($cv2_qualitative_data)==0)
                    {
                        $cv2_qualitative_data = '';
                    }
                    if(count($cv2_block_11_data)==0)
                    {
                        $cv2_block_11_data = '';
                    }
                    if(count($cv2_block_12_data)==0)
                    {
                        $cv2_block_12_data = '';
                    }
                    if(count($cv2_block_13_data)==0)
                    {
                        $cv2_block_13_data = '';
                        $cv2_experience_data = [];
                        $cv2_experience_excluded_data = [];
                    }
                    else
                    {
                        $cv2_ei_ids_array = [];
                        for($cv2_ei = 0; $cv2_ei < count($cv2_block_13_data); $cv2_ei++)
                        {
                            array_push($cv2_ei_ids_array,$cv2_block_13_data[$cv2_ei]->b13_name_id);
                        }
                        if(count($cv2_ei_ids_array)>0)
                        {
                            $cv2_experience_data = DB::table('tbl_experience')
                                ->whereIn('experience_id', $cv2_ei_ids_array)
                                ->get();
                            $cv2_experience_excluded_data =  DB::table('tbl_experience')
                                ->whereNotIn('experience_id', $cv2_ei_ids_array)
                                ->where("is_active", '=', '0')
                                ->get();
                        }
                        else
                        {
                            $cv2_experience_data = [];
                            $cv2_experience_excluded_data = [];
                        }
                    }
                    if(count($cv2_experience_data)==0)
                    {
                        $cv2_experience_data = '';
                        $cv2_experience_excluded_data = '';
                    }
                    if(count($cv2_block_14_data)==0)
                    {
                        $cv2_block_14_data = '';
                    }
                    if(count($cv2_block_15_data)==0)
                    {
                        $cv2_block_15_data = '';
                    }
                    return view('backend.views.compared_cv', ['compare_type'=>$request->compare_option, 'cv1_data'=>$cv1_data, 'cv1_footer_template_data'=>$cv1_footer_template_data, 'cv1_block_2_data'=>$cv1_block_2_data, 'cv1_parent_cv'=>$cv1_parent_cv, 'cv1_parent_cv_overall_ranking'=>$cv1_parent_cv_overall_ranking, 'cv1_block_3_data'=>$cv1_block_3_data, 'cv1_music_taste_data'=>$cv1_music_taste_data, 'cv1_block_4_data'=>$cv1_block_4_data, 'cv1_block_5_data'=>$cv1_block_5_data, 'cv1_block_6_data'=>$cv1_block_6_data, 'cv1_block_7_data'=>$cv1_block_7_data, 'cv1_block_8_data'=>$cv1_block_8_data, 'cv1_block_9_data'=>$cv1_block_9_data, 'cv1_block_10_data'=>$cv1_block_10_data, 'cv1_qualitative_data'=>$cv1_qualitative_data, 'cv1_block_11_data'=>$cv1_block_11_data, 'cv1_block_12_data'=>$cv1_block_12_data, 'cv1_block_13_data'=>$cv1_block_13_data, 'cv1_experience_data'=>$cv1_experience_data, 'cv1_experience_excluded_data'=>$cv1_experience_excluded_data, 'cv1_block_14_data'=>$cv1_block_14_data, 'cv1_block_15_data'=>$cv1_block_15_data,'cv1_block_16_mood_graph_data'=>$cv1_block_16_mood_graph_data, 'cv1_block_16_genre_graph_data'=>$cv1_block_16_genre_graph_data, 'cv1_block_17_mood_graph_data'=>$cv1_block_17_mood_graph_data, 'cv1_block_17_genre_graph_data'=>$cv1_block_17_genre_graph_data, 'cv1_block_18_mood_graph_data'=>$cv1_block_18_mood_graph_data, 'cv1_block_18_genre_graph_data'=>$cv1_block_18_genre_graph_data, 'cv1_block_19_mood_graph_data'=>$cv1_block_19_mood_graph_data, 'cv1_block_19_genre_graph_data'=>$cv1_block_19_genre_graph_data,'cv1_mood_aggr_graph_data'=>$cv1_mood_aggr_graph_data, 'cv1_genre_aggr_graph_data'=>$cv1_genre_aggr_graph_data, 'cv1_top_3_genre'=>$cv1_top_3_genre, 'cv2_data'=>$cv2_data, 'cv2_footer_template_data'=>$cv2_footer_template_data, 'cv2_block_2_data'=>$cv2_block_2_data, 'cv2_parent_cv'=>$cv2_parent_cv, 'cv2_parent_cv_overall_ranking'=>$cv2_parent_cv_overall_ranking, 'cv2_block_3_data'=>$cv2_block_3_data, 'cv2_music_taste_data'=>$cv2_music_taste_data, 'cv2_block_4_data'=>$cv2_block_4_data, 'cv2_block_5_data'=>$cv2_block_5_data, 'cv2_block_6_data'=>$cv2_block_6_data, 'cv2_block_7_data'=>$cv2_block_7_data, 'cv2_block_8_data'=>$cv2_block_8_data, 'cv2_block_9_data'=>$cv2_block_9_data, 'cv2_block_10_data'=>$cv2_block_10_data, 'cv2_qualitative_data'=>$cv2_qualitative_data, 'cv2_block_11_data'=>$cv2_block_11_data, 'cv2_block_12_data'=>$cv2_block_12_data, 'cv2_block_13_data'=>$cv2_block_13_data, 'cv2_experience_excluded_data'=>$cv2_experience_excluded_data, 'cv2_experience_data'=>$cv2_experience_data, 'cv2_block_14_data'=>$cv2_block_14_data, 'cv2_block_15_data'=>$cv2_block_15_data,'cv2_block_16_mood_graph_data'=>$cv2_block_16_mood_graph_data, 'cv2_block_16_genre_graph_data'=>$cv2_block_16_genre_graph_data, 'cv2_block_17_mood_graph_data'=>$cv2_block_17_mood_graph_data, 'cv2_block_17_genre_graph_data'=>$cv2_block_17_genre_graph_data, 'cv2_block_18_mood_graph_data'=>$cv2_block_18_mood_graph_data, 'cv2_block_18_genre_graph_data'=>$cv2_block_18_genre_graph_data, 'cv2_block_19_mood_graph_data'=>$cv2_block_19_mood_graph_data, 'cv2_block_19_genre_graph_data'=>$cv2_block_19_genre_graph_data,'cv2_mood_aggr_graph_data'=>$cv2_mood_aggr_graph_data, 'cv2_genre_aggr_graph_data'=>$cv2_genre_aggr_graph_data, 'cv2_top_3_genre'=>$cv2_top_3_genre]);
                }
                else
                {
                    return back()->with('fail', 'Something went wrong, please try again!');
                }
            }
            else
            {
                $request->validate([
                    "compare_option"=>'required',
                    "compare_industry_cv_id"=>'required|not_in:0'
                ]);
                if($request->cv_id != '' && $request->compare_industry_cv_id != '' && $request->compare_sub_industry_cv_id == '')
                {
                    $cv1_data = DB::table('tbl_cvs')->where('cv_id', '=', base64_decode($request->cv_id))->first();

                    if($cv1_data->parent_id != '' && $cv1_data->parent_id != null)
                    {
                        $cv1_parent_cv = DB::table('tbl_cvs')->where('cv_id', '=', $cv1_data->parent_id)->where('status', '=', 1)->where('is_active', '=', 0)->first();           
                    }
                    else
                    {
                        $cv1_parent_cv = null;
                    }
                    if($cv1_parent_cv != null)
                    {
                        $cv1_parent_cv_overall_ranking = DB::table('tbl_cv_block_2_data')->where('cv_id', '=', $cv1_parent_cv->cv_id)->where('is_active', '=', 0)->first();
                        
                    }
                    else
                    {
                        $cv1_parent_cv_overall_ranking = ''; 
                    }
                    //print_r($parent_cv_overall_ranking);exit;
                    
                    $cv1_block_2_data = DB::table('tbl_cv_block_2_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_3_data = DB::table('tbl_cv_block_3_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_4_data = DB::table('tbl_cv_block_4_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_5_data = DB::table('tbl_cv_block_5_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_6_data = DB::table('tbl_cv_block_6_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_7_data = DB::table('tbl_cv_block_7_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_8_data = DB::table('tbl_cv_block_8_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_9_data = DB::table('tbl_cv_block_9_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_10_data = DB::table('tbl_cv_block_10_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_11_data = DB::table('tbl_cv_block_11_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_12_data = DB::table('tbl_cv_block_12_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_13_data = DB::table('tbl_cv_block_13_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_14_data = DB::table('tbl_cv_block_14_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_15_data = DB::table('tbl_cv_block_15_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();

                    $cv1_block_16_mood_graph_data = DB::table('tbl_cv_block_16_mood_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_16_genre_graph_data = DB::table('tbl_cv_block_16_genre_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_17_mood_graph_data = DB::table('tbl_cv_block_17_mood_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_17_genre_graph_data = DB::table('tbl_cv_block_17_genre_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first(); 
                    $cv1_block_18_mood_graph_data = DB::table('tbl_cv_block_18_mood_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_18_genre_graph_data = DB::table('tbl_cv_block_18_genre_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_19_mood_graph_data = DB::table('tbl_cv_block_19_mood_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_19_genre_graph_data = DB::table('tbl_cv_block_19_genre_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_mood_aggr_graph_data = DB::table('tbl_mood_aggr_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_genre_aggr_graph_data = DB::table('tbl_genre_aggr_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_footer_template_data = DB::table('tbl_footer_template')->where('footer_template_id', '=', $cv1_data->footer_template_id)->first();

                    if(count($cv1_block_3_data)==0)
                    {
                        $cv1_block_3_data = '';
                        $cv1_music_taste_data = [];
                    }
                    else
                    {
                        $cv1_mti_ids_array = [];
                        for($cv1_mti = 0; $cv1_mti < count($cv1_block_3_data); $cv1_mti++)
                        {
                            array_push($cv1_mti_ids_array,$cv1_block_3_data[$cv1_mti]->b3_title_id);
                        }
                        if(count($cv1_mti_ids_array)>0)
                        {
                            $cv1_music_taste_data = DB::table('tbl_music_taste')
                                ->whereIn('music_taste_id', $cv1_mti_ids_array)
                                ->get();
                        }
                        else
                        {
                            $cv1_music_taste_data = [];
                        }
                    }   
                    if(count($cv1_music_taste_data)==0)
                    {
                        $cv1_music_taste_data = '';
                    }  
                    if(count($cv1_block_5_data)==0)
                    {
                        $cv1_block_5_data = '';
                    }
                    if(count($cv1_block_7_data)==0)
                    {
                        $cv1_block_7_data = '';
                    }
                    if(count($cv1_block_8_data)==0)
                    {
                        $cv1_block_8_data = '';
                    }
                    if(count($cv1_block_9_data)==0)
                    {
                        $cv1_block_9_data = '';
                    }
                    if(count($cv1_block_10_data)==0)
                    {
                        $cv1_block_10_data = '';
                        $cv1_qualitative_data = [];
                    }
                    else
                    {
                        $cv1_qlti_ids_array = [];
                        for($cv1_qlti = 0; $cv1_qlti < count($cv1_block_10_data); $cv1_qlti++)
                        {
                            array_push($cv1_qlti_ids_array,$cv1_block_10_data[$cv1_qlti]->b10_name_id);
                        }
                        if(count($cv1_qlti_ids_array)>0)
                        {
                            $cv1_qualitative_data = DB::table('tbl_qualitative')
                                ->whereIn('qualitative_id', $cv1_qlti_ids_array)
                                ->get();
                        }
                        else
                        {
                            $cv1_qualitative_data = [];
                        }
                    }
                    if(count($cv1_qualitative_data)==0)
                    {
                        $cv1_qualitative_data = '';
                    }
                    if(count($cv1_block_11_data)==0)
                    {
                        $cv1_block_11_data = '';
                    }
                    if(count($cv1_block_12_data)==0)
                    {
                        $cv1_block_12_data = '';
                    }
                    if(count($cv1_block_13_data)==0)
                    {
                        $cv1_block_13_data = '';
                        $cv1_experience_data = [];
                        $cv1_experience_excluded_data = [];
                    }
                    else
                    {
                        $cv1_ei_ids_array = [];
                        for($cv1_ei = 0; $cv1_ei < count($cv1_block_13_data); $cv1_ei++)
                        {
                            array_push($cv1_ei_ids_array,$cv1_block_13_data[$cv1_ei]->b13_name_id);
                        }
                        if(count($cv1_ei_ids_array)>0)
                        {
                            $cv1_experience_data = DB::table('tbl_experience')
                                ->whereIn('experience_id', $cv1_ei_ids_array)
                                ->get();
                            $cv1_experience_excluded_data =  DB::table('tbl_experience')
                                ->whereNotIn('experience_id', $cv1_ei_ids_array)
                                ->where("is_active", '=', '0')
                                ->get();
                        }
                        else
                        {
                            $cv1_experience_data = [];
                            $cv1_experience_excluded_data = [];
                        }
                    }
                    if(count($cv1_experience_data)==0)
                    {
                        $cv1_experience_data = '';
                        $cv1_experience_excluded_data = '';
                    }
                    if(count($cv1_block_14_data)==0)
                    {
                        $cv1_block_14_data = '';
                    }
                    if(count($cv1_block_15_data)==0)
                    {
                        $cv1_block_15_data = '';
                    }

                    /* if(count($cv1_block_16_mood_graph_data)==0)
                    {
                        $cv1_block_16_mood_graph_data = '';
                    }
                    if(count($cv1_block_16_genre_graph_data)==0)
                    {
                        $cv1_block_16_genre_graph_data = '';
                    } */
                    /* if(count($cv1_block_17_mood_graph_data)==0)
                    {
                        $cv1_block_17_mood_graph_data = '';
                    }
                    if(count($cv1_block_17_genre_graph_data)==0)
                    {
                        $cv1_block_17_genre_graph_data = '';
                    } */
                    /* if(count($cv1_block_18_mood_graph_data)==0)
                    {
                        $cv1_block_18_mood_graph_data = '';
                    }
                    if(count($cv1_block_18_genre_graph_data)==0)
                    {
                        $cv1_block_18_genre_graph_data = '';
                    } */
                    /* if(count($cv1_mood_aggr_graph_data)==0)
                    {
                        $cv1_mood_aggr_graph_data = '';
                    }
                    if(count($cv1_genre_aggr_graph_data)==0)
                    {
                        $cv1_genre_aggr_graph_data = '';
                    } */

                    //Industry
                    $industry_data = DB::table('tbl_industry')->where('industry_id', '=', base64_decode($request->compare_industry_cv_id))->first();
                    $industrywise_cv_data = DB::table('tbl_cvs')->where('industry_id', '=', base64_decode($request->compare_industry_cv_id))->where('status', '=', 1)->where('is_active', '=', 0)->get();
                    
                    if(count($industrywise_cv_data)==0)
                    {
                        $industrywise_cv_data = ''; 
                        $most_popular_genres_data_array = '';   
                        $music_expenditure_per_year_array = '';  
                        $music_expenditure_per_video_array = '';      
                    }
                    else
                    {
                        $industrywise_cv_id_array = [];
                        foreach($industrywise_cv_data as $icdata)
                        {
                            array_push($industrywise_cv_id_array,$icdata->cv_id);
                        }
                        //print_r($industrywise_cv_id_array);
                        $get_most_popular_genres_id = DB::table('tbl_cv_block_3_data')->whereIn('cv_id', $industrywise_cv_id_array)->where('is_active','=',0)->get();
                        
                        $get_most_popular_genres_id_array = [];
                        foreach($get_most_popular_genres_id as $data)
                        {
                            array_push($get_most_popular_genres_id_array, $data->b3_title_id);
                        }
                        //print_r($get_most_popular_genres_id_array);
                        $most_popular_genres_data_array = [];
                        
                        foreach(array_count_values($get_most_popular_genres_id_array) as $key=>$value)
                        {
                            $temp_array = array_count_values($get_most_popular_genres_id_array);
                            //Arr::pull($temp_array,$key);
                            //$get_most_popular_genres_id_array_without_current_id = array_diff(array_count_values($get_most_popular_genres_id_array), array($value));
                            $get_most_popular_genres_id_array_without_current_id = $temp_array;
                            $current_genres_percentage = number_format($value * 100 / array_sum(array_count_values($get_most_popular_genres_id_array)),1);                
                            //echo $key."-------".$value."--------".array_sum($get_most_popular_genres_id_array_without_current_id)."--------".$current_genres_percentage."<br><br>";
                            $get_most_popular_genres_data = DB::table('tbl_music_taste')->where('music_taste_id', '=', $key)->first();
                            $most_popular_genres_data_for_array = ['music_taste_name'=> $get_most_popular_genres_data->music_taste_name, 'music_taste_icon_name'=> $get_most_popular_genres_data->music_taste_icon_name, 'music_taste_percentage'=>$current_genres_percentage];
                            array_push($most_popular_genres_data_array,$most_popular_genres_data_for_array);
                        }    
                        
                        $cv_ids_array = [];
                
                        foreach($industrywise_cv_data as $cv_items)
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

                        $get_music_expenditure_per_year_id = DB::table('tbl_cv_block_14_data')->whereIn('cv_id', $industrywise_cv_id_array)->where('is_active', '=', 0)->whereNotNull('b14_number')->get();
                        $music_expenditure_per_year_array = [];
                        foreach($get_music_expenditure_per_year_id as $data)
                        {
                            //echo $data->b14_number."<br><br>";
                            if($data->b14_number != '' && $data->b14_number != null)
                            {
                                array_push($music_expenditure_per_year_array, $data->b14_number);
                            }                
                        }
                        //echo array_sum($music_expenditure_per_year_array);
                        if(count($music_expenditure_per_year_array)==0)
                        {
                            $music_expenditure_per_year_array = '';
                        }

                        $get_music_expenditure_per_video_id = DB::table('tbl_cv_block_15_data')->whereIn('cv_id', $industrywise_cv_id_array)->where('is_active', '=', 0)->whereNotNull('b15_number')->get();
                        $music_expenditure_per_video_array = [];
                        foreach($get_music_expenditure_per_video_id as $data)
                        {
                            //echo $data->b15_number."<br><br>";
                            if($data->b15_number != '' && $data->b15_number != null)
                            {
                                array_push($music_expenditure_per_video_array, $data->b15_number);
                            }                
                        }
                        //echo array_sum($music_expenditure_per_video_array);
                        if(count($music_expenditure_per_video_array)==0)
                        {
                            $music_expenditure_per_video_array = '';
                        }
                        //print_r($music_expenditure_per_video_array);
                        
                        $music_types_usage_on_average_data = DB::table('tbl_cv_block_13_data')->whereIn('cv_id', $industrywise_cv_id_array)->where('is_active', '=', 0)->get();
                        if(count($music_types_usage_on_average_data)==0)
                        {
                            $music_types_usage_on_average_data = '';
                            $experience_data = [];
                            $experience_excluded_data = [];
                        }
                        else
                        {
                            $ei_ids_array = [];
                            for($ei = 0; $ei < count($music_types_usage_on_average_data); $ei++)
                            {
                                if($music_types_usage_on_average_data[$ei]->b13_name_id != '' && $music_types_usage_on_average_data[$ei]->b13_name_id != null && $music_types_usage_on_average_data[$ei]->b13_name_id != 0 && $music_types_usage_on_average_data[$ei]->b13_number != '' && $music_types_usage_on_average_data[$ei]->b13_number != null)
                                {
                                    array_push($ei_ids_array,$music_types_usage_on_average_data[$ei]->b13_name_id);
                                } 
                                                
                            }
                            $b13id_array = [];
                            foreach($music_types_usage_on_average_data as $cvb13key => $cvb13data)
                            { 
                                array_push($b13id_array,$cvb13data->b13_id);                                                        
                            }
                            //print_r($b13id_array);
                            $number_sum_data_array = [];
                            foreach(array_unique($ei_ids_array) as $eiid_data)
                            {
                                $number_sum_data = 0;
                                $get_number = DB::table('tbl_cv_block_13_data')->where('b13_name_id', $eiid_data)->where('is_active', '=', 0)->get();
                                foreach($get_number as $number_data)
                                {
                                    if(in_array($number_data->b13_id,$b13id_array))
                                    {
                                        $number_sum_data = $number_sum_data+$number_data->b13_number;
                                    }
                                }
                                
                                $experience_data = DB::table('tbl_experience')
                                    ->where('experience_id', "=", $eiid_data)
                                    ->first();
                                $number_sum_data_array[$eiid_data] = $number_sum_data."$#$".$experience_data->experience_name;
                                //array_push($number_sum_data_array, [$eiid_data => $number_sum_data]);
                            
                            }
                            //print_r($number_sum_data_array);
                            if(count($ei_ids_array)>0)
                            {
                                $experience_data = DB::table('tbl_experience')
                                    ->whereIn('experience_id', $ei_ids_array)
                                    ->get();
                                $experience_excluded_data = DB::table('tbl_experience')
                                ->whereNotIn('experience_id', $ei_ids_array)
                                ->where("is_active", '=', '0')
                                ->get();
                            }
                            else
                            {
                                $experience_data = [];
                                $experience_excluded_data = [];
                            }
                        }           
                    }
                    $all_published_cv_data = DB::table('tbl_cvs')->where('status', '=', 1)->where('is_active', '=', 0)->get();
                    if(count($all_published_cv_data)==0)
                    {
                        $all_published_cv_data = ''; 
                        $all_published_cv_music_expenditure_per_year_array = '';  
                        $all_published_cv_music_expenditure_per_video_array = '';      
                    }
                    else
                    {
                        $all_published_cv_id_array = [];
                        foreach($all_published_cv_data as $pcvdata)
                        {
                            array_push($all_published_cv_id_array,$pcvdata->cv_id);
                        }
                        
                        $get_all_published_cv_music_expenditure_per_year_id = DB::table('tbl_cv_block_14_data')->whereIn('cv_id', $all_published_cv_id_array)->where('is_active', '=', 0)->whereNotNull('b14_number')->get();
                        $all_published_cv_music_expenditure_per_year_array = [];
                        foreach($get_all_published_cv_music_expenditure_per_year_id as $data)
                        {
                            //echo $data->b14_number."<br><br>";
                            if($data->b14_number != '' && $data->b14_number != null)
                            {
                                array_push($all_published_cv_music_expenditure_per_year_array, $data->b14_number);
                            }                
                        }
                        //echo array_sum($all_published_cv_music_expenditure_per_year_array);
                        if(count($all_published_cv_music_expenditure_per_year_array)==0)
                        {
                            $all_published_cv_music_expenditure_per_year_array = '';
                        }

                        $get_all_published_cv_music_expenditure_per_video_id = DB::table('tbl_cv_block_15_data')->whereIn('cv_id', $all_published_cv_id_array)->where('is_active', '=', 0)->whereNotNull('b15_number')->get();
                        $all_published_cv_music_expenditure_per_video_array = [];
                        foreach($get_all_published_cv_music_expenditure_per_video_id as $data)
                        {
                            //echo $data->b15_number."<br><br>";
                            if($data->b15_number != '' && $data->b15_number != null)
                            {
                                array_push($all_published_cv_music_expenditure_per_video_array, $data->b15_number);
                            }                
                        }
                        //echo array_sum($all_published_cv_music_expenditure_per_video_array);
                        if(count($all_published_cv_music_expenditure_per_video_array)==0)
                        {
                            $all_published_cv_music_expenditure_per_video_array = '';
                        }
                        //print_r($all_published_cv_music_expenditure_per_video_array);           
                    }                    
                    
                    $industry_youtube_mood_graph_data = DB::table('tbl_industry_youtube_mood_graph_data')->where('ind_id', '=', base64_decode($request->compare_industry_cv_id))->where('is_active', '=', 0)->first();
                    $industry_youtube_genre_graph_data = DB::table('tbl_industry_youtube_genre_graph_data')->where('ind_id', '=', base64_decode($request->compare_industry_cv_id))->where('is_active', '=', 0)->first();
                    $industry_instagram_mood_graph_data = DB::table('tbl_industry_instagram_mood_graph_data')->where('ind_id', '=', base64_decode($request->compare_industry_cv_id))->where('is_active', '=', 0)->first();
                    $industry_instagram_genre_graph_data = DB::table('tbl_industry_instagram_genre_graph_data')->where('ind_id', '=', base64_decode($request->compare_industry_cv_id))->where('is_active', '=', 0)->first(); 
                    $industry_tiktok_mood_graph_data = DB::table('tbl_industry_tiktok_mood_graph_data')->where('ind_id', '=', base64_decode($request->compare_industry_cv_id))->where('is_active', '=', 0)->first();
                    $industry_tiktok_genre_graph_data = DB::table('tbl_industry_tiktok_genre_graph_data')->where('ind_id', '=', base64_decode($request->compare_industry_cv_id))->where('is_active', '=', 0)->first();
                    $industry_twitter_mood_graph_data = DB::table('tbl_industry_twitter_mood_graph_data')->where('ind_id', '=', base64_decode($request->compare_industry_cv_id))->where('is_active', '=', 0)->first();
                    $industry_twitter_genre_graph_data = DB::table('tbl_industry_twitter_genre_graph_data')->where('ind_id', '=', base64_decode($request->compare_industry_cv_id))->where('is_active', '=', 0)->first();
                    $industry_mood_aggr_graph_data = DB::table('tbl_industry_mood_aggr_graph_data')->where('ind_id', '=', base64_decode($request->compare_industry_cv_id))->where('is_active', '=', 0)->first();
                    $industry_genre_aggr_graph_data = DB::table('tbl_industry_genre_aggr_graph_data')->where('ind_id', '=', base64_decode($request->compare_industry_cv_id))->where('is_active', '=', 0)->first();
                    
                    /* if(count($industry_youtube_mood_graph_data)==0)
                    {
                        $industry_youtube_mood_graph_data = '';
                    }
                    if(count($industry_youtube_genre_graph_data)==0)
                    {
                        $industry_youtube_genre_graph_data = '';
                    } */
                    /* if(count($industry_instagram_mood_graph_data)==0)
                    {
                        $industry_instagram_mood_graph_data = '';
                    }
                    if(count($industry_instagram_genre_graph_data)==0)
                    {
                        $industry_instagram_genre_graph_data = '';
                    } */
                    /* if(count($industry_tiktok_mood_graph_data)==0)
                    {
                        $industry_tiktok_mood_graph_data = '';
                    }
                    if(count($industry_tiktok_genre_graph_data)==0)
                    {
                        $industry_tiktok_genre_graph_data = '';
                    } */
                    /* if(count($industry_mood_aggr_graph_data)==0)
                    {
                        $industry_mood_aggr_graph_data = '';
                    }
                    if(count($industry_genre_aggr_graph_data)==0)
                    {
                        $industry_genre_aggr_graph_data = '';
                    } */
                    return view('backend.views.compared_cv', ['compare_type'=>$request->compare_option, 'cv1_data'=>$cv1_data, 'cv1_footer_template_data'=>$cv1_footer_template_data, 'cv1_block_2_data'=>$cv1_block_2_data, 'cv1_parent_cv_overall_ranking'=>$cv1_parent_cv_overall_ranking, 'cv1_block_3_data'=>$cv1_block_3_data, 'cv1_music_taste_data'=>$cv1_music_taste_data, 'cv1_block_4_data'=>$cv1_block_4_data, 'cv1_block_5_data'=>$cv1_block_5_data, 'cv1_block_6_data'=>$cv1_block_6_data, 'cv1_block_7_data'=>$cv1_block_7_data, 'cv1_block_8_data'=>$cv1_block_8_data, 'cv1_block_9_data'=>$cv1_block_9_data, 'cv1_block_10_data'=>$cv1_block_10_data, 'cv1_qualitative_data'=>$cv1_qualitative_data, 'cv1_block_11_data'=>$cv1_block_11_data, 'cv1_block_12_data'=>$cv1_block_12_data, 'cv1_block_13_data'=>$cv1_block_13_data, 'cv1_experience_data'=>$cv1_experience_data, 'cv1_experience_excluded_data'=>$cv1_experience_excluded_data, 'cv1_block_14_data'=>$cv1_block_14_data, 'cv1_block_15_data'=>$cv1_block_15_data,'cv1_block_16_mood_graph_data'=>$cv1_block_16_mood_graph_data, 'cv1_block_16_genre_graph_data'=>$cv1_block_16_genre_graph_data, 'cv1_block_17_mood_graph_data'=>$cv1_block_17_mood_graph_data, 'cv1_block_17_genre_graph_data'=>$cv1_block_17_genre_graph_data, 'cv1_block_18_mood_graph_data'=>$cv1_block_18_mood_graph_data, 'cv1_block_18_genre_graph_data'=>$cv1_block_18_genre_graph_data, 'cv1_block_19_mood_graph_data'=>$cv1_block_19_mood_graph_data, 'cv1_block_19_genre_graph_data'=>$cv1_block_19_genre_graph_data,'cv1_mood_aggr_graph_data'=>$cv1_mood_aggr_graph_data, 'cv1_genre_aggr_graph_data'=>$cv1_genre_aggr_graph_data, 'industry_data'=>$industry_data,'industrywise_cv_data'=>$industrywise_cv_data,'most_popular_genres_data_array'=>$most_popular_genres_data_array,'music_expenditure_per_year_array'=>$music_expenditure_per_year_array,'all_published_cv_music_expenditure_per_year_array'=>$all_published_cv_music_expenditure_per_year_array,'music_expenditure_per_video_array'=>$music_expenditure_per_video_array,'all_published_cv_music_expenditure_per_video_array'=>$all_published_cv_music_expenditure_per_video_array,'music_types_usage_on_average_data'=>$music_types_usage_on_average_data,'experience_data'=>$experience_data,'experience_excluded_data'=>$experience_excluded_data,'ei_ids_array'=>$ei_ids_array,'number_sum_data_array'=>$number_sum_data_array,'insudtry_yes_avg_data_array'=>$insudtry_yes_avg_data_array,'insudtry_no_avg_data_array'=>$insudtry_no_avg_data_array,'industry_youtube_mood_graph_data'=>$industry_youtube_mood_graph_data,'industry_youtube_genre_graph_data'=>$industry_youtube_genre_graph_data, 'industry_instagram_mood_graph_data'=>$industry_instagram_mood_graph_data, 'industry_instagram_genre_graph_data'=>$industry_instagram_genre_graph_data, 'industry_tiktok_mood_graph_data'=>$industry_tiktok_mood_graph_data, 'industry_tiktok_genre_graph_data'=>$industry_tiktok_genre_graph_data, 'industry_twitter_mood_graph_data'=>$industry_twitter_mood_graph_data, 'industry_twitter_genre_graph_data'=>$industry_twitter_genre_graph_data,'industry_mood_aggr_graph_data'=>$industry_mood_aggr_graph_data, 'industry_genre_aggr_graph_data'=>$industry_genre_aggr_graph_data]);
                    //return view('backend.views.compared_cv', ['compare_type'=>$request->compare_cv, 'cv1_data'=>$cv1_data, 'cv1_footer_template_data'=>$cv1_footer_template_data, 'cv1_block_2_data'=>$cv1_block_2_data, 'cv1_parent_cv_overall_ranking'=>$cv1_parent_cv_overall_ranking, 'cv1_block_3_data'=>$cv1_block_3_data, 'cv1_music_taste_data'=>$cv1_music_taste_data, 'cv1_block_4_data'=>$cv1_block_4_data, 'cv1_block_5_data'=>$cv1_block_5_data, 'cv1_block_6_data'=>$cv1_block_6_data, 'cv1_block_7_data'=>$cv1_block_7_data, 'cv1_block_8_data'=>$cv1_block_8_data, 'cv1_block_9_data'=>$cv1_block_9_data, 'cv1_block_10_data'=>$cv1_block_10_data, 'cv1_qualitative_data'=>$cv1_qualitative_data, 'cv1_block_11_data'=>$cv1_block_11_data, 'cv1_block_12_data'=>$cv1_block_12_data, 'cv1_block_13_data'=>$cv1_block_13_data, 'cv1_experience_data'=>$cv1_experience_data, 'cv1_block_14_data'=>$cv1_block_14_data, 'cv1_block_15_data'=>$cv1_block_15_data, 'industry_data'=>$industry_data,'cv_letters_AtoZ_array'=>$cv_letters_AtoZ_array,'cv_data_json'=>$cv_data_json,'industry_cv_data'=>$industry_cv_data, 'insudtry_yes_avg_data_array'=>$insudtry_yes_avg_data_array, 'insudtry_no_avg_data_array'=>$insudtry_no_avg_data_array]);
                }
                
                if($request->cv_id != '' && $request->compare_industry_cv_id != '' && $request->compare_sub_industry_cv_id != '')
                {
                    $cv1_data = DB::table('tbl_cvs')->where('cv_id', '=', base64_decode($request->cv_id))->first();

                    if($cv1_data->parent_id != '' && $cv1_data->parent_id != null)
                    {
                        $cv1_parent_cv = DB::table('tbl_cvs')->where('cv_id', '=', $cv1_data->parent_id)->where('status', '=', 1)->where('is_active', '=', 0)->first();           
                    }
                    else
                    {
                        $cv1_parent_cv = null;
                    }
                    if($cv1_parent_cv != null)
                    {
                        $cv1_parent_cv_overall_ranking = DB::table('tbl_cv_block_2_data')->where('cv_id', '=', $cv1_parent_cv->cv_id)->where('is_active', '=', 0)->first();
                        
                    }
                    else
                    {
                        $cv1_parent_cv_overall_ranking = ''; 
                    }
                    //print_r($parent_cv_overall_ranking);exit;
                    
                    $cv1_block_2_data = DB::table('tbl_cv_block_2_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_3_data = DB::table('tbl_cv_block_3_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_4_data = DB::table('tbl_cv_block_4_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_5_data = DB::table('tbl_cv_block_5_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_6_data = DB::table('tbl_cv_block_6_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_7_data = DB::table('tbl_cv_block_7_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_8_data = DB::table('tbl_cv_block_8_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_9_data = DB::table('tbl_cv_block_9_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_10_data = DB::table('tbl_cv_block_10_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_11_data = DB::table('tbl_cv_block_11_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_12_data = DB::table('tbl_cv_block_12_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_13_data = DB::table('tbl_cv_block_13_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_14_data = DB::table('tbl_cv_block_14_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();
                    $cv1_block_15_data = DB::table('tbl_cv_block_15_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->get();

                    $cv1_block_16_mood_graph_data = DB::table('tbl_cv_block_16_mood_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_16_genre_graph_data = DB::table('tbl_cv_block_16_genre_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_17_mood_graph_data = DB::table('tbl_cv_block_17_mood_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_17_genre_graph_data = DB::table('tbl_cv_block_17_genre_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first(); 
                    $cv1_block_18_mood_graph_data = DB::table('tbl_cv_block_18_mood_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_18_genre_graph_data = DB::table('tbl_cv_block_18_genre_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_19_mood_graph_data = DB::table('tbl_cv_block_19_mood_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_block_19_genre_graph_data = DB::table('tbl_cv_block_19_genre_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_mood_aggr_graph_data = DB::table('tbl_mood_aggr_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_genre_aggr_graph_data = DB::table('tbl_genre_aggr_graph_data')->where('cv_id', '=', $cv1_data->cv_id)->where('is_active', '=', 0)->first();
                    $cv1_footer_template_data = DB::table('tbl_footer_template')->where('footer_template_id', '=', $cv1_data->footer_template_id)->first();

                    if(count($cv1_block_3_data)==0)
                    {
                        $cv1_block_3_data = '';
                        $cv1_music_taste_data = [];
                    }
                    else
                    {
                        $cv1_mti_ids_array = [];
                        for($cv1_mti = 0; $cv1_mti < count($cv1_block_3_data); $cv1_mti++)
                        {
                            array_push($cv1_mti_ids_array,$cv1_block_3_data[$cv1_mti]->b3_title_id);
                        }
                        if(count($cv1_mti_ids_array)>0)
                        {
                            $cv1_music_taste_data = DB::table('tbl_music_taste')
                                ->whereIn('music_taste_id', $cv1_mti_ids_array)
                                ->get();
                        }
                        else
                        {
                            $cv1_music_taste_data = [];
                        }
                    }   
                    if(count($cv1_music_taste_data)==0)
                    {
                        $cv1_music_taste_data = '';
                    }  
                    if(count($cv1_block_5_data)==0)
                    {
                        $cv1_block_5_data = '';
                    }
                    if(count($cv1_block_7_data)==0)
                    {
                        $cv1_block_7_data = '';
                    }
                    if(count($cv1_block_8_data)==0)
                    {
                        $cv1_block_8_data = '';
                    }
                    if(count($cv1_block_9_data)==0)
                    {
                        $cv1_block_9_data = '';
                    }
                    if(count($cv1_block_10_data)==0)
                    {
                        $cv1_block_10_data = '';
                        $cv1_qualitative_data = [];
                    }
                    else
                    {
                        $cv1_qlti_ids_array = [];
                        for($cv1_qlti = 0; $cv1_qlti < count($cv1_block_10_data); $cv1_qlti++)
                        {
                            array_push($cv1_qlti_ids_array,$cv1_block_10_data[$cv1_qlti]->b10_name_id);
                        }
                        if(count($cv1_qlti_ids_array)>0)
                        {
                            $cv1_qualitative_data = DB::table('tbl_qualitative')
                                ->whereIn('qualitative_id', $cv1_qlti_ids_array)
                                ->get();
                        }
                        else
                        {
                            $cv1_qualitative_data = [];
                        }
                    }
                    if(count($cv1_qualitative_data)==0)
                    {
                        $cv1_qualitative_data = '';
                    }
                    if(count($cv1_block_11_data)==0)
                    {
                        $cv1_block_11_data = '';
                    }
                    if(count($cv1_block_12_data)==0)
                    {
                        $cv1_block_12_data = '';
                    }
                    if(count($cv1_block_13_data)==0)
                    {
                        $cv1_block_13_data = '';
                        $cv1_experience_data = [];
                        $cv1_experience_excluded_data = [];
                    }
                    else
                    {
                        $cv1_ei_ids_array = [];
                        for($cv1_ei = 0; $cv1_ei < count($cv1_block_13_data); $cv1_ei++)
                        {
                            array_push($cv1_ei_ids_array,$cv1_block_13_data[$cv1_ei]->b13_name_id);
                        }
                        if(count($cv1_ei_ids_array)>0)
                        {
                            $cv1_experience_data = DB::table('tbl_experience')
                                ->whereIn('experience_id', $cv1_ei_ids_array)
                                ->get();
                            $cv1_experience_excluded_data =  DB::table('tbl_experience')
                                ->whereNotIn('experience_id', $cv1_ei_ids_array)
                                ->where("is_active", '=', '0')
                                ->get();
                        }
                        else
                        {
                            $cv1_experience_data = [];
                            $cv1_experience_excluded_data = [];
                        }
                    }
                    if(count($cv1_experience_data)==0)
                    {
                        $cv1_experience_data = '';
                        $cv1_experience_excluded_data = '';
                    }
                    if(count($cv1_block_14_data)==0)
                    {
                        $cv1_block_14_data = '';
                    }
                    if(count($cv1_block_15_data)==0)
                    {
                        $cv1_block_15_data = '';
                    }

                    //Industry
                    $industry_data = DB::table('tbl_sub_industry')->where('sub_industry_id', '=', base64_decode($request->compare_sub_industry_cv_id))->first();
                    $industrywise_cv_data = DB::table('tbl_cvs')->where('sub_industry_id', '=', base64_decode($request->compare_sub_industry_cv_id))->where('status', '=', 1)->where('is_active', '=', 0)->get();
                    $parent_industry_data = DB::table('tbl_industry')->join('tbl_sub_industry', 'tbl_industry.industry_id', '=', 'tbl_sub_industry.parent_industry_id')->where('sub_industry_id', '=', base64_decode($request->compare_sub_industry_cv_id))->first();
                    if(count($industrywise_cv_data)==0)
                    {
                        $industrywise_cv_data = ''; 
                        $most_popular_genres_data_array = '';   
                        $music_expenditure_per_year_array = '';  
                        $music_expenditure_per_video_array = '';      
                    }
                    else
                    {
                        $industrywise_cv_id_array = [];
                        foreach($industrywise_cv_data as $icdata)
                        {
                            array_push($industrywise_cv_id_array,$icdata->cv_id);
                        }
                        //print_r($industrywise_cv_id_array);
                        $get_most_popular_genres_id = DB::table('tbl_cv_block_3_data')->whereIn('cv_id', $industrywise_cv_id_array)->where('is_active','=',0)->get();
                        
                        $get_most_popular_genres_id_array = [];
                        foreach($get_most_popular_genres_id as $data)
                        {
                            array_push($get_most_popular_genres_id_array, $data->b3_title_id);
                        }
                        //print_r($get_most_popular_genres_id_array);
                        $most_popular_genres_data_array = [];
                        
                        foreach(array_count_values($get_most_popular_genres_id_array) as $key=>$value)
                        {
                            $temp_array = array_count_values($get_most_popular_genres_id_array);
                            //Arr::pull($temp_array,$key);
                            //$get_most_popular_genres_id_array_without_current_id = array_diff(array_count_values($get_most_popular_genres_id_array), array($value));
                            $get_most_popular_genres_id_array_without_current_id = $temp_array;
                            $current_genres_percentage = number_format($value * 100 / array_sum(array_count_values($get_most_popular_genres_id_array)),1);                
                            //echo $key."-------".$value."--------".array_sum($get_most_popular_genres_id_array_without_current_id)."--------".$current_genres_percentage."<br><br>";
                            $get_most_popular_genres_data = DB::table('tbl_music_taste')->where('music_taste_id', '=', $key)->first();
                            $most_popular_genres_data_for_array = ['music_taste_name'=> $get_most_popular_genres_data->music_taste_name, 'music_taste_icon_name'=> $get_most_popular_genres_data->music_taste_icon_name, 'music_taste_percentage'=>$current_genres_percentage];
                            array_push($most_popular_genres_data_array,$most_popular_genres_data_for_array);
                        }    
                        
                        $cv_ids_array = [];
                
                        foreach($industrywise_cv_data as $cv_items)
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

                        $get_music_expenditure_per_year_id = DB::table('tbl_cv_block_14_data')->whereIn('cv_id', $industrywise_cv_id_array)->where('is_active', '=', 0)->whereNotNull('b14_number')->get();
                        $music_expenditure_per_year_array = [];
                        foreach($get_music_expenditure_per_year_id as $data)
                        {
                            //echo $data->b14_number."<br><br>";
                            if($data->b14_number != '' && $data->b14_number != null)
                            {
                                array_push($music_expenditure_per_year_array, $data->b14_number);
                            }                
                        }
                        //echo array_sum($music_expenditure_per_year_array);
                        if(count($music_expenditure_per_year_array)==0)
                        {
                            $music_expenditure_per_year_array = '';
                        }

                        $get_music_expenditure_per_video_id = DB::table('tbl_cv_block_15_data')->whereIn('cv_id', $industrywise_cv_id_array)->where('is_active', '=', 0)->whereNotNull('b15_number')->get();
                        $music_expenditure_per_video_array = [];
                        foreach($get_music_expenditure_per_video_id as $data)
                        {
                            //echo $data->b15_number."<br><br>";
                            if($data->b15_number != '' && $data->b15_number != null)
                            {
                                array_push($music_expenditure_per_video_array, $data->b15_number);
                            }                
                        }
                        //echo array_sum($music_expenditure_per_video_array);
                        if(count($music_expenditure_per_video_array)==0)
                        {
                            $music_expenditure_per_video_array = '';
                        }
                        //print_r($music_expenditure_per_video_array);
                        
                        $music_types_usage_on_average_data = DB::table('tbl_cv_block_13_data')->whereIn('cv_id', $industrywise_cv_id_array)->where('is_active', '=', 0)->get();
                        if(count($music_types_usage_on_average_data)==0)
                        {
                            $music_types_usage_on_average_data = '';
                            $experience_data = [];
                            $experience_excluded_data = [];
                        }
                        else
                        {
                            $ei_ids_array = [];
                            for($ei = 0; $ei < count($music_types_usage_on_average_data); $ei++)
                            {
                                if($music_types_usage_on_average_data[$ei]->b13_name_id != '' && $music_types_usage_on_average_data[$ei]->b13_name_id != null && $music_types_usage_on_average_data[$ei]->b13_name_id != 0 && $music_types_usage_on_average_data[$ei]->b13_number != '' && $music_types_usage_on_average_data[$ei]->b13_number != null)
                                {
                                    array_push($ei_ids_array,$music_types_usage_on_average_data[$ei]->b13_name_id);
                                } 
                                                
                            }
                            $b13id_array = [];
                            foreach($music_types_usage_on_average_data as $cvb13key => $cvb13data)
                            { 
                                array_push($b13id_array,$cvb13data->b13_id);                                                        
                            }
                            //print_r($b13id_array);
                            $number_sum_data_array = [];
                            foreach(array_unique($ei_ids_array) as $eiid_data)
                            {
                                $number_sum_data = 0;
                                $get_number = DB::table('tbl_cv_block_13_data')->where('b13_name_id', $eiid_data)->where('is_active', '=', 0)->get();
                                foreach($get_number as $number_data)
                                {
                                    if(in_array($number_data->b13_id,$b13id_array))
                                    {
                                        $number_sum_data = $number_sum_data+$number_data->b13_number;
                                    }
                                }
                                
                                $experience_data = DB::table('tbl_experience')
                                    ->where('experience_id', "=", $eiid_data)
                                    ->first();
                                $number_sum_data_array[$eiid_data] = $number_sum_data."$#$".$experience_data->experience_name;
                                //array_push($number_sum_data_array, [$eiid_data => $number_sum_data]);
                            
                            }
                            //print_r($number_sum_data_array);
                            if(count($ei_ids_array)>0)
                            {
                                $experience_data = DB::table('tbl_experience')
                                    ->whereIn('experience_id', $ei_ids_array)
                                    ->get();
                                $experience_excluded_data = DB::table('tbl_experience')
                                ->whereNotIn('experience_id', $ei_ids_array)
                                ->where("is_active", '=', '0')
                                ->get();
                            }
                            else
                            {
                                $experience_data = [];
                                $experience_excluded_data = [];
                            }
                        }           
                    }
                    $all_published_cv_data = DB::table('tbl_cvs')->where('status', '=', 1)->where('is_active', '=', 0)->get();
                    if(count($all_published_cv_data)==0)
                    {
                        $all_published_cv_data = ''; 
                        $all_published_cv_music_expenditure_per_year_array = '';  
                        $all_published_cv_music_expenditure_per_video_array = '';      
                    }
                    else
                    {
                        $all_published_cv_id_array = [];
                        foreach($all_published_cv_data as $pcvdata)
                        {
                            array_push($all_published_cv_id_array,$pcvdata->cv_id);
                        }
                        
                        $get_all_published_cv_music_expenditure_per_year_id = DB::table('tbl_cv_block_14_data')->whereIn('cv_id', $all_published_cv_id_array)->where('is_active', '=', 0)->whereNotNull('b14_number')->get();
                        $all_published_cv_music_expenditure_per_year_array = [];
                        foreach($get_all_published_cv_music_expenditure_per_year_id as $data)
                        {
                            //echo $data->b14_number."<br><br>";
                            if($data->b14_number != '' && $data->b14_number != null)
                            {
                                array_push($all_published_cv_music_expenditure_per_year_array, $data->b14_number);
                            }                
                        }
                        //echo array_sum($all_published_cv_music_expenditure_per_year_array);
                        if(count($all_published_cv_music_expenditure_per_year_array)==0)
                        {
                            $all_published_cv_music_expenditure_per_year_array = '';
                        }

                        $get_all_published_cv_music_expenditure_per_video_id = DB::table('tbl_cv_block_15_data')->whereIn('cv_id', $all_published_cv_id_array)->where('is_active', '=', 0)->whereNotNull('b15_number')->get();
                        $all_published_cv_music_expenditure_per_video_array = [];
                        foreach($get_all_published_cv_music_expenditure_per_video_id as $data)
                        {
                            //echo $data->b15_number."<br><br>";
                            if($data->b15_number != '' && $data->b15_number != null)
                            {
                                array_push($all_published_cv_music_expenditure_per_video_array, $data->b15_number);
                            }                
                        }
                        //echo array_sum($all_published_cv_music_expenditure_per_video_array);
                        if(count($all_published_cv_music_expenditure_per_video_array)==0)
                        {
                            $all_published_cv_music_expenditure_per_video_array = '';
                        }
                        //print_r($all_published_cv_music_expenditure_per_video_array);           
                    }                    
                    
                    $industry_youtube_mood_graph_data = DB::table('tbl_sub_industry_youtube_mood_graph_data')->where('sind_id', '=', base64_decode($request->compare_sub_industry_cv_id))->where('is_active', '=', 0)->first();
                    $industry_youtube_genre_graph_data = DB::table('tbl_sub_industry_youtube_genre_graph_data')->where('sind_id', '=', base64_decode($request->compare_sub_industry_cv_id))->where('is_active', '=', 0)->first();
                    $industry_instagram_mood_graph_data = DB::table('tbl_sub_industry_instagram_mood_graph_data')->where('sind_id', '=', base64_decode($request->compare_sub_industry_cv_id))->where('is_active', '=', 0)->first();
                    $industry_instagram_genre_graph_data = DB::table('tbl_sub_industry_instagram_genre_graph_data')->where('sind_id', '=', base64_decode($request->compare_sub_industry_cv_id))->where('is_active', '=', 0)->first(); 
                    $industry_tiktok_mood_graph_data = DB::table('tbl_sub_industry_tiktok_mood_graph_data')->where('sind_id', '=', base64_decode($request->compare_sub_industry_cv_id))->where('is_active', '=', 0)->first();
                    $industry_tiktok_genre_graph_data = DB::table('tbl_sub_industry_tiktok_genre_graph_data')->where('sind_id', '=', base64_decode($request->compare_sub_industry_cv_id))->where('is_active', '=', 0)->first();
                    $industry_twitter_mood_graph_data = DB::table('tbl_sub_industry_twitter_mood_graph_data')->where('sind_id', '=', base64_decode($request->compare_sub_industry_cv_id))->where('is_active', '=', 0)->first();
                    $industry_twitter_genre_graph_data = DB::table('tbl_sub_industry_twitter_genre_graph_data')->where('sind_id', '=', base64_decode($request->compare_sub_industry_cv_id))->where('is_active', '=', 0)->first();
                    $industry_mood_aggr_graph_data = DB::table('tbl_sub_industry_mood_aggr_graph_data')->where('sind_id', '=', base64_decode($request->compare_sub_industry_cv_id))->where('is_active', '=', 0)->first();
                    $industry_genre_aggr_graph_data = DB::table('tbl_sub_industry_genre_aggr_graph_data')->where('sind_id', '=', base64_decode($request->compare_sub_industry_cv_id))->where('is_active', '=', 0)->first();
                    
                    return view('backend.views.compared_cv', ['compare_type'=>$request->compare_option, 'cv1_data'=>$cv1_data, 'cv1_footer_template_data'=>$cv1_footer_template_data, 'cv1_block_2_data'=>$cv1_block_2_data, 'cv1_parent_cv_overall_ranking'=>$cv1_parent_cv_overall_ranking, 'cv1_block_3_data'=>$cv1_block_3_data, 'cv1_music_taste_data'=>$cv1_music_taste_data, 'cv1_block_4_data'=>$cv1_block_4_data, 'cv1_block_5_data'=>$cv1_block_5_data, 'cv1_block_6_data'=>$cv1_block_6_data, 'cv1_block_7_data'=>$cv1_block_7_data, 'cv1_block_8_data'=>$cv1_block_8_data, 'cv1_block_9_data'=>$cv1_block_9_data, 'cv1_block_10_data'=>$cv1_block_10_data, 'cv1_qualitative_data'=>$cv1_qualitative_data, 'cv1_block_11_data'=>$cv1_block_11_data, 'cv1_block_12_data'=>$cv1_block_12_data, 'cv1_block_13_data'=>$cv1_block_13_data, 'cv1_experience_data'=>$cv1_experience_data, 'cv1_experience_excluded_data'=>$cv1_experience_excluded_data, 'cv1_block_14_data'=>$cv1_block_14_data, 'cv1_block_15_data'=>$cv1_block_15_data,'cv1_block_16_mood_graph_data'=>$cv1_block_16_mood_graph_data, 'cv1_block_16_genre_graph_data'=>$cv1_block_16_genre_graph_data, 'cv1_block_17_mood_graph_data'=>$cv1_block_17_mood_graph_data, 'cv1_block_17_genre_graph_data'=>$cv1_block_17_genre_graph_data, 'cv1_block_18_mood_graph_data'=>$cv1_block_18_mood_graph_data, 'cv1_block_18_genre_graph_data'=>$cv1_block_18_genre_graph_data, 'cv1_block_19_mood_graph_data'=>$cv1_block_19_mood_graph_data, 'cv1_block_19_genre_graph_data'=>$cv1_block_19_genre_graph_data,'cv1_mood_aggr_graph_data'=>$cv1_mood_aggr_graph_data, 'cv1_genre_aggr_graph_data'=>$cv1_genre_aggr_graph_data, 'industry_data'=>$industry_data,'industrywise_cv_data'=>$industrywise_cv_data,'most_popular_genres_data_array'=>$most_popular_genres_data_array,'music_expenditure_per_year_array'=>$music_expenditure_per_year_array,'all_published_cv_music_expenditure_per_year_array'=>$all_published_cv_music_expenditure_per_year_array,'music_expenditure_per_video_array'=>$music_expenditure_per_video_array,'all_published_cv_music_expenditure_per_video_array'=>$all_published_cv_music_expenditure_per_video_array,'music_types_usage_on_average_data'=>$music_types_usage_on_average_data,'experience_data'=>$experience_data,'experience_excluded_data'=>$experience_excluded_data,'ei_ids_array'=>$ei_ids_array,'number_sum_data_array'=>$number_sum_data_array,'insudtry_yes_avg_data_array'=>$insudtry_yes_avg_data_array,'insudtry_no_avg_data_array'=>$insudtry_no_avg_data_array,'industry_youtube_mood_graph_data'=>$industry_youtube_mood_graph_data,'industry_youtube_genre_graph_data'=>$industry_youtube_genre_graph_data, 'industry_instagram_mood_graph_data'=>$industry_instagram_mood_graph_data, 'industry_instagram_genre_graph_data'=>$industry_instagram_genre_graph_data, 'industry_tiktok_mood_graph_data'=>$industry_tiktok_mood_graph_data, 'industry_tiktok_genre_graph_data'=>$industry_tiktok_genre_graph_data, 'industry_twitter_mood_graph_data'=>$industry_twitter_mood_graph_data, 'industry_twitter_genre_graph_data'=>$industry_twitter_genre_graph_data,'industry_mood_aggr_graph_data'=>$industry_mood_aggr_graph_data, 'industry_genre_aggr_graph_data'=>$industry_genre_aggr_graph_data,'parent_industry_data'=>$parent_industry_data]);
                    //return view('backend.views.compared_cv', ['compare_type'=>$request->compare_cv, 'cv1_data'=>$cv1_data, 'cv1_footer_template_data'=>$cv1_footer_template_data, 'cv1_block_2_data'=>$cv1_block_2_data, 'cv1_parent_cv_overall_ranking'=>$cv1_parent_cv_overall_ranking, 'cv1_block_3_data'=>$cv1_block_3_data, 'cv1_music_taste_data'=>$cv1_music_taste_data, 'cv1_block_4_data'=>$cv1_block_4_data, 'cv1_block_5_data'=>$cv1_block_5_data, 'cv1_block_6_data'=>$cv1_block_6_data, 'cv1_block_7_data'=>$cv1_block_7_data, 'cv1_block_8_data'=>$cv1_block_8_data, 'cv1_block_9_data'=>$cv1_block_9_data, 'cv1_block_10_data'=>$cv1_block_10_data, 'cv1_qualitative_data'=>$cv1_qualitative_data, 'cv1_block_11_data'=>$cv1_block_11_data, 'cv1_block_12_data'=>$cv1_block_12_data, 'cv1_block_13_data'=>$cv1_block_13_data, 'cv1_experience_data'=>$cv1_experience_data, 'cv1_block_14_data'=>$cv1_block_14_data, 'cv1_block_15_data'=>$cv1_block_15_data, 'industry_data'=>$industry_data,'cv_letters_AtoZ_array'=>$cv_letters_AtoZ_array,'cv_data_json'=>$cv_data_json,'industry_cv_data'=>$industry_cv_data, 'insudtry_yes_avg_data_array'=>$insudtry_yes_avg_data_array, 'insudtry_no_avg_data_array'=>$insudtry_no_avg_data_array]);
                }
            }
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }

    function displayIndustryCV($industry_parameter)
    {
        $industry_id = explode('$_$',$industry_parameter)[0];
        $cv_year = explode('$_$',$industry_parameter)[1];
        $industry_data = DB::table('tbl_industry')->where('industry_id', '=', base64_decode($industry_id))->first();
        $industrywise_cv_data = DB::table('tbl_cvs')->where('industry_id', '=', base64_decode($industry_id))->where('status', '=', 1)->where('is_active', '=', 0)->get();
        if(count($industrywise_cv_data)==0)
        {
            $industrywise_cv_data = ''; 
            $most_popular_genres_data_array = '';   
            $music_expenditure_per_year_array = '';  
            $music_expenditure_per_video_array = '';      
        }
        else
        {
            $industrywise_cv_id_array = [];
            foreach($industrywise_cv_data as $icdata)
            {
                array_push($industrywise_cv_id_array,$icdata->cv_id);
            }
            //print_r($industrywise_cv_id_array);
            $get_most_popular_genres_id = DB::table('tbl_cv_block_3_data')->whereIn('cv_id', $industrywise_cv_id_array)->where('is_active','=',0)->get();
            
            $get_most_popular_genres_id_array = [];
            foreach($get_most_popular_genres_id as $data)
            {
                array_push($get_most_popular_genres_id_array, $data->b3_title_id);
            }

            $most_popular_genres_data_array = [];

            foreach(array_count_values($get_most_popular_genres_id_array) as $key=>$value)
            {
                $temp_array = array_count_values($get_most_popular_genres_id_array);
                //Arr::pull($temp_array,$key);
                //$get_most_popular_genres_id_array_without_current_id = array_diff(array_count_values($get_most_popular_genres_id_array), array($value));
                $get_most_popular_genres_id_array_without_current_id = $temp_array;
                $current_genres_percentage = number_format($value * 100 / array_sum(array_count_values($get_most_popular_genres_id_array)),1);                
                //echo $key."-------".$value."--------".array_sum($get_most_popular_genres_id_array_without_current_id)."--------".$current_genres_percentage."<br><br>";
                $get_most_popular_genres_data = DB::table('tbl_music_taste')->where('music_taste_id', '=', $key)->first();
                $most_popular_genres_data_for_array = ['music_taste_name'=> $get_most_popular_genres_data->music_taste_name, 'music_taste_icon_name'=> $get_most_popular_genres_data->music_taste_icon_name, 'music_taste_percentage'=>$current_genres_percentage];
                array_push($most_popular_genres_data_array,$most_popular_genres_data_for_array);
            }    
            
            $cv_ids_array = [];
    
            foreach($industrywise_cv_data as $cv_items)
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

            $get_music_expenditure_per_year_id = DB::table('tbl_cv_block_14_data')->whereIn('cv_id', $industrywise_cv_id_array)->where('is_active', '=', 0)->get();
            $music_expenditure_per_year_array = [];
            foreach($get_music_expenditure_per_year_id as $data)
            {
                //echo $data->b14_number."<br><br>";
                if($data->b14_number != '' && $data->b14_number != null)
                {
                    array_push($music_expenditure_per_year_array, $data->b14_number);
                }                
            }
            //echo array_sum($music_expenditure_per_year_array);
            if(count($music_expenditure_per_year_array)==0)
            {
                $music_expenditure_per_year_array = '';
            }

            $get_music_expenditure_per_video_id = DB::table('tbl_cv_block_15_data')->whereIn('cv_id', $industrywise_cv_id_array)->where('is_active', '=', 0)->get();
            $music_expenditure_per_video_array = [];
            foreach($get_music_expenditure_per_video_id as $data)
            {
                //echo $data->b15_number."<br><br>";
                if($data->b15_number != '' && $data->b15_number != null)
                {
                    array_push($music_expenditure_per_video_array, $data->b15_number);
                }                
            }
            //echo array_sum($music_expenditure_per_video_array);
            if(count($music_expenditure_per_video_array)==0)
            {
                $music_expenditure_per_video_array = '';
            }
            //print_r($music_expenditure_per_video_array);
            
            $all_published_cv_data = DB::table('tbl_cvs')->where('status', '=', 1)->where('is_active', '=', 0)->get();
            if(count($all_published_cv_data)==0)
            {
                $all_published_cv_data = ''; 
                $all_published_cv_music_expenditure_per_year_array = '';  
                $all_published_cv_music_expenditure_per_video_array = '';      
            }
            else
            {
                $all_published_cv_id_array = [];
                foreach($all_published_cv_data as $pcvdata)
                {
                    array_push($all_published_cv_id_array,$pcvdata->cv_id);
                }
                
                $get_all_published_cv_music_expenditure_per_year_id = DB::table('tbl_cv_block_14_data')->whereIn('cv_id', $all_published_cv_id_array)->where('is_active', '=', 0)->whereNotNull('b14_number')->get();
                $all_published_cv_music_expenditure_per_year_array = [];
                foreach($get_all_published_cv_music_expenditure_per_year_id as $data)
                {
                    //echo $data->b14_number."<br><br>";
                    if($data->b14_number != '' && $data->b14_number != null)
                    {
                        array_push($all_published_cv_music_expenditure_per_year_array, $data->b14_number);
                    }                
                }
                //echo array_sum($all_published_cv_music_expenditure_per_year_array);
                if(count($all_published_cv_music_expenditure_per_year_array)==0)
                {
                    $all_published_cv_music_expenditure_per_year_array = '';
                }

                $get_all_published_cv_music_expenditure_per_video_id = DB::table('tbl_cv_block_15_data')->whereIn('cv_id', $all_published_cv_id_array)->where('is_active', '=', 0)->whereNotNull('b15_number')->get();
                $all_published_cv_music_expenditure_per_video_array = [];
                foreach($get_all_published_cv_music_expenditure_per_video_id as $data)
                {
                    //echo $data->b15_number."<br><br>";
                    if($data->b15_number != '' && $data->b15_number != null)
                    {
                        array_push($all_published_cv_music_expenditure_per_video_array, $data->b15_number);
                    }                
                }
                //echo array_sum($all_published_cv_music_expenditure_per_video_array);
                if(count($all_published_cv_music_expenditure_per_video_array)==0)
                {
                    $all_published_cv_music_expenditure_per_video_array = '';
                }
                //print_r($all_published_cv_music_expenditure_per_video_array);           
            }

            $music_types_usage_on_average_data = DB::table('tbl_cv_block_13_data')->whereIn('cv_id', $industrywise_cv_id_array)->where('is_active', '=', 0)->get();
            if(count($music_types_usage_on_average_data)==0)
            {
                $music_types_usage_on_average_data = '';
                $experience_data = [];
                $experience_excluded_data = [];
            }
            else
            {
                $ei_ids_array = [];
                for($ei = 0; $ei < count($music_types_usage_on_average_data); $ei++)
                {
                    if($music_types_usage_on_average_data[$ei]->b13_name_id != '' && $music_types_usage_on_average_data[$ei]->b13_name_id != null && $music_types_usage_on_average_data[$ei]->b13_name_id != 0 && $music_types_usage_on_average_data[$ei]->b13_number != '' && $music_types_usage_on_average_data[$ei]->b13_number != null)
                    {
                        array_push($ei_ids_array,$music_types_usage_on_average_data[$ei]->b13_name_id);
                    } 
                                    
                }
                $b13id_array = [];
                foreach($music_types_usage_on_average_data as $cvb13key => $cvb13data)
                { 
                    array_push($b13id_array,$cvb13data->b13_id);                                                        
                }
                //print_r($b13id_array);
                $number_sum_data_array = [];
                foreach(array_unique($ei_ids_array) as $eiid_data)
                {
                    $number_sum_data = 0;
                    $get_number = DB::table('tbl_cv_block_13_data')->where('b13_name_id', $eiid_data)->where('is_active', '=', 0)->get();
                    foreach($get_number as $number_data)
                    {
                        if(in_array($number_data->b13_id,$b13id_array))
                        {
                            $number_sum_data = $number_sum_data+$number_data->b13_number;
                        }
                    }
                    
                    $experience_data = DB::table('tbl_experience')
                        ->where('experience_id', "=", $eiid_data)
                        ->first();
                    $number_sum_data_array[$eiid_data] = $number_sum_data."$#$".$experience_data->experience_name;
                    //array_push($number_sum_data_array, [$eiid_data => $number_sum_data]);
                   
                }
                //print_r($number_sum_data_array);
                if(count($ei_ids_array)>0)
                {
                    $experience_data = DB::table('tbl_experience')
                        ->whereIn('experience_id', $ei_ids_array)
                        ->get();
                    $experience_excluded_data =  DB::table('tbl_experience')
                        ->whereNotIn('experience_id', $ei_ids_array)
                        ->where("is_active", '=', '0')
                        ->get();
                }
                else
                {
                    $experience_data = [];
                    $experience_excluded_data = [];
                }
            }           
        }
        //print_r($ei_ids_array);
        //print_r($experience_data);
        //print_r($music_types_usage_on_average_data); 
        
        $industry_youtube_mood_graph_data = DB::table('tbl_industry_youtube_mood_graph_data')->where('ind_id', '=', base64_decode($industry_id))->where('ind_year', '=', base64_decode($cv_year))->where('is_active', '=', 0)->first();
        $industry_youtube_genre_graph_data = DB::table('tbl_industry_youtube_genre_graph_data')->where('ind_id', '=', base64_decode($industry_id))->where('ind_year', '=', base64_decode($cv_year))->where('is_active', '=', 0)->first();
        $industry_instagram_mood_graph_data = DB::table('tbl_industry_instagram_mood_graph_data')->where('ind_id', '=', base64_decode($industry_id))->where('ind_year', '=', base64_decode($cv_year))->where('is_active', '=', 0)->first();
        $industry_instagram_genre_graph_data = DB::table('tbl_industry_instagram_genre_graph_data')->where('ind_id', '=', base64_decode($industry_id))->where('ind_year', '=', base64_decode($cv_year))->where('is_active', '=', 0)->first(); 
        $industry_tiktok_mood_graph_data = DB::table('tbl_industry_tiktok_mood_graph_data')->where('ind_id', '=', base64_decode($industry_id))->where('ind_year', '=', base64_decode($cv_year))->where('is_active', '=', 0)->first();
        $industry_tiktok_genre_graph_data = DB::table('tbl_industry_tiktok_genre_graph_data')->where('ind_id', '=', base64_decode($industry_id))->where('ind_year', '=', base64_decode($cv_year))->where('is_active', '=', 0)->first();
        $industry_twitter_mood_graph_data = DB::table('tbl_industry_twitter_mood_graph_data')->where('ind_id', '=', base64_decode($industry_id))->where('ind_year', '=', base64_decode($cv_year))->where('is_active', '=', 0)->first();
        $industry_twitter_genre_graph_data = DB::table('tbl_industry_twitter_genre_graph_data')->where('ind_id', '=', base64_decode($industry_id))->where('ind_year', '=', base64_decode($cv_year))->where('is_active', '=', 0)->first();
        $industry_mood_aggr_graph_data = DB::table('tbl_industry_mood_aggr_graph_data')->where('ind_id', '=', base64_decode($industry_id))->where('ind_year', '=', base64_decode($cv_year))->where('is_active', '=', 0)->first();
        $industry_genre_aggr_graph_data = DB::table('tbl_industry_genre_aggr_graph_data')->where('ind_id', '=', base64_decode($industry_id))->where('ind_year', '=', base64_decode($cv_year))->where('is_active', '=', 0)->first();

        $industry_year = base64_decode($cv_year);
        $industry_cv_genre_aggr_graph_values_data = DB::table('tbl_industry_genre_aggr_graph_data')->select('ambient','blues','classical','country','electronicDance','folk','indieAlternative','jazz','latin','metal','pop','punk','rapHipHop','reggae','rnb','rock','singerSongwriter')->where('ind_id', '=', base64_decode($industry_id))->where('ind_year', '=', base64_decode($cv_year))->where('is_active', '=', 0)->first();
        $industry_cv_genre_aggr_graph_values_arr = (array)$industry_cv_genre_aggr_graph_values_data;
        $industry_cv_genre_aggr_graph_values_arr1 = (array)$industry_cv_genre_aggr_graph_values_data;
        rsort($industry_cv_genre_aggr_graph_values_arr);
        $top3 = array_slice($industry_cv_genre_aggr_graph_values_arr, 0, 3);
        $top_3_genre = array();        
        foreach ($top3 as $key => $val) {
            //echo "key-".$key."----------- val-".$val."<br>";
            $key = array_search ($val, $industry_cv_genre_aggr_graph_values_arr1);
            unset($industry_cv_genre_aggr_graph_values_arr1[$key]);
            $top_3_genre[$key] = $val;
        }
        if(count($top_3_genre)==0)
        {
            $top_3_genre = '';
        }

        return view('backend.views.industry_display_cv', ['industry_data'=>$industry_data,'industrywise_cv_data'=>$industrywise_cv_data,'most_popular_genres_data_array'=>$most_popular_genres_data_array,'music_expenditure_per_year_array'=>$music_expenditure_per_year_array,'all_published_cv_music_expenditure_per_year_array'=>$all_published_cv_music_expenditure_per_year_array,'music_expenditure_per_video_array'=>$music_expenditure_per_video_array,'all_published_cv_music_expenditure_per_video_array'=>$all_published_cv_music_expenditure_per_video_array,'music_types_usage_on_average_data'=>$music_types_usage_on_average_data,'experience_data'=>$experience_data,'experience_excluded_data'=>$experience_excluded_data,'ei_ids_array'=>$ei_ids_array,'number_sum_data_array'=>$number_sum_data_array,'insudtry_yes_avg_data_array'=>$insudtry_yes_avg_data_array,'insudtry_no_avg_data_array'=>$insudtry_no_avg_data_array,'industry_youtube_mood_graph_data'=>$industry_youtube_mood_graph_data,'industry_youtube_genre_graph_data'=>$industry_youtube_genre_graph_data, 'industry_instagram_mood_graph_data'=>$industry_instagram_mood_graph_data, 'industry_instagram_genre_graph_data'=>$industry_instagram_genre_graph_data, 'industry_tiktok_mood_graph_data'=>$industry_tiktok_mood_graph_data, 'industry_tiktok_genre_graph_data'=>$industry_tiktok_genre_graph_data, 'industry_twitter_mood_graph_data'=>$industry_twitter_mood_graph_data, 'industry_twitter_genre_graph_data'=>$industry_twitter_genre_graph_data,'industry_mood_aggr_graph_data'=>$industry_mood_aggr_graph_data, 'industry_genre_aggr_graph_data'=>$industry_genre_aggr_graph_data, 'industry_year'=>$industry_year, 'top_3_genre'=>$top_3_genre]);
    }

    function displayIndustryCVLauncher($industry_id)
    {
        $cv_data_years = DB::table('tbl_cvs')->select('cv_year')->where('sub_industry_id', '=', base64_decode($industry_id))->where('status', '=', 1)->where('is_active', '=', 0)->distinct()->orderBy('cv_year','desc')->get();
        
        $industry_cv_data =  DB::table('tbl_industry')->where('industry_id','=',base64_decode($industry_id))->first();
        
        return view('backend.views.display_industry_cv_launcher',['cv_data_years'=>$cv_data_years,'industry_cv_data'=>$industry_cv_data]);
    }

    function displaySubIndustryCV($sub_industry_parameter)
    {
        $sub_industry_id = explode('$_$',$sub_industry_parameter)[0];
        $cv_year = explode('$_$',$sub_industry_parameter)[1];
        //echo $sub_industry_id."-------".$cv_year;exit;
        $parent_industry_data = DB::table('tbl_industry')->join('tbl_sub_industry', 'tbl_industry.industry_id', '=', 'tbl_sub_industry.parent_industry_id')->where('sub_industry_id', '=', base64_decode($sub_industry_id))->first();
        $sub_industry_data = DB::table('tbl_sub_industry')->where('sub_industry_id', '=', base64_decode($sub_industry_id))->first();
        $sub_industrywise_cv_data = DB::table('tbl_cvs')->where('sub_industry_id', '=', base64_decode($sub_industry_id))->where('status', '=', 1)->where('cv_year', '=', base64_decode($cv_year))->where('is_active', '=', 0)->get();
        if(count($sub_industrywise_cv_data)==0)
        {
            $sub_industrywise_cv_data = ''; 
            $most_popular_genres_data_array = '';   
            $music_expenditure_per_year_array = '';  
            $music_expenditure_per_video_array = '';      
        }
        else
        {
            $sub_industrywise_cv_id_array = [];
            foreach($sub_industrywise_cv_data as $sicdata)
            {
                array_push($sub_industrywise_cv_id_array,$sicdata->cv_id);
            }
            //print_r($industrywise_cv_id_array);
            $get_most_popular_genres_id = DB::table('tbl_cv_block_3_data')->whereIn('cv_id', $sub_industrywise_cv_id_array)->where('is_active','=',0)->get();
            
            $get_most_popular_genres_id_array = [];
            foreach($get_most_popular_genres_id as $data)
            {
                array_push($get_most_popular_genres_id_array, $data->b3_title_id);
            }

            $most_popular_genres_data_array = [];

            foreach(array_count_values($get_most_popular_genres_id_array) as $key=>$value)
            {
                $temp_array = array_count_values($get_most_popular_genres_id_array);
                //Arr::pull($temp_array,$key);
                //$get_most_popular_genres_id_array_without_current_id = array_diff(array_count_values($get_most_popular_genres_id_array), array($value));
                $get_most_popular_genres_id_array_without_current_id = $temp_array;
                $current_genres_percentage = number_format($value * 100 / array_sum(array_count_values($get_most_popular_genres_id_array)),1);                
                //echo $key."-------".$value."--------".array_sum($get_most_popular_genres_id_array_without_current_id)."--------".$current_genres_percentage."<br><br>";
                $get_most_popular_genres_data = DB::table('tbl_music_taste')->where('music_taste_id', '=', $key)->first();
                $most_popular_genres_data_for_array = ['music_taste_name'=> $get_most_popular_genres_data->music_taste_name, 'music_taste_icon_name'=> $get_most_popular_genres_data->music_taste_icon_name, 'music_taste_percentage'=>$current_genres_percentage];
                array_push($most_popular_genres_data_array,$most_popular_genres_data_for_array);
            }    
            
            $cv_ids_array = [];
    
            foreach($sub_industrywise_cv_data as $cv_items)
            {
                array_push($cv_ids_array, $cv_items->cv_id);
            }

            $sub_insudtry_yes_avg_data_array = [];
            $sub_insudtry_no_avg_data_array = [];
            foreach($cv_ids_array as $cv_id)
            {
                $sub_insudtry_avg_data = DB::table('tbl_cv_block_7_data')
                            ->where('cv_id', $cv_id)                        
                            ->where('is_active', 0)
                            ->get();
                foreach($sub_insudtry_avg_data as $data)
                {
                    if($data->b7_name == 'yes' || $data->b7_name == 'Yes' || $data->b7_name == 'YES')
                    {
                        if($data->b7_number!='' && $data->b7_number !=null)
                        {
                            array_push($sub_insudtry_yes_avg_data_array,$data->b7_number);
                        }                    
                    }
                    if($data->b7_name == 'no' || $data->b7_name == 'No' || $data->b7_name == 'NO')
                    {
                        if($data->b7_number!='' && $data->b7_number !=null)
                        {
                            array_push($sub_insudtry_no_avg_data_array,$data->b7_number);
                        }                    
                    }
                }
            }

            $get_music_expenditure_per_year_id = DB::table('tbl_cv_block_14_data')->whereIn('cv_id', $sub_industrywise_cv_id_array)->where('is_active', '=', 0)->get();
            $music_expenditure_per_year_array = [];
            foreach($get_music_expenditure_per_year_id as $data)
            {
                //echo $data->b14_number."<br><br>";
                if($data->b14_number != '' && $data->b14_number != null)
                {
                    array_push($music_expenditure_per_year_array, $data->b14_number);
                }                
            }
            //echo array_sum($music_expenditure_per_year_array);
            if(count($music_expenditure_per_year_array)==0)
            {
                $music_expenditure_per_year_array = '';
            }

            $get_music_expenditure_per_video_id = DB::table('tbl_cv_block_15_data')->whereIn('cv_id', $sub_industrywise_cv_id_array)->where('is_active', '=', 0)->get();
            $music_expenditure_per_video_array = [];
            foreach($get_music_expenditure_per_video_id as $data)
            {
                //echo $data->b15_number."<br><br>";
                if($data->b15_number != '' && $data->b15_number != null)
                {
                    array_push($music_expenditure_per_video_array, $data->b15_number);
                }                
            }
            //echo array_sum($music_expenditure_per_video_array);
            if(count($music_expenditure_per_video_array)==0)
            {
                $music_expenditure_per_video_array = '';
            }
            //print_r($music_expenditure_per_video_array);
            
            $all_published_cv_data = DB::table('tbl_cvs')->where('status', '=', 1)->where('is_active', '=', 0)->get();
            if(count($all_published_cv_data)==0)
            {
                $all_published_cv_data = ''; 
                $all_published_cv_music_expenditure_per_year_array = '';  
                $all_published_cv_music_expenditure_per_video_array = '';      
            }
            else
            {
                $all_published_cv_id_array = [];
                foreach($all_published_cv_data as $pcvdata)
                {
                    array_push($all_published_cv_id_array,$pcvdata->cv_id);
                }
                
                $get_all_published_cv_music_expenditure_per_year_id = DB::table('tbl_cv_block_14_data')->whereIn('cv_id', $all_published_cv_id_array)->where('is_active', '=', 0)->whereNotNull('b14_number')->get();
                $all_published_cv_music_expenditure_per_year_array = [];
                foreach($get_all_published_cv_music_expenditure_per_year_id as $data)
                {
                    //echo $data->b14_number."<br><br>";
                    if($data->b14_number != '' && $data->b14_number != null)
                    {
                        array_push($all_published_cv_music_expenditure_per_year_array, $data->b14_number);
                    }                
                }
                //echo array_sum($all_published_cv_music_expenditure_per_year_array);
                if(count($all_published_cv_music_expenditure_per_year_array)==0)
                {
                    $all_published_cv_music_expenditure_per_year_array = '';
                }

                $get_all_published_cv_music_expenditure_per_video_id = DB::table('tbl_cv_block_15_data')->whereIn('cv_id', $all_published_cv_id_array)->where('is_active', '=', 0)->whereNotNull('b15_number')->get();
                $all_published_cv_music_expenditure_per_video_array = [];
                foreach($get_all_published_cv_music_expenditure_per_video_id as $data)
                {
                    //echo $data->b15_number."<br><br>";
                    if($data->b15_number != '' && $data->b15_number != null)
                    {
                        array_push($all_published_cv_music_expenditure_per_video_array, $data->b15_number);
                    }                
                }
                //echo array_sum($all_published_cv_music_expenditure_per_video_array);
                if(count($all_published_cv_music_expenditure_per_video_array)==0)
                {
                    $all_published_cv_music_expenditure_per_video_array = '';
                }
                //print_r($all_published_cv_music_expenditure_per_video_array);           
            }

            $music_types_usage_on_average_data = DB::table('tbl_cv_block_13_data')->whereIn('cv_id', $sub_industrywise_cv_id_array)->where('is_active', '=', 0)->get();
            if(count($music_types_usage_on_average_data)==0)
            {
                $music_types_usage_on_average_data = '';
                $experience_data = [];
                $experience_excluded_data = [];
            }
            else
            {
                $ei_ids_array = [];
                for($ei = 0; $ei < count($music_types_usage_on_average_data); $ei++)
                {
                    if($music_types_usage_on_average_data[$ei]->b13_name_id != '' && $music_types_usage_on_average_data[$ei]->b13_name_id != null && $music_types_usage_on_average_data[$ei]->b13_name_id != 0 && $music_types_usage_on_average_data[$ei]->b13_number != '' && $music_types_usage_on_average_data[$ei]->b13_number != null)
                    {
                        array_push($ei_ids_array,$music_types_usage_on_average_data[$ei]->b13_name_id);
                    } 
                                    
                }
                $b13id_array = [];
                foreach($music_types_usage_on_average_data as $cvb13key => $cvb13data)
                { 
                    array_push($b13id_array,$cvb13data->b13_id);                                                        
                }
                //print_r($b13id_array);
                $number_sum_data_array = [];
                foreach(array_unique($ei_ids_array) as $eiid_data)
                {
                    $number_sum_data = 0;
                    $get_number = DB::table('tbl_cv_block_13_data')->where('b13_name_id', $eiid_data)->where('is_active', '=', 0)->get();
                    foreach($get_number as $number_data)
                    {
                        if(in_array($number_data->b13_id,$b13id_array))
                        {
                            $number_sum_data = $number_sum_data+$number_data->b13_number;
                        }
                    }
                    
                    $experience_data = DB::table('tbl_experience')
                        ->where('experience_id', "=", $eiid_data)
                        ->first();
                    $number_sum_data_array[$eiid_data] = $number_sum_data."$#$".$experience_data->experience_name;
                    //array_push($number_sum_data_array, [$eiid_data => $number_sum_data]);
                   
                }
                //print_r($number_sum_data_array);
                if(count($ei_ids_array)>0)
                {
                    $experience_data = DB::table('tbl_experience')
                        ->whereIn('experience_id', $ei_ids_array)
                        ->get();
                    $experience_excluded_data =  DB::table('tbl_experience')
                        ->whereNotIn('experience_id', $ei_ids_array)
                        ->where("is_active", '=', '0')
                        ->get();
                }
                else
                {
                    $experience_data = [];
                    $experience_excluded_data = [];
                }
            }           
        }
        //print_r($ei_ids_array);
        //print_r($experience_data);
        //print_r($music_types_usage_on_average_data); 

        $industry_youtube_mood_graph_data = DB::table('tbl_sub_industry_youtube_mood_graph_data')->where('sind_id', '=', base64_decode($sub_industry_id))->where('sind_year', '=', base64_decode($cv_year))->where('is_active', '=', 0)->first();
        $industry_youtube_genre_graph_data = DB::table('tbl_sub_industry_youtube_genre_graph_data')->where('sind_id', '=', base64_decode($sub_industry_id))->where('sind_year', '=', base64_decode($cv_year))->where('is_active', '=', 0)->first();
        $industry_instagram_mood_graph_data = DB::table('tbl_sub_industry_instagram_mood_graph_data')->where('sind_id', '=', base64_decode($sub_industry_id))->where('sind_year', '=', base64_decode($cv_year))->where('is_active', '=', 0)->first();
        $industry_instagram_genre_graph_data = DB::table('tbl_sub_industry_instagram_genre_graph_data')->where('sind_id', '=', base64_decode($sub_industry_id))->where('sind_year', '=', base64_decode($cv_year))->where('is_active', '=', 0)->first(); 
        $industry_tiktok_mood_graph_data = DB::table('tbl_sub_industry_tiktok_mood_graph_data')->where('sind_id', '=', base64_decode($sub_industry_id))->where('sind_year', '=', base64_decode($cv_year))->where('is_active', '=', 0)->first();
        $industry_tiktok_genre_graph_data = DB::table('tbl_sub_industry_tiktok_genre_graph_data')->where('sind_id', '=', base64_decode($sub_industry_id))->where('sind_year', '=', base64_decode($cv_year))->where('is_active', '=', 0)->first();
        $industry_twitter_mood_graph_data = DB::table('tbl_sub_industry_twitter_mood_graph_data')->where('sind_id', '=', base64_decode($sub_industry_id))->where('sind_year', '=', base64_decode($cv_year))->where('is_active', '=', 0)->first();
        $industry_twitter_genre_graph_data = DB::table('tbl_sub_industry_twitter_genre_graph_data')->where('sind_id', '=', base64_decode($sub_industry_id))->where('sind_year', '=', base64_decode($cv_year))->where('is_active', '=', 0)->first();
        $industry_mood_aggr_graph_data = DB::table('tbl_sub_industry_mood_aggr_graph_data')->where('sind_id', '=', base64_decode($sub_industry_id))->where('sind_year', '=', base64_decode($cv_year))->where('is_active', '=', 0)->first();
        $industry_genre_aggr_graph_data = DB::table('tbl_sub_industry_genre_aggr_graph_data')->where('sind_id', '=', base64_decode($sub_industry_id))->where('sind_year', '=', base64_decode($cv_year))->where('is_active', '=', 0)->first();
        
        $sub_industry_year = base64_decode($cv_year);
        $sub_industry_cv_genre_aggr_graph_values_data = DB::table('tbl_sub_industry_genre_aggr_graph_data')->select('ambient','blues','classical','country','electronicDance','folk','indieAlternative','jazz','latin','metal','pop','punk','rapHipHop','reggae','rnb','rock','singerSongwriter')->where('sind_id', '=', base64_decode($sub_industry_id))->where('sind_year', '=', base64_decode($cv_year))->where('is_active', '=', 0)->first();
        $sub_industry_cv_genre_aggr_graph_values_arr = (array)$sub_industry_cv_genre_aggr_graph_values_data;
        $sub_industry_cv_genre_aggr_graph_values_arr1 = (array)$sub_industry_cv_genre_aggr_graph_values_data;
        rsort($sub_industry_cv_genre_aggr_graph_values_arr);
        $top3 = array_slice($sub_industry_cv_genre_aggr_graph_values_arr, 0, 3);
        $top_3_genre = array();        
        foreach ($top3 as $key => $val) {
            //echo "key-".$key."----------- val-".$val."<br>";
            $key = array_search ($val, $sub_industry_cv_genre_aggr_graph_values_arr1);
            unset($sub_industry_cv_genre_aggr_graph_values_arr1[$key]);
            $top_3_genre[$key] = $val;
        }
        if(count($top_3_genre)==0)
        {
            $top_3_genre = '';
        }
        return view('backend.views.sub_industry_display_cv', ['parent_industry_data'=>$parent_industry_data,'sub_industry_data'=>$sub_industry_data,'sub_industrywise_cv_data'=>$sub_industrywise_cv_data,'most_popular_genres_data_array'=>$most_popular_genres_data_array,'music_expenditure_per_year_array'=>$music_expenditure_per_year_array,'all_published_cv_music_expenditure_per_year_array'=>$all_published_cv_music_expenditure_per_year_array,'music_expenditure_per_video_array'=>$music_expenditure_per_video_array,'all_published_cv_music_expenditure_per_video_array'=>$all_published_cv_music_expenditure_per_video_array,'music_types_usage_on_average_data'=>$music_types_usage_on_average_data,'experience_data'=>$experience_data,'experience_excluded_data'=>$experience_excluded_data,'ei_ids_array'=>$ei_ids_array,'number_sum_data_array'=>$number_sum_data_array,'sub_insudtry_yes_avg_data_array'=>$sub_insudtry_yes_avg_data_array,'sub_insudtry_no_avg_data_array'=>$sub_insudtry_no_avg_data_array,'industry_youtube_mood_graph_data'=>$industry_youtube_mood_graph_data,'industry_youtube_genre_graph_data'=>$industry_youtube_genre_graph_data, 'industry_instagram_mood_graph_data'=>$industry_instagram_mood_graph_data, 'industry_instagram_genre_graph_data'=>$industry_instagram_genre_graph_data, 'industry_tiktok_mood_graph_data'=>$industry_tiktok_mood_graph_data, 'industry_tiktok_genre_graph_data'=>$industry_tiktok_genre_graph_data, 'industry_twitter_mood_graph_data'=>$industry_twitter_mood_graph_data, 'industry_twitter_genre_graph_data'=>$industry_twitter_genre_graph_data,'industry_mood_aggr_graph_data'=>$industry_mood_aggr_graph_data, 'industry_genre_aggr_graph_data'=>$industry_genre_aggr_graph_data, 'sub_industry_year'=>$sub_industry_year, 'top_3_genre'=>$top_3_genre]);
    }

    function displaySubIndustryCVLauncher($sub_industry_id)
    {
        $cv_data_years = DB::table('tbl_cvs')->select('cv_year')->where('sub_industry_id', '=', base64_decode($sub_industry_id))->where('status', '=', 1)->where('is_active', '=', 0)->distinct()->orderBy('cv_year','desc')->get();
        
        $sub_industry_cv_data = DB::table('tbl_sub_industry')->where('sub_industry_id','=',base64_decode($sub_industry_id))->where('is_active', '=', 0)->first();
        
        $parent_industry_cv_data =  DB::table('tbl_industry')->where('industry_id','=',$sub_industry_cv_data->parent_industry_id)->first();
        
        return view('backend.views.display_sub_industry_cv_launcher',['cv_data_years'=>$cv_data_years,'sub_industry_cv_data'=>$sub_industry_cv_data,'parent_industry_cv_data'=>$parent_industry_cv_data]);
    }

    function sendDeleteAccountRequest(Request $request)
    {
        //return $request->input();
        $data = DB::table('tbl_users')->where('uid', '=', base64_decode($request->uid))->first();
        
        $email_data = [
            'name' => $data->name,
            'email' => $data->email,
            'expdate' => $data->expdate
        ];

        try {
            $admin_to_mail_id = config('custom.admin_to_mail_id');
            $bcc_mail_id = config('custom.bcc_mail_id');
            //Mail::to('amolsthorat90@gmail.com')->send(new DeleteAccountRequestEmailToAdmin($email_data));
            Mail::to($admin_to_mail_id)->bcc($bcc_mail_id)->send(new DeleteAccountRequestEmailToAdmin($email_data));
            
            $query = DB::table('tbl_delete_account_request')->insert([
                'user_id' => base64_decode($request->uid),
                'token' => '',
                'request_by' => session('LoggedUser'),
            ]);
            if($query)
            {
                // return redirect('backend.views.profile')->with('success','Email with account delete request confirmation is sent');
                //return back()->with('success','Email with account delete request confirmation is sent');
                session()->pull('LoggedUser');
                session()->pull('LoggedUserType');
                return redirect('/')->with('success','Email with account delete request is sent to admin');
            }
            else
            {
                return back();
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return back();
        }
    }
}
