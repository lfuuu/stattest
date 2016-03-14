<?php
namespace app\controllers\tariff;

use app\classes\BaseController;
use app\models\filter\DidGroupFilter;
use Yii;
use yii\filters\AccessControl;

class DidGroupController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['tarifs.read'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Список
     * @return string
     */
    public function actionIndex()
    {
        $filterModel = new DidGroupFilter();
        $filterModel->load(Yii::$app->request->getQueryParams());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }
}
