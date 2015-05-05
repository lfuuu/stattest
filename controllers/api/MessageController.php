<?php

namespace app\controllers\api;

use Yii;
use app\classes\ApiController;
use app\models\Message;

class MessageController extends ApiController {

    public function actionList() {
        $data = Yii::$app->request->bodyParams;
        $model = new Message();
        return Message::find()
                        ->where(['account_id' => $data['client_account_id']])
                        ->orderBy(['created_at' => ($data['order'] == 'desc' ) ? SORT_DESC : SORT_ASC])
                        ->limit(100)
                        ->asArray()
                        ->all();
    }

    public function actionDetails() {
        $data = Yii::$app->request->bodyParams;
        $model = Message::find()->where(['id' => $data['id'], 'account_id' => $data['client_account_id']])->with('text')->asArray()->one();
        return $model;
    }

    public function actionRead() {
        $data = Yii::$app->request->bodyParams;
        $model = Message::findOne(['id' => $data['id'], 'account_id' => $data['client_account_id']]);
        if($model){
            $model->is_read = 1;
            $model->save();
            return $model;
        }
        else
            throw new FormValidationException($model);
    }

}
