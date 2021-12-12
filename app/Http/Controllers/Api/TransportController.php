<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App;

class TransportController extends Controller
{

    /**
     * Получение краткой информации о транспорте
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getBriefInfo (Request $request)
    {
        //
    }

    public function test () {
        $params = json_encode(array(
            'spec' => array(
                'itemsType' => 'avl_unit',
                'propName' => 'sys_name',
                'propValueMask' => '*',
                'sortType' => 'sys_name'
            ),
            'force' => 0,
            'flags' => 1,
            'from' => 0,
            'to' => 0
        ));

        $wObject = \Wialon::core_search_items($params);
        $wItems = collect($wObject)
                        ->map(function ($item) {
                            return collect(json_decode($item)->items);
                        });

        $kInWhichValue = $wItems->search(function ($item) {
            return $item->contains('nm', 'Schmitz ах235031');
        });

        $wUpdate = \Wialon::useOnlyHosts([$kInWhichValue])->core_search_items($params);

        dd($wUpdate);
    }

}
