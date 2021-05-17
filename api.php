<?php


namespace IntegrationBundle\Utils;

use GuzzleHttp\Client;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\JsonResponse;

class CdekUtils
{
    use LoggerAwareTrait;

    const BASE_URL = 'https://api.cdek.ru';
    const TIMEOUT = 60;

    const TARIFF_CODE = 136;
    const FROM_LOCATION_CODE = 274;
    const CURRENCY = 1;

    const WEIGHT = 1000;
    const LENGTH = 15;
    const WIDTH = 15;
    const HEIGHT = 15;

    /** @var string */
    private $cdekId;

    /** @var string */
    private $cdekSecret;

    /**
     * CdekUtils constructor.
     * @param string $cdekId
     * @param string $cdekSecret
     */
    public function __construct(string $cdekId, string $cdekSecret)
    {
        $this->cdekId = $cdekId;
        $this->cdekSecret = $cdekSecret;
    }

    /**
     * @return string
     */
    public function calculation()
    {
        $toLocation = 105064;
        $response = $this->request($toLocation);

        if (isset($response['errors']) && $response['errors'][0]['code'] = 'ERR_NOT_FOUND_RECCITY') {
            return 'Invalid index';
        }

        return $response;
    }

    /**
     * @param int $toLocation
     * @return JsonResponse
     */
    public function request(int $toLocation)
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->getToken(),
            'Content-Type' => 'application/json;charset=utf-8',
        ];
        $body = json_encode([
            'currency' => self::CURRENCY,
            'tariff_code' => self::TARIFF_CODE,
            'from_location' => [
                'code' => self::FROM_LOCATION_CODE,
            ],
            'to_location' => [
                'postal_code' => $toLocation,
            ],
            'packages' => [
                'weight' => self::WEIGHT,
                'length' => self::LENGTH,
                'width'  => self::WIDTH,
                'height' => self::HEIGHT,
            ],
        ]);
        $client = new Client([
            'base_uri' => self::BASE_URL,
            'timeout'  => self::TIMEOUT,
        ]);
        $response = $client->post('/v2/calculator/tariff', [
            "headers" => $headers,
            "body" => $body
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * @return string
     */
    public function getToken()
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
        $formData = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->cdekId,
            'client_secret' => $this->cdekSecret,
        ];
        $client = new Client([
            'base_uri' => self::BASE_URL,
            'timeout'  => self::TIMEOUT,
        ]);
        $response = $client->post('/v2/oauth/token', [
            "headers" => $headers,
            "form_params" => $formData
        ]);

        $data = json_decode($response->getBody(), true);

        return $data['access_token'];
    }
}