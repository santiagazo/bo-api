<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\LoginCode;
use App\Models\User;
use App\Services\PhoneService;
use App\Services\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle login for both web (cookie) and mobile (token) clients.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        // For web SPA requests (stateful), use session-based auth
        if ($this->isStatefulRequest($request)) {
            if (! Auth::attempt($credentials, $request->boolean('remember'))) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            $request->session()->regenerate();

            return response()->json(
                new UserResource($request->user()->load(['memberships.company.address', 'memberships.roles.permissions', 'locations.address']))
            );
        }

        // For mobile/API requests (stateless), issue a token
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke previous tokens if needed (optional - uncomment for single device)
        // $user->tokens()->delete();

        $token = $user->createToken($request->input('device_name', 'mobile'))->plainTextToken;

        return response()->json([
            'user' => new UserResource($user->load(['memberships.company.address', 'memberships.roles.permissions', 'locations.address'])),
            'token' => $token,
        ]);
    }

    /**
     * Public registration is intentionally disabled.
     *
     * Built Ox is an invite-only SaaS. New users should be created through an
     * company-scoped invitation/onboarding flow so they never land in the
     * wrong company context accidentally.
     */
    public function register(): JsonResponse
    {
        return response()->json([
            'message' => 'Built Ox is invite-only. Ask an administrator to invite you.',
        ], 403);
    }

    /**
     * Handle logout for both web and mobile.
     */
    public function logout(Request $request): JsonResponse
    {
        // For stateful requests, invalidate the session
        if ($this->isStatefulRequest($request)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json(['message' => 'Logged out successfully']);
        }

        // For token-based auth, revoke the current token
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Get the authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json(
            new UserResource($request->user()->load(['memberships.company.address', 'memberships.roles.permissions', 'locations.address']))
        );
    }

    /**
     * Check if this is a stateful (web SPA) request.
     */
    protected function isStatefulRequest(Request $request): bool
    {
        return $request->hasSession() &&
               $request->session()->isStarted();
    }

    /**
     * Send a login code via SMS.
     * Rate limited to 3 requests per hour per phone number (disabled in local dev).
     */
    public function sendCode(Request $request, SmsService $sms, PhoneService $phoneService): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
        ]);

        // Normalize phone to E.164 for database lookup
        $phone = $phoneService->formatToE164($request->phone);

        if (! $phone) {
            // Invalid phone format, but don't reveal this
            return response()->json([
                'message' => 'If this phone number is registered, you will receive a code.',
                'expires_in' => 600,
            ]);
        }

        // Rate limiting: 3 codes per hour per phone (skip in local/testing)
        if (! app()->environment('local', 'testing')) {
            $key = 'login-code:'.$phone;
            if (RateLimiter::tooManyAttempts($key, 3)) {
                $seconds = RateLimiter::availableIn($key);
                throw ValidationException::withMessages([
                    'phone' => ["Too many code requests. Try again in {$seconds} seconds."],
                ]);
            }
        }

        // Find user by phone (E.164 format in database)
        $user = User::where('phone', $phone)->first();

        if (! $user) {
            // Don't reveal if user exists - still return success
            // but log for debugging
            \Log::info('Login code requested for non-existent phone', ['phone' => $phone]);

            return response()->json([
                'message' => 'If this phone number is registered, you will receive a code.',
                'expires_in' => 600,
            ]);
        }

        // Generate and send code
        $loginCode = LoginCode::generateFor($user, 10); // 10 minutes
        $sms->sendLoginCode($phone, $loginCode->code);

        // Track rate limit (skip in local/testing)
        if (! app()->environment('local', 'testing')) {
            RateLimiter::hit('login-code:'.$phone, 3600); // 1 hour window
        }

        return response()->json([
            'message' => 'If this phone number is registered, you will receive a code.',
            'expires_in' => 600,
        ]);
    }

    /**
     * Verify a login code and authenticate the user.
     * Returns a flag indicating password reset is recommended.
     */
    public function verifyCode(Request $request, PhoneService $phoneService): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        // Normalize phone to E.164 for database lookup
        $phone = $phoneService->formatToE164($request->phone);
        $user = $phone ? User::where('phone', $phone)->first() : null;

        if (! $user) {
            throw ValidationException::withMessages([
                'code' => ['Invalid code.'],
            ]);
        }

        // Find valid code
        $loginCode = LoginCode::where('user_id', $user->id)
            ->where('code', $request->code)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $loginCode) {
            throw ValidationException::withMessages([
                'code' => ['Invalid or expired code.'],
            ]);
        }

        // Mark code as used
        $loginCode->markAsUsed();

        // Log user in
        if ($this->isStatefulRequest($request)) {
            Auth::login($user, true); // Remember for 30 days
            $request->session()->regenerate();

            return response()->json([
                'user' => new UserResource($user->load(['memberships.company.address', 'memberships.roles.permissions', 'locations.address'])),
                'logged_in_via_code' => true, // Frontend uses this to prompt password reset
            ]);
        }

        // For mobile, return a token
        $token = $user->createToken($request->input('device_name', 'mobile'))->plainTextToken;

        return response()->json([
            'user' => new UserResource($user->load(['memberships.company.address', 'memberships.roles.permissions', 'locations.address'])),
            'token' => $token,
            'logged_in_via_code' => true,
        ]);
    }

    /**
     * Reset password for authenticated user.
     * Used after logging in with a code.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }
}
