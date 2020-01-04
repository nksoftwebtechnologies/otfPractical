<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $user = User::create([
                                'first_name' => $data['first_name'],
                                'last_name' => $data['last_name'],
                                'email' => $data['email'],
                                'token' => str_random(40),
                                'verified' => 0,
                                'password' => bcrypt($data['password']),
                                'phone' => $data['phone'],
                            ]);

        $fromName = $user['first_name'].' '.$user['last_name'];
        $fromEmail = $user['email'];
        $replyTo = 'no-reply@otfcoders.com';
        $subject = "Account Activation - OTFCoder";

        $email_content = '<h2>Welcome to the site '.$user['first_name'].' '.$user['last_name'].'</h2><br/>';
		$email_content .= 'Your registered email-id is '.$user['email'].', Please click on the below link to verify your email account<br/>';
		$email_content .= '<a href="'.url('user/verify', $user['token']).'">Verify Email</a>';
        
        $usr = [
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'email' => $user['email'],
                    'phone' => $user['phone'],
                    'id' => $user['id']
                ];

        /*
        Mail::send(['html'=>'emails.verifyUser'],['title' => $subject,'email_content' => $email_content],function($message)use($fromName, $fromEmail,$replyTo,$subject){
            $message->to($fromEmail)
                    ->subject($subject)
                    ->from($fromEmail, $fromName)
                    ->replyTo($replyTo);
        });
        */

        return $user;
    }

    public function verifyUser($token)
    {
        $user = User::where('token', $token)->first();
        if(isset($user) ){
            if(!$user->verified) {
                $user->verified = 1;
                $user->save();
                $status = "Your e-mail is verified. You can now login.";
            }else{
                $status = "Your e-mail is already verified. You can now login.";
            }
        }else{
            return redirect('/login')->with('warning', "Sorry your email cannot be identified.");
        }

        return redirect('/login')->with('status', $status);
    }
}