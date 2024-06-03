<?php

namespace App\Http\Controllers;

//use Illuminate\Contracts\Validation\Validator as ValidationValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
//use App\Models\Login;
use Carbon\Carbon;
use App\Helpers\UserSystemInfoHelper;
//use Illuminate\Support\Facades\Mail;
//use App\Mail\LoginDetailsEmail;
// use App\Mail\DeleteAccountRequestEmail;
//use App\Mail\ForgotPasswordRequestEmail;
//use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Artisan;

class LoginController extends Controller
{
    public function lgoinForm()
    {
        Artisan::call('config:clear');
        //echo Artisan::output();
        Artisan::call('cache:clear');
        //echo Artisan::output();
        return view('backend.views.login_form');
    }

    public function authenticateLgoin(Request $request)
    {
        $request->validate([
            //"email"=>['required','email:rfc,dns'],
            'email' => ['required','regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix'],
            "password"=>'required',
            'terms-of-use'=>'accepted'
        ]);

        $user = DB::table('tbl_users')
                ->where('email', '=', $request->email)
                ->where('status', '=', '0')
                ->where('password', '!=', NULL)
                ->first();

        if($user)
        {
            if(Hash::check($request->password, $user->password))
            {
                if($user->role_id == 2)
                {
                    $curr_date = Carbon::today();
                    if($curr_date->lt($user->expdate))
                    {
                        //return $request->input();
                        $request->session()->put('LoggedUser', $user->uid);
                        $request->session()->put('LoggedUserType', $user->role_id);
                        $tbl_config_data = DB::table('tbl_config')->where("type","=","request_timeout_time")->first();
                        $request->session()->put('RequestTimeOutTime', $tbl_config_data->value);
                        $user_ip = UserSystemInfoHelper::get_ip();
                        $user_browser = UserSystemInfoHelper::get_browsers();
                        $user_device = UserSystemInfoHelper::get_device();
                        $user_os = UserSystemInfoHelper::get_os();

                        $query = DB::table('tbl_users_login_log')->insert([
                            'user_id' => $user->uid,
                            'ip' => $user_ip,
                            'browser' => $user_browser,
                            'os' => $user_os,
                            'device' => $user_device,
                        ]);

                        if(session('LoggedUserType') == 1)
                        {
                            //return redirect('dashboard');
                            return redirect('brand-cvs');
                        }
                        else
                        {
                            $request->session()->put('device_type', $user_device);
                            return redirect('welcome');
                        }
                    }
                    else
                    {
                        return back()->with('fail', 'Account is expired');
                    }
                }
                else
                {
                    $request->session()->put('LoggedUser', $user->uid);
                    $request->session()->put('LoggedUserType', $user->role_id);
                    $user_ip = UserSystemInfoHelper::get_ip();
                    $user_browser = UserSystemInfoHelper::get_browsers();
                    $user_device = UserSystemInfoHelper::get_device();
                    $user_os = UserSystemInfoHelper::get_os();

                    $query = DB::table('tbl_users_login_log')->insert([
                        'user_id' => $user->uid,
                        'ip' => $user_ip,
                        'browser' => $user_browser,
                        'os' => $user_os,
                        'device' => $user_device,
                    ]);

                    if(session('LoggedUserType') == 1)
                    {
                        //return redirect('dashboard');
                        return redirect('brand-cvs');
                    }
                    else
                    {
                        $request->session()->put('device_type', $user_device);
                        return redirect('welcome');
                    }
                }
            }
            else
            {
                return back()->with('fail', 'Invalid Email or Password');
            }
        }
        else
        {
            //return back()->with('fail', 'Account not found with this email id');
            return back()->with('fail', 'Invalid Email or Password');
        }
    }

    public function dashboard()
    {
        //echo session('LoggedUserType'); exit;
        $data = DB::table('tbl_users')->where('uid', '=', session('LoggedUser'))->first();
        //print_r($data);
        //exit;
        $cv_data = DB::table('tbl_cvs')->where('is_active', '=', 0)->get();
        $industry_data = DB::table('tbl_industry')->where('is_active', '=', 0)->get();
        $music_taste_data = DB::table('tbl_music_taste')->where('is_active', '=', 0)->get();
        $experience_data = DB::table('tbl_experience')->where('is_active', '=', 0)->get();
        $qualitative_data = DB::table('tbl_qualitative')->where('is_active', '=', 0)->get();
        return view('backend.views.dashboard',['LoggedUserInfo'=>$data], ['cv_data'=>$cv_data, 'industry_data'=>$industry_data, 'music_taste_data'=>$music_taste_data, 'experience_data'=>$experience_data, 'qualitative_data'=>$qualitative_data]);
    }

    public function logout()
    {
        if(session()->has('LoggedUser'))
        {
            session()->pull('LoggedUser');
            session()->pull('LoggedUserType');
            return redirect('/');
            //return route('login');
        }
    }

