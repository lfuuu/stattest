<?php
namespace app\controllers;

use app\models\HistoryVersion;
use Yii;
use app\classes\BaseController;
use yii\base\Exception;

class VersionController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'actions' => ['show'],
                'roles' => ['clients.read'],
            ],
        ];
        return $behaviors;
    }

    public function actionShow()
    {
        $getRequest = Yii::$app->request->get();
        if(!$getRequest)
            throw new Exception('Models not exists');

        $versions = HistoryVersion::find();

        foreach($getRequest as $model => $id)
            $versions->orWhere(['model' => $model, 'model_id' => $id]);

        $versions = $versions->all();

        $this->layout = 'minimal';

        return Yii::$app->request->isAjax
            ? $this->renderPartial('show', ['versions' => $versions])
            : $this->render('show', ['versions' => $versions]);
    }
}