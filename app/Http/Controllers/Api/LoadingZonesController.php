<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoadingZone\CreateRequest;
use App\Http\Requests\Api\LoadingZone\UpdateRequest;
use App\Http\Resources\LoadingZoneResource;
use App\Models\LoadingZone;
use App\Repositories\LoadingZoneRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LoadingZonesController extends Controller
{

    private $loadingZoneRepository;

    /**
     * RetailOutletsController constructor.
     */
    public function __construct()
    {
        $this->loadingZoneRepository = app(LoadingZoneRepository::class);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request) {
        $request->validate([
            'offset' => 'required|integer',
            'limit' => 'required|integer',
            'sortBy' => 'required|string',
            'sortByDesc' => 'boolean',
            'search' => 'string|max:255'
        ]);

        $inp = function ($val) use ($request) {
            return $request->input($val);
        };

        $res = $this->loadingZoneRepository
                    ->search($inp('search'));

        $total = $res->count();
        $items = LoadingZoneResource::collection(
            $res->offset($inp('offset'))
                ->limit($inp('limit'))
                ->get()
                ->sortBy([
                    [$inp('sortBy'), $inp('sortByDesc') ? 'desc' : 'asc'],
                ])
                ->all()
        );

        return response()->json(
            compact( 'total', 'items'),
            200
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(CreateRequest $request)
    {
        $validated = $request->validated();

        $res = LoadingZone::create($validated);

        return response()->json(new LoadingZoneResource($res), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        $outlet = LoadingZone::find($id);
        $outlet->update($request->validated());

        return response()->json(new LoadingZoneResource($outlet), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        LoadingZone::find($id)->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }
}
