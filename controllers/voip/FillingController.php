<?php
/**
 * Controller for number filling report
 */

namespace app\controllers\voip;

use app\models\voip\Filling;
use Yii;
use app\classes\BaseController;
use app\models\voip\Generic;
use yii\filters\AccessControl;
use app\classes\SkaTpl;

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
                        'actions' => ['index', 'getasync'],
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
    public function actionIndex ()
    {
        $model = new Filling();
        $model->load(Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $model->getFilling()
        ]);
    }

}