<?php
/**
 * Controller for number cdr-workload report
 */

namespace app\controllers\voipreport;

use app\models\voip\filter\CdrWorkload;
use Yii;
use app\classes\BaseController;
use yii\filters\AccessControl;

class CdrWorkloadController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => ['voip.access'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Controller for /voip/cdr-workload
     *
     * @return string
     */
    public function actionIndex()
    {
        $model = new CdrWorkload();
        $model->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $model
        ]);
    }

}