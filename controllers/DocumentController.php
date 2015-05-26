<?php
namespace app\controllers;

use app\models\ClientDocument;
use Yii;
use app\classes\BaseController;
use yii\base\Exception;
use yii\helpers\Url;

class DocumentController extends BaseController
{
    public function actionDelete($id)
    {
        $model = ClientDocument::findOne($id);
        if(!$model)
            throw new Exception('Document not found');

        $model->is_active = 0;
        $model->save();
        $this->redirect(Yii::$app->request->referrer);
    }

}