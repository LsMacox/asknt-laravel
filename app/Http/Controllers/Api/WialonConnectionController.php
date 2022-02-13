<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Filters\WialonConnectionFilter;
use App\Http\Resources\WialonConnectionResource;
use App\Models\Wialon\WialonConnection;
use App\Repositories\WialonConnectionRepository;
use App\Services\Wialon\Wialon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WialonConnectionController extends Controller
{

    private $mainRepository;

    /**
     * WialonConnectionController constructor.
     */
    public function __construct()
    {
        $this->mainRepository = app(WialonConnectionRepository::class);
    }

    /**
     * Получения всего списка полей таблицы с фильтрацией
     * @param Request $request
     * @param WialonConnectionFilter $wialonConnectionFilter
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function list(Request $request, WialonConnectionFilter $wialonConnectionFilter)
    {
        $filteredList = WialonConnection::filter($wialonConnectionFilter)->get();

        return WialonConnectionResource::collection($filteredList);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request)
    {
        $this->validateAll($request);

        $hostParse = parse_url($request->host);
        $wialon = new Wialon($hostParse['scheme'] ?? 'http', $hostParse['host'], $hostParse['port'] ?? '');
        $loginResult = json_decode($wialon->login($request->token), true);

        ['carrier_code' => $carrier_code,
            'host' => $host,
            'token' => $token
        ] = $request->all();

        $isConnect = !isset($loginResult['error']);
        $isExists = $this->mainRepository->getByCredentials(
            $carrier_code, $host, $token
        )->count() > 0;

        if ($isExists) {
            throw ValidationException::withMessages([
                'credentials' => ['Такое соединение уже существует!'],
            ]);
        }

        if ($isConnect) {
            $data = $this->mainRepository->create(
                $request->all(['name', 'carrier_code', 'host', 'token'])
            );
        }

        return response()->json([
            'is_connection' => !isset($loginResult['error']),
            'data' => $data ?? null,
        ]);
    }

    public function update(Request $request)
    {

    }

    public function delete(Request $request)
    {

    }

    /**
     * Валидация всех полей модели
     * @param Request $request
     * @return array|void
     */
    protected function validateAll (Request $request) {
        $request->validate([
            'name' => 'required|string|min:2|max:255',
            'carrier_code' => 'required|integer',
            'host' => 'required|string',
            'token' => 'required|string'
        ]);
    }
}
