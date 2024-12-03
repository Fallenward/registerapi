<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\User;
use Carbon\Carbon;
use Ghasedak\DataTransferObjects\Request\InputDTO;
use Ghasedak\DataTransferObjects\Request\ReceptorDTO;
use Ghasedaksms\GhasedaksmsLaravel\Message\GhasedaksmsVerifyLookUp;
use Ghasedaksms\GhasedaksmsLaravel\Notification\GhasedaksmsBaseNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth; // for JWT token

class AuthController extends Controller
{
    public function generateOtp($length = 6) {
        $otp = '';
        for ($i = 0; $i < $length; $i++) {
            $otp .= rand(0, 9);
        }
        return $otp;
    }

    public function checkuser(Request $request, $phone_number)
    {
        $user = User::where('phone_number', $phone_number)->first();

        if (!$user) {
            $newuser = new User();
            $newuser->phone=$request->phone;
            $validationCode= rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) ;
            $newuser->otp= $validationCode;
            $newuser->save();
            try {
                $response = Http::withHeaders([
                    'ApiKey' => env("GHASEDAKAPI_KEY"),
                ])->post('https://gateway.ghasedak.me/rest/api/v1/WebService/SendOtpSMS', [
                    'sendDate' => now()->toIso8601String(),  // Example send date in ISO 8601 format
                    'receptors' => [
                        [
                            'mobile' => $request->phone,           // Mobile number from the request
                            'clientReferenceId' => (string) rand(1, 4), // Random client reference ID as a string
                        ],
                    ],
                    'templateName' => 'chartix',                    // Template name
                    'inputs' => [
                        [
                            'param' => 'code',                 // Parameter name
                            'value' => $validationCode,                // Parameter value
                        ],
                    ],
                    'udh' => true,                               // UDH flag
                ]);
            } catch (\Throwable $th) {
            }
            return response()->json(["data" => ["success" => false]], 200);
        }else {
            if ($user->otp == "ok") {
                return response()->json(["data" => ["success" => true]], 200);
            } else {
                $validationCode = rand(1, 9) . rand(1, 9) . rand(1, 9) . rand(1, 9);
                $user->otp = $validationCode;
                $user->save();
                try {
                    $response = Http::withHeaders([
                        'ApiKey' => env("GHASEDAKAPI_KEY"),
                    ])->post('https://gateway.ghasedak.me/rest/api/v1/WebService/SendOtpSMS', [
                        'sendDate' => now()->toIso8601String(),  // Example send date in ISO 8601 format
                        'receptors' => [
                            [
                                'mobile' => $request->phone,           // Mobile number from the request
                                'clientReferenceId' => (string) rand(1, 4), // Random client reference ID as a string
                            ],
                        ],
                        'templateName' => 'chartix',                    // Template name
                        'inputs' => [
                            [
                                'param' => 'code',                 // Parameter name
                                'value' => $validationCode,                // Parameter value
                            ],
                        ],
                        'udh' => true,                               // UDH flag
                    ]);
                    return response()->json(["data" => ["success" => true]], 200);
                } catch (\Throwable $th) {
                }
            }
        }
    }

    public function checkOtp(Request $request,)
    {
        $user = User::where('phone_number',$request->phone)->first();
        if ($user != null) {
            if ($user->otp == $request->otp) {
                $user->otp = "ok";
                $user->jwt = Str::random(40);
                $user->save();
                return response()->json(["data" => ["success" => true, "jwt" => $user->jwt]], 200);
            } else {
                return response()->json(["data" => ["success" => false, "message" => "otp is wrong"]], 200);
            }
        } else {
            return response()->json(["data" => ["success" => false, "message" => "phone number not found"]], 200);
        }
    }

    public function setPassword(Request $request)
    {

        $user = User::where('phone_number',$request->phone)->first();
        $user->password = Hash::make($request->password);
        $user->name = $request->name;
        $user->save();

        return response()->json(['data'=>['suscess' => true, 'massage'=> 'info set correctly']]);
    }
    public function checkauth(Request $request)
    {
        $user = User::where("phone", $request->phone)->first();
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(["data" => ["success" => false, "message" => "wrong password"]], 200);
        }
        $newJwt = Str::random(40);
        $user->jwt = $newJwt;
        $user->save();

        return response()->json(["data" => ["success" => false, "jwt" => $newJwt]], 200);
    }


}
