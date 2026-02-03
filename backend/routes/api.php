<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

Route::get("/user", function (Request $request) {
    return $request->user();
})->middleware("auth:sanctum");

Route::post("/login", function (Request $request) {
    $request->validate([
        "email" => "required|email",
        "password" => "required",
    ]);

    $user = User::where("email", $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json(["message" => "Invalid credentials"], 401);
    }

    $token = $user->createToken("test-token");

    return ["token" => $token->plainTextToken];
});

Route::get("/sanctum/test", function () {
    return ["message" => "Sanctum is installed!"];
});
