<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use App\Helpers\ErrorMailSender;

class IndustryController extends Controller
{
    public function addIndustry()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.add_industry',['cvs_year_data'=>$cvs_year]);
    }

    /* public function saveIndustry(Request $request)
    {
        $request->validate([
            "industry_name"=>'required|unique:tbl_industry',
            "industry_icon"=>'required|image|mimes:jpeg,png,jpg|max:2048',
            //"industry_description"=>'required'

        ]);

        //return $request->input();

        $image = $request->file('industry_icon');
        $img_name = str_replace(" ","-",mb_strtolower($request->industry_name, 'UTF-8')).'.'.$image->extension();

        $destinationPath = public_path('/images/industry_icons/thumbnail');
        $img = Image::make($image->path());
        $img->resize(200, 200, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$img_name);

        $destinationPath = public_path('/images/industry_icons/medium');
        $img = Image::make($image->path());
        $img->resize(600, 600, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$img_name);

        if($image->move(public_path('images/industry_icons/original'), $img_name))
        {

            $query = DB::table('tbl_industry')->insert(
                ['industry_name' => $request->industry_name,
                'industry_icon_name' => $img_name,
                'inddesc' => $request->industry_description,
                'created_by' => session('LoggedUser')]
            );

            if($query)
            {
                return redirect('industries')->with('success','Industry added successfully');
            }
            else
            {
                return back()->with('fail', 'Something went wrong, please try again!');
            }
        }
        else
        {
            return back()->with('fail', 'Something went wrong while uploading icon/image, please try again!');
        }
    } */

    public function saveIndustry(Request $request)
    {
        // $request->validate([
        //     "industry_name"=>'required|unique:tbl_industry',
        //     "industry_icon"=>'required|image|mimes:jpeg,png,jpg|max:2048',
        //     //"industry_description"=>'required'

        // ]);
        $request->validate([
            "industry_name"=>'required|unique:tbl_industry,industry_name,Null,industry_id,is_active,0',
            "industry_icon"=>'required|image|mimes:jpeg,png,jpg|max:2048',
            //"industry_description"=>'required'

        ]);

        //return $request->input();

        $id = DB::table('tbl_industry')->insertGetId(
            ['industry_name' => $request->industry_name,
            'inddesc' => $request->industry_description,
            'created_by' => session('LoggedUser')]
        );

        if($id)
        {
            $image = $request->file('industry_icon');
            //$img_name = str_replace(" ","-",mb_strtolower($request->industry_name, 'UTF-8')).'.'.$image->extension();
            $img_name = str_replace(" ","-",mb_strtolower(Str::substr($request->industry_name,0,4)."_".$id, 'UTF-8').'.'.$image->extension());

            $destinationPath = public_path('/images/industry_icons/thumbnail');
            $img = Image::make($image->path());
            $img->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$img_name);

            $destinationPath = public_path('/images/industry_icons/medium');
            $img = Image::make($image->path());
            $img->resize(600, 600, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$img_name);

            if($image->move(public_path('images/industry_icons/original'), $img_name))
            {
                DB::table('tbl_industry')->where('industry_id', $id)->update(['industry_icon_name' => $img_name]);
                return redirect('industries')->with('success','Industry added successfully');
            }
            else
            {
                $error_data = "Something went wrong while inserting Industry-".$request->industry_name;
                ErrorMailSender::sendErrorMail($error_data);
                return back()->with('fail', 'Something went wrong while uploading icon/image, please try again!');
            }
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }

    public function listIndustries()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.industry_list',['cvs_year_data'=>$cvs_year]);
    }

    function getIndustries(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('tbl_industry')->orderBy('industry_id', 'desc')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    if($data->is_active == '0')
                    {
                        $actionBtn = '<a href="edit-industry/'.base64_encode($data->industry_id).'" title="Click here eidt" class="edit btn btn-success btn-sm" onClick="addLoader()">Edit</a> <a href="disable-industry/'.base64_encode($data->industry_id).'" title="Click here to disable industry" class="delete btn btn-success btn-sm" onClick="addLoader()">Disable</a>';
                    }
                    else
                    {
                        //$actionBtn = '<a href="edit-industry/'.base64_encode($data->industry_id).'" title="Click here eidt" class="edit btn btn-success btn-sm" onClick="addLoader()">Edit</a> <a href="enable-industry/'.base64_encode($data->industry_id).'" title="Click here to enable industry" class="edit btn btn-success btn-sm" onClick="addLoader()">Enable</a>';
                        $actionBtn = '<a href="enable-industry/'.base64_encode($data->industry_id).'" title="Click here to enable industry" class="edit btn btn-success btn-sm" onClick="addLoader()">Enable</a>';
                    }
                    return $actionBtn;
                })
                ->addColumn('industry_icon_name', function ($data) { return '<img src="public/images/industry_icons/thumbnail/'.$data->industry_icon_name.'?i='.microtime().'" border="0" width="150" class="img-rounded" align="center" />';})
                ->rawColumns(['action','industry_icon_name'])
                ->setRowId(function($data){
                    return $data->industry_id;
                })
                ->make(true);
        }
    }

    function editIndustry($id)
    {
        $data = DB::table('tbl_industry')->where('industry_id', '=', base64_decode($id))->first();
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.edit_industry',['IndustryData'=>$data,'cvs_year_data'=>$cvs_year]);
    }

    function updateIndustry(Request $request)
    {
        // $request->validate([
        //     "industry_name"=>'required|unique:tbl_industry,industry_name,'.$request->industry_id.',industry_id',
        //     //"industry_description"=>'required',
        //     "industry_id"=>'required'
        // ]);
        $request->validate([
            "industry_name"=>'required|unique:tbl_industry,industry_name,'.$request->industry_id.',industry_id,is_active,0',
            //"industry_description"=>'required',
            "industry_id"=>'required'
        ]);

        if($request->hasfile('industry_icon'))
        {
            $request->validate(["industry_icon"=>'image|mimes:jpeg,png,jpg|max:2048']);
            $old_industry_data = DB::table('tbl_industry')->where('industry_id', $request->industry_id)->first();
            if($old_industry_data->industry_name != $request->industry_name || $old_industry_data->industry_icon_name != $request->industry_name)
            {
                if($old_industry_data->industry_icon_name != "" && $old_industry_data->industry_icon_name != null){
                    $og_file_path = public_path('/images/industry_icons/original/').$old_industry_data->industry_icon_name;
                    unlink($og_file_path);
                    $medium_file_path = public_path('/images/industry_icons/medium/').$old_industry_data->industry_icon_name;
                    unlink($medium_file_path);
                    $thumbnail_file_path = public_path('/images/industry_icons/thumbnail/').$old_industry_data->industry_icon_name;
                    unlink($thumbnail_file_path);
                }
            }

            //$image = $request->industry_icon;
            //$img_name = str_replace(" ","-",mb_strtolower($request->industry_name, 'UTF-8').'.'.$image->getClientOriginalExtension());

            $image = $request->file('industry_icon');
            //$img_name = str_replace(" ","-",mb_strtolower($request->industry_name, 'UTF-8')).'.'.$image->extension();
            $img_name = str_replace(" ","-",mb_strtolower(Str::substr($request->industry_name,0,4)."_".$request->industry_id, 'UTF-8').'.'.$image->extension());

            $destinationPath = public_path('/images/industry_icons/thumbnail');
            $img = Image::make($image->path());
            $img->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$img_name);

            $destinationPath = public_path('/images/industry_icons/medium');
            $img = Image::make($image->path());
            $img->resize(600, 600, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$img_name);

            if($image->move(public_path('images/industry_icons/original'), $img_name))
            {
                DB::table('tbl_industry')
                            ->where('industry_id', $request->industry_id)
                            ->update(['industry_icon_name' => '']);

                //DB::enableQueryLog();
                //$iname = e($request->industry_name);
                $update_with_icon_query =  DB::table('tbl_industry')
                                    ->where('industry_id', $request->industry_id)
                                    ->update(['industry_name' => $request->industry_name,'industry_icon_name' => $img_name,'inddesc' => $request->industry_description, 'edited_by'=>session('LoggedUser')]);
                //dd($update_query); exit;

                if($update_with_icon_query)
                {
                    return redirect('industries')->with('success','Industry data updated successfully');
                }
                else
                {
                    $error_data = "Something went wrong while updating Industry-".$request->industry_id;
                    ErrorMailSender::sendErrorMail($error_data);
                    return back()->with('fail', 'Something went wrong, please try again!');
                }
            }
        }
        else
        {
            $update_without_icon_query =  DB::table('tbl_industry')
                            ->where('industry_id', $request->industry_id)
                            ->update(['industry_name' => $request->industry_name,'inddesc' => $request->industry_description, 'edited_by'=>session('LoggedUser')]);

            if($update_without_icon_query)
            {
                return redirect('industries')->with('success','Industry data updated successfully');
            }
            else
            {
                $error_data = "Something went wrong while updating Industry-".$request->industry_id;
                ErrorMailSender::sendErrorMail($error_data);
                return back()->with('fail', 'Something went wrong, please try again!');
            }
        }

    }

    function enableIndustry($id)
    {
        $update_query = DB::table('tbl_industry')
                            ->where('industry_id', base64_decode($id))
                            ->update(['is_active' => '0', 'edited_by'=>session('LoggedUser')]);

        if($update_query)
        {
            return back()->with('success','Industry enabled successfully');
        }
        else
        {
            $error_data = "Something went wrong while enabling Industry-".base64_decode($id);
            ErrorMailSender::sendErrorMail($error_data);
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }

    function disableIndustry($id)
    {
        //echo base64_decode($id);

        $chk_mapping_status = DB::table('tbl_cvs')->where('industry_id', base64_decode($id))->where('is_active', 0)->where('status', 1)->get();
        //echo gettype(count($chk_mapping_status));
        // exit;
        if(count($chk_mapping_status)==0)
        {
            $update_query = DB::table('tbl_industry')
                            ->where('industry_id', base64_decode($id))
                            ->update(['is_active' => '1', 'edited_by'=>session('LoggedUser')]);

            if($update_query)
            {
                return back()->with('success','Industry disabled successfully');
            }
            else
            {
                $error_data = "Something went wrong while disabling Industry-".base64_decode($id);
                ErrorMailSender::sendErrorMail($error_data);
                return back()->with('fail', 'Something went wrong, please try again!');
            }
        }
        else
        {

            return back()->with('fail', 'Industry can not be disable, because it is already mapped with one of the sanpshot is the system');
        }

    }
}
