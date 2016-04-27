<?php
/**
 * Транзакции
 */

namespace app\controllers\uu;

use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\classes\uu\filter\AccountLogPeriodFilter;
use app\classes\uu\filter\AccountLogResourceFilter;
use app\classes\uu\filter\AccountLogSetupFilter;
use Yii;
use yii\filters\AccessControl;

class AccountLogController extends BaseController
{
    // Установить юзерские фильтры + добавить фильтр по клиенту, если он есть
    use AddClientAccountFilterTraits;

    /**
     * Права доступа
     * @return []
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['setup', 'period', 'resource'],
                        'roles' => ['tarifs.read'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionSetup()
    {
        $filterModel = new AccountLogSetupFilter();
        $this->addClientAccountFilter($filterModel);

        return $this->render('setup', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * @return string
     */
    public function actionPeriod()
    {
        $filterModel = new AccountLogPeriodFilter();
        $this->addClientAccountFilter($filterModel);

        return $this->render('period', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * @return string
     */
    public function actionResource()
    {
        $filterModel = new AccountLogResourceFilter();
        $this->addClientAccountFilter($filterModel);

        return $this->render('resource', [
            'filterModel' => $filterModel,
        ]);
    }
}