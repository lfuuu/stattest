<?php

namespace app\modules\sorm\controllers;

use app\exceptions\web\NotImplementedHttpException;
use app\models\filter\SormClientFilter;
use app\models\Task;
use app\modules\sorm\filters\ClientsFilter;
use app\modules\sorm\models\pg\Address;
use Yii;
use yii\web\Response;
use app\classes\BaseController;

class AddressController extends BaseController
{
    public function actionIndex($hash)
    {
        $model = Address::find()->where(['hash' => $hash])->one();

        if (!$model) {
            $model = Address::find()->where(['address' => $hash])->one();
        }

        if (!$model) {
            throw new \InvalidArgumentException('Адрес не найден');
        }

        if (\Yii::$app->request->isPost) {
            try {
                if ($model->load(\Yii::$app->request->post()) && $model->save()) {
                    \Yii::$app->session->addFlash('success', 'Данные сохранены');
                }
            } catch (\Exception $e) {
                \Yii::$app->session->addFlash('error', $e->getMessage());
            }
            $model->refresh();
        }

        return $this->render('index', [
            'hash' => $hash,
            'model' => $model,
        ]);
    }
}