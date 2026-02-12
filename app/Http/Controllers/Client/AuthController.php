<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('client.auth.login');
    }

    /**
     * Redirect to Google OAuth (login).
     */
    public function redirectToGoogle()
    {
        session()->forget('google_signup_intent');
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Redirect to Google OAuth (signup) – stores intent so callback can reject existing emails.
     */
    public function redirectToGoogleSignup()
    {
        session(['google_signup_intent' => true]);
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Handle Google OAuth callback: find or create user, then log in.
     */
    public function handleGoogleCallback()
    {
        try {
            $driver = Socialite::driver('google')->stateless();
            // Use project CA bundle for SSL (e.g. cacert.pem in project root)
            $caPath = base_path('cacert.pem');
            if (file_exists($caPath)) {
                $driver->setHttpClient(new \GuzzleHttp\Client(['verify' => $caPath]));
            }
            $googleUser = $driver->user();
        } catch (\Exception $e) {
            Log::error('Google OAuth callback failed', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
            return redirect()->route('login')->withErrors(['email' => 'Google sign-in failed or was cancelled. Please try again.']);
        }

        $isSignupIntent = session()->pull('google_signup_intent', false);

        $user = User::withTrashed()->where('google_id', $googleUser->getId())->first();

        if ($user) {
            // Already has google_id: if signup intent, they're trying to register again → show error
            if ($isSignupIntent) {
                return redirect()->route('signup')->withErrors(['email' => 'This email is already registered.']);
            }
            if ($user->trashed()) {
                return redirect()->route('login')->withErrors(['email' => 'Your account has been deleted. Please contact the administrator.']);
            }
            if ($user->status !== 'active') {
                return redirect()->route('login')->withErrors(['email' => 'Your account is inactive. Please contact the administrator.']);
            }
        } else {
            // Find by email (manual signup): link Google by filling google_id, then log in and redirect
            $user = User::withTrashed()->where('email', $googleUser->getEmail())->first();

            if ($user) {
                $user->google_id = $googleUser->getId();
                $user->save();
                if ($user->trashed() || $user->status !== 'active') {
                    return redirect()->route('login')->withErrors(['email' => 'Your account is not available. Please contact the administrator.']);
                }
            } else {
                // No account exists: login intent → show error (same as manual login); signup intent → create user
                if (!$isSignupIntent) {
                    return redirect()->route('login')->withErrors(['email' => 'This email address does not exist.']);
                }
                $username = $this->uniqueUsernameFromGoogle($googleUser);
                $user = User::create([
                    'username' => $username,
                    'name' => $googleUser->getName() ?? $googleUser->getEmail(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => Hash::make(Str::random(32)),
                    'role' => 'client',
                    'status' => 'active',
                ]);
            }
        }

        $user->last_login_at = now();
        $user->save();

        Auth::login($user, true);

        if ($user->role === 'admin') {
            return redirect()->intended(route('admin.dashboard'))->with('success', 'Welcome back!');
        }

        return redirect()->intended(route('home'))->with('success', 'Welcome back!');
    }

    /**
     * Generate a unique username from Google user (email prefix or name).
     */
    private function uniqueUsernameFromGoogle($googleUser): string
    {
        $email = $googleUser->getEmail();
        $base = $email ? Str::before($email, '@') : Str::slug($googleUser->getName() ?? 'user');
        $base = preg_replace('/[^a-zA-Z0-9_]/', '', $base) ?: 'user';
        $username = Str::limit($base, 20, '');
        $original = $username;
        $n = 0;
        while (User::where('username', $username)->exists()) {
            $username = $original.++$n;
        }
        return $username;
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Password is required.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if email exists (including soft-deleted users)
        $user = User::withTrashed()->where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'This email address does not exist.',
            ])->onlyInput('email');
        }

        // Check if user is soft deleted
        if ($user->trashed()) {
            return back()->withErrors([
                'email' => 'Your account has been deleted. Please contact the administrator.',
            ])->onlyInput('email');
        }

        // Check if user is active
        if ($user->status !== 'active') {
            return back()->withErrors([
                'email' => 'Your account is inactive. Please contact the administrator.',
            ])->onlyInput('email');
        }

        // Attempt authentication
        if (auth()->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Update last login time
            $user->last_login_at = now();
            $user->save();

            // Redirect admin users to admin panel
            if ($user->role === 'admin') {
                return redirect()->intended(route('admin.dashboard'))->with('success', 'Welcome back!');
            }

            return redirect()->intended(route('home'))->with('success', 'Welcome back!');
        }

        // If authentication fails, password is incorrect
        return back()->withErrors([
            'password' => 'The password is incorrect.',
        ])->onlyInput('email');
    }

    public function showSignup()
    {
        return view('client.auth.signup');
    }

    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
                'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'
            ],
        ], [
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
            'password.regex' => 'Password must contain at least one letter and one number.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'username' => $request->username,
            'name' => $request->username, // Using username as name for now
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'client', // Default role for signups
        ]);

        // Log the user in after registration
        auth()->login($user);

        return redirect()->route('signup')->with('success', 'Account created successfully!');
    }

    public function showForgotPassword()
    {
        return view('client.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $validator = Validator::make($request->only('email'), [
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', __($status));
        }

        return back()->withErrors(['email' => __($status)])->withInput();
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('client.auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
                'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/',
            ],
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
            'password.regex' => 'Password must contain at least one letter and one number.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput($request->only('email'));
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', __($status));
        }

        return back()->withErrors(['email' => __($status)])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been logged out successfully.');
    }
}
