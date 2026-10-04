<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Role};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')->latest()->get();
        $roles = Role::all();
        return view('admin.users.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:150',
            'email'    => 'required|email|unique:users,email',
            'role_id'  => 'required|exists:roles,id',
            'password' => 'required|string|min:12|confirmed',
        ]);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur créé.',
            'data'    => $user->load('role'),
        ]);
    }

    public function show(User $user)
    {
        return response()->json($user->load('role'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:150',
            'email'   => 'required|email|unique:users,email,'.$user->id,
            'role_id' => 'required|exists:roles,id',
            'actif'   => 'boolean',
        ]);

        $user->update($data);

        // Changer mot de passe si fourni
        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:12|confirmed']);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur mis à jour.',
            'data'    => $user->load('role'),
        ]);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas supprimer votre propre compte.',
            ], 422);
        }

        $user->delete();
        return response()->json(['success' => true, 'message' => 'Utilisateur supprimé.']);
    }

    public function toggleActif(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Action impossible.'], 422);
        }
        $user->update(['actif' => !$user->actif]);
        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour.',
            'actif'   => $user->actif,
        ]);
    }
}
