<?php


namespace App\Repositories;

use App\Models\ShipmentList\Shipment as Model;
use Illuminate\Database\Eloquent\Builder;

class ShipmentRepository extends CoreRepository
{
    /**
     * @return string
     */
    protected function getModelClass() {
        return Model::class;
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param string $sortBy
     * @param boolean $sortByDesc
     * @param Builder $builder
     * @return array
     */
    public function clientPaginate (
        int $offset,
        int $limit,
        string $sortBy,
        bool $sortByDesc,
        Builder $builder
    ) {
        if (empty($builder)) $builder = $this->startConditions();

        $total = $builder->count();
        $items = $builder
                ->offset($offset)
                ->limit($limit)
                ->orderBy(
                    $sortBy,
                    $sortByDesc ? 'desc' : 'asc',
                )
                ->get();

        return [$total, $items];
    }

    public function all() {
        return $this->startConditions()->all();
    }


}
