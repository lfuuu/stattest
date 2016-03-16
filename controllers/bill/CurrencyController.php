<?php
/**
 * Валюта
 */

namespace app\controllers\bill;

use app\classes\BaseController;
use app\models\filter\CurrencyRateFilter;
use Yii;

class CurrencyController extends BaseController
{
    /**
     * Список
     *
     * @return string
     */
    public function actionIndex()
    {
        $filterModel = new CurrencyRateFilter();
        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }

}