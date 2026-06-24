<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Mail;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Get the response for a successful password reset link.
     *
     * @param  string  $response
     * @return mixed
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'status' => __($response)
            ]);
        }
        return back()->with('status', __($response));
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param Request $request
     * @param $response
     * @return mixed
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {


        if ($request->expectsJson()) {
            return new JsonResponse([
                'message' =>  'The given data was invalid.',
                'errors' => [
                    'email' => __($response)
                ]
            ], 422);
        }
        return back()->withErrors(
            ['email' => __($response)]
        );
    }

    /**
     * Display the form to request a password reset link.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validateEmail(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
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
