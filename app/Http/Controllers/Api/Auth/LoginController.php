<?php

namespace App\Http\Controllers\Api\Auth;

use Adldap\Auth\BindException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
    public function token(Request $request) {
        $request->validate([
            'login' => 'required|string|min:2|max:255',
            'password' => 'required|string|min:2|max:255',
            'device_name' => 'required|string',
        ]);

        if (Auth::check()) {
            return Auth::user()->currentAccessToken();
        }

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

        try {
            $adUser = $this->ldap->search()->findByGuid($user->objectguid);

            if ($adUser) {
                $dbRoles = config('roles.models.role')::all();
                $adUserGroups = $adUser->getGroups()->filter(function ($group) use ($dbRoles) {
                    return $dbRoles->contains('name', $group->cn[0]);
                });

                if ($adUserGroups->isEmpty()) {
                    throw ValidationException::withMessages([
                        'login' => [__('auth.role')],
                    ]);
                }

                $role = $dbRoles->where('name', $adUserGroups->first()->cn[0])->first();
                $user->attachRole($role);
            } else {
                $role = $user->getRoles()->sortByDesc('level')->first();
            }
        } catch (BindException $exception) {
            $role = $user->getRoles()->sortByDesc('level')->first();
        }

        return response()->json(
            $user->createToken($request->device_name, ['level:'.$role->level])->plainTextToken
        );
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
