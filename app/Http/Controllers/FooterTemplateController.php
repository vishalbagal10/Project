<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class FooterTemplateController extends Controller
{
    public function addFooterTemplate()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.add_cv_footer_template',['cvs_year_data'=>$cvs_year]);
    }

    public function saveFooterTemplate(Request $request)
    {
        $request->validate([
            "footer_template_name"=>'required|unique:tbl_footer_template,footer_template_name',
            "footer_title"=>'required',
            "footer_description"=>'required'
        ]);
        
        //return $request->input();
        
        $query = DB::table('tbl_footer_template')->insert(
            ['footer_template_name' => $request->footer_template_name,
            'footer_title' => $request->footer_title,
            'footer_description' => $request->footer_description,
            'created_by' => session('LoggedUser')]
        );

        if($query)
        {
            return redirect('footer-templates')->with('success','Footer Template added successfully');
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        }        
        
    }

    public function listFooterTemplates()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.footer_template_list',['cvs_year_data'=>$cvs_year]);
    }

    function getFooterTemplates(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('tbl_footer_template')->orderBy('footer_template_id', 'desc')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('footer_description', function($data){
                    return $data->footer_description;
                })
                ->addColumn('action', function($data){
                    if($data->is_active == '0')
                    {
                        $actionBtn = '<a href="edit-footer-template/'.base64_encode($data->footer_template_id).'" title="Click here eidt" class="edit btn btn-success btn-sm" onClick="addLoader()">Edit</a> <a href="disable-footer-template/'.base64_encode($data->footer_template_id).'" title="Click here to disable footer template" class="delete btn btn-success btn-sm" onClick="addLoader()">Disable</a>';
                    }
                    else
                    {
                        //$actionBtn = '<a href="edit-footer-template/'.base64_encode($data->footer_template_id).'" title="Click here eidt" class="edit btn btn-success btn-sm" onClick="addLoader()">Edit</a> <a href="enable-footer-template/'.base64_encode($data->footer_template_id).'" title="Click here to enable footer template" class="edit btn btn-success btn-sm" onClick="addLoader()">Enable</a>';
                        $actionBtn = '<a href="enable-footer-template/'.base64_encode($data->footer_template_id).'" title="Click here to enable footer template" class="edit btn btn-success btn-sm" onClick="addLoader()">Enable</a>';
                    }
                    return $actionBtn;
                })
                ->rawColumns(['footer_description','action'])
                ->setRowId(function($data){
                    return $data->footer_template_id;
                })
                ->make(true);
        }
    }

    function editFooterTemplate($id)
    {
        $data = DB::table('tbl_footer_template')->where('footer_template_id', '=', base64_decode($id))->first();
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.edit_cv_footer_template',['FooterTemplateData'=>$data,'cvs_year_data'=>$cvs_year]);
    }

    function updateFooterTemplate(Request $request)
    {
        $request->validate([
            "footer_template_name"=>'required|unique:tbl_footer_template,footer_template_name,'.$request->footer_template_id.',footer_template_id',
            "footer_title"=>'required',
            "footer_description"=>'required',
            "footer_template_id"=>'required'
        ]);

        $update_query =  DB::table('tbl_footer_template')
                            ->where('footer_template_id', $request->footer_template_id)
                            ->update(['footer_template_name' => $request->footer_template_name,'footer_title' => $request->footer_title,'footer_description' => $request->footer_description, 'edited_by'=>session('LoggedUser')]);

        if($update_query)
        {
            return redirect('footer-templates')->with('success','Footer Template data updated successfully');
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        }
        
    }

    function enableFooterTemplate($id)
    {
        $update_query = DB::table('tbl_footer_template')
                            ->where('footer_template_id', base64_decode($id))
                            ->update(['is_active' => '0', 'edited_by'=>session('LoggedUser')]);

        if($update_query)
        {
            return back()->with('success','Footer Template enabled successfully');
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }

    function disableFooterTemplate($id)
    {
        // echo $id;
        // exit;
        $update_query = DB::table('tbl_footer_template')
                            ->where('footer_template_id', base64_decode($id))
                            ->update(['is_active' => '1', 'edited_by'=>session('LoggedUser')]);

        if($update_query)
        {
            return back()->with('success','Footer Template disabled successfully');
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }
}
