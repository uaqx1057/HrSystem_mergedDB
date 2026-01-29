<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\DriverCheckIn;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiDriverController extends Controller
{

    function encrypt_value($value)
    {
        $ciphering = "AES-128-CTR";
        $options = 0;
        $encryption_iv = '1234567891011121';
        $encryption_key = 'H%$^&%!@)(*)^%0';
        $value = openssl_encrypt($value, $ciphering, $encryption_key, $options, $encryption_iv);
        $value = str_replace('/', '_', $value);
        return $value;
    }

    function decrypt_value($value)
    {
        $ciphering = "AES-128-CTR";
        $options = 0;
        $encryption_iv = '1234567891011121';
        $encryption_key = 'H%$^&%!@)(*)^%0';
        $value = str_replace('_', '/', $value);
        $value = openssl_decrypt($value, $ciphering, $encryption_key, $options, $encryption_iv);
        return $value;
    }

    public function login(Request $request)
    {
    // Define validation rules
    $rules = [
        'iqaama_number' => 'required',
        'password' => 'required',
    ];

    // Define custom error messages
    $messages = [
        'iqaama_number.required' => 'The iqaama number field is required.',
        'password.required' => 'The password field is required.',
    ];

    // Perform validation
    $validator = Validator::make($request->all(), $rules, $messages);

    // Check if validation fails
    if ($validator->fails()) {
        return response()->json([
            'status' => 422,
            'message' => 'Validation Error',
            'data' => $validator->errors()
        ], 422);
    }

    $driver = Driver::where('iqaama_number', $request->iqaama_number)->first();

        if ($driver && Hash::check($request->password, $driver->password)) {
            $token = $this->encrypt_value($driver->iqaama_number);
            return response()->json([
                'status' => 200,
                'message' => 'Driver Login successful!',
                'data' => $token
            ], 200);
        } else {
            return response()->json(['status' => 401, 'message' => 'Invalid Iqaama Or Password ', 'data' => []], 401);
        }
    }

    public function driverCheckCurrentStatus(Request $request){
        $driver = $request->attributes->get('driver');

        $checkDriverStatus = DriverCheckIn::where('driver_id', $driver->id)->orderBy('id', 'desc')->first();

        if(!is_null($checkDriverStatus)){
            return response()->json([
                'status' => 200,
                'message' => 'Driver Status Fetched!',
                'data' => $checkDriverStatus->status
            ], 200);
        }else{
            return response()->json([
                'status' => 404,
                'message' => 'No Status Found',
                'data' => []
            ], 404);
        }
    }

    public function getDriverBusinesses(Request $request){
        $driver = $request->attributes->get('driver');
        $driver = Driver::with('businesses')->find($driver->id);

        return response()->json([
            'status' => 200,
            'message' => 'Business Fetched Successfully!',
            'data' => $driver->businesses
        ], 200);

    }

    public function driverCheckStatus(Request $request)
    {
        // Define validation rules
        $rules = [
            'status' => 'required|in:check-in,check-out',
        ];

        // Define custom error messages
        $messages = [
            'status.required' => 'The status field is required.',
        ];

        // Perform validation
        $validator = Validator::make($request->all(), $rules, $messages);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        $driver = $request->attributes->get('driver');

        $checkDriverStatus = DriverCheckIn::where('driver_id', $driver->id)->orderBy('id', 'desc')->first();

        if(is_null($checkDriverStatus) && $request->status == 'check-in'){
            DriverCheckIn::create(['driver_id' =>  $driver->id, 'status' => $request->status]);
            return response()->json([
                'status' => 200,
                'message' => 'Driver Check in successful!',
                'data' => []
            ], 200);
        }elseif(is_null($checkDriverStatus) && $request->status == 'check-out'){
            return response()->json([
                'status' => 400,
                'message' => 'Please check in first',
                'data' => []
            ], 400);
        }elseif($checkDriverStatus->status == 'check-out' && ($request->status == 'check-out' || $request->status == 'check-in')){
            return response()->json(['status' => 400, 'message' => 'Already Check Out', 'data' => []], 400);
        }elseif($checkDriverStatus->status == 'check-in' && $request->status == 'check-out'){
            $checkDriverStatus->status = $request->status;
            return response()->json([
                'status' => 200,
                'message' => 'Driver Check out successful!',
                'data' => []
            ], 200);
        }else{
            return response()->json([
                'status' => 400,
                'message' => 'Please check out first',
                'data' => []
            ], 400);
        }
    }

    public function logout(Request $request)
    {
        return response()->json([
            'status' => 200,
            'message' => 'Logged out successfully!',
            'data' => []
        ]);
    }

    public function test(Request $request) {

    }
}
