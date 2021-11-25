<?php


namespace App\Repositories;

use App\Models\Wialon\WialonConnection as Model;
use Illuminate\Http\Request;

class WialonConnectionRepository extends CoreRepository
{
    /**
     * @return string
     */
    protected function getModelClass() {
        return Model::class;
    }

    public function create(array $fields) {
        return $this->startConditions()->create($fields);
    }

    public function getByCredentials (
        string $carrier_code,
        string $host,
        string $token
    ) {
        $parseHost = parse_url($host);
        $rawHost = $parseHost['host'];

        if (isset($parseHost['port'])) {
            $rawHost .= ':' . $parseHost['port'];
        }

        return $this->startConditions()
            ->where('carrier_code', $carrier_code)
            ->where('host', 'like', '%'. $rawHost . '%')
            ->where('token', $token)->get();
    }
}
