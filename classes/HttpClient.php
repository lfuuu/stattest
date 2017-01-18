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

        $this->requestConfig['class'] = HttpRequest::className();
        $this->setTransport(\yii\httpclient\CurlTransport::class);
    }

    /**
     * @return $this
     */
    public function addRequestFormatJson()
    {
        $this->requestConfig['format'] = HttpClient::FORMAT_JSON;
        return $this;
    }

    /**
     * @return $this
     */
    public function addResponseFormatJson()
    {
        $this->responseConfig['format'] = HttpClient::FORMAT_JSON;
        return $this;
    }

    /**
     * @return HttpRequest
     */
    public function createJsonRequest()
    {
        return $this
            ->addRequestFormatJson()
            ->addResponseFormatJson()
            ->createRequest();
    }


}