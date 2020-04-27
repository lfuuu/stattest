<?php

namespace tests\codeception\web\api;

use \_WebTester as _WebTester;
use Yii;

class ApiExternalTest
{
    /** _WebTester **/
    protected $server;
    protected $apiUrl = '';
    protected $title = '';

    public function __construct(_WebTester $server)
    {
        $server->amBearerAuthenticated(Yii::$app->params['API_SECURE_KEY']);
        $server->haveHttpHeader("Content-Type", "application/json");
        //$server->seeResponseIsJson();

        $this->server = $server;

        $this->up();
    }

    /**
     * Up
     */
    public function up()
    {
        // override
    }

    /**
     * Down
     */
    public function down()
    {
        // override
    }

    /**
     * Получить ответ
     *
     * @param bool $asArray
     * @return array|string
     */
    public function getResponse($asArray = true)
    {
        $response = $this->server->grabResponse();

        return $asArray ? json_decode($response, true) : $response;
    }
}