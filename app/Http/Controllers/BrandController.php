<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class BrandController extends Controller
{
    public function addBrand()
    {
        return view('backend.views.add_brand');
    }

    public function saveBrand(Request $request)
    {
        $request->validate([
            "brand_name"=>'required|unique:tbl_brand,brand_name',
            "brand_icon"=>'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            //"brand_description"=>'required'
        ]);
        
        //return $request->input();
        $image = $request->brand_icon;
        $img_name = mb_strtolower($request->brand_name, 'UTF-8').'.'.$image->getClientOriginalExtension();
        if($image->move(public_path('images/brand_icons'), $img_name))
        {
            $query = DB::table('tbl_brand')->insert(
                ['brand_name' => $request->brand_name,
                'brand_icon_name' => $img_name,
                'brand_description' => $request->brand_description,
                'created_by' => session('LoggedUser')]
            );

            if($query)
            {
                return redirect('brands')->with('success','Brand added successfully');
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
        
    }

    public function listBrands()
    {
        return view('backend.views.brand_list');
    }

    function getBrands(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('tbl_brand')->orderBy('brand_id', 'desc')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    if($data->is_active == '0')
                    {
                        $actionBtn = '<a href="edit-brand/'.base64_encode($data->brand_id).'" title="Click here eidt" class="edit btn btn-success btn-sm" onClick="addLoader()">Edit</a> <a href="disable-brand/'.base64_encode($data->brand_id).'" title="Click here to disable brand" class="delete btn btn-success btn-sm" onClick="addLoader()">Disable</a>';
                    }
                    else
                    {
                        $actionBtn = '<a href="edit-brand/'.base64_encode($data->brand_id).'" title="Click here eidt" class="edit btn btn-success btn-sm" onClick="addLoader()">Edit</a> <a href="enable-brand/'.base64_encode($data->brand_id).'" title="Click here to enable brand" class="edit btn btn-success btn-sm" onClick="addLoader()">Enable</a>';
                    }
                    return $actionBtn;
                })
                ->addColumn('brand_icon_name', function ($data) { return '<img src="public/images/brand_icons/'.$data->brand_icon_name.'?i='.microtime().'" border="0" width="150" class="img-rounded" align="center" />';})
                ->rawColumns(['action','brand_icon_name'])
                ->setRowId(function($data){
                    return $data->brand_id;
                })
                ->make(true);
        }
    }

    function editBrand($id)
    {
        $data = DB::table('tbl_brand')->where('brand_id', '=', base64_decode($id))->first();
        return view('backend.views.edit_brand',['Brand'=>$data]);
    }

    function updateBrand(Request $request)
    {
        $request->validate([
            "brand_name"=>'required|unique:tbl_brand,brand_name,'.$request->brand_id.',brand_id',
            //"brand_description"=>'required',
            "brand_id"=>'required'
        ]);


        if($request->hasfile('brand_icon'))
        {
            $request->validate(["brand_icon"=>'image|mimes:jpeg,png,jpg,gif|max:2048']);
            $old_brand_data = DB::table('tbl_brand')->where('brand_id', $request->brand_id)->first();
            if($old_brand_data->brand_name != $request->brand_name || $old_brand_data->brand_icon_name != $request->brand_icon)
            {
                $file_path = public_path('/images/brand_icons/').$old_brand_data->brand_icon_name;
                unlink($file_path);
            } 
            
            $image = $request->brand_icon;
            $img_name = mb_strtolower($request->brand_name, 'UTF-8').'.'.$image->getClientOriginalExtension();
            if($image->move(public_path('images/brand_icons'), $img_name))
            {
                DB::table('tbl_brand')
                            ->where('brand_id', $request->brand_id)
                            ->update(['brand_icon_name' => '']);
                $update_with_icon_query =  DB::table('tbl_brand')
                            ->where('brand_id', $request->brand_id)
                            ->update(['brand_name' => $request->brand_name,'brand_icon_name' => $img_name,'brand_description' => $request->brand_description, 'edited_by'=>session('LoggedUser')]);
                                
                if($update_with_icon_query)
                {
                    return redirect('brands')->with('success','Brand data updated successfully');
                }
                else
                {
                    return back()->with('fail', 'Something went wrong, please try again1!');
                }
                
            }
        }
        else
        {
            $update_without_icon_query =  DB::table('tbl_brand')
                            ->where('brand_id', $request->brand_id)
                            ->update(['brand_name' => $request->brand_name,'brand_description' => $request->brand_description, 'edited_by'=>session('LoggedUser')]);

            if($update_without_icon_query)
            {
                return redirect('brands')->with('success','Brand data updated successfully');
            }
            else
            {
                return back()->with('fail', 'Something went wrong, please try again!');
            }
        }
    }

    function enableBrand($id)
    {
        $update_query = DB::table('tbl_brand')
                            ->where('brand_id', base64_decode($id))
                            ->update(['is_active' => '0', 'edited_by'=>session('LoggedUser')]);

        if($update_query)
        {
            return back()->with('success','Brand enabled successfully');
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }

    function disableBrand($id)
    {
        // echo $id;
        // exit;
        $update_query = DB::table('tbl_brand')
                            ->where('brand_id', base64_decode($id))
                            ->update(['is_active' => '1', 'edited_by'=>session('LoggedUser')]);

        if($update_query)
        {
            return back()->with('success','Brand disabled successfully');
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }
}
