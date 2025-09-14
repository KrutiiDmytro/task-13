<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;

class AdminProfileController extends Controller
{
    public function edit(): View
    {
        return view('admin.profile.edit', [
            'user' => auth()->user()
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($data);

        return redirect()
            ->route('admin.profile.edit')
            ->with('success', 'Профиль успешно обновлен!');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = auth()->user();
        
        $data = $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'Текущий пароль неверный.'
            ]);
        }

        $user->update([
            'password' => Hash::make($data['password'])
        ]);

        return redirect()
            ->route('admin.profile.edit')
            ->with('password_success', 'Пароль успешно изменен!');
    }
}