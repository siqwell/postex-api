<?php
namespace Postex\Endpoint;

use Postex\Base;
use Postex\Http\Client;
use Postex\Http\Response;

/**
 * Class TransactionalEmails
 * @package Postex\Endpoint
 */
class TransactionalEmails extends Base
{
    /**
     * @param int $page
     * @param int $perPage
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function getEmails($page = 1, $perPage = 10)
    {
        $client = new Client(array(
            'method'    => Client::METHOD_GET,
            'url'       => $this->config->getApiUrl('transactional-emails'),
            'paramsGet' => array(
                'page'     => (int)$page,
                'per_page' => (int)$perPage
            ),
        ));

        return $response = $client->request();
    }

    /**
     * @param $emailUid
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function getEmail($emailUid)
    {
        $client = new Client(array(
            'method'    => Client::METHOD_GET,
            'url'       => $this->config->getApiUrl(sprintf('transactional-emails/%s', (string)$emailUid)),
            'paramsGet' => array(),
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
        if (!empty($data['body'])) {
            $data['body'] = base64_encode($data['body']);
        }

        if (!empty($data['plain_text'])) {
            $data['plain_text'] = base64_encode($data['plain_text']);
        }

        $client = new Client(array(
            'method'     => Client::METHOD_POST,
            'url'        => $this->config->getApiUrl('transactional-emails'),
            'paramsPost' => array(
                'email' => $data
            ),
        ));

        return $response = $client->request();
    }

    /**
     * @param $emailUid
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function delete($emailUid)
    {
        $client = new Client(array(
            'method' => Client::METHOD_DELETE,
            'url'    => $this->config->getApiUrl(sprintf('transactional-emails/%s', $emailUid)),
        ));

        return $response = $client->request();
    }
}