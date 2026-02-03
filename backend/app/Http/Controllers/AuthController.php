<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        // Check if user exists and password is correct
        if ($user && Hash::check($credentials['password'], $user->password)) {
            // Check if email is verified
            if (!$user->isEmailVerified()) {
                return back()->withErrors([
                    'email' => 'Tu correo electrónico no ha sido verificado. Por favor, verifica tu correo antes de iniciar sesión.',
                ])->with('verification_required', true)
                  ->with('user_email', $user->email)
                  ->onlyInput('email');
            }

            // Login user
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    /**
     * Show the registration form.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // Get FREE plan by default for new companies
        $freePlan = SubscriptionPlan::where('slug', 'free')->first();
        
        // Generate slug from company name
        $companyName = $validated['name'] . ' Company';
        $baseSlug = Str::slug($companyName);
        $slug = $baseSlug;
        $counter = 1;
        
        // Ensure slug is unique
        while (Company::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        // Create company first with FREE plan assigned
        $company = Company::create([
            'name' => $companyName,
            'slug' => $slug,
            'currency' => 'UYU',
            'settings' => [],
            'plan_id' => $freePlan ? $freePlan->id : null,
        ]);

        // Create user and assign to company
        // First user is automatically owner
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'company_id' => $company->id,
            'is_owner' => true, // First user is owner
            'email_verified_at' => null, // Email not verified yet
        ]);

        // Generate verification token
        $token = $user->generateVerificationToken();

        // Build verification URL (valid for 15 minutes)
        $verificationUrl = url('/verify-email/' . $token);
        \Log::info('Verification URL: ' . $verificationUrl);

        // Send to n8n webhook
        try {
            Http::post('https://n8n.srv1206881.hstgr.cloud/webhook/cartify/new-user', [
                'name' => $user->name,
                'email' => $user->email,
                'verification_url' => $verificationUrl,
            ]);
            \Log::info('Verification email webhook sent successfully');
        } catch (\Exception $e) {
            \Log::error('Error sending verification email webhook: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
        }

        // Don't login automatically, require email verification
        return redirect('login')->with('message', 'Te hemos enviado un correo de verificación. Por favor, verifica tu correo electrónico antes de iniciar sesión.')
            ->with('user_email', $user->email);
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('login');
    }

    /**
     * Show the dashboard (transition).
     */
    public function dashboard()
    {
        return view('dashboard');
    }

    /**
     * Show the final home page.
     */
    public function home()
    {
        return view('home');
    }

    /**
     * Show the forgot password form.
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link via n8n webhook.
     */
    public function sendPasswordResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        // Always return success message to prevent email enumeration
        $status = 'Si tu correo existe en nuestro sistema, recibirás un enlace para restablecer tu contraseña.';

        if ($user) {
            // Generate token
            $token = Str::random(64);
            $expiresAt = Carbon::now()->addHours(1); // Token válido por 1 hora

            // Store token in database (hash the token for security)
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => Carbon::now(),
                ]
            );

            // Create reset URL
            $resetUrl = route('password.reset', [
                'token' => $token,
                'email' => $user->email,
            ]);

            // Send to n8n webhook
            try {
                $n8nWebhookUrl = config('services.n8n.password_reset_webhook');
                
                Http::timeout(10)->post($n8nWebhookUrl, [
                    'email' => $user->email,
                    'name' => $user->name,
                    'reset_url' => $resetUrl,
                    'expires_at' => $expiresAt->toIso8601String(),
                ]);

                Log::info('Password reset link sent via n8n', [
                    'email' => $user->email,
                    'reset_url' => $resetUrl,
                ]);
            } catch (\Exception $e) {
                Log::error('Error sending password reset link via n8n', [
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
                // Continue even if webhook fails - token is still generated
            }
        }

        return back()->with('status', $status);
    }

    /**
     * Show the password reset form.
     */
    public function showResetPassword(Request $request, $token)
    {
        $email = $request->query('email');

        if (!$email) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Correo electrónico requerido.']);
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Handle password reset.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // Find token in database
        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$tokenRecord) {
            return back()->withErrors(['email' => 'Token inválido o expirado.'])
                ->withInput($request->only('email'));
        }

        // Check if token is valid (created within last hour)
        $tokenAge = Carbon::parse($tokenRecord->created_at);
        if ($tokenAge->addHour()->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'El token ha expirado. Por favor, solicita un nuevo enlace.'])
                ->withInput($request->only('email'));
        }

        // Verify token
        if (!Hash::check($request->token, $tokenRecord->token)) {
            return back()->withErrors(['email' => 'Token inválido.'])
                ->withInput($request->only('email'));
        }

        // Find user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Usuario no encontrado.'])
                ->withInput($request->only('email'));
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        Log::info('Password reset successful', ['email' => $user->email]);

        return redirect()->route('login')
            ->with('success', 'Tu contraseña ha sido restablecida exitosamente. Puedes iniciar sesión ahora.');
    }
}
