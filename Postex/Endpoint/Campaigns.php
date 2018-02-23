<?php
namespace Postex\Endpoint;

use Postex\Base;
use Postex\Http\Client;
use Postex\Http\Response;

/**
 * Class Campaigns
 * @package Postex\Endpoint
 */
class Campaigns extends Base
{
    /**
     * @param int $page
     * @param int $perPage
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function getCampaigns($page = 1, $perPage = 10)
    {
        $client = new Client(array(
            'method'      => Client::METHOD_GET,
            'url'         => $this->config->getApiUrl('campaigns'),
            'paramsGet'   => array(
                'page'     => (int)$page,
                'per_page' => (int)$perPage
            ),
        ));

        return $response = $client->request();
    }

    /**
     * @param $campaignUid
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function getCampaign($campaignUid)
    {
        $client = new Client(array(
            'method'      => Client::METHOD_GET,
            'url'         => $this->config->getApiUrl(sprintf('campaigns/%s', (string)$campaignUid)),
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
        if (isset($data['template']['content'])) {
            $data['template']['content'] = base64_encode($data['template']['content']);
        }

        if (isset($data['template']['archive'])) {
            $data['template']['archive'] = base64_encode($data['template']['archive']);
        }

        $client = new Client(array(
            'method'     => Client::METHOD_POST,
            'url'        => $this->config->getApiUrl('campaigns'),
            'paramsPost' => array(
                'campaign' => $data
            ),
        ));

        return $response = $client->request();
    }

    /**
     * @param       $campaignUid
     * @param array $data
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function update($campaignUid, array $data)
    {
        if (isset($data['template']['content'])) {
            $data['template']['content'] = base64_encode($data['template']['content']);
        }

        if (isset($data['template']['archive'])) {
            $data['template']['archive'] = base64_encode($data['template']['archive']);
        }

        $client = new Client(array(
            'method'    => Client::METHOD_PUT,
            'url'       => $this->config->getApiUrl(sprintf('campaigns/%s', $campaignUid)),
            'paramsPut' => array(
                'campaign' => $data
            ),
        ));

        return $response = $client->request();
    }

    /**
     * @param $campaignUid
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function delete($campaignUid)
    {
        $client = new Client(array(
            'method' => Client::METHOD_DELETE,
            'url'    => $this->config->getApiUrl(sprintf('campaigns/%s', $campaignUid)),
        ));

        return $response = $client->request();
    }
}