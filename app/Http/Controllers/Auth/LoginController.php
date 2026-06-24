<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers {
        attemptLogin as attemptLoginAtAuthenticatesUsers;
        credentials as credentialsFromTrait;
    }

    /**
     * Credenciales con email normalizado (trim) para evitar fallos por espacios.
     */
    protected function credentials(Request $request)
    {
        $creds = $this->credentialsFromTrait($request);
        if (isset($creds['email'])) {
            $creds['email'] = trim($creds['email']);
        }
        return $creds;
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * Returns field name to use at login.
     *
     * @return string
     */
    public function username()
    {
        return config('auth.providers.users.field', 'email');
    }

    /**
     * Attempt to log the user into the application.
     * Incluye fallback: búsqueda por email case-insensitive cuando el intento normal falla.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        $result = $this->username() === 'email'
            ? $this->attemptLoginAtAuthenticatesUsers($request)
            : $this->attempLoginUsingUsernameAsAnEmail($request);

        if ($result) {
            return true;
        }

        // Fallback: buscar usuario por email case-insensitive y verificar contraseña manualmente
        return $this->attemptLoginCaseInsensitive($request);
    }

    /**
     * Intento de login con búsqueda de email case-insensitive.
     * Útil cuando hay diferencias de mayúsculas entre lo que se escribe y lo guardado en BD.
     */
    protected function attemptLoginCaseInsensitive(Request $request)
    {
        $email = trim($request->input('email', ''));
        $password = $request->input('password', '');
        if (empty($email) || empty($password)) {
            return false;
        }

        $user = User::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();
        if (!$user || !Hash::check($password, $user->password)) {
            return false;
        }

        Auth::login($user, $request->boolean('remember'));
        return true;
    }

    /**
     * Attempt to log the user into application using username as an email.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function attempLoginUsingUsernameAsAnEmail(Request $request)
    {
        return $this->guard()->attempt(
            ['email' => $request->input('username'), 'password' => $request->input('password')],
            $request->has('remember')
        );
    }
    protected function validateLogin(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                $this->username() => 'required',
                'password' => 'required',
                'cf-turnstile-response' => config('services.turnstile.secret_key') ? 'required' : 'nullable',
            ],
            [
                'cf-turnstile-response.required' => 'Valide el captcha para continuar.',
            ]
        );

        if (config('services.turnstile.secret_key')) {
            $validator->after(function ($validator) use ($request) {
                if (!$this->passesTurnstile($request->input('cf-turnstile-response'), $request->ip())) {
                    $validator->errors()->add('cf-turnstile-response', 'No se pudo validar el captcha. Intente nuevamente.');
                }
            });
        }

        $validator->validate();
    }

    protected function passesTurnstile(?string $token, ?string $ip = null): bool
    {
        $secret = config('services.turnstile.secret_key');
        if (empty($secret) || empty($token)) {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(10)->post(
                'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                [
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $ip,
                ]
            );

            return $response->ok() && $response->json('success') === true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
