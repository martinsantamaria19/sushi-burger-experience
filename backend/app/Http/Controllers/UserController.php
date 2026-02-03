<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Get all users from the same company
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return response()->json(['error' => 'Usuario no tiene una compañía asignada'], 400);
        }

        $users = $company->users()->orderBy('created_at', 'asc')->get();
        
        return response()->json($users);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return response()->json(['message' => 'Usuario no tiene una compañía asignada'], 400);
        }

        // Only owner can create users
        if (!$user->is_owner) {
            return response()->json(['message' => 'No tienes permisos para crear usuarios'], 403);
        }

        // Verificar límites del plan de suscripción para usuarios
        if (!$company->canCreateUser()) {
            $limit = $company->getUserLimit();
            $current = $company->getUsersCount();
            
            return response()->json([
                'message' => 'Has alcanzado el límite de usuarios permitidos en tu plan',
                'error_code' => 'SUBSCRIPTION_LIMIT_EXCEEDED',
                'limit_type' => 'users',
                'current' => $current,
                'limit' => $limit,
                'limit_reached' => true,
                'upgrade_url' => route('admin.subscription'),
            ], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'company_id' => $company->id,
            'is_owner' => false, // New users are not owners
            'email_verified_at' => now(), // Users created from admin panel are automatically verified
        ]);

        return response()->json($newUser, 201);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id): JsonResponse
    {
        $currentUser = Auth::user();
        $company = $currentUser->company;

        if (!$company) {
            return response()->json(['message' => 'Usuario no tiene una compañía asignada'], 400);
        }

        $user = $company->users()->find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // Only owner can update users, or user can update themselves (but not their role)
        if (!$currentUser->is_owner && $currentUser->id != $user->id) {
            return response()->json(['message' => 'No tienes permisos para actualizar este usuario'], 403);
        }

        // Validation rules
        $rules = [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($id)],
            'password' => ['sometimes', 'nullable', 'confirmed', Password::min(8)],
        ];

        $validated = $request->validate($rules);

        // Update user
        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }
        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }
        if (isset($validated['password']) && $validated['password']) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json($user);
    }

    /**
     * Remove the specified user
     */
    public function destroy($id): JsonResponse
    {
        $currentUser = Auth::user();
        $company = $currentUser->company;

        if (!$company) {
            return response()->json(['message' => 'Usuario no tiene una compañía asignada'], 400);
        }

        $user = $company->users()->find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // Cannot delete yourself
        if ($currentUser->id == $user->id) {
            return response()->json(['message' => 'No puedes eliminarte a ti mismo'], 400);
        }

        // Cannot delete owner
        if ($user->is_owner) {
            return response()->json(['message' => 'No se puede eliminar al propietario de la cuenta'], 400);
        }

        // Only owner can delete users
        if (!$currentUser->is_owner) {
            return response()->json(['message' => 'No tienes permisos para eliminar usuarios'], 403);
        }

        // Get owner for reassignment
        $owner = $company->users()->where('is_owner', true)->first();

        if (!$owner) {
            return response()->json(['message' => 'No se encontró propietario para reasignar datos'], 500);
        }

        // Reassign data to owner
        // This should reassign any data created by the user to the owner
        // For now, we'll just delete the user as the system uses company_id for most relationships
        
        // Note: Since most data is linked to company_id, not user_id, 
        // we mainly need to ensure the user is removed from the company
        $user->delete();

        return response()->json(['message' => 'Usuario eliminado exitosamente']);
    }
}

