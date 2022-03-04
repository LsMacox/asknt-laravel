<?php


namespace App\Repositories;

use App\Models\RetailOutlet as Model;

class RetailOutletRepository extends CoreRepository
{
    /**
     * @return string
     */
    protected function getModelClass() {
        return Model::class;
    }

    /**
     * @param $search
     * @return \Illuminate\Contracts\Container\ContextualBindingBuilder
     */
    public function search ($search) {
        return $this->startConditions()
            ->when($search, function ($query, $search) {
                return $query
                    ->where('name', 'ilike', "%$search%")
                    ->orWhere('address', 'ilike', "%$search%")
                    ->orWhere('shipment_retail_outlet_id', 'ilike', "%$search%");
            });
    }


}
