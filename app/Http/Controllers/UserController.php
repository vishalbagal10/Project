<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountVerificationEmail;
use App\Mail\ForgotPasswordRequestEmail;
use App\Mail\DeleteAccountConfirmationEmail;
use Yajra\DataTables\DataTables;
use App\Helpers\ErrorMailSender;

class UserController extends Controller
{
    function createAccount($type)
    {
        $data = DB::table('tbl_user_roles')->where('is_active', '=', 0)->get();
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        if(base64_decode($type) == 'admin')
        {
            return view('backend.views.add_admin_user_account', ['data'=>$data],['cvs_year_data'=>$cvs_year]);
        }
        else
        {
            return view('backend.views.add_client_user_account', ['data'=>$data],['cvs_year_data'=>$cvs_year]);
        }      
        
    }

    /* function saveAccount(Request $request)
    {
        $request->validate([
            "fullname"=>'required',
            "email"=>'required|email:rfc,dns|unique:tbl_users',
            "password"=>'required|min:8',
            "expdate"=>'required',
            "role"=>'required|not_in:0'
        ]);
        //return $request->input();

        $query = DB::table('tbl_users')->insert([
            'name' => $request->fullname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'expdate' => $request->expdate,
            'role_id' => $request->role,
            'created_by' => session('LoggedUser'),
        ]);

        $email_data = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if($query)
        {
            Mail::to($request->email)->cc('support@wits.bz')->send(new LoginDetailsEmail($email_data));
            
            $update =  DB::table('tbl_users')
              ->where('email', $request->email)
              ->update(['email_sent' => 1]);

            return back()->with('success','Account created successfully');
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    } */

    function saveAccount(Request $request)
    {
        //return $request->input();  
        if($request->sel_role == 0 || $request->sel_role == 2)
        {
            $request->validate([
                "full_name"=>'required',
                "email"=>'required|email:rfc,dns|unique:tbl_users',
                "expiry_date"=>'required',
                //"role"=>'required|not_in:0'
            ]);

            $id = DB::table('tbl_users')->insertGetId(
                ['name' => $request->full_name,
                'email' => $request->email,
                'expdate' => $request->expiry_date,
                'role_id' => $request->sel_role,
                'created_by' => session('LoggedUser'),]
            );
            //echo $id;
        }
        else
        {
            $request->validate([
                "full_name"=>'required',
                "email"=>'required|email:rfc,dns|unique:tbl_users',
                //"role"=>'required'
            ]);

            $id = DB::table('tbl_users')->insertGetId(
                ['name' => $request->full_name,
                'email' => $request->email,
                'role_id' => $request->sel_role,
                'created_by' => session('LoggedUser'),]
            );
            //echo $id;
        }
        //return $request->input();       
        
        $current_date_time = Carbon::now()->toDateTimeString();
        $token = sha1($request->email.$id.$current_date_time);
        //echo $token;

        $query = DB::table('tbl_email_verification')->insert([
            'user_id' => $id,
            'token' => $token,
        ]);

        $email_data = [
            'name' => $request->full_name,
            'token' => $token
        ];

        if($query)
        {
            $cc_mail_id = config('custom.cc_mail_id');
            $bcc_mail_id = config('custom.bcc_mail_id');
            //Mail::to($request->email)->cc('andrearebecchi@ampcontact.com')->bcc(['chetan.ningoo@gophygital.io','hitesh@gophygital.io'])->send(new AccountVerificationEmail($email_data));
            Mail::to($request->email)->cc($cc_mail_id)->bcc($bcc_mail_id)->send(new AccountVerificationEmail($email_data));
            
            DB::table('tbl_users')
                            ->where('uid', $id)
                            ->update(['email_sent' => '1']);
            if($request->sel_role == 0 || $request->sel_role == 2)
            {
                return redirect('client-users')->with('success','Account created successfully');
            }
            else
            {
                return redirect('admin-users')->with('success','Account created successfully');
            }
        }
        else
        {
            $error_data = "Something went wrong while creating account";
            ErrorMailSender::sendErrorMail($error_data);
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }

    function verifyAccount(Request $request)
    {
        $user_id = DB::table('tbl_email_verification')
                    ->where('token', '=', $request->token)
                    ->where('is_active', '=', '0')
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
            return view('backend.views.set_password',['id'=>$user_id->user_id],['token'=>$request->token],['cvs_year_data'=>$cvs_year]);
        }
        else
        {
            return redirect('/')->with('fail', 'Verification link is expired');
        }
        
    }

