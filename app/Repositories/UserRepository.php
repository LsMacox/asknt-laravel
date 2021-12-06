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

    public function findByLogin (string $login) {
        return $this->startConditions()
            ->where('email', $login)
            ->orWhere('name', $login)
            ->first();
    }

    public function getRoleById (int $id) {
        return $this->startConditions()
            ->find($id)
            ->getRoles()->first();
    }
}
