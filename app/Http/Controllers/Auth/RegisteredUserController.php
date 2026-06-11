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
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'citizenship_number' => 'required|string|max:50|unique:'.User::class,
            'phone_number' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
            'citizenship_front' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'citizenship_back' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $citizenshipFrontPath = $request->hasFile('citizenship_front')
            ? $request->file('citizenship_front')->store('citizenship', 'local')
            : null;

        $citizenshipBackPath = $request->hasFile('citizenship_back')
            ? $request->file('citizenship_back')->store('citizenship', 'local')
            : null;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'citizenship_number' => $request->citizenship_number,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'citizenship_front' => $citizenshipFrontPath,
            'citizenship_back' => $citizenshipBackPath,
        ]);

        $user->assignRole('Citizen');

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('citizen.dashboard'));
    }
}
