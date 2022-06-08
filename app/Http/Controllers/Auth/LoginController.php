<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function getLogin(Request $request)
    {
        $request->session()->put("state", $state = Str::random(40));
        $query = http_build_query([
            "client_id"     => config("auth.client_id"),
            "redirect_uri"  => config("auth.callback"),
            "response_type" => "code",
            "scope"         => config("auth.scopes"),
            "state"         => $state,
        ]);
        return redirect(config("auth.sso_host") . "/oauth/authorize?" . $query);
    }
    public function getCallback(Request $request)
    {
        $state = $request->session()->pull("state");

        throw_unless(strlen($state) > 0 && $state == $request->state, InvalidArgumentException::class);

        $response = Http::asForm()->post(
            config("auth.sso_host") . "/oauth/token",
            [
                "grant_type"    => "authorization_code",
                "client_id"     => config("auth.client_id"),
                "client_secret" => config("auth.client_secret"),
                "redirect_uri"  => config("auth.callback"),
                "code"          => $request->code,
            ]
        );
        $request->session()->put($response->json());
        return redirect(route("connect"));
    }
    public function connectUser(Request $request)
    {
        $access_token = $request->session()->get("access_token");
        $response     = Http::withHeaders([
            "Accept"        => "application/json",
            "Authorization" => "Bearer " . $access_token,
        ])->get(config("auth.sso_host") . "/api/user");
        $userArray = $response->json();
        try {
            $email = $userArray['email'];
        } catch (\Throwable $th) {
            return redirect("login")->withError("Failed to get login information! Try again.");
        }
        $user = User::where("email", $email)->first();
        if (!$user) {
            $user                    = new User;
            $user->name              = $userArray['name'];
            $user->email             = $userArray['email'];
            $user->email_verified_at = $userArray['email_verified_at'];
            $user->save();
        }
        Auth::login($user);
        return redirect(route("home"));
    }

    protected function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect(config('auth.sso_host') . '/home');
    }
}
