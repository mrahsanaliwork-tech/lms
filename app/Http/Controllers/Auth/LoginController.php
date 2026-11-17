<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        Auth::user()->update(['last_login_at' => now()]);

        return redirect()->intended($this->redirectBasedOnRole());
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function redirectBasedOnRole(): string
    {
        $user = Auth::user();

        return match (true) {
            $user->hasRole('SuperAdmin')          => route('admin.dashboard'),
            $user->hasRole('Admin')               => route('admin.dashboard'),
            $user->hasRole('Instructor')          => route('instructor.dashboard'),
            $user->hasRole('TeachingAssistant')   => route('instructor.dashboard'),
            $user->hasRole('OrganizationManager') => route('org.dashboard'),
            default                               => route('student.dashboard'),
        };
    }
}
