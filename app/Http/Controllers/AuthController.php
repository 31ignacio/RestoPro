<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller {

    public function showLogin() {
        $users = User::with('role')
            ->where('actif', true)
            ->orderBy('name')
            ->get(['id', 'name', 'role_id']);

        return view('auth.login', compact('users'));
    }

    public function login(Request $request) {
        $credentials = $request->validate([
            'user_id'  => 'required|integer|exists:users,id',
            'password' => 'required',
        ]);

        $user = User::with('role')->where('actif', true)->find($credentials['user_id']);

        if ($user && Hash::check($credentials['password'], $user->password)) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['login' => 'Nom ou mot de passe incorrect.'])
            ->withInput($request->only('user_id'));
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
