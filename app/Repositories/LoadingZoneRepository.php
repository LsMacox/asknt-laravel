<?php


namespace App\Repositories;

use App\Models\LoadingZone as Model;

class LoadingZoneRepository extends CoreRepository
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
                    ->where('name', 'ilike', "%$search%");
            });
    }

}
