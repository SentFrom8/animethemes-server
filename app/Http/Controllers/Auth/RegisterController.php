<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\Models\Auth\InvitationStatus;
use App\Http\Controllers\Controller;
use App\Models\Auth\Invitation;
use App\Models\Auth\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

/**
 * Class RegisterController.
 *
 * This controller handles the registration of new users as well as their
 * validation and creation. By default, this controller uses a trait to
 * provide this functionality without requiring any additional code.
 */
class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Create a new controller instance.
     */
    #[DoNotDiscover]
    public function __construct()
    {
        // route discovery wants class strings
        $this->middleware(['guest', 'signed', 'has_open_invitation', 'without_trashed:invitation'], ['only' => ['showRegistrationForm', 'register']]);
    }

    /**
     * Where to redirect users after registration.
     *
     * @return string
     */
    #[DoNotDiscover]
    public function redirectPath(): string
    {
        return route('dashboard');
    }

    /**
     * Show the application registration form.
     *
     * @param  Invitation  $invitation
     * @return View|Factory
     */
    #[Route(method: 'get', fullUri: 'register/{invitation}', name: 'showRegistrationForm')]
    public function showRegistrationForm(Invitation $invitation): View|Factory
    {
        return view('auth.register', [
            'invitation' => $invitation,
        ]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  Request  $request
     * @param  Invitation  $invitation
     * @return JsonResponse|RedirectResponse|Redirector
     *
     * @throws ValidationException
     */
    #[Route(method: 'post', fullUri: 'register/{invitation}', name: 'register')]
    public function register(Request $request, Invitation $invitation): JsonResponse|RedirectResponse|Redirector
    {
        $data = array_merge($request->all(), [
            'name' => $invitation->name,
            'email' => $invitation->email,
        ]);

        $this->validator($data)->validate();

        event(new Registered($user = $this->create($data)));

        $invitation->status = InvitationStatus::CLOSED();
        $invitation->save();

        $this->guard()->login($user);

        if ($response = $this->registered($request, $user)) {
            return $response;
        }

        return $request->wantsJson()
                    ? new JsonResponse([], 201)
                    : redirect($this->redirectPath());
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    #[DoNotDiscover]
    protected function validator(array $data): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:192'],
            'email' => ['required', 'string', 'email', 'max:192', Rule::unique(User::TABLE)],
            'password' => Password::required(),
            'terms' => ['required'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    #[DoNotDiscover]
    protected function create(array $data): User
    {
        /** @var User $user */
        $user = User::query()->create([
            User::ATTRIBUTE_EMAIL => Arr::get($data, 'email'),
            User::ATTRIBUTE_EMAIL_VERIFIED_AT => Date::now(),
            User::ATTRIBUTE_NAME => Arr::get($data, 'name'),
            User::ATTRIBUTE_PASSWORD => Hash::make(Arr::get($data, 'password')),
        ]);

        return $user;
    }
}
