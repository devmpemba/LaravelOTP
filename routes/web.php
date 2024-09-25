<?php

use App\Http\Controllers\OtpController;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Route;
use SalimMbise\OtpLibrary\OtpMailer;
use SalimMbise\OtpLibrary\OtpService;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Route::post('/send-otp', [OtpController::class, 'sendOtp']);
// Route::post('/verify-otp', [OtpController::class, 'verifyOtp']);

Route::get('/otp/send', function () {
    try {
        // Instantiate the OtpMailer class
        $otpMailer = new OtpMailer();

        // Generate a test OTP
        $otp = rand(100000, 999999);

        // Use a test email to send the OTP
        $testEmail = 'mpembaweb@gmail.com';

        // Send the OTP email
        $otpMailer->sendOtpEmail($testEmail, $otp);

        return "OTP email sent successfully to $testEmail with OTP: $otp";
    } catch (\Exception $e) {
        return "Failed to send OTP email. Error: " . $e->getMessage();
    }
});



Route::post('/otp/verify',function(Request $request) {

    $request->validate([
        'email' => 'required|email',
        'otp' => 'required|numeric',
    ]);

    $email = $request->input('email');
    $inputOtp = $request->input('otp');

    // Instantiate the OtpService
    $otpService = new OtpService();

    // Verify OTP
    $isValid = $otpService->verifyOtp($email, $inputOtp);

    if ($isValid) {
        return response()->json(['message' => 'OTP verified successfully']);
    } else {
        return response()->json(['message' => 'OTP verification failed'], 400);
    }
});
