<?php

namespace app\controllers\report\accounting;

use app\classes\BaseController;
use app\models\filter\SaleBookFilter;
use yii\filters\AccessControl;

class SaleBookController extends BaseController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['newaccounts_balance.read'],
                    ],
                ],
            ],
        ]);
    }

    public function actionIndex()
    {
        $filter = new SaleBookFilter();

        $filter->load(\Yii::$app->request->get()) && $filter->validate();

        return $this->render('index', [
            'filter' => $filter
        ]);
    }
}