<?php


namespace App\Repositories;

use App\Models\User as Model;

class UserRepository extends CoreRepository
{
    /**
     * @return string
     */
    protected function getModelClass() {
        return Model::class;
    }

    /**
     * @param string $login
     * @return mixed
     */
    public function findByLogin (string $login) {
        return $this->startConditions()
            ->where('email', $login)
            ->orWhere('name', $login)
            ->first();
    }

}
