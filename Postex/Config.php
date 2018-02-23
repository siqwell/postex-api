<?php
namespace Postex;

use Exception;

/**
 * Class Config
 * @package Postex
 */
class Config extends Base
{
    /**
     * @var string the api public key
     */
    public $publicKey;

    /**
     * @var string the api private key.
     */
    public $privateKey;

    /**
     * @var string the preffered charset.
     */
    public $charset = 'utf-8';

    /**
     * @var string the API url.
     */
    private $_apiUrl = 'https://postex.io/api/';

    /**
     * Config constructor.
     *
     * @param array $config
     *
     * @throws \ReflectionException
     */
    public function __construct(array $config = array())
    {
        $this->populateFromArray($config);
    }

    /**
     * @param $url
     *
     * @return $this
     * @throws Exception
     */
    public function setApiUrl($url)
    {
        if (!parse_url($url, PHP_URL_HOST)) {
            throw new Exception('Please set a valid api base url.');
        }

        $this->_apiUrl = trim($url, '/') . '/';

        return $this;
    }

    /**
     * @param null $endpoint
     *
     * @return string
     * @throws Exception
     */
    public function getApiUrl($endpoint = null)
    {
        if ($this->_apiUrl === null) {
            throw new Exception('Please set the api base url.');
        }

        return $this->_apiUrl . $endpoint;
    }
}