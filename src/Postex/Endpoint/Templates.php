<?php
namespace Postex\Endpoint;

use Postex\Base;
use Postex\Http\Client;
use Postex\Http\Response;

/**
 * Class Templates
 * @package Postex\Endpoint
 */
class Templates extends Base
{
    /**
     * @param int $page
     * @param int $perPage
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function getTemplates($page = 1, $perPage = 10)
    {
        $client = new Client(array(
            'method'      => Client::METHOD_GET,
            'url'         => $this->config->getApiUrl('templates'),
            'paramsGet'   => array(
                'page'     => (int)$page,
                'per_page' => (int)$perPage
            ),
        ));

        return $response = $client->request();
    }

    /**
     * @param $templateUid
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function getTemplate($templateUid)
    {
        $client = new Client(array(
            'method'      => Client::METHOD_GET,
            'url'         => $this->config->getApiUrl(sprintf('templates/%s', (string)$templateUid)),
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
        if (isset($data['content'])) {
            $data['content'] = base64_encode($data['content']);
        }

        if (isset($data['archive'])) {
            $data['archive'] = base64_encode($data['archive']);
        }

        $client = new Client(array(
            'method'     => Client::METHOD_POST,
            'url'        => $this->config->getApiUrl('templates'),
            'paramsPost' => array(
                'template' => $data
            ),
        ));

        return $response = $client->request();
    }

    /**
     * @param       $templateUid
     * @param array $data
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function update($templateUid, array $data)
    {
        if (isset($data['content'])) {
            $data['content'] = base64_encode($data['content']);
        }

        if (isset($data['archive'])) {
            $data['archive'] = base64_encode($data['archive']);
        }

        $client = new Client(array(
            'method'    => Client::METHOD_PUT,
            'url'       => $this->config->getApiUrl(sprintf('templates/%s', $templateUid)),
            'paramsPut' => array(
                'template' => $data
            ),
        ));

        return $response = $client->request();
    }

    /**
     * @param $templateUid
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function delete($templateUid)
    {
        $client = new Client(array(
            'method' => Client::METHOD_DELETE,
            'url'    => $this->config->getApiUrl(sprintf('templates/%s', $templateUid)),
        ));

        return $response = $client->request();
    }
}