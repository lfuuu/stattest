<?php
/**
 * Универсальный тарификатор
 */

namespace app\controllers\uu;

use app\classes\BaseController;
use app\classes\uu\filter\AccountLogMonitorFilter;
use app\classes\uu\filter\AccountLogPeriodFilter;
use app\classes\uu\filter\AccountLogResourceFilter;
use app\classes\uu\filter\AccountLogSetupFilter;
use Yii;
use yii\filters\AccessControl;

class AccountlogController extends BaseController
{
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
                        'actions' => ['setup', 'period', 'resource', 'monitor'],
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
        $filterModel->load(Yii::$app->request->get());

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
        $filterModel->load(Yii::$app->request->get());

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
        $filterModel->load(Yii::$app->request->get());

        return $this->render('resource', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * @return string
     */
    public function actionMonitor()
    {
        $filterModel = new AccountLogMonitorFilter();
        $filterModel->load(Yii::$app->request->get());

        return $this->render('monitor', [
            'filterModel' => $filterModel,
        ]);
    }
}