<?php
namespace Postex\Endpoint;

use Postex\Base;
use Postex\Http\Client;
use Postex\Http\Response;

/**
 * Class ListSubscribers
 * @package Postex\Endpoint
 */
class ListSubscribers extends Base
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
    public function getSubscribers($listUid, $page = 1, $perPage = 10)
    {
        $client = new Client(array(
            'method'      => Client::METHOD_GET,
            'url'         => $this->config->getApiUrl(sprintf('lists/%s/subscribers', $listUid)),
            'paramsGet'   => array(
                'page'     => (int)$page,
                'per_page' => (int)$perPage,
            ),
        ));

        return $response = $client->request();
    }

    /**
     * @param $listUid
     * @param $subscriberUid
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function getSubscriber($listUid, $subscriberUid)
    {
        $client = new Client(array(
            'method'      => Client::METHOD_GET,
            'url'         => $this->config->getApiUrl(sprintf('lists/%s/subscribers/%s', (string)$listUid, (string)$subscriberUid)),
            'paramsGet'   => array(),
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
    public function create($listUid, array $data)
    {
        $client = new Client(array(
            'method'     => Client::METHOD_POST,
            'url'        => $this->config->getApiUrl(sprintf('lists/%s/subscribers', (string)$listUid)),
            'paramsPost' => $data,
        ));

        return $response = $client->request();
    }

    /**
     * @param       $listUid
     * @param       $subscriberUid
     * @param array $data
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function update($listUid, $subscriberUid, array $data)
    {
        $client = new Client(array(
            'method'    => Client::METHOD_PUT,
            'url'       => $this->config->getApiUrl(sprintf('lists/%s/subscribers/%s', (string)$listUid, (string)$subscriberUid)),
            'paramsPut' => $data,
        ));

        return $response = $client->request();
    }

    /**
     * @param $listUid
     * @param $subscriberUid
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function unsubscribe($listUid, $subscriberUid)
    {
        $client = new Client(array(
            'method'    => Client::METHOD_PUT,
            'url'       => $this->config->getApiUrl(sprintf('lists/%s/subscribers/%s/unsubscribe', (string)$listUid, (string)$subscriberUid)),
            'paramsPut' => array(),
        ));

        return $response = $client->request();
    }

    /**
     * @param $listUid
     * @param $emailAddress
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function unsubscribeByEmail($listUid, $emailAddress)
    {
        $response = $this->emailSearch($listUid, $emailAddress);

        // the request failed.
        if ($response->isCurlError) {
            return $response;
        }

        $bodyData = $response->body->itemAt('data');

        // subscriber not found.
        if ($response->isError && $response->httpCode == 404) {
            return $response;
        }

        if (empty($bodyData['subscriber_uid'])) {
            return $response;
        }

        return $this->unsubscribe($listUid, $bodyData['subscriber_uid']);
    }

    /**
     * @param $listUid
     * @param $subscriberUid
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function delete($listUid, $subscriberUid)
    {
        $client = new Client(array(
            'method'       => Client::METHOD_DELETE,
            'url'          => $this->config->getApiUrl(sprintf('lists/%s/subscribers/%s', (string)$listUid, (string)$subscriberUid)),
            'paramsDelete' => array(),
        ));

        return $response = $client->request();
    }

    /**
     * @param $listUid
     * @param $emailAddress
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function deleteByEmail($listUid, $emailAddress)
    {
        $response = $this->emailSearch($listUid, $emailAddress);
        $bodyData = $response->body->itemAt('data');

        if ($response->isError || empty($bodyData['subscriber_uid'])) {
            return $response;
        }

        return $this->delete($listUid, $bodyData['subscriber_uid']);
    }

    /**
     * @param $listUid
     * @param $emailAddress
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function emailSearch($listUid, $emailAddress)
    {
        $client = new Client(array(
            'method'    => Client::METHOD_GET,
            'url'       => $this->config->getApiUrl(sprintf('lists/%s/subscribers/search-by-email', (string)$listUid)),
            'paramsGet' => array('EMAIL' => (string)$emailAddress),
        ));

        return $response = $client->request();
    }

    /**
     * @param $listUid
     * @param $data
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function createUpdate($listUid, $data)
    {
        $emailAddress = !empty($data['EMAIL']) ? $data['EMAIL'] : null;
        $response     = $this->emailSearch($listUid, $emailAddress);

        // the request failed.
        if ($response->isCurlError) {
            return $response;
        }

        $bodyData = $response->body->itemAt('data');

        // subscriber not found.
        if ($response->isError && $response->httpCode == 404) {
            return $this->create($listUid, $data);
        }

        if (empty($bodyData['subscriber_uid'])) {
            return $response;
        }

        return $this->update($listUid, $bodyData['subscriber_uid'], $data);
    }
}