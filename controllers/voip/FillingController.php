<?php
/**
 * Controller for number filling report
 */

namespace app\controllers\voip;

use app\models\voip\Filling;
use Yii;
use app\classes\BaseController;
use yii\filters\AccessControl;

class FillingController extends BaseController
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
     * Controller for /voip/filling
     *
     * @return string
     */
    public function actionIndex()
    {
        $model = new Filling();
        $model->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $model
        ]);
    }

}