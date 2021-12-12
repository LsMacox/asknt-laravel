<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RetailOutlets\CreateRequest;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RetailOutletsController extends Controller
{

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(CreateRequest $request)
    {
        Outlet::create($request->validated());

        return response('Торговая точка создана', 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CreateRequest $request, $id)
    {
        $outlet = Outlet::find($id);
        $outlet->update($request->validated());

        return response('Торговая точка обновлена', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Outlet::find($id)->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }
}
