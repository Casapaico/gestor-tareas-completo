<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->is_admin) {
                abort(403, 'No tienes permisos de administrador.');
            }
            return $next($request);
        });
    }

    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return view('admin.users', compact('users'));
    }

    public function createUser()
    {
        return view('admin.create-user');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Password::min(8)],
            'is_admin' => ['boolean']
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'Debe ser un email válido.',
            'email.unique' => 'Este email ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => $request->has('is_admin'),
            'active' => true, // Los usuarios nuevos están activos por defecto
        ]);

        return redirect()->route('admin.users')->with('success', '✅ Usuario creado exitosamente.');
    }

    public function editUser(User $user)
    {
        return view('admin.edit-user', compact('user'));
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', Password::min(8)],
            'is_admin' => ['boolean']
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'is_admin' => $request->has('is_admin'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users')->with('success', '✏️ Usuario actualizado exitosamente.');
    }

    public function destroyUser(User $user)
    {
        // Prevenir que el admin se elimine a sí mismo
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')->with('error', '❌ No puedes eliminarte a ti mismo.');
        }

        // Si el usuario tiene tareas, no se puede eliminar
        if ($user->id === 1) { // ID 1 suele ser el super admin
            return redirect()->route('admin.users')->with('error', '❌ No se puede eliminar al usuario principal.');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', '🗑️ Usuario eliminado exitosamente.');
    }

    public function resetDevice(User $user)
    {
        $user->update(['user_agent' => null]);
        
        return redirect()->route('admin.users')->with('success', "🔄 Dispositivo del usuario {$user->name} reiniciado. Podrá iniciar sesión desde cualquier dispositivo.");
    }

    public function toggleActive(User $user)
    {
        // Prevenir que el admin se desactive a sí mismo
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')->with('error', '❌ No puedes desactivarte a ti mismo.');
        }

        $user->active = !$user->active;
        $user->save();

        $status = $user->active ? 'activado' : 'desactivado';
        $emoji = $user->active ? '✅' : '🚫';

        return redirect()->route('admin.users')->with('success', "{$emoji} Usuario {$user->name} {$status} exitosamente.");
    }
}