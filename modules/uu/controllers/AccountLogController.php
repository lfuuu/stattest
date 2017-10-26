<?php
/**
 * Транзакции
 */

namespace app\modules\uu\controllers;

use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\modules\uu\filter\AccountLogMinFilter;
use app\modules\uu\filter\AccountLogPeriodFilter;
use app\modules\uu\filter\AccountLogResourceFilter;
use app\modules\uu\filter\AccountLogSetupFilter;
use yii\filters\AccessControl;

class AccountLogController extends BaseController
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
                        'actions' => ['setup', 'period', 'resource', 'min'],
                        'roles' => ['newaccounts_balance.read'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionSetup()
    {
        $filterModel = new AccountLogSetupFilter();
        $this->_addClientAccountFilter($filterModel);

        return $this->render(
            'setup',
            [
                'filterModel' => $filterModel,
            ]
        );
    }

    /**
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionPeriod()
    {
        $filterModel = new AccountLogPeriodFilter();
        $this->_addClientAccountFilter($filterModel);

        return $this->render(
            'period',
            [
                'filterModel' => $filterModel,
            ]
        );
    }

    /**
     * @return string
     */
    public function actionResource()
    {
        $filterModel = new AccountLogResourceFilter();
        $this->_addClientAccountFilter($filterModel);

        return $this->render(
            'resource',
            [
                'filterModel' => $filterModel,
            ]
        );
    }

    /**
     * @return string
     */
    public function actionMin()
    {
        $filterModel = new AccountLogMinFilter();
        $this->_addClientAccountFilter($filterModel);

        return $this->render(
            'min',
            [
                'filterModel' => $filterModel,
            ]
        );
    }

}