<?php
namespace app\controllers\templates\document;

use yii\filters\AccessControl;
use app\classes\BaseController;
use app\models\document\DocumentTemplate;

class TemplateController extends BaseController
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

    public function actionIndex()
    {
        return $this->render('list');
    }

    public function actionEdit($id = false)
    {
        $model = DocumentTemplate::findOne($id);

        if (!$model) {
            $model = new DocumentTemplate;
        }

        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            \Yii::$app->session->setFlash('success');
        }

        return $this->render('edit', ['model' => $model]);
    }

}