<?php

namespace app\modules\nnp\controllers;

use app\classes\BaseController;
use app\modules\nnp\filter\PrefixFilter;
use app\modules\nnp\forms\prefix\FormEdit;
use app\modules\nnp\forms\prefix\FormNew;
use Yii;

/**
 * Префиксы
 */
class PrefixController extends BaseController
{
    /**
     * Список
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        $filterModel = new PrefixFilter();
        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }
}
