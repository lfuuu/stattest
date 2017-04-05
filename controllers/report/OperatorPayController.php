<?php
/**
 * Отчет по платежам операторов
 */

namespace app\controllers\report;

use app\classes\BaseController;
use app\models\filter\OperatorPayFilter;
use Yii;

class OperatorPayController extends BaseController
{
    /**
     * Вывод списка
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        $this->view->title = 'Реестр неоплаченных счетов поставщиков';
        $filterModel = new OperatorPayFilter();
        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }
}