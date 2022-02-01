<?php

namespace App\Services\Wialon;

use Wialon;

class WialonResource
{

    /**
     * @param int $flags
     * @param string $propName
     * @return mixed
     */
    public function firstResource (int $flags = 1025, string $propName = 'notifications') {
        return $this->searchResources($flags, $propName)
            ->map(function ($resource) {
                return $resource->first();
            });
    }

    /**
     * @param int $flags
     * @param string $propName
     * @return mixed
     */
    public function searchResources (int $flags = 1025, string $propName = 'notifications') {
        $params = json_encode(array(
            'spec' => [
                'itemsType' => 'avl_resource',
                'propName' => $propName,
                'propValueMask' => '*',
                'sortType' => $propName,
                'propType' => '',
                'or_logic' => 0
            ],
            'force' => 0,
            'flags' => $flags,
            'from' => 0,
            'to' => 0,
        ));

        return \Wialon::core_search_items($params)->map(function ($resource) {
            return collect($resource['items']);
        });
    }

    /**
     * @return mixed
     */
    public function getObjectsWithRegPlate () {
        return $this->searchObjects(8388609, '')->map(function ($object) {
            return $object->map(function ($item) {
                $item->registration_plate = \Str::lower(
                    optional(collect($item->pflds)
                        ->where('n', 'registration_plate')
                        ->first())
                    ->v
                );
                return $item;
            });
        });
    }

    /**
     * @param int $flags
     * @param string $propName
     * @return mixed
     */
    public function searchObjects (int $flags = 8388609, string $propName = '') {
        $params = json_encode(array(
            'spec' => [
                'itemsType' => 'avl_unit',
                'propName' => $propName,
                'propValueMask' => '*',
                'sortType' => $propName,
                'propType' => '',
                'or_logic' => 0
            ],
            'force' => 0,
            'flags' => $flags,
            'from' => 0,
            'to' => 0,
        ));

        return \Wialon::core_search_items($params)->map(function ($object) {
            return !is_string($object) ? collect($object['items']) : collect();
        });
    }

}
