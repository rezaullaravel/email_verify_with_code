<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;


class LoginRegisterController extends Controller
{
    //login-register page
    public function index(){
        return view('login-register');

    }

    //post register
    public function postRegister(Request $request){
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|same:confirm-password',
            'confirm-password' => 'required',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);


        // Generate verification code and set expiration time
        $verification_code = random_int(100000, 999999);
        $expiration_time = now()->addMinutes(2);  // Code expires in 10 minutes
        $user->email_verification_code = $verification_code;
        $user->email_verification_expires_at = $expiration_time;
        $user->save();

        // Send verification email
        $messageData = [
            'name' => $user->name,
            'verification_code' => $verification_code,
            'expires_at' => $expiration_time->format('H:i A, d M Y'), // Format expiration time
        ];

        Mail::send('emails.user_confirmation', $messageData, function ($message) use ($user) {
            $message->to($user->email)->subject('Verify Your Account');
        });

        return redirect()->route('verify.email',$user->email)->with('message', 'A verification code has been sent to your email. Please verify your account.');
    }//end method


    public function showVerificationForm($email) {
        $user = User::where('email', $email)->firstOrFail();
        return view('verify', [
            'email' => $user->email,
            'expires_at' => $user->email_verification_expires_at->toIso8601String(), // Pass expiration time to view
        ]);
    }//end method




    public function resendVerificationCode($email) {
        $user = User::where('email', $email)->firstOrFail();

        // Generate new verification code and update expiration time
        $newCode = random_int(100000, 999999);
        $user->email_verification_code = $newCode;
        $user->email_verification_expires_at = now()->addMinutes(2);  // Reset expiration time to 10 minutes
        $user->save();

        // Send new verification code via email
        Mail::send('emails.user_confirmation', [
            'name' => $user->name,
            'verification_code' => $newCode,
            'expires_at' => $user->email_verification_expires_at->toIso8601String(),
        ], function ($message) use ($user) {
            $message->to($user->email)->subject('New Verification Code');
        });

        return redirect()->route('verify.email', ['email' => $email])->with('message', 'A new verification code has been sent.');
    }//end method



    public function verifyEmail(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required|numeric',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        // Check if code matches and if it's not expired
        if ($user->email_verification_code == $request->verification_code && now()->lessThanOrEqualTo($user->email_verification_expires_at)) {
            // Verification successful, update status
            $user->verify_status = 1;
            $user->save();

            return redirect('/login-register')->with('message', 'Your account has been verified. You can now log in.');
        } else {
            return redirect()->back()->with('message', 'Invalid or expired verification code.');
        }
    }



}//end class
