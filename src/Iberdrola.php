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
    const URI_BASE = 'https://www.iberdroladistribucionelectrica.com/consumidores/rest/';
    const USER_AGENT = 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_4_1 like Mac OS X)' .
        'AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15G77';
    const URI_LOGIN = 'loginNew/login';
    const URI_READING = 'escenarioNew/obtenerMedicionOnline/12';
    const URI_CONTRACT_LIST = 'cto/listaCtos/';
    const URI_CONTRACT_SELECT = '/consumidores/rest/cto/seleccion/';
    const URI_ICP_STATUS = 'rearmeICP/consultarEstado';
    const URI_ICP_RECONNECT = 'rearmeICP/reconexion';
    const URI_LIMITS_CONSUMPTION_DATES = 'consumoNew/obtenerLimiteFechasConsumo';
    const URI_READINGS_OF_THE_DAY = 'consumoNew/obtenerDatosConsumo/fechaInicio/' .
        '{date}/colectivo/USU/frecuencia/horas/acumular/false';

    private $client;
    private $email;
    private $password;

    private $isLogged;

    private $timeZoneService;

    /**
     * Iberdrola constructor.
     * @param string $email
     * @param string $password
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
                    'tipoAPP' => 'IOS',
                    'esVersionNueva' => '1',
                    'idioma' => 'es',
                ],
            ],
            'cookies' => true,
        ];
        $this->client = new \GuzzleHttp\Client($config);
        $this->email = $email;
        $this->password = $password;
        $this->isLogged = false;
        $this->timeZoneService = new TimeZoneService();
    }

    /**
     * Login to the API
     * @return bool
     */
    private function login(): bool
    {
        $options = [
            \GuzzleHttp\RequestOptions::JSON => [
                $this->email,
                $this->password,
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
        $response = $this->client->post(self::URI_LOGIN, $options);

        $this->isLogged = $response->getStatusCode() === 200 ? true : false;
        return $this->isLogged;
    }

    /**
     * Get the list of contracts
     * @return bool
     */
    public function getListContracts(): bool
    {
        if ($this->isLogged === false) {
            $this->login();
        }

        $response = $this->client->get(self::URI_CONTRACT_LIST);
        if ($response->getStatusCode() !== 200) {
            $this->isLogged = false;
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
     * @param string $id Contract ID
     * @return bool
     */
    public function selectContract(string $id): bool
    {
        if ($this->isLogged === false) {
            $this->login();
        }

        $uri = sprintf('%s%s', self::URI_CONTRACT_SELECT, $id);
        $response = $this->client->get($uri);
        if ($response->getStatusCode() !== 200) {
            $this->isLogged = false;
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
     * @return bool|mixed
     */
    public function getReading(): \stdClass
    {
        if ($this->isLogged === false) {
            $this->login();
        }

        $response = $this->client->get(self::URI_READING);
        if ($response->getStatusCode() !== 200) {
            $this->isLogged = false;
            return false;
        }

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Get the status of the ICP
     * @return bool
     */
    public function getIcpStatus(): bool
    {
        if ($this->isLogged === false) {
            $this->login();
        }

        $response = $this->client->post(self::URI_ICP_STATUS);
        if ($response->getStatusCode() !== 200) {
            $this->isLogged = false;
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
     * @return bool
     */
    public function reconnectIcp(): bool
    {
        if ($this->isLogged === false) {
            $this->login();
        }

        $response = $this->client->post(self::URI_ICP_RECONNECT);
        if ($response->getStatusCode() !== 200) {
            $this->isLogged = false;
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

    /**
     * Get Limits Consumption Dates
     * @return array|bool
     */
    public function getLimitsConsumptionDates(): array
    {
        if ($this->isLogged === false) {
            $this->login();
        }

        $response = $this->client->get(self::URI_LIMITS_CONSUMPTION_DATES);
        if ($response->getStatusCode() !== 200) {
            $this->isLogged = false;
            return false;
        }

        $data = json_decode($response->getBody()->getContents());
        $result = [
            'min' => \DateTime::createFromFormat('d-m-YH:i:s', $data->fechaMinima),
            'max' => \DateTime::createFromFormat('d-m-YH:i:s', $data->fechaMaxima),
        ];

        return $result;
    }

    /**
     * Get readings of the day
     * @param \DateTime $day Day from which the historical
     * @return array|bool
     * @throws \Exception
     */
    public function getReadingsOfTheDay(\DateTime $day): array
    {
        if ($this->isLogged === false) {
            $this->login();
        }

        $url = str_replace(
            '{date}',
            $day->format('d-m-YH:i:s'),
            self::URI_READINGS_OF_THE_DAY
        );

        $response = $this->client->get($url);
        if ($response->getStatusCode() !== 200) {
            $this->isLogged = false;
            return false;
        }

        return $this->normalizeMeasurements($response->getBody()->getContents());
    }

    /**
     * Normalize Measurements
     * @param string $json JSON returned
     * @return array|bool
     * @throws \Exception
     */
    private function normalizeMeasurements($json): array
    {
        $object = json_decode(utf8_encode($json));

        $date = $this->timeZoneService->assignTimeZone(
            \DateTime::createFromFormat('d-m-YH:i:s', $object->fechaPeriodo)
        );

        $measurements = [];
        foreach ($object->y->data[0] as $item) {
            $this->timeZoneService->changeTimeZone($date);
            $measurements[] = [
                'date' => $date->format(DATE_ATOM),
                'value' => $item ? doubleval($item->valor) : null,
            ];

            $date->modify('+1 hour');
        }

        return $measurements;
    }
}
