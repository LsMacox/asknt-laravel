<?php

namespace App\Models\Services\Auth;

use App\Models\BaseModel;
use Illuminate\Auth\Authenticatable as IlluminateAuthenticatable;
//use Illuminate\Auth\MustVerifyEmail;
//use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
//use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Laravel\Sanctum\HasApiTokens;


/**
 * Class Authenticatable
 * @package App\Models\Services\Auth
 */
class Authenticatable extends BaseModel implements
    AuthenticatableContract,
    AuthorizableContract
{
    use HasApiTokens, IlluminateAuthenticatable, Authorizable;
}
