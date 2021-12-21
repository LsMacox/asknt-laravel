<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use Adldap\AdldapInterface;
use Illuminate\Validation\ValidationException;
use Auth;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    /**
     * @var Adldap
     */
    protected $ldap;

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
                    now()->parse(
                        RateLimiter::
                        availableIn($this->throttleKey($request)) + 1
                    )->format('i') . ' минут.'
                ])],
            ]);
        }

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

        RateLimiter::clear('login:'.$request->ip());
        $user = Auth::user();

        $userRole = $userRepo->getRoleById($user->id);
        return $user->createToken($request->device_name, ['level:'.$userRole->level])->plainTextToken;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    protected function hasTooManyRequests(Request $request)
    {
        $maxLoginAttempts = 6 * 2;

        return RateLimiter::tooManyAttempts(
            $this->throttleKey($request), $maxLoginAttempts
        );
    }

    /**
     * Get the throttle key for the given request.
     *
     * @param Request $request
     * @return string
     */
    protected function throttleKey (Request $request): string {
        return 'login:'.$request->ip();
    }

}
