<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/setup', function () {
    $credentials = [
        'email' => 'admin@admin.com',
        'password' => 'password'
    ];

    // Intenta autenticar al usuario
    if (!Auth::attempt($credentials)) {
        // Si la autenticación falla, crea un nuevo usuario
        $user = new \App\Models\User();
        $user->name = "Admin";
        $user->email = $credentials['email'];
        $user->password = Hash::make($credentials['password']);
        $user->save();

        // Intenta autenticar nuevamente después de crear el usuario
        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'Authentication failed'], 401);
        }
    }

    // Obtiene el usuario autenticado
    $user = Auth::user();

    // Verifica que el usuario sea válido antes de crear tokens
    if (!$user) {
        return response()->json(['error' => 'User not authenticated'], 401);
    }

    // Intenta crear tokens para el usuario autenticado
    $adminToken = $user->createToken('admin-token', ['create', 'update', 'delete']);
    $updateToken = $user->createToken('update-token', ['create', 'update']);
    $basicToken = $user->createToken('basic-token');

    // Retorna los tokens si se crean correctamente
    return [
        'admin' => $adminToken->plainTextToken,
        'update' => $updateToken->plainTextToken,
        'basic' => $basicToken->plainTextToken
    ];
});
