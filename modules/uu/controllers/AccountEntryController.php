<?php
/**
 * Проводки
 */

namespace app\modules\uu\controllers;

use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\modules\uu\filter\AccountEntryFilter;
use yii\filters\AccessControl;

class AccountEntryController extends BaseController
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
                'class' => AccessControl::class,
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
        $filterModel = new AccountEntryFilter();
        $this->_addClientAccountFilter($filterModel);

        return $this->render(
            'index',
            [
                'filterModel' => $filterModel,
            ]
        );
    }
}