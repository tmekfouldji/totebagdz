<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, true)) {
            throw ValidationException::withMessages(['email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.']);
        }

        $request->session()->regenerate();

        return response()->json(['message' => 'تم تسجيل الدخول.', 'user' => $request->user()->only('id', 'name', 'email')]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'تم تسجيل الخروج.', 'csrf_token' => csrf_token()]);
    }
}
