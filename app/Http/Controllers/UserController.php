<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display listing of users
     */
    public function index(Request $request)
    {
        $users = User::all();

        return inertia('Users/Index', [
            'users' => $users,
            'currentUser' => $request->user(),
        ]);
    }

    /**
     * Show form for creating new user
     */
    public function create(Request $request)
    {
        // Only admin can access
        if ($request->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return inertia('Users/Create');
    }

    /**
     * Show form for editing user
     */
    public function edit(Request $request, User $user)
    {
        // Only admin can access
        if ($request->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return inertia('Users/Edit', [
            'user' => $user,
        ]);
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        // Only admin can create users
        if ($request->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki izin untuk menambah user.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => ['required', Rule::in(['admin', 'petugas', 'syahbandar'])],
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return back()->with('success', 'User berhasil ditambahkan!');
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        // Only admin can update users
        if ($request->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki izin untuk mengubah user.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => ['required', Rule::in(['admin', 'petugas', 'syahbandar'])],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return back()->with('success', 'User berhasil diperbarui!');
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request, User $user)
    {
        // Only admin can update passwords
        if ($request->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki izin untuk mengubah password user.');
        }

        $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password user berhasil diperbarui!');
    }

    /**
     * Remove user
     */
    public function destroy(Request $request, User $user)
    {
        // Only admin can delete users
        if ($request->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki izin untuk menghapus user.');
        }

        // Prevent deleting yourself
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['error' => 'Anda tidak dapat menghapus akun sendiri.']);
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus!');
    }
}