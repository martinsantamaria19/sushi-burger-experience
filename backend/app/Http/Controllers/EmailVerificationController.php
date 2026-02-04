<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EmailVerificationController extends Controller
{
    /**
     * Verify email with token
     */
    public function verify($token)
    {
        $user = User::where('email_verification_token', $token)->first();

        if (!$user) {
            return redirect()->route('login')->with('error', 'El enlace de verificación no es válido o ya fue utilizado.');
        }

        // Check if token is still valid (15 minutes)
        $sentAt = $user->email_verification_sent_at;
        if (!$sentAt) {
            return redirect()->route('login')->with('error', 'El enlace de verificación no es válido.');
        }

        $expiresAt = $sentAt->copy()->addMinutes(15);
        if ($expiresAt->isPast()) {
            return redirect()->route('login')->with('error', 'El enlace de verificación ha expirado. Por favor, solicita un nuevo enlace.')
                ->with('user_email', $user->email)
                ->with('verification_required', true);
        }

        // Verify email
        $user->verifyEmail();

        return redirect()->route('login')->with('success', '¡Correo verificado exitosamente! Ya puedes iniciar sesión.');
    }

    /**
     * Resend verification email
     */
    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'No se encontró un usuario con ese correo electrónico.',
            ]);
        }

        if ($user->isEmailVerified()) {
            return back()->with('message', 'Tu correo electrónico ya está verificado.');
        }

        // Generate new verification token
        $token = $user->generateVerificationToken();

        // Build verification URL (valid for 15 minutes)
        $verificationUrl = url('/verify-email/' . $token);

        // Send to n8n webhook
        try {
            Http::post('https://n8n.srv1206881.hstgr.cloud/webhook/cartify/new-user', [
                'name' => $user->name,
                'email' => $user->email,
                'verification_url' => $verificationUrl,
            ]);

            return back()->with('success', 'Se ha enviado un nuevo enlace de verificación a tu correo electrónico.');
        } catch (\Exception $e) {
            \Log::error('Error sending verification email webhook: ' . $e->getMessage());
            return back()->withErrors([
                'email' => 'Error al enviar el correo de verificación. Por favor, intenta nuevamente más tarde.',
            ]);
        }
    }
}

