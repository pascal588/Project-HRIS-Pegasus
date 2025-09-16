<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

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

    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'foto' => ['required', 'image', 'max:5120']
        ]);
        
        $employee = $request->user()->employee;
        
        // Hapus foto lama jika ada
        if ($employee->foto) {
            // Hapus file dari storage public tanpa menambahkan prefix 'public/'
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

        return Redirect::route('profile.edit')->with('status', 'photo-updated');
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