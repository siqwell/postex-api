<?php
namespace Postex\Endpoint;

use Postex\Base;
use Postex\Http\Client;
use Postex\Http\Response;

/**
 * Class Lists
 * @package Postex\Endpoint
 */
class Lists extends Base
{
    /**
     * @param int $page
     * @param int $perPage
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function getLists($page = 1, $perPage = 10)
    {
        $client = new Client(array(
            'method'      => Client::METHOD_GET,
            'url'         => $this->config->getApiUrl('lists'),
            'paramsGet'   => array(
                'page'     => (int)$page,
                'per_page' => (int)$perPage
            ),
        ));

        return $response = $client->request();
    }

    /**
     * @param $listUid
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function getList($listUid)
    {
        $client = new Client(array(
            'method'      => Client::METHOD_GET,
            'url'         => $this->config->getApiUrl(sprintf('lists/%s', (string)$listUid)),
            'paramsGet'   => array(),
        ));

        return $response = $client->request();
    }

    /**
     * @param array $data
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function create(array $data)
    {
        $client = new Client(array(
            'method'     => Client::METHOD_POST,
            'url'        => $this->config->getApiUrl('lists'),
            'paramsPost' => $data,
        ));

        return $response = $client->request();
    }

    /**
     * @param       $listUid
     * @param array $data
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function update($listUid, array $data)
    {
        $client = new Client(array(
            'method'    => Client::METHOD_PUT,
            'url'       => $this->config->getApiUrl(sprintf('lists/%s', $listUid)),
            'paramsPut' => $data,
        ));

        return $response = $client->request();
    }

    /**
     * @param $listUid
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function delete($listUid)
    {
        $client = new Client(array(
            'method' => Client::METHOD_DELETE,
            'url'    => $this->config->getApiUrl(sprintf('lists/%s', $listUid)),
        ));

        return $response = $client->request();
    }
}