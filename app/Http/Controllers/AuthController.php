<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Handle the Login Request
     */
    public function login(Request $request)
    {
        // 1. Validate incoming data
        $request->validate([
            'login_id' => 'required|string',
            'password' => 'nullable|string',
            'pin'      => 'nullable|string',
            'use_pin'  => 'nullable|boolean',
        ]);

        // 2. Determine if the user typed an email or a Teacher ID
        $loginType = filter_var($request->login_id, FILTER_VALIDATE_EMAIL) ? 'email' : 'teacher_id';

        // ── PIN-based login path ──────────────────────────────────────────────
        if ($request->boolean('use_pin')) {
            $user = User::where($loginType, $request->login_id)->first();

            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'Invalid PIN or account not found.'], 401);
            }

            // A. Check standard account lockout first
            if ($user->account_locked_until && $user->account_locked_until->isFuture()) {
                return response()->json(['status' => 'error', 'message' => 'Account locked. Please contact support.'], 403);
            }

            // B. Check PIN lockout
            if ($user->pin_locked_until && $user->pin_locked_until->isFuture()) {
                $secondsRemaining = now()->diffInSeconds($user->pin_locked_until, false);
                if ($secondsRemaining > 0) {
                    $minutes = floor($secondsRemaining / 60);
                    $seconds = $secondsRemaining % 60;
                    $minutesStr = $minutes > 0 ? "$minutes minute" . ($minutes > 1 ? "s" : "") : "";
                    $secondsStr = $seconds > 0 ? "$seconds second" . ($seconds != 1 ? "s" : "") : "";
                    $timeStr = ($minutesStr && $secondsStr) ? "$minutesStr and $secondsStr" : ($minutesStr ?: $secondsStr);
                    if (empty($timeStr)) {
                        $timeStr = "0 seconds";
                    }
                    return response()->json([
                        'status' => 'error',
                        'message' => "Too many failed login attempts. Your account has been temporarily locked for 15 minutes. Try again in $timeStr."
                    ], 423);
                }
            }

            // C. Validate PIN
            if (!$user->pin || !Hash::check($request->pin, $user->pin)) {
                $user->failed_pin_attempts += 1;
                
                // Audit log for failed PIN
                \App\Models\AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'Failed PIN',
                    'table_name' => 'users',
                    'record_id' => $user->id,
                    'description' => "Failed PIN attempt. IP: " . $request->ip() . ", User-Agent: " . $request->userAgent(),
                ]);

                if ($user->failed_pin_attempts >= 3) {
                    $user->pin_locked_until = now()->addMinutes(15);
                    $user->save();

                    // Audit log for Account Locked
                    \App\Models\AuditLog::create([
                        'user_id' => $user->id,
                        'action' => 'Account Locked',
                        'table_name' => 'users',
                        'record_id' => $user->id,
                        'description' => "Account locked due to 3 consecutive failed PIN attempts. IP: " . $request->ip(),
                    ]);

                    return response()->json([
                        'status' => 'error',
                        'message' => "Too many failed login attempts. Your account has been temporarily locked for 15 minutes. Please try again later or contact the administrator."
                    ], 423);
                }

                $user->save();
                $remaining = 3 - $user->failed_pin_attempts;
                return response()->json([
                    'status' => 'error',
                    'message' => "Invalid PIN. You have $remaining attempt" . ($remaining > 1 ? "s" : "") . " remaining."
                ], 401);
            }

            // D. Success PIN login
            $user->failed_pin_attempts = 0;
            $user->pin_locked_until = null;
            $user->save();

            Auth::guard('web')->login($user);
            $request->session()->regenerate();
            $request->session()->save();

            $redirectUrl = ($user->role === 'admin') ? '/admin/dashboard' : '/borrower/dashboard';
            return response()->json(['status' => 'success', 'message' => 'Login successful', 'redirect' => $redirectUrl], 200);
        }

        // ── Password-based login path (original) ─────────────────────────────
        if (Auth::guard('web')->attempt([$loginType => $request->login_id, 'password' => $request->password])) {
            
            $user = Auth::user();

            // FLOWCHART CHECK A: Account Lock Logic
            if ($user->account_locked_until && $user->account_locked_until->isFuture()) {
                Auth::guard('web')->logout();
                return response()->json(['status' => 'error', 'message' => 'Account locked. Please contact support.'], 403);
            }

            // FLOWCHART CHECK B & C: Unverified OR Needs Password Change
            if (!$user->is_active || $user->requires_password_change) {
                
                // 1. Log them out immediately so they can't access protected routes!
                Auth::guard('web')->logout();

                // 2. Put them in the "Holding Area"
                $request->session()->put('pending_verification_email', $user->email);

                // 3. Where do they need to go?
                if ($user->requires_password_change) {
                    return response()->json([
                        'status' => 'verify', 
                        'message' => 'Temporary credentials accepted. Please set your new password.', 
                        'redirect' => '/force-change-password'
                    ]);
                } else {
                    return response()->json([
                        'status' => 'verify', 
                        'message' => 'Account not verified. Please verify your OTP.', 
                        'redirect' => '/verify-otp'
                    ]);
                }
            }

            // Reset failed PIN attempts and lockout timer on successful password login too
            $user->update([
                'failed_pin_attempts' => 0,
                'pin_locked_until' => null
            ]);

            // FLOWCHART CHECK D: Standard Successful Login
            $request->session()->regenerate();
            $request->session()->save(); 

            $redirectUrl = ($user->role === 'admin') ? '/admin/dashboard' : '/borrower/dashboard';
            
            return response()->json([
                'status' => 'success', 
                'message' => 'Login successful', 
                'redirect' => $redirectUrl
            ], 200);
        }

        // Authentication Failed
        return response()->json(['status' => 'error', 'message' => 'Invalid credentials provided.'], 401);
    }

    /**
     * Set up the user's 4-digit PIN for the first time.
     */
    public function setPin(Request $request)
    {
        $request->validate([
            'pin' => ['required', 'string', 'digits:4'],
        ]);

        $user = Auth::user();
        $user->pin = Hash::make($request->pin);
        $user->pin_setup_completed = true;
        $user->save();

        return response()->json(['status' => 'success', 'message' => 'PIN set up successfully.']);
    }

    /**
     * Verify the user's PIN (used before submitting a borrow request).
     */
    public function verifyPin(Request $request)
    {
        $request->validate([
            'pin' => ['required', 'string', 'digits:4'],
        ]);

        $user = Auth::user();

        // 1. Check if user is currently locked out of borrowing requests
        if ($user->borrow_pin_locked_until) {
            if ($user->borrow_pin_locked_until->isPast()) {
                $user->failed_borrow_pin_attempts = 0;
                $user->borrow_pin_locked_until = null;
                $user->save();
            } else {
                $secondsRemaining = now()->diffInSeconds($user->borrow_pin_locked_until, false);
                if ($secondsRemaining > 0) {
                    $minutes = floor($secondsRemaining / 60);
                    $seconds = $secondsRemaining % 60;
                    $minutesStr = str_pad($minutes, 2, '0', STR_PAD_LEFT);
                    $secondsStr = str_pad($seconds, 2, '0', STR_PAD_LEFT);
                    return response()->json([
                        'status' => 'error',
                        'message' => "Request submission is temporarily locked. Please try again in $minutesStr minutes and $secondsStr seconds."
                    ], 423);
                }
            }
        }

        // 2. Validate PIN
        if (!$user->pin || !Hash::check($request->pin, $user->pin)) {
            $user->failed_borrow_pin_attempts += 1;

            // Audit log: Failed Request PIN verification
            \App\Models\AuditLog::create([
                'user_id' => $user->id,
                'action' => 'Failed Request PIN verification',
                'table_name' => 'users',
                'record_id' => $user->id,
                'description' => "Incorrect PIN attempt during request confirmation. IP: " . $request->ip() . ", User-Agent: " . $request->userAgent(),
            ]);

            if ($user->failed_borrow_pin_attempts >= 3) {
                $user->borrow_pin_locked_until = now()->addMinutes(15);
                $user->save();

                // Audit log: Request submission temporarily locked
                \App\Models\AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'Request submission temporarily locked',
                    'table_name' => 'users',
                    'record_id' => $user->id,
                    'description' => "Borrowing requests locked for 15 minutes due to 3 consecutive failed PIN attempts. IP: " . $request->ip(),
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Too many incorrect PIN attempts. You cannot submit borrowing requests for the next 15 minutes.'
                ], 423);
            }

            $user->save();
            $remaining = 3 - $user->failed_borrow_pin_attempts;
            $attemptStr = $remaining === 1 ? "attempt" : "attempts";
            return response()->json([
                'status' => 'error',
                'message' => "Incorrect PIN. You have $remaining $attemptStr remaining."
            ], 422);
        }

        // 3. Successful PIN Verification
        if ($user->failed_borrow_pin_attempts > 0) {
            // Audit log: Successful Request PIN verification after previous failures
            \App\Models\AuditLog::create([
                'user_id' => $user->id,
                'action' => 'Successful Request PIN verification after previous failures',
                'table_name' => 'users',
                'record_id' => $user->id,
                'description' => "Successful PIN verification after previous failed attempts. IP: " . $request->ip(),
            ]);
        }

        $user->failed_borrow_pin_attempts = 0;
        $user->borrow_pin_locked_until = null;
        $user->save();

        return response()->json(['status' => 'success', 'message' => 'PIN verified.']);
    }

    /**
     * Verify the OTP and Activate the Account
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric|digits:6',
        ]);

        $email = $request->session()->get('pending_verification_email');
        
        if (!$email) {
            return response()->json(['status' => 'error', 'message' => 'Session expired. Please log in to request a new code.'], 400);
        }

        $user = \App\Models\User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
        }

        // 1. Check if OTP is expired
        if (!$user->otp_expires_at || $user->otp_expires_at->isPast()) {
            return response()->json(['status' => 'error', 'message' => 'This code has expired. Please request a new one.'], 400);
        }

        // 2. Verify the Hash
        if (!Hash::check($request->otp, $user->otp_code)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid verification code.'], 400);
        }

        // 3. Success! Get the held password from the session
        $newPassword = $request->session()->get('pending_new_password');

        // 4. Activate the user, clear the OTP, and SAVE the new password
        $user->update([
            'password' => $newPassword ? $newPassword : $user->password, // Save new password
            'is_active' => true,
            'requires_password_change' => false,
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        // Clear all pending sessions
        $request->session()->forget(['pending_verification_email', 'pending_new_password']);

        return response()->json([
            'status' => 'success',
            'message' => 'Account activated and password updated! You may now log in.',
            'redirect' => '/' 
        ], 200);
    }

    /**
     * Handle the Logout Request
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'status' => 'success', 
            'message' => 'Logged out successfully', 
            'redirect' => '/'
        ], 200);
    }

    /**
     * Hold the new password and send the OTP
     */
    public function processForcePasswordChange(Request $request)
    {
        // 1. Validate the new passwords match and are secure
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $email = $request->session()->get('pending_verification_email');
        if (!$email) {
            return response()->json(['message' => 'Session expired. Please log in again.'], 400);
        }

        $user = \App\Models\User::where('email', $email)->first();

        // 2. Hash the new password and hold it in the server session! (DO NOT SAVE TO DB YET)
        $request->session()->put('pending_new_password', Hash::make($request->password));

        // 3. NOW we generate and send the OTP
        $otpCode = rand(100000, 999999);
        
        $user->update([
            'otp_code' => \Illuminate\Support\Facades\Hash::make($otpCode),
            'otp_expires_at' => now()->addMinutes(10)
        ]);

        \Illuminate\Support\Facades\Log::info("🔐 NEW OTP FOR {$user->email}: " . $otpCode);

        // 4. Send them to the verification screen
        return response()->json([
            'status' => 'success',
            'message' => 'Password accepted. Sending verification code...',
            'redirect' => '/verify-otp'
        ], 200);
    } 
}