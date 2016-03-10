<?php

namespace app\controllers;

use Yii;
use yii\web\Response;
use app\classes\BaseController;

define('API_HOST', Yii::$app->request->serverName);

class SwaggerController extends BaseController
{

    public function actionIndex()
    {
        $this->layout = 'empty';
        return $this->render('index', [
            'host' => Yii::$app->request->serverName,
            'documentationPath' => '/swagger/documentation',
            'apiKey' => Yii::$app->params['API_SECURE_KEY'],
        ]);
    }

    public function actionDocumentation()
    {
        $swagger = \Swagger\scan(Yii::$app->controllerPath . '/api');

        $response = Yii::$app->getResponse();
        $response->headers->set('Content-Type', 'application/json');
        $response->format = Response::FORMAT_JSON;

        print $swagger;

        Yii::$app->end();
    }

}