<?php

namespace App\Services\Wialon;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Class Wialon
 * @package App\Services\Wialon
 */
class Wialon {

    /**
     * Use only those default hosts that are specified in @var $only_hosts
     * @var array
     */
    private $only_hosts = [];

    /**
     * Wialon authentication token
     * @var string
     */
    private $sid = null;

    /**
     * Base url before wialon api
     * @var string
     */
    private $base_api_url = '';

    /**
     * Default parameters
     * @var array
     */
    private $default_params = [];


    /**
     * Wialon constructor.
     * @param string $scheme
     * @param string $host
     * @param string $port
     * @param string $sid
     * @param array $extra_params
     */
    function __construct (
        string $scheme = 'http',
        string $host = '',
        string $port = '',
        string $sid = '',
        array $extra_params = []
    ) {
        $this->default_params = array_replace(array(), (array)$extra_params);

        if (!empty($host)) {
            $this->base_api_url =
                sprintf('%s://%s%s/wialon/ajax.html?', $scheme, $host, mb_strlen($port)>0?':'.$port:'');
        }
    }

    /**
     * Sid setter
     * @param $sid
     */
    function set_sid ($sid): void {
        $this->sid = $sid;
    }

    /**
     * Sid getter
     * @return string
     */
    function get_sid (): string {
        return $this->sid;
    }

    /**
     * Update extra params
     * @param $extra_params
     * @return void
     */
    public function update_extra_params ($extra_params): void {
        $this->default_params = array_replace($this->default_params, $extra_params);
    }

    /**
     * @param array $hosts
     * @return $this
     */
    public function useOnlyHosts (array $hosts = []) {
        $this->only_hosts = $hosts;
        return $this;
    }

    /**
     * RemoteAPI request performer
     * action - RemoteAPI command name
     * args - JSON string with request parameters
     * @param $action
     * @param $args
     * @return bool|Collection
     */
    public function call ($action, $args): Collection {
        $url = $this->base_api_url;

        if (stripos($action, 'unit_group') === 0) {
            $svc = $action;
            $svc[mb_strlen('unit_group')] = '/';
        } else {
            $svc = preg_replace('\'_\'', '/', $action, 1);
        }

        $params = [
            'svc'=> $svc,
            'params'=> $args,
            'sid'=> $this->sid
        ];

        $all_params = array_replace($this->default_params, $params);
        $params_str = '';

        foreach ($all_params as $k => $v) {
            if (mb_strlen($params_str) > 0) {
                $params_str .= '&';
            }

            $params_str .= $k.'='.urlencode(is_object($v) || is_array($v)  ? json_encode($v) : $v);
        }

        $ch = curl_init();

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $params_str
        ];

        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);

        if (false === $result) {
            $result = '{"error":-1,"message":'.curl_error($ch).'}';
        }

        curl_close($ch);

        return collect(json_decode($result));
    }

    /**
     * Default call (if nothing is specified in the constructor of this class)
     * @param $action
     * @param $args
     * @return Collection
     */
    public function call_default ($action, $args): Collection {
        $results = [];
        $connections = collect(config('wialon.connections.default'));

        if (!empty($this->only_hosts)) {
            $connections = $connections->filter(function ($conn) {
                return in_array($conn['id'], $this->only_hosts);
            });
        }

        foreach ($connections as $connection) {
            ['id' => $id,
                'scheme' => $scheme,
                'host' => $host,
                'port' => $port,
                'token' => $token] = $connection;

            $wialon = new self($scheme, $host, $port);
            $loginResult = $wialon->login($token);

            if (isset($loginResult['error'])) {
                $results[$id] = WialonError::error($loginResult['error'], $loginResult['reason']);
            } else {
                $res = call_user_func_array([$wialon, $action], Arr::wrap($args));
                $results[$id] = $res;
            }
        }

        return collect($results);
    }

    /**
     * Login
     * user - wialon username
     * password - password
     * return - server response
     * @param $token
     * @return mixed
     */
    public function login ($token) {
        $data = json_encode([
            'token' => urlencode($token),
        ]);

        $result = $this->token_login($data);

        if (isset($result['eid'])) {
            $this->sid = $result['eid'];
        }

        return $result;
    }

    /**
     * Logout
     * return - server response
     * @return mixed
     */
    public function logout () {
        $result = $this->core_logout();

        if($result && 0 == $result['error']) {
            $this->sid = '';
        }

        return $result;
    }

    /**
     * Unknown methods handler
     * @param $name
     * @param $args
     * @return bool|array|Collection
     */
    public function __call (string $name, array $args) {
        $arguments = count($args) === 0 ? '{}' : $args[0];

        return !empty($this->base_api_url) ?
            $this->call($name, $arguments) :
            $this->call_default($name, $arguments);
    }
}
