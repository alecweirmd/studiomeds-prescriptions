<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Actions\WordpressLoginAction;

class LoginController extends Controller {
    /*
      |--------------------------------------------------------------------------
      | Login Controller
      |--------------------------------------------------------------------------
      |
      | This controller handles authenticating users for the application and
      | redirecting them to your home screen. The controller uses a trait
      | to conveniently provide its functionality to your applications.
      |
     */

use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function login(Request $request) {
        
        $user = User::where('email', $request->login)->first();


        if (Auth::attempt(['email' => $request->login, 'password' => $request->password])) {

            // Set Auth Details 
            Auth::login($user);

            $request->session()->put('user_type', $user->user_type);

            $user->save();

            return redirect()->intended('/dashboard');
        } else {
            Session::flash('warning', true);
            Session::flash('message', 'Login failed. <br> Please try again');

            return $this->sendFailedLoginResponse($request);
        }
    }

    public function logout(Request $request) {
        Auth::logout();
        return redirect('/login');
    }
}
