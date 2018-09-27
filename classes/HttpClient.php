<?php

namespace app\classes;

use yii\httpclient\Client;

/**
 * Class HttpClient
 *
 * @method HttpRequest createRequest()
 */
class HttpClient extends Client
{

    /**
     * HttpClient constructor
     *
     * @param array $config
     */
    public function __construct($config = [])
    {

        parent::__construct($config);

        $this->requestConfig['class'] = HttpRequest::class;
        $this->setTransport(\yii\httpclient\CurlTransport::class);
    }

    /**
     * @param string $format
     * @return $this
     */
    public function setRequestFormat($format)
    {
        $this->requestConfig['format'] = $format;
        return $this;
    }

    /**
     * @param string $format
     * @return $this
     */
    public function setResponseFormat($format)
    {
        $this->responseConfig['format'] = $format;
        return $this;
    }

    /**
     * @return HttpRequest
     */
    public function createJsonRequest()
    {
        return $this
            ->setRequestFormat(HttpClient::FORMAT_JSON)
            ->setResponseFormat(HttpClient::FORMAT_JSON)
            ->createRequest();
    }

}