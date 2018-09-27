<?php
namespace app\controllers\voip;

use Yii;
use app\classes\BaseController;
use yii\filters\AccessControl;
use app\models\filter\UsageTrunkFilter;

class TrunksController extends BaseController
{

    /**
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
                        'roles' => ['voip.admin'],
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
        $filterModel = new UsageTrunkFilter;
        $filterModel->load(Yii::$app->request->get());

        return $this->render('grid', [
            'filterModel' => $filterModel,
        ]);
    }

}