    /* function createAccount()
    {
        $data = DB::table('tbl_user_roles')->get();
        return view('backend.views.create_account', ['data'=>$data]);
    }

    function saveAccount(Request $request)
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
            Mail::to($request->email)->cc('support@soniccv.com')->send(new LoginDetailsEmail($email_data));

            $update =  DB::table('tbl_users')
              ->where('email', $request->email)
              ->update(['email_sent' => 1]);

            return back()->with('success','Account created successfully');
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }

    function listUsers()
    {
        return view('backend.views.user_list');
    }

    function getUsers(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('tbl_users')
            ->join('tbl_user_roles', 'tbl_users.role_id', '=', 'tbl_user_roles.id')
            ->where('role_id', '=', '2')
            ->where('is_active', '=', '0')
            ->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    if($data->status == '0')
                    {
                        $actionBtn = '<a href="javascript:void(0)" class="edit btn btn-success btn-sm">Edit</a> <a href="disable-user/'.$data->uid.'" class="delete btn btn-danger btn-sm">Disable</a>';
                    }
                    else
                    {
                        $actionBtn = '<a href="javascript:void(0)" class="edit btn btn-success btn-sm">Edit</a> <a href="enable-user/'.$data->uid.'" class="edit btn btn-success btn-sm">Enable</a>';
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
                            ->where('uid', $id)
                            ->update(['status' => '0', 'edited_by'=>session('LoggedUser')]);

        if($update_user)
        {
            return back()->with('success','Account enabled successfully');
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    }

    function disableUser($id)
    {
        // echo $id;
        // exit;
        $update_user = DB::table('tbl_users')
                            ->where('uid', $id)
                            ->update(['status' => '1', 'edited_by'=>session('LoggedUser')]);

        if($update_user)
        {
            return back()->with('success','Account disabled successfully');
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    } */

    /* function userProfile()
    {
        $data = DB::table('tbl_users')->where('uid', '=', session('LoggedUser'))->first();
        $user_role_data = DB::table('tbl_user_roles')->get();
        return view('backend.views.profile',['LoggedUserInfo'=>$data], ['user_role_data'=>$user_role_data]);
    }

    function saveProfile(Request $request)
    {
        $request->validate([
            "fullname"=>'required',
            //"email"=>"required|email:rfc,dns|unique:tbl_users",
            //"email"=>'required|email:rfc,dns|unique:tbl_users,email,'.session('LoggedUser'),
            "password"=>'required|min:8',
            "expdate"=>'required',
            //"role"=>'required'
        ]);
        //return $request->input();
        $chk_password = DB::table('tbl_users')
                            ->select('password')
                            ->where('uid', session('LoggedUser'))
                            ->first();
        //echo $chk_password->password;
        if($request->password == $chk_password)
        {
            $update_profile =  DB::table('tbl_users')
                           ->where('uid', session('LoggedUser'))
                           ->update(['name' => $request->fullname,'email' => $request->email, 'edited_by'=>session('LoggedUser')]);
        }
        else
        {
            $update_profile =  DB::table('tbl_users')
                           ->where('uid', session('LoggedUser'))
                           ->update(['name' => $request->fullname,'email' => $request->email,'password'=>Hash::make($request->password), 'edited_by'=>session('LoggedUser')]);
        }
        if($update_profile)
        {
            return back()->with('success','Account updated successfully');
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
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

        if (DB::table('tbl_delete_account_request')->where('id', $id)->where('token', '=',  $token)->doesntExist())
        {
            Mail::to($data->email)->send(new DeleteAccountRequestEmail($email_data));

            $query = DB::table('tbl_delete_account_request')->insert([
                'user_id' => $id,
                'token' => $token,
                'request_by' => session('LoggedUser'),
            ]);
            if($query)
            {
                // return redirect('backend.views.profile')->with('success','Email with account delete request confirmation is sent');
                return back()->with('success','Email with account delete request confirmation is sent');
            }
            else
            {
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
        //echo $request->token;
        $token = $request->token;
        return view('backend.views.delete_account',['token' => $token]);
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
                                ->update(['name' => 'DeletedUser#'.$current_date_time, 'email' => 'DeletedUser#'.$current_date_time, 'is_active' => '1', 'edited_by'=>$user_id->user_id]);
                if($delete_query)
                {
                    return redirect('/')->with('success', 'Account deleted sucessfully');
                }
                else
                {
                    return redirect('/')->with('fail', 'Something went wrong while deleting your account , please try again!');
                }
            }
            else
            {
                return redirect('/')->with('fail', 'Something went wrong while deleting your account , please try again!');
            }
        }
        else
        {
            return redirect('/')->with('fail', 'Something went wrong while deleting your account , please try again!');
        }
    }

    function deleteAccountCancel(Request $request)
    {
        $update_response = DB::table('tbl_delete_account_request')
                                ->where('token', '=', $request->token)
                                ->update(['response' => $request->res]);

        return redirect('/');
    } */

    /* function forgotPasswordRequest()
    {
        return view('backend.views.forgot_password_request');
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

            Mail::to($request->email)->send(new ForgotPasswordRequestEmail($email_data));

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
                    ->first();

        return view('backend.views.reset_password',['id'=>$user_id->user_id]);
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
            // return back()->with('success','Password updated successfully');
            return redirect('/')->with('success','Password updated successfully');
        }
        else
        {
            return back()->with('fail', 'Something went wrong, please try again!');
        }
    } */
}
