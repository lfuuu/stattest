<?php
namespace app\controllers\tariffication;

use app\classes\ListForm;
use app\models\tariffication\ServiceType;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use app\classes\BaseController;

class ServiceTypeController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $query = ServiceType::find();

        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => ListForm::PAGE_SIZE,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $provider,
        ]);
    }
}
