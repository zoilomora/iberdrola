<?php

namespace ZoiloMora\Iberdrola;

/**
 * Class Iberdrola
 *
 * @category Model
 * @package  ZoiloMora\Iberdrola
 * @author   Zoilo Mora <zoilo.mora@hotmail.com>
 * @license  https://opensource.org/licenses/MIT The MIT License
 * @link     https://github.com/zoilomora/iberdrola/blob/master/src/Iberdrola.php
 */
class Iberdrola
{
    const URI_BASE = 'https://www.iberdroladistribucionelectrica.com/consumidores' .
        '/rest/';
    const USER_AGENT = 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_4_1 like Mac OS X)' .
        'AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15G77';

    const URI_LOGIN = 'loginNew/login';
    const URI_READING = 'escenarioNew/obtenerMedicionOnline/12';
    const URI_CONTRACT_LIST = 'cto/listaCtos/';
    const URI_CONTRACT_SELECT = '/consumidores/rest/cto/seleccion/';
    const URI_ICP_STATUS = 'rearmeICP/consultarEstado';
    const URI_ICP_RECONNECT = 'rearmeICP/reconexion';

    private $_client;
    private $_email;
    private $_password;

    private $_isLogged;

    /**
     * Iberdrola constructor.
     *
     * @param string $email    Email address
     * @param string $password Password
     */
    public function __construct(string $email, string $password)
    {
        $config = [
            'base_uri' => self::URI_BASE,
            'defaults' => [
                'headers' => [
                    'User-Agent' => self::USER_AGENT,
                    'Content-Type' => 'application/json',
                    'movilAPP' => 'si',
                    'tipoAPP' => 'ANDROID',
                    'esVersionNueva' => '1',
                    'idioma' => 'es',
                ],
            ],
            'cookies' => true,
        ];
        $this->_client = new \GuzzleHttp\Client($config);
        $this->_email = $email;
        $this->_password = $password;
        $this->_isLogged = false;
    }

    /**
     * Login to the API
     *
     * @return bool
     */
    private function _login()
    {
        $options = [
            \GuzzleHttp\RequestOptions::JSON => [
                $this->_email,
                $this->_password,
                null,
                'iOS 11.4.1',
                'Movil',
                'Aplicación móvil V. 15',
                '0',
                '0',
                '0',
                null,
                'n',
            ],
        ];
        $response = $this->_client->post(self::URI_LOGIN, $options);

        $this->_isLogged = $response->getStatusCode() === 200 ? true : false;
        return $this->_isLogged;
    }

    /**
     * Get the list of contracts
     *
     * @return bool
     */
    public function getListContracts()
    {
        if ($this->_isLogged === false) {
            $this->_login();
        }

        $response = $this->_client->get(self::URI_CONTRACT_LIST);
        if ($response->getStatusCode() !== 200) {
            $this->_isLogged = false;
            return false;
        }

        $data = json_decode($response->getBody()->getContents());

        if (isset($data->success) && $data->success == true) {
            return $data->contratos;
        }

        return false;
    }

    /**
     * Select a contract for the following operations
     *
     * @param string $id Contract ID
     *
     * @return bool
     */
    public function selectContract(string $id)
    {
        if ($this->_isLogged === false) {
            $this->_login();
        }

        $uri = sprintf('%s%s', self::URI_CONTRACT_SELECT, $id);
        $response = $this->_client->get($uri);
        if ($response->getStatusCode() !== 200) {
            $this->_isLogged = false;
            return false;
        }

        $data = json_decode($response->getBody()->getContents());

        if (isset($data->success) && $data->success == true) {
            return true;
        }

        return false;
    }

    /**
     * Get meter reading
     *
     * @return bool|mixed
     */
    public function getReading()
    {
        if ($this->_isLogged === false) {
            $this->_login();
        }

        $response = $this->_client->get(self::URI_READING);
        if ($response->getStatusCode() !== 200) {
            $this->_isLogged = false;
            return false;
        }

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Get the status of the ICP
     *
     * @return bool
     */
    public function getIcpStatus()
    {
        if ($this->_isLogged === false) {
            $this->_login();
        }

        $response = $this->_client->post(self::URI_ICP_STATUS);
        if ($response->getStatusCode() !== 200) {
            $this->_isLogged = false;
            return false;
        }

        $result = json_decode($response->getBody()->getContents(), true);
        if (array_key_exists('icp', $result) && $result['icp'] === 'trueConectado') {
            return true;
        }

        return false;
    }

    /**
     * Reconnect ICP
     *
     * @return bool
     */
    public function reconnectIcp()
    {
        $response = $this->_client->post(self::URI_ICP_RECONNECT);
        if ($response->getStatusCode() !== 200) {
            $this->_isLogged = false;
            return false;
        }

        $data = json_decode($response->getBody()->getContents());

        if (isset($data->maximoRearme) && $data->maximoRearme === true) {
            return false;
        }

        if (isset($data->excepcion) && $data->excepcion === true) {
            return false;
        }

        if ($data->success === false) {
            return false;
        }

        return true;
    }
}
