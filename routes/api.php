<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Otp;
use Carbon\Carbon;
use SalimMbise\OtpLibrary\OtpMailer;
use SalimMbise\OtpLibrary\OtpService;

// Route to generate and save OTP
Route::post('/otp/generate', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
    ]);

    $email = $request->input('email');

    // Instantiate OtpMailer and OtpService
    $otpMailer = new OtpMailer();
    $otpService = new OtpService();

    // Generate a random OTP using OtpService
    $otp = $otpService->generateOtp(6);

    // Store OTP in the database
    Otp::updateOrCreate(
        ['email' => $email],  // Update if the email already exists
        [
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes(5),  // Set OTP expiry for 5 minutes
            'verified' => false, // Mark as not verified
        ]
    );

    // Send the OTP via email (or other methods)
    $otpMailer->sendOtpEmail($email, $otp);

    return response()->json(['message' => 'OTP generated successfully', 'otp' => $otp]);
});

// Route to verify OTP
Route::post('/otp/verify', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'otp' => 'required|numeric',
    ]);

    $email = $request->input('email');
    $inputOtp = $request->input('otp');

    // Retrieve OTP from the database
    $otpRecord = Otp::where('email', $email)->first();

    if (!$otpRecord) {
        return response()->json(['message' => 'No OTP found'], 400);
    }

    // Check if OTP has already been verified
    if ($otpRecord->verified) {
        return response()->json(['message' => 'OTP has already been verified'], 400);
    }

    // Check if OTP matches
    if ($otpRecord->otp !== $inputOtp) {
        return response()->json(['message' => 'OTP does not match'], 400);
    }

    // Check if OTP is expired
    if (Carbon::now()->gt($otpRecord->expires_at)) {
        return response()->json(['message' => 'OTP has expired'], 400);
    }

    // Mark OTP as verified
    $otpRecord->update(['verified' => true]);

    // OTP is valid, return success
    return response()->json(['message' => 'OTP verified successfully']);
});
