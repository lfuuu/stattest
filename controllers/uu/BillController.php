<?php
/**
 * Счета
 */

namespace app\controllers\uu;

use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\classes\uu\filter\BillFilter;
use yii\filters\AccessControl;

class BillController extends BaseController
{
    // Установить юзерские фильтры + добавить фильтр по клиенту, если он есть
    use AddClientAccountFilterTraits;

    /**
     * Права доступа
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => ['newaccounts_balance.read'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $filterModel = new BillFilter();
        $this->addClientAccountFilter($filterModel);

        return $this->render(
            'index',
            [
                'filterModel' => $filterModel,
            ]
        );
    }
}