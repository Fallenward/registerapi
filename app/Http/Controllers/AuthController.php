<?php

// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Mail\OtpMail;

class AuthController extends Controller
{

    public function generateOtp($length = 6) {
        $otp = '';
        for ($i = 0; $i < $length; $i++) {
            $otp .= rand(0, 9);
        }
        return $otp;
    }

    public function register(Request $request)
    {
        $validated = $request->validate([

            'phone_number' => 'required|unique:users',
            'password' => 'required|min:8',
        ]);

        $otp = $this->generateOtp();


        $user = User::create([
            
            'phone_number' => $request->phone_number,
            'password' => $request->password,
            'otp' => $otp,

        ]);


        return response()->json([
            'message' => 'OTP sent successfully, please verify it.',
        ]);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => 'required',
            'otp' => 'required|numeric',
        ]);

        $user = User::where('phone_number', $request->phone_number)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        if ($user->otp == $request->otp) {
            $user->update(['otp' => null]);



            return response()->json([
                'message' => 'Login successful.',
            ]);
        }

        return response()->json([
            'message' => 'Invalid or expired OTP.',
        ], 400);
    }

}
