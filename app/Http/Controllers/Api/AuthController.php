<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Physiotherapist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        $user = Auth::user();

        // ✅ Cek jika fisio, gunakan is_verified & is_active (SESUI DENGAN ADMIN)
        if ($user->role === User::ROLE_PHYSIO) {
            $physio = $user->physiotherapist;

            if (!$physio) {
                Auth::logout();

                return response()->json([
                    'success' => false,
                    'message' => 'Profil fisioterapis tidak ditemukan. Hubungi admin.',
                ], 404);
            }

            if (!$physio->is_verified || !$physio->is_active) {
                Auth::logout();

                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda sedang dalam proses verifikasi atau dinonaktifkan. Hubungi admin untuk info lebih lanjut.',
                ], 403);
            }
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);
    }

    /**
     * Register user baru (parent)
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
            'role'       => User::ROLE_PARENT,
            'is_premium' => false,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil',
            'token'   => $token,
            'user'    => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'role'       => $user->role,
                'is_premium' => $user->is_premium,
            ],
        ], 201);
    }

    /**
     * Register Physio (API /register/physio)
     */
    public function registerPhysio(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|min:6|confirmed',
            'phone'        => 'required|string|max:20',
            'clinic_name'  => 'required|string|max:255',
            'city'         => 'required|string|max:100',
            'specialty'    => 'required|string|max:255',
            'certificate'  => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if (!$request->hasFile('certificate')) {
            return response()->json([
                'success' => false,
                'message' => 'File sertifikat tidak ditemukan.',
            ], 422);
        }

        $certificatePath = $request->file('certificate')->store('certificates', 'public');

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
            'role'     => User::ROLE_PHYSIO,
        ]);

        Physiotherapist::create([
            'user_id'                   => $user->id,
            'name'                      => $data['name'],
            'email'                     => $data['email'],
            'phone'                     => $data['phone'],
            'clinic_name'               => $data['clinic_name'],
            'city'                      => $data['city'],
            'specialty'                 => $data['specialty'],
            'certificate_path'          => $certificatePath,
            'status'                    => 'pending', // opsional jika masih dipakai
            'is_accepting_consultations'=> false,
            'is_verified'               => false,
            'is_active'                 => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran berhasil. Akun Anda sedang diverifikasi oleh admin.',
        ], 201);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ], 200);
    }

    /**
     * Get user yang sedang login
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}
