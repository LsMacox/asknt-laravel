<?php

namespace App\Services\Wialon;

use Illuminate\Support\Arr;

/**
 * Class Wialon
 * @package App\Services\Wialon
 */
class Wialon{
    private $only_hosts = [];
    private $sid = null;
    private $base_api_url = '';
    private $default_params = [];

    /**
     * Wialon constructor.
     * @param string $scheme
     * @param string $host
     * @param string $port
     * @param string $sid
     * @param array $extra_params
     */
    function __construct($scheme = 'http', $host = '', $port = '', $sid = '', $extra_params = array()) {
        $this->sid = '';
        $this->default_params = array_replace(array(), (array)$extra_params);
        if (!empty($host)) {
            $this->base_api_url = sprintf('%s://%s%s/wialon/ajax.html?', $scheme, $host, mb_strlen($port)>0?':'.$port:'');
        }
    }

    /**
     * @param $sid
     */
    function set_sid($sid){
        $this->sid = $sid;
    }

    /**
     * @return string
     */
    function get_sid(){
        return $this->sid;
    }

    /**
     * @param $extra_params
     */
    public function update_extra_params($extra_params){
        $this->default_params = array_replace($this->default_params, $extra_params);
    }

    /**
     * RemoteAPI request performer
     * action - RemoteAPI command name
     * args - JSON string with request parameters
     * @param $action
     * @param $args
     * @return bool|string
     */
    public function call($action, $args){

        $url = $this->base_api_url;

        if (stripos($action, 'unit_group') === 0) {
            $svc = $action;
            $svc[mb_strlen('unit_group')] = '/';
        } else {
            $svc = preg_replace('\'_\'', '/', $action, 1);
        }
        $params = array(
            'svc'=> $svc,
            'params'=> $args,
            'sid'=> $this->sid
        );
        $all_params = array_replace($this->default_params , $params);
        $str = '';
        foreach ($all_params as $k => $v) {
            if(mb_strlen($str)>0)
                $str .= '&';
            $str .= $k.'='.urlencode(is_object($v) || is_array($v)  ? json_encode($v) : $v);
        }

        /* cUrl magic */
        $ch = curl_init();
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $str
        );
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);

        if($result === false)
            $result = '{"error":-1,"message":'.curl_error($ch).'}';

        curl_close($ch);
        return $result;
    }

    /**
     * Вызов по умолочанию (если в конструктор данного класса ничего не указано)
     * @param $action
     * @param $args
     * @return array
     */
    public function callDefault ($action, $args) {
        $results = [];
        $connections = collect(config('wialon.connections.default'));

        if (!empty($this->only_hosts)) {
            $connections = $connections->filter(function ($item) {
                return in_array($item['host'], $this->only_hosts);
            });
        }

        foreach ($connections as $connection) {
            ['scheme' => $scheme,
                'host' => $host,
                'port' => $port,
                'token' => $token] = $connection;

            $wialon = new self($scheme, $host, $port);
            $loginResult = $wialon->login($token);


            if (isset($loginResult['error'])) {
                $results[$host] = WialonError::error($loginResult['error'], $loginResult['reason']);
            } else {
                $res = call_user_func_array(array($wialon, $action), [$args]);
                $results[$host] = $res;
            }
        }

        return $results;
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
     * Login
     * user - wialon username
     * password - password
     * return - server response
     * @param $token
     * @return mixed
     */
    public function login($token) {
        $data = array(
            'token' => urlencode($token),
        );

        $result = $this->token_login(json_encode($data));

        $json_result = json_decode($result, true);

        if(isset($json_result['eid'])) {
            $this->sid = $json_result['eid'];
        }

        return $json_result;
    }

    /**
     * Logout
     * return - server response
     * @return mixed
     */
    public function logout() {
        $result = $this->core_logout();
        $json_result = json_decode($result, true);
        if($json_result && $json_result['error']==0)
            $this->sid = '';
        return $result;
    }

    /**
     * Unknown methods handler
     * @param $name
     * @param $args
     * @return bool|string
     */
    public function __call($name, $args) {
        $arguments = count($args) === 0 ? '{}' : $args[0];
        if (!empty($this->base_api_url)) {
            return $this->call($name, $arguments);
        }
        return $this->callDefault($name, $arguments);
    }
}
