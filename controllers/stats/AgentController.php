<?php
namespace app\controllers\stats;

use Yii;
use app\classes\BaseController;

class AgentController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'actions' => ['report'],
                'roles' => ['clients.read'],
            ],
        ];
        return $behaviors;
    }

    public function actionReport()
    {

        return $this->render('report', ['dataProvider' => $dataProvider, 'model' => $model]);
    }

}