    function setPassword(Request $request)
    {
        $request->validate([
            "password"=>'required|min:8',
            "confirm_password" => 'required|min:8|same:password',
        ]);

        //return view('backend.views.reset_password',['data'=>$user_id]);
        $update_query = DB::table('tbl_users')
                            ->where('uid', $request->user_id)
                            ->update(['password' => Hash::make($request->password)]);
        
        if($update_query)
        {
            DB::table('tbl_email_verification')
                            ->where('user_id', $request->user_id)
                            ->where('token', $request->vtoken)
                            ->update(['is_active' => '1']);
            // return back()->with('success','Password updated successfully');
            return redirect('/')->with('success','Account Verified successfully');
        }
        else
        {
            $error_data = "Something went wrong while verifying account and setting password for user-".$request->user_id;
            ErrorMailSender::sendErrorMail($error_data);
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }

    function listClientUsers()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.client_users_list',['cvs_year_data'=>$cvs_year]);
    }

    function getClientUsers(Request $request)
    {
        if ($request->ajax()) {
            /* $data = DB::table('tbl_users')
            ->join('tbl_user_roles', 'tbl_users.role_id', '=', 'tbl_user_roles.id')
            ->where('role_id', '=', '2')
            ->where('is_active', '=', '0')
            ->get(); */
            $data = DB::table('tbl_users')
            ->where('role_id', '=', '2')
            ->where('is_active', '=', '0')
            ->orderBy('uid', 'desc')
            ->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    if($data->status == '0')
                    {
                        // $actionBtn = '<a href="edit-account/'.base64_encode($data->uid).'" title="Click here eidt" class="edit btn btn-success btn-sm" onClick="addLoader()">Edit</a> <a href="disable-user/'.base64_encode($data->uid).'" title="Click here to disable user" class="delete btn btn-success btn-sm" onClick="addLoader()">Disable</a>';
                        $actionBtn = '<a href="edit-account/'.base64_encode($data->uid).'" title="Click here eidt" class="edit btn btn-success btn-sm" onClick="addLoader()">Edit</a> <a href="delete-account/'.base64_encode($data->uid).'" title="Click here to delete user" class="delete btn btn-success btn-sm" onClick="addLoader()">Delete Account</a>';
                    }
                    else
                    {
                        //$actionBtn = '<a href="edit-account/'.base64_encode($data->uid).'" title="Click here eidt" class="edit btn btn-success btn-sm" onClick="addLoader()">Edit</a> <a href="enable-user/'.base64_encode($data->uid).'" title="Click here to enable user" class="edit btn btn-success btn-sm" onClick="addLoader()">Enable</a>';
                        $actionBtn = '<a href="enable-user/'.base64_encode($data->uid).'" title="Click here to enable user" class="edit btn btn-success btn-sm" onClick="addLoader()">Enable</a>';
                    }
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->setRowId(function($data){
                    return $data->uid;
                })
                ->make(true);
        }
    }

