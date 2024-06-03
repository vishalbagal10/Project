<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use App\Helpers\ErrorMailSender;

class SubIndustryController extends Controller
{
    public function addSubIndustry()
    {
        $industry_data = DB::table('tbl_industry')->where('is_active', '=', 0)->get();
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.add_sub_industry', ['industry_data'=>$industry_data,'cvs_year_data'=>$cvs_year]);
    }

    public function saveSubIndustry(Request $request)
    {
        // $request->validate([
        //     "sub_industry_name"=>'required|unique:tbl_sub_industry',
        //     "sub_industry_icon"=>'required|image|mimes:jpeg,png,jpg|max:2048',
        //     //"industry_description"=>'required'

        // ]);
        $request->validate([
            "sub_industry_name"=>'required|unique:tbl_sub_industry,sub_industry_name,Null,sub_industry_id,is_active,0',
            "sub_industry_icon"=>'required|image|mimes:jpeg,png,jpg|max:2048',
            //"industry_description"=>'required'

        ]);

        //return $request->input();

        /* $query = DB::table('tbl_sub_industry')->insert(
            ['parent_industry_id' => $request->parent_industry_name,
            'sub_industry_name' => $request->sub_industry_name,
            'subinddesc' => $request->sub_industry_description,
            'created_by' => session('LoggedUser')]
        );

        if($query)
        {
            return redirect('sub_industries')->with('success','Sub Industry added successfully');
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        } */


        $id = DB::table('tbl_sub_industry')->insertGetId(
            ['parent_industry_id' => $request->parent_industry_name,
            'sub_industry_name' => $request->sub_industry_name,
            'subinddesc' => $request->sub_industry_description,
            'created_by' => session('LoggedUser')]
        );

        if($id)
        {
            $image = $request->file('sub_industry_icon');
            //$img_name = str_replace(" ","-",mb_strtolower($request->industry_name, 'UTF-8')).'.'.$image->extension();
            $img_name = str_replace(" ","-",mb_strtolower(Str::substr($request->sub_industry_name,0,4)."_".$id, 'UTF-8').'.'.$image->extension());

            $destinationPath = public_path('/images/sub_industry_icons/thumbnail');
            $img = Image::make($image->path());
            $img->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$img_name);

            $destinationPath = public_path('/images/sub_industry_icons/medium');
            $img = Image::make($image->path());
            $img->resize(600, 600, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$img_name);

            if($image->move(public_path('images/sub_industry_icons/original'), $img_name))
            {
                DB::table('tbl_sub_industry')->where('sub_industry_id', $id)->update(['sub_industry_icon_name' => $img_name]);
                return redirect('sub_industries')->with('success','Sub Industry added successfully');
            }
            else
            {
                $error_data = "Something went wrong while inserting /updating Sub Industry-".$request->sub_industry_name;
                ErrorMailSender::sendErrorMail($error_data);
                return back()->with('fail', 'Something went wrong while uploading icon/image, please try again!');
            }
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }

