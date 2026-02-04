<?php

namespace App\Http\Controllers;

use App\Models\MercadoPagoAccount;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MercadoPagoAccountController extends Controller
{
    /**
     * Show MercadoPago account configuration page.
     */
    public function index()
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No tienes una empresa asociada');
        }

        if (!$company->hasEcommerce()) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'La funcionalidad de ecommerce no está habilitada para esta empresa');
        }

        $mpAccount = $company->mercadopagoAccount;

        return view('admin.mercadopago', [
            'company' => $company,
            'mpAccount' => $mpAccount,
        ]);
    }

    /**
     * Store or update MercadoPago account credentials.
     */
    public function store(Request $request)
    {
        $request->validate([
            'access_token' => 'required|string',
            'public_key' => 'required|string',
            'app_id' => 'nullable|string',
            'user_id' => 'nullable|string',
            'environment' => 'required|in:sandbox,production',
        ]);

        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes una empresa asociada',
            ], 400);
        }

        try {
            $mpAccount = $company->mercadopagoAccount;

            if ($mpAccount) {
                // Update existing account
                $mpAccount->update([
                    'access_token' => $request->access_token,
                    'public_key' => $request->public_key,
                    'app_id' => $request->app_id,
                    'user_id' => $request->user_id,
                    'environment' => $request->environment,
                    'is_active' => true,
                    'connected_at' => now(),
                ]);
            } else {
                // Create new account
                $mpAccount = MercadoPagoAccount::create([
                    'company_id' => $company->id,
                    'access_token' => $request->access_token,
                    'public_key' => $request->public_key,
                    'app_id' => $request->app_id,
                    'user_id' => $request->user_id,
                    'environment' => $request->environment,
                    'is_active' => true,
                    'connected_at' => now(),
                ]);
            }

            Log::info('MercadoPago account configured', [
                'company_id' => $company->id,
                'mp_account_id' => $mpAccount->id,
                'environment' => $request->environment,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cuenta de MercadoPago configurada correctamente',
                'account' => $mpAccount,
            ]);
        } catch (\Exception $e) {
            Log::error('Error configuring MercadoPago account', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al configurar la cuenta: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Disconnect MercadoPago account.
     */
    public function destroy()
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes una empresa asociada',
            ], 400);
        }

        try {
            $mpAccount = $company->mercadopagoAccount;

            if ($mpAccount) {
                $mpAccount->update([
                    'is_active' => false,
                ]);

                Log::info('MercadoPago account disconnected', [
                    'company_id' => $company->id,
                    'mp_account_id' => $mpAccount->id,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Cuenta de MercadoPago desconectada',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No hay cuenta de MercadoPago configurada',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error disconnecting MercadoPago account', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al desconectar la cuenta',
            ], 500);
        }
    }

    /**
     * Test MercadoPago connection.
     */
    public function test()
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes una empresa asociada',
            ], 400);
        }

        $mpAccount = $company->mercadopagoAccount;

        if (!$mpAccount || !$mpAccount->isConnected()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay cuenta de MercadoPago configurada',
            ], 404);
        }

        try {
            // Test connection by making a simple API call
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $mpAccount->access_token,
                'Content-Type' => 'application/json',
            ])->get('https://api.mercadopago.com/users/me');

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Conexión exitosa con MercadoPago',
                    'user_data' => $response->json(),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al conectar con MercadoPago: ' . $response->body(),
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error testing MercadoPago connection', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al probar la conexión: ' . $e->getMessage(),
            ], 500);
        }
    }
}
