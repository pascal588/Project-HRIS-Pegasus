<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse; // Tambahkan ini

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user()->load('employee.roles.division'),
        ]);
    }

    public function updatePhoto(Request $request): JsonResponse // Ubah return type
    {
        $request->validate([
            'foto' => ['required', 'image', 'max:5120'] // 5MB
        ]);
        
        $employee = $request->user()->employee;
        
        try {
            // Hapus foto lama jika ada
            if ($employee->foto) {
                if (Storage::disk('public')->exists($employee->foto)) {
                    Storage::disk('public')->delete($employee->foto);
                }
            }
            
            // Simpan foto baru
            $path = $request->file('foto')->store('profile-photos', 'public');
            
            // Update database
            $employee->update([
                'foto' => $path
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Foto berhasil diupdate',
                'new_path' => $path
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Update user data
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Hapus foto profil jika ada
        if ($user->employee->foto) {
            if (Storage::disk('public')->exists($user->employee->foto)) {
                Storage::disk('public')->delete($user->employee->foto);
            }
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}