    public function listSubIndustries()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.sub_industry_list',['cvs_year_data'=>$cvs_year]);
    }

    function getSubIndustries(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('tbl_sub_industry')->join('tbl_industry', 'tbl_sub_industry.parent_industry_id', '=', 'tbl_industry.industry_id')->select('tbl_sub_industry.*', 'tbl_industry.industry_name')->orderBy('sub_industry_id', 'desc')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    if($data->is_active == '0')
                    {
                        $actionBtn = '<a href="edit-sub-industry/'.base64_encode($data->sub_industry_id).'" title="Click here eidt" class="edit btn btn-success btn-sm" onClick="addLoader()">Edit</a> <a href="disable-sub-industry/'.base64_encode($data->sub_industry_id).'" title="Click here to disable sub industry" class="delete btn btn-success btn-sm" onClick="addLoader()">Disable</a>';
                    }
                    else
                    {
                        //$actionBtn = '<a href="edit-industry/'.base64_encode($data->industry_id).'" title="Click here eidt" class="edit btn btn-success btn-sm" onClick="addLoader()">Edit</a> <a href="enable-industry/'.base64_encode($data->industry_id).'" title="Click here to enable industry" class="edit btn btn-success btn-sm" onClick="addLoader()">Enable</a>';
                        $actionBtn = '<a href="enable-sub-industry/'.base64_encode($data->sub_industry_id).'" title="Click here to enable sub industry" class="edit btn btn-success btn-sm" onClick="addLoader()">Enable</a>';
                    }
                    return $actionBtn;
                })
                //->addColumn('sub_industry_icon_name', function ($data) { return '<img src="public/images/sub_industry_icons/thumbnail/'.$data->sub_industry_icon_name.'?i='.microtime().'" border="0" width="150" class="img-rounded" align="center" />';})
                ->rawColumns(['action'])
                ->setRowId(function($data){
                    return $data->sub_industry_id;
                })
                ->make(true);
        }
    }

    function editSubIndustry($id)
    {
        $data = DB::table('tbl_sub_industry')->where('sub_industry_id', '=', base64_decode($id))->first();
        $parent_industry_data = DB::table('tbl_industry')->where('is_active', '=', 0)->get();
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.edit_sub_industry',['SubIndustryData'=>$data,'parent_industry_data'=>$parent_industry_data,'cvs_year_data'=>$cvs_year]);
    }

    function updateSubIndustry(Request $request)
    {
        // $request->validate([
        //     "sub_industry_name"=>'required|unique:tbl_sub_industry,sub_industry_name,'.$request->sub_industry_id.',sub_industry_id',
        //     //"industry_description"=>'required',
        //     "sub_industry_id"=>'required'
        // ]);

        $request->validate([
            "sub_industry_name"=>'required|unique:tbl_sub_industry,sub_industry_name,'.$request->sub_industry_id.',sub_industry_id,is_active,0',
            //"industry_description"=>'required',
            "sub_industry_id"=>'required'
        ]);

        if($request->parent_industry_name == '' || $request->parent_industry_name == null)
        {
            $parent_industry_name_id = $request->old_parent_industry_name;
        }
        else
        {
            $parent_industry_name_id = $request->parent_industry_name;
        }

        /* $update_query =  DB::table('tbl_sub_industry')
                            ->where('sub_industry_id', $request->sub_industry_id)
                            ->update(['parent_industry_id' => $parent_industry_name_id,'sub_industry_name' => $request->sub_industry_name,'subinddesc' => $request->sub_industry_description, 'edited_by'=>session('LoggedUser')]);

        if($update_query)
        {
            return redirect('sub_industries')->with('success','Sub Industry data updated successfully');
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        } */

        if($request->hasfile('sub_industry_icon'))
        {
            $request->validate(["sub_industry_icon"=>'image|mimes:jpeg,png,jpg|max:2048']);
            $old_sub_industry_data = DB::table('tbl_sub_industry')->where('sub_industry_id', $request->sub_industry_id)->first();

            if($old_sub_industry_data->sub_industry_name != $request->sub_industry_name || $old_sub_industry_data->sub_industry_icon_name != $request->sub_industry_name)
            {
                if($old_sub_industry_data->sub_industry_icon_name != "" && $old_sub_industry_data->sub_industry_icon_name != null){
                    $og_file_path = public_path('/images/sub_industry_icons/original/').$old_sub_industry_data->sub_industry_icon_name;
                    unlink($og_file_path);
                    $medium_file_path = public_path('/images/sub_industry_icons/medium/').$old_sub_industry_data->sub_industry_icon_name;
                    unlink($medium_file_path);
                    $thumbnail_file_path = public_path('/images/sub_industry_icons/thumbnail/').$old_sub_industry_data->sub_industry_icon_name;
                    unlink($thumbnail_file_path);
                }
            }

            //$image = $request->industry_icon;
            //$img_name = str_replace(" ","-",mb_strtolower($request->industry_name, 'UTF-8').'.'.$image->getClientOriginalExtension());

            $image = $request->file('sub_industry_icon');
            //$img_name = str_replace(" ","-",mb_strtolower($request->industry_name, 'UTF-8')).'.'.$image->extension();
            $img_name = str_replace(" ","-",mb_strtolower(Str::substr($request->sub_industry_name,0,4)."_".$request->sub_industry_id, 'UTF-8').'.'.$image->extension());

            $destinationPath = public_path('/images/sub_industry_icons/thumbnail');
            $img = Image::make($image->path());
            $img->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$img_name);

            $destinationPath = public_path('/images/sub_industry_icons/medium');
            $img = Image::make($image->path());
            $img->resize(600, 600, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$img_name);

            if($image->move(public_path('images/sub_industry_icons/original'), $img_name))
            {
                DB::table('tbl_sub_industry')
                            ->where('sub_industry_id', $request->sub_industry_id)
                            ->update(['sub_industry_icon_name' => '']);

                //DB::enableQueryLog();
                //$iname = e($request->industry_name);
                $update_with_icon_query =  DB::table('tbl_sub_industry')
                                    ->where('sub_industry_id', $request->sub_industry_id)
                                    ->update(['parent_industry_id' => $parent_industry_name_id,'sub_industry_name' => $request->sub_industry_name,'sub_industry_icon_name' => $img_name,'subinddesc' => $request->sub_industry_description, 'edited_by'=>session('LoggedUser')]);
                //dd($update_query); exit;

                if($update_with_icon_query)
                {
                    return redirect('sub_industries')->with('success','Sub Industry data updated successfully');
                }
                else
                {
                    $error_data = "Something went wrong while updating Sub Industry-".$request->sub_industry_id;
                    ErrorMailSender::sendErrorMail($error_data);
                    return back()->with('fail', 'Something went wrong, please try again!');
                }
            }
        }
        else
        {
            $update_without_icon_query =  DB::table('tbl_sub_industry')
                            ->where('sub_industry_id', $request->sub_industry_id)
                            ->update(['parent_industry_id' => $parent_industry_name_id,'sub_industry_name' => $request->sub_industry_name,'subinddesc' => $request->sub_industry_description, 'edited_by'=>session('LoggedUser')]);

            if($update_without_icon_query)
            {
                return redirect('sub_industries')->with('success','Sub Industry data updated successfully');
            }
            else
            {
                $error_data = "Something went wrong while updating Sub Industry-".$request->sub_industry_id;
                ErrorMailSender::sendErrorMail($error_data);
                return back()->with('fail', 'Something went wrong, please try again!');
            }
        }

    }

    function enableSubIndustry($id)
    {
        $update_query = DB::table('tbl_sub_industry')
                            ->where('sub_industry_id', base64_decode($id))
                            ->update(['is_active' => '0', 'edited_by'=>session('LoggedUser')]);

        if($update_query)
        {
            return back()->with('success','Sub Industry enabled successfully');
        }
        else
        {
            $error_data = "Something went wrong while enabling Sub Industry-".base64_decode($id);
            ErrorMailSender::sendErrorMail($error_data);
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }

    function disableSubIndustry($id)
    {
        // echo $id;
        // exit;

        $chk_mapping_status = DB::table('tbl_cvs')->where('sub_industry_id', base64_decode($id))->where('is_active', 0)->where('status', 1)->get();
        if(count($chk_mapping_status)==0)
        {
            $update_query = DB::table('tbl_sub_industry')
                                ->where('sub_industry_id', base64_decode($id))
                                ->update(['is_active' => '1', 'edited_by'=>session('LoggedUser')]);

            if($update_query)
            {
                return back()->with('success','Sub Industry disabled successfully');
            }
            else
            {
                $error_data = "Something went wrong while disabling Sub Industry-".base64_decode($id);
                ErrorMailSender::sendErrorMail($error_data);
                return back()->with('fail', 'Something went wrong, please try again!');
            }
        }
        else
        {
            return back()->with('fail', 'Sub industry can not be disable, because it is already mapped with one of the sanpshot is the system');
        }
    }
}