    function listAdminUsers()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.admin_users_list',['cvs_year_data'=>$cvs_year]);
    }

    function getAdminUsers(Request $request)
    {
        if ($request->ajax()) {
            /* $data = DB::table('tbl_users')
            ->join('tbl_user_roles', 'tbl_users.role_id', '=', 'tbl_user_roles.id')
            ->where('role_id', '=', '2')
            ->where('is_active', '=', '0')
            ->get(); */
            $data = DB::table('tbl_users')
            ->where('role_id', '=', '1')
            ->where('is_active', '=', '0')
            ->where('uid', '!=', session('LoggedUser'))
            ->orderBy('uid', 'desc')
            ->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    if($data->status == '0')
                    {
                        $actionBtn = '<a href="edit-account/'.base64_encode($data->uid).'" title="Click here eidt" class="edit btn btn-success btn-sm" onClick="addLoader()">Edit</a> <a href="disable-user/'.base64_encode($data->uid).'" title="Click here to disable user" class="delete btn btn-success btn-sm" onClick="addLoader()">Disable</a>';
                    }
                    else
                    {
                        //$actionBtn = '<a href="edit-account/'.base64_encode($data->uid).'" title="Click here eidt" class="edit btn btn-success btn-sm" onClick="addLoader()">Edit</a> <a href="enable-user/'.base64_encode($data->uid).'" title="Click here to enable user" class="edit btn btn-success btn-sm" onClick="addLoader()">Enable</a>';
                        $actionBtn = '<a href="enable-user/'.base64_encode($data->uid).'" title="Click here to enable user" class="edit btn btn-success btn-sm" onClick="addLoader()">Enable</a>';
                    }
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->setRowId(function($data){
                    return $data->uid;
                })
                ->make(true);
        }
    }

    function enableUser($id)
    {
        $update_user = DB::table('tbl_users')
                            ->where('uid', base64_decode($id))
                            ->update(['status' => '0', 'edited_by'=>session('LoggedUser')]);

        if($update_user)
        {
            return back()->with('success','Account enabled successfully');
        }
        else
        {
            $error_data = "Something went wrong while enabling user-".base64_decode($id);
            ErrorMailSender::sendErrorMail($error_data);
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }

    function disableUser($id)
    {
        // echo $id;
        // exit;
        $update_user = DB::table('tbl_users')
                            ->where('uid', base64_decode($id))
                            ->update(['status' => '1', 'edited_by'=>session('LoggedUser')]);

        if($update_user)
        {
            return back()->with('success','Account disabled successfully');
        }
        else
        {
            $error_data = "Something went wrong while disabling user-".base64_decode($id);
            ErrorMailSender::sendErrorMail($error_data);
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }

    function deleteClientUserAccount($id)
    {
        $get_user_data = DB::table('tbl_users')->where('uid', '=', base64_decode($id))->first();
        $email_data = [
            'name' => $get_user_data->name,
        ];

        $current_date_time = Carbon::now();
        $delete_query = DB::table('tbl_users')
                        ->where('uid', '=', base64_decode($id))
                        ->update(['name' => 'DeletedUser#'.$current_date_time, 'email' => 'DeletedUser#'.$current_date_time, 'password' => 'DeletedUser#'.$current_date_time, 'is_active' => '1', 'edited_by'=>session('LoggedUser')]);
        if($delete_query)
        {
            $cc_mail_id = config('custom.cc_mail_id');
            $bcc_mail_id = config('custom.bcc_mail_id');
            //Mail::to($get_user_data->email)->send(new DeleteAccountConfirmationEmail($email_data));
            Mail::to($get_user_data->email)->cc($cc_mail_id)->bcc($bcc_mail_id)->send(new DeleteAccountConfirmationEmail($email_data));
            return back()->with('success', 'Account deleted sucessfully');
        }
        else
        {
            $error_data = "Something went wrong while deleting client user account-".base64_decode($id);
            ErrorMailSender::sendErrorMail($error_data);
            return back()->with('fail', 'Something went wrong while deleting client user account , please try again!');
        } 
    }

    function editAccount($id)
    {
        $data = DB::table('tbl_users')->where('uid', '=', base64_decode($id))->first();
        $user_role_data = DB::table('tbl_user_roles')->where('is_active', '=', 0)->get();
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.edit_account',['UserData'=>$data, 'user_role_data'=>$user_role_data,'cvs_year_data'=>$cvs_year]);
    }

    function updateAccount(Request $request)
    {
        if($request->urole == 2)
        {
            $request->validate([
                "full_name"=>'required',
                "email"=>'required|email:rfc,dns|unique:tbl_users,email,'.$request->u_id.',uid',
                "expiry_date"=>'required'
            ]);
            //return $request->input();
            
            $update_client_account =  DB::table('tbl_users')
                            ->where('uid', $request->u_id)
                            ->update(['name' => $request->full_name,'email' => $request->email, 'expdate' => $request->expiry_date, 'edited_by'=>session('LoggedUser')]);
            
            if($update_client_account)
            {
                return redirect('client-users')->with('success','Account updated successfully');
            }
            else
            {
                $error_data = "Something went wrong while updating user account-".$request->u_id;
                ErrorMailSender::sendErrorMail($error_data);
                return back()->with('fail', 'Something went wrong, please try again!');
            }
        }
        else
        {
            $request->validate([
                "full_name"=>'required',
                "email"=>'required|email:rfc,dns|unique:tbl_users,email,'.$request->u_id.',uid',
            ]);
            //return $request->input();
            
            $update_admin_account =  DB::table('tbl_users')
                            ->where('uid', $request->u_id)
                            ->update(['name' => $request->full_name,'email' => $request->email, 'edited_by'=>session('LoggedUser')]);
            
            if($update_admin_account)
            {
                return redirect('admin-users')->with('success','Account updated successfully');
            }
            else
            {
                $error_data = "Something went wrong while updating user account-".$request->u_id;
                ErrorMailSender::sendErrorMail($error_data);
                return back()->with('fail', 'Something went wrong, please try again!');
            }
        }        
    }

    function forgotPasswordRequest()
    {
        $cvs_year = DB::table('tbl_cvs')
                    ->select('cv_year')
                    ->where('status', 1)
                    ->where('is_active', 0)
                    ->distinct()
                    ->orderBy('cv_year', 'desc')
                    ->get();
        return view('backend.views.forgot_password_request',['cvs_year_data'=>$cvs_year]);
    }

    function forgotPassword(Request $request)
    {
        $request->validate([
            "email"=>'required|email:rfc,dns',
        ]);
        //return $request->input();

        if (DB::table('tbl_users')->where('email', $request->email)->exists())
        {
            $data = DB::table('tbl_users')->where('email', $request->email)->first();

            $current_date_time = Carbon::now()->toDateTimeString();
            $token = sha1($data->email.$data->uid.$current_date_time);

            $email_data = [
                'name' => $data->name,
                'email' => $request->email,
                'token' => $token
            ];

            $cc_mail_id = config('custom.cc_mail_id');
            $bcc_mail_id = config('custom.bcc_mail_id');
            //Mail::to($request->email)->send(new ForgotPasswordRequestEmail($email_data));
            Mail::to($request->email)->cc($cc_mail_id)->bcc($bcc_mail_id)->send(new ForgotPasswordRequestEmail($email_data));

            $query = DB::table('tbl_password_resets')->insert([
                'user_id' => $data->uid,
                'token' => $token,
            ]);
            if($query)
            {
                return back()->with('success','Password reset link email is sent');
            }
            else
            {
                $error_data = "Something went wrong while inserting forget password token";
                ErrorMailSender::sendErrorMail($error_data);
                return back()->with('fail', 'Something went wrong, please try again!');
            } 
        }
        else
        {
            return back()->with('fail', 'Email not found in our system');
        }
    }

    function resetPasswordForm(Request $request)
    {
        $user_id = DB::table('tbl_password_resets')
                    ->where('token', '=', $request->token)
                    ->where('is_active', '=', '0')
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
            return view('backend.views.reset_password',['id'=>$user_id->user_id],['token'=>$request->token],['cvs_year_data'=>$cvs_year]);
        }
        else
        {
            return redirect('/')->with('fail', 'Reset Password link is expired');
        }
        
    }

    function resetPassword(Request $request)
    {
        $request->validate([
            "password"=>'required|min:8',
            "confirm_password" => 'required|min:8|same:password',
        ]);

        //return view('backend.views.reset_password',['data'=>$user_id]);
        $update_query = DB::table('tbl_users')
                            ->where('uid', $request->user_id)
                            ->update(['password' => Hash::make($request->password)]);
        
        if($update_query)
        {
            DB::table('tbl_password_resets')
                            ->where('user_id', $request->user_id)
                            ->where('token', $request->vtoken)
                            ->update(['is_active' => '1']);
            // return back()->with('success','Password updated successfully');
            return redirect('/')->with('success','Password updated successfully');
        }
        else
        {
            $error_data = "Something went wrong while restting password";
            ErrorMailSender::sendErrorMail($error_data);
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }
}
