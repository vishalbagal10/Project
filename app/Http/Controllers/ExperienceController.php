<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use App\Helpers\ErrorMailSender;

class ExperienceController extends Controller
{
    public function addExperience()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.add_experience',['cvs_year_data'=>$cvs_year]);
    }

    public function saveExperience(Request $request)
    {
        $request->validate([
            "experience_name"=>'required|unique:tbl_experience,experience_name',
            // "experience_icon"=>'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            //"experience_description"=>'required'
        ]);
        
        //return $request->input(); 
        $last_order_query = DB::table('tbl_experience')->where('is_active', '=', 0)->orderBy('display_order', 'desc')->limit(1)->first();
        $last_order = $last_order_query->display_order;
        
        $query = DB::table('tbl_experience')->insert(
            ['experience_name' => $request->experience_name,
            'experience_description' => $request->experience_description,
            'display_order' => $last_order+1,
            'created_by' => session('LoggedUser')]
        );

        if($query)
        {
            return redirect('experiences')->with('success','Experience added successfully');
        }
        else
        {
            $error_data = "Something went wrong while inserting Experience-".$request->experience_name;
            ErrorMailSender::sendErrorMail($error_data);
            return back()->with('fail', 'Something went wrong, please try again!');
        }

        /* $image = $request->experience_icon;
        $img_name = mb_strtolower($request->experience_name, 'UTF-8').'.'.$image->getClientOriginalExtension();
        if($image->move(public_path('images/experience_icons'), $img_name))
        {
            $query = DB::table('tbl_experience')->insert(
                ['experience_name' => $request->experience_name,
                'experience_icon_name' => $img_name,
                'experience_description' => $request->experience_description,
                'created_by' => session('LoggedUser')]
            );

            if($query)
            {
                return redirect('experiences')->with('success','Experience added successfully');
            }
            else
            {
                return back()->with('fail', 'Something went wrong, please try again!');
            }
        }
        else
        {
            return back()->with('fail', 'Something went wrong while uploading icon/image, please try again!');
        } */
      
    }

    public function listExperiences()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.experience_list',['cvs_year_data'=>$cvs_year]);
    }

    function getExperiences(Request $request)
    {
        /* if ($request->ajax()) {
            $data = DB::table('tbl_experience')->orderBy('experience_id', 'desc')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    if($data->is_active == '0')
                    {
                        $actionBtn = '<a href="edit-experience/'.base64_encode($data->experience_id).'" title="Click here eidt" class="edit btn btn-success btn-sm">Edit</a> <a href="disable-experience/'.base64_encode($data->experience_id).'" title="Click here to disable experience" class="delete btn btn-success btn-sm">Disable</a>';
                    }
                    else
                    {
                        $actionBtn = '<a href="edit-experience/'.base64_encode($data->experience_id).'" title="Click here eidt" class="edit btn btn-success btn-sm">Edit</a> <a href="enable-experience/'.base64_encode($data->experience_id).'" title="Click here to enable experience" class="edit btn btn-success btn-sm">Enable</a>';
                    }
                    return $actionBtn;
                })
                ->addColumn('experience_icon_name', function ($data) { return '<img src="public/images/experience_icons/'.$data->experience_icon_name.'?i='.microtime().'" border="0" width="150" class="img-rounded" align="center" />';})
                ->rawColumns(['action','experience_icon_name'])
                ->setRowId(function($data){
                    return $data->experience_id;
                })
                ->make(true);
        } */
        if ($request->ajax()) {
            $data = DB::table('tbl_experience')->orderBy('experience_id', 'desc')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    if($data->is_active == '0')
                    {
                        $actionBtn = '<a href="edit-experience/'.base64_encode($data->experience_id).'" title="Click here eidt" class="edit btn btn-success btn-sm" onClick="addLoader()">Edit</a> <a href="disable-experience/'.base64_encode($data->experience_id).'" title="Click here to disable experience" class="delete btn btn-success btn-sm" onClick="addLoader()">Disable</a>';
                    }
                    else
                    {
                        //$actionBtn = '<a href="edit-experience/'.base64_encode($data->experience_id).'" title="Click here eidt" class="edit btn btn-success btn-sm" onClick="addLoader()">Edit</a> <a href="enable-experience/'.base64_encode($data->experience_id).'" title="Click here to enable experience" class="edit btn btn-success btn-sm" onClick="addLoader()">Enable</a>';
                        $actionBtn = '<a href="enable-experience/'.base64_encode($data->experience_id).'" title="Click here to enable experience" class="edit btn btn-success btn-sm" onClick="addLoader()">Enable</a>';
                    }
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->setRowId(function($data){
                    return $data->experience_id;
                })
                ->make(true);
        }
    }

    function editExperience($id)
    {
        $data = DB::table('tbl_experience')->where('experience_id', '=', base64_decode($id))->first();
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.edit_experience',['Experience'=>$data,'cvs_year_data'=>$cvs_year]);
    }

    function updateExperience(Request $request)
    {
        $request->validate([
            "experience_name"=>'required|unique:tbl_experience,experience_name,'.$request->experience_id.',experience_id',
            //"experience_description"=>'required',
            "experience_id"=>'required'
        ]);


        /* if($request->hasfile('experience_icon'))
        {
            $request->validate(["experience_icon"=>'image|mimes:jpeg,png,jpg,gif|max:2048']);
            $old_experience_data = DB::table('tbl_experience')->where('experience_id', $request->experience_id)->first();
            if($old_experience_data->experience_name != $request->experience_name || $old_experience_data->experience_icon_name != $request->experience_icon)
            {
                $file_path = public_path('/images/experience_icons/').$old_experience_data->experience_icon_name;
                unlink($file_path);
            } 
            
            $image = $request->experience_icon;
            $img_name = mb_strtolower($request->experience_name, 'UTF-8').'.'.$image->getClientOriginalExtension();
            if($image->move(public_path('images/experience_icons'), $img_name))
            {
                DB::table('tbl_experience')
                            ->where('experience_id', $request->experience_id)
                            ->update(['experience_icon_name' => '']);
                $update_with_icon_query =  DB::table('tbl_experience')
                            ->where('experience_id', $request->experience_id)
                            ->update(['experience_name' => $request->experience_name,'experience_icon_name' => $img_name,'experience_description' => $request->experience_description, 'edited_by'=>session('LoggedUser')]);
                                
                if($update_with_icon_query)
                {
                    return redirect('experiences')->with('success','Experience data updated successfully');
                }
                else
                {
                    return back()->with('fail', 'Something went wrong, please try again1!');
                }
                
            }
        }
        else
        {
            $update_without_icon_query =  DB::table('tbl_experience')
                            ->where('experience_id', $request->experience_id)
                            ->update(['experience_name' => $request->experience_name,'experience_description' => $request->experience_description, 'edited_by'=>session('LoggedUser')]);

            if($update_without_icon_query)
            {
                return redirect('experiences')->with('success','Experience data updated successfully');
            }
            else
            {
                return back()->with('fail', 'Something went wrong, please try again!');
            }
        } */

        $update_without_icon_query =  DB::table('tbl_experience')
                            ->where('experience_id', $request->experience_id)
                            ->update(['experience_name' => $request->experience_name,'experience_description' => $request->experience_description, 'edited_by'=>session('LoggedUser')]);

        if($update_without_icon_query)
        {
            return redirect('experiences')->with('success','Experience data updated successfully');
        }
        else
        {
            $error_data = "Something went wrong while updating Experience-".$request->experience_id;
            ErrorMailSender::sendErrorMail($error_data);
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }

    function enableExperience($id)
    {
        $update_query = DB::table('tbl_experience')
                            ->where('experience_id', base64_decode($id))
                            ->update(['is_active' => '0', 'edited_by'=>session('LoggedUser')]);

        if($update_query)
        {
            return back()->with('success','Experience enabled successfully');
        }
        else
        {
            $error_data = "Something went wrong while enabling Experience-".base64_decode($id);
            ErrorMailSender::sendErrorMail($error_data);
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }

    function disableExperience($id)
    {
        // echo $id;
        // exit;
        $chk_mapping_status = DB::table('tbl_cv_block_13_data')->join('tbl_cvs','tbl_cv_block_13_data.cv_id','tbl_cvs.cv_id')->select('tbl_cvs.*')->where('tbl_cv_block_13_data.b13_name_id', base64_decode($id))->where('tbl_cv_block_13_data.is_active', 0)->where('tbl_cvs.is_active', 0)->where('tbl_cvs.status', 1)->get();
        if(count($chk_mapping_status)==0)
        {
            $update_query = DB::table('tbl_experience')
                                ->where('experience_id', base64_decode($id))
                                ->update(['is_active' => '1', 'edited_by'=>session('LoggedUser')]);

            if($update_query)
            {
                return back()->with('success','Experience disabled successfully');
            }
            else
            {
                $error_data = "Something went wrong while disabling Experience-".base64_decode($id);
                ErrorMailSender::sendErrorMail($error_data);
                return back()->with('fail', 'Something went wrong, please try again!');
            }
        }
        else
        {           
            return back()->with('fail', 'Experience can not be disable, because it is already mapped with one of the sanpshot is the system');
        }
    }
}
