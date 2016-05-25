<?php

namespace app\classes;


use yii\base\Component;
use yii\db\Exception;
use yii\httpclient\Client;

class DBROConnection extends Component
{
    public $url = "";

    /**
     * Исполняющий механизм запросов к DBRO
     *
     * @param $query
     * @return mixed
     * @throws Exception
     */
    public function executeQuery($query)
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl($this->url)
            ->setFormat(Client::FORMAT_JSON)
            ->setData($query)
            ->send();

        if (!$response->getIsOk()) {
            throw new Exception($response->getContent(), $response->getStatusCode());
        }

        return $response->data;
    }
}