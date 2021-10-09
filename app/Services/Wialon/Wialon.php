<?php


namespace App\Services\Wialon;

/* Classes for working with Wialon RemoteApi using PHP
*
* License:
* The MIT License (MIT)
*
* Copyright:
* 2002-2015 Gurtam, http://gurtam.com
*/

/**
 * Wialon RemoteApi wrapper Class
 * Class Wialon
 */
class Wialon {

    use WialonError;

    /**
     * @var string
     */
    private $sids = [];
    /**
     * @var string
     */
    private $base_api_url = '';
    /**
     * @var array
     */
    private $default_params = array();

    /**
     * Wialon constructor.
     * @param string $scheme
     * @param string $host
     * @param string $port
     * @param array $sids
     * @param array $extra_params
     */
    public function __construct(
        string $scheme,
        string $host,
        string $port,
        array $sids,
        array $extra_params = [])
    {
        $scheme = $scheme ?? config('wialon.connection.scheme');
        $host = $host ?? config('wialon.connection.host');
        $port = $port ?? config('wialon.connection.port');
        $extra_params = $extra_params ?? config('wialon.extra_params');
        if (!empty($sids)) $this->sids = $sids;


        $this->default_params = array_replace(array(), (array)$extra_params);
        $this->base_api_url = sprintf('%s://%s%s/wialon/ajax.html?', $scheme, $host, mb_strlen($port)>0?':'.$port:'');
    }

    /**
     * update extra parameters
     * @param $extra_params
     */
    public function update_extra_params($extra_params) {
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
    public function call($action, $args) {
        $results = [];
        foreach ($this->sids as $sid) {
            $url = $this->base_api_url;

            if (stripos($action, 'unit_group') === 0) {
                $svc = $action;
                $svc[mb_strlen('unit_group')] = '/';
            } else {
                $svc = preg_replace('\'_\'', '/', $action, 1);
            }

            $params = array(
                'svc' => $svc,
                'params' => $args,
                'sid' => $sid
            );
            $all_params = array_replace($this->default_params, $params);
            $str = '';
            foreach ($all_params as $k => $v) {
                if (mb_strlen($str) > 0)
                    $str .= '&';
                $str .= $k . '=' . urlencode(is_object($v) || is_array($v) ? json_encode($v) : $v);
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

            if ($result === FALSE)
                $result = '{"error":-1,"message":' . curl_error($ch) . '}';

            curl_close($ch);
            $results[] = $result;
        }
        return $results;
    }

    /** Login
     * user - wialon username
     * password - password
     * @param $token
     * @return mixed - server response
     */
    public function login(array $tokens) {
        $errors = [];
        $results = [];
        foreach ($tokens as $token) {
            $data = array(
                'token' => urlencode($token),
            );

            $result = $this->token_login(json_encode($data));

            $json_result = json_decode($result, true);
            if (isset($json_result['eid'])) {
                $this->sids[] = $json_result['eid'];
            }

            isset($json_result['error'])
                ? $errors[] = static::error($json_result['error'])
                : $results[] = $result;
        }

        return [
            'results' => $results,
            'errors' => $errors,
        ];
    }


    /** Logout
     * @return mixed - server responce
     */
    public function logout() {
        foreach ($this->sids as $sid) {
            $result = $this->core_logout();
            $json_result = json_decode($result, true);
            if ($json_result && $json_result['error'] == 0)
                array_shift($this->sids);
        }
        if (!empty($this->sids)) {
            $this->sids = [];
        }
    }


    /**
     * Unknown methods handler
     * @param $name
     * @param $args
     * @return bool|string
     */
    public function __call($name, $args) {
        return $this->call($name, count($args) === 0 ? '{}' : $args[0]);
    }
}
