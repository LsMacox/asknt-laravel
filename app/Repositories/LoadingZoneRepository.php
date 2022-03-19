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
                    ->where('name', 'ilike', "%$search%")
                    ->orWhere('id_sap', 'ilike', "%$search%")
                    ->orWhere('id_1c', 'ilike', "%$search%")
                    ->orWhere('lng', 'ilike', "%$search%")
                    ->orWhere('lat', 'ilike', "%$search%")
                    ->orWhere('radius', 'ilike', "%$search%");
            });
    }

    /**
     * @param string $idSap
     * @param string $id1c
     * @return mixed
     */
    public function builderByIdSapOr1c ($idSap, $id1c) {
        return $this->startConditions()
            ->where('id_sap', $idSap)
            ->orWhere('id_1c', $id1c);
    }

}
