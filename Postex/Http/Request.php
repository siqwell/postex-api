<?php
namespace Postex\Http;

use Postex\Base;
use Postex\Json;
use Postex\Params;

/**
 * Class Request
 * @package Postex\Http
 */
class Request extends Base
{
    /**
     * @var Client the http client injected.
     */
    public $client;

    /**
     * @var Params the request params.
     */
    public $params;

    /**
     * Constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return mixed|Response
     * @throws \Exception
     */
    public function send()
    {
        foreach ($this->getEventHandlers(self::EVENT_BEFORE_SEND_REQUEST) as $callback) {
            call_user_func_array($callback, array($this));
        }

        $client     = $this->client;
        $requestUrl = rtrim($client->url, '/'); // no trailing slash
        $scheme     = parse_url($requestUrl, PHP_URL_SCHEME);

        $getParams = (array)$client->paramsGet->toArray();
        if (!empty($getParams)) {
            ksort($getParams, SORT_STRING);
            $queryString = http_build_query($getParams, '', '&');
            if (!empty($queryString)) {
                $requestUrl .= '?' . $queryString;
            }
        }

        $this->sign($requestUrl);

        if ($client->isPutMethod || $client->isDeleteMethod) {
            $client->headers->add('X-HTTP-Method-Override', strtoupper($client->method));
        }

        $ch = curl_init($requestUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $client->timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $client->timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Postex Client ' . Client::CLIENT_VERSION);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);

        if ($client->getResponseHeaders) {
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
        }

        if (!ini_get('safe_mode')) {
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            if (!ini_get('open_basedir')) {
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            }
        }

        if ($client->headers->count > 0) {
            $headers = array();
            foreach ($client->headers as $name => $value) {
                $headers[] = $name . ': ' . $value;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if ($scheme === 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        if ($client->isPostMethod || $client->isPutMethod || $client->isDeleteMethod) {

            $params = new Params($client->paramsPost);
            $params->mergeWith($client->paramsPut);
            $params->mergeWith($client->paramsDelete);

            if (!$client->isPostMethod) {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($client->method));
            }

            curl_setopt($ch, CURLOPT_POST, $params->count);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params->toArray(), '', '&'));
        }

        $body        = curl_exec($ch);
        $curlCode    = curl_errno($ch);
        $curlMessage = curl_error($ch);

        $curlInfo = curl_getinfo($ch);
        $params   = $this->params = new Params($curlInfo);

        if ($curlCode === 0 && $client->getResponseHeaders) {
            $headersSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headers     = explode("\n", substr($body, 0, $headersSize));
            foreach ($headers as $index => $header) {
                $header = trim($header);
                if (empty($header)) {
                    unset($headers[$index]);
                }
            }
            $body = substr($body, $headersSize);
            $params->add('headers', new Params($headers));
        }

        $decodedBody = array();
        if ($curlCode === 0 && !empty($body)) {
            $decodedBody = Json::decode($body, true);
            if (!is_array($decodedBody)) {
                $decodedBody = array();
            }
        }

        $params->add('curl_code', $curlCode);
        $params->add('curl_message', $curlMessage);
        $params->add('body', new Params($decodedBody));

        $response = new Response($this);
        $body     = $response->body;

        if (!$response->isSuccess && $body->itemAt('status') !== 'success' && !$body->contains('error')) {
            $response->body->add('status', 'error');
            $response->body->add('error', $response->message);
        }

        curl_close($ch);

        foreach ($this->getEventHandlers(self::EVENT_AFTER_SEND_REQUEST) as $callback) {
            $response = call_user_func_array($callback, array($this, $response));
        }

        return $response;
    }

    /**
     * @param $requestUrl
     *
     * @throws \Exception
     */
    protected function sign($requestUrl)
    {
        $client = $this->client;
        $config = $this->config;

        $publicKey  = $config->publicKey;
        $privateKey = $config->privateKey;
        $timestamp  = time();

        $specialHeaderParams = array(
            'X-MW-PUBLIC-KEY'  => $publicKey,
            'X-MW-TIMESTAMP'   => $timestamp,
            'X-MW-REMOTE-ADDR' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
        );

        foreach ($specialHeaderParams as $key => $value) {
            $client->headers->add($key, $value);
        }

        $params = new Params($specialHeaderParams);
        $params->mergeWith($client->paramsPost);
        $params->mergeWith($client->paramsPut);
        $params->mergeWith($client->paramsDelete);

        $params = $params->toArray();
        ksort($params, SORT_STRING);

        $separator       = $client->paramsGet->count > 0 && strpos($requestUrl, '?') !== false ? '&' : '?';
        $signatureString = strtoupper($client->method) . ' ' . $requestUrl . $separator . http_build_query($params, '', '&');
        $signature       = hash_hmac('sha1', $signatureString, $privateKey, false);

        $client->headers->add('X-MW-SIGNATURE', $signature);
    }
}