<?php
namespace Postex\Http;

use Postex\Base;
use Postex\Params;

/**
 * Class Client
 * @package Postex\Http
 */
class Client extends Base
{
    /**
     * Marker for GET requests.
     */
    const METHOD_GET = 'GET';

    /**
     * Marker for POST requests.
     */
    const METHOD_POST = 'POST';

    /**
     * Marker for PUT requests.
     */
    const METHOD_PUT = 'PUT';

    /**
     * Marker for DELETE requests.
     */
    const METHOD_DELETE = 'DELETE';

    /**
     * Marker for the client version.
     */
    const CLIENT_VERSION = '1.0';

    /**
     * @var Params the GET params sent in the request.
     */
    public $paramsGet = array();

    /**
     * @var Params the POST params sent in the request.
     */
    public $paramsPost = array();

    /**
     * @var \Postex\Params the PUT params sent in the request.
     */
    public $paramsPut = array();

    /**
     * @var Params the DELETE params sent in the request.
     */
    public $paramsDelete = array();

    /**
     * @var \Postex\Params the headers sent in the request.
     */
    public $headers = array();

    /**
     * @var string the url where the remote calls will be made.
     */
    public $url;

    /**
     * @var int the default timeout for request.
     */
    public $timeout = 30;

    /**
     * @var bool whether to sign the request.
     */
    public $signRequest = true;

    /**
     * @var bool whether to get the response headers.
     */
    public $getResponseHeaders = false;

    /**
     * @var string the method used in the request.
     */
    public $method = self::METHOD_GET;

    /**
     * Client constructor.
     *
     * @param array $options
     *
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function __construct(array $options = array())
    {
        $this->populateFromArray($options);

        foreach (array('paramsGet', 'paramsPost', 'paramsPut', 'paramsDelete', 'headers') as $param) {
            if (!($this->$param instanceof Params)) {
                $this->$param = new Params($this->$param);
            }
        }
    }

    /**
     * Whether the request method is a GET method.
     *
     * @return bool
     */
    public function getIsGetMethod()
    {
        return strtoupper($this->method) === self::METHOD_GET;
    }

    /**
     * Whether the request method is a POST method.
     *
     * @return bool
     */
    public function getIsPostMethod()
    {
        return strtoupper($this->method) === self::METHOD_POST;
    }

    /**
     * Whether the request method is a PUT method.
     *
     * @return bool
     */
    public function getIsPutMethod()
    {
        return strtoupper($this->method) === self::METHOD_PUT;
    }

    /**
     * Whether the request method is a DELETE method.
     *
     * @return bool
     */
    public function getIsDeleteMethod()
    {
        return strtoupper($this->method) === self::METHOD_DELETE;
    }

    /**
     * @return mixed|Response
     * @throws \Exception
     */
    public function request()
    {
        $request = new Request($this);

        return $response = $request->send();
    }
}