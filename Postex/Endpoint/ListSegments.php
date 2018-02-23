<?php
namespace Postex\Endpoint;

use Postex\Base;
use Postex\Http\Client;
use Postex\Http\Response;

/**
 * Class ListSegments
 * @package Postex\Endpoint
 */
class ListSegments extends Base
{
    /**
     * @param     $listUid
     * @param int $page
     * @param int $perPage
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function getSegments($listUid, $page = 1, $perPage = 10)
    {
        $client = new Client(array(
            'method'      => Client::METHOD_GET,
            'url'         => $this->config->getApiUrl(sprintf('lists/%s/segments', $listUid)),
            'paramsGet'   => array(
                'page'     => (int)$page,
                'per_page' => (int)$perPage
            ),
        ));

        return $response = $client->request();
    }
}