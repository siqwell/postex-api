<?php
namespace Postex\Endpoint;

use Postex\Base;
use Postex\Http\Client;
use Postex\Http\Response;

/**
 * Class Countries
 * @package Postex\Endpoint
 */
class Countries extends Base
{
    /**
     * @param int $page
     * @param int $perPage
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function getCountries($page = 1, $perPage = 10)
    {
        $client = new Client(array(
            'method'      => Client::METHOD_GET,
            'url'         => $this->config->getApiUrl('countries'),
            'paramsGet'   => array(
                'page'     => (int)$page,
                'per_page' => (int)$perPage
            ),
        ));

        return $response = $client->request();
    }

    /**
     * @param     $countryId
     * @param int $page
     * @param int $perPage
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function getZones($countryId, $page = 1, $perPage = 10)
    {
        $client = new Client(array(
            'method'      => Client::METHOD_GET,
            'url'         => $this->config->getApiUrl(sprintf('countries/%d/zones', $countryId)),
            'paramsGet'   => array(
                'page'     => (int)$page,
                'per_page' => (int)$perPage
            ),
        ));

        return $response = $client->request();
    }
}