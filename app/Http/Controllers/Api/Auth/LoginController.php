<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Repositories\UserRepository;
use Adldap\AdldapInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Auth;

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
     * @param UserRepository $userRepo
     * @return mixed
     * @throws ValidationException
     */
    public function token(Request $request, UserRepository $userRepo) {
        $request->validate([
            'login' => 'required|string|min:2|max:255',
            'password' => 'required|string|min:2|max:255',
            'device_name' => 'required|string',
        ]);

        $user = $userRepo->findByLogin($request->login);
        $userRole = $user->getRoles()->first();

        if (! $user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => [__('auth.failed')],
            ]);
        }

        $user->tokens()->delete();

        return $user->createToken($request->device_name, ['level:'.$userRole->level])->plainTextToken;
    }

}
