<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * CATATAN: Ini file yang di-generate `php artisan breeze:install blade`.
     * Cari file yang sama persis di project kamu (path sama), lalu TIMPA/REPLACE
     * pakai isi file ini. Bedanya cuma 2 baris yang ditandain di bawah:
     * 1. 'role' => 'owner' saat create user
     * 2. Redirect ke halaman create toko, bukan ke '/dashboard' langsung
     *    (soalnya Owner baru belum punya toko sama sekali)
     */
    public function create(): \Illuminate\View\View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'owner', // <-- SATU-SATUNYA PERUBAHAN PENTING: user yang daftar sendiri = Owner
        ]);

        event(new Registered($user));

        Auth::login($user);

        // <-- PERUBAHAN KE-2: arahkan ke halaman bikin toko pertama, bukan '/dashboard'
        return redirect()->route('owner.stores.create')
            ->with('status', 'Akun berhasil dibuat! Yuk bikin toko pertama kamu.');
    }
}
