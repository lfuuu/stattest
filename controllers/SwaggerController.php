<?php

namespace app\controllers;

use app\classes\BaseController;
use Yii;
use yii\helpers\Json;
use yii\web\Response;

define('API_HOST', Yii::$app->request->serverName);

class SwaggerController extends BaseController
{
    /**
     * Базовый Swagger UI
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        $this->layout = 'empty';
        return $this->render('index', [
            'documentationPath' => '/swagger/documentation',
            'apiKey' => Yii::$app->params['API_SECURE_KEY'],
        ]);
    }

    /**
     * JSON список доступной документации
     *
     * @throws \yii\base\ExitException
     */
    public function actionDocumentation()
    {
        $response = Yii::$app->getResponse();
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        $response->format = Response::FORMAT_JSON;

        $swagger = \Swagger\scan([
            Yii::$app->basePath . '/classes/ApiController.php',
            Yii::$app->controllerPath . '/api',
        ]);

        $swagger->schemes = [];

        // чтобы использовать Response, необходимо возвращать (не echo!) массив
        // а swagger возвращает не массив, а уже json-строку.  Приходится из нее обратно делать массив
        return Json::decode((string)$swagger);
    }

}