<?php

namespace app\modules\atol\controllers;

use app\modules\atol\Module;
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
        // @todo Если так и не будет приходить - выпилить
        $post = Yii::$app->request->post();
        Yii::error('AtolAPI ' . print_r($post, true), Module::LOG_CATEGORY);
        return 'Ok';
    }
}