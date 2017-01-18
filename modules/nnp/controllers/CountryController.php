<?php

namespace app\modules\nnp\controllers;

use app\classes\BaseController;
use app\modules\nnp\filter\CountryFilter;
use Yii;

/**
 * Страны
 */
class CountryController extends BaseController
{
    /**
     * Список
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        $filterModel = new CountryFilter();
        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }
}
