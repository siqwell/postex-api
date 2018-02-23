<?php
namespace Postex\Endpoint;

use Postex\Base;
use Postex\Http\Client;
use Postex\Http\Response;

/**
 * Class Customers
 * @package Postex\Endpoint
 */
class Customers extends Base
{
    /**
     * @param array $data
     *
     * @return Response
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function create(array $data)
    {
        if (isset($data['customer']['password'])) {
            $data['customer']['confirm_password'] = $data['customer']['password'];
        }

        if (isset($data['customer']['email'])) {
            $data['customer']['confirm_email'] = $data['customer']['email'];
        }

        if (empty($data['customer']['timezone'])) {
            $data['customer']['timezone'] = 'UTC';
        }

        $client = new Client(array(
            'method'     => Client::METHOD_POST,
            'url'        => $this->config->getApiUrl('customers'),
            'paramsPost' => $data,
        ));

        return $response = $client->request();
    }
}