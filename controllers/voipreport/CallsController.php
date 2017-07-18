<?php
/**
 * Межоператорка (отчеты). Список звонков
 */

namespace app\controllers\voipreport;

use app\classes\BaseController;
use app\models\filter\CallsRawFilter;
use Yii;

class CallsController extends BaseController
{
    /**
     * Звонки в транке. Список звонков
     *
     * @return string
     */
    public function actionTrunc()
    {
        $filterModel = new CallsRawFilter();
        $filterModel->load(Yii::$app->request->get());

        return $this->render('trunc', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * Себестоимость. Отчет по направлениям
     *
     * @return string
     */
    public function actionCost()
    {
        $filterModel = new CallsRawFilter();
        $filterModel->load(Yii::$app->request->get());

        return $this->render('cost', [
            'filterModel' => $filterModel,
        ]);
    }

}