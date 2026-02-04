<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankAccountController extends Controller
{
    /**
     * Get the active restaurant ID from session (must belong to user's company).
     */
    protected function getActiveRestaurantId(): ?int
    {
        $id = session('active_restaurant_id');
        if (!$id) {
            return null;
        }
        $company = Auth::user()->company;
        if (
            !$company ||
            !$company->hasEcommerce() ||
            !$company->restaurants()->where('id', $id)->exists()
        ) {
            return null;
        }
        return (int) $id;
    }

    /**
     * List bank accounts for the active restaurant.
     */
    public function index(): JsonResponse
    {
        $restaurantId = $this->getActiveRestaurantId();
        if (!$restaurantId) {
            return response()->json(['message' => 'No hay restaurante seleccionado'], 400);
        }

        $accounts = BankAccount::where('restaurant_id', $restaurantId)
            ->orderBy('is_active', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($accounts);
    }

    /**
     * Store a new bank account for the active restaurant.
     */
    public function store(Request $request): JsonResponse
    {
        $restaurantId = $this->getActiveRestaurantId();
        if (!$restaurantId) {
            return response()->json(['message' => 'No hay restaurante seleccionado'], 400);
        }

        $validated = $request->validate([
            'bank_name' => 'required|string|max:100',
            'account_type' => 'required|in:checking,savings',
            'account_number' => 'required|string|max:50',
            'account_holder' => 'required|string|max:255',
            'currency' => 'nullable|string|size:3',
            'is_active' => 'nullable|boolean',
            'instructions' => 'nullable|string|max:1000',
        ]);

        $validated['restaurant_id'] = $restaurantId;
        $validated['currency'] = $validated['currency'] ?? 'UYU';
        $validated['is_active'] = $request->boolean('is_active', true);

        $account = BankAccount::create($validated);

        return response()->json($account, 201);
    }

    /**
     * Update a bank account.
     */
    public function update(Request $request, BankAccount $bank_account): JsonResponse
    {
        $company = Auth::user()->company;
        if (!$company || $bank_account->restaurant->company_id !== $company->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'bank_name' => 'sometimes|string|max:100',
            'account_type' => 'sometimes|in:checking,savings',
            'account_number' => 'sometimes|string|max:50',
            'account_holder' => 'sometimes|string|max:255',
            'currency' => 'nullable|string|size:3',
            'is_active' => 'nullable|boolean',
            'instructions' => 'nullable|string|max:1000',
        ]);

        $bank_account->update($validated);

        return response()->json($bank_account);
    }

    /**
     * Delete a bank account.
     */
    public function destroy(BankAccount $bank_account): JsonResponse
    {
        $company = Auth::user()->company;
        if (!$company || $bank_account->restaurant->company_id !== $company->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $bank_account->delete();

        return response()->json(['message' => 'Cuenta eliminada']);
    }
}
