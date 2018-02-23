<?php
namespace Postex\Endpoint;

use Postex\Base;
use Postex\Http\Client;
use Postex\Http\Response;

/**
 * Class ListFields
 * @package Postex\Endpoint
 */
class ListFields extends Base
{
    /**
     * @param $listUid
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function getFields($listUid)
    {
        $client = new Client(array(
            'method'      => Client::METHOD_GET,
            'url'         => $this->config->getApiUrl(sprintf('lists/%s/fields', $listUid)),
            'paramsGet'   => array(),
        ));

        return $response = $client->request();
    }
}