<?php

namespace App\Http\Controllers;

use App\Models\MusicTaste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;

class MusicTasteController extends Controller
{
    public function addMusicTaste()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.add_music_taste',['cvs_year_data'=>$cvs_year]);
    }

    public function saveMusicTaste(Request $request)
    {
        $request->validate([
            "music_taste_name"=>'required|unique:tbl_music_taste,music_taste_name',
            "music_taste_icon"=>'required|image|mimes:jpeg,png,jpg|max:2048',
            //"music_taste_description"=>'required'
        ]);
        
        $id = DB::table('tbl_music_taste')->insertGetId(
            ['music_taste_name' => $request->music_taste_name,
            'music_taste_description' => $request->music_taste_description,
            'created_by' => session('LoggedUser')]
        );

        if($id)
        {
            $image = $request->file('music_taste_icon');
            //$img_name = str_replace(" ","-",mb_strtolower($request->music_taste_name, 'UTF-8')).'.'.$image->extension();
            $img_name = str_replace(" ","-",mb_strtolower(Str::substr($request->music_taste_name,0,4)."_".$id, 'UTF-8').'.'.$image->extension());
        
            $destinationPath = public_path('/images/music_taste_icons/thumbnail');
            $img = Image::make($image->path());
            $img->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$img_name);

            $destinationPath = public_path('/images/music_taste_icons/medium');
            $img = Image::make($image->path());
            $img->resize(600, 600, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$img_name);
    
            //$destinationPath = public_path('/images/music_taste_icons');
            //$image->move($destinationPath, $img_name);

            if($image->move(public_path('images/music_taste_icons/original'), $img_name))
            {
                DB::table('tbl_music_taste')->where('music_taste_id', $id)->update(['music_taste_icon_name' => $img_name]);
                return redirect('music-tastes')->with('success','Music Taste added successfully');
            }
            else
            {
                return back()->with('fail', 'Something went wrong while uploading icon/image, please try again!');
            }            
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        }        
    }

    public function listMusicTastes()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.music_taste_list',['cvs_year_data'=>$cvs_year]);
    }

    function getMusicTastes(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('tbl_music_taste')->orderBy('music_taste_id', 'desc')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    if($data->is_active == '0')
                    {
                        $actionBtn = '<a href="edit-music-taste/'.base64_encode($data->music_taste_id).'" title="Click here eidt" class="edit btn btn-success btn-sm" onClick="addLoader()">Edit</a> <a href="disable-music-taste/'.base64_encode($data->music_taste_id).'" title="Click here to disable music taste" class="delete btn btn-success btn-sm" onClick="addLoader()">Disable</a>';
                    }
                    else
                    {
                        //$actionBtn = '<a href="edit-music-taste/'.base64_encode($data->music_taste_id).'" title="Click here eidt" class="edit btn btn-success btn-sm" onClick="addLoader()">Edit</a> <a href="enable-music-taste/'.base64_encode($data->music_taste_id).'" title="Click here to enable music taste" class="edit btn btn-success btn-sm" onClick="addLoader()">Enable</a>';
                        $actionBtn = '<a href="enable-music-taste/'.base64_encode($data->music_taste_id).'" title="Click here to enable music taste" class="edit btn btn-success btn-sm" onClick="addLoader()">Enable</a>';
                    }
                    return $actionBtn;
                })
                ->addColumn('music_taste_icon_name', function ($data) { return '<img src="public/images/music_taste_icons/thumbnail/'.$data->music_taste_icon_name.'?i='.microtime().'" border="0" width="150" class="img-rounded" align="center" />';})
                ->rawColumns(['action','music_taste_icon_name'])
                ->setRowId(function($data){
                    return $data->music_taste_id;
                })
                ->make(true);
        }
    }

    function editMusicTaste($id)
    {
        $data = DB::table('tbl_music_taste')->where('music_taste_id', '=', base64_decode($id))->first();
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.edit_music_taste',['MusicTaste'=>$data,'cvs_year_data'=>$cvs_year]);
    }

    function updateMusicTaste(Request $request)
    {
        $request->validate([
            "music_taste_name"=>'required|unique:tbl_music_taste,music_taste_name,'.$request->music_taste_id.',music_taste_id',
            //"music_taste_description"=>'required',
            "music_taste_id"=>'required'
        ]);


        if($request->hasfile('music_taste_icon'))
        {
            $request->validate(["music_taste_icon"=>'image|mimes:jpeg,png,jpg|max:2048']);
            $old_music_taste_data = DB::table('tbl_music_taste')->where('music_taste_id', $request->music_taste_id)->first();
            if($old_music_taste_data->music_taste_name != $request->music_taste_name || $old_music_taste_data->music_taste_icon_name != $request->music_taste_name)
            {
                $og_file_path = public_path('/images/music_taste_icons/original/').$old_music_taste_data->music_taste_icon_name;
                unlink($og_file_path);
                $medium_file_path = public_path('/images/music_taste_icons/medium/').$old_music_taste_data->music_taste_icon_name;
                unlink($medium_file_path);
                $thumbnail_file_path = public_path('/images/music_taste_icons/thumbnail/').$old_music_taste_data->music_taste_icon_name;
                unlink($thumbnail_file_path);
            } 
            
            //$image = $request->music_taste_icon;
            //$img_name = str_replace(" ","-",mb_strtolower($request->music_taste_name, 'UTF-8').'.'.$image->getClientOriginalExtension());
            

            $image = $request->file('music_taste_icon');
            //$img_name = str_replace(" ","-",mb_strtolower($request->music_taste_name, 'UTF-8')).'.'.$image->extension();
            $img_name = str_replace(" ","-",mb_strtolower(Str::substr($request->music_taste_name,0,4)."_".$request->music_taste_id, 'UTF-8').'.'.$image->extension());
        
            $destinationPath = public_path('/images/music_taste_icons/thumbnail');
            $img = Image::make($image->path());
            $img->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$img_name);

            $destinationPath = public_path('/images/music_taste_icons/medium');
            $img = Image::make($image->path());
            $img->resize(600, 600, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$img_name);
            
            if($image->move(public_path('images/music_taste_icons/original'), $img_name))
            {
                DB::table('tbl_music_taste')
                            ->where('music_taste_id', $request->music_taste_id)
                            ->update(['music_taste_icon_name' => '']);
                $update_with_icon_query =  DB::table('tbl_music_taste')
                            ->where('music_taste_id', $request->music_taste_id)
                            ->update(['music_taste_name' => $request->music_taste_name,'music_taste_icon_name' => $img_name,'music_taste_description' => $request->music_taste_description, 'edited_by'=>session('LoggedUser')]);
                                
                if($update_with_icon_query)
                {
                    return redirect('music-tastes')->with('success','Music Taste data updated successfully');
                }
                else
                {
                    return back()->with('fail', 'Something went wrong, please try again1!');
                }
                
            }
        }
        else
        {
            $update_without_icon_query =  DB::table('tbl_music_taste')
                            ->where('music_taste_id', $request->music_taste_id)
                            ->update(['music_taste_name' => $request->music_taste_name,'music_taste_description' => $request->music_taste_description, 'edited_by'=>session('LoggedUser')]);

            if($update_without_icon_query)
            {
                return redirect('music-tastes')->with('success','Music Taste data updated successfully');
            }
            else
            {
                return back()->with('fail', 'Something went wrong, please try again!');
            }
        }
    }

    function enableMusicTaste($id)
    {
        $update_query = DB::table('tbl_music_taste')
                            ->where('music_taste_id', base64_decode($id))
                            ->update(['is_active' => '0', 'edited_by'=>session('LoggedUser')]);

        if($update_query)
        {
            return back()->with('success','Music Taste enabled successfully');
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }

    function disableMusicTaste($id)
    {
        // echo $id;
        // exit;
        $update_query = DB::table('tbl_music_taste')
                            ->where('music_taste_id', base64_decode($id))
                            ->update(['is_active' => '1', 'edited_by'=>session('LoggedUser')]);

        if($update_query)
        {
            return back()->with('success','Music Taste disabled successfully');
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }
}
