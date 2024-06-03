<?php

namespace App\Http\Controllers;

//use App\Models\Qualitative;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class QualitativeController extends Controller
{
    public function addQualitative()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.add_qualitative',['cvs_year_data'=>$cvs_year]);
    }

    public function saveQualitative(Request $request)
    {
        $request->validate([
            "qualitative_name"=>'required|unique:tbl_qualitative',
            //"qualitative_description"=>'required'
        ]);
        
        //return $request->input();

        $query = DB::table('tbl_qualitative')->insert(
            ['qualitative_name' => $request->qualitative_name,
            'qualitative_description' => $request->qualitative_description,
            'created_by' => session('LoggedUser')]
        );

        if($query)
        {
            return redirect('qualitatives')->with('success','Qualitative added successfully');
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }

    public function listQualitatives()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.qualitative_list',['cvs_year_data'=>$cvs_year]);
    }

    function getQualitatives(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('tbl_qualitative')->orderBy('qualitative_id', 'desc')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    if($data->is_active == '0')
                    {
                        $actionBtn = '<a href="edit-qualitative/'.base64_encode($data->qualitative_id ).'" title="Click here eidt" class="edit btn btn-success btn-sm" onClick="addLoader()">Edit</a> <a href="disable-qualitative/'.base64_encode($data->qualitative_id ).'" title="Click here to disable qualitative" class="delete btn btn-success btn-sm" onClick="addLoader()">Disable</a>';
                    }
                    else
                    {
                        //$actionBtn = '<a href="edit-qualitative/'.base64_encode($data->qualitative_id ).'" title="Click here eidt" class="edit btn btn-success btn-sm" onClick="addLoader()">Edit</a> <a href="enable-qualitative/'.base64_encode($data->qualitative_id ).'" title="Click here to enable qualitative" class="edit btn btn-success btn-sm" onClick="addLoader()">Enable</a>';
                        $actionBtn = '<a href="enable-qualitative/'.base64_encode($data->qualitative_id ).'" title="Click here to enable qualitative" class="edit btn btn-success btn-sm" onClick="addLoader()">Enable</a>';
                    }
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->setRowId(function($data){
                    return $data->qualitative_id ;
                })
                ->make(true);
        }
    }

    function editQualitative($id)
    {
        $data = DB::table('tbl_qualitative')->where('qualitative_id', '=', base64_decode($id))->first();
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.edit_qualitative',['QualitativeData'=>$data,'cvs_year_data'=>$cvs_year]);
    }

    function updateQualitative(Request $request)
    {
        $request->validate([
            "qualitative_name"=>'required|unique:tbl_qualitative,qualitative_name,'.$request->qualitative_id .',qualitative_id',
            //"qualitative_description"=>'required',
            "qualitative_id"=>'required'
        ]);

        $update_query =  DB::table('tbl_qualitative')
                            ->where('qualitative_id', $request->qualitative_id)
                            ->update(['qualitative_name' => $request->qualitative_name,'qualitative_description' => $request->qualitative_description, 'edited_by'=>session('LoggedUser')]);
            
        if($update_query)
        {
            return redirect('qualitatives')->with('success','Qualitative data updated successfully');
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        }

    }

    function enableQualitative($id)
    {
        $update_query = DB::table('tbl_qualitative')
                            ->where('qualitative_id', base64_decode($id))
                            ->update(['is_active' => '0', 'edited_by'=>session('LoggedUser')]);

        if($update_query)
        {
            return back()->with('success','Qualitative enabled successfully');
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }

    function disableQualitative($id)
    {
        // echo $id;
        // exit;
        $chk_mapping_status = DB::table('tbl_cv_block_10_data')->join('tbl_cvs','tbl_cv_block_10_data.cv_id','tbl_cvs.cv_id')->select('tbl_cvs.*')->where('tbl_cv_block_10_data.b10_name_id', base64_decode($id))->where('tbl_cv_block_10_data.is_active', 0)->where('tbl_cvs.is_active', 0)->where('tbl_cvs.status', 1)->get();
        if(count($chk_mapping_status)==0)
        {
            $update_query = DB::table('tbl_qualitative')
                                ->where('qualitative_id', base64_decode($id))
                                ->update(['is_active' => '1', 'edited_by'=>session('LoggedUser')]);

            if($update_query)
            {
                return back()->with('success','Qualitative disabled successfully');
            }
            else
            {
                return back()->with('fail', 'Something went wrong, please try again!');
            }
        }
        else
        {           
            return back()->with('fail', 'Qualitative can not be disable, because it is already mapped with one of the sanpshot is the system');
        }
    }
}
