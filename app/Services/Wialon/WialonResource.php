<?php

namespace App\Services\Wialon;

use Wialon;

class WialonResource
{

    /**
     * @var array $useOnlyHosts
     */
    private $useOnlyHosts = [];

    /**
     * @param array|integer|string $hosts
     * @return $this
     */
    public function useOnlyHosts ($hosts) {
        $hosts = \Arr::wrap($hosts);
        $this->useOnlyHosts = $hosts;
        return $this;
    }

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

        return \Wialon::useOnlyHosts($this->useOnlyHosts)->core_search_items($params)->map(function ($resource) {
            return !is_string($resource) && isset($resource['items']) ? collect($resource['items']) : collect();
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

        return \Wialon::useOnlyHosts($this->useOnlyHosts)->core_search_items($params)->map(function ($object) {
            return !is_string($object) && isset($object['items']) ? collect($object['items']) : collect();
        });
    }

    /**
     * @param int $hostId
     * @param int $resId
     * @return mixed
     */
    public function getReportTemplates (int $hostId = null, int $resId = null) {
        if (!$hostId || !$resId) {
            return $this->searchResources(8193, '');
        }

        return $this->searchResources(8193, '')[$hostId]->where('id', $resId)->first();
    }

    /**
     * @return mixed
     */
    public function getObjectsWithRegPlate () {
        return $this->searchObjects(8388609, '')->map(function ($object) {
            return $object->map(function ($item) {
                $item->registration_plate = (string) \Str::of(
                    optional(collect($item->pflds)
                        ->where('n', 'registration_plate')
                        ->first())
                        ->v
                )->upper()->trim()->replaceMatches('/\s+/', '');
                return $item;
            });
        });
    }

    public function getObjectByRegPlate ($objects, $plates) {
        $plates = \Arr::wrap($plates);
        $idx = $objects
            ->search(function ($item) use ($plates) {
                $check = false;
                foreach ($plates as $plate) {
                    $check = $this->equalObjectRegPlate($item, $plate);
                    if ($check) break;
                }
                return $check;
            });

        return $idx ? $objects[$idx] : $idx;
    }

    public function equalObjectRegPlate ($objectItem, $plate) {
        return isset($objectItem->registration_plate) &&
                !empty($objectItem->registration_plate) &&
                $this->prepareRegPlate($plate) === $this->prepareRegPlate($objectItem->registration_plate);
    }

    public function prepareRegPlate ($plate) {
        return (string) \Str::of($plate)
            ->upper()
            ->trim()
            ->replaceMatches('/\s+/', '');
    }
}
