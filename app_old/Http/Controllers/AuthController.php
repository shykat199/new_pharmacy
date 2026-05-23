<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminLogInRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function loginForm()
    {
        $auth_user = Auth::user();

        if ($auth_user){
            return  redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password'=>'required',
        ]);

        $login = $request->post('email');
        $password = $request->post('password');

        $user = User::where('email', $login)
            ->orWhere('phone', $login)
            ->first();

        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';


        if ($user && Auth::attempt([$fieldType => $login, 'password' => $password])) {

            toast('Successfully Login', 'success');

            if ($user->role == USER_ROLE) {
                return redirect()->route('user.dashboard');
            } elseif ($user->role == STAFF_ROLE) {
                return redirect()->route('admin.create-invoice');
            } else {
                return redirect()->route('admin.dashboard');
            }
        }
        toast('Wrong credentials','error');
        return redirect()->back();
    }

    public function logout()
    {
        Auth::logout();
        toast('Successfully Logout','success');
        return redirect()->route('auth.login-form');
    }

    public function profile()
    {
        return view('profile');
    }

    public function updateProfile(Request $request)
    {
        Auth::user()->update([
           'name'=>$request->post('name'),
           'phone'=>$request->post('phone'),
           'email'=>$request->post('email'),
           'address'=>$request->post('address'),
        ]);
        toast('Profile Updated successfully','success');
        return redirect()->back()->with('success','Profile Updated successfully');
    }

    public function changePassword(Request $request)
    {
        $validator = $request->validate([
            'old_pass' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.confirmed' => 'New password and confirm password do not match.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->old_pass, $user->password)) {
            return back()->with('error', 'Old password is incorrect.');
        }

        $user->password = Hash::make($request->password);
        $user->save();

        toast('Password updated successfully.','success');

        return redirect()->back();
    }

    public function changeAccessPassword(Request $request)
    {
        $request->validate([
            'access_old_pass'            => 'required|string',
            'access_password'            => 'required|string|min:8|confirmed',
        ], [
            'access_password.confirmed'  => 'New password and confirm password do not match.',
        ]);

        $user = User::with('userAccessPassword')
            ->where('role', ADMIN_ROLE)
            ->firstOrFail();


        if (!Hash::check($request->access_old_pass, $user->userAccessPassword->password)) {
            return back()->with('error', 'Old password is incorrect.');
        }

        $user->userAccessPassword->update([
            'password' => Hash::make($request->access_password),
        ]);

        toast('Password updated successfully.', 'success');
        return back();
    }

}
