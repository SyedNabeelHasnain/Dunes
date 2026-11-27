<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('admin.profile.index', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if (\App\Models\User::count() <= 1) {
            return Redirect::route('profile.edit')->withErrors([
                'password' => 'Cannot delete the sole remaining administrator account.',
            ], 'userDeletion');
        }

        if ($user->normalizedRole() === \App\Models\User::ROLE_SUPER_ADMIN) {
            $otherSuperAdmins = \App\Models\User::where('role', \App\Models\User::ROLE_SUPER_ADMIN)
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();
            if ($otherSuperAdmins === 0) {
                return Redirect::route('profile.edit')->withErrors([
                    'password' => 'Cannot delete the last active Super Administrator account.',
                ], 'userDeletion');
            }
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
