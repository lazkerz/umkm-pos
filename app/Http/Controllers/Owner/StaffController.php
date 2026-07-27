<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index(Store $store)
    {
        $staff = $store->staff()->latest()->get();

        return view('owner.staff.index', compact('store', 'staff'));
    }

    public function create(Store $store)
    {
        return view('owner.staff.create', compact('store'));
    }

    public function store(Request $request, Store $store)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        User::create([
            'store_id' => $store->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => 'staff',
        ]);

        return redirect()
            ->route('owner.stores.staff.index', $store)
            ->with('success', 'Staff berhasil ditambahkan.');
    }

    public function update(Request $request, Store $store, User $staffMember)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ]);

        $staffMember->update($validated);

        return redirect()
            ->route('owner.stores.staff.index', $store)
            ->with('success', 'Data staff berhasil diupdate.');
    }

    public function destroy(Store $store, User $staffMember)
    {
        $staffMember->delete();

        return redirect()
            ->route('owner.stores.staff.index', $store)
            ->with('success', 'Staff berhasil dihapus.');
    }
}
