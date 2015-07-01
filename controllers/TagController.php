<?php
namespace app\controllers;

use app\classes\BaseController;
use app\models\TagToModel;
use \Yii;
use yii\filters\AccessControl;

class TagController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['clients.edit'],
                    ],
                ],
            ],
        ];
    }

    public function actionSet($modelName, $modelId, $tagId)
    {
        $tagModel = new TagToModel();
        $tagModel->tag_id = $tagId;
        $tagModel->model = $modelName;
        $tagModel->model_id = $modelId;
        $tagModel->create_at = date('Y-m-d H:i:s');
        $tagModel->user_id = Yii::$app->user->id;
        $tagModel->save();
    }

    public function actionUnset($modelName, $modelId, $tagId)
    {
        $tag = TagToModel::findOne(['model' => $modelName, 'model_id' => $modelId, 'tag_id' => $tagId]);
        if($tag)
            $tag->delete();
    }
}
