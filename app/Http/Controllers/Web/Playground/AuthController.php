<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Web\Playground;

use App\Repositories\UserRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private readonly UserRepo $users) {}

    public function showLogin(): View
    {
        return view('playground.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = $this->users->findByLogin($request->input('login'));

        if ($user === null || !Hash::check(value: $request->input('password'), hashedValue: $user->password)) {
            return back()->withErrors([
                'login' => 'These credentials do not match our records.',
            ])->onlyInput('login');
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended('/playground');
    }

    public function showRegister(): View
    {
        return view('playground.register');
    }

    public function checkUsername(Request $request): JsonResponse
    {
        $username = trim((string)$request->query(key: 'username', default: ''));

        if (strlen($username) < 3) {
            return response()->json(['available' => false, 'message' => 'At least 3 characters required']);
        }

        if (!preg_match('/^[a-z0-9_]+$/', $username)) {
            return response()->json(['available' => false, 'message' => 'Only lowercase letters, numbers and underscores']);
        }

        $taken = $this->users->usernameExists($username);

        return response()->json([
            'available' => !$taken,
            'message' => $taken ? 'Username is already taken' : 'Username is available',
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-z0-9_]+$/', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $this->users->create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/playground');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('playground.login');
    }
}
