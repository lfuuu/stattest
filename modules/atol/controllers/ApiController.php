<?php

namespace app\modules\atol\controllers;

use Yii;
use yii\web\Controller;


/**
 * Аутентификация не нужна. Поэтому Controller, а не BaseController
 */
class ApiController extends Controller
{
    /**
     * Инициализация
     */
    public function init()
    {
        $this->enableCsrfValidation = false;
    }

    /**
     * Сюда должен приходить ответ от Атол
     *
     * @return string
     */
    public function actionIndex()
    {
        // @todo Что и в каком формате они присылают - ХЗ. В документации это не описано.
        Yii::error(Yii::$app->request->post(), 'atol');
        return 'Ok';
    }
}