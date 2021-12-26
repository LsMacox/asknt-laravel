<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use Adldap\AdldapInterface;
use Illuminate\Validation\ValidationException;
use Auth;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * @var Adldap
     */
    protected $ldap;

    /**
     * @var int
     */
    protected $maxLoginAttempts = 5;

    /**
     * @var float|int
     */
    protected $decayMinutes = 60;

    /**
     * LoginController constructor.
     * @param AdldapInterface $ldap
     */
    public function __construct(AdldapInterface $ldap)
    {
        $this->ldap = $ldap;
    }

    /**
     * Получение токена для spa
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function token(Request $request, UserRepository $userRepo) {
        $request->validate([
            'login' => 'required|string|min:2|max:255',
            'password' => 'required|string|min:2|max:255',
            'device_name' => 'required|string',
        ]);

        if ($this->hasTooManyRequests($request)) {
            throw ValidationException::withMessages([
                'login' => [__('auth.throttle', ['time' =>
                    (int) now()->parse(
                        RateLimiter::
                        availableIn($this->throttleKey($request)) + 1
                    )->format('i') . ' минут.'
                ])],
            ]);
        }

        RateLimiter::hit($this->throttleKey($request), $this->decayMinutes * 60);


        $isAuth = Auth::attempt(
            [
                'email' => $request->login,
                'password' => $request->password,
            ]
        );

        if (!$isAuth) {
            throw ValidationException::withMessages([
                'login' => [__('auth.failed')],
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        $user = Auth::user();
        $role = $user->getRoles()->sortByDesc('level')->first();

        return $user->createToken($request->device_name, ['level:'.$role->level])->plainTextToken;
    }

    /**
     * Получение роли пользователя
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRole () {
        $role = Auth::user()->getRoles()->sortByDesc('level')->first();
        return response()->json($role);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    protected function hasTooManyRequests(Request $request)
    {
        return RateLimiter::tooManyAttempts(
            $this->throttleKey($request), $this->maxLoginAttempts
        );
    }

    /**
     * Get the throttle key for the given request.
     *
     * @param Request $request
     * @return string
     */
    public function throttleKey (Request $request): string {
        return sha1(\URL::current().'|'.$request->ip());
    }

}
