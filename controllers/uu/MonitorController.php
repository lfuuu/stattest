<?php
/**
 * Мониторинг транзакций
 */

namespace app\controllers\uu;

use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\classes\uu\filter\AccountLogMonitorFilter;
use app\classes\uu\filter\AccountLogPeriodFilter;
use app\classes\uu\filter\AccountLogResourceFilter;
use app\classes\uu\filter\AccountLogSetupFilter;
use Yii;
use yii\filters\AccessControl;

class MonitorController extends BaseController
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
                        'actions' => ['index'],
                        'roles' => ['tarifs.read'],
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
        $filterModel = new AccountLogMonitorFilter();
        $this->addClientAccountFilter($filterModel);

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }
}