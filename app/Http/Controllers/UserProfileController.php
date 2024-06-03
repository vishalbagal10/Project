<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\DeleteAccountRequestEmail;
use App\Helpers\ErrorMailSender;

class UserProfileController extends Controller
{
    function userProfile()
    {
        $data = DB::table('tbl_users')->where('uid', '=', session('LoggedUser'))->first();
        $user_role_data = DB::table('tbl_user_roles')->where('is_active', '=', 0)->get();
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.profile',['LoggedUserInfo'=>$data,'user_role_data'=>$user_role_data,'cvs_year_data'=>$cvs_year]);
    }

    function saveProfile(Request $request)
    {
        if($request->urole == 2)
        {
            $request->validate([
                "fullname"=>'required',
                //"email"=>"required|email:rfc,dns|unique:tbl_users",
                //"email"=>'required|email:rfc,dns|unique:tbl_users,email,'.session('LoggedUser'),
                "password"=>'required|min:8',
                "expdate"=>'required',
                //"role"=>'required'
            ]);
        }
        else
        {
            $request->validate([
                "fullname"=>'required',
                "password"=>'required|min:8',
            ]);
        }
        //return $request->input();
        $chk_password = DB::table('tbl_users')
                            ->select('password')
                            ->where('uid', session('LoggedUser'))
                            ->first();
        //echo $chk_password->password."<br>";
        //echo $request->password."<br>";
        if($request->password == $chk_password->password)
        {
            $update_profile =  DB::table('tbl_users')
                           ->where('uid', session('LoggedUser'))
                           ->update(['name' => $request->fullname,'email' => $request->email, 'edited_by'=>session('LoggedUser')]);
            
            if($update_profile)
            {
                return back()->with('success','Account updated successfully');
            }
            else
            {
                $error_data = "Something went wrong while updating profile of user-".session('LoggedUser');
                ErrorMailSender::sendErrorMail($error_data);
                return back()->with('fail', 'Something went wrong, please try again!');
            }
        }
        else
        {
            $update_profile_with_password =  DB::table('tbl_users')
                           ->where('uid', session('LoggedUser'))
                           ->update(['name' => $request->fullname,'email' => $request->email,'password'=>Hash::make($request->password), 'edited_by'=>session('LoggedUser')]);
                           
            if($update_profile_with_password)
            {
                session()->pull('LoggedUser');
                session()->pull('LoggedUserType');
                return redirect('/')->with('success','Account updated successfully');
            }
            else
            {
                $error_data = "Something went wrong while updating profile of user-".session('LoggedUser');
                ErrorMailSender::sendErrorMail($error_data);
                return back()->with('fail', 'Something went wrong, please try again!');
            }
        }        
    }

    function deleteAccountRequest($id)
    {
        $data = DB::table('tbl_users')
                            ->where('uid', $id)
                            ->first();
        
        $current_date_time = Carbon::now()->toDateTimeString();
        $token = sha1($data->email.$data->uid.$current_date_time);
        //echo $token;

        $email_data = [
            'name' => $data->name,
            'token' => $token
        ];

        if (DB::table('tbl_delete_account_request')->where('user_id', $id)->where('token', '=',  $token)->doesntExist())
        {
            $cc_mail_id = config('custom.cc_mail_id');
            $bcc_mail_id = config('custom.bcc_mail_id');
            //Mail::to($data->email)->send(new DeleteAccountRequestEmail($email_data));
            Mail::to($data->email)->cc($cc_mail_id)->bcc($bcc_mail_id)->send(new DeleteAccountRequestEmail($email_data));
            $query = DB::table('tbl_delete_account_request')->insert([
                'user_id' => $id,
                'token' => $token,
                'request_by' => session('LoggedUser'),
            ]);
            if($query)
            {
                // return redirect('backend.views.profile')->with('success','Email with account delete request confirmation is sent');
                //return back()->with('success','Email with account delete request confirmation is sent');
                session()->pull('LoggedUser');
                session()->pull('LoggedUserType');
                return redirect('/')->with('success','Email with account delete request confirmation is sent');
            }
            else
            {
                $error_data = "Something went wrong while token generation";
                ErrorMailSender::sendErrorMail($error_data);
                return back()->with('fail', 'Something went wrong, please try again!');
            }           
            
        }
        else
        {
            return back()->with('fail', 'Something went wrong with token generation, please try again!');
        }
    }

    function verifyDeleteAccount(Request $request)
    {
        session()->pull('LoggedUser');
        session()->pull('LoggedUserType');
        //echo $request->token;
        $token = $request->token;
        $user_id = DB::table('tbl_delete_account_request')
                    ->where('token', '=', $request->token)
                    ->where('response', '=', null)
                    ->first();

        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        
        if($user_id)
        {
            return view('backend.views.delete_account',['token' => $token,'cvs_year_data'=>$cvs_year]);
        }
        else
        {
            return redirect('/')->with('fail', 'Account Delete link is expired');
        }
    }

    function deleteAccount(Request $request)
    {
        $user_id = DB::table('tbl_delete_account_request')
                    ->where('token', '=', $request->token)
                    ->first();
        
        if($user_id)
        {
            $update_response = DB::table('tbl_delete_account_request')
                                ->where('token', '=', $request->token)
                                ->update(['response' => $request->response]);
            if($update_response)
            {
                $current_date_time = Carbon::now();
                $delete_query = DB::table('tbl_users')
                                ->where('uid', '=', $user_id->user_id)
                                ->update(['name' => 'DeletedUser#'.$current_date_time, 'email' => 'DeletedUser#'.$current_date_time, 'password' => 'DeletedUser#'.$current_date_time, 'is_active' => '1', 'edited_by'=>$user_id->user_id]);
                if($delete_query)
                {
                    return redirect('/')->with('success', 'Account deleted sucessfully');
                }
                else
                {
                    $error_data = "Something went wrong while deleting user account 1-".$user_id->user_id;
                    ErrorMailSender::sendErrorMail($error_data);
                    return redirect('/')->with('fail', 'Something went wrong while deleting your account , please try again!');
                }
            }
            else
            {
                $error_data = "Something went wrong while deleting user account 2-".$user_id->user_id;
                ErrorMailSender::sendErrorMail($error_data);
                return redirect('/')->with('fail', 'Something went wrong while deleting your account , please try again!');
            }
        }
        else
        {
            $error_data = "Something went wrong while deleting user account";
            ErrorMailSender::sendErrorMail($error_data);
            return redirect('/')->with('fail', 'Something went wrong while deleting your account , please try again!');
        }
    }

    function deleteAccountCancel(Request $request)
    {
        $update_response = DB::table('tbl_delete_account_request')
                                ->where('token', '=', $request->token)
                                ->update(['response' => $request->res]);
        
        return redirect('/');
    }
}
