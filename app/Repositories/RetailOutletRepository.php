<?php


namespace App\Repositories;

use App\Models\Outlet as Model;

class RetailOutletRepository extends CoreRepository
{
    /**
     * @return string
     */
    protected function getModelClass() {
        return Model::class;
    }

    public function search ($search) {
        return $this->startConditions()
            ->when($search, function ($query, $search) {
                return $query
                    ->where('name', 'ilike', "%$search%")
                    ->orWhere('address', 'ilike', "%$search%")
                    ->orWhere('code', 'ilike', "%$search%");
            });
    }

}
