<?php

namespace App\Services\Wialon;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Class Wialon
 * @package App\Services\Wialon
 */
class Wialon
{

    /**
     * Use only those default hosts that are specified in @var $only_hosts
     * @var array
     */
    private $only_hosts = [];

    /**
     * @var bool
     */
    protected $return_raw = false;

    /**
     * Wialon authentication token
     * @var array
     */
    private static $sids = [];

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

    private $host_id = 0;

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * @var string
     */
    public $debug_request = '';


    /**
     * Wialon constructor.
     * @param string $scheme
     * @param string $host
     * @param string $port
     * @param array $extra_params
     */
    function __construct (
        string $scheme = 'http',
        string $host = '',
        string $port = '',
        array $extra_params = []
    ) {
        $this->default_params = array_replace([], (array) $extra_params);

        if (!empty($host)) {
            $this->base_api_url =
                sprintf('%s://%s%s/wialon/ajax.html?', $scheme, $host, mb_strlen($port)>0?':'.$port:'');
        }
    }

    public function setHostId ($hostId) {
        $this->host_id = $hostId;
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
     * @param bool $return_raw
     * @return $this
     */
    public function returnRaw (bool $return_raw = true) {
        $this->return_raw = $return_raw;
        return $this;
    }

    /**
     * RemoteAPI request performer
     * action - RemoteAPI command name
     * args - JSON string with request parameters
     * @param $action
     * @param $args
     */
    public function call ($action, $args)
    {
        $url = $this->base_api_url;

        $args = (array) json_decode($args);

        if (stripos($action, 'unit_group') === 0) {
            $svc = $action;
            $svc[mb_strlen('unit_group')] = '/';
        } else {
            $svc = preg_replace('\'_\'', '/', $action, 1);
        }

        $params = [
            'svc' => $svc,
            'params' => $args,
            'sid' => static::$sids[$this->host_id ?? 0] ?? ''
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

        $this->debug_request = $url . urldecode($params_str);

        curl_close($ch);

        return $this->return_raw ? $result : collect(json_decode($result));
    }

    /**
     * Default call (if nothing is specified in the constructor of this class)
     * @param $action
     * @param $args
     */
    public function call_default ($action, $args)
    {
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
            $loginResult = [];

            if (!isset(static::$sids[$id]) || (isset(static::$sids[$id]) && empty(static::$sids[$id]))) {
                $loginResult = $wialon->login($token, $id);
            }

            if ($this->return_raw) {
                $wialon->returnRaw();
            }

            if (isset($loginResult['error'])) {
                $results[$id] = WialonError::error($loginResult['error'], $loginResult['reason']);
            } else {
                $wialon->setHostId($id);
                $res = call_user_func_array([$wialon, $action], Arr::wrap($args));
                if ($this->debug) {
                    $results[$id] = ['request' => $wialon->debug_request, 'response' => $res];
                } else {
                    $results[$id] = $res;
                }
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
     * @param $id
     * @return mixed
     */
    public function login ($token, $id = null) {
        $data = json_encode([
            'token' => urlencode($token),
        ]);

        if (empty($id)) {
            $id = count(static::$sids);
        }

        $result = $this->token_login($data);

        if (isset($result['eid'])) {
            static::$sids[$id] = $result['eid'];
        }

        return $result;
    }

    /**
     * Logout
     * return - server response
     * @return mixed
     */
    public function logout ($id = null) {
        $result = $this->core_logout();

        if (empty($id)) {
            $id = count(static::$sids);
        }

        if($result && 0 == $result['error']) {
            static::$sids[$id] = '';
        }

        return $result;
    }

    public function debug () {
        $this->debug = true;
        return $this;
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
